    <?php
    $requestPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');

    // Ensure standard SEO endpoints work even on hosts that route everything to index.php
    if ($requestPath === 'sitemap' || $requestPath === 'sitemap.xml') {
        require __DIR__ . '/sitemap.php';
        exit;
    }
    if ($requestPath === 'robots.txt') {
        require __DIR__ . '/robots.php';
        exit;
    }

    session_start();
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/PostController.php';
    require_once __DIR__ . '/controllers/CategoriesController.php';
    require_once __DIR__ . '/controllers/TagsController.php';
    require_once __DIR__ . '/components/Card.php';

    $controller = new PostController($db);
    $categoriesController = new CategoriesController($db);
    $tagsController = new TagsController($db);

    // Slug handling (pretty URL).
    // Some hosts internally route unknown URLs to index.php without passing ?slug=...
    // So we also fall back to REQUEST_URI when slug query param is missing.
    $slug = trim($_GET['slug'] ?? '', '/');

    // Explicit 404 route safeguard (avoid loops if /404 is routed to index.php by server)
    if ($requestPath === '404') {
        require __DIR__ . '/404.php';
        exit;
    }
    if ($requestPath === '404.php') {
        header('Location: /404', true, 301);
        exit;
    }

    if ($slug === '' && $requestPath !== '' && $requestPath !== 'index.php') {
        $slug = $requestPath;
    }

    if ($slug !== '') {
        $_GET['slug'] = $slug;
        require __DIR__ . '/blog/index.php';
        exit;
    }

    // Get all posts and filter only published ones
    $allPosts = $controller->getAll();
    $posts = array_filter($allPosts, function ($post) {
        return $post['status'] === 'published';
    });

    // Get all categories for sidebar
    $categories = $categoriesController->getAll();

    // Get all tags for sidebar
    $tags = $tagsController->getAll();

    // Get popular posts (by views)
    $popularPosts = $controller->getPopular(5);

    // Get featured posts (spotlight)
    $featuredPosts = $controller->getFeatured(5);

    $pageTitle = 'Blog - Beranda';
    include __DIR__ . '/components/Header.php';
    ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-4 md:py-6">
        <section class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Headline Section -->
                <?php if (!empty($posts)):
                    $headlinePost = $posts[0];
                    $otherPosts = array_slice($posts, 1, 3);
                ?>
                    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 lg:gap-4">
                            <!-- Main Headline -->
                            <div class="lg:col-span-2 min-w-0 lg:gap-4 p-4">
                                <?php renderCard($headlinePost, 'headline'); ?>
                            </div>

                            <!-- Side Headlines -->
                            <div class="flex flex-col justify-between gap-3 lg:gap-4 p-4 lg:py-4 lg:px-0">
                                <?php foreach ($otherPosts as $index => $post): ?>
                                    <?php renderCard($post, 'side'); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Latest News Section -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-12 bg-red-600"></div>
                        <h2 class="text-xl font-bold text-gray-900">Berita Terkini</h2>
                    </div>

                    <?php if (empty($posts)): ?>
                        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                            <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Berita</h3>
                            <p class="text-gray-500">Belum ada artikel yang dipublikasikan saat ini.</p>
                        </div>
                    <?php else:
                        $gridPosts = array_slice($posts, 4);
                    ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($gridPosts as $post): ?>
                                <?php renderCard($post, 'default'); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Featured/Sorotan Section -->
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
                            <?php foreach ($popularPosts as $index => $post): ?>
                                <?php renderCard($post, 'popular', $index + 1); ?>
                                <?php if ($index < count($popularPosts) - 1): ?>
                                    <div class="border-b border-gray-100"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Trending Topics -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-8 bg-red-600"></div>
                        <h3 class="text-lg font-bold text-gray-900">Trending</h3>
                    </div>
                    <div class="space-y-3">
                        <?php
                        $trendingPosts = array_slice($posts, 0, 5);
                        foreach ($trendingPosts as $index => $post):
                        ?>
                            <?php renderCard($post, 'trending', $index + 1); ?>
                        <?php endforeach; ?>
                    </div>
                </div>

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
        </section>
    </main>

    <?php include __DIR__ . '/components/Footer.php'; ?>