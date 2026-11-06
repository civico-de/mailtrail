---
title: Aufbewahrung
order: 10
---

# Aufbewahrung

Mailtexte sind personenbezogene Daten, die nur kurz gebraucht werden. mailtrail löscht selbst nie etwas. Es markiert jede Aktivität, die es anlegt, mit dem Feld „Recorded by Mail Trail“. Eine Aufbewahrungsregel kann so genau diese Aktivitäten auswählen und alle anderen unberührt lassen.

## Alte Texte mit sunset leeren

Die Extension [sunset](https://github.com/civico-de/sunset) wendet solche Regeln an. Diese Regel leert nach zwei Jahren den Text festgehaltener Mails und behält Betreff und Datum:

| Feld | Wert |
|---|---|
| `target_entity` | `Activity` |
| `target_filter` | `[["mailtrail.recorded","=",true]]` |
| `age_field` | `activity_date_time` |
| `retention_days` | `730` |
| `action` | `clear_fields` |
| `action_params` | `{"fields":["details"]}` |

Prüfen Sie die Regel mit dem Probelauf von sunset, bevor Sie sie scharf schalten.

## Löschen statt leeren

Um die ganze Aktivität zu entfernen, nehmen Sie stattdessen die Aktion `delete`. Ein Absender, der mailtrail fragt, ob eine Vorlage schon verschickt wurde (siehe [Referenz](reference.md)), sieht die gelöschten Mails dann nicht mehr. Halten Sie `retention_days` größer als den längsten Zeitraum, auf den ein solcher Absender zurückblickt.
