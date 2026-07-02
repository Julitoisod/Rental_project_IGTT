# How to Run — SND Rental Project

A CodeIgniter 4 rental management system running on XAMPP (Windows).

---

## Prerequisites

| Software | Version | Notes |
|----------|---------|-------|
| XAMPP | Latest | Includes Apache + MySQL |
| PHP | 8.2+ | Bundled with XAMPP |
| Composer | 2.x | PHP dependency manager |
| Git | Any | Optional, for cloning |

---

## 1. Clone / Place the Project

Place the project folder inside your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\RentalProject\
```

---

## 2. Install PHP Dependencies

Open a terminal in the project root and run:

```bash
composer install
```

This installs CodeIgniter 4 framework and dev dependencies (PHPUnit, Faker).

---

## 3. Configure Environment

The `.env` file is already present. Verify these key settings:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/RentalProject/public/'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = rentalproject
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

> **Note:** Update `app.baseURL` to match your actual URL. If you use XAMPP's default setup with the project in `htdocs/RentalProject`, the URL above should work.

---

## 4. Create the Database

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Create a new database named **`rentalproject`** with collation `utf8mb4_general_ci`.

---

## 5. Run Migrations

From the project root, run:

```bash
php spark migrate
```

This creates all required tables: `users`, `categories`, `items`, `rentals`, `notifications`, `reviews`, `wallet_transactions`, `payment_gateways`, `wallet_topup_requests`, and applies all schema updates.

---

## 6. Enable Apache mod_rewrite

Make sure `mod_rewrite` is enabled in XAMPP:

1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find the line: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment it.
4. Restart Apache from the XAMPP Control Panel.

Also ensure `AllowOverride All` is set for the htdocs directory in `httpd.conf`.

---

## 7. Run the Application

From the project root, start the built-in dev server:

```bash
php spark serve
C:\xampp\php\php.exe spark serve
```

> If `php` is not in your PATH, use the full path: `C:\xampp\php\php.exe spark serve`

Then open your browser:

```
http://localhost:8080
```

You will see the **Portal Selection** page with options for:
- **Customer** login/register
- **Owner** login/register
- **Admin** login/register

---

## 8. User Roles & Default Portals

| Role | Login URL | Dashboard |
|------|-----------|-----------|
| Customer | `/login` | `/customer/dashboard` |
| Owner | `/owner/login` | `/owner/dashboard` |
| SND Admin | `/admin/login` | `/admin/dashboard` |

Register a new account through the respective portal to get started.

---

## 9. Email Configuration (Optional)

The system uses Gmail SMTP for email verification and password resets. To configure:

1. Use a Gmail account with **2-Step Verification** enabled.
2. Generate an **App Password** at: Google Account → Security → App Passwords.
3. Update these values in `.env`:

```ini
email.SMTPUser = your-email@gmail.com
email.SMTPPass = 'your-app-password'
```

---

## 10. Folder Permissions

Ensure these directories are writable by the web server:

```
writable/          — logs, cache, sessions
public/uploads/    — user-uploaded images (items, IDs, profiles)
```

On Windows/XAMPP this is usually not an issue, but verify if you encounter upload errors.

---

## Useful Spark Commands

```bash
# Run migrations
php spark migrate

# Rollback all migrations
php spark migrate:rollback

# Start the built-in dev server (alternative to Apache)
php spark serve

# Clear cache
php spark cache:clear

# Run tests
composer test
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 404 on all routes | Enable `mod_rewrite` and set `AllowOverride All` |
| Database connection error | Verify MySQL is running and `rentalproject` DB exists |
| Blank page | Check `writable/logs/` for error details |
| Email not sending | Verify App Password and SMTP settings in `.env` |
| Upload fails | Ensure `public/uploads/` directory exists and is writable |

---

## Project Structure (Key Folders)

```
RentalProject/
├── app/
│   ├── Controllers/    — Route handlers (Auth, Rental, Item, etc.)
│   ├── Models/         — Database models
│   ├── Views/          — Blade-style PHP templates
│   ├── Config/         — App configuration (Routes, Database, Filters)
│   └── Database/
│       └── Migrations/ — Schema versioning (23 migrations)
├── public/             — Web root (index.php, uploads, assets)
├── writable/           — Logs, cache, sessions
├── .env                — Environment config
└── composer.json       — PHP dependencies
```
