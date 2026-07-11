<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch tracking history
$stmt = $pdo->prepare("
SELECT
    t.tracking_id,
    h.habit_name,
    h.frequency,
    t.completed_date,
    t.status,
    t.notes
FROM tracking t
INNER JOIN habits h
ON t.habit_id = h.habit_id
WHERE h.user_id = ?
ORDER BY t.completed_date DESC, h.habit_name ASC
");

$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tracking History</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/tracking.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>

<body>

<div class="app-container">

<!-- Sidebar -->

<nav class="sidebar">

<div class="brand">
<i class="fas fa-check-circle"></i>
<span>Habit Tracker</span>
</div>

<ul class="nav-links">

<li>
<a href="../dashboard/dashboard.php">
<i class="fas fa-th-large"></i> Dashboard
</a>
</li>

<li>
<a href="../habits/habits.php">
<i class="fas fa-list-ul"></i> Habits
</a>
</li>

<li>
<a href="../calendar/calendar.php">
<i class="fas fa-calendar-alt"></i> Calendar
</a>
</li>

<li>
<a href="history.php" class="active">
<i class="fas fa-history"></i> History
</a>
</li>

<li>
<a href="../profile/profile.php">
<i class="fas fa-user"></i> Profile
</a>
</li>

<li>
<a href="../settings/settings.php">
<i class="fas fa-cog"></i> Settings
</a>
</li>

</ul>

<div class="sidebar-footer">

<button id="themeToggle" class="theme-btn">
<i class="fas fa-moon"></i> Dark Mode
</button>

<a href="../auth/logout.php" class="logout-btn">
<i class="fas fa-sign-out-alt"></i> Logout
</a>

</div>

</nav>

<!-- Main Content -->

<main class="main-content">

<div class="tracking-container">

<div class="page-header">

<h1>
<i class="fas fa-history"></i>
Tracking History
</h1>

</div>

<!-- Search -->

<div class="filter-controls">

<div class="filter-group">

<label>Search Habit</label>

<input
type="text"
id="searchHistory"
placeholder="Search habit...">

</div>

<div class="filter-group">
    <label>Status</label>

    <select id="statusFilter">
        <option value="">All</option>
        <option value="Completed">Completed</option>
        <option value="Missed">Missed</option>
    </select>
</div>

</div>

<?php if(empty($history)): ?>

<div class="empty-state">

<i class="fas fa-calendar-times"></i>

<h3>No Tracking History</h3>

<p>No habits have been tracked yet.</p>

</div>

<?php else: ?>

<div class="tracking-table-wrapper">

<table class="tracking-table">

<thead>

<tr>

<th>Habit</th>

<th>Date</th>

<th>Frequency</th>

<th>Status</th>

<th>Notes</th>

</tr>

</thead>

<tbody>

<?php foreach($history as $row): ?>

<tr class="history-row">

<td class="habit-name">

<?php echo htmlspecialchars($row['habit_name']); ?>

</td>

<td>

<?php echo date("d M Y", strtotime($row['completed_date'])); ?>

</td>

<td>

<?php echo $row['frequency']; ?>

</td>

<td>

<?php if($row['status']=="Completed"): ?>

<span class="status-badge completed">

Completed

</span>

<?php else: ?>

<span class="status-badge missed">

Missed

</span>

<?php endif; ?>

</td>

<td>

<?php
echo !empty($row['notes'])
? htmlspecialchars($row['notes'])
: "-";
?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

</main>

</div>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/history.js"></script>

</body>
</html>