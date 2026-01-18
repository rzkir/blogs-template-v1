<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriesController.php';
require_once __DIR__ . '/../../controllers/TagsController.php';

$user = $_SESSION['user'];
$categoriesController = new CategoriesController($db);
$tagsController = new TagsController($db);

// Get all categories and tags
$categories = $categoriesController->getAll();
$tags = $tagsController->getAll();

// Helper function to convert title to slug
function titleToSlug($title)
{
    // Convert to lowercase
    $slug = strtolower($title);
    // Replace spaces with hyphens
    $slug = str_replace(' ', '-', $slug);
    // Remove special characters, keep only alphanumeric and hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    // Replace multiple hyphens with single hyphen
    $slug = preg_replace('/-+/', '-', $slug);
    // Trim hyphens from start and end
    $slug = trim($slug, '-');
    return $slug;
}

include __DIR__ . '/../header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 pt-4 lg:pt-6 p-4 sm:p-6 min-h-screen relative z-10">
        <div class="container mx-auto animate-fade-in max-w-4xl">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <a href="/dashboard/post/index.php"
                        class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                        Tambah Post
                    </h2>
                </div>
                <p class="text-slate-600 mt-2 text-sm sm:text-base ml-11">
                    Buat post baru untuk blog Anda
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-sky-600"></i>
                        Form Tambah Post
                    </h3>
                </div>
                <form action="/dashboard/post/process.php" method="POST" class="p-4 sm:p-6 space-y-6" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">

                    <!-- Title Field -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
                            Judul Post <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none"
                            placeholder="Masukkan judul post">
                        <p class="mt-1 text-xs text-slate-500">Slug akan di-generate otomatis dari judul.</p>
                    </div>

                    <!-- Slug Field -->
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none bg-slate-50"
                            placeholder="slug-post" readonly>
                        <p class="mt-1 text-xs text-slate-500">Slug akan terisi otomatis dari judul post.</p>
                    </div>

                    <!-- Description Field -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" required rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none resize-none"
                            placeholder="Masukkan deskripsi singkat post"></textarea>
                        <p class="mt-1 text-xs text-slate-500">Deskripsi singkat yang akan ditampilkan di preview.</p>
                    </div>

                    <!-- Content Field -->
                    <div>
                        <label for="content" class="block text-sm font-semibold text-slate-700 mb-2">
                            Konten <span class="text-red-500">*</span>
                        </label>
                        <div id="editor" style="min-height: 300px;"></div>
                        <input type="hidden" id="content" name="content" required>
                        <p class="mt-1 text-xs text-slate-500">Konten lengkap dari post.</p>
                    </div>

                    <!-- Image Field -->
                    <div>
                        <label for="image" class="block text-sm font-semibold text-slate-700 mb-2">
                            Gambar Thumbnail <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="image" name="image" accept="image/*"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                        <div id="imagePreview" class="mt-3 hidden">
                            <img id="previewImg" src="" alt="Preview" class="max-w-full h-auto rounded-xl border border-slate-300 max-h-64">
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Upload gambar untuk thumbnail post.</p>
                    </div>

                    <!-- Category Field -->
                    <div>
                        <label for="categories_id" class="block text-sm font-semibold text-slate-700 mb-2">
                            Kategori
                        </label>
                        <select id="categories_id" name="categories_id"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none">
                            <option value="">Pilih Kategori (Opsional)</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tags Field -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tags
                        </label>
                        <div class="border border-slate-300 rounded-xl p-4 max-h-60 overflow-y-auto">
                            <?php if (empty($tags)): ?>
                                <p class="text-sm text-slate-500 italic">Belum ada tags. Buat tags terlebih dahulu.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <?php foreach ($tags as $tag): ?>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                            <span class="text-sm text-slate-700"><?php echo htmlspecialchars($tag['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Pilih satu atau lebih tags untuk post ini.</p>
                    </div>

                    <!-- Status Field -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200/50">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Post
                        </button>
                        <a href="/dashboard/post/index.php"
                            class="flex-1 px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-semibold text-center">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    function titleToSlug(title) {
        let slug = title.toLowerCase();
        slug = slug.replace(/\s+/g, '-');
        slug = slug.replace(/[^a-z0-9\-]/g, '');
        slug = slug.replace(/-+/g, '-');
        slug = slug.trim('-');
        return slug;
    }

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function(e) {
        const title = e.target.value;
        const slug = titleToSlug(title);
        document.getElementById('slug').value = slug;
    });

    // Initialize Quill editor
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
                ['bold', 'italic', 'underline', 'strike'],
                [{
                    'color': []
                }, {
                    'background': []
                }],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'align': []
                }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden input before form submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const contentInput = document.getElementById('content');
        const content = quill.root.innerHTML;

        // Check if content is not empty (more than just empty paragraph)
        if (content.trim() === '<p><br></p>' || content.trim() === '') {
            e.preventDefault();
            alert('Konten post wajib diisi.');
            return false;
        }

        contentInput.value = content;
    });

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
        }
    });
</script>

</body>

</html>