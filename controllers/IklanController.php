<?php

class IklanController
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

    private function getSafeRedirect(string $fallback = '/pasang-iklan'): string
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
     * Handle iklan submission.
     * Expects POST: nama (required), email (required), telepon (required), jenis (required), pesan (required)
     */
    public function submit(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $redirectTo = $this->getSafeRedirect('/pasang-iklan');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $redirectTo);
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $jenis = trim($_POST['jenis'] ?? '');
        $pesan = trim($_POST['pesan'] ?? '');

        // Validation
        if ($nama === '') {
            $_SESSION['error'] = 'Nama / Kontak wajib diisi.';
            header('Location: ' . $redirectTo);
            exit;
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

        if ($telepon === '') {
            $_SESSION['error'] = 'Nomor telepon wajib diisi.';
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($jenis === '') {
            $_SESSION['error'] = 'Jenis iklan wajib dipilih.';
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($pesan === '') {
            $_SESSION['error'] = 'Pesan / Kebutuhan wajib diisi.';
            header('Location: ' . $redirectTo);
            exit;
        }

        // Basic length guards
        if (mb_strlen($nama) > 255) {
            $nama = mb_substr($nama, 0, 255);
        }
        if (mb_strlen($email) > 255) {
            $email = mb_substr($email, 0, 255);
        }
        if (mb_strlen($telepon) > 255) {
            $telepon = mb_substr($telepon, 0, 255);
        }
        if (mb_strlen($jenis) > 255) {
            $jenis = mb_substr($jenis, 0, 255);
        }

        $txStarted = false;
        try {
            $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

            // Insert log + iklan must be consistent because FK logs NOT NULL
            $this->db->begin_transaction();
            $txStarted = true;

            $logDescription = sprintf(
                'Permohonan iklan: %s (%s) - Jenis: %s',
                $nama,
                $email,
                $jenis
            );
            $logId = $this->insertLog($userId, 'submit_iklan', $logDescription);

            $stmt = $this->db->prepare("INSERT INTO `iklan` (nama, email, telepon, jenis, pesan, logs) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Gagal menyiapkan query iklan: ' . $this->db->error);
            }
            $stmt->bind_param('sssssi', $nama, $email, $telepon, $jenis, $pesan, $logId);
            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new Exception('Gagal menyimpan data iklan: ' . $err);
            }
            $stmt->close();

            $this->db->commit();
            $txStarted = false;

            $_SESSION['success'] = 'Permohonan iklan berhasil dikirim. Tim kami akan menghubungi Anda dalam 1-2 hari kerja.';
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

            error_log('Iklan submit error: ' . $e->getMessage());

            $_SESSION['error'] = 'Terjadi kesalahan pada server. Coba lagi nanti.';
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}
