<?php

include 'BD_carepoint.php';

// Handle form submission to add a schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $counciler_name = $_POST['counciler_name'] ?? '';

    if ($date && $time && $counciler_name) {
        $stmt = $pdo->prepare("INSERT INTO schedule (date, time, counciler_name) VALUES (?, ?, ?)");
        $stmt->execute([$date, $time, $counciler_name]);
    }
}

// Fetch all schedules (including confirmation status)
$stmt = $pdo->query("SELECT * FROM schedule ORDER BY date, time");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Schedule Admin</title>
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
        .form-box { margin: 20px 0; background: #333; padding: 20px; border-radius: 8px; }
        label { display: block; margin-bottom: 8px; }
        input[type="text"], input[type="date"], input[type="time"] {
            width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 5px; border: none;
        }
        button { background: #10ab5b; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0e8c4a; }
        .confirmed { background: #27ae60; color: #fff; padding: 6px 12px; border-radius: 5px; }
        .pending { background: #e67e22; color: #fff; padding: 6px 12px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav>
        <a href="Admin.php">Dashboard</a>
        <a href="User_Management.php">User Management</a>
            <a href="Register Councilers.php">Register Councilor</a>
        <a href="schedule_admin.php">Schedules</a>
        
        <a href="Login.php">Logout</a>
    </nav>
    <main>
        <h2>Schedules for Councilors</h2>
        <div class="form-box">
            <form method="post">
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" required>
                <label for="time">Time:</label>
                <input type="time" name="time" id="time" required>
                <label for="counciler_name">Councilor Name:</label>
                <input type="text" name="counciler_name" id="counciler_name" required>
                <button type="submit">Add Schedule</button>
            </form>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Councilor Name</th>
                <th>Status</th>
            </tr>
            <?php foreach ($schedules as $schedule): ?>
            <tr>
                <td><?php echo $schedule['id']; ?></td>
                <td><?php echo htmlspecialchars($schedule['date']); ?></td>
                <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                <td><?php echo htmlspecialchars($schedule['counciler_name']); ?></td>
                <td>
                    <?php
                    if (isset($schedule['confirmed']) && $schedule['confirmed']) {
                        echo '<span class="confirmed">Confirmed</span>';
                    } else {
                        echo '<span class="pending">Pending</span>';
                    }
                    ?>
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