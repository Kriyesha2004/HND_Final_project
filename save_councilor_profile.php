<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

try {
	$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
	if ($userId <= 0) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

	$name = isset($_POST['name']) ? trim($_POST['name']) : '';
	$email = isset($_POST['email']) ? trim($_POST['email']) : '';
	if ($name === '' || $email === '') { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Name and email required']); exit; }

	$stmt = $pdo->prepare('UPDATE register SET name = ?, email = ? WHERE id = ?');
	$stmt->execute([$name, $email, $userId]);

	echo json_encode(['success' => true]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success'=>false,'error'=>'Database error']);
} 