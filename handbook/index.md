---
title: mailtrail
---

# mailtrail

mailtrail records every e-mail CiviCRM sends to a contact as an Email activity on that contact. Receipts, event confirmations, scheduled reminders and mails sent by other extensions then show up in the contact's activity tab with subject, date and plain-text body. Staff can see what went out and when, without asking the mail server.

## What is recorded

mailtrail listens to the mails CiviCRM sends through its own mailer. It records a mail only after the mailer accepted it, so a mail that failed to send never appears in the history.

For each mail, mailtrail creates one activity:

| Field | Value |
|---|---|
| Activity type | Email |
| Status | Completed |
| Subject | The subject of the mail |
| Details | The plain-text body. A mail with only an HTML part is converted to text. |
| With contact | The recipient |
| Added by | The logged-in user, or the recipient when nobody is logged in, as with scheduled jobs and public forms |
| Message template ID | The message template the mail was rendered from, when it had one |

Attachments, headers and the HTML part are not stored.

## What is not recorded

- Mails without a recipient contact, such as system notifications and alerts to administrators.
- Mails sent to an address that does not belong to the contact. A scheduled reminder that goes to other recipients than the contact it is about is an example.
- Mails from CiviCRM's "Send an Email" screen and copies of case activities. CiviCRM already records those as activities.
- CiviMail mailings. They keep their own delivery reports.

## Requirements

mailtrail needs no other extension. To clear or delete old mail bodies, install [sunset](https://github.com/civico-de/sunset), see [Retention](retention.md). The supported CiviCRM and PHP versions are listed in the extension's `info.xml`.

## Install

Download the release archive from the [releases page](https://github.com/civico-de/mailtrail/releases), unpack it into your CiviCRM extensions directory and enable it:

```bash
cv ext:enable mailtrail
```

There are no settings. Recording starts with the next mail.
