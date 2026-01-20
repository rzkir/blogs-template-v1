<?php

require_once __DIR__ . '/config/security.php';
app_enforce_https();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login');
    exit;
}

$action = $_POST['action'] ?? '';

$csrfToken = $_POST['csrf_token'] ?? null;
// Only enforce CSRF for state-changing auth endpoints
if (in_array($action, ['login', 'register', 'logout'], true)) {
    if (!app_csrf_validate(is_string($csrfToken) ? $csrfToken : null)) {
        app_secure_session_start();
        $_SESSION['error'] = 'Permintaan tidak valid (CSRF). Silakan refresh halaman dan coba lagi.';
        header('Location: /login');
        exit;
    }
}

$controller = new AuthController($db);

switch ($action) {
    case 'register':
        $controller->register();
        break;
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        app_secure_session_start();
        $_SESSION['error'] = 'Aksi tidak dikenal.';
        header('Location: /login');
        exit;
}
