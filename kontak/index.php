<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Kontak - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6 md:py-10">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-red-600 transition-colors"><i class="fas fa-home"></i> Beranda</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">Kontak</span>
        </div>

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-envelope text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Hubungi Kami</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Punya pertanyaan, saran, atau kerja sama? Silakan isi form di bawah.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Kontak Info -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
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
            </div>

            <!-- Form -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <form action="#" method="POST" class="space-y-4" onsubmit="return false;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                                <input type="text" id="nama" name="nama" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Nama Anda">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input type="email" id="email" name="email" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div>
                            <label for="subjek" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subjek</label>
                            <input type="text" id="subjek" name="subjek" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="Ringkasan pesan Anda">
                        </div>
                        <div>
                            <label for="pesan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesan</label>
                            <textarea id="pesan" name="pesan" rows="4" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                                placeholder="Tulis pesan Anda..."></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i> Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>