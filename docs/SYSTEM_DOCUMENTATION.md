# Digital Business Card System Technical Documentation

## 1. Overview

This project is a standalone PHP 8.1+ web application for generating digital business card QR codes for Right to Care Zambia staff.

The application supports:

- admin authentication
- bulk import from a transposed CSV format
- single-contact entry
- vCard payload generation
- PNG QR code generation
- single QR preview and download
- ZIP package export
- print-friendly export for PDF generation

The QR codes are data-bearing QR codes, not link QR codes. Each QR embeds a vCard 3.0 payload directly, which means scanning works even when the application server is offline.

## 2. Technology Stack

### 2.1 Backend

- PHP `^8.1`
- MySQL or MariaDB via PDO
- Composer autoloading for vendor packages
- `chillerlan/php-qrcode` for QR image generation

### 2.2 Frontend

- server-rendered PHP views
- Bootstrap 5.3.3 loaded from CDN
- Google Fonts loaded from CDN
- custom styling in `public/assets/css/app.css`

### 2.3 Runtime Extensions

The codebase depends on these PHP extensions in production:

- `pdo_mysql` for database connectivity
- `gd` for PNG QR rendering
- `mbstring` for Composer requirement compatibility
- `zip` for bulk ZIP export

Notes:

- `ext-zip` is used at runtime by the ZIP export flow even though it is not listed in `composer.json`
- the application can run without ZIP export if the extension is unavailable, but the download-all action will fail with a runtime error

## 3. High-Level Architecture

The application uses a lightweight front-controller pattern with manual route dispatching.

```text
Browser
  -> public/index.php
  -> app/bootstrap.php
  -> Controller
  -> Validator / Repository / Service layer
  -> MySQL + storage/qrcodes
  -> PHP view layout
  -> HTML, PNG, ZIP, or printable report response
```

### 3.1 Entry Point

`public/index.php` is the only web entry point. It:

- boots the application
- resolves the normalized request path
- routes requests with explicit `if` checks
- instantiates controllers directly
- renders `404` and `500` views when needed

### 3.2 Bootstrap Sequence

`app/bootstrap.php` performs application initialization:

- defines `BASE_PATH`
- registers a custom autoloader for `App\`
- loads Composer dependencies if `vendor/autoload.php` exists
- loads helper functions
- loads application and database configuration into `$GLOBALS['config']`
- sets the default timezone
- starts the PHP session for web requests

### 3.3 Hosting Model

The helper layer supports installation in a subdirectory such as:

```text
/Digital_Business_Card_System/public
```

`current_path()` strips the script directory from `REQUEST_URI`, which allows the same route definitions to work whether the project is hosted at the domain root or inside a subfolder.

## 4. Directory Structure

```text
app/
  Controllers/
  Core/
  Repositories/
  Services/
  Support/
  Views/
config/
database/
docs/
media/
public/
  assets/css/
  downloads/
  media/
scripts/
storage/
  qrcodes/
