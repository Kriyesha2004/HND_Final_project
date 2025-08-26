<?php

include 'BD_carepoint.php'; // Make sure this file sets up $pdo

try {
    // Total users
    $sql_total = "SELECT COUNT(*) AS total FROM register";
    $stmt_total = $pdo->query($sql_total);
    $total_users = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

    // Total councilors
    $sql_councilors = "SELECT COUNT(*) AS total FROM register WHERE id IN (6,10)";
$stmt_councilors = $pdo->query($sql_councilors);
$total_councilors = $stmt_councilors->fetch(PDO::FETCH_ASSOC)['total'];

    
// ...existing code...
$sql_cbt_count = "SELECT COUNT(*) AS total FROM cbt_exercises";
$stmt_cbt_count = $pdo->query($sql_cbt_count);
$total_cbt_exercises = $stmt_cbt_count->fetch(PDO::FETCH_ASSOC)['total'];
// ...existing code...

} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Care Point - Admin Dashboard</title>

<style>
  :root {
    --main-color: #10ab5b;
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
    margin: 4px 20px;
    font-weight: bold;
    transition: background 0.3s, color 0.3s;
  }

  nav a:hover {
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
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
    font-size: 1.5em;
    font-weight: bold;
    margin: 10px 0 0;
    color: var(--text-color);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: var(--bg-color);
    border-radius: 8px;
    overflow: hidden;
    color: var(--text-color);
  }

  th, td {
    padding: 12px;
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

  .empty {
    text-align: center;
    color: #888;
    font-style: italic;
  }
</style>
</head>
<body>

<header>
  🌿 Care Point Admin Dashboard
</header>

<div class="dashboard">
  <nav>
    <a href="#">Dashboard</a>
    <a href="User_Management.php">User Management</a>
    <a href="Register Councilers.php">Register Councilers</a>
    <a href="schedule_admin.php">Scheduls</a>
  
    <a href="Login.php">Logout</a>
  </nav>

  <main>
        <h2>Overview</h2>
        <div class="stats">
          <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
          </div>
          <div class="card">
            <h3>Total Councilors</h3>
            <p><?php echo $total_councilors; ?></p>
          </div>
         <div class="card">
  <h3>Active CBT Sessions</h3>
  <p><?php echo $total_cbt_exercises; ?></p>
</div>
          
        </div>

    
    </table>
  </main>
</div>

</body>
</html>
