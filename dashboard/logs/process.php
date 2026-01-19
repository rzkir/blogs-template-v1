<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/logs');
    exit;
}

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$authController = new AuthController($db);
$userId = (int)($_SESSION['user']['id'] ?? 0);
$action = $_POST['action'] ?? '';
$page = max(1, (int)($_POST['page'] ?? 1));
$redirectPage = $page > 1 ? '?page=' . $page : '';

try {
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID log tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM `logs` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new Exception('Log tidak ditemukan atau sudah dihapus.');
        }

        $authController->log($userId, 'log_delete', 'Log dihapus (ID: ' . $id . ')');
        $_SESSION['success'] = 'Log berhasil dihapus.';
    } elseif ($action === 'delete_all') {
        $result = $db->query("DELETE FROM `logs`");
        $deleted = $result ? $db->affected_rows : 0;
        $authController->log($userId, 'log_delete_all', 'Semua logs dihapus (' . $deleted . ' entri)');
        $_SESSION['success'] = 'Semua logs berhasil dihapus.';
        $redirectPage = ''; // Kembali ke halaman 1
    } else {
        $_SESSION['error'] = 'Aksi tidak dikenal.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    if ($userId) {
        $authController->log($userId, 'log_error', 'Error hapus log: ' . $e->getMessage());
    }
}

header('Location: /dashboard/logs' . $redirectPage);
exit;
