<?php
// save_journal.php
require 'BD_carepoint.php';  // your PDO connection

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['content']) || empty($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID or content']);
    exit;
}

$user_id = intval($data['user_id']);
$content = $data['content'];
$title = isset($data['title']) ? trim($data['title']) : '';
$id = isset($data['id']) ? intval($data['id']) : null;

// Basic content sanitization
function sanitize_html($html) {
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/on\w+\s*=\s*(["\']).*?\1/is', '', $html);
    return $html;
}

$content = sanitize_html($content);

try {
    if ($id) {
        // Update existing journal
        $stmt = $pdo->prepare("UPDATE user_journals SET title = ?, content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $id, $user_id]);
        echo json_encode(['success' => true, 'journal_id' => $id]);
    } else {
        // Insert new journal
        $stmt = $pdo->prepare("INSERT INTO user_journals (user_id, title, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$user_id, $title, $content]);
        $newId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'journal_id' => $newId]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
