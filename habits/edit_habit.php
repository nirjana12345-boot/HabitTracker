<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$habit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Get habit data
$stmt = $pdo->prepare("SELECT * FROM habits WHERE habit_id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);
$habit = $stmt->fetch();

if (!$habit) {
    $_SESSION['error'] = "Habit not found.";
    redirect('habits.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habit_name = sanitize($_POST['habit_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $frequency = $_POST['frequency'] ?? 'Daily';
    $reminder_enabled = isset($_POST['reminder_enabled']) ? 1 : 0;
    $reminder_time = $_POST['reminder_time'] ?? null;
    
    // Validation
    $errors = [];
    if (strlen($habit_name) < 3) {
        $errors[] = "Habit name must be at least 3 characters";
    }
    if (strlen($habit_name) > 100) {
        $errors[] = "Habit name must be less than 100 characters";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE habits 
                               SET habit_name = ?, description = ?, frequency = ?, 
                                   reminder_enabled = ?, reminder_time = ? 
                               WHERE habit_id = ? AND user_id = ?");
        
        if ($stmt->execute([$habit_name, $description, $frequency, $reminder_enabled, $reminder_time, $habit_id, $user_id])) {
            $_SESSION['success'] = "Habit updated successfully!";
            redirect('habits.php');
        } else {
            $error = "Failed to update habit. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Habit - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/habits.css">
        <link rel="stylesheet" href="../assets/css/edit_habit.css">

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
                <li><a href="habits.php" class="active"><i class="fas fa-list-ul"></i> Habits</a></li>
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
            <div class="edit-container">
                <div class="edit-card">
                     <h2 class="edit-title">
    <i class="fas fa-pen-to-square"></i>
    Edit Habit
</h2>

<p class="edit-subtitle">
    Update your habit details and reminder settings.
</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="habit-form">
                    <div class="form-group">
                        <label>Habit Name <span class="required">*</span></label>
                        <input type="text" name="habit_name" required minlength="3" maxlength="100" 
                               placeholder="e.g., Read 20 Pages" 
                               value="<?php echo htmlspecialchars($habit['habit_name']); ?>">
                        <small style="color: var(--text-light);">3-100 characters</small>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" 
                                  placeholder="Describe your habit (optional)"><?php echo htmlspecialchars($habit['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Frequency</label>
                            <select name="frequency">
                                <option value="Daily" <?php echo $habit['frequency'] == 'Daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="Weekly" <?php echo $habit['frequency'] == 'Weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="Monthly" <?php echo $habit['frequency'] == 'Monthly' ? 'selected' : ''; ?>>Monthly</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Reminder Time</label>
                            <input type="time" name="reminder_time" 
                                   value="<?php echo $habit['reminder_time'] ?? '09:00'; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="reminder-box">
                            <div class="form-group">
    <label>Reminder</label>

    <div class="reminder-box">

        <label class="toggle-switch">
            <input type="checkbox"
                   name="reminder_enabled"
                   <?php echo $habit['reminder_enabled'] ? 'checked' : ''; ?>>
            <span class="slider"></span>
        </label>

        <span>Enable Reminder Notification</span>

    </div>
</div>

                    <div class="edit-actions">
                        <div class="edit-actions">

    <a href="habits.php" class="btn-cancel">
        <i class="fas fa-arrow-left"></i>
        Cancel
    </a>

    <button type="submit" class="btn-save">
        <i class="fas fa-floppy-disk"></i>
        Save Changes
    </button>

</div>
                </form>
            </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>