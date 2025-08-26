<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

try {
	$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
	if ($userId <= 0) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

	$payload = json_decode(file_get_contents('php://input'), true) ?: [];
	$current = isset($payload['current_password']) ? (string)$payload['current_password'] : '';
	$new = isset($payload['new_password']) ? (string)$payload['new_password'] : '';

	if ($current === '' || $new === '') { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing fields']); exit; }

	$stmt = $pdo->prepare('SELECT password FROM register WHERE id = ? LIMIT 1');
	$stmt->execute([$userId]);
	$row = $stmt->fetch();
	if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit; }

	$hashed = $row['password'];
	if (!password_verify($current, $hashed)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Current password is incorrect']); exit; }

	$newHash = password_hash($new, PASSWORD_BCRYPT);
	$upd = $pdo->prepare('UPDATE register SET password = ? WHERE id = ?');
	$upd->execute([$newHash, $userId]);

	echo json_encode(['success'=>true]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success'=>false,'error'=>'Database error']);
} 