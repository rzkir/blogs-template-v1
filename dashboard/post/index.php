<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/PostController.php';
require_once __DIR__ . '/../../controllers/CategoriesController.php';

$user = $_SESSION['user'];
$controller = new PostController($db);
$categoriesController = new CategoriesController($db);

// Filters (GET)
$selectedCategoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$selectedStatus = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
$selectedViewSort = isset($_GET['view']) ? trim((string)$_GET['view']) : '';

$allowedStatuses = ['published', 'draft', 'archived'];
if (!in_array($selectedStatus, $allowedStatuses, true)) {
    $selectedStatus = '';
}

$allowedViewSort = ['views_desc', 'views_asc'];
if (!in_array($selectedViewSort, $allowedViewSort, true)) {
    $selectedViewSort = '';
}

// Pagination
$limit = 10; // Posts per page
// Baca nilai page dari URL, pastikan integer dan minimal 1
$pageFromGet = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageFromGet = (int)$pageFromGet; // Ensure integer type

// Get categories for filter dropdown
$categories = $categoriesController->getAll();

// Get total posts count (with filters)
$totalPosts = (int)$controller->getTotalFiltered(
    $selectedCategoryId > 0 ? $selectedCategoryId : null,
    $selectedStatus !== '' ? $selectedStatus : null
);

// Calculate pagination
$totalPages = $totalPosts > 0 ? (int)ceil($totalPosts / $limit) : 1;
// Pastikan totalPages minimal 1
$totalPages = max(1, $totalPages);
// Pastikan page tidak melebihi totalPages untuk query, tapi tetap gunakan nilai dari GET untuk kondisi
$page = min($pageFromGet, $totalPages);
$currentPage = (int)$page;
$offset = (int)(($currentPage - 1) * $limit);

// Base query params untuk pagination (hindari pakai $_GET mentah)
$baseParams = [];
if ($selectedCategoryId > 0) {
    $baseParams['category'] = $selectedCategoryId;
}
if ($selectedStatus !== '') {
    $baseParams['status'] = $selectedStatus;
}
if ($selectedViewSort !== '') {
    $baseParams['view'] = $selectedViewSort;
}

