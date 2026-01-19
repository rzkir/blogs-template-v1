<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PostController.php';

$user = $_SESSION['user'];
$authController = new AuthController($db);
$postController = new PostController($db);

// Get statistics
$stats = [];

// Total Users
$result = $db->query("SELECT COUNT(*) as total FROM accounts");
$stats['total_users'] = $result->fetch_assoc()['total'] ?? 0;

// Total Logs
$result = $db->query("SELECT COUNT(*) as total FROM logs");
$stats['total_logs'] = $result->fetch_assoc()['total'] ?? 0;

// Recent Logs (last 24 hours)
$result = $db->query("SELECT COUNT(*) as total FROM logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stats['recent_logs'] = $result->fetch_assoc()['total'] ?? 0;

// Total Login Attempts (failed)
$result = $db->query("SELECT COUNT(*) as total FROM login_attempts WHERE attempts > 0");
$stats['failed_attempts'] = $result->fetch_assoc()['total'] ?? 0;

// Get recent activity logs
$recentLogs = [];
$result = $db->query("
    SELECT l.*, a.fullname, a.email 
    FROM logs l 
    LEFT JOIN accounts a ON l.user_id = a.id 
    ORDER BY l.created_at DESC 
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentLogs[] = $row;
    }
}

// Get recent posts (limit 10)
$recentPosts = $postController->getAllFiltered(null, null, null, 10, 0);

// Get total posts count
$totalPosts = $postController->getTotalFiltered(null, null);

