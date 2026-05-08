# RTCZ Bulk QR Contact Generator

This project is a standalone PHP and MySQL web application for Right to Care Zambia. An admin can upload a transposed CSV file or enter one contact manually, and the system builds a vCard payload for each employee and generates a QR code PNG that opens the contact save screen directly on a phone.

For the technical reference and maintainer guide, see [SYSTEM_DOCUMENTATION.md](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/docs/SYSTEM_DOCUMENTATION.md).

## 1. Updated Architecture

The app uses a simple front-controller structure that is easy to test locally first:

- `public/index.php` is the only web entry point and handles routing.
- `config/` stores the local-first app and database defaults.
- `app/Controllers/` contains admin login, dashboard, bulk import, single entry, results, preview, download, and ZIP/PDF export actions.
- `app/Repositories/` contains PDO queries for admins and imported employee records.
- `app/Services/TransposedCsvImportService.php` parses the transposed CSV layout.
- `app/Services/VcardService.php` builds the vCard QR payload.
- `app/Services/QrCodeService.php` generates PNG QR files with `chillerlan/php-qrcode`.
- `app/Views/` contains Bootstrap 5 admin screens for login, dashboard, import, and results.
- `storage/qrcodes/` stores generated QR code PNG files.
- `public/downloads/sample_contacts_transposed.csv` provides a ready-made CSV example for local testing.

## 2. Updated Database Schema

Use [schema.sql](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/database/schema.sql) to create the new structure.

- `admins`
  - `id`
  - `username`
  - `password_hash`
  - `created_at`
- `employees`
- `id`
- `employee_number`
- `honorific`
- `suffix`
- `first_name`
  - `last_name`
  - `organization`
  - `title`
  - `phone`
  - `email`
  - `street`
  - `city`
  - `region`
  - `postal_code`
  - `country`
  - `mecard_payload` (legacy-named payload storage column)
  - `qr_code_path`
  - `created_at`
  - `updated_at`

Duplicate handling now follows these rules:

- names can repeat
- `email` is unique
- `phone` is unique
- `employee_number` is optional and unique when present

The QR payload does not include `employee_number`, so it stays internal even when saved in the database. The optional `honorific` and `suffix` values can be included in the QR so phones that support structured name fields save them more cleanly.

## 3. CSV Import Parser

The parser lives in [TransposedCsvImportService.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Services/TransposedCsvImportService.php).

It does four main things:

1. Reads the uploaded CSV row by row with `fgetcsv()`.
2. Treats column A as field names and each later column as one employee.
3. Validates that all required field rows exist.
4. Skips fully empty employee columns safely.

Required CSV field rows:

- `LastName`
- `FirstName`
- `Organization`
- `Title`
- `Phone`
- `Email`
- `Street`
- `City`
- `Region`
- `PostalCode`
- `Country`

Optional CSV field row:

- `EmployeeNumber`
- `Honorific`
- `Suffix`

## 4. vCard Builder

[VcardService.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Services/VcardService.php) builds the QR payload in vCard format. The QR code stores contact data directly, not a URL.

Example payload shape:

```text
BEGIN:VCARD
VERSION:3.0
FN:Ms. Mary Bwalya
N:Bwalya;Mary;;Ms.;
ORG:Right to Care Zambia
TITLE:Program Manager
TEL;TYPE=CELL,VOICE:+260 977 123 100
EMAIL;TYPE=INTERNET:mary.bwalya@righttocare.org.zm
ADR;TYPE=WORK:;;Plot 12 Addis Ababa Drive;Lusaka;Lusaka Province;10101;Zambia
URL:https://righttocare-zambia.org/
END:VCARD
```

## 5. QR Generation Service

[QrCodeService.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Services/QrCodeService.php) uses `chillerlan/php-qrcode` to generate PNG files in [storage/qrcodes](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/storage/qrcodes).

The file naming format is:

```text
first_last_id_qrcode.png
```

Example:

```text
paul_chinyemba_12_qrcode.png
```

## 6. Bulk Import Page

The upload screen is [import.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Views/employees/import.php).

Routes:

- `GET /admin/import`
- `POST /admin/import`

Single-entry routes:

- `GET /admin/contacts/create`
- `POST /admin/contacts/create`

