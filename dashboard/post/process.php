<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/post/index.php');
    exit;
}

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/PostController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Helper function to convert title to slug
function titleToSlug($title)
{
    // Convert to lowercase
    $slug = strtolower($title);
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

// Helper function to handle image upload
function handleImageUpload($file, $existingImage = null)
{
    // If no file uploaded and existing image exists, return existing
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $existingImage;
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $file['type'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.');
    }

    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran gambar terlalu besar. Maksimal 5MB.');
    }

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../../uploads/images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Gagal mengupload gambar.');
    }

    // Delete old image if exists
    if ($existingImage) {
        $oldImagePath = __DIR__ . '/../..' . $existingImage;
        if (file_exists($oldImagePath)) {
            @unlink($oldImagePath);
        }
    }

    // Return relative path for database
    return '/uploads/images/' . $filename;
}

$controller = new PostController($db);
$authController = new AuthController($db);
$userId = $_SESSION['user']['id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            $categoriesId = !empty($_POST['categories_id']) ? (int)$_POST['categories_id'] : null;
            $tags = isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [];

            if (empty($title)) {
                throw new Exception('Judul post wajib diisi.');
            }

            if (empty($slug)) {
                // Auto-generate slug from title if not provided
                $slug = titleToSlug($title);
            }

            if (empty($description)) {
                throw new Exception('Deskripsi post wajib diisi.');
            }

            if (empty($content)) {
                throw new Exception('Konten post wajib diisi.');
            }

            // Handle image upload
            $imageFile = $_FILES['image'] ?? null;
            if (empty($imageFile['name'])) {
                throw new Exception('Gambar thumbnail wajib diupload.');
            }
            $image = handleImageUpload($imageFile);

            // Validate status
            if (!in_array($status, ['draft', 'published', 'archived'])) {
                $status = 'draft';
            }

            $newId = $controller->create(
                $title,
                $slug,
                $description,
                $content,
                $image,
                $status,
                $categoriesId,
                $userId,
                $tags
            );

            $authController->log($userId, 'post_create', 'Post baru dibuat: ' . $title);
            $_SESSION['success'] = 'Post berhasil ditambahkan.';
            header('Location: /dashboard/post/index.php');
            exit;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            $categoriesId = !empty($_POST['categories_id']) ? (int)$_POST['categories_id'] : null;
            $tags = isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [];
            $existingImage = $_POST['existing_image'] ?? null;

            if ($id <= 0) {
                throw new Exception('ID post tidak valid.');
            }

            if (empty($title)) {
                throw new Exception('Judul post wajib diisi.');
            }

            if (empty($slug)) {
                // Auto-generate slug from title if not provided
                $slug = titleToSlug($title);
            }

            if (empty($description)) {
                throw new Exception('Deskripsi post wajib diisi.');
            }

            if (empty($content)) {
                throw new Exception('Konten post wajib diisi.');
            }

            // Handle image upload (use existing if no new file uploaded)
            $imageFile = $_FILES['image'] ?? null;
            $image = handleImageUpload($imageFile, $existingImage);

            // If no image at all, use existing
            if (empty($image) && $existingImage) {
                $image = $existingImage;
            }

            if (empty($image)) {
                throw new Exception('Gambar thumbnail wajib diisi.');
            }

            // Validate status
            if (!in_array($status, ['draft', 'published', 'archived'])) {
                $status = 'draft';
            }

            $controller->update(
                $id,
                $title,
                $slug,
                $description,
                $content,
                $image,
                $status,
                $categoriesId,
                $tags
            );

            $authController->log($userId, 'post_update', 'Post diupdate: ' . $title . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Post berhasil diupdate.';
            header('Location: /dashboard/post/index.php');
            exit;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID post tidak valid.');
            }

            // Get post title before deletion for logging
            $post = $controller->getById($id);
            $postTitle = $post['title'] ?? 'Unknown';

            $controller->delete($id);
            $authController->log($userId, 'post_delete', 'Post dihapus: ' . $postTitle . ' (ID: ' . $id . ')');
            $_SESSION['success'] = 'Post berhasil dihapus.';
            header('Location: /dashboard/post/index.php');
            exit;

        default:
            $_SESSION['error'] = 'Aksi tidak dikenal.';
            header('Location: /dashboard/post/index.php');
            exit;
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $_SESSION['error'] = $errorMessage;

    // Log error
    if (isset($authController) && isset($userId)) {
        $authController->log($userId, 'post_error', 'Error: ' . $errorMessage);
    }

    // Redirect back to appropriate page
    if ($action === 'create') {
        header('Location: /dashboard/post/create.php');
    } elseif ($action === 'update') {
        header('Location: /dashboard/post/edit.php?id=' . ($_POST['id'] ?? ''));
    } else {
        header('Location: /dashboard/post/index.php');
    }
    exit;
}
