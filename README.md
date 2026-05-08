# Henco

Order management web app for field workers — manage clients, products, carts and orders, with email/PDF order delivery.

Stack: PHP 8 + MySQL, Bootstrap 5, jQuery. PHPMailer / Mailjet (vendored) for transactional email and dompdf for order PDFs.

## Quick start

1. **Clone**
   ```sh
   git clone https://github.com/hugocenturio/henco.git
   cd henco
   ```

2. **Configure** — copy `.env.example` to `.env` and fill in DB / Mailjet credentials.
   ```sh
   cp .env.example .env
   ```
   Or run the wizard at `/setup` after pointing your web server at the project — it will write `.env` for you.

3. **Database** — create the MySQL database matching `DB_NAME` and import `database/schema.sql`. The setup wizard will also do this on first run.

4. **Serve** — point Apache at the project root with `mod_rewrite` enabled (`.htaccess` is included), or run locally:
   ```sh
   php -S localhost:8000
   ```
   Visit `http://localhost:8000`.

## Architecture

Phase 3 introduced a thin MVC layer on top of the legacy code base. Every request goes through the front controller `index.php`, which dispatches via the router to a controller action, which prepares data and renders a view inside a layout.

```
.htaccess          URL rewrite — assets pass through, everything else hits index.php
index.php          Front controller (loads bootstrap, dispatches via router)
bootstrap.php      Autoloader, env loader, helpers, security headers
.env               Real credentials (gitignored)
.env.example       Template for the above

config/
  config.php       Legacy fallback config (gitignored). New installs use .env only
  config.example.php
  security.php     Hardened session + security headers + CSP
  routes.php       URL → controller@action map
  .htaccess        Denies direct HTTP access to credentials

app/
  Core/
    Autoloader.php   Tiny PSR-4 autoloader for the App\ namespace
    Env.php          Reads .env into getenv/$_ENV
    Router.php       Pattern-matching dispatcher
    Controller.php   Base controller (db, view, redirect, json, requireAuth)
    View.php         Layout + view rendering with output buffering
    Database.php     Singleton mysqli wrapper
    Request.php      Wraps $_SERVER, $_GET, $_POST, $_FILES + base-path detection
  Controllers/       AuthController, DashboardController, ProductController, ...
  Views/
    layouts/         main.php (admin chrome), auth.php (login/activate)
    partials/        sidebar.php, topbar.php, flash.php
    auth/  dashboard/  orders/  products/  categories/  clients/  users/
    settings/  setup/  profile/
  helpers.php        e(), csrf_*, url(), asset(), flash_pull(), translate()

database/schema.sql
locales/             en.json, pt.json (i18n)
css/  scss/  js/  images/  _icons/  plugins/  uploads/   served directly
vendor/              composer dependencies (committed for now; see roadmap)
```

## Routes (highlights)

| Method | URL | Action |
|--------|-----|--------|
| GET / POST | `/login` | AuthController@login |
| GET | `/logout` | AuthController@logout |
| GET | `/activate?code=...` | AuthController@activate |
| ANY | `/setup` | SetupController@index |
| ANY | `/dashboard` | DashboardController@index |
| ANY | `/profile` | ProfileController@index |
| GET | `/my-orders` | OrderController@mine |
| ANY | `/order-products` | OrderController@products |
| ANY | `/cart` | OrderController@cart |
| ANY | `/finalize-order` | OrderController@finalize |
| ANY | `/order-history` | OrderController@history |
| ANY | `/order-details?order_id=` | OrderController@details |
| POST | `/order-email` | OrderController@sendEmail |
| ANY | `/products` | ProductController@index |
| ANY | `/products/details?product_id=` | ProductController@details |
| ANY | `/products/upload` | ProductController@upload |
| ANY | `/categories` | CategoryController@index |
| ANY | `/clients` | ClientController@index |
| ANY | `/clients/details?client_id=` | ClientController@details |
| ANY | `/users` | UserController@index |
| ANY | `/settings` | SettingsController@index |
| POST | `/notifications/read` | NotificationController@markRead |

See `config/routes.php` for the full list.

## Roadmap

- ✅ Phase 1 — Repair & sync working version onto GitHub.
- ✅ Phase 2 — Security hardening (CSRF, headers, rate-limit, session bootstrap).
- ✅ Phase 3 — Structure (front controller, router, controllers / views / layouts, PSR-4 autoloader, `.env`-based config).
- Phase 4 — UX/UI + responsive (mobile-first for field workers, dark mode).
- Phase 5 — Notifications (toasts, persistent badge, optional web push).
- Phase 6 — Quality (PHPStan, central logger, basic tests for auth & checkout).
- Phase 3.5 — Move `vendor/` and frontend `plugins/` out of the repo: regenerate via `composer install` and `npm install` once stable manifests are in place.

## Migrating from a previous install

If you ran the legacy version (root-level `dashboard.php`, `cart.php`, etc.):

1. Pull the latest code.
2. Move credentials out of `config/config.php` into `.env`. The new bootstrap reads `.env` first and falls back to `config/config.php` if present, so the cutover is non-breaking.
3. URLs no longer use `.php`. `dashboard.php` → `/dashboard`, `products.php` → `/products`, etc.
