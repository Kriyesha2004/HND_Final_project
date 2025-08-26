<?php
// DB connection using PDO
$host = 'localhost';  
$db   = 'carepoint'; // your database name
$user = 'root';
$pass = '';

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Handle connection error (don't reveal sensitive info in production)
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
