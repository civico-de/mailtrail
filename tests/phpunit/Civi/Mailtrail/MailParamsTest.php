<?php

declare(strict_types = 1);

namespace Civi\Mailtrail\Tests;

use Civi\Mailtrail\MailParams;
use PHPUnit\Framework\TestCase;

/**
 * The typed view on untyped mail params: every value shape maps to a
 * predictable int / string, and the token context is read the same way.
 *
 * @covers \Civi\Mailtrail\MailParams
 */
class MailParamsTest extends TestCase {

  /**
   * @return iterable<string, array{mixed, int}>
   */
  public static function intCases(): iterable {
    yield 'int' => [42, 42];
    yield 'float' => [4.9, 4];
    yield 'numeric string' => ['17', 17];
    yield 'non-numeric string' => ['abc', 0];
    yield 'array' => [[1], 0];
    yield 'null' => [NULL, 0];
    yield 'bool' => [TRUE, 0];
  }

  /**
   * @dataProvider intCases
   */
  public function testInt(mixed $value, int $expected): void {
    static::assertSame($expected, (new MailParams(['k' => $value]))->int('k'));
  }

  public function testIntOfMissingKeyIsZero(): void {
    static::assertSame(0, (new MailParams([]))->int('k'));
  }

  /**
   * @return iterable<string, array{mixed, string}>
   */
  public static function stringCases(): iterable {
    yield 'string' => ['Hallo', 'Hallo'];
    yield 'int' => [7, '7'];
    yield 'float' => [1.5, '1.5'];
    yield 'bool' => [TRUE, '1'];
    yield 'array' => [['x'], ''];
    yield 'null' => [NULL, ''];
  }

  /**
   * @dataProvider stringCases
   */
  public function testString(mixed $value, string $expected): void {
    static::assertSame($expected, (new MailParams(['k' => $value]))->string('k'));
  }

  public function testStringOfMissingKeyIsEmpty(): void {
    static::assertSame('', (new MailParams([]))->string('k'));
  }

  public function testTokenContextReadsNestedValues(): void {
    $params = new MailParams(['tokenContext' => ['contactId' => '5', 'name' => 'x']]);
    static::assertSame(5, $params->tokenContext()->int('contactId'));
    static::assertSame('x', $params->tokenContext()->string('name'));
    static::assertSame(0, $params->tokenContext()->int('missing'));
  }

  /**
   * @return iterable<string, array{mixed}>
   */
  public static function nonArrayContexts(): iterable {
    yield 'missing' => [NULL];
    yield 'string' => ['ctx'];
    yield 'int' => [1];
  }

  /**
   * @dataProvider nonArrayContexts
   */
  public function testNonArrayTokenContextIsEmpty(mixed $context): void {
    $params = new MailParams($context === NULL ? [] : ['tokenContext' => $context]);
    static::assertSame(0, $params->tokenContext()->int('contactId'));
    static::assertSame('', $params->tokenContext()->string('name'));
  }

}