// Get posts (with filters and pagination)
$posts = $controller->getAllFiltered(
    $selectedCategoryId > 0 ? $selectedCategoryId : null,
    $selectedStatus !== '' ? $selectedStatus : null,
    $selectedViewSort !== '' ? $selectedViewSort : null,
    $limit,
    $offset
);

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
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/25 ring-4 ring-sky-500/10">
                            <i class="fas fa-newspaper text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Posts
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Kelola artikel, status, dan performa blog Anda
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-stream"></i>
                                <span>Konten & publikasi</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-700"><?php echo number_format($totalPosts); ?></span>
                            <span class="text-slate-500 text-sm">posts</span>
                        </div>
                        <a href="/dashboard/post/create.php"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 rounded-xl hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Post</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Posts Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-newspaper text-sky-600"></i>
                            Daftar Posts
                        </h3>

                        <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
                            <input type="hidden" name="page" value="1">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-600">Category</label>
                                <select name="category" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-200">
                                    <option value="0">Semua</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$selectedCategoryId === (int)$cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-600">Status</label>
                                <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-200">
                                    <option value="">Semua</option>
                                    <option value="published" <?php echo $selectedStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $selectedStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="archived" <?php echo $selectedStatus === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-600">View</label>
                                <select name="view" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-200">
                                    <option value="">Default</option>
                                    <option value="views_desc" <?php echo $selectedViewSort === 'views_desc' ? 'selected' : ''; ?>>Terbanyak</option>
                                    <option value="views_asc" <?php echo $selectedViewSort === 'views_asc' ? 'selected' : ''; ?>>Tersedikit</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-sky-600 rounded-xl hover:bg-sky-700 transition-colors">
                                    <i class="fas fa-filter"></i>
                                    <span>Filter</span>
                                </button>
                                <a href="/dashboard/post/index.php" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                                    <i class="fas fa-rotate-left"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Judul</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Tags</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Status</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Views</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-newspaper text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Belum ada post</p>
                                            <p class="text-slate-400 text-sm mt-1">Mulai dengan menambahkan post pertama</p>
                                            <a href="/dashboard/post/create.php"
                                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                                                <i class="fas fa-plus"></i>
                                                <span>Tambah Post</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($posts as $index => $post): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                    <?php echo strtoupper(substr($post['title'], 0, 1)); ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500 mt-1">
                                                        <?php echo htmlspecialchars($post['slug']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <?php if (!empty($post['category_name'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                                                    <i class="fas fa-folder mr-1"></i>
                                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-sm italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <?php if (!empty($post['tags']) && is_array($post['tags'])): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <?php foreach (array_slice($post['tags'], 0, 3) as $tag): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                            <?php echo htmlspecialchars($tag['name']); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($post['tags']) > 3): ?>
                                                        <span class="text-xs text-slate-500">+<?php echo count($post['tags']) - 3; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-sm italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <?php
                                            $statusColors = [
                                                'published' => 'bg-green-100 text-green-700 border-green-200',
                                                'draft' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                'archived' => 'bg-slate-100 text-slate-700 border-slate-200'
                                            ];
                                            $statusColor = $statusColors[$post['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $statusColor; ?>">
                                                <?php echo ucfirst($post['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="flex items-center gap-1 text-sm text-slate-600">
                                                <i class="fas fa-eye text-slate-400"></i>
                                                <span class="font-medium"><?php echo number_format($post['views'] ?? 0); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="/dashboard/post/edit.php?id=<?php echo $post['id']; ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="/dashboard/post/process.php" method="POST" class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus post ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-4 sm:px-6 py-4 border-t border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-slate-600">
                                Menampilkan <span class="font-semibold"><?php echo min((int)$offset + 1, (int)$totalPosts); ?></span> -
                                <span class="font-semibold"><?php echo min((int)$offset + count($posts), (int)$totalPosts); ?></span> dari
                                <span class="font-semibold"><?php echo number_format((int)$totalPosts); ?></span> posts
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Page Numbers -->
                                <div class="flex items-center gap-1">
                                    <?php
                                    // Pastikan operasi aritmatika menggunakan integer
                                    $startPage = max(1, (int)$currentPage - 2);
                                    $endPage = min((int)$totalPages, (int)$currentPage + 2);

                                    if ($startPage > 1): ?>
                                        <?php
                                        $firstParams = $baseParams;
                                        $firstParams['page'] = 1;
                                        $firstUrl = '/dashboard/post/index.php?' . http_build_query($firstParams);
                                        ?>
                                        <a href="<?php echo htmlspecialchars($firstUrl); ?>"
                                            class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                            1
                                        </a>
                                        <?php if ($startPage > 2): ?>
                                            <span class="px-2 text-slate-400">...</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <?php
                                        $pageParams = $baseParams;
                                        $pageParams['page'] = (int)$i;
                                        $pageUrl = '/dashboard/post/index.php?' . http_build_query($pageParams);
                                        ?>
                                        <?php if ((int)$i === (int)$currentPage): ?>
                                            <span class="px-3 py-2 text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 rounded-lg shadow-md">
                                                <?php echo $i; ?>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($pageUrl); ?>"
                                                class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($endPage < $totalPages): ?>
                                        <?php if ($endPage < $totalPages - 1): ?>
                                            <span class="px-2 text-slate-400">...</span>
                                        <?php endif; ?>
                                        <?php
                                        $lastParams = $baseParams;
                                        $lastParams['page'] = (int)$totalPages;
                                        $lastUrl = '/dashboard/post/index.php?' . http_build_query($lastParams);
                                        ?>
                                        <a href="<?php echo htmlspecialchars($lastUrl); ?>"
                                            class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                            <?php echo $totalPages; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>

</html>