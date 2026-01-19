<?php
// Get categories if not already set
if (!isset($footerCategories)) {
    if (!isset($db)) {
        require_once __DIR__ . '/../config/db.php';
    }
    if (!class_exists('CategoriesController')) {
        require_once __DIR__ . '/../controllers/CategoriesController.php';
    }
    $categoriesController = new CategoriesController($db);
    $footerCategories = $categoriesController->getAll();
}
?>

<!-- Footer -->
<footer class="footer relative mt-auto overflow-hidden">
    <!-- Accent line -->
    <div class="footer-accent"></div>

    <div class="container mx-auto px-4 py-10 sm:py-12 lg:py-14">
        <!-- Main grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 mb-10 lg:mb-12">
            <!-- Brand & About -->
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="/" class="inline-flex items-center gap-2 mb-4 group">
                    <span class="footer-logo">
                        <i class="fas fa-newspaper text-white"></i>
                    </span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Blog News</span>
                </a>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-xs mb-4">
                    Portal berita terpercaya dengan berbagai topik aktual. Dapatkan informasi terbaru dan terverifikasi.
                </p>
            </div>

            <!-- Kategori -->
            <div>
                <div class="footer-section-title">Kategori</div>
                <ul class="space-y-2.5 mt-3">
                    <?php if (!empty($footerCategories)): ?>
                        <?php foreach (array_slice($footerCategories, 0, 5) as $category): ?>
                            <li>
                                <a href="/category/?slug=<?php echo htmlspecialchars($category['categories_id']); ?>" class="footer-link">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-sm text-gray-500 dark:text-gray-400">Belum ada kategori</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Layanan -->
            <div>
                <div class="footer-section-title">Layanan</div>
                <ul class="space-y-2.5 mt-3">
                    <li><a href="/berlangganan" class="footer-link">Berlangganan</a></li>
                    <li><a href="/pasang-iklan" class="footer-link">Pasang Iklan</a></li>
                    <li><a href="/kontak" class="footer-link">Kontak</a></li>
                    <li><a href="/bantuan" class="footer-link">Bantuan</a></li>
                </ul>
            </div>

            <!-- Newsletter & Social -->
            <div>
                <div class="footer-section-title">Ikuti Kami</div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 mb-4">Dapatkan update berita terbaru langsung di inbox Anda.</p>

                <!-- Newsletter form -->
                <form class="footer-newsletter-form mb-6" action="/berlangganan/process.php" method="POST">
                    <div class="flex gap-2">
                        <input type="hidden" name="nama" value="">
                        <input type="email" name="email" placeholder="Alamat email" class="footer-newsletter-input" required>
                        <button type="submit" class="footer-newsletter-btn" aria-label="Berlangganan">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>

                <!-- Social links -->
                <div class="flex flex-wrap gap-3">
                    <a href="https://www.instagram.com/rzkir.20" class="footer-social" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://github.com/rzkir" class="footer-social" aria-label="GitHub" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://www.tiktok.com/@rizkiramadhan.20" class="footer-social" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/rizki-ramadhan12" class="footer-social" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://wa.me/6285811668557" class="footer-social" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/rizki.ramadhan.419859" class="footer-social" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="footer-bottom">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                <p class="text-gray-600 dark:text-gray-400 order-2 md:order-1">
                    &copy; 2026 - <?php echo date('Y'); ?> Blog News. All rights reserved.
                </p>
                <div class="flex flex-wrap justify-center items-center gap-4 md:gap-6 order-1 md:order-2">
                    <a href="/" class="footer-legal-link">Kebijakan Privasi</a>
                    <a href="/" class="footer-legal-link">Syarat & Ketentuan</a>
                    <a href="/" class="footer-legal-link">Disclaimer</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top - fixed -->
<a href="#" class="footer-back-top fixed z-40 right-4 bottom-20 md:bottom-6 md:right-6" id="footerBackToTop" aria-label="Kembali ke atas">
    <i class="fas fa-arrow-up"></i>
</a>

</body>

</html>