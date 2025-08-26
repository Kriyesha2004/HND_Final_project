<?php
require 'BD_carepoint.php';

try {
    // Create profile_questions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `profile_questions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `question_text` varchar(500) NOT NULL,
          `question_type` enum('emoji_scale', 'emoji_choice', 'text') NOT NULL,
          `options` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // Create user_profile_answers table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_profile_answers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `question_id` int(11) NOT NULL,
          `answer` varchar(255) NOT NULL,
          `answer_date` date NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `question_id` (`question_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // Add updated_at column if it doesn't exist (for existing tables)
    try {
        $pdo->exec("ALTER TABLE `user_profile_answers` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()");
        echo "✅ Added updated_at column to existing table!\n";
    } catch (PDOException $e) {
        // Column might already exist, that's okay
        echo "ℹ️  updated_at column already exists or not needed.\n";
    }
    
    // Check if questions already exist
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM profile_questions");
    $check_stmt->execute();
    $question_count = $check_stmt->fetchColumn();
    
    if ($question_count == 0) {
        // Insert default questions with emoji options
        $questions = [
            ['How stressed do you feel right now?', 'emoji_scale', '😴,😌,😊,😐,😕,😟,😰,😨,😱,💀'],
            ['How well did you sleep last night?', 'emoji_choice', '😴,😊,😐,😫'],
            ['Have you felt overwhelmed recently?', 'emoji_choice', '😰,😐,😊'],
            ['What\'s your biggest source of stress?', 'emoji_choice', '💼,📚,👨‍👩‍👧‍👦,🏥,❓'],
            ['Have you taken a break for yourself today?', 'emoji_choice', '✅,❌'],
            ['Do you feel you have someone to talk to?', 'emoji_choice', '😊,🤔,😔'],
            ['How\'s your energy level right now?', 'emoji_choice', '⚡,🔋,🪫'],
            ['Have you noticed physical symptoms of stress?', 'emoji_choice', '😰,😐,😊'],
            ['What\'s one thing you wish could be different?', 'text', NULL]
        ];
        
        $insert_stmt = $pdo->prepare("INSERT INTO profile_questions (question_text, question_type, options) VALUES (?, ?, ?)");
        
        foreach ($questions as $question) {
            $insert_stmt->execute($question);
        }
        
        echo "✅ Database tables created successfully!\n";
        echo "✅ Default questions inserted!\n";
    } else {
        echo "✅ Database tables already exist!\n";
        echo "✅ Questions already present!\n";
    }
    
    echo "\n🎉 Profile system is ready to use!\n";
    echo "📝 You can now access Myprofile.php to test the system.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
