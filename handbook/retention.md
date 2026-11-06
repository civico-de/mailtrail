---
title: Retention
order: 10
---

# Retention

Mail bodies are personal data with a short useful life. mailtrail never deletes anything itself. It marks every activity it creates with the field "Recorded by Mail Trail", so a retention policy can select exactly these activities and leave all others alone.

## Clear old bodies with sunset

The [sunset](https://github.com/civico-de/sunset) extension applies such policies. This policy empties the text of recorded mails after two years and keeps subject and date:

| Field | Value |
|---|---|
| `target_entity` | `Activity` |
| `target_filter` | `[["mailtrail.recorded","=",true]]` |
| `age_field` | `activity_date_time` |
| `retention_days` | `730` |
| `action` | `clear_fields` |
| `action_params` | `{"fields":["details"]}` |

Preview the policy with sunset's dry run before you switch it live.

## Delete instead of clearing

To remove the whole activity, use the action `delete` instead. A sender that asks mailtrail whether a template was already sent (see [Reference](reference.md#was-a-template-sent-recently)) then no longer sees the deleted mails. Keep `retention_days` above the longest interval such a sender looks back.
