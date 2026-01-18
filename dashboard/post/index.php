<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/PostController.php';

$user = $_SESSION['user'];
$controller = new PostController($db);

// Get all posts
$posts = $controller->getAll();

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
                        Posts
                    </h2>
                    <p class="text-slate-600 mt-2 text-sm sm:text-base">
                        Kelola artikel blog Anda
                    </p>
                </div>
                <a href="/dashboard/post/create.php"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105 font-semibold">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Post</span>
                </a>
            </div>

            <!-- Posts Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-newspaper text-sky-600"></i>
                        Daftar Posts
                    </h3>
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
            </div>
        </div>
    </main>
</div>

</body>

</html>