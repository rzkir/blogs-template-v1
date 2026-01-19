<?php
$user = $_SESSION['user'] ?? null;
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blog Template V1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/style/style.css" />
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-50 min-h-screen">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-[45] lg:hidden hidden transition-opacity duration-300"></div>

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200/50 sticky top-0 z-[50]">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Mobile Menu Button & Logo -->
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="hidden lg:flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-newspaper text-white text-sm"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-lg">Dashboard</span>
                    </div>
                </div>

                <!-- Right Section: Back to Home & User Menu -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Back to Home Button -->
                    <a href="/"
                        class="hidden sm:flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                        <i class="fas fa-home"></i>
                        <span>Kembali ke Home</span>
                    </a>

                    <!-- Divider -->
                    <div class="hidden sm:block w-px h-6 bg-slate-300"></div>

                    <!-- Profile Dropdown -->
                    <div class="relative" id="profile-dropdown">
                        <!-- Profile Button -->
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-3 sm:gap-3 p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <!-- Desktop: Full Profile Info -->
                            <div class="hidden sm:flex items-center gap-3">
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-semibold shadow-lg ring-2 ring-red-200">
                                    <?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-slate-500 transition-transform duration-200" id="profile-chevron"></i>
                            </div>

                            <!-- Mobile: Avatar Only -->
                            <div class="sm:hidden flex items-center gap-2">
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                    <?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-slate-500 transition-transform duration-200" id="profile-chevron-mobile"></i>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown-menu"
                            class="absolute right-0 mt-2 w-56 sm:w-64 bg-white rounded-xl shadow-xl border border-slate-200/50 py-2 z-50 hidden">
                            <!-- Profile Info Section -->
                            <div class="px-4 py-3 border-b border-slate-200/50 bg-gradient-to-br from-red-50 to-red-100/50">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                        <?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Menu Items -->
                            <div class="py-1">
                                <a href="/" class="sm:hidden flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-home w-5 text-slate-400"></i>
                                    <span>Kembali ke Home</span>
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-user w-5 text-slate-400"></i>
                                    <span>Profile</span>
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-cog w-5 text-slate-400"></i>
                                    <span>Settings</span>
                                </a>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-slate-200/50 my-1"></div>

                            <!-- Logout -->
                            <div class="py-1">
                                <form action="process.php" method="POST" class="w-full">
                                    <input type="hidden" name="action" value="logout">
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors text-left font-medium">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        window.APP_MESSAGES = {
            success: <?php echo json_encode($successMessage); ?>,
            error: <?php echo json_encode($errorMessage); ?>,
        };
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/ai-endpoint.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/toast.js"></script>