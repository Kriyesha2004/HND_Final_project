<?php
session_start();
require 'BD_carepoint.php';
header('Content-Type: application/json');

try {
	$body = file_get_contents('php://input');
	$data = json_decode($body, true);
	if (!is_array($data)) { $data = $_POST; }

	$songId = isset($data['song_id']) ? trim($data['song_id']) : '';
	$like = isset($data['like']) ? (bool)$data['like'] : null;
	$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // fallback for demo

	if ($songId === '' || $like === null) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Missing song_id or like']);
		exit;
	}

	// Ensure table exists (idempotent)
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

	$stmt = $pdo->prepare("INSERT INTO user_song_reactions (user_id, song_id, liked)
		VALUES (?, ?, ?)
		ON DUPLICATE KEY UPDATE liked = VALUES(liked), updated_at = CURRENT_TIMESTAMP()");
	$stmt->execute([$userId, $songId, $like ? 1 : 0]);

	echo json_encode(['success' => true]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Database error']);
} 