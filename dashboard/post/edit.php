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
require_once __DIR__ . '/../../controllers/TagsController.php';

$user = $_SESSION['user'];
$postController = new PostController($db);
$categoriesController = new CategoriesController($db);
$tagsController = new TagsController($db);

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

// Get all categories and tags
$categories = $categoriesController->getAll();
$tags = $tagsController->getAll();

// Get selected tag IDs
$selectedTagIds = array_column($post['tags'], 'id');

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
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <a href="/dashboard/post/index.php"
                            class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                            Edit Post
                        </h2>
                    </div>
                    <button type="button" id="generateAllWithAI"
                        class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all flex items-center gap-2 shadow-lg font-semibold">
                        <i class="fas fa-magic"></i>
                        <span>Generate Content with AI</span>
                    </button>
                </div>
                <p class="text-slate-600 mt-2 text-sm sm:text-base ml-11">
                    Edit informasi post
                </p>
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
                                                <?php echo in_array($tag['id'], $selectedTagIds) ? 'checked' : ''; ?>
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
                            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
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