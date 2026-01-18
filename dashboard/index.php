<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$user = $_SESSION['user'];
$authController = new AuthController($db);

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

include __DIR__ . '/header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                    Dashboard
                </h2>
                <p class="text-slate-600 mt-2 text-sm sm:text-base">
                    Selamat datang, <span class="font-semibold text-sky-600"><?php echo htmlspecialchars($user['fullname']); ?></span>! 👋
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
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
            </div>

            <!-- Recent Activity Logs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
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
        </div>
    </main>
</div>

</body>

</html>