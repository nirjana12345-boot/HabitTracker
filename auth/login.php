<?php
require_once '../config/db.php';

if (isLoggedIn()) {
    redirect('../dashboard/dashboard.php');
}

$error = '';
$remember_username = '';

// Check if remember me cookie exists
if (isset($_COOKIE['remember_username'])) {
    $remember_username = $_COOKIE['remember_username'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        if ($remember) {
            setcookie(
                'remember_username',
                $username,
                time() + (86400 * 30),
                "/"
            );
        } else {
            setcookie(
                'remember_username',
                '',
                time() - 3600,
                "/"
            );
        }

        // Redirect immediately after successful login
        redirect('../dashboard/dashboard.php');

    } else {
        $error = "Invalid username or password";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<?php
$theme = $_COOKIE['theme'] ?? 'light';
?>

<body class="<?php echo ($theme === 'dark') ? 'dark-mode' : ''; ?>">

    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <i class="fas fa-check-circle"></i>
                <h1>Habit Tracker</h1>
                <p>Build better habits, one day at a time</p>
            </div>
            
            <div class="auth-title">Welcome Back</div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username or Email</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Enter your username or email" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : htmlspecialchars($remember_username); ?>">
                    </div>
                </div>
                    <div class="form-group">
    <label>Password</label>

    <div class="input-icon">
        <i class="fas fa-lock left-icon"></i>

        <input
            type="password"
            id="loginPassword"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="button" class="password-toggle" id="toggleLoginPassword">
            <i class="fas fa-eye"></i>
        </button>
    </div>
</div>
                    
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remember_username']) ? 'checked' : ''; ?>>
                        Remember Me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register</a>
            </div>
        </div>
    </div>

   
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/auth.js"></script>
</body>
</html>