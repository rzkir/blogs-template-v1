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

<body class="bg-gradient-to-br from-slate-50 via-red-50/30 to-slate-50 min-h-screen">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-[45] lg:hidden hidden transition-opacity duration-300"></div>

    <!-- Mobile Menu Button (hanya tampil di mobile) -->
    <button id="mobile-menu-btn" class="lg:hidden fixed top-4 right-4 z-[50] p-2.5 rounded-xl bg-white/90 backdrop-blur shadow-md border border-slate-200/50 text-slate-600 hover:bg-slate-100 transition-colors">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <script>
        window.APP_MESSAGES = {
            success: <?php echo json_encode($successMessage); ?>,
            error: <?php echo json_encode($errorMessage); ?>,
        };
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/js/ai-endpoint.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/toast.js"></script>