vendor/
```

Key directories:

- `app/Controllers/` contains HTTP request handlers
- `app/Core/` contains auth, CSRF, and database access primitives
- `app/Repositories/` contains all direct SQL interaction
- `app/Services/` contains CSV parsing, vCard generation, and QR image generation
- `app/Views/` contains layouts and page templates
- `database/` contains schema and sample seed data
- `scripts/` contains CLI administration utilities
- `storage/qrcodes/` stores generated QR PNG files outside the public web root

## 5. Request Lifecycle

### 5.1 Standard HTML Request

1. The request hits `public/index.php`.
2. `app/bootstrap.php` initializes autoloading, config, helpers, timezone, and session state.
3. The current route is resolved with `current_path()`.
4. A controller action validates authentication if required.
5. The controller coordinates validation, persistence, and service calls.
6. The controller renders a view or returns a direct file response.

### 5.2 File and Asset Responses

QR preview, QR download, and ZIP export bypass HTML rendering and write headers directly:

- preview returns `image/png` inline
- download returns `image/png` as an attachment
- ZIP export returns `application/zip`

### 5.3 Error Handling

The front controller wraps dispatch in a `try/catch` block:

- unmatched routes render `errors/404`
- unhandled exceptions render `errors/500`
- when `APP_DEBUG` is enabled, the raw exception message is shown
- otherwise a generic server error message is shown

## 6. Route Map

| Method | Route | Responsibility |
| --- | --- | --- |
| `GET` | `/` | Redirect to dashboard if logged in, otherwise login |
| `GET` | `/admin/login` | Render login page |
| `POST` | `/admin/login` | Authenticate admin |
| `POST` | `/admin/logout` | Destroy session |
| `POST` | `/admin/session/ping` | Keep active session alive |
| `GET` | `/admin/dashboard` | Render dashboard metrics |
| `GET` | `/admin/import` | Render bulk CSV import page |
| `POST` | `/admin/import` | Process uploaded CSV and generate QR codes |
| `GET` | `/admin/contacts/create` | Render single-contact form |
| `POST` | `/admin/contacts/create` | Create one contact and generate QR |
| `GET` | `/admin/employees/create` | Alias for single-contact form |
| `POST` | `/admin/employees/create` | Alias for single-contact creation |
| `GET` | `/admin/results` | Render stored contacts and latest import summary |
| `GET` | `/admin/employees` | Alias for results page |
| `GET` | `/admin/qrcodes/{id}/preview` | Stream QR PNG inline |
| `GET` | `/admin/qrcodes/{id}/download` | Download QR PNG |
| `GET` | `/admin/qrcodes/download-all` | Generate ZIP package of all contacts |
| `GET` | `/admin/qrcodes/print-report` | Render printable report layout |

## 7. Core Modules

### 7.1 `App\Core\Database`

Responsibilities:

- creates a single PDO connection per request
- configures exception-based PDO error handling
- disables emulated prepared statements

Connection DSN format:

```text
mysql:host={host};port={port};dbname={database};charset={charset}
```

### 7.2 `App\Core\Auth`

Responsibilities:

- authenticates an admin against the `admins` table
- stores the authenticated admin in `$_SESSION['admin']`
- rotates the session ID on login and logout
- tracks inactivity timeout in `$_SESSION['admin_last_activity_at']`

Authentication details:

- passwords are verified with `password_verify()`
- sessions expire after the configured inactivity threshold
- the current implementation defaults to `300` seconds unless overridden by environment variables

### 7.3 `App\Core\Csrf`

Responsibilities:

- creates a session-backed CSRF token
- renders a hidden input helper for forms
- validates tokens on `POST` actions

Failure mode:

- invalid tokens return HTTP `419`
- the response body is a plain-text error message

### 7.4 `App\Support\Validator`

Responsibilities:

- validates login payloads
- validates uploaded CSV files
- normalizes and validates employee/contact payloads

Validation rules include:

- required field enforcement
- string length limits aligned to schema sizes
- basic phone format validation
- email format validation

### 7.5 `App\Repositories\AdminRepository`

Responsibilities:

- lookup admin users by username
- create admin records for CLI bootstrap flows

### 7.6 `App\Repositories\EmployeeRepository`

Responsibilities:

- discover available `employees` table columns
- detect schema drift
- paginate and search contacts
- fetch dashboard metrics
- create employee records
- persist generated payload and QR path metadata

Important implementation detail:

- the public method is named `upsertContact()`
- the current behavior is create-only, not update-or-insert
- duplicate email, phone, and employee number values cause an exception
- records are never automatically updated during import

### 7.7 `App\Services\TransposedCsvImportService`

Responsibilities:

- parse the uploaded CSV using a transposed spreadsheet model
- map spreadsheet labels to internal field names
- strip a UTF-8 BOM when present
- ignore unknown rows
- detect missing required rows
- skip fully empty employee columns

Additional behavior:

- import failures are tagged with spreadsheet-style column labels such as `B`, `C`, and `AA`
- the parser reads rows with `fgetcsv()` and trims all cell values

### 7.8 `App\Services\VcardService`

Responsibilities:

- build a vCard 3.0 payload from an employee record
- include organization website from app configuration
- escape newlines, commas, semicolons, and backslashes correctly

Payload characteristics:

- `employee_number` is intentionally excluded from the QR payload
- `honorific` and `suffix` are included in `FN` and `N` when present
- line endings are CRLF (`\r\n`)

### 7.9 `App\Services\QrCodeService`

Responsibilities:

- render a QR code PNG from the stored vCard payload
- create `storage/qrcodes/` when needed
- remove an old file if the QR filename changes

Filename format:

```text
{first_name}_{last_name}_{id}_qrcode.png
```

Example:

```text
mary_bwalya_12_qrcode.png
```

## 8. Controller Responsibilities

### 8.1 `AuthController`

- renders the login page
- validates login submissions
- authenticates admins
- logs users out
- exposes the session heartbeat endpoint

### 8.2 `DashboardController`

- enforces login
- checks that the current database schema includes required employee columns
- renders summary metrics:
  - total employees
  - total QR-enabled employees
  - distinct organization count
  - five most recently updated employees

### 8.3 `EmployeeController`

Responsibilities:

- render import and single-entry forms
- process CSV import
- create individual contacts
- render the results page
- preview and download QR codes
- export ZIP archives
- render the printable report

Notable controller behavior:

- import summaries are stored once in `$_SESSION['import_summary']` and consumed on the next results-page load
- QR preview, download, ZIP export, and print export regenerate missing or legacy QR assets on demand
- schema compatibility is enforced before import, create, results, preview, ZIP, and print actions

## 9. Data Model

### 9.1 `admins`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `INT UNSIGNED` | Primary key |
| `username` | `VARCHAR(50)` | Unique |
| `password_hash` | `VARCHAR(255)` | Created with `password_hash()` |
| `created_at` | `TIMESTAMP` | Defaults to current timestamp |

### 9.2 `employees`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `INT UNSIGNED` | Primary key |
| `employee_number` | `VARCHAR(50)` nullable | Optional internal identifier, unique when present |
| `honorific` | `VARCHAR(20)` nullable | Optional prefix |
| `suffix` | `VARCHAR(30)` nullable | Optional name suffix |
| `first_name` | `VARCHAR(80)` | Required |
| `last_name` | `VARCHAR(80)` | Required |
| `organization` | `VARCHAR(150)` | Required |
| `title` | `VARCHAR(150)` | Required |
| `phone` | `VARCHAR(50)` | Required, unique |
| `email` | `VARCHAR(150)` | Required, unique |
| `street` | `VARCHAR(150)` | Required |
| `city` | `VARCHAR(120)` | Required |
| `region` | `VARCHAR(120)` | Required |
| `postal_code` | `VARCHAR(30)` | Required |
| `country` | `VARCHAR(120)` | Required |
| `mecard_payload` | `TEXT` nullable | Legacy column name; stores vCard content |
| `qr_code_path` | `VARCHAR(255)` nullable | Relative filesystem path |
| `created_at` | `TIMESTAMP` | Defaults to current timestamp |
| `updated_at` | `TIMESTAMP` | Auto-updated on modification |

### 9.3 Indexes and Constraints

- unique: `employee_number`
- unique: `phone`
- unique: `email`
- index: `(last_name, first_name)`
- index: `organization`
- index: `title`

### 9.4 Schema Compatibility Guard

The application checks for these columns before enabling bulk features:

- `organization`
- `title`
- `street`
- `city`
- `region`
- `postal_code`
- `country`
- `mecard_payload`
- `qr_code_path`
- `updated_at`

If any are missing, the dashboard and employee flows stop with a remediation message instructing the operator to re-import `database/schema.sql`.

## 10. Employee Search Behavior

The results page supports keyword search against these columns:

- `first_name`
- `last_name`
- `CONCAT(first_name, " ", last_name)`
- `organization`
- `title`
- `phone`
- `email`
- `city`
- `country`

Search semantics:

- the query is split on whitespace
- up to six terms are used
- each term must match at least one searchable column
- terms are combined with `AND`
- columns within a term are combined with `OR`

This produces reasonably strict multi-word search behavior. For example:

```text
mary lusaka
```

matches rows where one searchable field matches `mary` and another matches `lusaka`.

## 11. CSV Import Pipeline

### 11.1 Expected Format

The import file is transposed:

- column A contains field labels
- each following column represents one employee

Supported labels:

| CSV Label | Internal Key | Required |
| --- | --- | --- |
| `EmployeeNumber` | `employee_number` | No |
| `Honorific` | `honorific` | No |
| `Suffix` | `suffix` | No |
| `LastName` | `last_name` | Yes |
| `FirstName` | `first_name` | Yes |
| `Organization` | `organization` | Yes |
| `Title` | `title` | Yes |
| `Phone` | `phone` | Yes |
| `Email` | `email` | Yes |
| `Street` | `street` | Yes |
| `City` | `city` | Yes |
| `Region` | `region` | Yes |
| `PostalCode` | `postal_code` | Yes |
| `Country` | `country` | Yes |

Unknown rows are ignored, which lets operators carry extra spreadsheet rows without breaking the import.

### 11.2 Processing Steps

1. Validate upload presence, extension, and size.
2. Parse CSV rows with `fgetcsv()`.
3. Normalize row labels and build a field-row map.
4. Verify all required labels exist.
5. Read one employee payload per non-empty column.
6. Validate required values and field formats.
7. Reject duplicates before insert.
8. Create the employee row.
9. Build the vCard payload.
10. Generate the QR PNG.
11. Save payload and QR path back to the employee record.
12. Build an import summary for the next page load.

### 11.3 Import Outcomes

The import summary tracks:

- processed columns
- skipped empty columns
- success count
- created count
- failure count
- per-column success details
- per-column failure reasons

## 12. QR Generation and Export

### 12.1 QR Regeneration Strategy

Whenever a QR preview, QR download, ZIP export, or print export is requested, the controller verifies that:

- the stored payload begins with `BEGIN:VCARD`
- the referenced QR file exists

If either check fails:

- a fresh vCard payload is rebuilt
- a new PNG is generated
- the database record is updated

This makes the export layer self-healing for older records or missing files.

### 12.2 ZIP Package Contents

The ZIP export contains:

- `qr-codes/` directory with one PNG per employee
- `employee_list.csv` contact register
- `README.txt` package summary

### 12.3 Printable Report

The print report uses a dedicated print layout and renders:

- employee name
- title
- organization
- phone
- email
- full address
- employee number when present
- QR image preview
- QR filename

The operator is expected to use the browser print dialog to save the page as PDF.

## 13. Views and UX Notes

### 13.1 Layouts

- `layouts/admin.php` is the authenticated shell and also hosts the login page
- `layouts/print.php` is the print/PDF shell

### 13.2 Session Heartbeat

The admin layout includes client-side JavaScript that:

- tracks user activity
- schedules automatic logout after the configured inactivity window
- periodically posts to `/admin/session/ping`
- redirects to login when the session is no longer valid

This keeps active users signed in while still enforcing inactivity timeouts.

### 13.3 Results Page

The results view includes:

- latest import summary
- searchable contact directory
- server-side pagination at 10 contacts per page
- inline QR thumbnails
- direct preview and download links

## 14. Configuration

### 14.1 Application Configuration

`config/app.php` supports these environment variables:

| Variable | Default |
| --- | --- |
| `APP_ORGANISATION_WEBSITE` | `https://righttocare-zambia.org/` |
| `APP_URL` | `http://160.242.60.31/Digital_Business_Card_System/public` |
| `APP_TIMEZONE` | `Africa/Lusaka` |
| `APP_SESSION_NAME` | `rtc_zambia_cards_admin` |
| `APP_SESSION_COOKIE_SECURE` | auto-detected from HTTPS or port 443 |
| `APP_SESSION_COOKIE_SAMESITE` | `Lax` |
| `APP_SESSION_TIMEOUT_SECONDS` | `300` |
| `APP_DEBUG` | `false` |

