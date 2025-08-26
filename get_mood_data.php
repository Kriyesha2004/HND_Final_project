<?php
require 'BD_carepoint.php';

header('Content-Type: application/json');

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$days = $_GET['days'] ?? 7; // Default to 7 days

try {
    // Get historical mood data from mood tracking table
    $mood_stmt = $pdo->prepare("
        SELECT mood_score, mood_type, tracking_date 
        FROM user_mood_tracking 
        WHERE user_id = ? 
        AND tracking_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ORDER BY tracking_date ASC
    ");
    $mood_stmt->execute([$user_id, $days]);
    $historical_mood = $mood_stmt->fetchAll();
    
    // Calculate today's mood based on answered questions
    $today_mood = calculateTodayMood($pdo, $user_id);
    
    // Combine historical data with today's calculated mood
    $mood_data = [];
    $labels = [];
    
    // Add historical data
    foreach ($historical_mood as $mood) {
        $labels[] = date('M j', strtotime($mood['tracking_date']));
        $mood_data[] = $mood['mood_score'];
    }
    
    // Add today's mood if not already in historical data
    if ($today_mood && !in_array(date('Y-m-d'), array_column($historical_mood, 'tracking_date'))) {
        $labels[] = 'Today';
        $mood_data[] = $today_mood['score'];
    }
    
    // If no data exists, create default data
    if (empty($mood_data)) {
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));
            $mood_data[] = rand(60, 85); // Default random mood scores
        }
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $mood_data,
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
    $mood_indicators = [];
    
    foreach ($today_answers as $answer) {
        $score = calculateAnswerScore($answer['answer'], $answer['question_type']);
        if ($score !== null) {
            $total_score += $score;
            $question_count++;
            $mood_indicators[] = [
                'question' => $answer['question_text'],
                'answer' => $answer['answer'],
                'score' => $score
            ];
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
        'type' => $mood_type,
        'indicators' => $mood_indicators
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
