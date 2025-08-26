<?php
require 'BD_carepoint.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $confirm_password = trim($_POST["confirm_password"] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO register (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);
        
        header("Location: Login.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Register with Care Point to access mental health support, therapy, and wellness tools." />
  <title>Register - Care Point</title>
  <link rel="stylesheet" href="register.css" />
</head>
<body>

  <!-- Header Section -->
  <header>
    <a href="index.html#home" class="logo">Care <span>Point</span></a>
    <div class="bx bx-menu" id="menu-icon"></div>

    <ul class="navbar">
      <li><a href="Home.html">Home</a></li>
      
    </ul>
  </header>

  <!-- Registration Form Section -->
  <section class="register">
    <div class="register-content">
      <h2>Create Your Care Point Account</h2>

      <form action="Register.php" method="POST" class="register-form">

    

        <div class="input-group">
          <label for="name">Anonymous Name</label>
          <input type="text" id="name" name="name" placeholder="Enter your Nick name" required />
        </div>

        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter a password" required />
        </div>

        <div class="input-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />
        </div>

        
        <button type="submit" class="btn">Register</button>
      </form>
    </div>
  </section>

</body>
</html>
