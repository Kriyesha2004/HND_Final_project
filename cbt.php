<?php
include 'BD_carepoint.php';

// Fetch all CBT exercises
$stmt = $pdo->query("SELECT * FROM cbt_exercises ORDER BY id ASC");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Care Point - CBT</title>
  <link rel="stylesheet" href="cbt.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #000000;
      color: #333;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: auto;
    }
    h1 {
      color: #2d6a4f;
    }
    .subtitle {
      color: #555;
      margin-bottom: 20px;
    }
    .exercise-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .exercise {
      background: rgb(31, 30, 30);
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    .exercise-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #95d5b2;
      color: #1b4332;
      padding: 12px 16px;
    }
    .exercise-header h3 {
      margin: 0;
    }
    .toggle-btn {
      background: #1b4332;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
    }
    .toggle-btn:hover {
      background: #2d6a4f;
    }
    .exercise-details {
      display: none;
      padding: 15px;
      background: #f9fdfb;
    }
    .exercise-details.active {
      display: block;
    }
    .exercise-details p {
      margin: 0 0 8px 0;
    }
    .exercise-details ul, 
    .exercise-details ol {
      margin: 0;
      padding-left: 20px;
    }
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 220px;
      height: 100vh;
      background: #222;
      color: #fff;
      padding-top: 30px;
    }
    .sidebar .logo {
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.5em;
      color: #10ab5b;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .sidebar ul li {
      margin-bottom: 10px;
    }
    .sidebar ul li a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
      border-radius: 8px;
      transition: background 0.3s, color 0.3s;
    }
    .sidebar ul li.active a,
    .sidebar ul li a:hover {
      background: #10ab5b;
      color: #222;
    }
    .main-content {
      margin-left: 240px;
      padding: 40px 20px;
    }
    .page-title {
      color: #10ab5b;
      margin-bottom: 30px;
    }
    .logoimg {
        width: 50px;
        height: 50px;
      } 
      
      
      .logo{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
      }

    .exercise-url { margin-top: 10px; }
    .exercise-url a { color: #10ab5b; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="sidebar">
     <h2 class="logo">
        <img src="./stickers/primary 2.png" alt="" class="logoimg" /> Care Point
      </h2>
    <ul>
      <li><a href="Sidebar.html">🏠 Home</a></li>
      <li><a href="Music.html">🎵 Music</a></li>
      <li><a href="Journaling.php">📓 Journaling</a></li>
      <li class="active"><a href="#">🧠 CBT</a></li>
      <li><a href="Myprofile.php">👤 My Profile</a></li>
      <li><a href="Home.html">🚪 Log Out</a></li>
    </ul>
  </div>
  <div class="main-content">
    <h1 class="page-title">CBT</h1>
    <div class="container">
      <h1>🌿 CBT Exercises</h1>
      <p class="subtitle">Learn and practice proven CBT techniques to improve your mental wellbeing.</p>
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
    </div>
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