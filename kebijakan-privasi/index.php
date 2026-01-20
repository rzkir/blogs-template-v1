<?php
$pageTitle = 'Kebijakan Privasi - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <header class="mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold mb-3">
                <i class="fas fa-shield-alt"></i>
                <span>Legal</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">Kebijakan Privasi</h1>
            <p class="text-gray-600">Terakhir diperbarui: <?php echo date('d M Y'); ?></p>
        </header>

        <article class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 sm:p-7">
            <div class="post-content">
                <p>
                    Kebijakan Privasi ini menjelaskan bagaimana <strong>Blog News</strong> mengumpulkan, menggunakan, menyimpan,
                    dan melindungi informasi Anda ketika Anda mengakses situs kami atau menggunakan layanan yang tersedia.
                </p>

                <h2>Informasi yang Kami Kumpulkan</h2>
                <ul>
                    <li><strong>Informasi yang Anda kirimkan</strong>: seperti nama dan alamat email ketika Anda berlangganan newsletter atau menghubungi kami.</li>
                    <li><strong>Informasi teknis</strong>: seperti alamat IP, jenis perangkat, browser, halaman yang dikunjungi, dan waktu akses (log server).</li>
                </ul>

                <h2>Bagaimana Kami Menggunakan Informasi</h2>
                <ul>
                    <li><strong>Menyediakan layanan</strong>: memproses langganan, menanggapi pertanyaan, dan mengelola fitur situs.</li>
                    <li><strong>Peningkatan situs</strong>: analisis untuk meningkatkan pengalaman pengguna dan kualitas konten.</li>
                    <li><strong>Keamanan</strong>: mencegah aktivitas mencurigakan, spam, dan penyalahgunaan.</li>
                </ul>

                <h2>Cookies & Teknologi Serupa</h2>
                <p>
                    Kami dapat menggunakan cookies untuk membantu situs bekerja dengan baik, menyimpan preferensi, serta analitik penggunaan.
                    Anda dapat mengatur browser untuk menolak cookies, namun beberapa fitur mungkin tidak berfungsi optimal.
                </p>

                <h2>Berbagi Informasi kepada Pihak Ketiga</h2>
                <p>
                    Kami tidak menjual data pribadi Anda. Informasi dapat dibagikan secara terbatas hanya jika diperlukan untuk:
                    pemrosesan layanan (mis. email newsletter), kepatuhan hukum, atau perlindungan keamanan sistem.
                </p>

                <h2>Keamanan Data</h2>
                <p>
                    Kami menerapkan langkah-langkah keamanan yang wajar untuk melindungi data. Namun, tidak ada metode transmisi atau
                    penyimpanan yang 100% aman. Kami mendorong Anda untuk menjaga keamanan data akun/akses Anda.
                </p>

                <h2>Hak Anda</h2>
                <ul>
                    <li><strong>Akses & perbaikan</strong>: meminta akses atau koreksi atas data Anda.</li>
                    <li><strong>Berhenti berlangganan</strong>: Anda dapat berhenti menerima email kapan saja melalui tautan unsubscribe (jika tersedia) atau menghubungi kami.</li>
                </ul>

                <h2>Perubahan Kebijakan</h2>
                <p>
                    Kebijakan ini dapat diperbarui sewaktu-waktu. Tanggal “Terakhir diperbarui” akan menandai perubahan terbaru.
                </p>

                <h2>Kontak</h2>
                <p>
                    Jika Anda memiliki pertanyaan terkait Kebijakan Privasi ini, silakan hubungi kami melalui halaman
                    <a href="/kontak">Kontak</a>.
                </p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>