<?php
/**
 * Security bootstrap — must be required at the very top of every entry point,
 * BEFORE any output (so headers and session cookie can be set).
 *
 *   require_once __DIR__ . '/config/security.php';   // from project root
 *   require_once 'config/security.php';              // from a sibling file
 */

// -------------------------------------------------------------------
// 1. Hardened session start
// -------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax', // Strict breaks GET-redirect-after-login flows
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}

// -------------------------------------------------------------------
// 2. Security response headers
// -------------------------------------------------------------------
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    ) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // Permissive CSP: allows the CDNs the templates already use, plus inline
    // styles/scripts that the legacy templates rely on. Tighten in a later phase.
    header(
        "Content-Security-Policy: "
        . "default-src 'self'; "
        . "img-src 'self' data: https:; "
        . "font-src 'self' data: https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://code.jquery.com; "
        . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://code.jquery.com; "
        . "connect-src 'self'; "
        . "frame-ancestors 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self'"
    );
}

// -------------------------------------------------------------------
// 3. CSRF helpers
// -------------------------------------------------------------------
// Helpers are loaded by bootstrap.php; only fall back when this file is
// required by a legacy script that did not go through bootstrap.
if (!function_exists('csrf_token')) {
    $legacyHelpers = __DIR__ . '/../helpers.php';
    $appHelpers    = __DIR__ . '/../app/helpers.php';
    if (is_file($appHelpers)) {
        require_once $appHelpers;
    } elseif (is_file($legacyHelpers)) {
        require_once $legacyHelpers;
    }
}
