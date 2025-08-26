-- Group chat tables for Carepoint

CREATE TABLE `group_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'user',
  `message_text` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at`(`created_at`),
  KEY `idx_user_id`(`user_id`),
  CONSTRAINT `fk_group_messages_user` FOREIGN KEY (`user_id`) REFERENCES `register`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
