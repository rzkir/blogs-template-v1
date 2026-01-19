<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get categories from database for navigation menu
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/CategoriesController.php';
require_once __DIR__ . '/../controllers/PostController.php';

$categoriesController = new CategoriesController($db);
$categories = $categoriesController->getAll();

// Get latest posts for breaking news ticker
$postController = new PostController($db);
$breakingNews = $postController->getLatestPosts(10);

// Get current page for active link detection
$currentPage = $_SERVER['REQUEST_URI'];
$currentPath = parse_url($currentPage, PHP_URL_PATH);
$currentQuery = parse_url($currentPage, PHP_URL_QUERY);
parse_str($currentQuery ?? '', $queryParams);

// Helper function to check if link is active
function isActiveLink($path, $categoryId = null)
{
    global $currentPath, $queryParams;

    // Check for home page
    if ($path === '/' && ($currentPath === '/' || $currentPath === '/index.php')) {
        return true;
    }

    // Check for category page
    if ($path === '/category/' && $categoryId !== null) {
        if (str_contains($currentPath, '/category/') && isset($queryParams['slug']) && $queryParams['slug'] == $categoryId) {
            return true;
        }
    }

    // Check for blog page
    if ($path === '/blog' && (str_contains($currentPath, '/blog') || str_contains($currentPath, '/blog/'))) {
        return true;
    }

    return false;
}
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

    <!-- Theme Script - Load BEFORE body to prevent flash -->
    <script>
        (function() {
            const theme = localStorage.getItem('blog-theme') || 'system';
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.removeAttribute('data-theme');
            }
        })();
    </script>

    <!-- Main JavaScript -->
    <script src="/js/main.js" defer></script>

    <style>
        /* Prevent flash by setting initial colors */
        html:not(.dark) header {
            background-color: #ffffff;
        }

        html.dark header {
            background-color: #111827;
        }
    </style>
</head>

