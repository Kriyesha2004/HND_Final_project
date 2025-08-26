<?php
include 'BD_carepoint.php';

$result = $conn->query("SELECT url FROM sticker");
$stickers = [];
while ($row = $result->fetch_assoc()) {
    $stickers[] = $row;
}

header('Content-Type: application/json');
echo json_encode($stickers);
?>
