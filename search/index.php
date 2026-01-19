<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/TagsController.php';

$controller = new PostController($db);
$tagsController = new TagsController($db);

// Get search keyword from URL
$keyword = $_GET['q'] ?? '';

// Get posts by search keyword
$posts = [];
if (!empty($keyword)) {
    $posts = $controller->search($keyword);
}

// Get all tags for sidebar
$tags = $tagsController->getAll();

$pageTitle = 'Hasil Pencarian: ' . htmlspecialchars($keyword) . ' - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-6">
    <!-- Search Header -->
    <div class="mb-6">
        <div class="flex items-center gap-1.5 sm:gap-2 mb-2 text-xs sm:text-sm flex-wrap">
            <a href="/" class="text-gray-500 hover:text-red-600 transition-colors">
                <i class="fas fa-home"></i> <span class="hidden sm:inline">Beranda</span>
            </a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-700 font-medium">Pencarian</span>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 mb-2">
            <i class="fas fa-search text-red-600 text-xl sm:text-2xl"></i>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Hasil Pencarian</h1>
        </div>
        <?php if (!empty($keyword)): ?>
            <p class="text-sm sm:text-base text-gray-600">
                Menampilkan <span class="font-semibold"><?php echo count($posts); ?> artikel</span> untuk:
                <span class="font-semibold text-red-600 break-words">"<?php echo htmlspecialchars($keyword); ?>"</span>
            </p>
        <?php else: ?>
            <p class="text-sm sm:text-base text-gray-600">Masukkan kata kunci untuk mencari artikel</p>
        <?php endif; ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Search Results -->
            <?php if (empty($keyword)): ?>
                <!-- Empty Search State -->
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Mulai Pencarian</h3>
                    <p class="text-gray-500 mb-6">Gunakan form pencarian di atas untuk mencari artikel</p>
                    <a href="/" class="inline-block px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Kembali ke Beranda
                    </a>
                </div>
            <?php elseif (empty($posts)): ?>
                <!-- No Results State -->
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-inbox text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak Ada Hasil</h3>
                    <p class="text-gray-500 mb-6">
                        Tidak ditemukan artikel untuk kata kunci <span class="font-semibold">"<?php echo htmlspecialchars($keyword); ?>"</span>
                    </p>
                    <div class="flex gap-3 justify-center">
                        <a href="/search" class="inline-block px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Cari Lagi
                        </a>
                        <a href="/" class="inline-block px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Results Found -->
                <?php
                $headlinePost = $posts[0];
                $otherPosts = array_slice($posts, 1, 3);
                ?>

                <!-- Headline Section -->
                <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 lg:gap-4">
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
                        <?php if (!empty($otherPosts)): ?>
                            <div class="space-y-3 p-4 lg:p-0 lg:space-y-4">
                                <?php foreach ($otherPosts as $index => $post): ?>
                                    <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block group">
                                        <div class="flex gap-2 sm:gap-3">
                                            <div class="relative w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 overflow-hidden rounded">
                                                <?php if (!empty($post['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                                <?php else: ?>
                                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-image text-gray-400 text-sm"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors mb-1">
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
                        <?php endif; ?>
                    </div>
                </div>

                <!-- More Results -->
                <?php if (count($posts) > 4):
                    $gridPosts = array_slice($posts, 4);
                ?>
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-12 bg-red-600"></div>
                            <h2 class="text-xl font-bold text-gray-900">Hasil Lainnya</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 space-y-6">
            <!-- Search Box -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-1 w-8 bg-red-600"></div>
                    <h3 class="text-lg font-bold text-gray-900">Cari Artikel</h3>
                </div>
                <form action="/search" method="GET" class="relative">
                    <input type="text"
                        name="q"
                        value="<?php echo htmlspecialchars($keyword); ?>"
                        placeholder="Masukkan kata kunci..."
                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        required>
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-600 hover:text-red-700">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
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
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>