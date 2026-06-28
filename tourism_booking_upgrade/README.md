# Tourism Booking Upgrade (Merged)

This merged build combines the cleaner layout, setup flow, and compatibility fixes.

## Run locally
1. Put the folder in `xampp/htdocs/`.
2. Start Apache and MySQL.
3. Open `http://localhost/tourism_booking_upgrade/setup.php`.
4. Then open the homepage or admin panel.

Demo admin: `admin@demo.com` / `admin123`


A premium tourism website built with PHP, Bootstrap 5, MySQL/MariaDB, CSS3, and JavaScript.

## Fast local run
1. Copy the `tourism_booking_upgrade` folder into `xampp/htdocs/`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open `http://localhost/tourism_booking_upgrade/setup.php` once.
4. Open the homepage or admin dashboard.

## Demo admin
- Email: `admin@demo.com`
- Password: `admin123`

## Database
- Database name: `tourism_FM`
- Host: `127.0.0.1`
- User: `root`
- Password: empty

The app creates the database automatically from `database/schema.sql` and seeds it from the JSON backups when needed.

## Included
- Premium cinematic homepage
- Dynamic destinations, world explorer, and tour pages
- Booking flow with approval/payment workflow
- Admin booking actions: approve, reject, contacted, mark paid
- MySQL tables for users, packages, bookings, payment transactions, notifications, and audit logs
- Image upload support
- CSRF protection on write actions

## Notes
- `setup.php` is the easiest first-run entry point.
- `database/install.php` is still available if you prefer the installer page.
- `data/*.json` are kept as seed/backup files.