Notes:

- `APP_URL` is used for absolute URL generation only
- QR codes do not depend on `APP_URL` because they embed vCard data directly
- local development should override `APP_URL` to match the local server

### 14.2 Database Configuration

`config/database.php` supports:

| Variable | Default |
| --- | --- |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `rtc_digital_cards` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | empty string |

Charset is fixed to `utf8mb4`.

## 15. Local Development and Deployment

### 15.1 Initial Setup

1. Install dependencies with `composer install`.
2. Import `database/schema.sql`.
3. Optionally import `database/seed.sql`.
4. Create an admin user with `php scripts/create_admin.php`.
5. Serve the `public/` directory through PHP's built-in server or Apache.

### 15.2 Example Local Server Command

```powershell
php -S 127.0.0.1:8000 -t public
```

Recommended local overrides:

```text
APP_URL=http://127.0.0.1:8000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rtc_digital_cards
DB_USERNAME=root
DB_PASSWORD=
```

### 15.3 Apache or Shared Windows Hosting

The application is suitable for classic Apache + PHP hosting where the document root points to `public/`.

For subfolder hosting, ensure:

- the web server exposes `public/`
- `APP_URL` includes the subfolder path
- PHP can write to `storage/qrcodes/`

### 15.4 Offline and Network Considerations

