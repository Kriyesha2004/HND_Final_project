<?php
require 'BD_carepoint.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sticker'])) {
    $uploadDir = 'stickers/'; // folder must exist and be writable
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileTmpPath = $_FILES['sticker']['tmp_name'];
    $fileName = basename($_FILES['sticker']['name']);
    $targetPath = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($fileTmpPath, $targetPath)) {
        // Save URL/path to DB
        $stmt = $pdo->prepare("INSERT INTO sticker (url) VALUES (?)");
        $stmt->execute([$targetPath]);

        echo "Sticker uploaded successfully!";
    } else {
        echo "Failed to move uploaded file.";
    }
} else {
    echo "No sticker file uploaded.";
}
?>