include __DIR__ . '/header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

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
                            <i class="fas fa-tachometer-alt text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Dashboard
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Selamat datang, <span class="font-semibold text-sky-600"><?php echo htmlspecialchars($user['fullname']); ?></span>! 👋
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-chart-line"></i>
                                <span>Ringkasan aktivitas</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-700"><?php echo number_format($totalPosts); ?></span>
                            <span class="text-slate-500 text-sm">posts</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Total Users Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 p-5 sm:p-6 card-hover group overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/20 to-blue-600/20 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wide mb-1">Total Users</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($stats['total_users']); ?></p>
                            <p class="text-xs text-slate-400 mt-1">Active accounts</p>
                        </div>
                        <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Logs Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 p-5 sm:p-6 card-hover group overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/20 to-green-600/20 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wide mb-1">Total Logs</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($stats['total_logs']); ?></p>
                            <p class="text-xs text-slate-400 mt-1">All activities</p>
                        </div>
                        <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-history text-white text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 p-5 sm:p-6 card-hover group overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-400/20 to-orange-600/20 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wide mb-1">Aktivitas 24 Jam</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($stats['recent_logs']); ?></p>
                            <p class="text-xs text-slate-400 mt-1">Last 24 hours</p>
                        </div>
                        <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clock text-white text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Failed Attempts Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 p-5 sm:p-6 card-hover group overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-400/20 to-red-600/20 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wide mb-1">Failed Attempts</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($stats['failed_attempts']); ?></p>
                            <p class="text-xs text-slate-400 mt-1">Security alerts</p>
                        </div>
                        <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-exclamation-triangle text-white text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Posts Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 p-5 sm:p-6 card-hover group overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-400/20 to-purple-600/20 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wide mb-1">Total Posts</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($totalPosts); ?></p>
                            <p class="text-xs text-slate-400 mt-1">All articles</p>
                        </div>
                        <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-newspaper text-white text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Logs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden mb-6 sm:mb-8">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-history text-sky-600"></i>
                            Aktivitas Terkini
                        </h3>
                        <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1 rounded-full">10 terbaru</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">User</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Action</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Description</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">IP Address</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-inbox text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Tidak ada aktivitas terbaru</p>
                                            <p class="text-slate-400 text-sm mt-1">Aktivitas akan muncul di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $index => $log): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                                    <?php echo $log['fullname'] ? strtoupper(substr($log['fullname'], 0, 1)) : 'G'; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800 truncate">
                                                        <?php echo $log['fullname'] ? htmlspecialchars($log['fullname']) : '<span class="text-slate-400 italic">Guest</span>'; ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500 truncate hidden sm:block">
                                                        <?php echo htmlspecialchars($log['email'] ?? 'N/A'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm
                                                <?php
                                                if (strpos($log['action'], 'success') !== false) {
                                                    echo 'bg-green-100 text-green-700 border border-green-200';
                                                } elseif (strpos($log['action'], 'failed') !== false || strpos($log['action'], 'error') !== false) {
                                                    echo 'bg-red-100 text-red-700 border border-red-200';
                                                } else {
                                                    echo 'bg-blue-100 text-blue-700 border border-blue-200';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <div class="text-sm text-slate-700 max-w-xs truncate" title="<?php echo htmlspecialchars($log['description'] ?? '-'); ?>">
                                                <?php echo htmlspecialchars($log['description'] ?? '-'); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                                            <div class="text-xs text-slate-600 font-mono bg-slate-50 px-2 py-1 rounded border border-slate-200 inline-block">
                                                <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="text-xs sm:text-sm text-slate-600 whitespace-nowrap">
                                                <?php
                                                $date = new DateTime($log['created_at']);
                                                echo '<div class="font-medium">' . $date->format('d/m/Y') . '</div>';
                                                echo '<div class="text-slate-400 text-xs">' . $date->format('H:i:s') . '</div>';
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-newspaper text-sky-600"></i>
                            Post Terbaru
                        </h3>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                                Total: <?php echo number_format($totalPosts); ?>
                            </span>
                            <a href="/dashboard/post/index.php"
                                class="text-xs text-sky-600 hover:text-sky-700 font-semibold flex items-center gap-1">
                                <span>Lihat Semua</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <?php if (empty($recentPosts)): ?>
                        <div class="py-12 text-center">
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
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <?php foreach ($recentPosts as $post): ?>
                                <?php
                                $statusColors = [
                                    'published' => 'bg-green-100 text-green-700 border-green-200',
                                    'draft' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'archived' => 'bg-slate-100 text-slate-700 border-slate-200'
                                ];
                                $statusColor = $statusColors[$post['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                ?>
                                <div class="bg-white rounded-xl border border-slate-200/50 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                                    <!-- Card Header with Image/Icon -->
                                    <div class="relative aspect-video overflow-hidden bg-slate-100">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent"></div>
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center">
                                                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
                                                <div class="relative z-10 h-16 w-16 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                                    <?php echo strtoupper(substr($post['title'], 0, 1)); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute top-3 right-3 z-10">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $statusColor; ?> bg-white/90 backdrop-blur-sm shadow-sm">
                                                <?php echo ucfirst($post['status']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="p-4 sm:p-5">
                                        <!-- Title -->
                                        <h4 class="text-base font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-sky-600 transition-colors">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </h4>

                                        <!-- Slug -->
                                        <p class="text-xs text-slate-500 mb-3 truncate">
                                            <?php echo htmlspecialchars($post['slug']); ?>
                                        </p>

                                        <!-- Category -->
                                        <?php if (!empty($post['category_name'])): ?>
                                            <div class="mb-3">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                                                    <i class="fas fa-folder mr-1"></i>
                                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Tags -->
                                        <?php if (!empty($post['tags']) && is_array($post['tags'])): ?>
                                            <div class="flex flex-wrap gap-1.5 mb-4">
                                                <?php foreach (array_slice($post['tags'], 0, 3) as $tag): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                        <?php echo htmlspecialchars($tag['name']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($post['tags']) > 3): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-slate-500 bg-slate-100">
                                                        +<?php echo count($post['tags']) - 3; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Footer with Stats and Actions -->
                                        <div class="flex items-center justify-between pt-3 border-t border-slate-200/50">
                                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                                <i class="fas fa-eye text-slate-400"></i>
                                                <span class="font-medium"><?php echo number_format($post['views'] ?? 0); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a href="/dashboard/post/edit.php?id=<?php echo $post['id']; ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                <a href="/dashboard/post/index.php"
                                                    class="p-2 text-sky-600 hover:bg-sky-50 rounded-lg transition-colors"
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>

</html>