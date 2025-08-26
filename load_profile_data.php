<?php
require 'BD_carepoint.php';

header('Content-Type: application/json');

// Get user ID from session (you'll need to implement session management)
// For now, we'll use a default user ID or get it from GET parameter
$user_id = $_GET['user_id'] ?? 1; // Default to user ID 1 for testing

try {
    // Get user details from register table
    $user_stmt = $pdo->prepare("SELECT id, name, email FROM register WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch();
    
    if (!$user_data) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Get all questions
    $questions_stmt = $pdo->prepare("SELECT * FROM profile_questions ORDER BY id");
    $questions_stmt->execute();
    $questions = $questions_stmt->fetchAll();
    
    // Get user's answers for today
    $answers_stmt = $pdo->prepare("
        SELECT qa.question_id, qa.answer, q.question_text, q.question_type, q.options
        FROM user_profile_answers qa
        JOIN profile_questions q ON qa.question_id = q.id
        WHERE qa.user_id = ? AND qa.answer_date = CURDATE()
        ORDER BY qa.question_id
    ");
    $answers_stmt->execute([$user_id]);
    $answers = $answers_stmt->fetchAll();
    
    // Get user's mood tracking data for the last 7 days
    $mood_stmt = $pdo->prepare("
        SELECT answer_date, answer
        FROM user_profile_answers qa
        JOIN profile_questions q ON qa.question_id = q.id
        WHERE qa.user_id = ? AND q.question_text = 'How stressed do you feel right now?'
        AND qa.answer_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY qa.answer_date DESC
    ");
    $mood_stmt->execute([$user_id]);
    $mood_data = $mood_stmt->fetchAll();
    
    $response = [
        'user' => $user_data,
        'questions' => $questions,
        'today_answers' => $answers,
        'mood_data' => $mood_data
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
