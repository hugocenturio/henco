# Henco

Order management web app for field workers — manage clients, products, carts and orders, with email/PDF order delivery.

Stack: PHP 8 + MySQL, Bootstrap 5, jQuery. Uses PHPMailer / Mailjet / Swiftmailer (vendored) for transactional email and dompdf for order PDFs.

## Quick start

1. **Clone**
   ```sh
   git clone https://github.com/hugocenturio/henco.git
   cd henco
   ```

2. **Configure**
   ```sh
   cp config/config.example.php config/config.php
   # edit config/config.php with your DB credentials and Mailjet keys
   ```

3. **Database**
   - Create a MySQL database matching `DB_NAME` in your config.
   - Import `database/schema.sql`.
   - Open `setup.php` once in the browser to seed the first admin user, then remove or restrict it.

4. **Serve**
   - Drop the folder under your web server (Apache/nginx + PHP 8) and point a vhost at it, or run locally:
     ```sh
     php -S localhost:8000
     ```

## Layout

```
config/        DB + Mailjet credentials (config.php gitignored)
database/      schema.sql
controllers... front-facing PHP entrypoints (login, dashboard, cart, ...)
views          header.php / footer.php / page_template.php
locales/       translations (pt.json)
css/ scss/ js/ frontend assets
plugins/       third-party Bootstrap admin plugins
vendor/        composer dependencies (PHPMailer, dompdf, Mailjet, Guzzle, Symfony, ...)
uploads/       runtime uploads (gitignored)
```

> **Note:** `vendor/` and `plugins/` are currently committed because no `composer.json` / `package.json` is checked in. Dependency manifests will be added in a follow-up phase.

## Roadmap

- **Phase 1 — Repair & sync** *(this push)*: replace broken GitHub MVC scaffold with the working flat version; sanitize secrets; baseline `.gitignore` and README.
- Phase 2 — Security hardening (CSRF, prepared statements review, XSS escaping, rate-limit on login, generic auth error messages, session hardening, security headers).
- Phase 3 — Structure (front controller + simple router, controller/view separation, `composer.json`, `.env`-based config).
- Phase 4 — UX/UI + responsive (mobile-first for field workers, dark mode, dashboard polish, navigation).
- Phase 5 — Notifications (toast UI, persistent notifications with badge; optional web push).
- Phase 6 — Quality (PHPStan, central logger, basic tests for auth & checkout).
