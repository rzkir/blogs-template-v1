<?php

class TagsController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Get all tags with user information
     */
    public function getAll(): array
    {
        $tags = [];
        $stmt = $this->db->prepare("SELECT t.*, ac.fullname, ac.email FROM `tags` t LEFT JOIN `accounts` ac ON t.user_id = ac.id ORDER BY t.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }

    /**
     * Get single tag by ID with user information
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT t.*, ac.fullname, ac.email FROM `tags` t LEFT JOIN `accounts` ac ON t.user_id = ac.id WHERE t.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();

        return $tag ?: null;
    }

    /**
     * Create new tag
     */
    public function create(string $name, string $tagsId, int $userId): int
    {
        if (empty($name)) {
            throw new Exception('Nama tag wajib diisi.');
        }

        if (empty($tagsId)) {
            throw new Exception('Tags ID wajib diisi.');
        }

        $stmt = $this->db->prepare("INSERT INTO `tags` (name, tags_id, user_id) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssi', $name, $tagsId, $userId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal membuat tag: ' . $error);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Update tag
     */
    public function update(int $id, string $name, string $tagsId): bool
    {
        if (empty($name)) {
            throw new Exception('Nama tag wajib diisi.');
        }

        if (empty($tagsId)) {
            throw new Exception('Tags ID wajib diisi.');
        }

        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Tag tidak ditemukan.');
        }

        $stmt = $this->db->prepare("UPDATE `tags` SET name = ?, tags_id = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query: ' . $this->db->error);
        }
        $stmt->bind_param('ssi', $name, $tagsId, $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal mengupdate tag: ' . $error);
        }

        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }

    /**
     * Delete tag
     */
    public function delete(int $id): bool
    {
        // Verify data exists
        $existing = $this->getById($id);
        if (!$existing) {
            throw new Exception('Tag tidak ditemukan.');
        }

        $stmt = $this->db->prepare("DELETE FROM `tags` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }
}
