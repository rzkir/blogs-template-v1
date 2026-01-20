<?php

class AuthController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return $ip;
    }


    public function log(?int $userId, string $action, ?string $description = null): void
    {
        try {
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $stmt = $this->db->prepare(
                "INSERT INTO `logs` (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issss', $userId, $action, $description, $ipAddress, $userAgent);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log('Log error: ' . $e->getMessage());
        }
    }

    /**
     * Cek status block IP. Jika sudah lewat, auto reset.
     */
    private function getIpLockState(string $ip): array
    {
        // return: [blocked(bool), blocked_until(?string), attempts(int)]
        try {
            $stmt = $this->db->prepare("SELECT attempts, blocked_until FROM `login_attempts` WHERE ip_address = ? LIMIT 1");
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                return ['blocked' => false, 'blocked_until' => null, 'attempts' => 0];
            }

            $attempts = (int)($row['attempts'] ?? 0);
            $blockedUntil = $row['blocked_until'] ?? null;

            if ($blockedUntil !== null) {
                // blocked_until <= NOW() ? reset
                $stmt = $this->db->prepare("SELECT (blocked_until > NOW()) AS still_blocked FROM `login_attempts` WHERE ip_address = ? LIMIT 1");
                $stmt->bind_param('s', $ip);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stillBlocked = (int)($r['still_blocked'] ?? 0) === 1;

                if ($stillBlocked) {
                    return ['blocked' => true, 'blocked_until' => $blockedUntil, 'attempts' => $attempts];
                }

                // expired → reset
                $stmt = $this->db->prepare("UPDATE `login_attempts` SET attempts = 0, blocked_until = NULL WHERE ip_address = ?");
                $stmt->bind_param('s', $ip);
                $stmt->execute();
                $stmt->close();

                return ['blocked' => false, 'blocked_until' => null, 'attempts' => 0];
            }

            return ['blocked' => false, 'blocked_until' => null, 'attempts' => $attempts];
        } catch (Throwable $e) {
            error_log('Lock state error: ' . $e->getMessage());
            return ['blocked' => false, 'blocked_until' => null, 'attempts' => 0];
        }
    }

    /**
     * Tambah attempt gagal. Jika >= 3, set blocked_until = NOW()+15 menit.
     */
    private function recordFailedAttempt(string $ip): array
    {
        try {
            // upsert attempts
            $stmt = $this->db->prepare(
                "INSERT INTO `login_attempts` (ip_address, attempts, last_attempt_at) VALUES (?, 1, NOW()) "
                    . "ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_at = NOW()"
            );
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $stmt->close();

            // ambil attempts terbaru
            $stmt = $this->db->prepare("SELECT attempts, blocked_until FROM `login_attempts` WHERE ip_address = ? LIMIT 1");
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $attempts = (int)($row['attempts'] ?? 0);
            $blockedUntil = $row['blocked_until'] ?? null;

            if ($attempts >= 3 && $blockedUntil === null) {
                $stmt = $this->db->prepare("UPDATE `login_attempts` SET blocked_until = (NOW() + INTERVAL 15 MINUTE) WHERE ip_address = ?");
                $stmt->bind_param('s', $ip);
                $stmt->execute();
                $stmt->close();

                // refresh
                $stmt = $this->db->prepare("SELECT attempts, blocked_until FROM `login_attempts` WHERE ip_address = ? LIMIT 1");
                $stmt->bind_param('s', $ip);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $attempts = (int)($row['attempts'] ?? $attempts);
                $blockedUntil = $row['blocked_until'] ?? $blockedUntil;
            }

            return ['attempts' => $attempts, 'blocked_until' => $blockedUntil];
        } catch (Throwable $e) {
            error_log('Record failed attempt error: ' . $e->getMessage());
            return ['attempts' => 0, 'blocked_until' => null];
        }
    }

    private function clearAttempts(string $ip): void
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM `login_attempts` WHERE ip_address = ?");
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log('Clear attempts error: ' . $e->getMessage());
        }
    }

    /**
     * Get login attempt status for display purposes.
     * Returns: ['blocked' => bool, 'blocked_until' => ?string, 'attempts' => int]
     */
    public function getLoginAttemptStatus(?string $ip = null): array
    {
        if ($ip === null) {
            $ip = $this->getClientIp();
        }
        return $this->getIpLockState($ip);
    }

    public function register(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fullname        = trim($_POST['fullname'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullname === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $_SESSION['error'] = 'Semua field wajib diisi.';
            $this->log(null, 'register_failed', 'Field tidak lengkap untuk email ' . $email);
            header('Location: /register.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format email tidak valid.';
            $this->log(null, 'register_failed', 'Format email tidak valid: ' . $email);
            header('Location: /register.php');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Konfirmasi password tidak sama.';
            $this->log(null, 'register_failed', 'Konfirmasi password tidak sama untuk email ' . $email);
            header('Location: /register.php');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter.';
            $this->log(null, 'register_failed', 'Password terlalu pendek untuk email ' . $email);
            header('Location: /register.php');
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT id FROM `accounts` WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->close();
                $_SESSION['error'] = 'Email sudah terdaftar.';
                $this->log(null, 'register_failed', 'Email sudah terdaftar: ' . $email);
                header('Location: /register.php');
                exit;
            }
            $stmt->close();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->db->prepare("INSERT INTO `accounts` (fullname, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $fullname, $email, $hashedPassword);
            $stmt->execute();
            $newUserId = $stmt->insert_id;
            $stmt->close();

            $this->log($newUserId, 'register_success', 'Registrasi admin baru: ' . $email);

            $_SESSION['success'] = 'Registrasi berhasil. Silakan login.';
            header('Location: /login');
            exit;
        } catch (Throwable $e) {
            error_log('Register error: ' . $e->getMessage());
            $this->log(null, 'register_error', $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan pada server. Coba lagi nanti.';
            header('Location: /register.php');
            exit;
        }
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = $this->getClientIp();

        $lock = $this->getIpLockState($ip);
        if ($lock['blocked']) {
            $_SESSION['error'] = 'Terlalu banyak percobaan login gagal dari IP ini. Coba lagi setelah 15 menit.';
            header('Location: /login');
            exit;
        }

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email/Nama lengkap dan password wajib diisi.';
            header('Location: /login');
            exit;
        }

        try {
            // Cek apakah input adalah email atau fullname
            $stmt = $this->db->prepare("SELECT id, fullname, email, password, role, picture FROM `accounts` WHERE email = ? OR fullname = ? LIMIT 1");
            $stmt->bind_param('ss', $email, $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user || !password_verify($password, $user['password'])) {
                // catat gagal → jika sudah blocked, stop logging tambahan
                $state = $this->recordFailedAttempt($ip);

                $attempts = (int)($state['attempts'] ?? 0);
                $blockedUntil = $state['blocked_until'] ?? null;

                if ($blockedUntil !== null) {
                    // hanya log 1x saat mencapai limit
                    $this->log(null, 'login_blocked_ip', 'IP diblokir sampai ' . $blockedUntil);
                    $_SESSION['error'] = 'Terlalu banyak percobaan login gagal. Login dibatasi 15 menit.';
                } else {
                    $remaining = max(3 - $attempts, 0);
                    $this->log(null, 'login_failed', 'Kredensial salah untuk email/nama ' . $email);
                    $_SESSION['error'] = 'Email/Nama lengkap atau password salah. Sisa percobaan: ' . $remaining . 'x.';
                }

                header('Location: /login');
                exit;
            }

            if ($user['role'] !== 'admin') {
                $_SESSION['error'] = 'Akses ditolak. Akun ini bukan admin.';
                $this->log((int)$user['id'], 'login_denied', 'Role bukan admin untuk email/nama ' . $email);
                header('Location: /login');
                exit;
            }

            // sukses → reset attempt IP
            $this->clearAttempts($ip);

            $_SESSION['user'] = [
                'id'       => $user['id'],
                'fullname' => $user['fullname'],
                'email'    => $user['email'],
                'role'     => $user['role'],
                'picture'  => $user['picture'] ?? null,
            ];

            $this->log((int)$user['id'], 'login_success', 'Login admin berhasil untuk email/nama ' . $email);

            $_SESSION['success'] = 'Berhasil login sebagai admin.';
            header('Location: /dashboard');
            exit;
        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->log(null, 'login_error', $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan pada server. Coba lagi nanti.';
            header('Location: /login');
            exit;
        }
    }

    /**
     * Change user password helper.
     * Returns array: ['success' => bool, 'message' => string]
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return ['success' => false, 'message' => 'Semua field wajib diisi.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Konfirmasi password tidak cocok.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password baru minimal 6 karakter.'];
        }

        try {
            $stmt = $this->db->prepare("SELECT password FROM `accounts` WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row || !password_verify($currentPassword, $row['password'])) {
                return ['success' => false, 'message' => 'Password lama tidak benar.'];
            }

            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE `accounts` SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('si', $hashed, $userId);
            $stmt->execute();
            $stmt->close();

            $this->log($userId, 'change_password', 'User changed password');

            return ['success' => true, 'message' => 'Password berhasil diperbarui.'];
        } catch (Throwable $e) {
            error_log('Change password error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengubah password.'];
        }
    }

    /**
     * Change user fullname helper.
     * Returns array: ['success' => bool, 'message' => string, 'fullname' => ?string]
     */
    public function changeName(int $userId, string $newFullname): array
    {
        $newFullname = trim($newFullname);

        if ($newFullname === '') {
            return ['success' => false, 'message' => 'Nama lengkap wajib diisi.', 'fullname' => null];
        }

        // simple length guard to avoid abuse / accidental huge input
        if (mb_strlen($newFullname) < 3) {
            return ['success' => false, 'message' => 'Nama lengkap minimal 3 karakter.', 'fullname' => null];
        }
        if (mb_strlen($newFullname) > 100) {
            return ['success' => false, 'message' => 'Nama lengkap maksimal 100 karakter.', 'fullname' => null];
        }

        try {
            $stmt = $this->db->prepare("UPDATE `accounts` SET fullname = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('si', $newFullname, $userId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            // treat unchanged name as success (no-op)
            $this->log($userId, 'change_name', 'User changed fullname');

            if ($affected < 0) {
                return ['success' => false, 'message' => 'Gagal memperbarui nama.', 'fullname' => null];
            }

            return ['success' => true, 'message' => 'Nama berhasil diperbarui.', 'fullname' => $newFullname];
        } catch (Throwable $e) {
            error_log('Change name error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengubah nama.', 'fullname' => null];
        }
    }

    /**
     * Change user picture helper.
     * Returns array: ['success' => bool, 'message' => string, 'picture' => ?string]
     */
    public function changePicture(int $userId, ?array $file, ?string $existingPicture = null): array
    {
        // If no file uploaded, return error
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                return ['success' => false, 'message' => 'Silakan pilih gambar untuk diupload.', 'picture' => null];
            }
            return ['success' => false, 'message' => 'Gagal mengupload gambar. Error code: ' . $file['error'], 'picture' => null];
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $file['type'];
        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.', 'picture' => null];
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Ukuran gambar terlalu besar. Maksimal 5MB.', 'picture' => null];
        }

        try {
            // Create upload directory if not exists
            $uploadDir = __DIR__ . '/../uploads/images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return ['success' => false, 'message' => 'Gagal menyimpan gambar.', 'picture' => null];
            }

            // Return relative path for database
            $picturePath = '/uploads/images/' . $filename;

            // Update database
            $stmt = $this->db->prepare("UPDATE `accounts` SET picture = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('si', $picturePath, $userId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            // Delete old image if exists
            if ($existingPicture && $existingPicture !== '') {
                $oldImagePath = __DIR__ . '/..' . $existingPicture;
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $this->log($userId, 'change_picture', 'User changed profile picture');

            if ($affected < 0) {
                // If update failed, delete the uploaded file
                @unlink($uploadPath);
                return ['success' => false, 'message' => 'Gagal memperbarui gambar profil.', 'picture' => null];
            }

            return ['success' => true, 'message' => 'Gambar profil berhasil diperbarui.', 'picture' => $picturePath];
        } catch (Throwable $e) {
            error_log('Change picture error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengubah gambar profil.', 'picture' => null];
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user']['id'] ?? null;
        $this->log($userId, 'logout', 'User logged out');
        session_unset();
        session_destroy();
        http_response_code(200);
        exit;
    }
}
