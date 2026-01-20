<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Kontak - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6 md:py-10">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-6" aria-label="Breadcrumb">
            <a href="/" class="breadcrumb__link">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <span class="breadcrumb__divider">/</span>
            <span class="breadcrumb__current">Kontak</span>
        </nav>

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-envelope text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Hubungi Kami</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Punya pertanyaan, saran, atau kerja sama? Hubungi kami melalui informasi berikut.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</p>
                        <a href="mailto:redaksi@blognews.com" class="text-sm text-gray-600 dark:text-gray-400 hover:text-red-600">redaksi@blognews.com</a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600">
                        <i class="fab fa-whatsapp"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</p>
                        <a href="https://wa.me/6285811668557" class="text-sm text-gray-600 dark:text-gray-400 hover:text-red-600" target="_blank" rel="noopener noreferrer">+62 858-1166-8557</a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Jakarta, Indonesia</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Waktu Respon</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Senin–Jumat, 09:00–17:00 WIB</p>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600 mt-0.5">
                        <i class="fas fa-share-alt"></i>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Media Sosial</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="https://www.instagram.com/rzkir.20" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-instagram"></i> Instagram
                            </a>
                            <a href="https://github.com/rzkir" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-github"></i> GitHub
                            </a>
                            <a href="https://www.tiktok.com/@rizkiramadhan.20" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-tiktok"></i> TikTok
                            </a>
                            <a href="https://www.linkedin.com/in/rizki-ramadhan12" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-linkedin-in"></i> LinkedIn
                            </a>
                            <a href="https://wa.me/6285811668557" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://www.facebook.com/rizki.ramadhan.419859" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-200 dark:hover:border-red-900/40 transition-colors" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>