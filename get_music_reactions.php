<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

try {
	$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // fallback for demo
	$pdo->exec("CREATE TABLE IF NOT EXISTS user_song_reactions (
		id INT NOT NULL AUTO_INCREMENT,
		user_id INT NOT NULL,
		song_id VARCHAR(512) NOT NULL,
		liked TINYINT(1) NOT NULL DEFAULT 1,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
		PRIMARY KEY (id),
		UNIQUE KEY uniq_user_song (user_id, song_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	$stmt = $pdo->prepare("SELECT song_id FROM user_song_reactions WHERE user_id = ? AND liked = 1");
	$stmt->execute([$userId]);
	$rows = $stmt->fetchAll();
	$liked = array_map(function($r){ return $r['song_id']; }, $rows);
	echo json_encode(['success' => true, 'liked' => $liked]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Database error']);
} 