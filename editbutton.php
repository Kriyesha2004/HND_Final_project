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

// Fetch user details
$user_stmt = $pdo->prepare("SELECT id, name, email FROM register WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch();

if (!$user_data) {
    die("User not found");
}

$success_message = '';
$error_message = '';

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($new_name) || empty($new_email)) {
        $error_message = "Name and email are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        try {
            if (!empty($new_password)) {
                // Update with password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE register SET name = ?, email = ?, password = ? WHERE id = ?");
                $update_stmt->execute([$new_name, $new_email, $hashed_password, $user_id]);
            } else {
                // Update without password
                $update_stmt = $pdo->prepare("UPDATE register SET name = ?, email = ? WHERE id = ?");
                $update_stmt->execute([$new_name, $new_email, $user_id]);
            }
            
            $success_message = "Profile updated successfully!";
            
            // Refresh user data
            $user_stmt->execute([$user_id]);
            $user_data = $user_stmt->fetch();
            
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Care Point</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .edit-container {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(74, 222, 128, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #4ade80;
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            color: #bbb;
            font-size: 1.1em;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2em;
            font-weight: bold;
            color: white;
            box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4ade80;
            font-weight: 500;
            font-size: 0.95em;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(74, 222, 128, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4ade80;
            background: rgba(74, 222, 128, 0.05);
            box-shadow: 0 0 20px rgba(74, 222, 128, 0.2);
        }

        .form-group input::placeholder {
            color: #666;
        }

        .password-section {
            background: rgba(74, 222, 128, 0.05);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(74, 222, 128, 0.1);
            margin: 20px 0;
        }

        .password-section h3 {
            color: #4ade80;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(74, 222, 128, 0.3);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid #4ade80;
            color: #4ade80;
        }

        .btn-secondary:hover {
            background: #4ade80;
            color: #000;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .user-info {
            background: rgba(74, 222, 128, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(74, 222, 128, 0.1);
        }

        .user-info h3 {
            color: #4ade80;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(74, 222, 128, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #bbb;
            font-weight: 500;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .edit-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .header h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
            </div>
            <h1>Edit Profile</h1>
            <p>Update your personal information</p>
        </div>

        <?php if ($success_message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="user-info">
            <h3>📋 Current Information</h3>
            <div class="info-item">
                <span class="info-label">User ID:</span>
                <span class="info-value">#<?php echo $user_data['id']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="name">👤 Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">📧 Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required placeholder="Enter your email address">
            </div>

            <div class="password-section">
                <h3>🔐 Change Password (Optional)</h3>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password (leave blank to keep current)">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                </div>
            </div>

            <button type="submit" class="btn">💾 Save Changes</button>
            <a href="Myprofile.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary" style="text-decoration: none; display: block; text-align: center;">← Back to Profile</a>
        </form>
    </div>

    <script>
        // Add some interactive effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Auto-hide success message after 5 seconds
        const successMessage = document.querySelector('.message.success');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>
