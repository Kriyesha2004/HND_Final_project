<?php
require 'BD_carepoint.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `group_messages` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `user_id` INT NOT NULL,
      `role` VARCHAR(32) NOT NULL DEFAULT 'user',
      `message_text` TEXT NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_created_at`(`created_at`),
      KEY `idx_user_id`(`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    echo "✅ group_messages table ready.\n";
    echo "Visit /Carepoint/GroupChat.php to test.\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
