
<?php

require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM habits
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Habits</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/habits.css">
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
                <li><a href="../dashboard/dashboard.php" ><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="../habits/habits.php" class="active"><i class="fas fa-list-ul"></i> Habits</a></li>
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

<div class="habits-container">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-list-check"></i> My Habits</h1>
            <p>Track your daily habits and stay consistent.</p>
        </div>

        <a href="add_habit.php" class="btn-add">
            <i class="fas fa-plus"></i> Add Habit
        </a>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <i class="fas fa-list"></i>
            <h2><?php echo count($habits); ?></h2>
            <span>Total Habits</span>
        </div>

        <div class="stat-card green">
            <i class="fas fa-check-circle"></i>
            <h2><?php echo count($habits); ?></h2>
            <span>Active</span>
        </div>

        <div class="stat-card orange">
            <i class="fas fa-bell"></i>
            <h2>
                <?php
                $reminders=0;
                foreach($habits as $h){
                    if($h['reminder_enabled']) $reminders++;
                }
                echo $reminders;
                ?>
            </h2>
            <span>Reminders</span>
        </div>

    </div>

    <div class="search-box">

        <i class="fas fa-search"></i>

        <input
        type="text"
        id="searchHabit"
        placeholder="Search habits...">

    </div>

    <div class="habit-grid">

<?php if(empty($habits)): ?>

<div class="empty-state">

<i class="fas fa-seedling"></i>

<h2>No Habits Yet</h2>

<p>Start building better habits today.</p>

<a href="add_habit.php" class="btn-add">
Create First Habit
</a>

</div>

<?php else: ?>

<?php foreach($habits as $habit): ?>

<div class="habit-card">

<div class="card-top">

<h2 class="habit-title">

<i class="fas fa-check-circle"></i>

<?php echo htmlspecialchars($habit['habit_name']); ?>

</h2>

<span class="badge">

<?php echo $habit['frequency']; ?>

</span>

</div>

<p class="description">

<?php echo htmlspecialchars($habit['description']); ?>

</p>

<div class="habit-info">

<div>

<i class="fas fa-bell"></i>

<?php

echo $habit['reminder_enabled']
? "Reminder ON"
: "Reminder OFF";

?>

</div>

<div>

<i class="fas fa-clock"></i>

<?php

echo $habit['reminder_time']
? date("g:i A",strtotime($habit['reminder_time']))
: "--";

?>

</div>

</div>

<div class="card-buttons">

<a href="edit_habit.php?id=<?php echo $habit['habit_id']; ?>" class="btn-edit">

<i class="fas fa-edit"></i>

Edit

</a>

<a
href="delete_habit.php?id=<?php echo $habit['habit_id']; ?>"
class="btn-delete"
onclick="return confirm('Delete this habit?')">

<i class="fas fa-trash"></i>

Delete

</a>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</main>



           
<script src="../assets/js/main.js"></script>
<script src="../assets/js/habits.js"></script>

</body>
</html>