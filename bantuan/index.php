<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Bantuan - Blog News';
include __DIR__ . '/../components/Header.php';

$faqs = [
    [
        'q' => 'Bagaimana cara berlangganan newsletter?',
        'a' => 'Kunjungi halaman Berlangganan, masukkan email dan nama (opsional), lalu klik "Berlangganan Sekarang". Anda akan menerima konfirmasi di email.',
    ],
    [
        'q' => 'Bagaimana cara mencari artikel?',
        'a' => 'Gunakan kotak pencarian di header, atau kunjungi halaman Blog dan filter berdasarkan kategori atau tag. Anda juga bisa langsung mengetik kata kunci di URL /search?q=kata-kunci.',
    ],
    [
        'q' => 'Apakah berita di Blog News gratis?',
        'a' => 'Ya, semua konten dapat diakses secara gratis. Berlangganan newsletter juga tidak dipungut biaya.',
    ],
    [
        'q' => 'Bagaimana cara pasang iklan?',
        'a' => 'Isi form di halaman Pasang Iklan dengan data Anda dan kebutuhan iklan. Tim kami akan menghubungi dalam 1–2 hari kerja untuk diskusi paket dan penempatan.',
    ],
    [
        'q' => 'Saya menemukan konten yang tidak pantas. Ke mana melapor?',
        'a' => 'Silakan hubungi kami melalui halaman Kontak dengan subjek "Laporan Konten" dan jelaskan link serta alasannya. Kami akan meninjau segera.',
    ],
];
?>

<main class="container mx-auto px-4 py-6 md:py-10">
    <div class="max-w-3xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-6" aria-label="Breadcrumb">
            <a href="/" class="breadcrumb__link">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <span class="breadcrumb__divider">/</span>
            <span class="breadcrumb__current">Bantuan</span>
        </nav>

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-question-circle text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Pusat Bantuan</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Temukan jawaban atas pertanyaan yang sering diajukan. Masih ada yang ingin ditanyakan? <a href="/kontak" class="text-red-600 hover:underline">Hubungi kami</a>.
            </p>
        </div>

        <!-- FAQ -->
        <div class="space-y-4">
            <?php foreach ($faqs as $i => $faq): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button type="button" class="faq-toggle w-full flex items-center justify-between gap-4 p-4 md:p-5 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" aria-expanded="false" data-faq-target="faq-<?php echo $i; ?>">
                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($faq['q']); ?></span>
                        <i class="fas fa-chevron-down text-red-600 flex-shrink-0 transition-transform duration-200"></i>
                    </button>
                    <div id="faq-<?php echo $i; ?>" class="faq-content hidden border-t border-gray-200 dark:border-gray-700">
                        <div class="p-4 md:p-5 pt-2 md:pt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base leading-relaxed">
                            <?php echo htmlspecialchars($faq['a']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="mt-10 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Tidak menemukan jawaban yang Anda cari?</p>
            <a href="/kontak" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                <i class="fas fa-envelope"></i> Kirim Pertanyaan
            </a>
        </div>
    </div>
</main>

<script>
    (document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.faq-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-faq-target');
                var target = document.getElementById(targetId);
                var icon = this.querySelector('.fa-chevron-down');
                if (!target) return;
                var isOpen = !target.classList.contains('hidden');
                document.querySelectorAll('.faq-content').forEach(function(el) {
                    el.classList.add('hidden');
                });
                document.querySelectorAll('.faq-toggle .fa-chevron-down').forEach(function(el) {
                    el.classList.remove('rotate-180');
                });
                if (!isOpen) {
                    target.classList.remove('hidden');
                    if (icon) icon.classList.add('rotate-180');
                }
            });
        });
    }))();
</script>

<?php include __DIR__ . '/../components/Footer.php'; ?>