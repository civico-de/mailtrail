# mailtrail

Records every e-mail CiviCRM sends to a contact as an Email activity on that contact:
subject, plain-text body, and the message template it was rendered from. Receipts,
scheduled reminders, extension mails, anything that goes through `CRM_Utils_Mail::send()`
with a contact, shows up in the contact's activity tab. Staff can see what went out and
when.

Documentation: https://civico-de.github.io/mailtrail/

## What it does

- Listens to `hook_civicrm_postEmailSend`, which core fires after the mailer accepted the
  message. Mails that failed to send are not recorded.
- Creates one activity per mail: type Email, status Completed, `subject` from the mail,
  `details` set to the plain-text body (HTML-only mails are converted), the contact as
  target, and the logged-in user as source (or the contact, when sent by a job).
- Marks each activity with the managed custom fields `mailtrail.recorded` (always true)
  and `mailtrail.template_id` (the originating `MessageTemplate` id: the explicit one, or
  the workflow's default template for mails sent by workflow name), and stores a contribution id from the token context in `source_record_id`.
- Provides `Recorder::sentSince($contactId, $templateId, $since)` in the class
  `\Civi\Mailtrail\Recorder` for senders that must not remind too often. The activity log
  is the marker, so no separate bookkeeping is needed.

## What it skips

- Mails without a contact id, such as system notifications and admin alerts.
- Mails whose To addresses do not belong to that contact. Core passes the triggering
  contact's id also for scheduled reminders to alternate recipients and for the
  on-behalf-of duplicate alert to the contribution page's sender address.
- CiviCase activity copies (workflow `case_activity`). Core records those itself.
- Mails from core's "Send an Email" screen (`groupName` is Activity Email Sender). Core
  already creates that activity.
- CiviMail bulk mailings. They never pass through `postEmailSend` and keep their own
  delivery log.

## Retention

Bodies of transactional mails are personal data with a short useful life. mailtrail does
not delete anything itself. It marks its activities so a
[sunset](https://github.com/civico-de/sunset) policy can. This example clears the text
after two years and keeps subject and date:

| Field | Value |
| --- | --- |
| `target_entity` | `Activity` |
| `target_filter` | `[["mailtrail.recorded","=",true]]` |
| `age_field` | `activity_date_time` |
| `retention_days` | `730` |
| `action` | `clear_fields` |
| `action_params` | `{"fields":["details"]}` |

Preview with sunset's dry run before switching the policy live. To drop the whole activity
instead, use the action `delete`. Then `sentSince()` forgets the send too, so keep
`retention_days` above the longest reminder interval.

## Install

```bash
cv ext:enable mailtrail
```

There are no settings. Uninstall removes the custom group (managed, `cleanup: always`).
Recorded activities stay, and their custom values are dropped with the group.

## Development

```bash
cd .docker && docker compose up -d   # CiviCRM :8084, maildev :1084
docker compose exec -u www-data app bash -c 'cd /var/www/html/ext/mailtrail \
  && ckphpunit && phpstan analyse && cklint --all && ckconform'
```

## License and support

AGPL-3.0-or-later. © civico GmbH. See [LICENSE.txt](LICENSE.txt). Report bugs in the [issue tracker](https://github.com/civico-de/mailtrail/issues). Report security issues by email to security@civico.de, not in a public issue; see [SECURITY.md](SECURITY.md).
