<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
  http_response_code(401);
  echo json_encode(['error' => 'Not authenticated']);
  exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'user';
$message = trim($_POST['message_text'] ?? '');

if ($message === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Message cannot be empty']);
  exit();
}

try {
  $stmt = $pdo->prepare('INSERT INTO group_messages (user_id, role, message_text) VALUES (?, ?, ?)');
  $stmt->execute([$userId, $userRole, $message]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error']);
}
