<?php

class BerlanggananController
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

    private function insertLog(?int $userId, string $action, ?string $description = null): int
    {
        $ipAddress = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $this->db->prepare(
            "INSERT INTO `logs` (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan log: ' . $this->db->error);
        }

        $stmt->bind_param('issss', $userId, $action, $description, $ipAddress, $userAgent);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal menyimpan log: ' . $err);
        }

        $logId = (int)$stmt->insert_id;
        $stmt->close();
        return $logId;
    }

    private function getSafeRedirect(string $fallback = '/berlangganan'): string
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if ($ref === '') {
            return $fallback;
        }

        $parts = parse_url($ref);
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';

        if ($path === '' || !str_starts_with($path, '/')) {
            return $fallback;
        }

        return $path . $query;
    }

    /**
     * Handle newsletter subscription.
     * Expects POST: email (required), nama (optional)
     */
    public function subscribe(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $redirectTo = $this->getSafeRedirect('/berlangganan');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $redirectTo);
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $nama = trim($_POST['nama'] ?? '');

        // Nama di DB NOT NULL, tapi UI "opsional" → fallback aman
        if ($nama === '') {
            $beforeAt = explode('@', $email)[0] ?? '';
            $nama = $beforeAt !== '' ? $beforeAt : 'Subscriber';
        }

        if ($email === '') {
            $_SESSION['error'] = 'Alamat email wajib diisi.';
            header('Location: ' . $redirectTo);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format email tidak valid.';
            header('Location: ' . $redirectTo);
            exit;
        }

        // Basic length guard
        if (mb_strlen($nama) > 255) {
            $nama = mb_substr($nama, 0, 255);
        }

        $txStarted = false;
        try {
            // cek sudah berlangganan
            $stmt = $this->db->prepare("SELECT id FROM `berlangganan` WHERE email = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Gagal menyiapkan query cek: ' . $this->db->error);
            }
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

            if ($existing) {
                // log attempt duplicate (tanpa insert berlangganan)
                try {
                    $this->insertLog($userId, 'subscribe_duplicate', 'Email sudah berlangganan: ' . $email);
                } catch (Throwable $e) {
                    error_log('Subscribe duplicate log error: ' . $e->getMessage());
                }

                $_SESSION['success'] = 'Email ini sudah terdaftar sebagai subscriber.';
                header('Location: ' . $redirectTo);
                exit;
            }

            // insert log + berlangganan harus konsisten karena FK logs NOT NULL
            $this->db->begin_transaction();
            $txStarted = true;

            $logId = $this->insertLog($userId, 'subscribe_newsletter', 'Berlangganan newsletter: ' . $email);

            $stmt = $this->db->prepare("INSERT INTO `berlangganan` (email, nama, logs) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Gagal menyiapkan query berlangganan: ' . $this->db->error);
            }
            $stmt->bind_param('ssi', $email, $nama, $logId);
            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new Exception('Gagal menyimpan data berlangganan: ' . $err);
            }
            $stmt->close();

            $this->db->commit();
            $txStarted = false;

            $_SESSION['success'] = 'Berhasil berlangganan. Terima kasih!';
            header('Location: ' . $redirectTo);
            exit;
        } catch (Throwable $e) {
            if ($txStarted) {
                try {
                    $this->db->rollback();
                } catch (Throwable $rollbackErr) {
                    // ignore rollback error
                }
            }

            error_log('Subscribe error: ' . $e->getMessage());

            $_SESSION['error'] = 'Terjadi kesalahan pada server. Coba lagi nanti.';
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}
