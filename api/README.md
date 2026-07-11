# ClassiFind Mobile API

JSON endpoints that let the Flutter app read/write the **same MySQL database**
as the website, instead of the app's local SQLite copy.

## 1. Install

1. Copy the `api/` folder into your website root (next to `index.php`, `config/`, etc.)
   so it's reachable at `https://yourdomain.com/classifieds/api/...`
   (or whatever your `BASE_URL` in `config/db.php` is).
2. Run the migration once, in phpMyAdmin or the mysql CLI:
   ```sql
   SOURCE api/migration_api_tokens.sql;
   ```
   This adds one new table, `api_tokens`, used to authenticate the mobile app
   (separate from the website's PHP session cookies).
3. That's it - no changes to your existing pages, tables, or uploads folder.
   The API reads/writes `users`, `categories`, `advertisements`,
   `advertisement_images`, `favorites`, and `reports` exactly like the website does.

## 2. How auth works

The app logs in via `POST api/auth.php` (`action=login`), gets back a `token`,
and sends it on every subsequent request as:

```
Authorization: Bearer <token>
```

Tokens last 30 days (see `AD_EXPIRY_DAYS`-independent `api_tokens.ExpiryDate`).
Browsing ads doesn't require a token, same as the website.

## 3. Endpoints

| Method | URL | Auth? | Purpose |
|---|---|---|---|
| POST | `auth.php` (`action=register`) | no | Create account, returns token + user |
| POST | `auth.php` (`action=login`) | no | Returns token + user |
| POST | `auth.php` (`action=logout`) | yes | Invalidates the token |
| POST | `auth.php` (`action=forgot`) | no | Confirms the email exists |
| POST | `auth.php` (`action=reset`) | no | Sets a new password by email |
| GET | `profile.php` | yes | Current user |
| POST | `profile.php` (`action=update`) | yes | Update name/mobile/city/state |
| POST | `profile.php` (`action=change_password`) | yes | Change password |
| GET | `categories.php` | no | All categories (flat, with `parentCategoryId`) |
| GET | `ads.php` | no | Browse Active ads (keyword/category/city/price/condition/sort filters) |
| POST | `ads.php` (multipart) | yes | Create an ad (with up to 5 `images[]`) |
| GET | `ad_detail.php?id=` | no | Single ad (increments view count) |
| POST | `ad_detail.php` (`action=update/delete/mark_sold/renew/publish`) | yes, owner only | Manage one ad |
| GET | `my_ads.php?status=` | yes | Your ads for the Dashboard tabs |
| GET | `favorites.php` | yes | Your favorite ads |
| POST | `favorites.php` (`action=add/remove`) | yes | Toggle a favorite |
| POST | `report_ad.php` | yes | Report an ad |

All responses are JSON: `{"success": true, ...}` or `{"success": false, "error": "..."}`.

## 4. CORS

`_bootstrap.php` sends permissive CORS headers since a mobile app has no
browser origin to restrict. If you later add a web build of the Flutter app,
you can tighten `Access-Control-Allow-Origin` to your specific domain(s).
