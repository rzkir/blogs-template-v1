<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/category/index.php');
    exit;
}

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriesController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Helper function to convert name to slug
function nameToSlug($name)
{
    // Convert to lowercase
    $slug = strtolower($name);
    // Replace spaces with hyphens
    $slug = str_replace(' ', '-', $slug);
    // Remove special characters, keep only alphanumeric and hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    // Replace multiple hyphens with single hyphen
    $slug = preg_replace('/-+/', '-', $slug);
    // Trim hyphens from start and end
    $slug = trim($slug, '-');
    return $slug;
}

$controller = new CategoriesController($db);
$authController = new AuthController($db);

// Validate and get user ID from session
if (!isset($_SESSION['user']['id'])) {
    $_SESSION['error'] = 'Session user tidak valid. Silakan login kembali.';
    header('Location: /login');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Verify user exists in database
$stmt = $db->prepare("SELECT id FROM `accounts` WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$userExists = $result->fetch_assoc();
$stmt->close();

if (!$userExists) {
    $_SESSION['error'] = 'User tidak ditemukan di database. Silakan login kembali.';
    header('Location: /login');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                throw new Exception('Nama kategori wajib diisi.');
            }

            // Generate categories_id automatically from name
            $categoriesId = nameToSlug($name);

            $newId = $controller->create($name, $categoriesId, $userId);
            $authController->log($userId, 'category_create', 'Kategori baru dibuat: ' . $name);
            $_SESSION['success'] = 'Kategori berhasil ditambahkan.';
            header('Location: /dashboard/category/index.php');
            exit;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                throw new Exception('Nama kategori wajib diisi.');
            }

            if ($id <= 0) {
                throw new Exception('ID kategori tidak valid.');
            }

            // Generate categories_id automatically from name
            $categoriesId = nameToSlug($name);

            $controller->update($id, $name, $categoriesId);
            $authController->log($userId, 'category_update', 'Kategori diupdate: ' . $name . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Kategori berhasil diupdate.';
            header('Location: /dashboard/category/index.php');
            exit;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID kategori tidak valid.');
            }

            // Get category name before deletion for logging
            $category = $controller->getById($id);
            $categoryName = $category['name'] ?? 'Unknown';

            $controller->delete($id);
            $authController->log($userId, 'category_delete', 'Kategori dihapus: ' . $categoryName . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Kategori berhasil dihapus.';
            header('Location: /dashboard/category/index.php');
            exit;

        default:
            $_SESSION['error'] = 'Aksi tidak dikenal.';
            header('Location: /dashboard/category/index.php');
            exit;
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $_SESSION['error'] = $errorMessage;

    // Log error
    if (isset($authController) && isset($userId)) {
        $authController->log($userId, 'category_error', 'Error: ' . $errorMessage);
    }

    // Redirect back to index page
    header('Location: /dashboard/category/index.php');
    exit;
}
