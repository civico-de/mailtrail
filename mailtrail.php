<?php

declare(strict_types = 1);

// Loaded before PHPUnit starts coverage.
// @codeCoverageIgnoreStart
// phpcs:disable PSR1.Files.SideEffects
require_once 'mailtrail.civix.php';
// phpcs:enable
// @codeCoverageIgnoreEnd

/**
 * @file
 * Entry point: civix lifecycle only. The business hook
 * (hook_civicrm_postEmailSend) is a scan class, Civi\Mailtrail\Listener.
 */

/**
 * Implements hook_civicrm_config().
 *
 * @codeCoverageIgnore Civix delegate exercised during fresh install.
 */
function mailtrail_civicrm_config(\CRM_Core_Config $config): void {
  _mailtrail_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @codeCoverageIgnore Civix delegate exercised during fresh install.
 */
function mailtrail_civicrm_install(): void {
  _mailtrail_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @codeCoverageIgnore Civix delegate exercised during fresh install.
 */
function mailtrail_civicrm_enable(): void {
  _mailtrail_civix_civicrm_enable();
}
