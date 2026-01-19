<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$user = $_SESSION['user'];
$authController = new AuthController($db);

// Handle submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = $authController->changePassword(
            (int)$user['id'],
            $currentPassword,
            $newPassword,
            $confirmPassword
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        header('Location: /dashboard/profile');
        exit;
    }

    if ($action === 'change_name') {
        $newFullname = $_POST['fullname'] ?? '';

        $result = $authController->changeName((int)$user['id'], $newFullname);

        if (!empty($result['success']) && !empty($result['fullname'])) {
            // sync session so header/sidebar updates instantly
            $_SESSION['user']['fullname'] = $result['fullname'];
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        header('Location: /dashboard/profile');
        exit;
    }
}

// Ambil data dari database
$profile = null;
$stmt = $db->prepare("SELECT id, fullname, email, role FROM accounts WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $user['id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
    }
    $stmt->close();
}
// Fallback ke session jika query gagal
if (!$profile) {
    $profile = [
        'id' => $user['id'],
        'fullname' => $user['fullname'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}
$profile['created_at'] = $profile['created_at'] ?? null;
$profile['updated_at'] = $profile['updated_at'] ?? null;

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in">
            <!-- Profile Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <!-- Header dengan avatar -->
                <div class="px-6 py-8 sm:py-10 bg-gradient-to-br from-sky-50 via-white to-blue-50/50 border-b border-slate-200/50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-48 h-48 bg-sky-200/30 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <div class="relative flex flex-col sm:flex-row items-center sm:items-start gap-6">
                        <div class="h-24 w-24 sm:h-28 sm:w-28 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold shadow-lg shadow-sky-500/30 ring-4 ring-white/80 flex-shrink-0">
                            <?php echo strtoupper(substr($profile['fullname'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div class="text-center sm:text-left flex-1">
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-800">
                                <?php echo htmlspecialchars($profile['fullname']); ?>
                            </h3>
                            <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($profile['email']); ?></p>
                            <span class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                <i class="fas fa-shield-alt mr-1.5"></i>
                                <?php echo htmlspecialchars(ucfirst($profile['role'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detail -->
                <div class="px-6 py-5 sm:py-6 divide-y divide-slate-200/50">
                    <div class="flex flex-col sm:flex-row sm:items-center py-4 first:pt-0 gap-2 sm:gap-4">
                        <span class="text-slate-500 text-sm font-medium sm:w-36 flex-shrink-0">Nama lengkap</span>
                        <span class="text-slate-800 font-medium"><?php echo htmlspecialchars($profile['fullname']); ?></span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center py-4 gap-2 sm:gap-4">
                        <span class="text-slate-500 text-sm font-medium sm:w-36 flex-shrink-0">Email</span>
                        <span class="text-slate-800 font-medium"><?php echo htmlspecialchars($profile['email']); ?></span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center py-4 gap-2 sm:gap-4">
                        <span class="text-slate-500 text-sm font-medium sm:w-36 flex-shrink-0">Role</span>
                        <span class="text-slate-800 font-medium"><?php echo htmlspecialchars(ucfirst($profile['role'])); ?></span>
                    </div>
                    <?php if (!empty($profile['created_at'])): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center py-4 gap-2 sm:gap-4">
                            <span class="text-slate-500 text-sm font-medium sm:w-36 flex-shrink-0">Bergabung</span>
                            <span class="text-slate-700">
                                <?php
                                $d = new DateTime($profile['created_at']);
                                echo $d->format('d F Y');
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['updated_at'])): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center py-4 gap-2 sm:gap-4">
                            <span class="text-slate-500 text-sm font-medium sm:w-36 flex-shrink-0">Terakhir diubah</span>
                            <span class="text-slate-700">
                                <?php
                                $d = new DateTime($profile['updated_at']);
                                echo $d->format('d F Y, H:i');
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Change Name -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden mt-6 sm:mt-8">
                    <div class="px-6 py-5 sm:py-6 border-b border-slate-200/50 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-md shadow-sky-500/30">
                            <i class="fas fa-user-pen text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg sm:text-xl font-bold text-slate-800">Ubah Nama</h3>
                            <p class="text-slate-600 text-sm sm:text-base">Perbarui nama lengkap akun Anda.</p>
                        </div>
                        <button type="button" id="openChangeNameModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-edit"></i>
                            <span>Ganti Nama</span>
                        </button>
                    </div>
                    <div class="px-6 py-6 text-sm text-slate-600">
                        Nama akan ditampilkan di dashboard. Minimal 3 karakter.
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden mt-6 sm:mt-8">
                    <div class="px-6 py-5 sm:py-6 border-b border-slate-200/50 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-md shadow-amber-400/30">
                            <i class="fas fa-key text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg sm:text-xl font-bold text-slate-800">Ubah Password</h3>
                            <p class="text-slate-600 text-sm sm:text-base">Perbarui kata sandi akun Anda.</p>
                        </div>
                        <button type="button" id="openChangePasswordModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-edit"></i>
                            <span>Ganti Password</span>
                        </button>
                    </div>
                    <div class="px-6 py-6 text-sm text-slate-600">
                        Demi keamanan, perubahan password dilakukan lewat modal.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="changePasswordBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
            <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-md shadow-amber-400/30 flex-shrink-0">
                    <i class="fas fa-key text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800">Ubah Password</h3>
                    <p class="text-slate-600 text-sm sm:text-base">Masukkan password lama dan password baru.</p>
                </div>
                <button type="button" id="closeChangePasswordModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" id="changePasswordForm" class="px-6 py-6 space-y-5">
                <input type="hidden" name="action" value="change_password">
                <div class="grid grid-cols-1 gap-4">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Password saat ini</span>
                        <input type="password" name="current_password" required
                            class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                            placeholder="Masukkan password lama">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Password baru</span>
                        <input type="password" name="new_password" minlength="6" required
                            class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                            placeholder="Minimal 6 karakter">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Konfirmasi password baru</span>
                        <input type="password" name="confirm_password" minlength="6" required
                            class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                            placeholder="Ulangi password baru">
                    </label>
                </div>
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="text-xs text-slate-500">
                        Pastikan password baru berbeda dari sebelumnya dan mudah diingat.
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="cancelChangePassword" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-save"></i>
                            <span>Simpan Password</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Name Modal -->
    <div id="changeNameModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="changeNameBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200/60 overflow-hidden animate-fade-in">
            <div class="px-6 py-5 border-b border-slate-200/60 flex items-start gap-3">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-md shadow-sky-500/30 flex-shrink-0">
                    <i class="fas fa-user-pen text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800">Ubah Nama</h3>
                    <p class="text-slate-600 text-sm sm:text-base">Masukkan nama lengkap baru Anda.</p>
                </div>
                <button type="button" id="closeChangeNameModal" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" id="changeNameForm" class="px-6 py-6 space-y-5">
                <input type="hidden" name="action" value="change_name">
                <div class="grid grid-cols-1 gap-4">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Nama lengkap</span>
                        <input type="text" name="fullname" required minlength="3" maxlength="100"
                            value="<?php echo htmlspecialchars($profile['fullname']); ?>"
                            class="w-full rounded-xl border border-slate-200/80 px-4 py-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all bg-white/90"
                            placeholder="Contoh: Rizki Pratama">
                    </label>
                </div>
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="text-xs text-slate-500">
                        Setelah disimpan, nama akan langsung diperbarui di dashboard.
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="cancelChangeName" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-200 transition-all duration-200">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-save"></i>
                            <span>Simpan Nama</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>

</html>