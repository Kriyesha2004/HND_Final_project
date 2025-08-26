<?php
session_start();
header('Content-Type: application/json');

// Get user ID from session or default to 1 (same as journaling page)
$userId = $_SESSION['user_id'] ?? 1;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['journal_id']) || !is_numeric($input['journal_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid journal ID']);
    exit;
}

$journalId = intval($input['journal_id']);

try {
    // Database connection
    $host = 'localhost';  
    $db   = 'carepoint';
    $user = 'root';
    $pass = '';

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the journal exists
    $stmt = $pdo->prepare("SELECT id, user_id FROM journals WHERE id = ?");
    $stmt->execute([$journalId]);
    $journal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$journal) {
        echo json_encode(['success' => false, 'error' => 'Journal not found']);
        exit;
    }
    
    // Check if user owns the journal
    if ($journal['user_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Delete the journal
    $stmt = $pdo->prepare("DELETE FROM journals WHERE id = ? AND user_id = ?");
    $stmt->execute([$journalId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Journal deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete journal']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>