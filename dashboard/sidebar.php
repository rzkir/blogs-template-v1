<?php
// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
$baseUrl = '/dashboard';
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white/95 backdrop-blur-md shadow-xl border-r border-slate-200/50 sidebar-transition -translate-x-full lg:translate-x-0">
    <nav class="px-4 py-6 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        <!-- Dashboard Menu Item -->
        <a href="<?php echo $baseUrl; ?>"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo $currentPage === 'index.php' ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-home w-5 <?php echo $currentPage === 'index.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Posts Menu Item -->
        <a href="<?php echo $baseUrl; ?>/post"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo (strpos($_SERVER['PHP_SELF'], '/post/') !== false) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-newspaper w-5 <?php echo (strpos($_SERVER['PHP_SELF'], '/post/') !== false) ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Posts</span>
        </a>

        <!-- Categories Menu Item -->
        <a href="<?php echo $baseUrl; ?>/category"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo (strpos($_SERVER['PHP_SELF'], '/category/') !== false) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-folder-tree w-5 <?php echo (strpos($_SERVER['PHP_SELF'], '/category/') !== false) ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Categories</span>
        </a>

        <a href="<?php echo $baseUrl; ?>/tags"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo (strpos($_SERVER['PHP_SELF'], '/tags/') !== false) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-tags w-5 <?php echo (strpos($_SERVER['PHP_SELF'], '/tags/') !== false) ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Tags</span>
        </a>

        <!-- Users Menu Item -->
        <a href="<?php echo $baseUrl; ?>/users"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo $currentPage === 'users.php' ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-users w-5 <?php echo $currentPage === 'users.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Users</span>
        </a>

        <!-- Settings Menu Item -->
        <a href="<?php echo $baseUrl; ?>/settings"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo $currentPage === 'settings.php' ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-cog w-5 <?php echo $currentPage === 'settings.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Settings</span>
        </a>

        <!-- Logs Menu Item -->
        <a href="<?php echo $baseUrl; ?>/logs"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo $currentPage === 'logs.php' ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-history w-5 <?php echo $currentPage === 'logs.php' ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Activity Logs</span>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200/50 bg-slate-50/50">
        <div class="text-xs text-slate-500 text-center">
            <p class="font-medium">&copy; <?php echo date('Y'); ?> Blog Template V1</p>
            <p class="mt-1 text-slate-400">Version 1.0.0</p>
        </div>
    </div>
</aside>

<script>
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const menuBtn = document.getElementById('mobile-menu-btn');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if (menuBtn) {
            menuBtn.addEventListener('click', toggleSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when clicking a link on mobile
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });
    });
</script>