# RTCZ Bulk QR Contact Generator

## 1. Purpose

The RTCZ Bulk QR Contact Generator is a standalone PHP and MySQL web application for Right to Care Zambia.

Its purpose is to:

- generate QR codes that open a contact-save screen directly on a smartphone
- reduce repeated manual entry of employee contact details
- support both bulk employee uploads and one-off contact creation
- give administrators a simple way to preview, download, print, and package QR codes

Unlike a URL-based QR system, this version stores the contact data directly inside each QR code using vCard format. Once a QR image has been generated, it can be printed or shared and scanned even when the server or PC is offline.

## 2. Business Logic

### 2.1 What the system does

- An admin logs in to the system.
- The admin either:
  - uploads a transposed CSV file for bulk generation, or
  - fills in a single-contact form
- The system validates the data.
- The system generates a QR code PNG for each valid employee.
- The QR code stores the employee contact details directly.
- The admin can:
  - preview a QR code
  - download a single QR code
  - download all QR codes in one ZIP package
  - open a print-friendly page and save it as PDF

### 2.2 Duplicate handling

This system is configured to prevent silent replacement of staff records.

Current duplicate rules:

- same names are allowed
- duplicate email is blocked
- duplicate phone number is blocked
- duplicate employee number is blocked when that field exists in the database

Important:

- duplicate records are rejected
- they are not automatically updated
- this prevents one employee from accidentally replacing another

## 3. QR Technology Used

This system uses **vCard QR codes**.

That means:

- the contact data is stored directly inside the QR code
- scanning the QR opens the device contact-save prompt
- the QR does not open a public webpage
- the QR does not depend on internet access after generation

### 3.1 Why this matters

Pros:

- works offline
- PC does not need to be on for QR scanning to work
- server does not need to be reachable for QR scanning to work
- good for printed business cards or printed assets

Cons:

- if contact data changes, the QR must be regenerated
- printed QR codes do not update automatically
- there is no live server-managed profile behind the QR

## 4. System Features

### 4.1 Admin authentication

- username and password login
- password hash stored in the database
- session-based authentication
- inactivity timeout

### 4.2 Bulk CSV import

- accepts transposed CSV files
- column A contains field names
- each column after A is one employee
- skips completely empty employee columns
- reports success and failure counts

### 4.3 Single contact entry

- allows one employee to be added without preparing a CSV file
- useful for quick additions and corrections

### 4.4 QR outputs

- single preview
- single download
- ZIP package download
- print/PDF-friendly report

### 4.5 ZIP package contents

The ZIP package contains:

- all QR PNG files in a `qr-codes/` folder
- `employee_list.csv`
- `README.txt`

## 5. Main Admin Workflow

### 5.1 Bulk import workflow

1. Log in
2. Open `Bulk Import CSV`
3. Upload the transposed CSV file
4. Click `Generate QR Codes`
5. Review the results page
6. Preview or download the generated QR codes
7. Optionally download the ZIP package or print the report as PDF

### 5.2 Single entry workflow

1. Log in
2. Open `Add Single Contact`
3. Fill in the required fields
4. Save the contact
5. Review the results page
6. Preview or download the QR code

## 6. Required Contact Fields

The system stores these fields per employee:

- `employee_number` (optional internal identifier)
- `honorific` (optional prefix such as Mr., Mrs., Ms., or Dr.)
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

Important:

- `employee_number` is internal only
- it is not included in the QR payload that is scanned

## 7. CSV Format

The CSV format is transposed.

Example:

```csv
EmployeeNumber,RTCZ001,RTCZ002,RTCZ003
Honorific,Ms.,Mrs.,Mr.
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

Required field rows:

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

Optional field row:

- `EmployeeNumber`
- `Honorific`

## 8. How to Save the Excel File as CSV

1. Open the contact list in Excel.
2. Put field names in column A.
3. Put one employee in each column from column B onward.
4. Click `File`.
5. Click `Save As`.
6. Choose `CSV UTF-8 (Comma delimited) (*.csv)` if available.
7. Save the file.
8. Upload it in the system.

## 9. Routes

### Admin routes

- `/admin/login`
- `/admin/dashboard`
- `/admin/import`
- `/admin/contacts/create`
- `/admin/results`
- `/admin/qrcodes/{id}/preview`
- `/admin/qrcodes/{id}/download`
- `/admin/qrcodes/download-all`
- `/admin/qrcodes/print-report`

## 10. Folder Overview

```text
config/
app/
  Controllers/
  Core/
  Repositories/
  Services/
  Support/
  Views/
