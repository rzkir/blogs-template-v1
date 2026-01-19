<?php
session_start();

// Redirect jika belum login atau bukan admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/PostController.php';
require_once __DIR__ . '/../../controllers/CategoriesController.php';

$user = $_SESSION['user'];
$postController = new PostController($db);
$categoriesController = new CategoriesController($db);

// Get post ID from query string
$postId = $_GET['id'] ?? null;

if (!$postId) {
    $_SESSION['error'] = 'ID post tidak ditemukan.';
    header('Location: /dashboard/post/index.php');
    exit;
}

// Get post data
$post = $postController->getById((int)$postId);

if (!$post) {
    $_SESSION['error'] = 'Post tidak ditemukan.';
    header('Location: /dashboard/post/index.php');
    exit;
}

// Get all categories
$categories = $categoriesController->getAll();

// Get selected tag names
$selectedTagNames = array_column($post['tags'], 'name');

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
        <div class="container mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8 relative overflow-hidden rounded-2xl bg-white/90 backdrop-blur-sm border border-slate-200/60 shadow-lg shadow-slate-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50/60 via-transparent to-sky-50/40 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-sky-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl pointer-events-none"></div>

                <div class="relative px-5 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25 ring-4 ring-amber-500/10">
                            <i class="fas fa-edit text-white text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                                Edit Post
                            </h2>
                            <p class="text-slate-500 mt-1 text-sm">
                                Edit informasi post
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fas fa-pen-nib"></i>
                                <span>Konten & publikasi</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <a href="/dashboard/post/index.php"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white/80 border border-slate-200/80 rounded-xl hover:bg-slate-50 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                        <button type="button" id="generateAllWithAI"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                            <i class="fas fa-magic"></i>
                            <span>Generate Content with AI</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-edit text-sky-600"></i>
                        Form Edit Post
                    </h3>
                </div>
                <form id="formEditPost" action="/dashboard/post/process.php" method="POST" class="p-4 sm:p-6 space-y-6" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">

                    <!-- Title Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="title" class="block text-sm font-semibold text-slate-700">
                                Judul Post <span class="text-red-500">*</span>
                            </label>
                            <button type="button" id="aiGenerateTitle"
                                class="text-xs px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-magic"></i>
                                <span>AI Generate</span>
                            </button>
                        </div>
                        <input type="text" id="title" name="title" required
                            value="<?php echo htmlspecialchars($post['title']); ?>"
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
                            value="<?php echo htmlspecialchars($post['slug']); ?>"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none"
                            placeholder="slug-post">
                        <p class="mt-1 text-xs text-slate-500">URL-friendly version dari judul.</p>
                    </div>

                    <!-- Description Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700">
                                Deskripsi <span class="text-red-500">*</span>
                            </label>
                            <button type="button" id="aiGenerateDescription"
                                class="text-xs px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-magic"></i>
                                <span>AI Generate</span>
                            </button>
                        </div>
                        <textarea id="description" name="description" required rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none resize-none"
                            placeholder="Masukkan deskripsi singkat post"><?php echo htmlspecialchars($post['description']); ?></textarea>
                        <p class="mt-1 text-xs text-slate-500">Deskripsi singkat yang akan ditampilkan di preview.</p>
                    </div>

                    <!-- Content Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="content" class="block text-sm font-semibold text-slate-700">
                                Konten <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" id="aiGenerateContent"
                                    class="text-xs px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all flex items-center gap-1.5 shadow-sm">
                                    <i class="fas fa-magic"></i>
                                    <span>AI Generate</span>
                                </button>
                                <button type="button" id="aiImproveContent"
                                    class="text-xs px-3 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:from-blue-600 hover:to-cyan-600 transition-all flex items-center gap-1.5 shadow-sm">
                                    <i class="fas fa-sparkles"></i>
                                    <span>AI Improve</span>
                                </button>
                            </div>
                        </div>
                        <div id="editor" style="min-height: 300px;"></div>
                        <input type="hidden" id="content" name="content" value="<?php echo htmlspecialchars($post['content']); ?>">
                        <p class="mt-1 text-xs text-slate-500">Konten lengkap dari post.</p>
                    </div>

                    <!-- Image Field -->
                    <div>
                        <label for="image" class="block text-sm font-semibold text-slate-700 mb-2">
                            Gambar Thumbnail
                        </label>
                        <?php if (!empty($post['image'])): ?>
                            <div class="mb-3">
                                <p class="text-sm text-slate-600 mb-2">Gambar saat ini:</p>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Current image"
                                    class="max-w-full h-auto rounded-xl border border-slate-300 max-h-64">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                        <div id="imagePreview" class="mt-3 hidden">
                            <p class="text-sm text-slate-600 mb-2">Preview gambar baru:</p>
                            <img id="previewImg" src="" alt="Preview" class="max-w-full h-auto rounded-xl border border-slate-300 max-h-64">
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Upload gambar baru untuk mengganti thumbnail (opsional).</p>
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($post['image']); ?>">
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
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo ($post['categories_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tags Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-slate-700">
                                Tags
                            </label>
                            <button type="button" id="aiGenerateTags"
                                class="text-xs px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-magic"></i>
                                <span>AI Generate</span>
                            </button>
                        </div>
                        <div class="border border-slate-300 rounded-xl p-3 min-h-[60px] focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/20 transition-all">
                            <div id="tagsContainer" class="flex flex-wrap gap-2 mb-2">
                                <?php foreach ($selectedTagNames as $tagName): ?>
                                    <span class="tag-chip inline-flex items-center gap-1.5 px-3 py-1 bg-sky-100 text-sky-700 rounded-full text-sm">
                                        <?php echo htmlspecialchars($tagName); ?>
                                        <button type="button" class="tag-remove text-sky-600 hover:text-sky-800" data-tag="<?php echo htmlspecialchars($tagName); ?>">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="text" id="tagInput"
                                class="w-full px-2 py-1 border-0 outline-none text-sm"
                                placeholder="Ketik tag dan tekan Enter (contoh: programming, php, tutorial)">
                            <input type="hidden" id="tagsArray" name="tags" value="<?php echo htmlspecialchars(json_encode($selectedTagNames)); ?>">
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Masukkan tags sebagai array, pisahkan dengan koma atau tekan Enter untuk setiap tag.</p>
                    </div>

                    <!-- Status Field -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all outline-none">
                            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <!-- Featured/Sorotan Field -->
                    <div>
                        <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                <?php echo (!empty($post['is_featured']) && $post['is_featured'] == 1) ? 'checked' : ''; ?>
                                class="w-5 h-5 text-yellow-600 bg-white border-yellow-300 rounded focus:ring-yellow-500 focus:ring-2 cursor-pointer">
                            <label for="is_featured" class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                                <i class="fas fa-star text-yellow-500"></i>
                                Tandai sebagai Sorotan (Featured)
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Post yang ditandai sebagai sorotan akan ditampilkan di section khusus di halaman utama.</p>
                    </div>

                    <!-- Views Info -->
                    <div class="bg-slate-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas fa-eye text-slate-400"></i>
                            <span class="font-medium">Total Views: <?php echo number_format($post['views'] ?? 0); ?></span>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200/50">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105 font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            Update Post
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

<!-- Modal Generate All with AI -->
<div id="aiGenerateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-magic text-purple-500"></i>
                    Generate Content with AI
                </h3>
                <button type="button" id="closeAIModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label for="aiPrompt" class="block text-sm font-semibold text-slate-700 mb-2">
                    Masukkan topik atau ide untuk blog post <span class="text-red-500">*</span>
                </label>
                <textarea id="aiPrompt" rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all outline-none resize-none"
                    placeholder="Contoh: Cara membuat website dengan PHP, Tips belajar programming, Review framework JavaScript terbaru, dll."></textarea>
                <p class="mt-1 text-xs text-slate-500">AI akan generate judul, deskripsi, dan konten lengkap berdasarkan topik ini.</p>
            </div>
            <div id="aiGenerateProgress" class="hidden space-y-3">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <i class="fas fa-spinner fa-spin text-purple-500"></i>
                    <span id="aiProgressText">Generating content...</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div id="aiProgressBar" class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-200 flex gap-3">
            <button type="button" id="startAIGenerate"
                class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all font-semibold shadow-lg">
                <i class="fas fa-magic mr-2"></i>
                Generate
            </button>
            <button type="button" id="cancelAIGenerate"
                class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors font-semibold">
                Batal
            </button>
        </div>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="/js/ai-helper.js"></script>

</body>

</html>