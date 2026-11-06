---
title: Reference
order: 20
---

# Reference

## Custom fields

mailtrail adds the custom field group "Mail Trail" (API name `mailtrail`) to activities. It is hidden from public forms and collapsed on the activity view.

| Field | API name | Type | Content |
|---|---|---|---|
| Recorded by Mail Trail | `mailtrail.recorded` | Yes/No | Always yes on activities mailtrail created. The selector for retention policies and searches. |
| Message template ID | `mailtrail.template_id` | Number | The id of the message template the mail was rendered from. Empty for mails without a template. |

Both fields are searchable, so a SearchKit search can list, for example, every receipt sent with a given template.

When the mail carried a contribution in its token context, as contribution receipts do, the activity's source record is that contribution.

## Was a template sent recently?

Code that sends reminders can ask whether a template already went to a contact, without keeping its own bookkeeping:

```php
$alreadySent = \Civi\Mailtrail\Recorder::sentSince($contactId, $templateId, '2026-01-01 00:00:00');
```

The answer is true when an Email activity with that template and that contact as recipient exists at or after the given time.

## Uninstall

Uninstalling mailtrail removes the custom field group and with it the two values on every recorded activity. The activities themselves stay, with subject and body.
