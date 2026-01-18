<?php

require_once __DIR__ . '../config/db.php';
require_once __DIR__ . '../controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login');
    exit;
}

$action = $_POST['action'] ?? '';

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
        session_start();
        $_SESSION['error'] = 'Aksi tidak dikenal.';
        header('Location: /login');
        exit;
}
