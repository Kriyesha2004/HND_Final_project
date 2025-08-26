<?php
include 'BD_carepoint.php';

// Handle search
$search = $_GET['search'] ?? '';

// Filter out Admin and Coun1..Coun10 users
$clients_stmt = $pdo->prepare("
    SELECT id, name, email FROM register
    WHERE name LIKE ?
      AND name != 'Admin'
      AND name NOT LIKE 'Coun%'
    ORDER BY name ASC
");
$clients_stmt->execute(['%' . $search . '%']);
$clients = $clients_stmt->fetchAll();

// Get last 7 mood answers for a client
function getMoodData($pdo, $client_id) {
    $stmt = $pdo->prepare("
        SELECT qa.answer_date, qa.answer
        FROM user_profile_answers qa
        JOIN profile_questions pq ON qa.question_id = pq.id
        WHERE qa.user_id = ? AND pq.question_type IN ('emoji_scale', 'emoji_choice')
        ORDER BY qa.answer_date DESC
        LIMIT 7
    ");
    $stmt->execute([$client_id]);
    $data = [];
    while ($row = $stmt->fetch()) {
        $data[] = [
            'date' => $row['answer_date'],
            'answer' => $row['answer']
        ];
    }
    return array_reverse($data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Clients</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    .search-box { 
      margin-bottom: 30px; 
    }
    
    .search-input { 
      padding: 10px; 
      border-radius: 6px; 
      border: none; 
      width: 250px; 
      background: var(--bg-color);
      color: var(--text-color);
    }
    
    .search-btn { 
      padding: 10px 18px; 
      border-radius: 6px; 
      border: none; 
      background: var(--main-color); 
      color: var(--bg-color); 
      cursor: pointer; 
      font-weight: bold; 
    }
    
    .search-btn:hover {
      background: #0d8a47;
    }
    
    table { 
      width: 100%; 
      border-collapse: collapse; 
      margin-bottom: 30px; 
      background: var(--bg-color);
      border-radius: 8px;
      overflow: hidden;
    }
    
    th, td { 
      padding: 12px; 
      border-bottom: 1px solid var(--snd-bg-color); 
      text-align: left; 
    }
    
    th { 
      background: var(--main-color); 
      color: var(--bg-color); 
    }
    
    tr:hover { 
      background: var(--snd-bg-color); 
    }
    
    .mood-chart-cell { 
      width: 320px; 
    }
    
    .client-name { 
      font-weight: bold; 
      color: var(--main-color); 
    }
    
    .client-email { 
      color: var(--text-color); 
      font-size: 0.95em; 
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
    }
  </style>
</head>
<body>
  <header>🌿 Care Point Counselor Dashboard</header>

  <div class="dashboard">
    <nav>
      <a href="Councilor.php">Dashboard</a>
      <a href="My Clients.php" class="active">My Clients</a>
      <a href="GroupChat.php">💬 Group Chat</a>
      <a href="Councior_cbt.php">CBT Upload</a>
      <a href="schedule_counciler.php">Schedule</a>
      <a href="CouncilorSettings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </nav>

        <main>
      <h2>My Clients</h2>
    <form class="search-box" method="get">
      <input type="text" name="search" class="search-input" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit" class="search-btn">Search</button>
    </form>
    <table>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Mood Chart (Last 7)</th>
      </tr>
      <?php foreach ($clients as $client): 
        $mood_data = getMoodData($pdo, $client['id']);
        $labels = json_encode(array_column($mood_data, 'date'));
        $answers = json_encode(array_column($mood_data, 'answer'));
        $chart_id = "moodChart_" . $client['id'];
      ?>
      <tr>
        <td class="client-name"><?php echo htmlspecialchars($client['name']); ?></td>
        <td class="client-email"><?php echo htmlspecialchars($client['email']); ?></td>
        <td class="mood-chart-cell">
          <canvas id="<?php echo $chart_id; ?>" height="80"></canvas>
          <script>
            (function() {
              const labels = <?php echo $labels; ?>;
              const answers = <?php echo $answers; ?>;
              function moodToScore(mood) {
                switch (mood) {
                  case '😊': return 100;
                  case '😐': return 60;
                  case '😔': return 30;
                  case '😢': return 10;
                  case '😡': return 40;
                  default: return 50;
                }
              }
              const scores = answers.map(moodToScore);
              const ctx = document.getElementById('<?php echo $chart_id; ?>').getContext('2d');
              new Chart(ctx, {
                type: 'line',
                data: {
                  labels: labels,
                  datasets: [{
                    label: 'Mood',
                    data: scores,
                    borderColor: '#a8e063',
                    backgroundColor: 'rgba(168,224,99,0.15)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#a8e063',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                  }]
                },
                options: {
                  responsive: false,
                  plugins: { legend: { display: false } },
                  scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                    x: { grid: { display: false } }
                  }
                }
              });
            })();
          </script>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($clients)): ?>
      <tr>
        <td colspan="3" style="color:#e74c3c;">No clients found.</td>
      </tr>
      <?php endif; ?>
    </table>
    </main>
  </div>
</body>
</html>