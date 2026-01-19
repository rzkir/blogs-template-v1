<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/iklan');
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
            throw new Exception('ID iklan tidak valid.');
        }

        // Get data before deletion for logging
        $stmt = $db->prepare("SELECT nama, email, jenis FROM `iklan` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $iklan = $result->fetch_assoc();
        $stmt->close();

        if (!$iklan) {
            throw new Exception('Data iklan tidak ditemukan atau sudah dihapus.');
        }

        // Delete iklan (logs akan terhapus otomatis karena CASCADE)
        $stmt = $db->prepare("DELETE FROM `iklan` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new Exception('Gagal menghapus data iklan.');
        }

        $authController->log($userId, 'iklan_delete', 'Iklan dihapus: ' . $iklan['nama'] . ' (' . $iklan['email'] . ') - Jenis: ' . $iklan['jenis']);
        $_SESSION['success'] = 'Data iklan berhasil dihapus.';
    } elseif ($action === 'delete_all') {
        $countResult = $db->query("SELECT COUNT(*) as total FROM iklan");
        $total = (int)($countResult->fetch_assoc()['total'] ?? 0);

        $result = $db->query("DELETE FROM `iklan`");
        $deleted = $result ? $db->affected_rows : 0;
        $authController->log($userId, 'iklan_delete_all', 'Semua data iklan dihapus (' . $deleted . ' entri)');
        $_SESSION['success'] = 'Semua data iklan berhasil dihapus.';
        $redirectPage = ''; // Kembali ke halaman 1
    } else {
        $_SESSION['error'] = 'Aksi tidak dikenal.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    if ($userId) {
        $authController->log($userId, 'iklan_error', 'Error hapus iklan: ' . $e->getMessage());
    }
}

header('Location: /dashboard/iklan' . $redirectPage);
exit;
