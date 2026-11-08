# SCP Foundation CRUD Database

## Project Overview

A complete database-driven PHP application for **COMP.5210 – Web Application Implementation, Assignment 3**. Administrators can maintain fictional SCP Foundation records through a responsive dark interface. It runs on standard Apache/PHP/MySQL shared hosting without a build step or Node server. The existing sibling deadline-tracker project has been preserved.

## Features

- Create, read, update and delete SCP records using PDO prepared statements.
- Search by SCP number, title or object class; clear search and useful empty states.
- Ten initial SCP records with original concise adapted summaries.
- Server validation, unique SCP numbers, retained form values, dismissible flash messages and POST/Redirect/GET.
- Bootstrap deletion modal with a separate confirmation page as a JavaScript-free fallback.
- POST-only deletion, CSRF validation, escaped output, positive integer IDs and safe image paths.
- Responsive Bootstrap grid: one column below 768px, two from 768px, three from 992px.
- Local SVG image placeholder, accessible labels, skip link and visible keyboard focus.
- No login system, as specified by the assessment. Use the host's directory password protection if restricting access to a live demonstration is needed.

## Technologies

PHP 8+, MySQL 5.7+/MariaDB, PDO MySQL, HTML5, CSS3, Bootstrap 5.3.3, vanilla JavaScript. PHP sessions and the standard pdo_mysql extension must be enabled. No npm installation is required. Bootstrap loads from jsDelivr; optional fonts load from Google Fonts, with system-font fallbacks. An internet connection is required for Bootstrap's complete layout and modal/mobile-menu behaviour.

## Final directory structure and file responsibilities

```text
scp-database/
├── index.php                    Database-backed grid, summary counts and search
├── view.php                     Full record and timestamps; missing-record handling
├── create.php                   Shared form in create mode
├── edit.php                     Shared form in update mode
├── delete.php                   GET confirmation / CSRF-protected POST deletion
├── about.php                    Assessment information and SCP attribution
├── README.md
├── ATTRIBUTION.md               Source credits and SCP content licensing
├── .gitignore                   Excludes local credentials and logs
├── .htaccess                    Default page; deny sensitive source downloads
├── config/
│   ├── database.php             Local credentials and PDO connection (Git ignored)
│   ├── database.example.php     Tracked configuration template
│   └── .htaccess                Block direct web access
├── includes/
│   ├── functions.php            Escaping, PDO access, validation, CSRF, flashes
│   ├── record-form.php          Shared create/update processing and form markup
│   ├── header.php               Reusable navbar, document head and flash display
│   ├── footer.php               Reusable assessment/student footer
│   └── .htaccess
├── assets/
│   ├── css/style.css            Custom SCP visual system and responsive rules
│   ├── js/script.js             Delete modal and failed-image fallback
│   └── images/placeholder-scp.svg
├── database/
│   ├── scp_database.sql         Table definition and ten initial records
│   └── .htaccess
├── documentation/
│   ├── testing-template.md      Manual assessment tests and screenshot checklist
│   ├── verification.md          Executed checks and remaining validation
│   └── .htaccess
└── tests/                       Optional development tools; not required to host
    ├── integration.py           Isolated PHP/MySQL CRUD and security checks
    ├── browser.cjs              Optional Playwright responsive/browser checks
    ├── evidence/                Generated screenshots (Git ignored)
    └── .htaccess                Block direct web access
```

## Local Installation

1. Install/start XAMPP, WAMP, MAMP or an equivalent **PHP 8+ / MySQL** environment. Enable `pdo_mysql` and PHP sessions.
2. Copy this `scp-database` folder to your web root, for example `C:\xampp\htdocs\scp-database`.
3. Open phpMyAdmin. Create a database called `scp_database`, using `utf8mb4_unicode_ci`, then **select that database**.
4. Choose **Import**, select `database/scp_database.sql`, and run the import. The table and ten records will be created.
5. Edit `config/database.php`: set `$host`, `$dbname`, `$username` and `$password`. For a fresh Git clone, copy `config/database.example.php` to `config/database.php` first. The supplied local file defaults to `127.0.0.1`, `scp_database`, `root` and an empty password; change these to match your installation.
6. Open `http://localhost/scp-database/`. You should see ten records (one Safe, six Euclid, three Keter).
7. Complete the test sheet before submission and capture your own screenshots.

