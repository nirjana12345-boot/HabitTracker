<?php
require_once '../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "Email not found.";
    }
}
?>

<form method="POST">
    <h2>Forgot Password</h2>

    <?php if($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Enter your email" required>

    <button type="submit">Continue</button>
</form>