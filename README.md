# WayGO

WayGO is a vehicle rental and booking management system built with PHP, MySQL/MariaDB, HTML, CSS, and vanilla JavaScript. It supports public vehicle browsing, customer bookings, favorites, delivery details, fleet management, banner management, booking status updates, dashboards, maps, and email notifications.

## Main Features

- Browse and search available vehicles
- Filter by category, transmission, daily rate, dates, and sorting
- Vehicle details with image galleries and pickup maps
- Customer registration and login
- Customer favorites
- Pickup or delivery booking workflow
- Optional booking add-ons
- Booking status workflow: pending, confirmed, active, completed, cancelled
- Admin fleet management
- Vehicle image upload, gallery reorder, and deletion
- Admin banner management
- Admin dashboard and revenue statistics
- Automated booking confirmation and booking status email notifications
- CSRF protection for forms and AJAX mutations
- Responsive layouts for phones, tablets, and desktop screens
- Automatic address lookup for fleet pickup locations

## Requirements

For local development:

- XAMPP
- Apache
- MySQL or MariaDB
- PHP 8.0 or newer recommended
- PHP extensions: `mysqli`, `mysqlnd`, `fileinfo`, `openssl`, `mbstring`
- A modern browser with JavaScript enabled

The project was developed and tested locally with PHP 8.2 and MariaDB.

## Project Structure

```text
WayGO/
|-- account/       Account settings and password changes
|-- auth/          Login, registration, and logout
|-- banners/       Admin banner management
|-- booking/       Booking creation and management
|-- config/        Database and email configuration
|-- css/           Main stylesheet
|-- dashboard/     Customer/admin dashboard pages
|-- database/      MySQL schema and seed data
|-- favorites/     Customer favorites
|-- fleet/         Admin vehicle management
|-- includes/      Shared helpers, partials, mailer, and PHPMailer
|-- js/            Frontend JavaScript modules
|-- uploads/       Banner and vehicle image uploads
|-- vehicles/      Browse, search, and vehicle detail pages
|-- index.php      Redirects to the vehicle browser
```

## Local XAMPP Setup

### 1. Copy the project

Copy or clone the project into the XAMPP web root:

```text
C:\xampp\htdocs\WayGO
```

If the project folder has another name, the application can still work because internal page links are relative. The database and upload configuration still need to be updated for the target environment.

### 2. Start XAMPP services

Open the XAMPP Control Panel and start:

- Apache
- MySQL

Make sure Apache is using a PHP version that supports the required extensions.

### 3. Create and import the database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

Create a database named:

```text
waygo
```

Then select the database and import:

```text
database/WayGo_schema.sql
```

This schema includes the database tables, sample vehicles, banners, images, and the seeded admin account used for local testing.

The schema creates the following main tables:

- `users`
- `vehicles`
- `vehicle_images`
- `bookings`
- `booking_addons`
- `favorites`
- `banners`

### 4. Configure the local database connection

Open:

```text
config/database.php
```

The default local XAMPP configuration is:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'waygo');
```

If your MySQL installation uses a password or another port, update these values.

### 5. Check upload folders

The application stores uploaded files in:

```text
uploads/banners/
uploads/vehicles/
```

Make sure both directories exist and are writable by PHP. The application uses these folders for banner images, vehicle images, and vehicle gallery images.

### 6. Open the application

Open:

```text
http://localhost/WayGO/
```

The root page redirects to:

```text
http://localhost/WayGO/vehicles/browse.php
```

## Accounts and Roles

### Customer account

Customer accounts are created through:

```text
http://localhost/WayGO/auth/register.php
```

New registrations are assigned the `customer` role automatically. Customers can browse vehicles, save favorites, create bookings, choose pickup or delivery, and view or cancel eligible bookings.

### Admin account

The database schema does not include a shared admin password. Register an account, then promote it through phpMyAdmin:

```text
Email: your-email@example.com
Role: admin
```

Open the admin dashboard after importing the database:

```text
http://localhost/WayGO/dashboard/index.php
```

To promote a different registered account to admin, open phpMyAdmin and run this SQL, replacing the email:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'your-email@example.com';
```