It validates the uploaded CSV file, parses every employee column, validates required fields, and generates QR PNGs only for valid employee records.

## 7. Results Page

The results screen is [index.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Views/employees/index.php).

Routes:

- `GET /admin/results`
- `GET /admin/employees` as a compatibility alias

It shows:

- the latest import summary
- success and failure counts
- the employee result table
- QR preview and download actions
- a full searchable list of stored contacts

## 8. Single QR Download Action

Routes:

- `GET /admin/qrcodes/{id}/preview`
- `GET /admin/qrcodes/{id}/download`

These actions are handled in [EmployeeController.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Controllers/EmployeeController.php). Preview streams the PNG inline, while download forces a file download.

## 9. ZIP Bulk Download Action

Route:

- `GET /admin/qrcodes/download-all`

This uses PHP `ZipArchive` to package all generated QR PNG files into a single ZIP download.

## 10. Sample CSV Data

A ready-to-use sample file is available at [sample_contacts_transposed.csv](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/public/downloads/sample_contacts_transposed.csv).

Sample data:

```csv
EmployeeNumber,RTCZ001,RTCZ002,RTCZ003
Honorific,Ms.,Mrs.,Mr.
Suffix,,,III
LastName,Bwalya,Malama,Nyangu
FirstName,Mary,Beatrice,Ithamar
Organization,Right to Care Zambia,Right to Care Zambia,Right to Care Zambia
Title,Program Manager,Admin Officer,Applications Developer
Phone,+260 977 123 100,0972448338,0979511258
Email,mary.bwalya@righttocare.org.zm,beatrice@righttocare-zambia.org,ithamar.nyangu@righttocare-zambia.org
Street,Plot 12 Addis Ababa Drive,Plot 12 Addis Ababa Drive,Plot 12 Addis Ababa Drive
City,Lusaka,Lusaka,Lusaka
Region,Lusaka Province,Lusaka Province,Lusaka Province
PostalCode,10101,10101,10101
Country,Zambia,Zambia,Zambia
```

To save an Excel sheet correctly:

1. Put the field names in column A.
2. Put each employee in a new column starting from column B.
3. In Excel, choose `File` > `Save As`.
4. Pick `CSV UTF-8 (Comma delimited) (*.csv)` if available.
5. Upload the saved CSV file from the import page.

## 11. Local Testing Setup

This project is now configured for local-first testing.

Default local values:

- `APP_URL=http://127.0.0.1:8000`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=rtc_digital_cards`
- `DB_USERNAME=root`
- `DB_PASSWORD=` (blank by default for local WAMP-style testing)

Run locally from the project root:

```powershell
& "C:\wamp64\bin\php\php8.2.26\php.exe" -S 127.0.0.1:8000 -t public
```

Then open:

- `http://127.0.0.1:8000/admin/login`
- `http://127.0.0.1:8000/admin/import`
- `http://127.0.0.1:8000/admin/contacts/create`
- `http://127.0.0.1:8000/admin/results`

## 12. Install Notes

1. Import [schema.sql](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/database/schema.sql).
2. Optionally import [seed.sql](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/database/seed.sql) for sample contacts.
3. Install dependencies:

   ```bash
   composer install
   ```

4. Make sure PHP has these extensions enabled:
   - `pdo_mysql`
   - `gd`
   - `zip`
   - `mbstring` is recommended, though the app falls back safely for string length checks
5. Create the first admin:

   ```bash
   php scripts/create_admin.php --username=admin --password=RTCZ2025
   ```

6. After local testing passes, override `APP_URL` and DB settings for the server environment.

## 13. Optional Employee Number Upgrade

If your current local database was created before the `employee_number`, `honorific`, and `suffix` fields were added, the app will still work. To enable them later without rebuilding the whole database, run:

```sql
ALTER TABLE employees
    ADD COLUMN employee_number VARCHAR(50) NULL AFTER id,
    ADD COLUMN honorific VARCHAR(20) NULL AFTER employee_number,
    ADD COLUMN suffix VARCHAR(30) NULL AFTER honorific,
    ADD UNIQUE KEY uq_employees_employee_number (employee_number),
    ADD UNIQUE KEY uq_employees_phone (phone);
```
