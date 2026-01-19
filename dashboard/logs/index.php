<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$user = $_SESSION['user'];

// Pagination
$perPage = 50;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Total logs
$countResult = $db->query("SELECT COUNT(*) as total FROM logs");
$totalLogs = (int)($countResult->fetch_assoc()['total'] ?? 0);
$totalPages = $totalLogs > 0 ? (int)ceil($totalLogs / $perPage) : 1;
$page = min(max(1, $page), $totalPages);
$offset = ($page - 1) * $perPage;

// Get logs with user info
$logs = [];
$result = $db->query("
    SELECT l.*, a.fullname, a.email 
    FROM logs l 
    LEFT JOIN accounts a ON l.user_id = a.id 
    ORDER BY l.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8 relative overflow-hidden rounded-2xl bg-white/90 backdrop-blur-sm border border-slate-200/60 shadow-lg shadow-slate-200/50">
                <!-- Decorative gradient -->
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50/60 via-transparent to-sky-50/40 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-sky-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl pointer-events-none"></div>

                <div class="relative px-5 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <!-- Left: Title block -->
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25 ring-4 ring-amber-500/10">
                            <i class="fas fa-history text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Activity Logs
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Riwayat aktivitas login, register, dan aksi lainnya
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-stream"></i>
                                <span>Audit trail & keamanan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Stats & actions -->
                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-700"><?php echo number_format($totalLogs); ?></span>
                            <span class="text-slate-500 text-sm">logs</span>
                        </div>
                        <?php if ($totalLogs > 0): ?>
                            <form action="/dashboard/logs/process.php" method="POST" class="inline"
                                onsubmit="return confirm('Hapus SEMUA <?php echo number_format($totalLogs); ?> logs?\n\nTindakan ini tidak dapat dibatalkan.');">
                                <input type="hidden" name="action" value="delete_all">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 hover:border-red-300 rounded-xl transition-all duration-200 hover:shadow-md">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Hapus Semua</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-history text-sky-600"></i>
                        Daftar Logs
                    </h3>
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
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-inbox text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Tidak ada data logs</p>
                                            <p class="text-slate-400 text-sm mt-1">Aktivitas akan tercatat di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $index => $log): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                                    <?php echo ($log['fullname'] ?? null) ? strtoupper(substr($log['fullname'], 0, 1)) : 'G'; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800 truncate">
                                                        <?php echo ($log['fullname'] ?? null) ? htmlspecialchars($log['fullname']) : '<span class="text-slate-400 italic">Guest</span>'; ?>
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
                                                } elseif (strpos($log['action'], 'failed') !== false || strpos($log['action'], 'error') !== false || strpos($log['action'], 'blocked') !== false) {
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
                                        <td class="px-4 sm:px-6 py-4">
                                            <form action="/dashboard/logs/process.php" method="POST" class="inline"
                                                onsubmit="return confirm('Hapus log ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$log['id']; ?>">
                                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                <button type="submit"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-4 sm:px-6 py-4 border-t border-slate-200/50 bg-slate-50/30 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm text-slate-600">
                            Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>
                            <span class="text-slate-400">(<?php echo number_format($totalLogs); ?> total)</span>
                        </p>
                        <div class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                                </a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-sky-500 to-blue-600 rounded-lg hover:shadow-lg transition-all">
                                    Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>

</html>