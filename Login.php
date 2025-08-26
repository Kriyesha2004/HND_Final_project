<?php
session_start();
require 'BD_carepoint.php'; // This gives us $pdo

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "<script>alert('Please enter both username and password.'); window.location.href='Login.php';</script>";
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, name, email, password FROM register WHERE name = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row) {
        $stored_hashed_password = $row['password'];

        if (password_verify($password, $stored_hashed_password)) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['logged_in'] = true;

            // Set role based on special passwords (simple role model)
            if ($password === "2003") {
                $_SESSION['role'] = 'admin';
                header("Location: Admin.php");
            } elseif ($password === "2004") {
                $_SESSION['role'] = 'counselor';
                header("Location: Councilor.php");
            } else {
                $_SESSION['role'] = 'user';
                header("Location: Myprofile.php?user_id=" . $row['id']);
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='Login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.location.href='Login.php';</script>";
    }
}
?>

<!-- Login Form UI -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to Care Point to access personalized mental health support and resources.">
    <title>Login - Care Point</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .logonew{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-left: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .logoimg{
            width: 50px;
            height: 50px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logonew">
            <img src="./stickers/primary 2.png" alt="" class="logoimg">
            <a href="index.html#home" class="logo">Care <span>Point</span></a>
        </div>
        <div class="bx bx-menu" id="menu-icon"></div>
        <ul class="navbar">
            <a href="Home.html" class="nav-btn"><i class='bx bx-log-in'></i> Home</a>
        </ul>
    </header>

    <section class="login">
        <div class="login-content">
            <h2>Login to Care Point</h2>
            <form action="Login.php" method="POST" class="login-form">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div> 
    </section>
</body>
</html>
