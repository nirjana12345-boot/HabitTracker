<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habit_id = (int)$_POST['habit_id'];
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Verify habit belongs to user
    $stmt = $pdo->prepare("SELECT habit_id FROM habits WHERE habit_id = ? AND user_id = ?");
    $stmt->execute([$habit_id, $user_id]);
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Invalid habit";
        redirect('../dashboard/dashboard.php');
    }
    
    // Check if already tracked today
    $stmt = $pdo->prepare("SELECT habit_id FROM tracking WHERE habit_id = ? AND completed_date = ?");
    $stmt->execute([$habit_id, $today]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Already tracked today";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tracking (habit_id, completed_date, status) VALUES (?, ?, 'Completed')");
        if ($stmt->execute([$habit_id, $today])) {
            $_SESSION['success'] = "Habit completed!";
        }
    }
    
    redirect('../dashboard/dashboard.php');
}
?>