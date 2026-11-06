---
title: Referenz
order: 20
---

# Referenz

## Benutzerdefinierte Felder

mailtrail ergänzt Aktivitäten um die Feldgruppe „Mail Trail“ (API-Name `mailtrail`). Sie erscheint nicht in öffentlichen Formularen und ist in der Aktivitätsansicht eingeklappt.

| Feld | API-Name | Typ | Inhalt |
|---|---|---|---|
| Recorded by Mail Trail | `mailtrail.recorded` | Ja/Nein | Immer „Ja“ bei Aktivitäten, die mailtrail angelegt hat. Das Auswahlkriterium für Aufbewahrungsregeln und Suchen. |
| Message template ID | `mailtrail.template_id` | Zahl | Die ID der Nachrichtenvorlage, aus der die Mail erzeugt wurde. Leer bei Mails ohne Vorlage. |

Beide Felder sind durchsuchbar. Eine SearchKit-Suche kann so etwa alle Bestätigungen auflisten, die mit einer bestimmten Vorlage verschickt wurden.

Trug die Mail eine Zuwendung in ihrem Token-Kontext, wie es Zuwendungsbestätigungen tun, ist diese Zuwendung der Quelldatensatz der Aktivität.

## Wurde eine Vorlage kürzlich verschickt?

Code, der Erinnerungen verschickt, kann fragen, ob eine Vorlage schon an einen Kontakt ging, ohne eigene Buchführung:

```php
$alreadySent = \Civi\Mailtrail\Recorder::sentSince($contactId, $templateId, '2026-01-01 00:00:00');
```

Die Antwort ist wahr, wenn ab dem angegebenen Zeitpunkt eine E-Mail-Aktivität mit dieser Vorlage und diesem Kontakt als Empfänger existiert.

## Deinstallation

Beim Deinstallieren entfernt mailtrail die Feldgruppe und damit die beiden Werte an jeder festgehaltenen Aktivität. Die Aktivitäten selbst bleiben mit Betreff und Text erhalten.
