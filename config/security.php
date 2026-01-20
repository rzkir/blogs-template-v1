<?php

/**
 * Basic security helpers (headers, HTTPS enforcement, CSRF, session cookie flags).
 * Keep this lightweight and safe to include from any entrypoint before output.
 */

function app_is_https(): bool
{
    // Common direct HTTPS signal
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') {
        return true;
    }

    // Reverse proxy / CDN (Cloudflare, Nginx, etc.)
    $xfp = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if (is_string($xfp) && strtolower(trim(explode(',', $xfp)[0])) === 'https') {
        return true;
    }
    $cfVisitor = $_SERVER['HTTP_CF_VISITOR'] ?? '';
    if (is_string($cfVisitor) && stripos($cfVisitor, '"scheme":"https"') !== false) {
        return true;
    }

    return false;
}

function app_enforce_https(): void
{
    // Avoid breaking local development / CLI runs
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        return;
    }

    if (app_is_https()) {
        return;
    }

    // Best-effort redirect to https version of the same URL
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';

    $hostLower = strtolower((string)$host);
    if ($hostLower === '' || str_starts_with($hostLower, 'localhost') || str_starts_with($hostLower, '127.0.0.1') || str_starts_with($hostLower, '[::1]') || $hostLower === '::1') {
        return;
    }

    if ($host !== '') {
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}

function app_secure_session_start(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $isHttps = app_is_https();

    // PHP 7.3+ array cookie params
    @session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function app_csp_nonce(): string
{
    static $nonce = null;
    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }
    return $nonce;
}

/**
 * Send baseline security headers.
 * Returns CSP nonce if CSP is enabled.
 */
function app_send_security_headers(array $options = []): ?string
{
    $noIndex = (bool)($options['noindex'] ?? false);
    $cspMode = (string)($options['csp'] ?? 'none'); // 'none' | 'auth'

    // Basic hardening
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-site');

    // HSTS only makes sense over HTTPS
    if (app_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    if ($noIndex) {
        header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
    }

    if ($cspMode === 'auth') {
        $nonce = app_csp_nonce();

        // CSP tuned for login/register pages
        // - Allows required CDNs used by current templates
        // - Blocks framing, plugins, and cross-site form submits
        $csp = "default-src 'self'; "
            . "base-uri 'self'; "
            . "object-src 'none'; "
            . "frame-ancestors 'none'; "
            . "form-action 'self'; "
            . "img-src 'self' data:; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
            . "script-src 'self' 'nonce-" . $nonce . "' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; "
            . "connect-src 'self'; "
            . "upgrade-insecure-requests";

        header('Content-Security-Policy: ' . $csp);
        return $nonce;
    }

    return null;
}

function app_csrf_get_token(): string
{
    app_secure_session_start();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function app_csrf_validate(?string $token): bool
{
    app_secure_session_start();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sessionToken) || $sessionToken === '' || !is_string($token) || $token === '') {
        return false;
    }
    return hash_equals($sessionToken, $token);
}
