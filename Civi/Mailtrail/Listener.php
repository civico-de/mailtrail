<?php

declare(strict_types = 1);

namespace Civi\Mailtrail;

use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * hook_civicrm_postEmailSend → Recorder. Core fires the hook only after the
 * mailer accepted the message, so nothing that failed to send is recorded.
 * A recording failure is logged and never surfaces to the sender: the mail
 * is already out, and an exception here would misreport it as unsent.
 *
 * @service mailtrail.listener
 */
class Listener extends AutoService implements EventSubscriberInterface {

  /**
   * @return array<string, array<int, array<int, int|string>>>
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_civicrm_postEmailSend' => [['onPostEmailSend', 0]],
    ];
  }

  public function onPostEmailSend(GenericHookEvent $event): void {
    try {
      // The hook's single argument (&$params), positional in the generic event.
      $values = $event->getHookValues();
      $params = is_array($values[0] ?? NULL) ? $values[0] : [];
      /** @var array<string, mixed> $params */
      Recorder::record($params);
    }
    catch (\Throwable $e) {
      \Civi::log()->warning('mailtrail: could not record sent mail: ' . $e->getMessage());
    }
  }

}
