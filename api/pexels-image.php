<?php

/**
 * Proxy: cari gambar di Pexels berdasarkan kata kunci (prompt).
 * Dipanggil dari editor konten (create/edit post) saat tombol Gambar.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$q = trim((string) ($_GET['q'] ?? $_GET['query'] ?? ''));
if ($q === '') {
    echo json_encode(['error' => 'Parameter q (kata kunci) wajib diisi.']);
    exit;
}

$config = @include __DIR__ . '/../config/pexels.php';
$apiKey = $config['api_key'] ?? '';
if ($apiKey === '' || $apiKey === 'GANTI_DENGAN_PEXELS_API_KEY_ANDA') {
    echo json_encode(['error' => 'Pexels API key belum diatur. Atur di config/pexels.php.']);
    exit;
}

$url = 'https://api.pexels.com/v1/search?query=' . urlencode($q) . '&per_page=5';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: ' . $apiKey],
    CURLOPT_TIMEOUT        => 10,
]);
$body = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200) {
    echo json_encode(['error' => 'Pexels API gagal (HTTP ' . $code . ').']);
    exit;
}

$data = json_decode($body, true);
$photos = $data['photos'] ?? [];
if (empty($photos)) {
    echo json_encode(['error' => 'Tidak ada gambar ditemukan untuk: ' . $q]);
    exit;
}

$photo = $photos[0];
$src = $photo['src'] ?? [];
$imgUrl = $src['large'] ?? $src['medium'] ?? $src['original'] ?? '';
if ($imgUrl === '') {
    echo json_encode(['error' => 'URL gambar tidak tersedia.']);
    exit;
}

echo json_encode(['url' => $imgUrl, 'photographer' => $photo['photographer'] ?? '']);
