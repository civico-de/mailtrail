<?php

declare(strict_types = 1);

namespace Civi\Mailtrail;

/**
 * Whether a mail's To list reaches a contact: core passes the triggering
 * contact's id also for mails addressed to someone else.
 */
final class Recipient {

  public static function isContact(int $contactId, string $toEmail): bool {
    $addresses = self::mailboxes($toEmail);
    if ($addresses === []) {
      return FALSE;
    }
    return \Civi\Api4\Email::get(checkPermissions: FALSE)
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('email', 'IN', $addresses)
      ->selectRowCount()
      ->execute()
      ->count() > 0;
  }

  /**
   * Addresses of a comma-separated list; in `Name <address>` only the part in
   * angle brackets counts, so an address used as display name is ignored.
   *
   * @return list<string>
   */
  public static function mailboxes(string $addressList): array {
    $addresses = [];
    foreach (explode(',', $addressList) as $part) {
      $address = preg_match('/<([^<>]+)>/', $part, $match) === 1 ? $match[1] : $part;
      $address = trim($address, " \t\"'");
      if (str_contains($address, '@')) {
        $addresses[] = $address;
      }
    }
    return $addresses;
  }

}
