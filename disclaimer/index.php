<?php
$pageTitle = 'Disclaimer - Blog News';
include __DIR__ . '/../components/Header.php';
?>

<main class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <header class="mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold mb-3">
                <i class="fas fa-exclamation-circle"></i>
                <span>Legal</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">Disclaimer</h1>
            <p class="text-gray-600">Terakhir diperbarui: <?php echo date('d M Y'); ?></p>
        </header>

        <article class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 sm:p-7">
            <div class="post-content">
                <p>
                    Halaman ini berisi penafian terkait penggunaan situs <strong>Blog News</strong>. Dengan mengakses situs ini,
                    Anda dianggap telah memahami dan menyetujui disclaimer berikut.
                </p>

                <h2>Informasi Umum</h2>
                <p>
                    Konten di situs ini disediakan untuk tujuan informasi umum. Kami berusaha menyajikan informasi yang akurat,
                    namun tidak menjamin kelengkapan, keandalan, atau ketepatan waktu informasi tersebut.
                </p>

                <h2>Keputusan Anda</h2>
                <p>
                    Segala tindakan yang Anda lakukan berdasarkan informasi dari situs ini adalah tanggung jawab Anda sendiri.
                    Blog News tidak bertanggung jawab atas kerugian atau dampak yang timbul dari penggunaan informasi tersebut.
                </p>

                <h2>Tautan Eksternal</h2>
                <p>
                    Situs ini dapat menyertakan tautan ke situs lain. Kami tidak memiliki kontrol atas konten dan kebijakan situs pihak ketiga,
                    dan tidak bertanggung jawab atas perubahan atau praktik mereka.
                </p>

                <h2>Hak Cipta</h2>
                <p>
                    Seluruh konten (teks, gambar, desain) adalah milik Blog News atau pemilik hak yang sah. Penggunaan ulang konten
                    tanpa izin dapat melanggar hak cipta.
                </p>

                <h2>Kontak</h2>
                <p>
                    Jika Anda menemukan konten yang perlu dikoreksi atau memiliki pertanyaan, silakan hubungi kami melalui halaman
                    <a href="/kontak">Kontak</a>.
                </p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../components/Footer.php'; ?>