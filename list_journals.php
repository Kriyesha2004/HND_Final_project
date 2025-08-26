<?php
require 'BD_carepoint.php';
header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;

if (!$user_id) {
  echo json_encode(['success' => false, 'error' => 'User ID required']);
  exit;
}

try {
  $stmt = $pdo->prepare('SELECT id, title, updated_at FROM user_journals WHERE user_id = ? ORDER BY updated_at DESC LIMIT ?');
  $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
  $stmt->bindValue(2, $limit, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll();
  echo json_encode(['success' => true, 'journals' => $rows]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
