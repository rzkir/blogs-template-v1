<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/TagsController.php';
require_once __DIR__ . '/../components/Card.php';

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
                        <div class="lg:col-span-2 min-w-0 lg:gap-4 p-4">
                            <?php renderCard($headlinePost, 'headline'); ?>
                        </div>

                        <!-- Side Headlines -->
                        <?php if (!empty($otherPosts)): ?>
                            <div class="flex flex-col justify-between gap-3 lg:gap-4 p-4 lg:py-4 lg:px-0">
                                <?php foreach ($otherPosts as $index => $post): ?>
                                    <?php renderCard($post, 'side'); ?>
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
                                <?php renderCard($post, 'default'); ?>
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