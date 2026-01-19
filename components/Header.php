<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get categories from database for navigation menu
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/CategoriesController.php';

$categoriesController = new CategoriesController($db);
$categories = $categoriesController->getAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Berita Terkini Hari Ini - Blog'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/style/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen" style="font-family: 'Inter', sans-serif;">
    <!-- Top Bar -->
    <div class="bg-red-600 text-white py-2 hidden md:block">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center text-sm">
                <div class="flex items-center gap-6">
                    <span class="font-medium">Berita Terkini Hari Ini</span>
                    <span class="text-red-100">|</span>
                    <span class="text-red-100">Kabar Akurat Terpercaya</span>
                </div>
                <div class="flex items-center gap-4">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="/dashboard" class="hover:text-red-100 transition-colors">Dashboard</a>
                        <span class="text-red-100">|</span>
                        <a href="/logout.php" class="hover:text-red-100 transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="/login" class="hover:text-red-100 transition-colors">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Logo and Search -->
            <div class="flex items-center justify-between py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-red-600 rounded flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <a href="/">Blog News</a>
                        </h1>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-4 flex-1 max-w-md mx-8">
                    <div class="relative flex-1">
                        <input type="text" placeholder="Cari berita..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="md:hidden p-2 hover:bg-gray-100 rounded">
                        <i class="fas fa-search text-gray-600"></i>
                    </button>
                    <button class="md:hidden p-2 hover:bg-gray-100 rounded" id="mobileMenuBtn">
                        <i class="fas fa-bars text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="hidden md:block">
                <div class="flex items-center gap-1 overflow-x-auto">
                    <a href="/" class="px-4 py-3 text-sm font-semibold text-gray-900 hover:bg-red-50 hover:text-red-600 transition-colors border-b-2 border-transparent hover:border-red-600">
                        Beranda
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="/category/index.php?category=<?php echo htmlspecialchars($category['categories_id']); ?>"
                                class="px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-200">
                <div class="px-4 py-3 space-y-2">
                    <a href="/" class="block px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-red-50 hover:text-red-600 rounded transition-colors">
                        Beranda
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="/category/index.php?category=<?php echo htmlspecialchars($category['categories_id']); ?>"
                                class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded transition-colors">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="/dashboard" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded transition-colors">
                            Dashboard
                        </a>
                        <a href="/logout.php" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded transition-colors">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="/login" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded transition-colors">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>