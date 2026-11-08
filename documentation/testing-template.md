# COMP.5210 Assignment 3 — CRUD test record

Student: YOUR NAME · ID: YOUR STUDENT ID  
Tester: __________ · Date: __________ · Browser/version: __________  
Local URL: __________ · Live URL: __________ · PHP/MySQL versions: __________

Import a fresh database first. Use a disposable record such as SCP-900001 for CRUD tests so the ten seed records remain available for screenshots. Run the same checks after deployment. Actual Result, Status and Evidence/Screenshot are deliberately blank for assessment evidence.

| Test ID | Feature | Test Procedure | Expected Result | Actual Result | Status | Evidence/Screenshot |
| --- | --- | --- | --- | --- | --- | --- |
| TC01 | Create valid SCP record | Add SCP-900001; fill all required fields; select Safe; submit. | Record created, redirect to list, success message displayed. | | | |
| TC02 | Required validation | Submit create with empty and whitespace-only required fields. | Clear errors; no record inserted; entered values retained. | | | |
| TC03 | Duplicate SCP number | Submit a new record numbered SCP-173; also try lowercase scp-173. | Duplicate prevented with field error. | | | |
| TC04 | Read all | Open the homepage after fresh SQL import. | Ten database records, one Safe, six Euclid and three Keter. | | | |
| TC05 | Read individual | Open View on SCP-173. | Correct number, title, class, description, procedures, image fallback and timestamps. | | | |
| TC06 | Invalid IDs | Open view.php?id=abc, id=0, id=-1, id[]=1, no ID and id=99999999; repeat on edit/delete. | Professional 404 page; no PHP warnings or database changes. | | | |
| TC07 | Update | Edit SCP-900001; change title, class, text and image; save; reopen. | Updated values persist with success message; own number allowed. | | | |
| TC08 | Delete | Click Delete on disposable record; confirm. | Confirmation displayed; POST removes only the selected record; success message. | | | |
| TC09 | Cancel deletion | Click Delete on a seed record; cancel or close modal. | Record remains; no write request. | | | |
| TC10 | Search number | Search SCP-096, then clear. | Matching record shown; clearing restores full list. | | | |
| TC11 | Desktop responsiveness | Inspect 1920×1080, 1366×768 and 1024×768. | Three cards per row, readable forms/navigation, no horizontal overflow. | | | |
| TC12 | Mobile responsiveness | Inspect 390×844 and 375×667; open menu, form and modal. | One card per row; usable menu/controls; no horizontal overflow. | | | |
| TC13 | Tablet responsiveness | Inspect 768×1024. | Two cards per row; forms and navigation usable. | | | |
| TC14 | Search title/class | Search Tickle, then Euclid. | SCP-999 for Tickle; matching Euclid records for class search. | | | |
| TC15 | No search results | Search a random nonexistent phrase. | “No SCP records matched your search.” and a clear-search option. | | | |
| TC16 | Empty archive | In a disposable database only, remove all records and open list. | Empty state with Add SCP action and zero counts. | | | |
| TC17 | Edit validation | Submit an empty title; use SCP-173 on a different record; send unsupported class. | Errors shown, no invalid changes, previous input retained. | | | |
| TC18 | POST-only deletion | Open delete.php?id=1 without confirming; return to list. | Confirmation page appears and record remains. | | | |
| TC19 | CSRF protection | Remove/change csrf field in create, edit and delete requests using developer tools. | Request rejected; no database write; readable message. | | | |
| TC20 | SQL injection | Search with apostrophes, SQL-like strings, % and _; enter SQL-like text in a description. | Search handles input literally; schema intact; text saves as data. | | | |
| TC21 | XSS protection | Put <script>alert(1)</script> in a disposable title/description; view list, record and edit. | Text displayed escaped; no script executes. | | | |
| TC22 | PRG and flash | Create/update/delete a disposable record, then refresh the redirected list. | No repeated write; flash appears once. | | | |
| TC23 | Image fallback | Use blank image, valid local path and an unreachable HTTPS image URL. | Blank/broken image shows local placeholder; valid image loads. | | | |
| TC24 | Invalid image source | Submit javascript: URL, http: URL or ../ path. | Validation rejects source and retains other data. | | | |
| TC25 | Database failure | Temporarily use wrong credentials, then restore them. | Friendly error; no credentials, raw exception or stack trace displayed. | | | |
| TC26 | Keyboard accessibility | Tab through navigation/search/actions/form; Escape closes modal; submit using keyboard. | Visible focus, labelled controls, logical order and usable modal. | | | |
| TC27 | Field lengths | Exceed title/image maximums using developer tools; submit large description. | Server rejects out-of-range values; no truncated save. | | | |
| TC28 | Repeat SQL import | Reimport SQL after changing a seed title. | No duplicate numbers; existing edited title is preserved. | | | |
| TC29 | Folder deployment | Deploy to /scp/ and follow every link/action. | Relative links/assets work from subfolder. | | | |
| TC30 | Protected files | On Apache open config, includes, SQL and documentation URLs. | Access forbidden; public pages continue working. | | | |
| TC31 | Stale deletion | Submit valid POST delete twice for the same disposable ID. | First succeeds; second reports missing record without errors. | | | |
| TC32 | JavaScript unavailable | Disable JS and click Delete. | Separate confirmation page allows cancel or protected POST delete. | | | |

## Evidence checklist

- Homepage with ten records and summary counts.
- Single record with description, containment and dates.
- Successful create, update and delete notifications.
- Required-field and duplicate-number errors.
- Search match and no-match states.
- Delete confirmation and cancellation.
- Desktop, tablet and both requested mobile sizes, including open mobile menu.
- phpMyAdmin structure, unique index and ten imported records.
- CPanel live URL, PHP version and working database-backed page (hide credentials).

Record failed tests honestly, describe fixes and retest. Do not mark unexecuted tests as passed.
