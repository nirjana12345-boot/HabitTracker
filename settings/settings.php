<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user settings
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get all habits for reminder settings
$stmt = $pdo->prepare("SELECT habit_id, habit_name, reminder_enabled, reminder_time FROM habits WHERE user_id = ?");
$stmt->execute([$user_id]);
$habits = $stmt->fetchAll();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $browser = isset($_POST['browser_notifications']) ? 1 : 0;
$email = isset($_POST['email_notifications']) ? 1 : 0;
$summary = isset($_POST['daily_summary']) ? 1 : 0;
$streak = isset($_POST['streak_alerts']) ? 1 : 0;

$stmt = $pdo->prepare("
UPDATE users
SET
browser_notifications=?,
email_notifications=?,
daily_summary=?,
streak_alerts=?
WHERE user_id=?
");

$stmt->execute([
$browser,
$email,
$summary,
$streak,
$_SESSION['user_id']
]);
    if (isset($_POST['update_reminders'])) {
        // Update reminder settings for each habit
        foreach ($habits as $habit) {

    $habit_id = $habit['habit_id'];

    $reminder_enabled = isset($_POST["reminder_$habit_id"]) ? 1 : 0;
    $reminder_time = $_POST["time_$habit_id"] ?? null;

    $stmt = $pdo->prepare("
        UPDATE habits
        SET reminder_enabled = ?, reminder_time = ?
        WHERE habit_id = ? AND user_id = ?
    ");

    $stmt->execute([
        $reminder_enabled,
        $reminder_time,
        $habit_id,
        $user_id
    ]);
}
        $message = "Reminder settings updated successfully!";
        
        // Refresh habits data
        $stmt = $pdo->prepare("
    SELECT habit_id, habit_name, reminder_enabled, reminder_time
    FROM habits
    WHERE user_id = ?
");
        $stmt->execute([$user_id]);
        $habits = $stmt->fetchAll();
    }
    
    if (isset($_POST['update_notifications'])) {
        // Update notification preferences (stored in a separate settings table or user meta)
        // For simplicity, we'll just store in session for now
        $_SESSION['notifications'] = [
            'email' => isset($_POST['email_notifications']) ? 1 : 0,
            'browser' => isset($_POST['browser_notifications']) ? 1 : 0,
            'daily_summary' => isset($_POST['daily_summary']) ? 1 : 0,
            'streak_alerts' => isset($_POST['streak_alerts']) ? 1 : 0,
        ];
        $message = "Notification preferences updated!";
    }
    
    if (isset($_POST['delete_account'])) {
        // Confirm deletion
        $confirm = $_POST['confirm_delete'] ?? '';
        if ($confirm === 'DELETE') {
            // Delete all user data (cascade delete will handle habits and tracking)
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            if ($stmt->execute([$user_id])) {
                session_destroy();
                redirect('../auth/login.php');
            }
        } else {
            $error = "Please type 'DELETE' to confirm account deletion";
        }
    }

    
}

// Get current notification settings (from session or default)
$notifications = $_SESSION['notifications'] ?? [
    'email' => 1,
    'browser' => 1,
    'daily_summary' => 1,
    'streak_alerts' => 1,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
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
                <li><a href="../profile/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i> Dark Mode</button>
                <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="settings-container">
                <div class="page-header">
                    <h1><i class="fas fa-cog"></i> Settings</h1>
                    <p>Manage your preferences and account settings</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Reminder Settings -->
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-bell"></i>
                        <h2>Reminder Settings</h2>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <p style="color: var(--text-light); margin-bottom: 16px;">
                                Configure reminders for each habit. You'll receive notifications at the set time.
                            </p>
                            
                            <?php foreach ($habits as $habit): ?>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <span class="setting-label"><?php echo htmlspecialchars($habit['habit_name']); ?></span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <input type="time" name="time_<?php echo $habit['habit_id']; ?>" 
                                               value="<?php echo $habit['reminder_time'] ?? ''; ?>">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="reminder_<?php echo $habit['habit_id']; ?>" 
                                                   <?php echo $habit['reminder_enabled'] ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="update_reminders" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Reminders
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-bell"></i>
                        <h2>Notification Preferences</h2>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <div class="notification-preferences">
                                <div class="pref-item">
                                    <input type="checkbox" name="email_notifications" 
                                           <?php echo $notifications['email'] ? 'checked' : ''; ?>>
                                    <label>Email Notifications</label>
                                </div>
                                <div class="pref-item">
                                    <input type="checkbox" name="browser_notifications" 
                                           <?php echo $notifications['browser'] ? 'checked' : ''; ?>>
                                    <label>Browser Notifications</label>
                                </div>
                                <div class="pref-item">
                                    <input type="checkbox" name="daily_summary" 
                                           <?php echo $notifications['daily_summary'] ? 'checked' : ''; ?>>
                                    <label>Daily Summary</label>
                                </div>
                                <div class="pref-item">
                                    <input type="checkbox" name="streak_alerts" 
                                           <?php echo $notifications['streak_alerts'] ? 'checked' : ''; ?>>
                                    <label>Streak Alerts</label>
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="update_notifications" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-palette"></i>
                        <h2>Appearance</h2>
                    </div>
                    <div class="section-body">
                        <div class="setting-item">
                            <div class="setting-info">
                                <span class="setting-label">Dark Mode</span>
                                <span class="setting-description">Switch between light and dark theme</span>
                            </div>
                            <button id="settingsThemeToggle" class="btn-secondary">
                                <i class="fas fa-moon"></i> Toggle Theme
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="settings-section danger-zone">
                    <div class="section-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2>Danger Zone</h2>
                    </div>
                    <div class="section-body">
                        <div class="setting-item">
                            <div class="setting-info">
                                <span class="setting-label" style="color: var(--danger);">Delete Account</span>
                                <span class="setting-description">Permanently delete your account and all data. This action cannot be undone.</span>
                            </div>
                        </div>
                        <form method="POST" style="margin-top: 12px;">
                            <div class="form-group">
                                <label>Type <strong>DELETE</strong> to confirm:</label>
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <input type="text" name="confirm_delete" placeholder="Type DELETE" 
                                           style="max-width: 200px;">
                                    <button type="submit" name="delete_account" class="btn-danger">
                                        <i class="fas fa-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Settings page theme toggle
            const settingsThemeToggle = document.getElementById('settingsThemeToggle');
            if (settingsThemeToggle) {
                settingsThemeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-mode');
                    const icon = this.querySelector('i');
                    if (document.body.classList.contains('dark-mode')) {
                        icon.className = 'fas fa-sun';
                        this.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
                    } else {
                        icon.className = 'fas fa-moon';
                        this.innerHTML = '<i class="fas fa-moon"></i> Toggle Theme';
                    }
                });
            }
        });
    </script> -->
</body>
</html>