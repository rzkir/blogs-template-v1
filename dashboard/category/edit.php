<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriesController.php';

$user = $_SESSION['user'];
$controller = new CategoriesController($db);

// Get category ID from query string
$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    $_SESSION['error'] = 'ID kategori tidak ditemukan.';
    header('Location: /dashboard/category/index.php');
    exit;
}

// Get category data
$category = $controller->getById((int)$categoryId);

if (!$category) {
    $_SESSION['error'] = 'Kategori tidak ditemukan.';
    header('Location: /dashboard/category/index.php');
    exit;
}

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in max-w-2xl">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <a href="/dashboard/category/index.php"
                        class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                        Edit Kategori
                    </h2>
                </div>
                <p class="text-slate-600 mt-2 text-sm sm:text-base ml-11">
                    Edit informasi kategori
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-edit text-sky-600"></i>
                        Form Edit Kategori
                    </h3>
                </div>
                <form action="/dashboard/category/process.php" method="POST" class="p-4 sm:p-6 space-y-6">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required
                            value="<?php echo htmlspecialchars($category['name']); ?>"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none"
                            placeholder="Masukkan nama kategori">
                        <p class="mt-1 text-xs text-slate-500">Nama kategori akan ditampilkan di blog. Categories ID akan di-generate otomatis dari nama kategori.</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200/50">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            Update Kategori
                        </button>
                        <a href="/dashboard/category/index.php"
                            class="flex-1 px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-semibold text-center">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>

</html>