Alternatively, after setting up MySQL, run `php -S 127.0.0.1:8080 -t scp-database` from the parent folder, then open `http://127.0.0.1:8080/`. PHP's built-in server is for local testing only; Apache `.htaccess` protections apply on Apache hosting.

## Database import details

The SQL file creates `scp_subjects` in the **currently selected database**. `CREATE DATABASE` and `USE` are supplied as optional commented local commands because CPanel commonly requires an account-prefixed database and does not allow applications to create arbitrary database names. For command-line local import, first create/select `scp_database`, then import the SQL into it.

Fresh import creates exactly ten seed records. A repeated import preserves existing records with matching SCP numbers and adds missing seeds; it does not reset edits or delete user data. Back up before importing into an existing database. It cannot migrate an incompatible pre-existing table schema.

## CPanel Deployment

1. Select **PHP 8 or newer**, enable PDO MySQL, and ensure sessions work in your hosting panel.
2. Upload the application contents to `public_html/scp/` (or `public_html/` for a root deployment). Include hidden `.htaccess` files. No build step is required.
3. In **MySQL Databases**, create a database, such as `account_scp_database`, and a dedicated MySQL user with a strong password.
4. Assign that user to the database with the privileges required to import the schema. After import, the application only needs SELECT, INSERT, UPDATE and DELETE.
5. In **phpMyAdmin**, select the actual prefixed database and import `database/scp_database.sql`. Do not uncomment the local `CREATE DATABASE` / `USE` lines on CPanel.
6. Copy the configuration template to `config/database.php` if necessary. Set the host supplied by your provider (often `localhost`), full prefixed database name, full username and password. Keep this file out of Git.
7. Open `https://YOUR-DOMAIN/scp/` and repeat the manual CRUD/search checks. Links and assets use relative paths, so folder deployment works without changing application URLs.
8. Verify that opening `/scp/config/database.example.php`, `/scp/database/scp_database.sql` and `/scp/includes/` is forbidden. The supplied access rules use Apache 2.4 syntax. If the host rejects these directives, ask its support for equivalent access protection.

Use normal host-recommended permissions (typically files 644, directories 755); no world-writable application directories are needed. Database errors are logged server-side while visitors receive a readable message. Do not expose PHP error display on the live host.

## Database design

The single entity is `scp_subjects`. There are no artificial related tables. `scp_number` has a unique index; `id` identifies a record for CRUD operations. Database collation makes duplicate numbers case-insensitive, and the application normalises entered SCP numbers to uppercase.

| Field | Data Type | Constraints | Description |
| --- | --- | --- | --- |
| id | INT | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Internal record identifier |
| scp_number | VARCHAR(20) | UNIQUE, NOT NULL | Public identifier, e.g. SCP-173 |
| title | VARCHAR(150) | NOT NULL | Short anomaly title |
| object_class | VARCHAR(50) | NOT NULL | Safe, Euclid, Keter, Thaumiel, Neutralized, Explained or Pending |
| description | TEXT | NOT NULL | Original concise description |
| containment_procedures | TEXT | NOT NULL | Instructions for maintaining containment |
| image | VARCHAR(255) | NULL | Optional HTTPS image URL or local assets/images path |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP | Last database change |

```text
┌───────────────────────────┐
│ scp_subjects              │
├───────────────────────────┤
│ PK  id                    │
│ UQ  scp_number            │
│     title                 │
│     object_class          │
│     description           │
│     containment_procedures│
│     image (nullable)      │
│     created_at            │
│     updated_at            │
└───────────────────────────┘
```

