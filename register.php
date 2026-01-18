<?php
session_start();

// Ambil dan tampilkan pesan sukses / error dari session (jika ada)
$successMessage = $_SESSION['success'] ?? '';
$errorMessage   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - PT Samson Sure</title>
    <link rel="icon" href="/favicon.ico">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Toastr CSS (tanpa integrity agar tidak diblokir) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center py-8">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <h1 class="text-2xl font-semibold text-slate-800 text-center mb-2">Register Admin</h1>
            <p class="text-sm text-slate-500 text-center mb-6">Buat akun admin untuk mengelola company profile.</p>

            <form action="process.php" method="POST" autocomplete="off" class="space-y-4">
                <input type="hidden" name="action" value="register">

                <div>
                    <label for="fullname" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="fullname" id="fullname" required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none" />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" minlength="6" required
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none" />
                        <button type="button" data-toggle-password="password"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg data-eye-icon="password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg data-eye-slash-icon="password" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Minimal 6 karakter.</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" minlength="6" required
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none" />
                        <button type="button" data-toggle-password="confirm_password"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg data-eye-icon="confirm_password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg data-eye-slash-icon="confirm_password" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full inline-flex justify-center items-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition-colors">
                    Daftar
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="login.php" class="text-sm text-sky-600 hover:text-sky-700 hover:underline">Sudah punya akun? Login di sini</a>
            </div>
        </div>

        <p class="mt-6 text-xs text-center text-slate-400">
            &copy; <?php echo date('Y'); ?> PT Samson Sure. All rights reserved.
        </p>
    </div>

    <!-- Inject session messages into JS -->
    <script>
        window.APP_MESSAGES = {
            success: <?php echo json_encode($successMessage); ?>,
            error: <?php echo json_encode($errorMessage); ?>,
        };
    </script>

    <!-- Toastr JS (tanpa integrity agar tidak diblokir) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/toast.js"></script>
</body>

</html>