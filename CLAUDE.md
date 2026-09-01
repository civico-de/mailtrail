# mailtrail

CiviCRM extension that records every e-mail sent to a contact as an Email
activity (subject + plain-text body + originating message template). The
README is the behavioral spec; keep code and README in lockstep.

## Work in this repository

- **Record after send, never before:** the only entry point is
  `hook_civicrm_postEmailSend`, which core fires after the mailer accepted the
  message. Do not move recording into `alterMailParams` — a mail that failed
  to send must not appear in the contact's history.
- **Text only:** `details` holds the plain-text body (HTML-only mails are
  converted). No HTML, no attachments, no headers.
- **Never throw into the sender:** a recording failure is logged; the mail is
  already out.
- **No own retention code.** `mailtrail.recorded` is the selector; clearing
  old bodies is a [sunset](https://github.com/civico-de/sunset) policy (README
  "Retention"). Do not add a pruning job here.
- `Recorder::sentSince()` is the public marker API for consumers that must not
  remind too often. Keep both custom fields managed (`cleanup: always`) so
  uninstall removes them.

## Verify

Gates are the shared civikitchen layer (`cklint --all`, `ckfmt --check`,
`ckconform`, `phpstan analyse`, `ckphpunit`) — run them in the `.docker/`
stack; CI calls the same pipeline. Headless tests use the mock mailer so the
real hook path (render → send → record) is exercised without SMTP.
