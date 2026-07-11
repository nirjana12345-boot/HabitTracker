<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user data
$stmt = $pdo->prepare("SELECT user_id, username, email, created_at FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user statistics
// Total habits
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM habits WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_habits = $stmt->fetch()['total'];

// Total completions
$stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM tracking t 
                       JOIN habits h ON t.habit_id = h.habit_id 
                       WHERE h.user_id = ? AND t.status = 'Completed'");
$stmt->execute([$user_id]);
$total_completed = $stmt->fetch()['completed'];

// Current streak (simplified - best streak across all habits)
$stmt = $pdo->prepare("SELECT MAX(streak) as max_streak FROM (
    SELECT COUNT(*) as streak FROM tracking t1 
    JOIN habits h ON t1.habit_id = h.habit_id 
    WHERE h.user_id = ? AND t1.status = 'Completed' 
    AND NOT EXISTS (
        SELECT 1 FROM tracking t2 
        WHERE t2.habit_id = t1.habit_id AND t2.status = 'Missed' 
        AND t2.completed_date > t1.completed_date
    )
    GROUP BY t1.habit_id
) as streaks");
$stmt->execute([$user_id]);
$best_streak = $stmt->fetch()['max_streak'] ?? 0;

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate username
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if username or email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username or email already exists";
    }
    
    // Handle password change
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if (!password_verify($current_password, $user_data['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        if (strlen($new_password) < 6 && !empty($new_password)) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }
    
    if (empty($errors)) {
        // Update basic info
        $sql = "UPDATE users SET username = ?, email = ?";
        $params = [$username, $email];
        
        // Update password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params[] = $hashed_password;
        }
        
        $sql .= " WHERE habit_id = ?";
        $params[] = $user_id;
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $_SESSION['username'] = $username;
            $message = "Profile updated successfully!";
            // Refresh user data
            $user['username'] = $username;
            $user['email'] = $email;
        } else {
            $error = "Failed to update profile";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Get recent activity
$stmt = $pdo->prepare("SELECT t.*, h.habit_name, t.created_at 
                       FROM tracking t 
                       JOIN habits h ON t.habit_id = h.habit_id 
                       WHERE h.user_id = ? 
                       ORDER BY t.created_at DESC 
                       LIMIT 10");
$stmt->execute([$user_id]);
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="brand">
                <i class="fas fa-check-circle"></i>
                <span>Habit Tracker</span>
            </div>
            <ul class="nav-links">
                <li><a href="../dashboard/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="../habits/habits.php"><i class="fas fa-list-ul"></i> Habits</a></li>
                <li><a href="../calendar/calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                <li><a href="../tracking/history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../settings/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i> Dark Mode</button>
                <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <div class="email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="member-since">
                        <i class="fas fa-calendar-alt"></i> 
                        Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </div>
                </div>

                <!-- Profile Stats -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $total_habits; ?></span>
                        <span class="stat-label">Total Habits</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $total_completed; ?></span>
                        <span class="stat-label">Total Completed</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $best_streak; ?></span>
                        <span class="stat-label">Best Streak</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo round(($total_completed / max(1, $total_habits * 30)) * 100, 1); ?>%</span>
                        <span class="stat-label">Completion Rate</span>
                    </div>
                </div>

                <!-- Update Profile Form -->
                <div class="profile-form">
                    <div class="form-title">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                         <div class="form-group">
    <label>Current Password</label>
    <div class="password-wrapper">
        <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
        <i class="fas fa-eye toggle-password" data-target="current_password"></i>
    </div>
</div>

<div class="form-group">
    <label>New Password</label>
    <div class="password-wrapper">
        <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        <i class="fas fa-eye toggle-password" data-target="new_password"></i>
    </div>
</div>

<div class="form-group">
    <label>Confirm New Password</label>
    <div class="password-wrapper">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
        <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
    </div>
</div>
                  </form>
                </div>

                <!-- Recent Activity -->
                <div class="activity-timeline">
                    <div class="timeline-title">
                        <i class="fas fa-clock"></i> Recent Activity
                    </div>
                    
                    <?php if (empty($recent_activity)): ?>
                        <p style="color: var(--text-light); text-align: center; padding: 20px;">
                            No activity yet. Start tracking your habits!
                        </p>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-<?php echo $activity['status'] === 'Completed' ? 'check-circle' : 'times-circle'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="activity-text">
                                        <?php echo htmlspecialchars($activity['habit_name']); ?>
                                        <span style="color: var(--text-light);">
                                            - <?php echo $activity['status'] === 'Completed' ? '✅ Completed' : '❌ Missed'; ?>
                                        </span>
                                    </div>
                                    <div class="activity-time">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo date('M d, Y', strtotime($activity['completed_date'])); ?>
                                        <?php if ($activity['notes']): ?>
                                            <span style="margin-left: 12px;">
                                                <i class="fas fa-comment"></i> <?php echo htmlspecialchars($activity['notes']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/profile.js"></script>
</body>
</html>