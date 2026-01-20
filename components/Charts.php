<?php

/**
 * Charts Component
 * Displays post statistics charts
 * 
 * @param array $postsStats Statistics data for charts
 * @param int $totalPosts Total number of posts
 */
?>

<!-- Posts Statistics Charts -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
        <div class="flex items-center justify-between">
            <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-chart-pie text-sky-600"></i>
                Statistik Post
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
        <?php if ($totalPosts == 0): ?>
            <div class="py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                        <i class="fas fa-chart-pie text-2xl text-slate-400"></i>
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Posts by Status Chart -->
                <div class="bg-white rounded-xl border border-slate-200/50 p-4 sm:p-6">
                    <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-donut text-purple-600"></i>
                        Post Berdasarkan Status
                    </h4>
                    <div class="relative h-64">
                        <?php if (!empty($postsStats['by_status'])): ?>
                            <canvas id="statusChart"></canvas>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-slate-400">
                                <p class="text-sm">Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Posts by Category Chart -->
                <div class="bg-white rounded-xl border border-slate-200/50 p-4 sm:p-6">
                    <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-600"></i>
                        Post Berdasarkan Kategori
                    </h4>
                    <div class="relative h-64">
                        <?php if (!empty($postsStats['by_category'])): ?>
                            <canvas id="categoryChart"></canvas>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-slate-400">
                                <p class="text-sm">Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Posts by Month Chart -->
                <div class="bg-white rounded-xl border border-slate-200/50 p-4 sm:p-6 lg:col-span-2">
                    <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-green-600"></i>
                        Trend Post (6 Bulan Terakhir)
                    </h4>
                    <div class="relative h-64">
                        <?php if (!empty($postsStats['by_month'])): ?>
                            <canvas id="monthChart"></canvas>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-slate-400">
                                <p class="text-sm">Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Views by Month Chart -->
                <div class="bg-white rounded-xl border border-slate-200/50 p-4 sm:p-6 lg:col-span-2">
                    <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-eye text-indigo-600"></i>
                        Trend Views (6 Bulan Terakhir)
                    </h4>
                    <div class="relative h-64">
                        <?php if (!empty($postsStats['views_by_month'])): ?>
                            <canvas id="viewsChart"></canvas>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-slate-400">
                                <p class="text-sm">Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart Data (for JavaScript) -->
<script>
    window.postsChartData = {
        status: {
            labels: <?php echo json_encode(array_keys($postsStats['by_status'] ?? [])); ?>,
            data: <?php echo json_encode(array_values($postsStats['by_status'] ?? [])); ?>
        },
        category: {
            labels: <?php echo json_encode(array_column($postsStats['by_category'] ?? [], 'name')); ?>,
            data: <?php echo json_encode(array_column($postsStats['by_category'] ?? [], 'count')); ?>
        },
        month: {
            labels: <?php echo json_encode(array_map(function ($m) {
                        try {
                            $date = DateTime::createFromFormat('Y-m', $m['month']);
                            return $date ? $date->format('M Y') : $m['month'];
                        } catch (Exception $e) {
                            return $m['month'];
                        }
                    }, $postsStats['by_month'] ?? [])); ?>,
            data: <?php echo json_encode(array_column($postsStats['by_month'] ?? [], 'count')); ?>
        },
        views: {
            labels: <?php echo json_encode(array_map(function ($m) {
                        try {
                            $date = DateTime::createFromFormat('Y-m', $m['month']);
                            return $date ? $date->format('M Y') : $m['month'];
                        } catch (Exception $e) {
                            return $m['month'];
                        }
                    }, $postsStats['views_by_month'] ?? [])); ?>,
            data: <?php echo json_encode(array_column($postsStats['views_by_month'] ?? [], 'total_views')); ?>
        }
    };
</script>