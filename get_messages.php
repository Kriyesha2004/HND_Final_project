<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit();
}

try {
  $stmt = $pdo->query('SELECT gm.id, gm.user_id, gm.message_text, gm.role, gm.created_at, r.name
                       FROM group_messages gm
                       JOIN register r ON r.id = gm.user_id
                       ORDER BY gm.id ASC LIMIT 500');
  $messages = [];
  while ($row = $stmt->fetch()) {
    $messages[] = [
      'id' => $row['id'],
      'user_id' => $row['user_id'],
      'message_text' => $row['message_text'],
      'role' => $row['role'],
      'created_at' => $row['created_at'],
      'name' => $row['name']
    ];
  }
  echo json_encode(['messages' => $messages]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error']);
}
