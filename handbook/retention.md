---
title: Retention
order: 10
---

# Retention

Mail bodies are personal data with a short useful life. mailtrail never deletes anything itself. It marks every activity it creates with the field "Recorded by Mail Trail", so a retention policy can select exactly these activities and leave all others alone.

## Clear old bodies

Any job that can run an APIv4 call can apply such a policy, for example a scheduled job or a cron entry that calls `cv`. Preview which activities a policy would touch:

```bash
cv api4 Activity.get +s id +w 'mailtrail.recorded=1' +w 'activity_date_time<"-2 years"'
```

This call then empties the text of recorded mails older than two years and keeps subject and date:

```bash
cv api4 Activity.update +w 'mailtrail.recorded=1' +w 'activity_date_time<"-2 years"' +v 'details='
```

## Delete instead of clearing

To remove the whole activity, run `Activity.delete` with the same conditions. A sender that asks mailtrail whether a template was already sent (see [Reference](reference.md#was-a-template-sent-recently)) then no longer sees the deleted mails. Keep the age above the longest interval such a sender looks back.
