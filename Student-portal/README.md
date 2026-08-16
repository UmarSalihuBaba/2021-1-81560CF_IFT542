# Student Registration Web Application — Baseline (Task 1 lab build)

**Status:** This is the BASELINE build for the IFT542 assignment. It intentionally
contains the vulnerabilities identified in the Task 1 STRIDE threat model
(SQL injection in `login.php`, no CSRF token in `courses.php`, unrestricted
URL import in `upload.php`, trust of client-supplied `user_id` in `profile.php`,
verbose login errors). These will be fixed in Task 2 and Task 3 with clearly
labeled "before/after" files — do not deploy this baseline outside an isolated lab.

## Requirements
- XAMPP (Apache + MySQL 8.0 + PHP 8.1) or equivalent LAMP stack
- Browser

## Setup
1. Copy the `studentreg/` folder into `C:/xampp/htdocs/studentreg/`
2. Start Apache and MySQL from the XAMPP control panel.
3. Import the database:
   ```
   mysql -u root -p < db/schema.sql
   mysql -u root -p < db/seed.sql
   mysql -u root -p studentreg_db < db/schema_update_task2.sql
   mysql -u root -p studentreg_db < db/schema_update_task3.sql
   ```
4. Edit `config.php` if your MySQL user/password differ from the XAMPP defaults.
5. Visit `http://localhost/studentreg/`

## Test Accounts (fictitious data only)
| Email | Password | Role |
|---|---|---|
| amina.bello@example.test | Password@123 | student |
| john.okafor@example.test | Password@123 | student |
| admin@example.test | Password@123 | admin |

## Project Structure
```
studentreg/
  config.php                   - DB connection settings (placeholders)
  .htaccess                    - Apache hardening (Task 3 misconfiguration fixes)
  includes/db.php               - mysqli connection helper
  includes/auth.php             - session + auth + audit-log helpers
  includes/login_security.php   - account lockout + IP rate limiting (Task 2)
  includes/csrf.php             - CSRF token generation/verification (Task 3)
  includes/url_safety.php        - SSRF allowlist + private-IP blocking (Task 3)
  includes/security_headers.php - CSP + security headers (Task 3)
  login.php                    - SECURE login (Task 2 fix applied)
  login_vulnerable.php          - BASELINE, kept for before/after evidence
  register.php                 - account creation (password_hash + prepared stmts)
  dashboard.php                - post-login landing page
  profile.php                  - SECURE profile update (XSS/CSRF/access-control fixed, Task 3)
  profile_vulnerable.php        - BASELINE, kept for before/after evidence
  courses.php                  - SECURE course registration (CSRF fixed, Task 3)
  courses_vulnerable.php        - BASELINE, kept for before/after evidence
  upload.php                   - SECURE document upload + URL import (SSRF fixed, Task 3)
  upload_vulnerable.php         - BASELINE, kept for before/after evidence
  admin/                        - admin panel (courses, users, audit log) — CSRF-protected
  db/schema.sql                 - table definitions
  db/seed.sql                   - fictitious seed data
  db/schema_update_task2.sql    - adds login_attempts table
  db/schema_update_task3.sql    - adds users.bio column
  tests/task2_tests.php         - automated auth/SQLi test suite
```

## Running the Task 2 test suite
With Apache/MySQL running and the DB seeded/migrated:
```
php tests/task2_tests.php
```
This exercises valid login, invalid login, account enumeration resistance,
SQL injection payloads, bcrypt hash format, and account lockout — see
`task2_secure_auth_report.md` for how to interpret the output.

## Manually verifying Task 3 fixes
- **XSS:** on `profile.php`, save a bio containing `<script>alert(1)</script>` —
  it should render as visible text, not execute. Compare with
  `profile_vulnerable.php`, where the same input executes.
- **CSRF:** try resubmitting a course-registration form with the
  `csrf_token` field removed or altered — it should be rejected with a 403.
- **SSRF:** try importing `http://127.0.0.1/` or `http://169.254.169.254/` via
  the upload page's URL-import field — both should be rejected as
  "not permitted." Compare with `upload_vulnerable.php`, which would fetch them.
- **Headers/misconfiguration:** inspect response headers in the browser dev
  tools for `Content-Security-Policy`, `X-Frame-Options`, and confirm
  `X-Powered-By` is absent.
