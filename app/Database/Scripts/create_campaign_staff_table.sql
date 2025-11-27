-- Migration: Create campaign_staff table
-- Tabel untuk relasi many-to-many antara staff users dan campaigns
-- Digunakan untuk fitur assign staff ke campaign tertentu

CREATE TABLE IF NOT EXISTS `campaign_staff` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID Urunan',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID Staff User',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `campaign_id` (`campaign_id`),
    KEY `user_id` (`user_id`),
    UNIQUE KEY `campaign_user_unique` (`campaign_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

