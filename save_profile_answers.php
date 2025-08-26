<?php
require 'BD_carepoint.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

$question_id = $_POST['question_id'] ?? null;
$answer = $_POST['answer'] ?? null;

if (!$question_id || !$answer) {
    http_response_code(400);
    echo json_encode(['error' => 'Question ID and answer are required']);
    exit;
}

try {
    // Check if user already answered this question today
    $check_stmt = $pdo->prepare("SELECT id FROM user_profile_answers WHERE user_id = ? AND question_id = ? AND answer_date = CURDATE()");
    $check_stmt->execute([$user_id, $question_id]);
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing answer (updated_at will be automatically updated)
        $update_stmt = $pdo->prepare("UPDATE user_profile_answers SET answer = ? WHERE user_id = ? AND question_id = ? AND answer_date = CURDATE()");
        $update_stmt->execute([$answer, $user_id, $question_id]);
        $message = "Answer updated successfully!";
    } else {
        // Insert new answer
        $insert_stmt = $pdo->prepare("INSERT INTO user_profile_answers (user_id, question_id, answer, answer_date) VALUES (?, ?, ?, CURDATE())");
        $insert_stmt->execute([$user_id, $question_id, $answer]);
        $message = "Answer saved successfully!";
    }
    
    // Calculate and save today's mood after saving answer
    $today_mood = calculateTodayMood($pdo, $user_id);
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'mood_updated' => $today_mood !== null,
        'today_mood' => $today_mood
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function calculateTodayMood($pdo, $user_id) {
    // Get today's answers
    $answers_stmt = $pdo->prepare("
        SELECT qa.question_id, qa.answer, pq.question_text, pq.question_type
        FROM user_profile_answers qa
        JOIN profile_questions pq ON qa.question_id = pq.id
        WHERE qa.user_id = ? AND qa.answer_date = CURDATE()
    ");
    $answers_stmt->execute([$user_id]);
    $today_answers = $answers_stmt->fetchAll();
    
    if (empty($today_answers)) {
        return null;
    }
    
    $total_score = 0;
    $question_count = 0;
    
    foreach ($today_answers as $answer) {
        $score = calculateAnswerScore($answer['answer'], $answer['question_type']);
        if ($score !== null) {
            $total_score += $score;
            $question_count++;
        }
    }
    
    if ($question_count === 0) {
        return null;
    }
    
    $average_score = round($total_score / $question_count);
    $mood_type = getMoodType($average_score);
    
    // Save today's mood to tracking table
    saveTodayMood($pdo, $user_id, $average_score, $mood_type);
    
    return [
        'score' => $average_score,
        'type' => $mood_type
    ];
}

function calculateAnswerScore($answer, $question_type) {
    if ($question_type === 'emoji_scale') {
        // Map emoji scale to scores (1-10 scale)
        $emoji_scores = [
            '😴' => 10, '😌' => 9, '😊' => 8, '😐' => 7, '😕' => 6,
            '😟' => 5, '😰' => 4, '😨' => 3, '😱' => 2, '💀' => 1
        ];
        return isset($emoji_scores[$answer]) ? $emoji_scores[$answer] * 10 : null;
    } elseif ($question_type === 'emoji_choice') {
        // Map emoji choices to scores
        $emoji_scores = [
            // Sleep quality
            '😴' => 30, '😊' => 80, '😐' => 60, '😫' => 20,
            // Stress/overwhelmed
            '😰' => 30, '😐' => 60, '😊' => 85,
            // Stress source
            '💼' => 50, '📚' => 60, '👨‍👩‍👧‍👦' => 70, '🏥' => 40, '❓' => 65,
            // Break taken
            '✅' => 85, '❌' => 40,
            // Someone to talk to
            '😊' => 90, '🤔' => 60, '😔' => 30,
            // Energy level
            '⚡' => 90, '🔋' => 75, '🪫' => 30,
            // Physical symptoms
            '😰' => 25, '😐' => 60, '😊' => 85
        ];
        return isset($emoji_scores[$answer]) ? $emoji_scores[$answer] : null;
    }
    
    return null; // Text answers don't contribute to mood score
}

function getMoodType($score) {
    if ($score >= 80) return 'happy';
    if ($score >= 60) return 'neutral';
    if ($score >= 40) return 'sad';
    if ($score >= 20) return 'cry';
    return 'angry';
}

function saveTodayMood($pdo, $user_id, $score, $mood_type) {
    try {
        // Check if mood already exists for today
        $check_stmt = $pdo->prepare("SELECT id FROM user_mood_tracking WHERE user_id = ? AND tracking_date = CURDATE()");
        $check_stmt->execute([$user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing mood
            $update_stmt = $pdo->prepare("UPDATE user_mood_tracking SET mood_score = ?, mood_type = ? WHERE user_id = ? AND tracking_date = CURDATE()");
            $update_stmt->execute([$score, $mood_type, $user_id]);
        } else {
            // Insert new mood
            $insert_stmt = $pdo->prepare("INSERT INTO user_mood_tracking (user_id, mood_score, mood_type, tracking_date) VALUES (?, ?, ?, CURDATE())");
            $insert_stmt->execute([$user_id, $score, $mood_type]);
        }
    } catch (PDOException $e) {
        // Log error but don't fail the request
        error_log("Error saving mood: " . $e->getMessage());
    }
}
?>