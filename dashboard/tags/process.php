<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/tags/index.php');
    exit;
}

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TagsController.php';
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

$controller = new TagsController($db);
$authController = new AuthController($db);
$userId = $_SESSION['user']['id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                throw new Exception('Nama tag wajib diisi.');
            }

            // Generate tags_id automatically from name
            $tagsId = nameToSlug($name);

            $newId = $controller->create($name, $tagsId, $userId);
            $authController->log($userId, 'tag_create', 'Tag baru dibuat: ' . $name);
            $_SESSION['success'] = 'Tag berhasil ditambahkan.';
            header('Location: /dashboard/tags/index.php');
            exit;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                throw new Exception('Nama tag wajib diisi.');
            }

            if ($id <= 0) {
                throw new Exception('ID tag tidak valid.');
            }

            // Generate tags_id automatically from name
            $tagsId = nameToSlug($name);

            $controller->update($id, $name, $tagsId);
            $authController->log($userId, 'tag_update', 'Tag diupdate: ' . $name . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Tag berhasil diupdate.';
            header('Location: /dashboard/tags/index.php');
            exit;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID tag tidak valid.');
            }

            // Get tag name before deletion for logging
            $tag = $controller->getById($id);
            $tagName = $tag['name'] ?? 'Unknown';

            $controller->delete($id);
            $authController->log($userId, 'tag_delete', 'Tag dihapus: ' . $tagName . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Tag berhasil dihapus.';
            header('Location: /dashboard/tags/index.php');
            exit;

        default:
            $_SESSION['error'] = 'Aksi tidak dikenal.';
            header('Location: /dashboard/tags/index.php');
            exit;
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $_SESSION['error'] = $errorMessage;

    // Log error
    if (isset($authController) && isset($userId)) {
        $authController->log($userId, 'tag_error', 'Error: ' . $errorMessage);
    }

    // Redirect back to index page
    header('Location: /dashboard/tags/index.php');
    exit;
}
