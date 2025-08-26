<?php
require 'BD_carepoint.php';
header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$journal_id = isset($_GET['journal_id']) ? intval($_GET['journal_id']) : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User ID required']);
    exit;
}

try {
    if ($journal_id) {
        $stmt = $pdo->prepare('SELECT id, title, content FROM user_journals WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$journal_id, $user_id]);
    } else {
        $stmt = $pdo->prepare('SELECT id, title, content FROM user_journals WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
        $stmt->execute([$user_id]);
    }

    $row = $stmt->fetch();
    if ($row) {
        echo json_encode(['success' => true, 'journal' => $row]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No journal found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
