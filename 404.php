<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure correct HTTP status for 404 page
http_response_code(404);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/CategoriesController.php';

$controller = new PostController($db);
$categoriesController = new CategoriesController($db);

// Get popular posts
$popularPosts = $controller->getPopular(5);

// Get categories
$categories = $categoriesController->getAll();

$pageTitle = '404 - Halaman Tidak Ditemukan';
include __DIR__ . '/components/Header.php';
?>

<!-- 404 Content -->
<main class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Left Side - Illustration -->
            <div class="order-2 lg:order-1">
                <div class="relative">
                    <!-- Large 404 Text -->
                    <div class="text-center lg:text-left">
                        <h1 class="text-[120px] sm:text-[180px] lg:text-[200px] font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-red-600 to-red-700 leading-none select-none">
                            404
                        </h1>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full">
                            <div class="flex justify-center lg:justify-start">
                                <div class="relative">
                                    <i class="fas fa-search text-6xl sm:text-7xl text-red-500/20 animate-pulse"></i>
                                    <i class="fas fa-times-circle absolute -top-2 -right-2 text-2xl text-red-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Content -->
            <div class="order-1 lg:order-2 text-center lg:text-left">
                <div class="inline-block px-4 py-2 bg-red-100 text-red-600 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Oops!
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Halaman Tidak Ditemukan
                </h2>

                <p class="text-base sm:text-lg text-gray-600 mb-6 leading-relaxed">
                    Maaf, halaman yang Anda cari tidak dapat ditemukan. Halaman mungkin telah dipindahkan,
                    dihapus, atau URL yang Anda masukkan salah.
                </p>

                <!-- Search Box -->
                <div class="mb-6">
                    <form action="/search" method="GET" class="relative">
                        <input type="text"
                            name="q"
                            placeholder="Cari artikel di sini..."
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                            required>
                        <button type="submit"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-8">
                    <a href="/"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <i class="fas fa-home"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <a href="javascript:history.back()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:border-red-600 hover:text-red-600 transition-all">
                        <i class="fas fa-arrow-left"></i>
                        <span>Halaman Sebelumnya</span>
                    </a>
                </div>

                <!-- Quick Links -->
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-compass mr-2 text-red-600"></i>Link Cepat:
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a href="/" class="inline-block px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-red-50 hover:text-red-600 transition-colors">
                            <i class="fas fa-home text-xs mr-1"></i>Beranda
                        </a>
                        <?php if (!empty($categories)): ?>
                            <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                                <?php if (!empty($category['slug'])): ?>
                                    <a href="/category/?slug=<?php echo htmlspecialchars($category['slug']); ?>"
                                        class="inline-block px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-red-50 hover:text-red-600 transition-colors">
                                        <?php echo htmlspecialchars($category['name'] ?? ''); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Posts Section -->
        <?php if (!empty($popularPosts)): ?>
            <div class="mt-16">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <div class="h-1 w-12 bg-red-600"></div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-fire text-orange-500"></i> Artikel Popular
                        </h3>
                        <div class="h-1 w-12 bg-red-600"></div>
                    </div>
                    <p class="text-gray-600">Mungkin Anda tertarik dengan artikel berikut</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    <?php foreach ($popularPosts as $post): ?>
                        <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all transform hover:scale-105 group">
                            <a href="/blog/?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block">
                                <div class="relative h-40 overflow-hidden">
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-4xl text-white/30"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($post['category_name'])): ?>
                                        <div class="absolute top-2 left-2">
                                            <span class="px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded">
                                                <?php echo htmlspecialchars($post['category_name']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h4 class="text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors mb-2">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h4>
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

        <!-- Help Section -->
        <div class="mt-12 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-gray-900/30 border border-red-100/70 dark:border-red-900/30 rounded-2xl p-8 text-center">
            <i class="fas fa-question-circle text-4xl text-red-600 dark:text-red-400 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Butuh Bantuan?</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                Jika Anda yakin halaman ini seharusnya ada, silakan hubungi kami atau laporkan masalah ini.
            </p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="mailto:support@example.com"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-gray-700 hover:text-red-600 dark:hover:text-red-400 transition-colors shadow-sm">
                    <i class="fas fa-envelope"></i>
                    <span class="text-sm font-medium">Hubungi Kami</span>
                </a>
                <a href="/"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-gray-700 hover:text-red-600 dark:hover:text-red-400 transition-colors shadow-sm">
                    <i class="fas fa-bug"></i>
                    <span class="text-sm font-medium">Laporkan Masalah</span>
                </a>
            </div>
        </div>
    </div>
</main>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>

<?php include __DIR__ . '/components/Footer.php'; ?>