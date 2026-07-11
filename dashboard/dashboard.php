<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
SELECT
browser_notifications,
email_notifications,
daily_summary,
streak_alerts
FROM users
WHERE user_id=?
");

$stmt->execute([$user_id]);

$settings = $stmt->fetch();
$username = $_SESSION['username'];

// Get statistics
// Total habits
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM habits WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_habits = $stmt->fetch()['total'];

// Completed today
$stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM tracking t 
                       JOIN habits h ON t.habit_id = h.habit_id 
                       WHERE h.user_id = ? AND t.completed_date = CURDATE() AND t.status = 'Completed'");
$stmt->execute([$user_id]);
$completed_today = $stmt->fetch()['completed'];

// Missed today
$stmt = $pdo->prepare("SELECT COUNT(*) as missed FROM tracking t 
                       JOIN habits h ON t.habit_id = h.habit_id 
                       WHERE h.user_id = ? AND t.completed_date = CURDATE() AND t.status = 'Missed'");
$stmt->execute([$user_id]);
$missed_today = $stmt->fetch()['missed'];

// Get all habits with today's status
$stmt = $pdo->prepare("SELECT h.*, 
                       (SELECT status FROM tracking WHERE habit_id = h.habit_id 
                       AND completed_date = CURDATE()
                       ) as today_status
                       FROM habits h 
                       WHERE h.user_id = ? 
                       ORDER BY h.created_at DESC");
$stmt->execute([$user_id]);
$habits = $stmt->fetchAll();

// Function to get current streak
function getCurrentStreak($pdo, $habit_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as streak
        FROM tracking
        WHERE habit_id = ?
        AND status = 'Completed'
        AND completed_date > COALESCE((
            SELECT MAX(completed_date)
            FROM tracking
            WHERE habit_id = ?
            AND status = 'Missed'
        ), '1900-01-01')
    ");
    $stmt->execute([$habit_id, $habit_id]);
    $result = $stmt->fetch();
    return $result ? (int)$result['streak'] : 0;
}

// Function to get longest streak
function getLongestStreak($pdo, $habit_id) {
    $stmt = $pdo->prepare("
        SELECT MAX(streak_count) as longest_streak
        FROM (
            SELECT COUNT(*) as streak_count
            FROM tracking t1
            WHERE t1.habit_id = ?
            AND t1.status = 'Completed'
            AND NOT EXISTS (
                SELECT 1
                FROM tracking t2
                WHERE t2.habit_id = t1.habit_id
                AND t2.status = 'Missed'
                AND t2.completed_date > t1.completed_date
            )
            GROUP BY t1.completed_date
        ) as streaks
    ");
    $stmt->execute([$habit_id]);
    $result = $stmt->fetch();
    return $result ? (int)$result['longest_streak'] : 0;
}

// Calculate overall streaks
$best_current_streak = 0;
$best_longest_streak = 0;
foreach ($habits as $habit) {
    $current = getCurrentStreak($pdo, $habit['habit_id']);
    $longest = getLongestStreak($pdo, $habit['habit_id']);
    if ($current > $best_current_streak) $best_current_streak = $current;
    if ($longest > $best_longest_streak) $best_longest_streak = $longest;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="../habits/habits.php"><i class="fas fa-list-ul"></i> Habits</a></li>
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
            <div class="dashboard-header">
                <h1>
                    Welcome, <?php echo htmlspecialchars($username); ?>!
                    <small>Here's your habit tracking summary</small>
                </h1>
                <div class="header-actions">
                    <a href="../habits/add_habit.php" class="btn-primary">
                        <i class="fas fa-plus"></i> New Habit
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_habits; ?></h3>
                        <p>Total Habits</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_today; ?></h3>
                        <p>Completed Today</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $best_current_streak; ?></h3>
                        <p>Current Streak</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $best_longest_streak; ?></h3>
                        <p>Longest Streak</p>
                    </div>
                </div>
            </div>

            <!-- Habits List -->
            <div class="habits-section">
                <div class="section-header">
                    <h2>Today's Habits</h2>
                    <span class="habit-count"><?php echo count($habits); ?> habits</span>
                </div>
                
                <div class="habit-grid">
                    <?php if (empty($habits)): ?>
                        <div style="text-align: center; padding: 40px; background: var(--white); border-radius: var(--radius);">
                            <i class="fas fa-clipboard-list" style="font-size: 3rem; color: var(--text-light);"></i>
                            <h3 style="margin-top: 16px;">No habits yet</h3>
                            <p style="color: var(--text-light);">Start by creating your first habit!</p>
                            <a href="../habits/add_habit.php" class="btn-primary" style="margin-top: 16px;">
                                <i class="fas fa-plus"></i> Create Habit
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($habits as $habit): 
                            $current_streak = getCurrentStreak($pdo, $habit['habit_id']);
                            $longest_streak = getLongestStreak($pdo, $habit['habit_id']);
                        ?>
                            <div class="habit-card">
                                <div class="habit-header">
                                    <h3><?php echo htmlspecialchars($habit['habit_name']); ?></h3>
                                    <span class="frequency-badge"><?php echo $habit['frequency']; ?></span>

                                    
                                </div>
                                
                                <?php if ($habit['description']): ?>
                                    <p class="habit-description"><?php echo htmlspecialchars($habit['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="habit-stats">
                                    <span>
                                        <i class="fas fa-fire" style="color: var(--warning);"></i>
                                        Streak: <span class="streak-number"><?php echo $current_streak; ?></span>
                                    </span>
                                    <span>
                                        <i class="fas fa-trophy" style="color: var(--primary);"></i>
                                        Best: <span class="streak-number"><?php echo $longest_streak; ?></span>
                                    </span>
                                </div>
                                
                                <div class="habit-actions">
                                    <form action="../tracking/track_habit.php" method="POST" style="flex: 1;">
                                        <input type="hidden" name="habit_id" value="<?php echo $habit['habit_id']; ?>">
                                        <input type="hidden" name="redirect" value="../dashboard/dashboard.php">
                                        
                                        <?php if ($habit['today_status'] === 'Completed'): ?>
                                            <button type="button" class="track-btn completed" disabled>
                                                <i class="fas fa-check"></i> Completed
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="track-btn pending">
                                                <i class="fas fa-check-circle"></i> Mark Complete
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <div class="action-icons">
                                        <a href="../habits/edit_habit.php?id=<?php echo $habit['habit_id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../habits/delete_habit.php?id=<?php echo $habit['habit_id']; ?>" 
                                           class="delete" title="Delete"
                                           onclick="return confirm('Delete this habit?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/reminders.js"></script>
</body>
</html>