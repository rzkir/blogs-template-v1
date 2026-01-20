<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/CategoriesController.php';
require_once __DIR__ . '/../components/Card.php';

$controller = new PostController($db);
$categoriesController = new CategoriesController($db);

// Get category slug from URL
$categorySlug = $_GET['slug'] ?? '';

if (empty($categorySlug)) {
    // If no category specified, redirect to home
    header('Location: /');
    exit;
}

// Get category information
$category = $categoriesController->getBySlug($categorySlug);

if (!$category) {
    // Category not found
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

// Get posts by category
$posts = $controller->getByCategorySlug($categorySlug);

// Get all categories for sidebar
$categories = $categoriesController->getAll();

$pageTitle = $category['name'] . ' - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-6">
    <!-- Category Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <a href="/" class="text-gray-500 hover:text-red-600 transition-colors">
                <i class="fas fa-home"></i> Beranda
            </a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($category['name']); ?></span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($category['name']); ?></h1>
        <p class="text-gray-600">
            <?php echo count($posts); ?> artikel ditemukan
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
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
                    <h2 class="text-xl font-bold text-gray-900">Berita <?php echo htmlspecialchars($category['name']); ?></h2>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Berita</h3>
                        <p class="text-gray-500">Belum ada artikel dalam kategori <?php echo htmlspecialchars($category['name']); ?>.</p>
                        <a href="/" class="inline-block mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Kembali ke Beranda
                        </a>
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
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 space-y-6">
            <!-- Trending Topics -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-1 w-8 bg-red-600"></div>
                    <h3 class="text-lg font-bold text-gray-900">Trending</h3>
                </div>
                <div class="space-y-3">
                    <?php
                    $trendingPosts = array_slice($posts, 0, 5);
                    if (!empty($trendingPosts)):
                        foreach ($trendingPosts as $index => $post):
                    ?>
                            <?php renderCard($post, 'trending', $index + 1); ?>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <p class="text-sm text-gray-500 px-3 py-2">Belum ada artikel trending</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Popular Categories -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-1 w-8 bg-red-600"></div>
                    <h3 class="text-lg font-bold text-gray-900">Kategori</h3>
                </div>
                <div class="space-y-2">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <a href="/category/?slug=<?php echo htmlspecialchars($cat['categories_id']); ?>"
                                class="block px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 rounded transition-colors <?php echo $cat['categories_id'] === $categorySlug ? 'bg-red-50 text-red-600 font-semibold' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 px-3 py-2">Belum ada kategori</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>