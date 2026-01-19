<?php
// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = '/dashboard';

// Check if we're exactly on the dashboard index (not in subdirectories)
$isDashboardActive = preg_match('#^/dashboard/?$|^/dashboard/index\.php$#', $requestUri);
?>

<?php $user = $user ?? $_SESSION['user'] ?? null; ?>
<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white/95 backdrop-blur-md shadow-xl border-r border-slate-200/50 sidebar-transition -translate-x-full lg:translate-x-0 flex flex-col">
    <!-- User Profile Block -->
    <div class="px-4 pt-4 pb-3 border-b border-slate-200/50 flex-shrink-0">
        <a href="#" class="flex items-center gap-3 p-2 -mx-2 rounded-xl hover:bg-slate-100 transition-colors group">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-semibold shadow-md flex-shrink-0">
                <?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></p>
                <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            </div>
        </a>
    </div>

    <nav class="px-4 py-4 space-y-1 overflow-y-auto flex-1 min-h-0">
        <!-- Dashboard Menu Item -->
        <a href="<?php echo $baseUrl; ?>"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo $isDashboardActive ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-home w-5 <?php echo $isDashboardActive ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Post Management Section -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Post Management</p>
        </div>

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

        <!-- Profile Section -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Profile</p>
        </div>

        <!-- Profile Menu Item -->
        <a href="<?php echo $baseUrl; ?>/profile"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo (strpos($_SERVER['PHP_SELF'], '/profile') !== false) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-user w-5 <?php echo (strpos($_SERVER['PHP_SELF'], '/profile') !== false) ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Profile</span>
        </a>

        <!-- Activity Logs Management Section -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Activity Logs Management</p>
        </div>

        <!-- Logs Menu Item -->
        <a href="<?php echo $baseUrl; ?>/logs"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?php echo (strpos($_SERVER['PHP_SELF'], '/logs') !== false) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/30' : 'text-slate-700 hover:bg-slate-100 hover:text-sky-600'; ?>">
            <i class="fas fa-history w-5 <?php echo (strpos($_SERVER['PHP_SELF'], '/logs') !== false) ? '' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="font-medium">Activity Logs</span>
        </a>

        <!-- Kembali ke Home -->
        <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-700 hover:bg-slate-100 hover:text-red-600">
            <i class="fas fa-globe w-5 group-hover:scale-110 transition-transform"></i>
            <span class="font-medium">Kembali ke Home</span>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-200/50 bg-slate-50/50 flex-shrink-0">
        <form action="/dashboard/process.php" method="POST" class="mb-3">
            <input type="hidden" name="action" value="logout">
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
        <div class="text-xs text-slate-500 text-center">
            <p class="font-medium">&copy; <?php echo date('Y'); ?> Blog Template V1</p>
            <p class="mt-1 text-slate-400">Version 1.0.0</p>
        </div>
    </div>
</aside>