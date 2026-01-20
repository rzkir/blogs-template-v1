<?php
require_once __DIR__ . '/../config/security.php';
app_enforce_https();
app_secure_session_start();
$cspNonce = app_send_security_headers([
    'noindex' => true,
    'csp' => 'auth',
]);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$successMessage = $_SESSION['success'] ?? '';
$errorMessage   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: /dashboard');
    exit;
}

$authController = new AuthController($db);
$attemptStatus = $authController->getLoginAttemptStatus();
$isBlocked = $attemptStatus['blocked'];
$blockedUntil = $attemptStatus['blocked_until'];
$attempts = $attemptStatus['attempts'];

if ($isBlocked && $errorMessage === '') {
    $errorMessage = 'Terlalu banyak percobaan login gagal dari IP ini. Coba lagi setelah 15 menit.';
}

$csrfToken = app_csrf_get_token();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Blog News</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
</head>

<body class="min-h-screen flex items-center justify-center px-4 py-10 bg-slate-100" style="font-family: 'Inter', sans-serif;">
    <div class="relative w-full max-w-md">
        <!-- background decoration (pakai warna yang sudah ada: merah/abu) -->
        <div aria-hidden="true" class="pointer-events-none absolute -inset-6 rounded-[28px] bg-gradient-to-br from-red-600/10 via-transparent to-transparent blur-2xl"></div>

        <div class="relative bg-white shadow-xl rounded-2xl p-8 border border-slate-200">
            <div class="mb-6">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-red-600 shadow-md">
                    <span class="text-white text-lg font-extrabold">BN</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 text-center mb-1">Login Admin</h1>
                <p class="text-sm text-slate-500 text-center">Masuk untuk mengelola Blog News.</p>
            </div>

            <?php if ($isBlocked): ?>
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Login dibatasi untuk IP ini. Silakan tunggu sampai limit berakhir.
                </div>
            <?php endif; ?>

            <form action="/process.php" method="POST" autocomplete="on" class="space-y-4"
                <?php echo $isBlocked ? 'onsubmit="return false;"' : ''; ?>>
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email atau Nama
                        Lengkap</label>
                    <input type="text" name="email" id="email" required autocomplete="username"
                        <?php echo $isBlocked ? 'disabled' : ''; ?>
                        placeholder="Masukkan email atau nama lengkap"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 disabled:bg-slate-100 disabled:text-slate-400" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                            <?php echo $isBlocked ? 'disabled' : ''; ?>
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm shadow-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 disabled:bg-slate-100 disabled:text-slate-400" />
                        <button type="button" id="togglePassword" <?php echo $isBlocked ? 'disabled' : ''; ?>
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" <?php echo $isBlocked ? 'disabled' : ''; ?>
                    class="w-full inline-flex justify-center items-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    <?php echo $isBlocked ? 'Login Disabled' : 'Masuk'; ?>
                </button>
            </form>
        </div>

        <p class="mt-6 text-xs text-center text-slate-400">
            &copy; <?php echo date('Y'); ?> Blog News. All rights reserved.
        </p>
    </div>

    <script<?php echo $cspNonce ? ' nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES) . '"' : ''; ?>>
        window.APP_MESSAGES = {
        success: <?php echo json_encode($successMessage); ?>,
        error: <?php echo json_encode($errorMessage); ?>,
        };
        </script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/toast.js"></script>
</body>

</html>