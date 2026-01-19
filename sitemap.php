<?php
header("Content-Type: application/xml; charset=utf-8");

// Get base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'];

// Include database configuration and controllers
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/CategoriesController.php';
require_once __DIR__ . '/controllers/TagsController.php';

$postController = new PostController($db);
$categoriesController = new CategoriesController($db);
$tagsController = new TagsController($db);

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Homepage
echo "<url>
    <loc>{$baseUrl}/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
</url>";

// Blog listing page
echo "<url>
    <loc>{$baseUrl}/blog</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
</url>";

// Static pages
$staticPages = [
    '/kontak' => ['changefreq' => 'monthly', 'priority' => '0.7'],
    '/bantuan' => ['changefreq' => 'monthly', 'priority' => '0.7'],
    '/berlangganan' => ['changefreq' => 'monthly', 'priority' => '0.6'],
    '/pasang-iklan' => ['changefreq' => 'monthly', 'priority' => '0.6'],
];

foreach ($staticPages as $page => $settings) {
    echo "<url>
        <loc>{$baseUrl}{$page}</loc>
        <changefreq>{$settings['changefreq']}</changefreq>
        <priority>{$settings['priority']}</priority>
    </url>";
}

// Published blog posts
$allPosts = $postController->getAll();
$publishedPosts = array_filter($allPosts, function ($post) {
    return $post['status'] === 'published';
});

foreach ($publishedPosts as $post) {
    $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d', strtotime($post['created_at']));
    $url = $baseUrl . '/blog/?slug=' . urlencode($post['slug']);

    echo "<url>
        <loc>" . htmlspecialchars($url) . "</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>";
}

// Categories
$categories = $categoriesController->getAll();
foreach ($categories as $category) {
    $url = $baseUrl . '/category/?slug=' . urlencode($category['categories_id']);
    $lastmod = !empty($category['updated_at']) ? date('Y-m-d', strtotime($category['updated_at'])) : date('Y-m-d', strtotime($category['created_at']));

    echo "<url>
        <loc>" . htmlspecialchars($url) . "</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>";
}

// Tags
$tags = $tagsController->getAll();
foreach ($tags as $tag) {
    $slug = $tag['slug'] ?? $tag['tags_id'];
    $url = $baseUrl . '/tags/?slug=' . urlencode($slug);
    $lastmod = !empty($tag['updated_at']) ? date('Y-m-d', strtotime($tag['updated_at'])) : date('Y-m-d', strtotime($tag['created_at']));

    echo "<url>
        <loc>" . htmlspecialchars($url) . "</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>";
}

echo '</urlset>';
