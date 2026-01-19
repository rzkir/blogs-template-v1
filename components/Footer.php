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
    <footer class="bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h4 class="text-white font-bold mb-4">Tentang Kami</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="hover:text-white transition-colors">Tentang Blog</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Redaksi</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Pedoman Media</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Karir</a></li>
                    </ul>
                </div>

                <!-- Kategori -->
                <div>
                    <h4 class="text-white font-bold mb-4">Kategori</h4>
                    <ul class="space-y-2 text-sm">
                        <?php if (!empty($footerCategories)): ?>
                            <?php foreach ($footerCategories as $category): ?>
                                <li>
                                    <a href="/blog/?category=<?php echo htmlspecialchars($category['categories_id']); ?>"
                                        class="hover:text-white transition-colors">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-500 text-sm">Belum ada kategori</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Layanan -->
                <div>
                    <h4 class="text-white font-bold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="hover:text-white transition-colors">Berlangganan</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Pasang Iklan</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="/" class="hover:text-white transition-colors">Bantuan</a></li>
                    </ul>
                </div>

                <!-- Follow Us -->
                <div>
                    <h4 class="text-white font-bold mb-4">Ikuti Kami</h4>
                    <div class="flex gap-3 mb-4">
                        <a href="/" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="/" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <a href="/" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="/" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-youtube text-sm"></i>
                        </a>
                    </div>
                    <p class="text-xs text-gray-500">
                        Dapatkan update berita terbaru
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                    <p>&copy; <?php echo date('Y'); ?> Blog News. All rights reserved.</p>
                    <div class="flex gap-6">
                        <a href="/" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                        <a href="/" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                        <a href="/" class="hover:text-white transition-colors">Disclaimer</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Load main.js for theme switcher and other functionality -->
    <script src="/js/main.js"></script>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
    </body>

    </html>