<?php
include 'BD_carepoint.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash('2004', PASSWORD_DEFAULT);

    if ($name && $email) {
        $stmt = $pdo->prepare("INSERT INTO register (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        $success = "Councilor registered successfully!";
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Councilors</title>
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
        .form-box { margin: 40px auto; background: #333; padding: 30px; border-radius: 8px; max-width: 400px; }
        label { display: block; margin-bottom: 8px; }
        input[type="text"], input[type="email"] {
            width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 5px; border: none;
        }
        button { background: #10ab5b; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0e8c4a; }
        .success { color: #27ae60; margin-bottom: 10px; }
        .error { color: #e74c3c; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav>
        <a href="Admin.php">Dashboard</a>
        <a href="User_Management.php">User Management</a>
        <a href="Register Councilers.php">Register Councilors</a>
        <a href="schedule_admin.php">Schedules</a>
        
        <a href="Login.php">Logout</a>
    </nav>
    <main>
        <h2>Register Councilor</h2>
        <div class="form-box">
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <label for="name">Councilor Name:</label>
                <input type="text" name="name" id="name" required>
                <label for="email">Councilor Email:</label>
                <input type="email" name="email" id="email" required>
                <button type="submit">Register</button>
            </form>
        </div>
    </main>
</div>
</body>