Generated QR images work offline once created, but the admin interface may still rely on network access for:

- Bootstrap CDN
- Google Fonts CDN

If the deployment environment is fully offline, those assets should be vendored or self-hosted.

## 16. CLI Administration Utilities

### 16.1 Create Admin

`scripts/create_admin.php`

Features:

- CLI-only execution
- accepts `--username` and `--password`
- falls back to interactive prompts if arguments are omitted
- prevents duplicate usernames

### 16.2 Update Admin Password

`scripts/update_admin_password.php`

Features:

- updates an existing admin password hash
- requires `--username` and `--password`
- exits with an error if the user does not exist

Example:

```powershell
php scripts/update_admin_password.php --username=admin --password=NewPassword123!
```

## 17. Seed Data

`database/seed.sql` inserts three sample employees with realistic contact data and null QR metadata. QR files are generated lazily the first time those records are previewed, downloaded, or included in exports.

## 18. Security Considerations

Current security controls:

- password hashes stored in the database
- CSRF protection on all authenticated `POST` actions
- session ID regeneration on login and logout
- inactivity-based session expiry
- prepared statements for data writes and filtered reads
- HTML escaping through `e()` in the view layer

Operational cautions:

- there is no role-based access model; all authenticated admins are equivalent
- there is no rate limiting on login
- file upload validation checks extension and upload status, but not MIME type
- Bootstrap and Google Fonts are loaded from external CDNs

