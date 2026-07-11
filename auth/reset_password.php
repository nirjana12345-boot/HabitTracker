<?php
require_once '../config/db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password === $confirm) {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "UPDATE users SET password=? WHERE email=?"
        );

        $stmt->execute([
            $hashed,
            $_SESSION['reset_email']
        ]);

        unset($_SESSION['reset_email']);

        $_SESSION['success'] = "Password reset successfully.";
        header("Location: login.php");
        exit();
    } else {
        $error = "Passwords do not match.";
    }
}
?>