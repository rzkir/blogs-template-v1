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

// Get all categories
$categories = $controller->getAll();
$totalCategories = is_array($categories) ? count($categories) : 0;

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8 relative overflow-hidden rounded-2xl bg-white/90 backdrop-blur-sm border border-slate-200/60 shadow-lg shadow-slate-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50/60 via-transparent to-sky-50/40 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-sky-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl pointer-events-none"></div>

                <div class="relative px-5 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/25 ring-4 ring-purple-500/10">
                            <i class="fas fa-folder-open text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Categories
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Kelola kategori blog Anda
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-stream"></i>
                                <span>Organisasi konten</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-700"><?php echo number_format($totalCategories); ?></span>
                            <span class="text-slate-500 text-sm">kategori</span>
                        </div>
                        <button type="button" id="openCreateCategoryModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 rounded-xl hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Kategori</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-tags text-sky-600"></i>
                        Daftar Kategori
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Categories ID</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Dibuat Oleh</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Tanggal Dibuat</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-tags text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Belum ada kategori</p>
                                            <p class="text-slate-400 text-sm mt-1">Mulai dengan menambahkan kategori pertama</p>
                                            <button type="button" id="openCreateCategoryModalEmpty"
                                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                                                <i class="fas fa-plus"></i>
                                                <span>Tambah Kategori</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $index => $category): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                    <?php echo strtoupper(substr($category['name'], 0, 1)); ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <?php if (!empty($category['categories_id'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    <?php echo htmlspecialchars($category['categories_id']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-sm italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="text-sm text-slate-700">
                                                <?php echo htmlspecialchars($category['fullname'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <?php echo htmlspecialchars($category['email'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="text-xs sm:text-sm text-slate-600 whitespace-nowrap">
                                                <?php
                                                $date = new DateTime($category['created_at']);
                                                echo '<div class="font-medium">' . $date->format('d/m/Y') . '</div>';
                                                echo '<div class="text-slate-400 text-xs">' . $date->format('H:i:s') . '</div>';
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="openEditCategoryModal p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit"
                                                    data-category-id="<?php echo htmlspecialchars($category['id']); ?>"
                                                    data-category-name="<?php echo htmlspecialchars($category['name']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="/dashboard/category/process.php" method="POST" class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                    <button type="submit"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Create Category Modal -->
<div id="createCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="createCategoryBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-md shadow-sky-500/30 flex-shrink-0">
                <i class="fas fa-plus text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Tambah Kategori</h3>
                <p class="text-slate-600 text-sm sm:text-base">Buat kategori baru untuk blog Anda.</p>
            </div>
            <button type="button" id="closeCreateCategoryModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="/dashboard/category/process.php" id="createCategoryForm" class="px-6 py-6 space-y-5">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 gap-4">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-700">Nama Kategori <span class="text-red-500">*</span></span>
                    <input type="text" name="name" required
                        class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                        placeholder="Masukkan nama kategori">
                    <p class="text-xs text-slate-500">Nama kategori akan ditampilkan di blog. Categories ID akan di-generate otomatis dari nama kategori.</p>
                </label>
            </div>
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-xs text-slate-500">
                    Pastikan nama kategori unik dan mudah diingat.
                </div>
                <div class="flex gap-2">
                    <button type="button" id="cancelCreateCategory" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                        <i class="fas fa-save"></i>
                        <span>Simpan Kategori</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="editCategoryBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Edit Kategori</h3>
                <p class="text-slate-600 text-sm sm:text-base">Edit informasi kategori.</p>
            </div>
            <button type="button" id="closeEditCategoryModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="/dashboard/category/process.php" id="editCategoryForm" class="px-6 py-6 space-y-5">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editCategoryId">
            <div class="grid grid-cols-1 gap-4">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-700">Nama Kategori <span class="text-red-500">*</span></span>
                    <input type="text" name="name" id="editCategoryName" required
                        class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                        placeholder="Masukkan nama kategori">
                    <p class="text-xs text-slate-500">Nama kategori akan ditampilkan di blog. Categories ID akan di-generate otomatis dari nama kategori.</p>
                </label>
            </div>
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-xs text-slate-500">
                    Setelah disimpan, perubahan akan langsung diterapkan.
                </div>
                <div class="flex gap-2">
                    <button type="button" id="cancelEditCategory" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                        <i class="fas fa-save"></i>
                        <span>Update Kategori</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>

</html>