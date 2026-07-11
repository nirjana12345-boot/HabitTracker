<?php
require_once '../config/db.php';
$fieldErrors = [];

if (isLoggedIn()) {
    redirect('../dashboard/dashboard.php');
}
  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = sanitize($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Username
    if (strlen($username) < 3)
     $fieldErrors['username'] = "Username must be at least 3 characters.";

    // if (strlen($username) > 50)
    //     $fieldErrors['email'] = "Please enter a valid email address.";

    
    // Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
     $fieldErrors['email'] = "Please enter a valid email address.";

    // Password
    if (strlen($password) < 6){
      $fieldErrors['password'] = "Password must contain atleast 6 characters.";

    }if (!preg_match('/[A-Z]/', $password)){
        $fieldErrors['password'] = "Password must contain an uppercase letter.";

    }if (!preg_match('/[a-z]/', $password)){
        $fieldErrors['password'] = "Password must contain a lowercase letter.";
 
    }if (!preg_match('/[0-9]/', $password)){
        $fieldErrors['password'] = "Password must contain a number.";

    }if ($password !== $confirm_password){
        $fieldErrors['confirm_password'] = "Passwords do not match.";

    }
    

    // Check existing username/email
    if (empty($fieldErrors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $fieldErrors['email'] = "Username or email already exists.";
        }
    }

    // Register
    if (empty($fieldErrors)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");

        if ($stmt->execute([$username,$email,$hashed_password])) {

            $_SESSION['success'] = "Registration successful!";
            redirect('login.php');

        } else {

            $errors[] = "Registration failed.";

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <i class="fas fa-check-circle"></i>
                <h1>Habit Tracker</h1>
                <p>Create your account and start tracking</p>
            </div>
            
            <div class="auth-title">Create Account</div>
            
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" placeholder="Choose a username" required 
                           
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                <?php if(isset($fieldErrors['username'])): ?>
    <small class="error-text"><?php echo $fieldErrors['username']; ?></small>
<?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" placeholder="Enter your email" required


                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                                       <?php if(isset($fieldErrors['email'])): ?>
    <small class="error-text"><?php echo $fieldErrors['email']; ?></small>
<?php endif; ?>

                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="registerPassword" placeholder="Enter password" required>
                        <?php if(isset($fieldErrors['password'])): ?>
    <small class="error-text"><?php echo $fieldErrors['password']; ?></small>
<?php endif; ?>
                        <button type="button" class="password-toggle" id="toggleRegisterPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                            <?php if(isset($fieldErrors['confirm_password'])): ?>
    <small class="error-text"><?php echo $fieldErrors['confirm_password']; ?></small>
<?php endif; ?>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatchMessage" style="font-size: 0.85rem; margin-top: 4px;"></div>
                </div>
                
               
                
                <button type="submit" class="btn-primary" id="registerBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/auth.js"></script>
</body>
</html>