<?php

include 'BD_carepoint.php';

// Fetch all schedules
$stmt = $pdo->query("SELECT * FROM schedule ORDER BY date, time");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle confirmation
if (isset($_GET['confirm'])) {
    $id = intval($_GET['confirm']);
    // You can update the schedule table to mark as confirmed, e.g. add a 'confirmed' column
    $stmt = $pdo->prepare("UPDATE schedule SET confirmed = 1 WHERE id = ?");
    $stmt->execute([$id]);
    $confirmed_message = "Schedule ID $id confirmed!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Councilor Schedules</title>
    <style>
        body { background: #222; color: #fff; font-family: Arial; }
        .dashboard { display: flex; min-height: 100vh; }
        nav {
            width: 220px;
            background-color: #222;
            padding: 20px 0;
            min-height: 100vh;
        }
        nav a {
            display: block;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin: 4px 20px;
            font-weight: bold;
            transition: background 0.3s, color 0.3s;
        }
        nav a:hover {
            background-color: #10ab5b;
            color: #222;
        }
        main {
            flex: 1;
            padding: 20px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #444; }
        th { background: #10ab5b; color: #222; }
        tr:nth-child(even) { background: #333; }
        .confirm-btn { color: #fff; background: #10ab5b; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; text-decoration: none; }
        .confirmed { background: #27ae60; color: #fff; padding: 6px 12px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav>
        <a href="Councilor.php">Dashboard</a>
        
        <a href="Councior_cbt.php">CBT Exercises</a>
        <a href="schedule_counciler.php">My Schedules</a>
        <a href="CouncilorSettings.php">Settings</a>
        <a href="Login.php">Logout</a>
    </nav>
    <main>
        <h2>All Schedules</h2>
        <?php if (!empty($confirmed_message)): ?>
            <p class="confirmed"><?php echo htmlspecialchars($confirmed_message); ?></p>
        <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Councilor Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($schedules as $schedule): ?>
            <tr>
                <td><?php echo $schedule['id']; ?></td>
                <td><?php echo htmlspecialchars($schedule['date']); ?></td>
                <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                <td><?php echo htmlspecialchars($schedule['counciler_name']); ?></td>
                <td>
                    <?php if (isset($schedule['confirmed']) && $schedule['confirmed']): ?>
                        <span class="confirmed">Confirmed</span>
                    <?php else: ?>
                        <a href="?confirm=<?php echo $schedule['id']; ?>" class="confirm-btn" onclick="return confirm('Confirm this schedule?');">Confirm</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($schedules)): ?>
            <tr><td colspan="5" style="text-align:center;">No schedules found.</td></tr>
            <?php endif; ?>
        </table>
    </main>
</div>
</body>
</html>