<?php
include 'BD_carepoint.php';

// Get real-time statistics
// Count assigned users (excluding Admin and Counselors)
$users_stmt = $pdo->prepare("
    SELECT COUNT(*) as user_count 
    FROM register 
    WHERE name != 'Admin' AND name NOT LIKE 'Coun%'
");
$users_stmt->execute();
$assigned_users = $users_stmt->fetch()['user_count'];

// Initialize session counts (will be 0 if table doesn't exist)
$scheduled_sessions = 0;
$pending_requests = 0;
$upcoming_sessions = [];

// Try to get session data if schedule table exists
try {
    // Check if schedule table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'schedule'");
    if ($table_check->rowCount() > 0) {
        // Count scheduled sessions
        $sessions_stmt = $pdo->query("SELECT COUNT(*) as session_count FROM schedule WHERE status = 'confirmed'");
        $scheduled_sessions = $sessions_stmt->fetch()['session_count'];

        // Count pending requests
        $pending_stmt = $pdo->query("SELECT COUNT(*) as pending_count FROM schedule WHERE status = 'pending'");
        $pending_requests = $pending_stmt->fetch()['pending_count'];

        // Get upcoming sessions
        $upcoming_sessions_stmt = $pdo->query("
            SELECT s.*, r.name as client_name 
            FROM schedule s 
            JOIN register r ON s.user_id = r.id 
            WHERE s.session_date >= CURDATE() 
            ORDER BY s.session_date ASC, s.session_time ASC 
            LIMIT 10
        ");
        $upcoming_sessions = $upcoming_sessions_stmt->fetchAll();
    }
} catch (Exception $e) {
    // Table doesn't exist or other error, use default values
    $scheduled_sessions = 0;
    $pending_requests = 0;
    $upcoming_sessions = [];
}

// Get recent journal entries as an alternative metric
try {
    $recent_journals_stmt = $pdo->query("
        SELECT COUNT(*) as journal_count 
        FROM user_journals 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recent_journals = $recent_journals_stmt->fetch()['journal_count'];
} catch (Exception $e) {
    $recent_journals = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Care Point - Counselor Dashboard</title>
    <style>
      :root {
        --main-color: #12a559;
        --bg-color: #1c1c1c;
        --text-color: #fff;
        --snd-bg-color: #222;
      }

      body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: var(--bg-color);
        color: var(--text-color);
      }

      header {
        background-color: var(--main-color);
        color: var(--bg-color);
        padding: 15px 20px;
        text-align: center;
        font-weight: bold;
        letter-spacing: 1px;
        font-size: 1.5em;
      }

      .dashboard {
        display: flex;
        min-height: 100vh;
      }

      nav {
        width: 220px;
        background-color: var(--snd-bg-color);
        padding: 20px 0;
      }

      nav a {
        display: block;
        padding: 12px 20px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        margin: 6px 20px;
        font-weight: bold;
        transition: background 0.3s, color 0.3s;
      }

      nav a:hover,
      nav a.active {
        background-color: var(--main-color);
        color: var(--bg-color);
      }

      main {
        flex: 1;
        padding: 20px;
        background-color: var(--snd-bg-color);
        border-radius: 12px;
        margin: 20px;
      }

      h2 {
        color: var(--main-color);
        margin-bottom: 20px;
      }

      .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
      }

      .card {
        background-color: var(--bg-color);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 255, 123, 0.2);
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
      }

      .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 255, 123, 0.5);
      }

      .card h3 {
        margin: 0;
        color: var(--main-color);
        font-size: 1.1em;
      }

      .card p {
        font-size: 1.8em;
        font-weight: bold;
        margin: 10px 0 0;
        color: var(--text-color);
      }

      table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-color);
        border-radius: 8px;
        overflow: hidden;
        color: var(--text-color);
      }

      th,
      td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid var(--snd-bg-color);
      }

      th {
        background-color: var(--main-color);
        color: var(--bg-color);
      }

      tr:nth-child(even) {
        background-color: var(--snd-bg-color);
      }

      .status-confirmed {
        color: #27ae60;
        font-weight: bold;
      }

      .status-pending {
        color: #f39c12;
        font-weight: bold;
      }

      .status-cancelled {
        color: #e74c3c;
        font-weight: bold;
      }

      .no-data {
        text-align: center;
        color: #95a5a6;
        font-style: italic;
        padding: 20px;
      }

      /* Responsive tweaks */
      @media (max-width: 600px) {
        .dashboard {
          flex-direction: column;
        }
        nav {
          width: 100%;
          min-height: auto;
          padding: 10px 0;
          display: flex;
          justify-content: space-around;
        }
        nav a {
          margin: 0 10px;
          padding: 10px 12px;
          font-size: 0.9em;
        }
        main {
          margin: 10px;
          border-radius: 0;
          padding: 15px;
        }
        .stats {
          grid-template-columns: 1fr 1fr;
        }
      }
    </style>
  </head>
  <body>
    <header>🌿 Care Point Counselor Dashboard</header>

    <div class="dashboard">
      <nav>
        <a href="Councilor.php" class="active">Dashboard</a>
        <a href="My Clients.php">My Clients</a>
        <a href="GroupChat.php">💬 Group Chat</a>
        <a href="Councior_cbt.php">CBT Upload</a>
        <a href="schedule_counciler.php">Schedule</a>
        <a href="CouncilorSettings.php">Settings</a>
        <a href="logout.php">Logout</a>
      </nav>

      <main>
        <h2>Overview</h2>
        <div class="stats">
          <div class="card">
            <h3>Assigned Users</h3>
            <p><?php echo $assigned_users; ?></p>
          </div>
          <div class="card">
            <h3>Scheduled Sessions</h3>
            <p><?php echo $scheduled_sessions; ?></p>
          </div>
          <div class="card">
            <h3><?php echo ($scheduled_sessions > 0) ? 'Pending Requests' : 'Recent Journals'; ?></h3>
            <p><?php echo ($scheduled_sessions > 0) ? $pending_requests : $recent_journals; ?></p>
          </div>
        </div>

        <h2><?php echo ($scheduled_sessions > 0) ? 'Upcoming Sessions' : 'Recent Activity'; ?></h2>
        <table>
          <thead>
            <tr>
              <?php if ($scheduled_sessions > 0): ?>
                <th>Client</th>
                <th>Date</th>
                <th>Time</th>
                <th>Session Type</th>
                <th>Status</th>
              <?php else: ?>
                <th>Client</th>
                <th>Journal Title</th>
                <th>Created Date</th>
                <th>Status</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if ($scheduled_sessions > 0 && !empty($upcoming_sessions)): ?>
              <?php foreach ($upcoming_sessions as $session): ?>
                <tr>
                  <td><?php echo htmlspecialchars($session['client_name']); ?></td>
                  <td><?php echo date('Y-m-d', strtotime($session['session_date'])); ?></td>
                  <td><?php echo date('h:i A', strtotime($session['session_time'])); ?></td>
                  <td><?php echo htmlspecialchars($session['session_type']); ?></td>
                  <td class="status-<?php echo strtolower($session['status']); ?>">
                    <?php echo ucfirst(htmlspecialchars($session['status'])); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php elseif ($scheduled_sessions == 0): ?>
              <?php 
              // Show recent journal entries instead
              try {
                $recent_journals_list = $pdo->query("
                  SELECT uj.title, uj.created_at, r.name as client_name
                  FROM user_journals uj
                  JOIN register r ON uj.user_id = r.id
                  WHERE r.name != 'Admin' AND r.name NOT LIKE 'Coun%'
                  ORDER BY uj.created_at DESC
                  LIMIT 5
                ");
                $recent_journals_data = $recent_journals_list->fetchAll();
                
                if (!empty($recent_journals_data)) {
                  foreach ($recent_journals_data as $journal) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($journal['client_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($journal['title']) . '</td>';
                    echo '<td>' . date('Y-m-d H:i', strtotime($journal['created_at'])) . '</td>';
                    echo '<td class="status-confirmed">Completed</td>';
                    echo '</tr>';
                  }
                } else {
                  echo '<tr><td colspan="4" class="no-data">No recent activity found.</td></tr>';
                }
              } catch (Exception $e) {
                echo '<tr><td colspan="4" class="no-data">No recent activity found.</td></tr>';
              }
              ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="no-data">No upcoming sessions found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </main>
    </div>
  </body>
</html>