## 19. Known Design Decisions and Constraints

- the `mecard_payload` column name is retained for backward compatibility, but it stores vCard content
- duplicate contacts are blocked instead of updated
- QR filenames are derived from name + ID and stored as relative paths
- generated PNG files are not publicly browsable by default because they live under `storage/`
- the results-page import summary is ephemeral and exists for one redirect cycle
- the application uses direct controller instantiation instead of a container or routing framework

## 20. Extension Points

Common enhancement areas:

- add edit and delete workflows for contacts
- replace manual routing with a dedicated router
- move environment loading to `.env` support
- add audit logging for imports and downloads
- self-host frontend assets for air-gapped deployments
- add automated tests around import validation and QR generation
- rename `mecard_payload` to `vcard_payload` through a managed migration

## 21. Troubleshooting Reference

### 21.1 Login Fails Even With Correct Credentials

Check:

- the `admins` table contains the expected username
- the password was created with `password_hash()`
- the session directory is writable by PHP

### 21.2 Import Page Shows a Database Update Error

The `employees` table does not match the expected schema. Re-import `database/schema.sql`, optionally re-import `database/seed.sql`, and recreate the admin account if the database was rebuilt.

### 21.3 ZIP Export Fails

Check:

- the PHP `zip` extension is enabled
- PHP can create temporary files in the system temp directory
- QR files exist or can be regenerated

### 21.4 QR Preview or Download Fails

Check:

- Composer dependencies are installed
- the `gd` extension is enabled
- PHP can create and write to `storage/qrcodes/`

### 21.5 Generated QR Does Not Open a Contact Screen

Check:

- the scanning device supports vCard QR codes
- the PNG has not been recompressed heavily
- the contact data is not malformed or unusually long

## 22. Summary

The Digital Business Card System is a lightweight PHP/MySQL administrative tool for generating offline-capable vCard QR codes from structured staff contact data.

From a maintenance perspective, the most important implementation characteristics are:

- direct route dispatch through a single front controller
- create-only contact persistence with duplicate blocking
- schema-aware bulk features
- lazy QR regeneration
- filesystem-backed QR storage outside the public web root
