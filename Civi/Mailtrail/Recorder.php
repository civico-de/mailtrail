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
    $mail = new MailParams($params);
    $contactId = self::contactId($mail);
    if ($contactId <= 0 || $mail->string('groupName') === self::CORE_ACTIVITY_SENDER) {
      return 0;
    }
    $create = \Civi\Api4\Activity::create(checkPermissions: FALSE)
      ->addValue('activity_type_id:name', 'Email')
      ->addValue('status_id:name', 'Completed')
      ->addValue('subject', $mail->string('subject'))
      ->addValue('details', nl2br(htmlspecialchars(self::plainText($mail))))
      ->addValue('source_contact_id', \CRM_Core_Session::getLoggedInContactID() ?: $contactId)
      ->addValue('target_contact_id', [$contactId])
      ->addValue('activity_date_time', date('Y-m-d H:i:s'))
      ->addValue('mailtrail.recorded', value: TRUE);
    self::addOptionalReferences($create, $mail);
    $row = $create->execute()->first();
    return (new MailParams(is_array($row) ? $row : []))->int('id');
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
   * Top-level contactId, else the one from the token context.
   */
  private static function contactId(MailParams $mail): int {
    $contactId = $mail->int('contactId');
    return $contactId > 0 ? $contactId : $mail->tokenContext()->int('contactId');
  }

  /**
   * The text part, or the HTML part converted when the mail has no text.
   */
  private static function plainText(MailParams $mail): string {
    $text = trim($mail->string('text'));
    $html = $mail->string('html');
    if ($text === '' && $html !== '') {
      $text = trim(\CRM_Utils_String::htmlToText($html));
    }
    return $text;
  }

  private static function addOptionalReferences(\Civi\Api4\Generic\DAOCreateAction $create, MailParams $mail): void {
    $templateId = $mail->int('messageTemplateID');
    if ($templateId > 0) {
      $create->addValue('mailtrail.template_id', $templateId);
    }
    $contributionId = $mail->tokenContext()->int('contributionId');
    if ($contributionId > 0) {
      $create->addValue('source_record_id', $contributionId);
    }
  }

}
