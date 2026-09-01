<?php

declare(strict_types = 1);

namespace Civi\Mailtrail;

/**
 * One Email activity per sent mail: subject, plain-text body (HTML-only mails
 * are converted), mailtrail.recorded = TRUE as the marker every retention rule
 * can select on, the sending template in mailtrail.template_id and, when the
 * mail carried one, the contribution in source_record_id.
 *
 * Skipped: mails without a contact (system notifications), and mails from
 * core's "Send an Email" screen, which creates its own activity. CiviMail bulk
 * deliveries never pass through hook_civicrm_postEmailSend.
 */
class Recorder {

  public const CORE_ACTIVITY_SENDER = 'Activity Email Sender';

  /**
   * @param array<string, mixed> $params CRM_Utils_Mail::send() parameters.
   * @return int Activity id, 0 when nothing was recorded.
   */
  public static function record(array $params): int {
    $tokenContext = is_array($params['tokenContext'] ?? NULL) ? $params['tokenContext'] : [];
    $contactId = self::intOf($params['contactId'] ?? NULL) ?: self::intOf($tokenContext['contactId'] ?? NULL);
    if ($contactId <= 0 || self::stringOf($params['groupName'] ?? NULL) === self::CORE_ACTIVITY_SENDER) {
      return 0;
    }
    $text = trim(self::stringOf($params['text'] ?? NULL));
    $html = self::stringOf($params['html'] ?? NULL);
    if ($text === '' && $html !== '') {
      $text = trim(\CRM_Utils_String::htmlToText($html));
    }
    $create = \Civi\Api4\Activity::create(checkPermissions: FALSE)
      ->addValue('activity_type_id:name', 'Email')
      ->addValue('status_id:name', 'Completed')
      ->addValue('subject', self::stringOf($params['subject'] ?? NULL))
      ->addValue('details', nl2br(htmlspecialchars($text)))
      ->addValue('source_contact_id', \CRM_Core_Session::getLoggedInContactID() ?: $contactId)
      ->addValue('target_contact_id', [$contactId])
      ->addValue('activity_date_time', date('Y-m-d H:i:s'))
      ->addValue('mailtrail.recorded', value: TRUE);
    $templateId = self::intOf($params['messageTemplateID'] ?? NULL);
    if ($templateId > 0) {
      $create->addValue('mailtrail.template_id', $templateId);
    }
    $contributionId = self::intOf($tokenContext['contributionId'] ?? NULL);
    if ($contributionId > 0) {
      $create->addValue('source_record_id', $contributionId);
    }
    $row = $create->execute()->first();
    return self::intOf($row['id'] ?? NULL);
  }

  /**
   * Was template $templateId sent to $contactId since $since (Y-m-d H:i:s)?
   * The marker for senders that must not remind too often.
   */
  public static function sentSince(int $contactId, int $templateId, string $since): bool {
    return \Civi\Api4\Activity::get(checkPermissions: FALSE)
      ->addWhere('activity_type_id:name', '=', 'Email')
      ->addWhere('mailtrail.template_id', '=', $templateId)
      ->addWhere('target_contact_id', 'CONTAINS', $contactId)
      ->addWhere('activity_date_time', '>=', $since)
      ->selectRowCount()
      ->execute()
      ->count() > 0;
  }

  /**
   * Mail params are untyped; a non-scalar value counts as absent.
   */
  private static function intOf(mixed $value): int {
    return is_int($value) || is_float($value) || is_string($value) && is_numeric($value) ? (int) $value : 0;
  }

  private static function stringOf(mixed $value): string {
    return is_scalar($value) ? (string) $value : '';
  }

}
