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

// Total berlangganan
$countResult = $db->query("SELECT COUNT(*) as total FROM berlangganan");
$totalBerlangganan = (int)($countResult->fetch_assoc()['total'] ?? 0);
$totalPages = $totalBerlangganan > 0 ? (int)ceil($totalBerlangganan / $perPage) : 1;
$page = min(max(1, $page), $totalPages);
$offset = ($page - 1) * $perPage;

// Get berlangganan data
$berlangganan = [];
$result = $db->query("
    SELECT b.*, l.action, l.description, l.created_at as log_created_at
    FROM berlangganan b
    LEFT JOIN logs l ON b.logs = l.id
    ORDER BY b.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $berlangganan[] = $row;
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
                <div class="absolute inset-0 bg-gradient-to-br from-red-50/60 via-transparent to-red-50/40 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-red-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-red-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl pointer-events-none"></div>

                <div class="relative px-5 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <!-- Left: Title block -->
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-red-600 flex items-center justify-center shadow-lg ring-4 ring-red-500/10">
                            <i class="fas fa-envelope-open-text text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Data Berlangganan
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Daftar subscriber newsletter
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-users"></i>
                                <span>Newsletter subscribers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Stats -->
                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-100/80 border border-emerald-200/60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-semibold text-emerald-700"><?php echo number_format($totalBerlangganan); ?></span>
                            <span class="text-emerald-600 text-sm">subscribers</span>
                        </div>
                        <?php if ($totalBerlangganan > 0): ?>
                            <form action="/dashboard/berlangganan/process.php" method="POST" class="inline"
                                onsubmit="return confirm('Hapus SEMUA <?php echo number_format($totalBerlangganan); ?> data berlangganan?\n\nTindakan ini tidak dapat dibatalkan.');">
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

            <!-- Berlangganan Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-list text-emerald-600"></i>
                        Daftar Berlangganan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">No</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Action</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal Berlangganan</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            <?php if (empty($berlangganan)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                                <i class="fas fa-inbox text-2xl text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-500 font-medium">Tidak ada data berlangganan</p>
                                            <p class="text-slate-400 text-sm mt-1">Belum ada subscriber</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($berlangganan as $index => $item): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150 <?php echo $index % 2 === 0 ? 'bg-white/50' : ''; ?>">
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="text-sm font-semibold text-slate-700">
                                                <?php echo number_format(($page - 1) * $perPage + $index + 1); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                                    <?php echo strtoupper(substr($item['email'], 0, 1)); ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-slate-800 truncate">
                                                        <?php echo htmlspecialchars($item['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="text-sm text-slate-700">
                                                <?php echo htmlspecialchars($item['nama']); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm bg-red-100 text-red-700 border border-red-200">
                                                <?php echo htmlspecialchars($item['action'] ?? 'subscribe_newsletter'); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="text-xs sm:text-sm text-slate-600 whitespace-nowrap">
                                                <?php
                                                $date = new DateTime($item['created_at']);
                                                echo '<div class="font-medium">' . $date->format('d/m/Y') . '</div>';
                                                echo '<div class="text-slate-400 text-xs">' . $date->format('H:i:s') . '</div>';
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <?php
                                                // Format email subject dan body untuk newsletter
                                                $emailSubject = urlencode('Terima Kasih Telah Berlangganan Newsletter');
                                                $emailBody = urlencode('Halo ' . htmlspecialchars($item['nama']) . ",\n\nTerima kasih telah berlangganan newsletter Blog News!\n\nAnda akan menerima update berita terbaru, artikel pilihan, dan informasi menarik lainnya langsung di inbox Anda.\n\nSalam,\nTim Blog News");
                                                $emailLink = 'mailto:' . htmlspecialchars($item['email']) . '?subject=' . $emailSubject . '&body=' . $emailBody;
                                                ?>
                                                <a href="<?php echo $emailLink; ?>"
                                                    target="_blank"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Kirim Email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <form action="/dashboard/berlangganan/process.php" method="POST" class="inline"
                                                    onsubmit="return confirm('Hapus data berlangganan <?php echo htmlspecialchars($item['email']); ?>?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                                                    <input type="hidden" name="page" value="<?php echo $page; ?>">
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
                    <div class="px-4 sm:px-6 py-4 border-t border-slate-200/50 bg-slate-50/30 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm text-slate-600">
                            Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>
                            <span class="text-slate-400">(<?php echo number_format($totalBerlangganan); ?> total)</span>
                        </p>
                        <div class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                                </a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 hover:shadow-lg transition-all">
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