<body class="min-h-screen" style="font-family: 'Inter', sans-serif;">
    <!-- Breaking News Ticker -->
    <div class="bg-gradient-to-r from-red-600 via-red-600 to-red-700 text-white py-2.5 shadow-sm overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-4">
                <div class="flex flex-1 items-center gap-2 flex-shrink-0">
                    <i class="fas fa-bolt text-yellow-300 animate-pulse"></i>
                    <span class="font-bold text-sm uppercase tracking-wide">Breaking News</span>
                </div>

                <div class="flex-1 overflow-hidden relative">
                    <div class="breaking-news-ticker" id="breakingNewsTicker">
                        <?php if (!empty($breakingNews)): ?>
                            <?php foreach ($breakingNews as $news): ?>
                                <a href="/blog/<?php echo htmlspecialchars($news['slug']); ?>" class="ticker-item">
                                    <span class="text-sm font-medium">
                                        <i class="fas fa-circle text-xs text-yellow-300 mr-2"></i>
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="ticker-item text-sm font-medium">
                                <i class="fas fa-circle text-xs text-yellow-300 mr-2"></i>
                                Selamat datang di Blog News - Portal berita terpercaya
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white dark:bg-gray-900 shadow-md sticky top-0 z-50 transition-all duration-300" id="mainHeader">
        <div class="container mx-auto px-4">
            <!-- Logo and Search -->
            <div class="flex items-center justify-between py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-600 rounded flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-base sm:text-xl"></i>
                        </div>
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">
                            <a href="/">Blog News</a>
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-4 max-w-md">
                        <form action="/search" method="GET" class="relative flex-1">
                            <input type="text"
                                name="q"
                                placeholder="Cari berita..."
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-600">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-2">
                        <!-- Theme Switcher -->
                        <div class="relative" id="themeSwitcher">
                            <button type="button" id="themeToggleBtn" class="p-1.5 sm:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i id="themeIcon" class="fas fa-sun text-gray-600 dark:text-gray-300 text-sm sm:text-base"></i>
                            </button>
                            <!-- Theme Dropdown -->
                            <div id="themeDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="py-2">
                                    <button type="button" data-theme="light" class="theme-option w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors">
                                        <i class="fas fa-sun w-5"></i>
                                        <span>Light</span>
                                        <i class="fas fa-check ml-auto theme-check hidden text-red-600"></i>
                                    </button>
                                    <button type="button" data-theme="dark" class="theme-option w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors">
                                        <i class="fas fa-moon w-5"></i>
                                        <span>Dark</span>
                                        <i class="fas fa-check ml-auto theme-check hidden text-red-600"></i>
                                    </button>
                                    <button type="button" data-theme="system" class="theme-option w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors">
                                        <i class="fas fa-desktop w-5"></i>
                                        <span>System</span>
                                        <i class="fas fa-check ml-auto theme-check hidden text-red-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="md:hidden p-1.5 sm:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" id="mobileSearchBtn">
                            <i class="fas fa-search text-gray-600 dark:text-gray-300 text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Search -->
            <div id="mobileSearch" class="hidden md:hidden px-4 py-3 border-b border-gray-200">
                <form action="/search" method="GET" class="relative">
                    <input type="text"
                        name="q"
                        placeholder="Cari berita..."
                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-600">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Category Navigation - Visible on all screen sizes -->
            <nav class="border-b border-gray-200 dark:border-gray-700">
                <div class="category-nav" style="padding-bottom: 0.5rem;">
                    <a href="/" class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-semibold hidden md:block <?php echo isActiveLink('/') ? 'text-red-600 bg-red-50 dark:bg-red-900/20 border-b-2 border-red-600' : 'text-gray-900 dark:text-gray-300 border-b-2 border-transparent hover:border-red-600'; ?> hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors flex-shrink-0">
                        Beranda
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="/category/?slug=<?php echo htmlspecialchars($category['categories_id']); ?>"
                                class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-medium <?php echo isActiveLink('/category/', $category['categories_id']) ? 'text-red-600 bg-red-50 dark:bg-red-900/20 border-b-2 border-red-600' : 'text-gray-700 dark:text-gray-300 border-b-2 border-transparent hover:border-red-600'; ?> hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-red-600 transition-colors flex-shrink-0">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Mobile Bottom Navigation - Only visible on mobile -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-lg z-50 safe-area-bottom">
        <div class="flex items-center justify-around h-14 sm:h-16">
            <a href="/" class="flex flex-col items-center justify-center flex-1 h-full <?php echo isActiveLink('/') ? 'text-red-600' : 'text-gray-600'; ?> hover:text-red-600 transition-colors relative">
                <?php if (isActiveLink('/')): ?>
                    <div class="absolute top-0 left-0 right-0 h-1 bg-red-600"></div>
                <?php endif; ?>
                <i class="fas fa-home text-xl sm:text-2xl mb-0.5 sm:mb-1"></i>
                <span class="text-xs font-medium">Beranda</span>
            </a>
            <a href="/blog" class="flex flex-col items-center justify-center flex-1 h-full <?php echo isActiveLink('/blog') ? 'text-red-600' : 'text-gray-600'; ?> hover:text-red-600 transition-colors relative">
                <?php if (isActiveLink('/blog')): ?>
                    <div class="absolute top-0 left-0 right-0 h-1 bg-red-600"></div>
                <?php endif; ?>
                <i class="fas fa-newspaper text-xl sm:text-2xl mb-0.5 sm:mb-1"></i>
                <span class="text-xs font-medium">Blog</span>
            </a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/dashboard" class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-red-600 transition-colors">
                    <i class="fas fa-tachometer-alt text-xl sm:text-2xl mb-0.5 sm:mb-1"></i>
                    <span class="text-xs font-medium">Dashboard</span>
                </a>
                <a href="/logout.php" class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-red-600 transition-colors">
                    <i class="fas fa-sign-out-alt text-xl sm:text-2xl mb-0.5 sm:mb-1"></i>
                    <span class="text-xs font-medium">Logout</span>
                </a>
            <?php else: ?>
                <a href="/login" class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-red-600 transition-colors">
                    <i class="fas fa-sign-in-alt text-xl sm:text-2xl mb-0.5 sm:mb-1"></i>
                    <span class="text-xs font-medium">Login</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>