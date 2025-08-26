<?php
session_start();
require 'BD_carepoint.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Delete user record from DB
    $stmt = $pdo->prepare("DELETE FROM register WHERE id = ?");
    $stmt->execute([$userId]);

    // Clear session
    session_unset();
    session_destroy();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
