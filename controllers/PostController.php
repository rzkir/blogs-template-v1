<?php

class PostController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Get all posts with category, tags, and user information
     */
    public function getAll(): array
    {
        $posts = [];
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.name as category_name,
                c.categories_id as category_slug,
                ac.fullname,
                ac.email,
                GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                GROUP_CONCAT(DISTINCT t.name) as tag_names
            FROM `posts` p
            LEFT JOIN `categories` c ON p.categories_id = c.id
            LEFT JOIN `accounts` ac ON p.user_id = ac.id
            LEFT JOIN `post_tags` pt ON p.id = pt.post_id
            LEFT JOIN `tags` t ON pt.tag_id = t.id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Parse tags
            $row['tags'] = [];
            if (!empty($row['tag_ids']) && !empty($row['tag_names'])) {
                $tagIds = explode(',', $row['tag_ids']);
                $tagNames = explode(',', $row['tag_names']);
                for ($i = 0; $i < count($tagIds); $i++) {
                    $row['tags'][] = [
                        'id' => $tagIds[$i],
                        'name' => $tagNames[$i] ?? ''
                    ];
                }
            }
            unset($row['tag_ids'], $row['tag_names']);
            $posts[] = $row;
        }
        $stmt->close();
        return $posts;
    }

    /**
     * Get single post by ID with category, tags, and user information
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.name as category_name,
                c.categories_id as category_slug,
                ac.fullname,
                ac.email
            FROM `posts` p
            LEFT JOIN `categories` c ON p.categories_id = c.id
            LEFT JOIN `accounts` ac ON p.user_id = ac.id
            WHERE p.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        if (!$post) {
            return null;
        }

        // Get tags for this post
        $post['tags'] = $this->getTagsByPostId($id);

        return $post;
    }

    /**
     * Get tags for a specific post
     */
    public function getTagsByPostId(int $postId): array
    {
        $tags = [];
        $stmt = $this->db->prepare("
            SELECT t.* 
            FROM `tags` t
            INNER JOIN `post_tags` pt ON t.id = pt.tag_id
            WHERE pt.post_id = ?
            ORDER BY t.name ASC
        ");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }

    /**
     * Create new post
     */
    public function create(
        string $title,
        string $slug,
        string $description,
        string $content,
        string $image,
        string $status,
        ?int $categoriesId,
        int $userId,
        array $tagNames = []
    ): int {
        if (empty($title)) {
            throw new Exception('Judul post wajib diisi.');
        }

        if (empty($slug)) {
            throw new Exception('Slug post wajib diisi.');
        }

        if (empty($content)) {
            throw new Exception('Konten post wajib diisi.');
        }

        // Check if slug already exists
        $existing = $this->getBySlug($slug);
        if ($existing) {
            throw new Exception('Slug sudah digunakan. Gunakan slug yang berbeda.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO `posts` (title, slug, description, content, image, status, categories_id, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssssssii', $title, $slug, $description, $content, $image, $status, $categoriesId, $userId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal membuat post: ' . $error);
        }

        $newId = $stmt->insert_id;
        $stmt->close();

        // Set tags if provided
        if (!empty($tagNames)) {
            $this->setPostTagsByName($newId, $tagNames, $userId);
        }

        return $newId;
    }

    /**
     * Update post
     */
    public function update(
        int $id,
        string $title,
        string $slug,
        string $description,
        string $content,
        string $image,
        string $status,
        ?int $categoriesId,
        array $tagNames = []
    ): bool {
        if (empty($title)) {
            throw new Exception('Judul post wajib diisi.');
        }

        if (empty($slug)) {
            throw new Exception('Slug post wajib diisi.');
        }

        if (empty($content)) {
            throw new Exception('Konten post wajib diisi.');
        }

        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Post tidak ditemukan.');
        }

        // Check if slug already exists (excluding current post)
        $slugCheck = $this->getBySlug($slug);
        if ($slugCheck && $slugCheck['id'] != $id) {
            throw new Exception('Slug sudah digunakan. Gunakan slug yang berbeda.');
        }

        $stmt = $this->db->prepare("
            UPDATE `posts` 
            SET title = ?, slug = ?, description = ?, content = ?, image = ?, status = ?, categories_id = ? 
            WHERE id = ?
        ");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssssssii', $title, $slug, $description, $content, $image, $status, $categoriesId, $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal mengupdate post: ' . $error);
        }

        $success = $stmt->affected_rows > 0;
        $stmt->close();

        // Update tags
        // Get userId from existing post
        $existing = $this->getById($id);
        $userId = $existing['user_id'] ?? 0;
        if (!empty($tagNames) && $userId > 0) {
            $this->setPostTagsByName($id, $tagNames, $userId);
        } else {
            // Clear tags if empty
            $this->setPostTags($id, []);
        }

        return $success;
    }

    /**
     * Delete post
     */
    public function delete(int $id): bool
    {
        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Post tidak ditemukan.');
        }

        // Tags will be deleted automatically due to CASCADE
        $stmt = $this->db->prepare("DELETE FROM `posts` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }

    /**
     * Get post by slug
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `posts` WHERE slug = ?");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        return $post ?: null;
    }

    /**
     * Get posts by category ID (using categories_id slug)
     */
    public function getByCategorySlug(string $categorySlug): array
    {
        $posts = [];
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.name as category_name,
                c.categories_id as category_slug,
                ac.fullname,
                ac.email,
                GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                GROUP_CONCAT(DISTINCT t.name) as tag_names
            FROM `posts` p
            INNER JOIN `categories` c ON p.categories_id = c.id
            LEFT JOIN `accounts` ac ON p.user_id = ac.id
            LEFT JOIN `post_tags` pt ON p.id = pt.post_id
            LEFT JOIN `tags` t ON pt.tag_id = t.id
            WHERE c.categories_id = ? AND p.status = 'published'
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param('s', $categorySlug);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Parse tags
            $row['tags'] = [];
            if (!empty($row['tag_ids']) && !empty($row['tag_names'])) {
                $tagIds = explode(',', $row['tag_ids']);
                $tagNames = explode(',', $row['tag_names']);
                for ($i = 0; $i < count($tagIds); $i++) {
                    $row['tags'][] = [
                        'id' => $tagIds[$i],
                        'name' => $tagNames[$i] ?? ''
                    ];
                }
            }
            unset($row['tag_ids'], $row['tag_names']);
            $posts[] = $row;
        }
        $stmt->close();
        return $posts;
    }

    /**
     * Set tags for a post (replaces existing tags) - by tag IDs
     */
    private function setPostTags(int $postId, array $tagIds): void
    {
        // Delete existing tags
        $stmt = $this->db->prepare("DELETE FROM `post_tags` WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();

        // Insert new tags
        if (!empty($tagIds)) {
            $stmt = $this->db->prepare("INSERT INTO `post_tags` (post_id, tag_id) VALUES (?, ?)");
            foreach ($tagIds as $tagId) {
                $tagId = (int)$tagId;
                if ($tagId > 0) {
                    $stmt->bind_param('ii', $postId, $tagId);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
    }

    /**
     * Set tags for a post by tag names (creates tags if they don't exist)
     */
    private function setPostTagsByName(int $postId, array $tagNames, int $userId): void
    {
        // Delete existing tags
        $stmt = $this->db->prepare("DELETE FROM `post_tags` WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();

        if (empty($tagNames)) {
            return;
        }

        // Helper function to convert name to slug
        $nameToSlug = function ($name) {
            $slug = strtolower($name);
            $slug = str_replace(' ', '-', $slug);
            $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            return trim($slug, '-');
        };

        // Get or create tags
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            // Check if tag exists by name
            $stmt = $this->db->prepare("SELECT id FROM `tags` WHERE name = ? LIMIT 1");
            $stmt->bind_param('s', $tagName);
            $stmt->execute();
            $result = $stmt->get_result();
            $tag = $result->fetch_assoc();
            $stmt->close();

            if ($tag) {
                // Tag exists, use its ID
                $tagIds[] = $tag['id'];
            } else {
                // Tag doesn't exist, create it
                $tagsId = $nameToSlug($tagName);
                $stmt = $this->db->prepare("INSERT INTO `tags` (name, tags_id, user_id) VALUES (?, ?, ?)");
                $stmt->bind_param('ssi', $tagName, $tagsId, $userId);
                if ($stmt->execute()) {
                    $tagIds[] = $stmt->insert_id;
                }
                $stmt->close();
            }
        }

        // Link tags to post
        if (!empty($tagIds)) {
            $stmt = $this->db->prepare("INSERT INTO `post_tags` (post_id, tag_id) VALUES (?, ?)");
            foreach ($tagIds as $tagId) {
                $stmt->bind_param('ii', $postId, $tagId);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    /**
     * Increment views for a post
     */
    public function incrementViews(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE `posts` SET views = views + 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
