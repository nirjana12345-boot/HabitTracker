<?php
require_once '../config/db.php';
$errors = [];

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $habit_name = sanitize($_POST['habit_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $frequency = $_POST['frequency'] ?? 'Daily';
    $reminder_enabled = isset($_POST['reminder_enabled']) ? 1 : 0;
    $reminder_time = $_POST['reminder_time'] ?? null;

    if (!$reminder_enabled) {
    $reminder_time = null;
}

    $errors = [];
    if (strlen($habit_name) < 3) {
        $errors[] = "Habit name must be at least 3 characters";
    }
    $stmt = $pdo->prepare("
SELECT habit_id
FROM habits
WHERE user_id = ?
AND habit_name = ?
");

$stmt->execute([$user_id, $habit_name]);

if ($stmt->fetch()) {
    $errors[] = "Habit already exists.";
}
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, habit_name, description, frequency, reminder_enabled, reminder_time) 
                               VALUES (?, ?, ?, ?, ?, ?)");
       if ($stmt->execute([ 
    $user_id,
    $habit_name,
    $description,
    $frequency,
    $reminder_enabled,
    $reminder_time,])) {
    $_SESSION['success'] = "Habit created successfully!";
    redirect('../dashboard/dashboard.php');
} else {
    $errors[] = "Failed to create habit.";
}
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Habit - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="brand"><i class="fas fa-check-circle"></i><span>Habit Tracker</span></div>
            <ul class="nav-links">
                <li><a href="../dashboard/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="habits.php"><i class="fas fa-list-ul"></i> Habits</a></li>
                <li><a href="../calendar/calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                <li><a href="../tracking/history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="../profile/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../settings/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i> Dark Mode</button>
                <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        <main class="main-content">
            <div class="form-container">
                <h1><i class="fas fa-plus"></i> Add New Habit</h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="habit-form">
                    <div class="form-group">
                        <label>Habit Name *</label>
                        <input type="text" name="habit_name" required minlength="3" 
                               placeholder="e.g., Read 20 pages">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" 
                                  placeholder="Describe your habit..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Frequency</label>
                        <select name="frequency">
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                    <!-- <div class="form-group">
                        <label>
                            <input type="checkbox" name="reminder_enabled" checked>
                            Enable Reminder
                        </label>
                    </div> -->
                    <div class="form-group">
                        <label>Reminder Time</label>
                        <input type="time" name="reminder_time" value="09:00">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Create Habit</button>
                        <a href="../dashboard/dashboard.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>