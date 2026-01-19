<?php

class CategoriesController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Get all categories with user information
     */
    public function getAll(): array
    {
        $categories = [];
        $stmt = $this->db->prepare("SELECT c.*, ac.fullname, ac.email FROM `categories` c LEFT JOIN `accounts` ac ON c.user_id = ac.id ORDER BY c.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $stmt->close();
        return $categories;
    }

    /**
     * Get single category by ID with user information
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT c.*, ac.fullname, ac.email FROM `categories` c LEFT JOIN `accounts` ac ON c.user_id = ac.id WHERE c.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        return $category ?: null;
    }

    /**
     * Get single category by categories_id (slug) with user information
     */
    public function getBySlug(string $categoriesId): ?array
    {
        $stmt = $this->db->prepare("SELECT c.*, ac.fullname, ac.email FROM `categories` c LEFT JOIN `accounts` ac ON c.user_id = ac.id WHERE c.categories_id = ?");
        $stmt->bind_param('s', $categoriesId);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        return $category ?: null;
    }

    /**
     * Create new category
     */
    public function create(string $name, string $categoriesId, int $userId): int
    {
        if (empty($name)) {
            throw new Exception('Nama kategori wajib diisi.');
        }

        if (empty($categoriesId)) {
            throw new Exception('Categories ID wajib diisi.');
        }

        $stmt = $this->db->prepare("INSERT INTO `categories` (name, categories_id, user_id) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssi', $name, $categoriesId, $userId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal membuat kategori: ' . $error);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Update category
     */
    public function update(int $id, string $name, string $categoriesId): bool
    {
        if (empty($name)) {
            throw new Exception('Nama kategori wajib diisi.');
        }

        if (empty($categoriesId)) {
            throw new Exception('Categories ID wajib diisi.');
        }

        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Kategori tidak ditemukan.');
        }

        $stmt = $this->db->prepare("UPDATE `categories` SET name = ?, categories_id = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssi', $name, $categoriesId, $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal mengupdate kategori: ' . $error);
        }

        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }

    /**
     * Delete category
     */
    public function delete(int $id): bool
    {
        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Kategori tidak ditemukan.');
        }

        $stmt = $this->db->prepare("DELETE FROM `categories` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }
}
