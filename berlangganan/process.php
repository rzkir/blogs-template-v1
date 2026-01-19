<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /berlangganan');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/BerlanggananController.php';

$controller = new BerlanggananController($db);
$controller->subscribe();
