---
title: mailtrail
---

# mailtrail

mailtrail hält jede E-Mail, die CiviCRM an einen Kontakt verschickt, als E-Mail-Aktivität bei diesem Kontakt fest. Zuwendungsbestätigungen, Veranstaltungsbestätigungen, geplante Erinnerungen und Mails anderer Extensions erscheinen dann im Aktivitäten-Reiter des Kontakts, mit Betreff, Datum und Text. Das Team sieht, was wann hinausging, ohne den Mailserver fragen zu müssen.

## Was festgehalten wird

mailtrail erfasst die Mails, die CiviCRM über seinen eigenen Mailer verschickt. Eine Mail wird erst festgehalten, nachdem der Mailer sie angenommen hat. Eine Mail, die nicht versendet werden konnte, taucht deshalb nie im Verlauf auf.

Für jede Mail legt mailtrail eine Aktivität an:

| Feld | Wert |
|---|---|
| Aktivitätstyp | E-Mail |
| Status | Abgeschlossen |
| Betreff | Der Betreff der Mail |
| Details | Der Text der Mail. Eine Mail, die nur einen HTML-Teil hat, wird in Text umgewandelt. |
| Mit Kontakt | Der Empfänger |
| Hinzugefügt von | Der angemeldete Benutzer, oder der Empfänger, wenn niemand angemeldet ist, etwa bei geplanten Jobs und öffentlichen Formularen |
| Nachrichtenvorlagen-ID | Die Nachrichtenvorlage, aus der die Mail erzeugt wurde, sofern es eine gab |

Anhänge, Kopfzeilen und der HTML-Teil werden nicht gespeichert.

## Was nicht festgehalten wird

- Mails ohne Empfänger-Kontakt, etwa Systembenachrichtigungen und Hinweise an Administratoren.
- Mails aus der CiviCRM-Maske „E-Mail senden“. Die hält CiviCRM bereits selbst als Aktivität fest.
- CiviMail-Mailings. Sie haben ihre eigenen Versandberichte.

## Voraussetzungen

mailtrail braucht keine andere Extension. Um alte Mailtexte zu leeren oder zu löschen, installieren Sie [sunset](https://github.com/civico-de/sunset), siehe [Aufbewahrung](retention.md). Die unterstützten CiviCRM- und PHP-Versionen stehen in der `info.xml` der Extension.

## Installation

Laden Sie das Release-Archiv von der [Release-Seite](https://github.com/civico-de/mailtrail/releases), entpacken Sie es in das Extension-Verzeichnis von CiviCRM und aktivieren Sie es:

```bash
cv ext:enable mailtrail
```

Es gibt keine Einstellungen. Die Erfassung beginnt mit der nächsten Mail.
