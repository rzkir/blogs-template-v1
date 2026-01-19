-- Add is_featured column to posts table for spotlight/featured functionality
-- Run this SQL in your phpMyAdmin or MySQL client

ALTER TABLE `posts` 
ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 
AFTER `status`;

-- Optional: Mark some existing posts as featured for testing
-- UPDATE `posts` SET `is_featured` = 1 WHERE id IN (1, 2, 3) LIMIT 3;
