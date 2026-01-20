<?php
require_once __DIR__ . '/config/security.php';
app_enforce_https();
app_secure_session_start();

session_unset();
session_destroy();

app_secure_session_start();
@session_regenerate_id(true);
$_SESSION['success'] = 'Berhasil logout.';
header('Location: /login');
exit;
