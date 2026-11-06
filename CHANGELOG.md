# Changelog

## 0.2.1 (2026-11-06)

- A mail is recorded only when one of its To addresses belongs to the contact.
  Scheduled reminders sent to alternate recipients and the on-behalf-of
  duplicate alert are no longer recorded on the contact they are about.
- Copies of case activities are no longer recorded twice; core already creates
  their Email activity.
- Mails sent by workflow, such as contribution receipts, carry the workflow's
  default template in `mailtrail.template_id`, so `Recorder::sentSince()` finds
  them.
- A handbook in English and German, shown inside CiviCRM by the handbook
  extension and published at https://civico-de.github.io/mailtrail/.

## 0.2.0 (2026-11-06)

- Licensed under AGPL-3.0-or-later.

## 0.1.1 (2026-10-30)

- Requires PHP 8.2 or later; 0.1.0 required 8.3.
- Releases are cut by the shared release workflow and published as a GitHub
  release with the extension archive and its checksum.

## 0.1.0 (2026-09-02)

- Records every mail CiviCRM sends to a contact as an Email activity with
  subject, plain-text body and the originating message template.
