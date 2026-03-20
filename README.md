# Right to Care Zambia Digital Business Card System

This project is a standalone PHP and MySQL web application for managing digital staff business cards. Each employee gets a random public token, a QR code image, a mobile-friendly public contact page, and a downloadable `.vcf` contact file.

## 1. Architecture

The app uses a simple front-controller pattern:

- `public/index.php` is the single entry point and router.
- `config/` stores app and database configuration.
- `app/Controllers/` handles admin and public routes.
- `app/Repositories/` contains PDO-based database queries.
- `app/Services/` handles QR code generation and vCard creation.
- `app/Views/` contains reusable Bootstrap 5 templates.
- `storage/qrcodes/` stores generated QR code PNG files.
- `database/` contains the MySQL schema and dummy seed data.
- `scripts/create_admin.php` creates the first admin user with a hashed password.

## 2. Folder Structure

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
public/
  assets/css/
scripts/
storage/qrcodes/
```

## 3. Database Schema

Use [database/schema.sql](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/database/schema.sql) to create the database and tables.

## 4. Database Connection

Database settings are in [config/database.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/config/database.php). The app reads from environment variables first, then falls back to local defaults:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

The PDO connection is created in [app/Core/Database.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Core/Database.php).

## 5. Authentication

- Admin login uses sessions and `password_verify()`.
- Password hashes are stored in the `admins` table.
- CSRF protection is applied to login, logout, save, update, and deactivate forms.
- Auth logic lives in [app/Core/Auth.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Core/Auth.php) and [app/Controllers/AuthController.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Controllers/AuthController.php).

Create the first admin user after importing the database:

```bash
php scripts/create_admin.php --username=admin --password=ChangeMe123!
```

## 6. Employee CRUD

- Employees list: `/admin/employees`
- Add employee: `/admin/employees/create`
- Edit employee: `/admin/employees/{id}/edit`
- Deactivate employee: `/admin/employees/{id}/deactivate`
- Download QR code: `/admin/employees/{id}/qr`

The main CRUD controller is [app/Controllers/EmployeeController.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Controllers/EmployeeController.php).

## 7. QR Code Generation

This app uses `chillerlan/php-qrcode` and stores QR PNG files in `storage/qrcodes/`.

- Composer package: `chillerlan/php-qrcode:^5.0`
- QR service: [app/Services/QrCodeService.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Services/QrCodeService.php)
- Public QR URL format: `/c/{token}`

## 8. Public Contact Card

The public employee contact page is rendered from one reusable template:

- View: [app/Views/public/contact.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Views/public/contact.php)
- Route: `/c/{token}`

If the employee is inactive, the page shows a simple inactive message instead of contact actions.

## 9. vCard Download

- Route: `/c/{token}/vcf`
- Service: [app/Services/VCardService.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/app/Services/VCardService.php)

The vCard includes:

- Full name
- Right to Care Zambia as the organisation
- Position
- Work phone
- Work email
- Optional department and location

## 10. Dummy Seed Data

Import [database/seed.sql](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/database/seed.sql) to add 5 sample employees.

## 11. Install and Run on Apache, PHP, and MySQL

1. Create the database and tables:

   ```sql
   SOURCE database/schema.sql;
   SOURCE database/seed.sql;
   ```

2. Install PHP dependencies with Composer:

   ```bash
   composer install
   ```

3. Make sure PHP has these extensions enabled:

   - `pdo_mysql`
   - `gd`
   - `mbstring`

4. Point your Apache virtual host or document root to the `public/` folder.

5. Enable Apache `mod_rewrite` so the routes work with [public/.htaccess](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/public/.htaccess).

6. Set your public app URL for correct QR code links:

   - Windows example:

     ```powershell
     setx APP_URL "http://localhost"
     ```

   - Linux example:

     ```bash
     export APP_URL="https://cards.righttocare.org.zm"
     ```

7. Set your database environment variables or edit [config/database.php](/c:/Users/Inyangu/Desktop/Development/Digital_Business_Card_System/config/database.php).

8. Create the first admin:

   ```bash
   php scripts/create_admin.php --username=admin --password=ChangeMe123!
   ```

9. Open the admin login page:

   ```text
   http://your-domain-or-localhost/admin/login
   ```

## Notes

- Inactive employees are not deleted permanently.
- Public URLs never expose internal employee IDs or employee numbers.
- QR files are regenerated on create or edit when the employee is active.
- Bootstrap 5 is loaded from a CDN for quick setup.
