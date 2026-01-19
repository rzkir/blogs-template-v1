<?php

/**
 * Format date to relative time (if less than 1 day) or absolute date
 * 
 * @param string $datetime - Datetime string
 * @return string - Formatted date string
 */
function formatRelativeDate($datetime)
{
    // Set timezone to Asia/Jakarta (adjust to your timezone)
    date_default_timezone_set('Asia/Jakarta');

    $date = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $date->getTimestamp();

    // If less than 1 day (86400 seconds), show relative time
    if ($diff < 86400) {
        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            if ($minutes < 1) {
                $minutes = 1; // Show at least 1 minute
            }
            return $minutes . ' menit yang lalu';
        } else {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        }
    }

    // If 1 day or more, show date in format "19 Jan 2026"
    return $date->format('d M Y');
}

/**
 * Reusable Card Component
 * 
 * @param array $post - Post data array containing: title, slug, image, category_name, description, created_at, views
 * @param string $type - Card type: 'default', 'featured', 'popular', 'side'
 * @param int $index - Index number (used for popular/trending cards)
 */

function renderCard($post, $type = 'default', $index = null)
{
    $slug = htmlspecialchars($post['slug']);
    $title = htmlspecialchars($post['title']);
    $image = !empty($post['image']) ? htmlspecialchars($post['image']) : null;
    $categoryName = !empty($post['category_name']) ? htmlspecialchars($post['category_name']) : null;
    $description = !empty($post['description']) ? htmlspecialchars($post['description']) : null;
    $views = number_format($post['views'] ?? 0);
    $date = new DateTime($post['created_at']);
    $relativeDate = formatRelativeDate($post['created_at']);

    // Default Card (Grid)
    if ($type === 'default'): ?>
        <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
            <a href="/blog/?slug=<?php echo $slug; ?>" class="block">
                <div class="relative aspect-ratio-16-9 overflow-hidden">
                    <?php if ($image): ?>
                        <img src="<?php echo $image; ?>"
                            alt="<?php echo $title; ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <i class="fas fa-newspaper text-5xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    <?php if ($categoryName): ?>
                        <div class="absolute top-3 left-3">
                            <span class="px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded">
                                <?php echo $categoryName; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-red-600 transition-colors">
                        <?php echo $title; ?>
                    </h3>
                    <?php if ($description): ?>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            <?php echo $description; ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span><?php echo $relativeDate; ?></span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-eye"></i>
                            <?php echo $views; ?>
                        </span>
                    </div>
                </div>
            </a>
        </article>

    <?php
    // Featured Card
    elseif ($type === 'featured'): ?>
        <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
            <a href="/blog/?slug=<?php echo $slug; ?>" class="block">
                <div class="relative aspect-ratio-16-9 overflow-hidden">
                    <?php if ($image): ?>
                        <img src="<?php echo $image; ?>"
                            alt="<?php echo $title; ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-yellow-200 to-yellow-300 flex items-center justify-center">
                            <i class="fas fa-star text-5xl text-yellow-500"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-semibold rounded flex items-center gap-1">
                            <i class="fas fa-star"></i> Featured
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-yellow-600 transition-colors">
                        <?php echo $title; ?>
                    </h3>
                    <?php if ($description): ?>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            <?php echo $description; ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span><?php echo $relativeDate; ?></span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-eye"></i>
                            <?php echo $views; ?>
                        </span>
                    </div>
                </div>
            </a>
        </article>

    <?php
    // Popular Card (Sidebar)
    elseif ($type === 'popular'): ?>
        <a href="/blog/?slug=<?php echo $slug; ?>" class="flex gap-3 group">
            <span class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 text-white text-sm font-bold rounded-full flex items-center justify-center shadow-md">
                <?php echo $index; ?>
            </span>
            <div class="flex-1 min-w-0">
                <span class="text-sm text-gray-700 line-clamp-2 group-hover:text-blue-600 transition-colors block mb-1">
                    <?php echo $title; ?>
                </span>
                <span class="text-xs text-gray-500">
                    <i class="fas fa-eye"></i> <?php echo $views; ?> views
                </span>
            </div>
        </a>

    <?php
    // Side/Trending Card (Sidebar - small)
    elseif ($type === 'side'): ?>
        <a href="/blog/?slug=<?php echo $slug; ?>" class="flex gap-2 sm:gap-3 group">
            <div class="relative w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 overflow-hidden rounded">
                <?php if ($image): ?>
                    <img src="<?php echo $image; ?>"
                        alt="<?php echo $title; ?>"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                <?php else: ?>
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-sm sm:text-base"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0 space-y-4">
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors mb-1">
                    <?php echo $title; ?>
                </h3>
                <p class="text-xs text-gray-500">
                    <?php echo $relativeDate; ?>
                </p>
            </div>
        </a>

    <?php
    // Trending Card (numbered)
    elseif ($type === 'trending'): ?>
        <a href="/blog/?slug=<?php echo $slug; ?>" class="flex gap-3 group">
            <span class="flex-shrink-0 w-6 h-6 bg-red-600 text-white text-xs font-bold rounded-full flex items-center justify-center">
                <?php echo $index; ?>
            </span>
            <span class="text-sm text-gray-700 line-clamp-2 group-hover:text-red-600 transition-colors flex-1">
                <?php echo $title; ?>
            </span>
        </a>

    <?php
    // Main Headline Card (Hero – desain baru: kartu teks di atas gambar)
    elseif ($type === 'headline'):
        $fullname = htmlspecialchars($post['fullname'] ?? 'Admin');
    ?>
        <a href="/blog/?slug=<?php echo $slug; ?>" class="block group headline-hero w-full">
            <div class="headline-hero__wrap">
                <?php if ($image): ?>
                    <img src="<?php echo $image; ?>"
                        alt="<?php echo $title; ?>"
                        class="headline-hero__img">
                <?php else: ?>
                    <div class="headline-hero__placeholder">
                        <i class="fas fa-newspaper"></i>
                    </div>
                <?php endif; ?>
                <div class="headline-hero__shade"></div>
                <div class="headline-hero__card">
                    <div class="headline-hero__card-inner">
                        <?php if ($categoryName): ?>
                            <span class="headline-hero__tag"><?php echo $categoryName; ?></span>
                        <?php endif; ?>
                        <h2 class="headline-hero__title line-clamp-2"><?php echo $title; ?></h2>
                        <?php if (!empty($description)): ?>
                            <p class="headline-hero__desc line-clamp-2"><?php echo $description; ?></p>
                        <?php endif; ?>
                        <div class="headline-hero__meta">
                            <span><?php echo $date->format('d M Y'); ?></span>
                            <span class="headline-hero__meta-dot">·</span>
                            <span><?php echo $fullname; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
<?php endif;
}
?>