<?php
// save_journal.php
require 'BD_carepoint.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['content'])) {
  echo json_encode(['success' => false, 'error' => 'No content']);
  exit;
}
$content = $data['content'];
$user_id = isset($data['user_id']) ? intval($data['user_id']) : null;

// Basic sanitization (remove <script> and event attributes)
// NOTE: prefer HTMLPurifier in production.
function sanitize_html($html) {
  $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
  $html = preg_replace('/on\w+\s*=\s*(["\']).*?\1/is', '', $html);
  $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\']*\2/i', '$1="#"', $html);
  return $html;
}
$clean = sanitize_html($content);

$stmt = $pdo->prepare("INSERT INTO journals (user_id, content_html) VALUES (?, ?)");
$stmt->execute([$user_id, $clean]);
$id = $pdo->lastInsertId();

echo json_encode(['success' => true, 'journal_id' => $id]);
