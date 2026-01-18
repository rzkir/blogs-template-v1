
<?php
session_start();

session_unset();
session_destroy();

session_start();
$_SESSION['success'] = 'Berhasil logout.';
header('Location: login.php');
exit;
