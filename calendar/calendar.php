<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

$firstDay = strtotime("$year-$month-01");
$daysInMonth = date('t', $firstDay);
$startDay = date('w', $firstDay);

// Get all habits for the user
$stmt = $pdo->prepare("
    SELECT habit_id, habit_name
    FROM habits
    WHERE user_id = ?
    ORDER BY habit_name
");
$stmt->execute([$user_id]);

$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get tracking data for the month
$startDate = "$year-$month-01";
$endDate = date('Y-m-t', strtotime($startDate));
$stmt = $pdo->prepare("SELECT t.habit_id, t.completed_date, t.status FROM tracking t 
                       JOIN habits h ON t.habit_id = h.habit_id 
                       WHERE h.user_id = ? AND completed_date BETWEEN ? AND ?");
$stmt->execute([$user_id, $startDate, $endDate]);
$trackingData = $stmt->fetchAll();
$trackingMap = [];
foreach ($trackingData as $data) {
    $trackingMap[$data['habit_id']][$data['completed_date']] = $data['status'];
}


// Previous month
$prevMonth = $month - 1;
$prevYear = $year;

if ($prevMonth == 0) {
    $prevMonth = 12;
    $prevYear--;
}

// Next month
$nextMonth = $month + 1;
$nextYear = $year;

if ($nextMonth == 13) {
    $nextMonth = 1;
    $nextYear++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Habit Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/calendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="brand"><i class="fas fa-check-circle"></i><span>Habit Tracker</span></div>
            <ul class="nav-links">
                <li><a href="../dashboard/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="../habits/habits.php"><i class="fas fa-list-ul"></i> Habits</a></li>
                <li><a href="../calendar/calendar.php" class="active"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
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
            <div class="calendar-container">
                <div class="calendar-header">
                    <a href="?year=<?php echo $year; ?>&month=<?php echo $month-1; ?>" class="nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h2><?php echo date('F Y', $firstDay); ?></h2>
                    <a href="?year=<?php echo $year; ?>&month=<?php echo $month+1; ?>" class="nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="calendar-grid">
                    <div class="day-label">Sun</div>
                    <div class="day-label">Mon</div>
                    <div class="day-label">Tue</div>
                    <div class="day-label">Wed</div>
                    <div class="day-label">Thu</div>
                    <div class="day-label">Fri</div>
                    <div class="day-label">Sat</div>
                    
                    <?php
                    // Empty cells before first day
                    for ($i = 0; $i < $startDay; $i++) {
                        echo '<div class="day empty"></div>';
                    }
                    
                    // Days of the month
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                        $isToday = ($date === date('Y-m-d'));
                        $hasData = false;
                        $allCompleted = true;
                        
                        // Check if all habits were completed this day
                        foreach ($habits as $habit) {

    if (isset($trackingMap[$habit['habit_id']][$date])) {

        $hasData = true;

        if ($trackingMap[$habit['habit_id']][$date] != 'Completed') {
            $allCompleted = false;
        }

    } else {

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $allCompleted = false;
        }

    }
}
                        $class = 'day';
                        if ($isToday) $class .= ' today';
                        if ($hasData && $allCompleted) $class .= ' completed';
                        else if (!$hasData && strtotime($date) < strtotime(date('Y-m-d'))) $class .= ' missed';
                        else if (strtotime($date) > strtotime(date('Y-m-d'))) $class .= ' future';
                        ?>
                        <div class="<?php echo $class; ?>">
                            <?php echo $day; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/calendar.js"></script>
</body>
</html>