<?php
session_start();
require 'BD_carepoint.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: Login.php");
    exit();
}

// Get user ID from session or GET parameter
$user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? 1;

try {
    // Get user details
    $user_stmt = $pdo->prepare("SELECT id, name, email FROM register WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch();
    
    if (!$user_data) {
        die("User not found");
    }
    
    // Get all questions
    $questions_stmt = $pdo->prepare("SELECT * FROM profile_questions ORDER BY id");
    $questions_stmt->execute();
    $questions = $questions_stmt->fetchAll();
    
    // Get user's answers for today
    $answers_stmt = $pdo->prepare("
        SELECT qa.question_id, qa.answer
        FROM user_profile_answers qa
        WHERE qa.user_id = ? AND qa.answer_date = CURDATE()
    ");
    $answers_stmt->execute([$user_id]);
    $today_answers = [];
    while ($row = $answers_stmt->fetch()) {
        $today_answers[$row['question_id']] = $row['answer'];
    }

    // Get last 7 mood answers for the chart
    $mood_answers_stmt = $pdo->prepare("
        SELECT qa.answer_date, qa.answer
        FROM user_profile_answers qa
        JOIN profile_questions pq ON qa.question_id = pq.id
        WHERE qa.user_id = ? AND pq.question_type IN ('emoji_scale', 'emoji_choice')
        ORDER BY qa.answer_date DESC
        LIMIT 7
    ");
    $mood_answers_stmt->execute([$user_id]);
    $mood_data = [];
    while ($row = $mood_answers_stmt->fetch()) {
        $mood_data[] = [
            'date' => $row['answer_date'],
            'answer' => $row['answer']
        ];
    }
    $mood_data = array_reverse($mood_data); // oldest first

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Care Point - My Profile</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #0f0f0f;
      color: #fff;
      display: flex;
    }
    .sidebar {
      width: 220px;
      background-color: #111;
      padding: 20px;
      height: 100vh;
    }
    .logo {
      color: #4ade80;
      font-size: 1.5em;
      margin-bottom: 30px;
    }
    ul {
      list-style: none;
      padding: 0;
    }
    li {
      margin: 15px 0;
    }
    a {
      color: #bbb;
      text-decoration: none;
      font-size: 1em;
    }
    .active a {
      color: #4ade80;
      font-weight: bold;
    }
    .main-content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .header-content h1 {
      color: #4ade80;
      margin-bottom: 5px;
      font-size: 1.8em;
    }
    .welcome-text {
      color: #bbb;
      font-size: 0.9em;
      margin: 0;
    }
    .settings-btn {
      background: none;
      border: none;
      font-size: 1.2em;
      color: #bbb;
      cursor: pointer;
    }
    .profile-container {
      margin-top: 20px;
    }
    .profile-card {
      text-align: center;
      margin-bottom: 20px;
    }
    .profile-pic {
      position: relative;
      margin: auto;
      margin-bottom: 15px;
    }
    .profile-avatar {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #4ade80, #22c55e);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: auto;
      box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3);
    }
    .avatar-text {
      font-size: 2.5em;
      font-weight: bold;
      color: white;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .profile-status {
      position: absolute;
      bottom: 5px;
      right: 50%;
      transform: translateX(50%);
      display: flex;
      align-items: center;
      gap: 5px;
      background: rgba(0,0,0,0.8);
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
    }
    .status-dot {
      width: 8px;
      height: 8px;
      background: #4ade80;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }
    .status-text {
      color: #4ade80;
      font-weight: 500;
    }
    .profile-stats {
      display: flex;
      justify-content: space-around;
      margin: 20px 0;
      gap: 20px;
    }
    .stat-item {
      text-align: center;
      padding: 10px;
      background: rgba(74, 222, 128, 0.1);
      border-radius: 10px;
      border: 1px solid rgba(74, 222, 128, 0.2);
    }
    .stat-number {
      display: block;
      font-size: 1.5em;
      font-weight: bold;
      color: #4ade80;
    }
    .stat-label {
      display: block;
      font-size: 0.8em;
      color: #bbb;
      margin-top: 5px;
    }
    .user-details {
      background: rgba(74, 222, 128, 0.05);
      border-radius: 12px;
      padding: 20px;
      margin: 20px 0;
      border: 1px solid rgba(74, 222, 128, 0.1);
    }
    .detail-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid rgba(74, 222, 128, 0.1);
    }
    .detail-item:last-child {
      border-bottom: none;
    }
    .detail-label {
      color: #bbb;
      font-weight: 500;
      font-size: 0.9em;
    }
    .detail-value {
      color: #fff;
      font-weight: 600;
      font-size: 0.9em;
    }
    .edit-btn {
      background: #4ade80;
      border: none;
      padding: 8px 15px;
      margin-top: 10px;
      cursor: pointer;
      color: black;
      border-radius: 6px;
      text-decoration: none;
      display: inline-block;
    }
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .card {
      background: #1a1a1a;
      padding: 15px;
      border-radius: 10px;
    }
    .highlight {
      color: #4ade80;
    }
    .mood-icons {
      display: flex;
      justify-content: space-around;
      font-size: 1.5em;
      margin-bottom: 10px;
      cursor: pointer;
    }
    .mood-icons span {
      transition: transform 0.2s;
    }
    .mood-icons span:hover {
      transform: scale(1.2);
    }
    canvas {
      margin-top: 10px;
    }
    .question-card {
      background: #1a1a1a;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
    }
    .question {
      font-size: 1.1em;
      margin-bottom: 10px;
    }
    .options {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 10px;
    }
    .option-btn {
      background: #333;
      color: white;
      padding: 8px 14px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-size: 1.2em;
      transition: background 0.2s;
    }
    .option-btn:hover {
      background: #4ade80;
      color: black;
    }
    .tick-btn {
      background: #4ade80;
      color: black;
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1em;
      transition: background 0.2s;
    }
    .tick-btn:hover {
      background: #22c55e;
    }
    .selected {
      background: #4ade80 !important;
      color: black !important;
    }
    .text-input {
      width: 100%;
      padding: 8px;
      border-radius: 6px;
      border: none;
      background: #333;
      color: white;
      margin-bottom: 10px;
    }
    .success-message {
      color: #4ade80;
      font-size: 0.9em;
      margin-top: 5px;
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
    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
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
      <li><a href="Journaling.php">📓 Journal</a></li>
      <li><a href="cbt.php">🧠CBT</a></li>
      <li><a href="GroupChat.php">💬Group Chat</a></li>
      <li class="active"><a href="#">👤 My Profile</a></li>
      <li><a href="logout.php">🚪 Log Out</a></li>
    </ul>
  </div>

  <div class="main-content">
    <header>
      <div class="header-content">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
        <p class="welcome-text">Here's your personal dashboard</p>
      </div>
      <button class="settings-btn">⚙️Setting</button>
    </header>

    <div class="profile-container">
      <div class="profile-card">
        <div class="profile-pic">
          <div class="profile-avatar">
            <span class="avatar-text"><?php echo strtoupper(substr($user_data['name'], 0, 1)); ?></span>
          </div>
          <div class="profile-status">
            <span class="status-dot"></span>
            <span class="status-text">Active</span>
          </div>
        </div>
        <h3><?php echo htmlspecialchars($user_data['name']); ?></h3>
        <p class="highlight"><?php echo htmlspecialchars($user_data['email']); ?></p>
        <div class="profile-stats">
          <div class="stat-item">
            <span class="stat-number"><?php echo count($today_answers); ?></span>
            <span class="stat-label">Today's Answers</span>
          </div>
          <div class="stat-item">
            <span class="stat-number"><?php echo $user_data['id']; ?></span>
            <span class="stat-label">User ID</span>
          </div>
        </div>
        <div class="user-details">
          <div class="detail-item">
            <span class="detail-label">👤 Username:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user_data['name']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">📧 Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">🆔 Account ID:</span>
            <span class="detail-value">#<?php echo $user_data['id']; ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">📅 Member Since:</span>
            <span class="detail-value"><?php echo date('F Y'); ?></span>
          </div>
        </div>
        <a href="editbutton.php?user_id=<?php echo $user_id; ?>" class="edit-btn">✏️ Edit Profile</a>
      </div>

      <div class="info-grid">
        <div class="card">
          <h4>Monthly Mood Tracking</h4>
          <div class="mood-icons">
            <span onclick="updateMood('happy')">😊</span>
            <span onclick="updateMood('neutral')">😐</span>
            <span onclick="updateMood('sad')">😔</span>
            <span onclick="updateMood('cry')">😢</span>
            <span onclick="updateMood('angry')">😡</span>
          </div>
          <canvas id="moodChart" height="150"></canvas>
        </div>

        <div class="card">
          <h4>🌱 Stress & Mood Awareness Questions</h4>
          <div id="questions-container">
            <?php foreach ($questions as $question): ?>
              <div class="question-card" data-question-id="<?php echo $question['id']; ?>">
                <div class="question"><?php echo htmlspecialchars($question['question_text']); ?></div>
                <?php if ($question['question_type'] === 'emoji_scale' || $question['question_type'] === 'emoji_choice'): ?>
                  <div class="options">
                    <?php 
                    $options = explode(',', $question['options']);
                    foreach ($options as $option): 
                      $isSelected = isset($today_answers[$question['id']]) && $today_answers[$question['id']] === trim($option);
                    ?>
                      <button class="option-btn <?php echo $isSelected ? 'selected' : ''; ?>" 
                              data-answer="<?php echo htmlspecialchars(trim($option)); ?>">
                        <?php echo htmlspecialchars(trim($option)); ?>
                      </button>
                    <?php endforeach; ?>
                  </div>
                <?php elseif ($question['question_type'] === 'text'): ?>
                  <input type="text" class="text-input" placeholder="Type your answer..." 
                         value="<?php echo isset($today_answers[$question['id']]) ? htmlspecialchars($today_answers[$question['id']]) : ''; ?>">
                <?php endif; ?>
                <button class="tick-btn" onclick="saveAnswer(<?php echo $question['id']; ?>, '<?php echo $question['question_type']; ?>')">✔</button>
                <div class="success-message" id="message-<?php echo $question['id']; ?>"></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Highlight selected answer
    document.querySelectorAll('.options').forEach(optionGroup => {
        const buttons = optionGroup.querySelectorAll('.option-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
            });
        });
    });

    function saveAnswer(questionId, questionType) {
        let answer = '';
        if (questionType === 'text') {
            const input = document.querySelector(`[data-question-id="${questionId}"] .text-input`);
            answer = input.value.trim();
        } else {
            const selectedBtn = document.querySelector(`[data-question-id="${questionId}"] .option-btn.selected`);
            if (selectedBtn) {
                answer = selectedBtn.getAttribute('data-answer');
            }
        }
        if (!answer) {
            alert('Please select an answer or enter text');
            return;
        }
        const formData = new FormData();
        formData.append('question_id', questionId);
        formData.append('answer', answer);
        fetch('save_profile_answers.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById(`message-${questionId}`);
            if (data.success) {
                messageDiv.textContent = data.message;
                messageDiv.style.color = '#4ade80';
                updateMoodChart();
                if (data.mood_updated && data.today_mood) {
                    showMoodUpdate(data.today_mood);
                }
            } else {
                messageDiv.textContent = 'Error: ' + data.error;
                messageDiv.style.color = '#ff6b6b';
            }
            setTimeout(() => {
                messageDiv.textContent = '';
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving answer');
        });
    }

    function showMoodUpdate(moodData) {
        const moodEmojis = {
            'happy': '😊',
            'neutral': '😐',
            'sad': '😔',
            'cry': '😢',
            'angry': '😡'
        };
        const emoji = moodEmojis[moodData.type] || '😊';
        const message = `Mood updated! ${emoji} Score: ${moodData.score}/100`;
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4ade80;
            color: black;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Mood chart with real user data
    const moodLabels = <?php echo json_encode(array_column($mood_data, 'date')); ?>;
    const moodAnswers = <?php echo json_encode(array_column($mood_data, 'answer')); ?>;
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
    const moodScores = moodAnswers.map(moodToScore);

    let moodChart;
    function initMoodChart() {
        const ctx = document.getElementById('moodChart').getContext('2d');
        moodChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: moodLabels,
                datasets: [{
                    label: 'Mood Progress',
                    data: moodScores,
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.2)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4ade80',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Mood Score: ${context.parsed.y}/100`;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    function updateMoodChart() {
        fetch('get_mood_data.php?days=7')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    moodChart.data.labels = data.labels;
                    moodChart.data.datasets[0].data = data.data;
                    moodChart.update();
                    if (data.today_mood) {
                        updateMoodTypeDisplay(data.today_mood);
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching mood data:', error);
            });
    }

    function updateMoodTypeDisplay(moodData) {
        const moodEmojis = {
            'happy': '😊',
            'neutral': '😐',
            'sad': '😔',
            'cry': '😢',
            'angry': '😡'
        };
        const moodCard = document.querySelector('.card h4');
        if (moodCard) {
            const emoji = moodEmojis[moodData.type] || '😊';
            moodCard.innerHTML = `Monthly Mood Tracking ${emoji} (${moodData.score}/100)`;
        }
    }

    function updateMood(mood) {
        // This function is now for manual mood selection (keeping for compatibility)
        const moodScores = {
            'happy': [85, 88, 92, 90, 87, 89, 91],
            'neutral': [60, 58, 62, 65, 63, 61, 64],
            'sad': [35, 32, 30, 28, 33, 31, 29],
            'cry': [15, 12, 10, 8, 13, 11, 9],
            'angry': [45, 42, 40, 38, 43, 41, 39]
        };
        moodChart.data.datasets[0].data = moodScores[mood] || moodScores['happy'];
        moodChart.update();
    }

    // Initialize chart and load real-time data
    initMoodChart();
    // updateMoodChart(); // Uncomment if you want to fetch latest data via AJAX
  </script>

</body>
</html>