# Changelog

0.1.0 shipped before the release workflow existed and was never tagged; its changes are listed
under 0.1.1.

## [Unreleased]

## [0.2.1] - 2026-11-06

### Added

- A handbook in English and German, shown inside CiviCRM by the handbook extension and published
  at https://civico-de.github.io/mailtrail/.

### Fixed

- A mail is recorded only when one of its To addresses belongs to the contact. Scheduled reminders
  sent to alternate recipients and the on-behalf-of duplicate alert are no longer recorded on the
  contact they are about.
- Copies of case activities are no longer recorded twice; core already creates their Email
  activity.
- Mails sent by workflow, such as contribution receipts, carry the workflow's default template in
  `mailtrail.template_id`, so `Recorder::sentSince()` finds them.

## [0.2.0] - 2026-11-06

### Changed

- Licensed under AGPL-3.0-or-later.

## [0.1.1] - 2026-10-30

### Added

- Records every mail CiviCRM sends to a contact as an Email activity with subject, plain-text body
  and the originating message template.

### Changed

- Requires PHP 8.2 or later; 0.1.0 required 8.3.
- Releases are cut by the shared release workflow and published as a GitHub release with the
  extension archive and its checksum.

[Unreleased]: https://github.com/civico-de/mailtrail/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/civico-de/mailtrail/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/civico-de/mailtrail/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/civico-de/mailtrail/releases/tag/v0.1.1
