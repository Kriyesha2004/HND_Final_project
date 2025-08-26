<?php
session_start();
require 'BD_carepoint.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: Login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Group Chat - Care Point</title>
  <style>
    /* Base */
    * { box-sizing: border-box; }
    body { margin:0; font-family: Arial, sans-serif; background:#0f0f0f; color:#fff; }

    /* App layout */
    .app { display:flex; min-height: 100vh; width: 100%; }

    /* Sidebar */
    .sidebar { width: 240px; background-color: #111; padding: 20px; border-right: 1px solid #1e1e1e; }
    .logo { color: #4ade80; font-size: 1.5em; margin-bottom: 30px; }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar li { margin: 14px 0; }
    .sidebar a { color: #bbb; text-decoration: none; font-size: 1em; display:block; padding: 8px 10px; border-radius: 8px; }
    .sidebar a:hover { background:#1a1a1a; color:#fff; }
    .sidebar .active a { color: #4ade80; font-weight: bold; background:#0f1a12; }

    /* Content */
    .content { flex:1; display:flex; flex-direction:column; }
    .header { display:flex; justify-content: space-between; align-items:center; padding: 16px 20px; border-bottom: 1px solid #1e1e1e; background:#121212; position: sticky; top:0; z-index: 10; }
    .badge { background:#4ade80; color:#000; padding:4px 10px; border-radius: 999px; font-weight:700; font-size:12px; }
    .badge.counselor { background:#fde68a; }

    /* Chat area */
    .chat-area { flex:1; display:flex; flex-direction:column; padding: 16px 20px; gap: 10px; }
    .chat-box { flex:1; background:#151515; border:1px solid #222; border-radius:12px; padding: 12px; overflow-y:auto; }
    .msg { margin:10px 0; padding:10px 12px; border-radius:10px; background:#1e1e1e; border:1px solid #2a2a2a; }
    .meta { display:flex; gap:10px; align-items:center; margin-bottom:6px; font-size:12px; color:#bbb; }
    .name { color:#4ade80; font-weight:700; }
    .role { background:#222; color:#ddd; padding:2px 8px; border-radius:999px; font-size:11px; }
    .role.counselor { background:#fde68a; color:#000; }
    .text { font-size:15px; color:#eee; }

    /* Composer fixed at bottom of content */
    .composer { display:flex; gap:10px; padding-top: 10px; }
    .composer input { flex:1; padding:12px; border-radius:10px; border:1px solid #2a2a2a; background:#111; color:#fff; }
    .composer button { padding:12px 18px; background:#4ade80; color:#000; border:none; border-radius:10px; font-weight:700; cursor:pointer; }
    .composer button:disabled { opacity:.6; cursor:not-allowed; }

    @media (max-width: 900px) {
      .sidebar { display:none; }
      .content { padding-top: 0; }
    }
  </style>
</head>
<body>
  <div class="app">
    <!-- Left sidebar -->
    <aside class="sidebar">
      <?php if ($userRole === 'counselor'): ?>
        <h2 class="logo">🌿 Care Point</h2>
        <ul>
          <li><a href="Councilor.php">📊 Dashboard</a></li>
          <li class="active"><a href="GroupChat.php">💬 Group Chat</a></li>
          <li><a href="My Clients.php">👥 My Clients</a></li>
          <li><a href="Councior_cbt.php"> CBT Upload</a></li>
          <li><a href="schedule_counciler.php">🗓️ Schedule</a></li>
    
          
          <li><a href="CouncilorSettings.php">⚙️ Settings</a></li>
          <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
      <?php else: ?>
        <h2 class="logo">🌿 Care Point</h2>
        <ul>
          <li><a href="Sidebar.html">🏠 Home</a></li>
          <li><a href="Music.html">🎵 Music</a></li>
          <li><a href="Journaling.php">📓 Journal</a></li>
          <li><a href="cbt.php">🧠 CBT</a></li>
          <li class="active"><a href="GroupChat.php">💬 Group Chat</a></li>
          <li><a href="Myprofile.php">👤 My Profile</a></li>
          <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
      <?php endif; ?>
    </aside>

    <!-- Right content -->
    <main class="content">
      <div class="header">
        <h2>Group Chat</h2>
        <div>
          <span class="badge"><?php echo htmlspecialchars($userName); ?></span>
          <?php if ($userRole === 'counselor'): ?>
            <span class="badge counselor">Counselor</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="chat-area">
        <div id="chat" class="chat-box"></div>
        <div class="composer">
          <input id="message" type="text" placeholder="Type a message..." />
          <button id="send">Send</button>
        </div>
      </div>
    </main>
  </div>

  <script>
    const chatEl = document.getElementById('chat');
    const inputEl = document.getElementById('message');
    const sendBtn = document.getElementById('send');

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function renderMessage(msg) {
      const wrapper = document.createElement('div');
      wrapper.className = 'msg';
      const roleClass = msg.role === 'counselor' ? 'role counselor' : 'role';
      wrapper.innerHTML = `
        <div class="meta">
          <span class="name">${escapeHtml(msg.name)}</span>
          <span class="${roleClass}">${escapeHtml(msg.role)}</span>
          <span>${escapeHtml(msg.created_at)}</span>
        </div>
        <div class="text">${escapeHtml(msg.message_text)}</div>
      `;
      chatEl.appendChild(wrapper);
    }

    async function loadMessages() {
      const res = await fetch('get_messages.php');
      const data = await res.json();
      chatEl.innerHTML = '';
      (data.messages || []).forEach(renderMessage);
      chatEl.scrollTop = chatEl.scrollHeight;
    }

    async function sendMessage() {
      const text = inputEl.value.trim();
      if (!text) return;
      sendBtn.disabled = true;
      try {
        const res = await fetch('send_message.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'message_text=' + encodeURIComponent(text)
        });
        const data = await res.json();
        if (data.success) {
          inputEl.value = '';
          await loadMessages();
        } else {
          alert(data.error || 'Failed to send');
        }
      } catch (e) {
        alert('Network error');
      } finally {
        sendBtn.disabled = false;
      }
    }

    sendBtn.addEventListener('click', sendMessage);
    inputEl.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendMessage(); });

    loadMessages();
    setInterval(loadMessages, 3000);
  </script>
</body>
</html>
