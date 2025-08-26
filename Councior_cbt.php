<?php
include 'BD_carepoint.php';

// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission to add a new exercise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cbt'])) {
    $title = $_POST['title'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $steps = $_POST['steps'] ?? '';
    $url = $_POST['url'] ?? '';

    if ($title && $purpose && $steps) {
        $stmt = $pdo->prepare("INSERT INTO cbt_exercises (title, purpose, steps, url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $purpose, $steps, $url]);
        $success = "CBT exercise added successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch all CBT exercises
$stmt = $pdo->query("SELECT * FROM cbt_exercises ORDER BY id ASC");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CBT Exercises</title>
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

    .exercise-list { 
      display: flex; 
      flex-direction: column; 
      gap: 15px; 
    }
    
    .exercise { 
      background: var(--bg-color); 
      border-radius: 10px; 
      box-shadow: 0 4px 8px rgba(0, 255, 123, 0.2); 
      overflow: hidden; 
    }
    
    .exercise-header { 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      background: var(--main-color); 
      color: var(--bg-color); 
      padding: 12px 16px; 
    }
    
    .exercise-header h3 { 
      margin: 0; 
    }
    
    .toggle-btn { 
      background: var(--bg-color); 
      color: var(--text-color); 
      border: none; 
      padding: 6px 12px; 
      border-radius: 5px; 
      cursor: pointer; 
    }
    
    .toggle-btn:hover { 
      background: var(--main-color); 
      color: var(--bg-color); 
    }
    
    .exercise-details { 
      display: none; 
      padding: 15px; 
      background: var(--snd-bg-color); 
    }
    
    .exercise-details.active { 
      display: block; 
    }
    
    .exercise-details p { 
      margin: 0 0 8px 0; 
    }
    
    .exercise-details ul, .exercise-details ol { 
      margin: 0; 
      padding-left: 20px; 
    }
    
    .exercise-url { 
      margin-top: 10px; 
    }
    
    .exercise-url a { 
      color: var(--main-color); 
      text-decoration: underline; 
    }
    
    .form-box { 
      background: var(--bg-color); 
      padding: 20px; 
      border-radius: 8px; 
      margin-bottom: 30px; 
      box-shadow: 0 4px 8px rgba(0, 255, 123, 0.2);
    }
    
    .form-box label { 
      display: block; 
      margin-bottom: 6px; 
      color: var(--main-color); 
    }
    
    .form-box input, .form-box textarea { 
      width: 100%; 
      padding: 8px; 
      margin-bottom: 12px; 
      border-radius: 5px; 
      border: none; 
      background: var(--snd-bg-color); 
      color: var(--text-color); 
    }
    
    .form-box button { 
      background: var(--main-color); 
      color: var(--bg-color); 
      border: none; 
      padding: 10px 20px; 
      border-radius: 5px; 
      cursor: pointer; 
      font-weight: bold;
    }
    
    .form-box button:hover { 
      background: #0d8a47; 
    }
    
    .success { 
      color: #27ae60; 
      margin-bottom: 10px; 
    }
    
    .error { 
      color: #e74c3c; 
      margin-bottom: 10px; 
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
      <a href="My Clients.php">My Clients</a>
      <a href="GroupChat.php">💬 Group Chat</a>
      <a href="Councior_cbt.php" class="active">CBT Upload</a>
      <a href="schedule_counciler.php">Schedule</a>
      <a href="CouncilorSettings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </nav>

    <main>
      <h2>🌿 CBT Exercises</h2>
    <div class="form-box">
      <h2>Add CBT Exercise</h2>
      <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      <form method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>
        <label for="purpose">Purpose:</label>
        <input type="text" name="purpose" id="purpose" required>
        <label for="steps">Steps (one per line):</label>
        <textarea name="steps" id="steps" rows="5" required></textarea>
        <label for="url">URL (optional):</label>
        <input type="text" name="url" id="url">
        <button type="submit" name="add_cbt">Add Exercise</button>
      </form>
    </div>
    <div class="exercise-list">
      <?php foreach ($exercises as $exercise): ?>
        <div class="exercise">
          <div class="exercise-header">
            <h3><?php echo htmlspecialchars($exercise['title']); ?></h3>
            <button class="toggle-btn">View Details</button>
          </div>
          <div class="exercise-details">
            <p><strong>Purpose:</strong> <?php echo htmlspecialchars($exercise['purpose']); ?></p>
            <ul>
              <?php
                $steps = explode("\n", $exercise['steps']);
                foreach ($steps as $step) {
                  echo '<li>' . htmlspecialchars($step) . '</li>';
                }
              ?>
            </ul>
            <?php if (!empty($exercise['url'])): ?>
              <div class="exercise-url">
                <strong>Learn more:</strong>
                <a href="<?php echo htmlspecialchars($exercise['url']); ?>" target="_blank">
                  <?php echo htmlspecialchars($exercise['url']); ?>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($exercises)): ?>
        <p style="color:#e74c3c;">No CBT exercises found in the database.</p>
      <?php endif; ?>
    </div>
    </main>
  </div>
  <script>
    document.querySelectorAll('.toggle-btn').forEach(button => {
      button.addEventListener('click', () => {
        const details = button.parentElement.nextElementSibling;
        details.classList.toggle('active');
        button.textContent = details.classList.contains('active')
          ? 'Hide Details'
          : 'View Details';
      });
    });
  </script>
</body>
</html>