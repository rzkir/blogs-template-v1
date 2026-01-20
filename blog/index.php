<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/CategoriesController.php';

$controller = new PostController($db);
$categoriesController = new CategoriesController($db);

// Get post slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    // If no slug specified, redirect to home
    header('Location: /');
    exit;
}

// Get post information
$post = $controller->getBySlug($slug);

if (!$post) {
    // Post not found, redirect to 404
    header('Location: /404.php');
    exit;
}

// Only show published posts to public
if ($post['status'] !== 'published' && (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')) {
    header('Location: /404.php');
    exit;
}

// Increment views
$controller->incrementViews($post['id']);

// Get related posts from same category
$relatedPosts = [];
if (!empty($post['categories_id'])) {
    $allCategoryPosts = $controller->getByCategorySlug($post['category_slug']);
    // Filter out current post and limit to 4
    $relatedPosts = array_filter($allCategoryPosts, function ($p) use ($post) {
        return $p['id'] !== $post['id'];
    });
    $relatedPosts = array_slice($relatedPosts, 0, 4);
}

// Get all categories for sidebar
$categories = $categoriesController->getAll();

$pageTitle = htmlspecialchars($post['title']) . ' - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-6">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs sm:text-sm flex-wrap">
            <a href="/" class="text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1">
                <i class="fas fa-home"></i> <span class="hidden sm:inline">Beranda</span>
            </a>
            <?php if (!empty($post['category_name'])): ?>
                <span class="text-gray-400">/</span>
                <a href="/category/?slug=<?php echo htmlspecialchars($post['category_slug']); ?>"
                    class="text-gray-500 hover:text-red-600 transition-colors truncate max-w-[150px] sm:max-w-none">
                    <?php echo htmlspecialchars($post['category_name']); ?>
                </a>
            <?php endif; ?>
            <span class="text-gray-400">/</span>
            <span class="text-gray-700 truncate flex-1 min-w-0"><?php echo htmlspecialchars($post['title']); ?></span>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Article -->
            <article class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Category Badge -->
                <?php if (!empty($post['category_name'])): ?>
                    <div class="px-4 py-4">
                        <a href="/category/?slug=<?php echo htmlspecialchars($post['category_slug']); ?>"
                            class="inline-block px-2.5 py-1 sm:px-3 sm:py-1 bg-red-600 text-white text-xs sm:text-sm font-semibold rounded hover:bg-red-700 transition-colors">
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Title -->
                <header class="px-4 sm:px-6 pt-4 pb-6">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h1>

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-gray-600 border-b border-gray-200 pb-4">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <?php if (!empty($post['picture'])): ?>
                                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-gray-200">
                                    <img src="<?php echo htmlspecialchars($post['picture']); ?>"
                                        alt="<?php echo htmlspecialchars($post['fullname'] ?? 'Admin'); ?>"
                                        class="w-full h-full object-cover">
                                </div>
                            <?php else: ?>
                                <i class="fas fa-user-circle text-gray-400"></i>
                            <?php endif; ?>
                            <span class="font-medium"><?php echo htmlspecialchars($post['fullname'] ?? 'Admin'); ?></span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <i class="fas fa-calendar text-gray-400"></i>
                            <span>
                                <?php
                                $date = new DateTime($post['created_at']);
                                echo $date->format('d M Y');
                                ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <i class="fas fa-eye text-gray-400"></i>
                            <span><?php echo number_format($post['views'] + 1); ?> views</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($post['description'])): ?>
                        <div class="mt-4 text-base sm:text-lg text-gray-700 leading-relaxed font-medium">
                            <?php echo htmlspecialchars($post['description']); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Featured Image -->
                <?php if (!empty($post['image'])): ?>
                    <div class="px-4 sm:px-6 pb-6">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                            class="w-full rounded-lg object-cover">
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div class="px-4 sm:px-6 pb-6">
                    <div class="prose prose-base sm:prose-lg max-w-none post-content">
                        <?php echo $post['content']; ?>
                    </div>
                </div>

                <!-- Share Buttons -->
                <div class="px-6 pb-6 border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <span class="text-gray-700 font-semibold text-sm sm:text-base">Bagikan artikel ini:</span>
                        <div class="flex gap-2 flex-wrap">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                                target="_blank"
                                class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>"
                                target="_blank"
                                class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                                target="_blank"
                                class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700 transition-colors">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Related Posts -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="mt-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-12 bg-red-600"></div>
                        <h2 class="text-xl font-bold text-gray-900">Berita Terkait</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($relatedPosts as $related): ?>
                            <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                                <a href="/blog/?slug=<?php echo htmlspecialchars($related['slug']); ?>" class="block">
                                    <div class="flex gap-3 sm:gap-4 p-3 sm:p-4">
                                        <div class="relative w-24 h-24 sm:w-32 sm:h-32 flex-shrink-0 overflow-hidden rounded">
                                            <?php if (!empty($related['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($related['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($related['title']); ?>"
                                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400 text-xl sm:text-2xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm sm:text-base font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors mb-1 sm:mb-2">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </h3>
                                            <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 mb-1 sm:mb-2 hidden sm:block">
                                                <?php echo htmlspecialchars($related['description'] ?? ''); ?>
                                            </p>
                                            <div class="flex items-center gap-2 sm:gap-3 text-xs text-gray-500">
                                                <span>
                                                    <?php
                                                    $date = new DateTime($related['created_at']);
                                                    echo $date->format('d M Y');
                                                    ?>
                                                </span>
                                                <span class="hidden sm:inline">•</span>
                                                <span class="hidden sm:inline"><?php echo number_format($related['views'] ?? 0); ?> views</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 space-y-6">
            <!-- Tags -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-1 w-8 bg-red-600"></div>
                    <h3 class="text-lg font-bold text-gray-900">Tags</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if (!empty($post['tags']) && is_array($post['tags'])): ?>
                        <?php foreach ($post['tags'] as $tag): ?>
                            <a href="/tags/?slug=<?php echo htmlspecialchars($tag['slug'] ?? $tag['tags_id']); ?>"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <i class="fas fa-tag mr-1 text-xs"></i>
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 px-3 py-2">Belum ada tags</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-1 w-8 bg-red-600"></div>
                    <h3 class="text-lg font-bold text-gray-900">Kategori</h3>
                </div>
                <div>
                    <?php if (!empty($post['category_name'])): ?>
                        <a href="/category/?slug=<?php echo htmlspecialchars($post['category_slug']); ?>"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-folder mr-2"></i>
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </a>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 px-3 py-2">Tidak ada kategori</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>