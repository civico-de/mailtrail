<?php

declare(strict_types = 1);

namespace Civi\Mailtrail;

/**
 * Typed view on the untyped CRM_Utils_Mail::send() parameter array: a missing
 * key or a value of the wrong shape reads as 0 / '' / empty context.
 */
final class MailParams {

  /**
   * @param array<mixed> $params
   */
  public function __construct(
    private array $params,
  ) {}

  public function int(string $key): int {
    return self::intOf($this->params[$key] ?? NULL);
  }

  public function string(string $key): string {
    return self::stringOf($this->params[$key] ?? NULL);
  }

  /**
   * The nested token context, empty when the sender passed none.
   */
  public function tokenContext(): self {
    $context = $this->params['tokenContext'] ?? NULL;
    return new self(is_array($context) ? $context : []);
  }

  private static function intOf(mixed $value): int {
    if (is_int($value) || is_float($value)) {
      return (int) $value;
    }
    return is_string($value) && is_numeric($value) ? (int) $value : 0;
  }

  private static function stringOf(mixed $value): string {
    return is_scalar($value) ? (string) $value : '';
  }

}
