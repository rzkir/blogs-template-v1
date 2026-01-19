<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/CategoriesController.php';
require_once __DIR__ . '/controllers/TagsController.php';

$controller = new PostController($db);
$categoriesController = new CategoriesController($db);
$tagsController = new TagsController($db);

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
<main class="container mx-auto px-4 py-6">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Headline Section -->
            <?php if (!empty($posts)):
                $headlinePost = $posts[0];
                $otherPosts = array_slice($posts, 1, 3);
            ?>
                <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <!-- Main Headline -->
                        <div class="lg:col-span-2">
                            <a href="/blog/?slug=<?php echo htmlspecialchars($headlinePost['slug']); ?>" class="block group">
                                <div class="relative h-80 overflow-hidden">
                                    <?php if (!empty($headlinePost['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($headlinePost['image']); ?>"
                                            alt="<?php echo htmlspecialchars($headlinePost['title']); ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-8xl text-white/30"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                    <div class="absolute bottom-0 left-0 right-0 p-6">
                                        <?php if (!empty($headlinePost['category_name'])): ?>
                                            <span class="inline-block px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded mb-3">
                                                <?php echo htmlspecialchars($headlinePost['category_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <h2 class="text-2xl lg:text-3xl font-bold text-white mb-2 line-clamp-2 group-hover:text-red-200 transition-colors">
                                            <?php echo htmlspecialchars($headlinePost['title']); ?>
                                        </h2>
                                        <p class="text-white/90 text-sm line-clamp-2">
                                            <?php echo htmlspecialchars($headlinePost['description'] ?? ''); ?>
                                        </p>
                                        <div class="flex items-center gap-3 mt-3 text-white/80 text-xs">
                                            <span>
                                                <?php
                                                $date = new DateTime($headlinePost['created_at']);
                                                echo $date->format('d M Y, H:i');
                                                ?>
                                            </span>
                                            <span>•</span>
                                            <span><?php echo htmlspecialchars($headlinePost['fullname'] ?? 'Admin'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Side Headlines -->
                        <div class="space-y-4">
                            <?php foreach ($otherPosts as $index => $post): ?>
                                <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block group">
                                    <div class="flex gap-3">
                                        <div class="relative w-24 h-24 flex-shrink-0 overflow-hidden rounded">
                                            <?php if (!empty($post['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors mb-1">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                <?php
                                                $date = new DateTime($post['created_at']);
                                                echo $date->format('d M Y');
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                                <?php if ($index < count($otherPosts) - 1): ?>
                                    <div class="border-b border-gray-200"></div>
                                <?php endif; ?>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($gridPosts as $post): ?>
                            <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                                <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block">
                                    <div class="relative h-48 overflow-hidden">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                                <i class="fas fa-newspaper text-5xl text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($post['category_name'])): ?>
                                            <div class="absolute top-3 left-3">
                                                <span class="px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded">
                                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-red-600 transition-colors">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </h3>
                                        <?php if (!empty($post['description'])): ?>
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                                <?php echo htmlspecialchars($post['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>
                                                <?php
                                                $date = new DateTime($post['created_at']);
                                                echo $date->format('d M Y');
                                                ?>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-eye"></i>
                                                <?php echo number_format($post['views'] ?? 0); ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 space-y-6">
            <!-- Featured/Sorotan Section -->
            <?php if (!empty($featuredPosts)): ?>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-8 bg-yellow-500"></div>
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-star text-yellow-500"></i> Sorotan
                        </h3>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($featuredPosts as $index => $post): ?>
                            <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block group">
                                <div class="flex gap-3">
                                    <div class="relative w-20 h-20 flex-shrink-0 overflow-hidden rounded">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-yellow-100 flex items-center justify-center">
                                                <i class="fas fa-star text-yellow-500"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-yellow-600 transition-colors mb-1">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </h4>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span>
                                                <i class="fas fa-eye"></i>
                                                <?php echo number_format($post['views'] ?? 0); ?>
                                            </span>
                                            <span>•</span>
                                            <span>
                                                <?php
                                                $date = new DateTime($post['created_at']);
                                                echo $date->format('d M Y');
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php if ($index < count($featuredPosts) - 1): ?>
                                <div class="border-b border-gray-200"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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
                            <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="flex gap-3 group">
                                <span class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 text-white text-sm font-bold rounded-full flex items-center justify-center shadow-md">
                                    <?php echo $index + 1; ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm text-gray-700 line-clamp-2 group-hover:text-blue-600 transition-colors block mb-1">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-eye"></i> <?php echo number_format($post['views'] ?? 0); ?> views
                                    </span>
                                </div>
                            </a>
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
                        <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="flex gap-3 group">
                            <span class="flex-shrink-0 w-6 h-6 bg-red-600 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                <?php echo $index + 1; ?>
                            </span>
                            <span class="text-sm text-gray-700 line-clamp-2 group-hover:text-red-600 transition-colors flex-1">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </span>
                        </a>
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
                            <a href="/tags?slug=<?php echo htmlspecialchars($tag['tags_id']); ?>"
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

<?php include __DIR__ . '/components/Footer.php'; ?>