database/
docs/
public/
  assets/css/
  downloads/
  media/
scripts/
storage/qrcodes/
```

## 11. Technical Architecture

### 11.1 Core structure

- `public/index.php` is the front controller and router
- `app/bootstrap.php` loads config, autoloading, helpers, and sessions
- `config/database.php` contains DB configuration
- `config/app.php` contains app configuration

### 11.2 Main services

- `TransposedCsvImportService`
  - parses uploaded CSV files
- `VcardService`
  - builds the vCard payload
- `QrCodeService`
  - generates the PNG QR image

### 11.3 Repository layer

- `EmployeeRepository`
  - handles employee storage
  - duplicate checks
  - list and export queries
- `AdminRepository`
  - handles admin lookup and creation

## 12. Local Testing

Typical local URL:

- `http://127.0.0.1:8000`

Common local pages:

- `http://127.0.0.1:8000/admin/login`
- `http://127.0.0.1:8000/admin/import`
- `http://127.0.0.1:8000/admin/contacts/create`
- `http://127.0.0.1:8000/admin/results`

Start the local server:

```powershell
& "C:\wamp64\bin\php\php8.2.26\php.exe" -S 127.0.0.1:8000 -t public
```

## 13. Windows Apache Server Notes

For your Windows Apache setup, the project has been prepared for:

- internal path example:
  - `http://10.7.50.26/Digital_Business_Card_System/public`
- public path example:
  - `http://160.242.60.31/Digital_Business_Card_System/public`

Important:

- this vCard version does not need the public URL for the QR to work after generation
- the public/internal URL matters mainly for accessing the admin app in the browser

## 14. PHP Requirements

Required:

- `pdo_mysql`
- `gd`
- `zip`

Recommended:

- `mbstring`

## 15. Database Setup

Import:

- `database/schema.sql`

Optional sample data:

- `database/seed.sql`

Create the first admin:

```powershell
C:\php\php.exe C:\Apache24\htdocs\Digital_Business_Card_System\scripts\create_admin.php --username=admin --password=RTCZ2025
```

## 16. Troubleshooting

### Problem: QR does not scan

Check:

- the image is clear
- the QR is not compressed too heavily by a messaging app
- the phone camera or QR scanner supports vCard contacts

### Problem: QR works locally but not through server URL

Remember:

- this version stores contact data directly in the QR
- once the PNG is generated, scanning does not depend on the server

### Problem: A new person replaced an existing one

That was earlier caused by update-style duplicate handling. The current version is designed to block duplicate identifiers instead.

### Problem: Employee number or honorific field does not appear

Your database may not yet have the optional `employee_number` and `honorific` columns. The app will still work without them.

To enable it later:

```sql
ALTER TABLE employees
    ADD COLUMN employee_number VARCHAR(50) NULL AFTER id,
    ADD COLUMN honorific VARCHAR(20) NULL AFTER employee_number,
    ADD UNIQUE KEY uq_employees_employee_number (employee_number),
    ADD UNIQUE KEY uq_employees_phone (phone);
```

## 17. Recommended Operational Use

Best use cases for this system:

- printed business cards
- downloadable QR contact packs
- offline sharing of staff contact information

Less ideal if:

- staff contact details change very often
- printed QR cards are expected to remain valid forever without reprint

In those cases, a token URL QR model is more flexible than a direct vCard QR model.

## 18. Summary

This system is best described as:

- a bulk and single-entry QR contact generator
- using vCard QR technology
- optimized for offline scanning
- protected against accidental duplicate replacement
- designed for simple PHP + MySQL + Apache hosting
