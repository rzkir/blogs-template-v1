<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/berlangganan');
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
            throw new Exception('ID berlangganan tidak valid.');
        }

        // Get email before deletion for logging
        $stmt = $db->prepare("SELECT email, nama FROM `berlangganan` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $berlangganan = $result->fetch_assoc();
        $stmt->close();

        if (!$berlangganan) {
            throw new Exception('Data berlangganan tidak ditemukan atau sudah dihapus.');
        }

        // Delete berlangganan (logs akan terhapus otomatis karena CASCADE)
        $stmt = $db->prepare("DELETE FROM `berlangganan` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new Exception('Gagal menghapus data berlangganan.');
        }

        $authController->log($userId, 'berlangganan_delete', 'Berlangganan dihapus: ' . $berlangganan['email'] . ' (' . $berlangganan['nama'] . ')');
        $_SESSION['success'] = 'Data berlangganan berhasil dihapus.';
    } elseif ($action === 'delete_all') {
        $countResult = $db->query("SELECT COUNT(*) as total FROM berlangganan");
        $total = (int)($countResult->fetch_assoc()['total'] ?? 0);

        $result = $db->query("DELETE FROM `berlangganan`");
        $deleted = $result ? $db->affected_rows : 0;
        $authController->log($userId, 'berlangganan_delete_all', 'Semua data berlangganan dihapus (' . $deleted . ' entri)');
        $_SESSION['success'] = 'Semua data berlangganan berhasil dihapus.';
        $redirectPage = ''; // Kembali ke halaman 1
    } else {
        $_SESSION['error'] = 'Aksi tidak dikenal.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    if ($userId) {
        $authController->log($userId, 'berlangganan_error', 'Error hapus berlangganan: ' . $e->getMessage());
    }
}

header('Location: /dashboard/berlangganan' . $redirectPage);
exit;
