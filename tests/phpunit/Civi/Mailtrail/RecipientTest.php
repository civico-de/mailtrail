<?php

declare(strict_types = 1);

namespace Civi\Mailtrail\Tests;

use Civi\Mailtrail\Recipient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Mailtrail\Recipient
 */
class RecipientTest extends TestCase {

  public function testMailboxesTakeTheAngleBracketAddressAndIgnoreDisplayNames(): void {
    static::assertSame(['a@example.org'], Recipient::mailboxes('a@example.org'));
    static::assertSame(['a@example.org', 'b@example.org'], Recipient::mailboxes(' a@example.org , B <b@example.org>'));
    static::assertSame(['organiser@example.org'], Recipient::mailboxes('"member@example.org" <organiser@example.org>'));
    static::assertSame([], Recipient::mailboxes(''));
    static::assertSame([], Recipient::mailboxes('undisclosed-recipients:;'));
  }

}
