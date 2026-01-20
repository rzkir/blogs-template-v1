<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TagsController.php';

$user = $_SESSION['user'];
$controller = new TagsController($db);

// Pagination
$limit = 10; // Tags per page
$pageFromGet = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageFromGet = (int)$pageFromGet;

// Total tags
$totalTags = (int)$controller->getTotal();
$totalPages = $totalTags > 0 ? (int)ceil($totalTags / $limit) : 1;
$totalPages = max(1, $totalPages);
$currentPage = min($pageFromGet, $totalPages);
$offset = (int)(($currentPage - 1) * $limit);

// Get tags (paginated)
$tags = $controller->getAllPaginated($limit, $offset);

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8 relative overflow-hidden rounded-2xl bg-white/90 backdrop-blur-sm border border-slate-200/60 shadow-lg shadow-slate-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50/60 via-transparent to-red-50/40 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-red-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl pointer-events-none"></div>

                <div class="relative px-5 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-red-600 flex items-center justify-center shadow-lg ring-4 ring-red-500/10">
                            <i class="fas fa-tags text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Tags
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Kelola tag blog Anda
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-stream"></i>
                                <span>Metadata & pencarian</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-700"><?php echo number_format($totalTags); ?></span>
                            <span class="text-slate-500 text-sm">tag</span>
                        </div>
                        <button type="button" id="openCreateTagModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Tag</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tags Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-tags text-red-600"></i>
                        Daftar Tag
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Tags ID</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Dibuat Oleh</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Tanggal Dibuat</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($tags)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-tags text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Belum ada tag</p>
                                            <p class="text-slate-400 text-sm mt-1">Mulai dengan menambahkan tag pertama</p>
                                            <button type="button" id="openCreateTagModalEmpty"
                                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                                                <i class="fas fa-plus"></i>
                                                <span>Tambah Tag</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $index => $tag): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-lg bg-red-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                    <?php echo strtoupper(substr($tag['name'], 0, 1)); ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800">
                                                        <?php echo htmlspecialchars($tag['name']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <?php if (!empty($tag['tags_id'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    <?php echo htmlspecialchars($tag['tags_id']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-sm italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="text-sm text-slate-700">
                                                <?php echo htmlspecialchars($tag['fullname'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <?php echo htmlspecialchars($tag['email'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="text-xs sm:text-sm text-slate-600 whitespace-nowrap">
                                                <?php
                                                $date = new DateTime($tag['created_at']);
                                                echo '<div class="font-medium">' . $date->format('d/m/Y') . '</div>';
                                                echo '<div class="text-slate-400 text-xs">' . $date->format('H:i:s') . '</div>';
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="openEditTagModal p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit"
                                                    data-tag-id="<?php echo htmlspecialchars($tag['id']); ?>"
                                                    data-tag-name="<?php echo htmlspecialchars($tag['name']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="/dashboard/tags/process.php" method="POST" class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus tag ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
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
                                Menampilkan <span class="font-semibold"><?php echo min($offset + 1, $totalTags); ?></span> -
                                <span class="font-semibold"><?php echo min($offset + count($tags), $totalTags); ?></span> dari
                                <span class="font-semibold"><?php echo number_format($totalTags); ?></span> tag
                            </div>

                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <?php
                                $currentPageInt = (int)$currentPage;
                                $totalPagesInt = (int)$totalPages;

                                // Show all page numbers
                                for ($i = 1; $i <= $totalPagesInt; $i++):
                                    $pageUrl = '/dashboard/tags/index.php?page=' . $i;
                                ?>
                                    <?php if ($i === $currentPageInt): ?>
                                        <span class="px-3 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-md">
                                            <?php echo $i; ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($pageUrl); ?>"
                                            class="px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Create Tag Modal -->
<div id="createTagModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="createTagBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
            <div class="h-12 w-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                <i class="fas fa-plus text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Tambah Tag</h3>
                <p class="text-slate-600 text-sm sm:text-base">Buat tag baru untuk blog Anda.</p>
            </div>
            <button type="button" id="closeCreateTagModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="/dashboard/tags/process.php" id="createTagForm" class="px-6 py-6 space-y-5">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 gap-4">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-700">Nama Tag <span class="text-red-500">*</span></span>
                    <input type="text" name="name" required
                        class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                        placeholder="Masukkan nama tag">
                    <p class="text-xs text-slate-500">Nama tag akan ditampilkan di blog. Tags ID akan di-generate otomatis dari nama tag.</p>
                </label>
            </div>
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-xs text-slate-500">
                    Pastikan nama tag unik dan mudah diingat.
                </div>
                <div class="flex gap-2">
                    <button type="button" id="cancelCreateTag" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                        <i class="fas fa-save"></i>
                        <span>Simpan Tag</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Tag Modal -->
<div id="editTagModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="editTagBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
            <div class="h-12 w-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Edit Tag</h3>
                <p class="text-slate-600 text-sm sm:text-base">Edit informasi tag.</p>
            </div>
            <button type="button" id="closeEditTagModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="/dashboard/tags/process.php" id="editTagForm" class="px-6 py-6 space-y-5">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editTagId">
            <div class="grid grid-cols-1 gap-4">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-700">Nama Tag <span class="text-red-500">*</span></span>
                    <input type="text" name="name" id="editTagName" required
                        class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                        placeholder="Masukkan nama tag">
                    <p class="text-xs text-slate-500">Nama tag akan ditampilkan di blog. Tags ID akan di-generate otomatis dari nama tag.</p>
                </label>
            </div>
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-xs text-slate-500">
                    Setelah disimpan, perubahan akan langsung diterapkan.
                </div>
                <div class="flex gap-2">
                    <button type="button" id="cancelEditTag" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                        <i class="fas fa-save"></i>
                        <span>Update Tag</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>

</html>