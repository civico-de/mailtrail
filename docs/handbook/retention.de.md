---
title: Aufbewahrung
order: 10
---

# Aufbewahrung

Mailtexte sind personenbezogene Daten, die nur kurz gebraucht werden. mailtrail löscht selbst nie etwas. Es markiert jede Aktivität, die es anlegt, mit dem Feld „Recorded by Mail Trail“. Eine Aufbewahrungsregel kann so genau diese Aktivitäten auswählen und alle anderen unberührt lassen.

## Alte Texte leeren

Jeder Job, der einen APIv4-Aufruf ausführen kann, kann eine solche Regel anwenden, zum Beispiel ein geplanter Job oder ein Cron-Eintrag, der `cv` aufruft. Vorher zeigt dieser Aufruf, welche Aktivitäten die Regel trifft:

```bash
cv api4 Activity.get +s id +w 'mailtrail.recorded=1' +w 'activity_date_time<"-2 years"'
```

Dieser Aufruf leert dann den Text festgehaltener Mails, die älter als zwei Jahre sind, und behält Betreff und Datum:

```bash
cv api4 Activity.update +w 'mailtrail.recorded=1' +w 'activity_date_time<"-2 years"' +v 'details='
```

## Löschen statt leeren

Um die ganze Aktivität zu entfernen, führen Sie `Activity.delete` mit denselben Bedingungen aus. Ein Absender, der mailtrail fragt, ob eine Vorlage schon verschickt wurde (siehe [Referenz](reference.md#wurde-eine-vorlage-kürzlich-verschickt)), sieht die gelöschten Mails dann nicht mehr. Halten Sie das Alter größer als den längsten Zeitraum, auf den ein solcher Absender zurückblickt.