`TEXT NOT NULL` prevents nulls; PHP rejects empty or whitespace-only fields. Text fields are limited to 60,000 UTF-8 bytes to fit MySQL TEXT storage. No mbstring extension is required. Search treats `%`, `_` and `!` literally. Sort order is numeric by SCP number and then full identifier. Timestamps are displayed in the database server's timezone; they do not claim to be converted to local browser time.

## Validation and image behaviour

SCP numbers use `SCP-` followed by 1–10 digits and an optional 1–5 character uppercase suffix (for example `SCP-001-A`). Uniqueness is checked before save and enforced by the database to handle concurrent submissions. Edits exclude their own ID from the duplicate check. Only supported object classes are accepted.

No file-upload feature is required. Copy permitted local images into `assets/images/` and enter a simple path such as `assets/images/scp-173.jpg`. HTTPS URLs are allowed but the supplied records all use the bundled placeholder so the archive does not depend on remote images. Invalid paths are rejected on save; blank values and images that fail to load show the SVG fallback.

## Git

This folder is ready to place in a Git repository. No remote repository or commit has been created on your behalf. Clone your own repository or download its ZIP, copy the example configuration, import the SQL and follow local installation. From this folder you can run `git init`, `git add .` and commit after reviewing files. `.gitignore` excludes `config/database.php`; never force-add hosting credentials. Check `git status --ignored` before pushing. Make sure the SQL, template configuration, assets, documentation and `.htaccess` files are tracked.

## Testing checklist

Use `documentation/testing-template.md` for test procedures, expected results and blank columns for your actual results, status and evidence. See `documentation/verification.md` for what was checked during implementation.

- Import SQL into an empty database; confirm ten unique SCP numbers.
- List and view database-backed records; test invalid and missing IDs.
- Create a record, reject empty/duplicate/invalid values, and retain form entries.
- Update all fields; retain the current SCP number; reject another record's number.
- Confirm/cancel deletion; verify GET never deletes and missing CSRF is rejected.
- Search number/title/class; clear search; test no match and SQL/XSS-like strings.
- Verify one-time success messages and no repeated writes after refresh.
- Check 1920×1080, 1366×768, 1024×768, 768×1024, 390×844 and 375×667. Test the mobile menu, keyboard, form controls and horizontal overflow.
- Stop MySQL temporarily and check the friendly unavailable message; restore it afterward.

## Automated development checks

For optional repeatable development checks, run:

```powershell
python tests/integration.py --php "C:\path\to\php.exe" --mysqld "C:\path\to\mysqld.exe"
```

This starts a temporary MySQL database on port 13316 and a PHP server on port 18080, imports the seed twice, exercises real HTTP CRUD/security requests, tests a database outage, and shuts down the test services. It refuses occupied ports and does not use your configured database. Python is a development tool only. Optional `--node "C:\path\to\node.exe"` runs `tests/browser.cjs` when Playwright is available to that Node installation (for example through `NODE_PATH`) and Chrome is installed; generated screenshots go to `tests/evidence/`. These tools are not needed on CPanel. The default application configuration file must exist before testing.

## Values to replace manually

| Value | Location |
| --- | --- |
| Database host, name, username, password | `config/database.php` (or SCP_DB_HOST, SCP_DB_NAME, SCP_DB_USER, SCP_DB_PASSWORD environment variables) |
| Student name and student ID | `includes/footer.php` and `about.php` |
| Live URL and repository URL in your report | Your final assessment documentation; app links do not need a live URL |
| Actual results, status and screenshots | `documentation/testing-template.md` |
| Optional reference images | `assets/images/` and record image fields |

## Attribution

SCP concepts and the adapted demonstration records are attributed in `ATTRIBUTION.md` under CC BY-SA 3.0. No article images are included. The About page links to the SCP Wiki and the content license.
