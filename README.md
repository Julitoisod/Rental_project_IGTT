# SND Rental - Equipment Rental Management System

A CodeIgniter 4 based rental management system for equipment rental operations.

## What is SND Rental?

SND Rental is a web-based equipment rental management system that allows users to list, browse, and rent equipment. It includes features for user authentication, item management, booking system, wallet integration, and payment gateway support.

## Server Requirements

- PHP 8.2 or higher
- MySQL 5.7+ (via XAMPP)
- Composer (for dependency management)
- XAMPP (Apache + MySQL)

## Installation & Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy the `.env` file if not already present and ensure database settings are correct:

```
database.default.hostname = localhost
database.default.database = rentalproject
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

### 3. Start XAMPP

1. Open XAMPP Control Panel
2. Start **Apache** module
3. Start **MySQL** module

Or via command line:
```
c:\xampp\xampp_start.exe
```

### 4. Create Database

1. Open phpMyAdmin at: http://localhost/phpmyadmin
2. Create a new database named: `rentalproject`
3. Select collation: `utf8mb4_general_ci`

### 5. Run Migrations

Run the database migrations to create all required tables:

```bash
php spark migrate
```

### 6. (Optional) Seed Database

If you want to populate initial data:

```bash
php spark db:seed
```

## How to Run the System

### Method 1: Via XAMPP (Recommended)

1. Ensure XAMPP Apache and MySQL are running
2. The project is located at: `c:\xampp\htdocs\RentalProject`
3. Access the application at: **http://localhost/RentalProject/public**

### Method 2: Via PHP Built-in Server

Run from the project root directory:

```bash
php spark serve
```

Then access at: **http://localhost:8080**

## Default Login (if seeded)

- **Admin**: admin@sndrental.com / admin123
- **User**: user@sndrental.com / user123

## Features

- User Authentication (Registration, Login, Password Reset)
- Equipment Listing and Management
- Booking System with Status Tracking
- Wallet System for Payments
- Payment Gateway Integration
- Review and Rating System
- Email Notifications via SMTP (Gmail)

## Troubleshooting

### Database Connection Issues
- Ensure MySQL is running in XAMPP
- Verify database name matches in `.env`
- Check username/password (default XAMPP: root/empty)

### Port Conflicts
- If port 80 is in use, XAMPP may not start Apache
- Check XAMPP Control Panel for port conflicts
- Modify Apache port in `c:\xampp\apache\conf\httpd.conf`

### Email Not Working
- Gmail requires App Password, not regular password
- Generate App Password at: Google Account → Security → 2-Step Verification → App passwords
- Update `email.SMTPPass` in `.env`

## Project Structure

```
RentalProject/
├── app/                  # Application source code
│   ├── Config/          # Configuration files
│   ├── Controllers/     # Controller classes
│   ├── Database/        # Migrations and Seeds
│   ├── Models/          # Data models
│   └── Views/           # View templates
├── public/              # Web root (index.php)
├── writable/            # Writable directories (logs, cache)
├── vendor/              # Composer dependencies
├── spark                # CLI tool
└── .env                 # Environment configuration
```

## License

See LICENSE file for details.