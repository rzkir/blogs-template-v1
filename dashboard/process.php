<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard');
    exit;
}

$action = $_POST['action'] ?? '';

// require DB + AuthController to allow logging with IP
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController($db);

switch ($action) {
    case 'logout':
        $userId = $_SESSION['user']['id'] ?? null;
        // log logout (AuthController will capture client IP)
        $controller->log($userId ? (int)$userId : null, 'logout', 'User logged out');

        session_unset();
        session_destroy();
        session_start();
        $_SESSION['success'] = 'Berhasil logout.';
        header('Location: /login');
        exit;

    default:
        $_SESSION['error'] = 'Aksi dashboard tidak dikenal.';
        header('Location: /dashboard');
        exit;
}
