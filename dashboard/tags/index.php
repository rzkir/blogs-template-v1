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

// Get all tags
$tags = $controller->getAll();

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                        Tags
                    </h2>
                    <p class="text-slate-600 mt-2 text-sm sm:text-base">
                        Kelola tag blog Anda
                    </p>
                </div>
                <a href="/dashboard/tags/create.php"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105 font-semibold">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Tag</span>
                </a>
            </div>

            <!-- Tags Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-tags text-sky-600"></i>
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
                                            <a href="/dashboard/tags/create.php"
                                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                                                <i class="fas fa-plus"></i>
                                                <span>Tambah Tag</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $index => $tag): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
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
                                                <a href="/dashboard/tags/edit.php?id=<?php echo $tag['id']; ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
            </div>
        </div>
    </main>
</div>

</body>

</html>