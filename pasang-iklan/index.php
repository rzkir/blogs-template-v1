<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Pasang Iklan - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6 md:py-10">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-red-600 transition-colors"><i class="fas fa-home"></i> Beranda</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">Pasang Iklan</span>
        </div>

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-bullhorn text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">Pasang Iklan</h1>
            <p class="text-gray-600 dark:text-gray-400">
                Jadikan Blog News mitra promosi Anda. Jangkau ribuan pembaca setiap hari.
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
            <form action="#" method="POST" class="space-y-4" onsubmit="return false;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama / Kontak</label>
                        <input type="text" id="nama" name="nama" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="Nama atau nama perusahaan">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="email@contoh.com">
                    </div>
                </div>
                <div>
                    <label for="jenis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Iklan</label>
                    <select id="jenis" name="jenis"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">— Pilih —</option>
                        <option value="banner">Banner</option>
                        <option value="sponsor">Sponsor Artikel</option>
                        <option value="native">Native Ads</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label for="pesan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesan / Kebutuhan</label>
                    <textarea id="pesan" name="pesan" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                        placeholder="Jelaskan kebutuhan iklan, durasi, budget (jika ada), dll."></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Permohonan
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
            Tim kami akan menghubungi Anda dalam 1–2 hari kerja.
        </p>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>