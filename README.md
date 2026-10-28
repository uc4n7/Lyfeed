# LyfeFeed - Social Networking Web Application

Full-stack PHP (MVC) + MySQL + Bootstrap 5 project.

## Quick start
1. Copy this folder to your web root (e.g. `C:\xampp\htdocs\social_app`).
2. Import `sql/social_app.sql` in phpMyAdmin (creates the `social_app` database, 4 tables, sample data).
3. Edit `config/database.php` if your MySQL credentials differ (default: `root`, no password).
4. Make `public/assets/uploads/` writable.
5. Open `http://localhost/social_app/public/`
   (or run `php -S localhost:8000 -t public` and open `http://localhost:8000`).

Sample accounts (password `password123`): `maria_santos`, `juan_dela_cruz`, `ana_reyes`, `carlos_mendoza`.

## Structure
- `app/models` - PDO data access (User, Post, Comment, Like)
- `app/controllers` - Auth, Profile, Post, Comment, Like, Feed, Search
- `app/views` - layouts, pages, partials (no DB queries)
- `app/core` - Router, base Controller, Database, helpers
- `public` - front controller (`index.php`) and assets
- `sql/social_app.sql` - schema + sample data
- `docs/` - ERD (`erd.png`, `erd.dot`) and `MiniSocial_Documentation.docx`

## Security
Prepared statements, `htmlspecialchars` output escaping, `password_hash`, session regeneration, CSRF tokens on all POSTs, ownership checks on edit/delete, validated image uploads.
