# ClassiFind — Online Classified Ads Portal (MVP)

A full-stack classified advertisements portal built with **PHP + MySQL (PDO) + Bootstrap 5**,
matching the MVP functional specification: registration/login, ad posting with images,
category browsing, search & filters, contact seller, favorites, reporting, and a complete
admin panel for moderation.

## Features implemented

- **User Management** — Register, login, forgot/reset password, edit profile, change password
- **Advertisement Management** — Post, edit, delete, upload up to 5 images, mark sold, renew, save as draft
- **Categories** — 12 top-level categories with sample subcategories, fully manageable by admin
- **Search & Filters** — Keyword, category/subcategory, city, price range, condition, posted today; sort by latest/oldest/price
- **Ad Details** — Image gallery, seller info, Call / WhatsApp / Email seller, report ad
- **Favorites** — Save/remove ads, dedicated Favorites page
- **My Dashboard** — Tabbed view of Active / Pending / Draft / Sold / Expired / Rejected ads with actions
- **Reporting** — Users can report ads (Spam, Fraud, Duplicate, Offensive, Wrong Category)
- **Admin Panel** — Stats dashboard, user management (suspend/activate/delete/reset password),
  ad moderation (approve/reject/delete), category management, report review

## Tech stack

| Layer    | Technology                     |
|----------|---------------------------------|
| Frontend | HTML, CSS, Bootstrap 5          |
| Backend  | PHP 8 (PDO, prepared statements)|
| Database | MySQL 8                         |
| Hosting  | Any PHP/MySQL host (e.g. Hostinger) |

## Local / server setup

1. **Create the database.** Import `database.sql` (via phpMyAdmin or CLI):
   ```
   mysql -u root -p < database.sql
   ```
   This creates the `classifieds_portal` database, all tables, seed categories/subcategories,
   and a default admin account.

2. **Configure the database connection.** Edit `config/db.php`:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'classifieds_portal';
   $DB_USER = 'your_db_user';
   $DB_PASS = 'your_db_password';
   ```
   If the app is hosted in a subfolder (not domain root), set `BASE_URL` in the same file,
   e.g. `define('BASE_URL', '/classifieds');`.

3. **Set folder permissions.** The `uploads/` folder must be writable by the web server:
   ```
   chmod 755 uploads/
   ```

4. **Point your web server** at this folder's `index.php` (Apache/Nginx with PHP-FPM,
   or Hostinger's shared hosting `public_html`).

5. **Default admin login:**
   - Email: `admin@classifieds.local`
   - Password: `Admin@123`

   ⚠️ **Change this password immediately after first login** (via the admin's profile
   change-password flow, same as a regular user).

## Notes & assumptions

- **Email verification** was marked optional in the spec and is not implemented in this MVP;
  accounts are active immediately after registration.
- **Forgot Password** generates a reset link server-side. Since no email/SMTP service is
  configured, the link is displayed directly on screen instead of being emailed — wire up
  `forgot_password.php` to your mail provider (e.g. PHPMailer + SMTP) to email it in production.
- **WhatsApp contact** uses the public `wa.me` deep link with the seller's mobile number.
- Ads auto-expire 30 days after posting (or renewal); expiry is checked opportunistically on
  page load rather than via a cron job. For production, consider a daily cron hitting
  `expireOldAds()` for consistency.
- All forms are CSRF-protected, passwords are hashed with `password_hash()`/bcrypt, and all
  queries use PDO prepared statements.
- `.htaccess` files restrict access to `config/`, block PHP execution inside `uploads/`, and
  disable directory listing — make sure Apache's `AllowOverride All` is enabled, or replicate
  these rules in your Nginx config.

## Folder structure

```
├── database.sql
├── config/db.php
├── includes/            (functions, header, footer)
├── assets/              (css, js)
├── uploads/             (ad & profile images — writable)
├── index.php            (browse/search homepage)
├── register.php / login.php / logout.php
├── forgot_password.php / reset_password.php
├── profile.php / change_password.php
├── post_ad.php / edit_ad.php / delete_ad.php
├── mark_sold.php / renew_ad.php / publish_ad.php
├── ad_details.php / report_ad.php
├── favorites.php / toggle_favorite.php
├── dashboard.php
├── get_subcategories.php  (AJAX helper)
└── admin/
    ├── index.php (stats dashboard)
    ├── users.php
    ├── ads.php
    ├── categories.php
    ├── reports.php
    └── includes/ (admin header/footer)
```

## What's next (per the spec's future scope)

- Paid/featured listings
- Internal chat between buyer and seller
- Mobile apps
- AI-powered recommendations
- Email notifications (approval, expiry reminders, new messages)
