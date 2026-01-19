<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Berlangganan - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6 md:py-10">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-red-600 transition-colors"><i class="fas fa-home"></i> Beranda</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">Berlangganan</span>
        </div>

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-envelope-open-text text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Berlangganan Newsletter</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Dapatkan berita terbaru, artikel pilihan, dan update langsung di inbox Anda. Gratis!
            </p>
        </div>

        <!-- Form & Benefits -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="#" method="POST" class="space-y-4" onsubmit="return false;">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="nama@email.com">
                    </div>
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama (opsional)</label>
                        <input type="text" id="nama" name="nama"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="Nama Anda">
                    </div>
                    <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Berlangganan Sekarang
                    </button>
                </form>
            </div>
            <div class="px-6 md:px-8 pb-6 md:pb-8">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Dengan berlangganan, Anda akan mendapat:</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-red-600 w-4"></i> Ringkasan berita harian</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-red-600 w-4"></i> Artikel pilihan redaksi</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-red-600 w-4"></i> Kabar terbaru tanpa spam</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-red-600 w-4"></i> Bisa berhenti kapan saja</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>