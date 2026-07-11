<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$habit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify habit belongs to user
$stmt = $pdo->prepare("SELECT habit_id FROM habits WHERE habit_id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);

if ($stmt->rowCount() > 0) {
    // Delete the habit (cascade will delete tracking records)
    $stmt = $pdo->prepare("DELETE FROM habits WHERE habit_id = ?");
    if ($stmt->execute([$habit_id])) {
        $_SESSION['success'] = "Habit deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete habit.";
    }
} else {
    $_SESSION['error'] = "Habit not found.";
}

redirect('login.php');
?>