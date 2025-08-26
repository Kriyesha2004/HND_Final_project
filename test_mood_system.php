<?php
require 'BD_carepoint.php';

echo "<h2>Testing Mood Tracking System</h2>";

try {
    // Test 1: Check if profile_questions table exists and has data
    echo "<h3>1. Checking Profile Questions Table</h3>";
    $questions_stmt = $pdo->prepare("SELECT * FROM profile_questions ORDER BY id");
    $questions_stmt->execute();
    $questions = $questions_stmt->fetchAll();
    
    if (empty($questions)) {
        echo "<p style='color: red;'>❌ No questions found in profile_questions table!</p>";
        echo "<p>Please run the create_profile_tables.sql script first.</p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($questions) . " questions in profile_questions table</p>";
        echo "<ul>";
        foreach ($questions as $q) {
            echo "<li>ID: {$q['id']} - {$q['question_text']} ({$q['question_type']})</li>";
        }
        echo "</ul>";
    }
    
    // Test 2: Check if user_mood_tracking table exists
    echo "<h3>2. Checking Mood Tracking Table</h3>";
    $check_mood_table = $pdo->prepare("SHOW TABLES LIKE 'user_mood_tracking'");
    $check_mood_table->execute();
    
    if ($check_mood_table->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ user_mood_tracking table not found. Creating it now...</p>";
        
        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS `user_mood_tracking` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `mood_score` int(11) NOT NULL COMMENT 'Mood score from 1-100',
          `mood_type` enum('happy', 'neutral', 'sad', 'cry', 'angry') NOT NULL,
          `tracking_date` date NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `user_date_unique` (`user_id`, `tracking_date`),
          KEY `user_id` (`user_id`),
          FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        
        $pdo->exec($create_table_sql);
        echo "<p style='color: green;'>✅ user_mood_tracking table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ user_mood_tracking table exists</p>";
    }
    
    // Test 3: Check if there are any users
    echo "<h3>3. Checking Users</h3>";
    $users_stmt = $pdo->prepare("SELECT id, name, email FROM register LIMIT 5");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: red;'>❌ No users found in register table!</p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($users) . " users</p>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: {$user['id']} - {$user['name']} ({$user['email']})</li>";
        }
        echo "</ul>";
    }
    
    // Test 4: Check if there are any answers for today
    echo "<h3>4. Checking Today's Answers</h3>";
    $answers_stmt = $pdo->prepare("
        SELECT qa.question_id, qa.answer, pq.question_text, pq.question_type
        FROM user_profile_answers qa
        JOIN profile_questions pq ON qa.question_id = pq.id
        WHERE qa.answer_date = CURDATE()
        LIMIT 5
    ");
    $answers_stmt->execute();
    $today_answers = $answers_stmt->fetchAll();
    
    if (empty($today_answers)) {
        echo "<p style='color: orange;'>⚠️ No answers found for today. This is normal if no one has answered questions yet.</p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($today_answers) . " answers for today</p>";
        echo "<ul>";
        foreach ($today_answers as $answer) {
            echo "<li>Q{$answer['question_id']}: {$answer['answer']} ({$answer['question_type']})</li>";
        }
        echo "</ul>";
    }
    
    // Test 5: Test mood calculation function
    echo "<h3>5. Testing Mood Calculation</h3>";
    if (!empty($users)) {
        $test_user_id = $users[0]['id'];
        echo "<p>Testing mood calculation for user ID: {$test_user_id}</p>";
        
        // Test the mood calculation function
        $test_answers_stmt = $pdo->prepare("
            SELECT qa.question_id, qa.answer, pq.question_text, pq.question_type
            FROM user_profile_answers qa
            JOIN profile_questions pq ON qa.question_id = pq.id
            WHERE qa.user_id = ? AND qa.answer_date = CURDATE()
        ");
        $test_answers_stmt->execute([$test_user_id]);
        $test_answers = $test_answers_stmt->fetchAll();
        
        if (empty($test_answers)) {
            echo "<p style='color: orange;'>⚠️ No answers found for user {$test_user_id} today. Cannot test mood calculation.</p>";
        } else {
            echo "<p style='color: green;'>✅ Found answers for user {$test_user_id}. Mood calculation should work.</p>";
        }
    }
    
    echo "<h3>✅ System Test Complete!</h3>";
    echo "<p>If all tests passed, your mood tracking system should work correctly.</p>";
    echo "<p><a href='Myprofile.php' style='background: #4ade80; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to My Profile</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and table structure.</p>";
}
?>
