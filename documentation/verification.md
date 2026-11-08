# Implementation verification

Checked on **2 November 2026 (Asia/Karachi)** using PHP 8.4.27 and MySQL 8.0.39 on Windows. All runtime test data is isolated from the application's deployment database. The existing deadline tracker was preserved.

## Completed PHP/MySQL integration checks

- All 12 PHP files pass `php -l`.
- SQL imports into a fresh MySQL database and reimports without duplicates: exactly ten seed records.
- Homepage retrieves ten cards dynamically, including correct class statistics.
- Search returns correct number/title/class matches; no-match, apostrophe/SQL-like, percent and underscore searches behave safely.
- Missing, malformed, negative, zero, array and nonexistent IDs return professional 404 pages on view, edit and delete.
- Create rejects empty required fields, duplicate numbers, invalid class, unsafe image source and overlong title; retains input.
- Successful create stores the record, redirects and displays a one-time success notification.
- XSS-like title text is escaped in the list, view and prefilled form.
- View displays stored details, timestamps and the local image placeholder.
- Update preloads fields, rejects another record's SCP number, permits its own number and persists changed fields.
- Create, update and delete reject invalid CSRF tokens.
- GET deletion displays confirmation without removing a record; cancelling preserves it.
- Valid POST deletion removes the selected record and reports success; stale deletion reports a missing record safely.
- Refresh does not duplicate inserts and consumes the flash message.
- About page, CSS, JavaScript and local placeholder are available through relative subfolder URLs.
- Stopping the test database returns a friendly HTTP 503 without a raw exception or SQLSTATE in the response.

## Browser verification

Automated headless Chrome checks passed against the real PHP/MySQL application and CDN Bootstrap assets. Generated previews are saved under `tests/evidence/`. The final combined integration/browser run completed successfully and shut down both temporary services.

| Viewport | Columns | Six pages without horizontal overflow | Result |
| --- | --- | --- | --- |
| 1920×1080 | 3 | Database, create, view, edit, delete confirmation, About | Pass |
| 1366×768 | 3 | All six pages | Pass |
| 1024×768 | 3 | All six pages | Pass |
| 768×1024 | 2 | All six pages | Pass |
| 390×844 | 1 | All six pages; mobile menu opens and closes | Pass |
| 375×667 | 1 | All six pages; mobile menu opens and closes | Pass |

Additional browser checks passed: Bootstrap delete modal opening, correct selected ID and cancellation; search submission and filtering; empty form submission with accessible server validation; zero browser JavaScript errors. Desktop database, form and detail previews were visually inspected, together with the mobile database preview. Screenshots are generated development evidence, separate from the blank assessment testing sheet.

## Review performed

Reviewed prepared statements, output escaping, POST deletion, session/CSRF helpers, unique database constraint, validation limits, local image restrictions, shared forms/layout, relative links, Git credential exclusion, responsive breakpoints and deployment instructions. The seed data contains the ten requested SCP identifiers with original concise adapted summaries and individual source attribution.

## Still requires your deployment evidence

- Apache/CPanel deployment and `.htaccess` enforcement (the local test server is PHP's built-in server).
- Hosting credentials, account-prefixed database/user names and the live HTTPS URL.
- Manual keyboard/screen-reader review, physical-device testing and any browser required by the lecturer.
- Completing the blank Actual Result, Status and Evidence columns in `testing-template.md` with your own assessment results.
- Replacing student name/ID placeholders in the footer and About page.

No claim of a live deployment is made. No hosting credentials, repository remote or submission screenshots have been fabricated.
