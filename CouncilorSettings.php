<?php
session_start();
require 'BD_carepoint.php';

$userId = $_SESSION['user_id'] ?? 1;

// Load current profile
$stmt = $pdo->prepare('SELECT name, email FROM register WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['name' => '', 'email' => ''];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Care Point - Counselor Settings</title>
    <style>
      :root { --main-color:#12a559; --bg-color:#1c1c1c; --text-color:#fff; --snd-bg-color:#222; }
      body { margin:0; font-family:Arial, sans-serif; background-color:var(--bg-color); color:var(--text-color); }
      header { background-color:var(--main-color); color:var(--bg-color); padding:15px 20px; text-align:center; font-weight:bold; letter-spacing:1px; font-size:1.5em; }
      .dashboard { display:flex; min-height:100vh; }
      nav { width:220px; background-color:var(--snd-bg-color); padding:20px 0; }
      nav a { display:block; padding:12px 20px; color:var(--text-color); text-decoration:none; border-radius:8px; margin:6px 20px; font-weight:bold; transition:background .3s, color .3s; }
      nav a:hover, nav a.active { background-color:var(--main-color); color:var(--bg-color); }
      main { flex:1; padding:20px; background-color:var(--snd-bg-color); border-radius:12px; margin:20px; }
      h2 { color:var(--main-color); margin-bottom:20px; }
      .card { background-color:var(--bg-color); padding:20px; border-radius:12px; box-shadow:0 4px 8px rgba(0,255,123,.2); margin-bottom:20px; }
      .row { display:flex; gap:12px; flex-wrap:wrap; }
      label { display:block; margin:8px 0 6px; font-weight:bold; }
      input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #333; background:#111; color:#fff; }
      button { background:var(--main-color); color:#000; border:none; padding:10px 16px; border-radius:8px; font-weight:bold; cursor:pointer; }
      .muted { color:#aaa; font-size:.9em; }
      .success { color:#22c55e; font-weight:bold; }
      .error { color:#f87171; font-weight:bold; }
      .danger { background:#e11d48; color:#fff; }
    </style>
  </head>
  <body>
    <header>🌿 Care Point Counselor Settings</header>
    <div class="dashboard">
      <nav>
        <a href="Councilor.php">Dashboard</a>
        <a href="My Clients.php">My Clients</a>
        <a href="GroupChat.php">💬 Group Chat</a>
        <a href="Councior_cbt.php">CBT Upload</a>
        <a href="schedule_counciler.php">Schedule</a>
        <a href="CouncilorSettings.php" class="active">Settings</a>
        <a href="logout.php">Logout</a>
      </nav>
      <main>
        <h2>Profile</h2>
        <div class="card">
          <form id="profileForm">
            <div class="row">
              <div style="flex:1 1 260px;">
                <label for="name">Display Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" />
              </div>
              <div style="flex:1 1 260px;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" />
              </div>
            </div>
            <div style="margin-top:12px; display:flex; gap:10px; align-items:center;">
              <button type="submit">Save Profile</button>
              <span id="profileMsg" class="muted"></span>
            </div>
          </form>
        </div>

        <h2>Delete Account</h2>
        <div class="card">
          <p class="muted">⚠️ Deleting your account is permanent and cannot be undone.</p>
          <button id="deleteAccountBtn" class="danger">Delete My Account</button>
          <span id="deleteMsg" class="muted"></span>
        </div>
      </main>
    </div>

    <script>
      // Save profile
      document.getElementById('profileForm').addEventListener('submit', async function(e){
        e.preventDefault();
        const form = new FormData(this);
        const res = await fetch('save_councilor_profile.php', { method: 'POST', body: form });
        const data = await res.json().catch(()=>({success:false,error:'Invalid response'}));
        const msg = document.getElementById('profileMsg');
        if (data.success) { msg.textContent = 'Saved!'; msg.className = 'success'; }
        else { msg.textContent = data.error || 'Failed to save'; msg.className = 'error'; }
      });

      // Delete account
      document.getElementById('deleteAccountBtn').addEventListener('click', async function(){
        if (!confirm("Are you sure you want to delete your account? This cannot be undone.")) return;
        const res = await fetch('delete_councilor_account.php', { method:'POST' });
        const data = await res.json().catch(()=>({success:false,error:'Invalid response'}));
        const msg = document.getElementById('deleteMsg');
        if (data.success) { 
          msg.textContent = 'Account deleted. Redirecting...'; 
          msg.className = 'success'; 
          setTimeout(()=> window.location.href = 'logout.php', 1500);
        } else { 
          msg.textContent = data.error || 'Failed to delete account'; 
          msg.className = 'error'; 
        }
      });
    </script>
  </body>
</html>
