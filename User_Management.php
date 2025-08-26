<?php

include 'BD_carepoint.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM register WHERE id = ?");
    $stmt->execute([$id]);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM register WHERE name LIKE ? OR email LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #444; }
        th { background: #10ab5b; color: #222; }
        tr:nth-child(even) { background: #333; }
        .search-box { margin: 20px 0; }
        .delete-btn { color: #fff; background: #e74c3c; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav>
        <a href="Admin.php">Dashboard</a>
        <a href="User_Management.php">User Management</a>
        <a href="schedule_admin.php">Scheduls</a>
       
        <a href="Login.php">Logout</a>
    </nav>
    <main>
        <h2>User Management</h2>
        <form method="get" class="search-box">
            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Delete this user?');" class="delete-btn">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
            <?php endif; ?>
        </table>
    </main>
</div>