<?php
$pageTitle = 'Syarat & Ketentuan - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <header class="mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold mb-3">
                <i class="fas fa-file-contract"></i>
                <span>Legal</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">Syarat & Ketentuan</h1>
            <p class="text-gray-600">Terakhir diperbarui: <?php echo date('d M Y'); ?></p>
        </header>

        <article class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 sm:p-7">
            <div class="post-content">
                <p>
                    Dengan mengakses dan menggunakan situs <strong>Blog News</strong>, Anda setuju untuk terikat oleh Syarat & Ketentuan ini.
                    Jika Anda tidak setuju, mohon untuk tidak menggunakan situs ini.
                </p>

                <h2>Penggunaan Layanan</h2>
                <ul>
                    <li><strong>Tujuan wajar</strong>: gunakan situs ini untuk tujuan yang sah dan tidak melanggar hukum.</li>
                    <li><strong>Akun admin</strong>: akses admin hanya untuk pihak berwenang. Dilarang mencoba mengakses tanpa izin.</li>
                </ul>

                <h2>Konten</h2>
                <ul>
                    <li><strong>Kepemilikan</strong>: konten (artikel, desain, logo) dilindungi hak cipta dan/atau hak kekayaan intelektual.</li>
                    <li><strong>Penggunaan ulang</strong>: dilarang menyalin/menyebarkan ulang konten tanpa izin, kecuali diizinkan oleh hukum (mis. kutipan wajar dengan atribusi).</li>
                </ul>

                <h2>Larangan</h2>
                <ul>
                    <li>Melakukan scraping berlebihan, spam, atau mengganggu stabilitas layanan.</li>
                    <li>Menyisipkan kode berbahaya, mencoba eksploitasi, atau tindakan yang merugikan.</li>
                    <li>Mengunggah/menyebarkan konten yang melanggar hukum atau hak pihak lain melalui fitur yang tersedia.</li>
                </ul>

                <h2>Tautan Pihak Ketiga</h2>
                <p>
                    Situs ini dapat memuat tautan ke situs pihak ketiga. Kami tidak bertanggung jawab atas isi, kebijakan,
                    maupun praktik situs pihak ketiga tersebut.
                </p>

                <h2>Penafian (Disclaimer)</h2>
                <p>
                    Informasi disajikan “sebagaimana adanya”. Kami berupaya menyajikan konten akurat dan terkini,
                    namun tidak menjamin sepenuhnya bebas dari kesalahan atau keterlambatan pembaruan.
                </p>

                <h2>Pembatasan Tanggung Jawab</h2>
                <p>
                    Sejauh diizinkan oleh hukum, Blog News tidak bertanggung jawab atas kerugian langsung maupun tidak langsung
                    yang timbul dari penggunaan situs ini.
                </p>

                <h2>Perubahan Syarat</h2>
                <p>
                    Kami dapat mengubah Syarat & Ketentuan ini kapan saja. Perubahan berlaku sejak dipublikasikan di halaman ini.
                </p>

                <h2>Kontak</h2>
                <p>
                    Untuk pertanyaan terkait Syarat & Ketentuan, silakan hubungi kami melalui halaman <a href="/kontak">Kontak</a>.
                </p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>