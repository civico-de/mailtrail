<?php

declare(strict_types = 1);

namespace Civi\Mailtrail\Tests;

use Civi\Mailtrail\Recorder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Recording sent mails as activities — via the real hook path (sendTemplate →
 * mock mailer → postEmailSend) and via Recorder::record() for the edge rules.
 *
 * @group headless
 * @covers \Civi\Mailtrail\Recorder
 * @covers \Civi\Mailtrail\Listener
 * @covers \Civi\Mailtrail\Recipient
 */
class RecorderHeadlessTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless(): \Civi\Test\CiviEnvBuilder {
    return ck_headless()->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    \Civi::settings()->set('mailing_backend', ['outBound_option' => \CRM_Mailing_Config::OUTBOUND_OPTION_MOCK]);
  }

  public function testATemplateMailSentThroughCoreIsRecordedWithTextAndTemplate(): void {
    $contactId = $this->contact('trail@example.org');
    $templateId = $this->template('Trail Test', "Hallo {contact.first_name},\nalles klar.");

    \CRM_Core_BAO_MessageTemplate::sendTemplate([
      'messageTemplateID' => $templateId,
      'contactId' => $contactId,
      'tokenContext' => ['contactId' => $contactId, 'contributionId' => 4711],
      'from' => 'Test <test@example.org>',
      'toEmail' => 'trail@example.org',
    ]);

    $acts = $this->activities($contactId);
    static::assertCount(1, $acts);
    static::assertSame('Trail Test', $acts[0]['subject']);
    static::assertSame("Hallo Trail,<br />\nalles klar.", $acts[0]['details']);
    static::assertTrue((bool) $acts[0]['mailtrail.recorded']);
    static::assertSame($templateId, self::int($acts[0]['mailtrail.template_id']));
    static::assertSame(4711, self::int($acts[0]['source_record_id']));
    static::assertSame('Completed', $acts[0]['status_id:name']);

    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
    static::assertTrue(Recorder::sentSince($contactId, $templateId, $yesterday));
    static::assertFalse(Recorder::sentSince($contactId, $templateId + 1, $yesterday));
    static::assertFalse(Recorder::sentSince($this->contact('other@example.org'), $templateId, $yesterday));
  }

  public function testAWorkflowMailIsTaggedWithTheWorkflowsDefaultTemplate(): void {
    $contactId = $this->contact('workflow@example.org');
    $templateId = $this->template('Workflow Test', 'Hallo.', 'mailtrail_test_workflow');

    \CRM_Core_BAO_MessageTemplate::sendTemplate([
      'workflow' => 'mailtrail_test_workflow',
      'contactId' => $contactId,
      'from' => 'Test <test@example.org>',
      'toEmail' => 'workflow@example.org',
    ]);

    $acts = $this->activities($contactId);
    static::assertCount(1, $acts);
    static::assertSame($templateId, self::int($acts[0]['mailtrail.template_id']));
    static::assertTrue(Recorder::sentSince($contactId, $templateId, date('Y-m-d H:i:s', strtotime('-1 day'))));
  }

  public function testAMailIsRecordedWhenAnyToAddressIsTheContacts(): void {
    $contactId = $this->contact('member@example.org');
    static::assertGreaterThan(0, Recorder::record([
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
      'toEmail' => 'Office <office@example.org>, Member <Member@Example.org>',
    ]));
    static::assertCount(1, $this->activities($contactId));
  }

  public function testAMailToSomeoneElseIsNotRecordedOnTheTriggeringContact(): void {
    // Alternate reminder recipients and the on-behalf duplicate alert keep the
    // triggering contact's id while the mail goes to other addresses.
    $contactId = $this->contact('member@example.org');
    static::assertSame(0, Recorder::record([
      'groupName' => 'Scheduled Reminder Sender',
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
      'toEmail' => 'organiser@example.org',
    ]));
    static::assertSame(0, Recorder::record([
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
      'toEmail' => '"member@example.org" <organiser@example.org>',
    ]));
    static::assertSame(0, Recorder::record([
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
    ]));
    static::assertCount(0, $this->activities($contactId));
  }

  public function testCaseActivityCopiesAreLeftToCore(): void {
    $contactId = $this->contact('case@example.org');
    static::assertSame(0, Recorder::record([
      'workflow' => 'case_activity',
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
      'toEmail' => 'case@example.org',
    ]));
    static::assertCount(0, $this->activities($contactId));
  }

  public function testHtmlOnlyMailIsStoredAsText(): void {
    $contactId = $this->contact('html@example.org');
    Recorder::record([
      'contactId' => $contactId,
      'subject' => 'Nur HTML',
      'html' => '<p>Hallo <b>Welt</b></p>',
      'toEmail' => 'html@example.org',
    ]);
    $acts = $this->activities($contactId);
    static::assertCount(1, $acts);
    static::assertStringNotContainsString('<b>', self::str($acts[0]['details']));
    static::assertStringContainsString('Hallo', self::str($acts[0]['details']));
    static::assertTrue((bool) $acts[0]['mailtrail.recorded']);
    static::assertNull($acts[0]['mailtrail.template_id']);
  }

  public function testCoreSendEmailAndContactlessMailsAreSkipped(): void {
    $contactId = $this->contact('core@example.org');
    static::assertSame(0, Recorder::record([
      'groupName' => Recorder::CORE_ACTIVITY_SENDER,
      'contactId' => $contactId,
      'subject' => 'x',
      'text' => 'y',
      'toEmail' => 'core@example.org',
    ]));
    static::assertSame(0, Recorder::record([
      'subject' => 'Admin alert',
      'text' => 'y',
      'toEmail' => 'ops@example.org',
    ]));
    static::assertCount(0, $this->activities($contactId));
  }

  private function contact(string $email): int {
    $row = \Civi\Api4\Contact::create(checkPermissions: FALSE)
      ->addValue('contact_type', 'Individual')
      ->addValue('first_name', 'Trail')
      ->addValue('last_name', 'Tester')
      ->addChain('email', \Civi\Api4\Email::create(checkPermissions: FALSE)
        ->addValue('contact_id', '$id')
        ->addValue('email', $email))
      ->execute()
      ->first();
    return self::int($row['id'] ?? 0);
  }

  private function template(string $title, string $text, ?string $workflow = NULL): int {
    $create = \Civi\Api4\MessageTemplate::create(checkPermissions: FALSE);
    if ($workflow !== NULL) {
      $create->addValue('workflow_name', $workflow)->addValue('is_default', value: TRUE);
    }
    $row = $create
      ->addValue('msg_title', $title)
      ->addValue('msg_subject', $title)
      ->addValue('msg_text', $text)
      ->addValue('msg_html', '<p>' . nl2br($text) . '</p>')
      ->addValue('is_active', value: TRUE)
      ->execute()
      ->first();
    return self::int($row['id'] ?? 0);
  }

  /**
   * @return list<array<mixed>>
   */
  private function activities(int $contactId): array {
    $result = \Civi\Api4\Activity::get(checkPermissions: FALSE)
      ->addSelect(
        'subject',
        'details',
        'source_record_id',
        'status_id:name',
        'mailtrail.recorded',
        'mailtrail.template_id',
      )
      ->addWhere('activity_type_id:name', '=', 'Email')
      ->addWhere('target_contact_id', 'CONTAINS', $contactId)
      ->execute();
    $rows = [];
    foreach ($result as $row) {
      static::assertIsArray($row);
      $rows[] = $row;
    }
    return $rows;
  }

  /**
   * Narrow an APIv4 value before casting; the API returns ids as int or numeric string. */
  private static function int(mixed $value): int {
    static::assertIsNumeric($value);
    return (int) $value;
  }

  private static function str(mixed $value): string {
    static::assertIsString($value);
    return $value;
  }

}
