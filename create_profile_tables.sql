-- Add new tables for profile questions and answers system

-- Table for storing user profile questions
CREATE TABLE `profile_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` varchar(500) NOT NULL,
  `question_type` enum('emoji_scale', 'emoji_choice', 'text') NOT NULL,
  `options` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing user answers to profile questions
CREATE TABLE `user_profile_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `answer_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `question_id` (`question_id`),
  FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `profile_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default questions with emoji options
INSERT INTO `profile_questions` (`question_text`, `question_type`, `options`) VALUES
('How stressed do you feel right now?', 'emoji_scale', '😴,😌,😊,😐,😕,😟,😰,😨,😱,💀'),
('How well did you sleep last night?', 'emoji_choice', '😴,😊,😐,😫'),
('Have you felt overwhelmed recently?', 'emoji_choice', '😰,😐,😊'),
('What''s your biggest source of stress?', 'emoji_choice', '💼,📚,👨‍👩‍👧‍👦,🏥,❓'),
('Have you taken a break for yourself today?', 'emoji_choice', '✅,❌'),
('Do you feel you have someone to talk to?', 'emoji_choice', '😊,🤔,😔'),
('How''s your energy level right now?', 'emoji_choice', '⚡,🔋,🪫'),
('Have you noticed physical symptoms of stress?', 'emoji_choice', '😰,😐,😊'),
('What''s one thing you wish could be different?', 'text', NULL);
