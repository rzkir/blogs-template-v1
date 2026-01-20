<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/CategoriesController.php';
require_once __DIR__ . '/controllers/TagsController.php';
require_once __DIR__ . '/components/Card.php';

$controller = new PostController($db);
$categoriesController = new CategoriesController($db);
$tagsController = new TagsController($db);

// Get slug from URL parameter
$slug = $_GET['slug'] ?? '';

// If no slug provided, show all blog posts
if (empty($slug)) {
    // Get all published posts
    $allPosts = $controller->getAll();
    $posts = array_filter($allPosts, function ($post) {
        return $post['status'] === 'published';
    });

    // Get all categories for sidebar
    $categories = $categoriesController->getAll();

    // Get all tags for sidebar
    $tags = $tagsController->getAll();

    // Get popular posts
    $popularPosts = $controller->getPopular(5);

    // Get featured posts
    $featuredPosts = $controller->getFeatured(5);

    $pageTitle = 'Semua Blog Posts';
    include __DIR__ . '/components/Header.php';
?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content Area -->
            <div class="flex-1">
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="h-1 w-12 bg-red-600"></div>
                            <h1 class="text-2xl font-bold text-gray-900">Semua Blog Posts</h1>
                        </div>
                        <span class="text-sm text-gray-500"><?php echo count($posts); ?> artikel</span>
                    </div>

                    <?php if (empty($posts)): ?>
                        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                            <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Blog</h3>
                            <p class="text-gray-500">Belum ada artikel blog yang dipublikasikan saat ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($posts as $post): ?>
                                <?php renderCard($post, 'default'); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Featured Section -->
                <?php if (!empty($featuredPosts)): ?>
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-12 bg-yellow-500"></div>
                            <h2 class="text-xl font-bold text-gray-900">
                                <i class="fas fa-star text-yellow-500"></i> Sorotan
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($featuredPosts as $post): ?>
                                <?php renderCard($post, 'featured'); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="w-full lg:w-80 space-y-6">
                <!-- Popular Posts Section -->
                <?php if (!empty($popularPosts)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-8 bg-blue-600"></div>
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-fire text-orange-500"></i> Popular
                            </h3>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($popularPosts as $index => $popularPost): ?>
                                <?php renderCard($popularPost, 'popular', $index + 1); ?>
                                <?php if ($index < count($popularPosts) - 1): ?>
                                    <div class="border-b border-gray-100"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-8 bg-red-600"></div>
                        <h3 class="text-lg font-bold text-gray-900">Tags</h3>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <?php if (!empty($tags)): ?>
                            <?php foreach ($tags as $tag): ?>
                                <a href="/tags/?slug=<?php echo htmlspecialchars($tag['slug'] ?? $tag['tags_id']); ?>"
                                    class="block px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 rounded transition-colors">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 px-3 py-2">Belum ada tags</p>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </main>

<?php
    include __DIR__ . '/components/Footer.php';
    exit;
}

// If slug provided, redirect to blog detail page
header('Location: /blog/?slug=' . urlencode($slug));
exit;
