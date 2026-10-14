# Shortener

A tiny URL shortener in plain PHP 8.1+ with SQLite. No framework.

## Run

```bash
php -S localhost:8000 -t public
```

Open http://localhost:8000. The SQLite file is created in `data/` on the first request.

If 8000 is taken, use a different port (e.g., `php - S localhost:8001 -t public) or kill the process using it.

## Layout

```
public/index.php         front controller and routes
public/.htaccess         Apache rewrite to index.php
src/Database.php         PDO connection, applies schema.sql
src/LinkRepository.php   all SQL lives here
src/Shortener.php        URL validation and code generation
views/home.php           the single page
schema.sql               links table
config.php               db path, code length
```

## Routes

| Method | Path             | What it does                          |
|--------|------------------|---------------------------------------|
| GET    | `/`              | Form and recent links                 |
| POST   | `/shorten`       | Validates, stores, redirects to `/`   |
| GET    | `/{code}`        | Counts a click, 302 to the target URL |
| GET    | `/{code}/stats`  | JSON with clicks and created date     |

## Next steps

1. Custom aliases (`/my-link`) with a reserved-words list.
2. A `clicks` table (timestamp, referrer, user agent) instead of a counter.
3. Link expiry and a cleanup script.
4. Rate limiting per IP on `POST /shorten`.
5. Tests with PHPUnit against an in-memory SQLite database.
