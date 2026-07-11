<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$stmt = $pdo->prepare("
SELECT habit_name, reminder_time
FROM habits
WHERE user_id = ?
AND reminder_enabled = 1
AND reminder_time IS NOT NULL
");

$stmt->execute([$_SESSION['user_id']]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>