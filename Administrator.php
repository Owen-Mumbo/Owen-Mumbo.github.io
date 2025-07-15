<?php
include("database.php");

// Initialize response array
$response = [];

// CREATE User
if (isset($_POST['create'])) {
    $uname = $conn->real_escape_string($_POST['username']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);

    $sql = "INSERT INTO users (Username, Password, Email, Role) VALUES ('$uname', '$password', '$email', '$role')";

    if ($conn->query($sql) === TRUE) {
        $response['create'] = "User created successfully.";
    } else {
        $response['create_error'] = "Error: " . $conn->error;
    }
}

// UPDATE User
if (isset($_POST['update'])) {
    $email = $conn->real_escape_string($_POST['update_email']);
    $updates = [];
    
    if (!empty($_POST['new_email'])) {
        $updates[] = "Email = '".$conn->real_escape_string($_POST['new_email'])."'";
    }
    
    if (!empty($_POST['new_password'])) {
        $updates[] = "Password = '".password_hash($conn->real_escape_string($_POST['new_password']), PASSWORD_DEFAULT)."'";
    }
    
    if (!empty($_POST['new_role'])) {
        $updates[] = "Role = '".$conn->real_escape_string($_POST['new_role'])."'";
    }
    
    if (!empty($updates)) {
        $sql = "UPDATE users SET ".implode(', ', $updates)." WHERE Email = '$email'";
        
        if ($conn->query($sql)) {
            $response['update'] = "User updated successfully.";
        } else {
            $response['update_error'] = "Error: " . $conn->error;
        }
    } else {
        $response['update_error'] = "No fields to update.";
    }
}

// DELETE User
if (isset($_POST['delete'])) {
    $email = $conn->real_escape_string($_POST['delete_email']);

    $sql = "DELETE FROM users WHERE Email = '$email'";

    if ($conn->query($sql)) {
        $response['delete'] = "User deleted successfully.";
    } else {
        $response['delete_error'] = "Error: " . $conn->error;
    }
}

// READ Users
$users = [];
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link href="Administrator.css" rel="stylesheet"/>
</head>
<body>
     <div class="header-container">
        <h1>Administrator Control Panel</h1>
        <div class="nav">
            <a href="Task.php">Assign Task</a>
            <a href="Login.php?logout=1">Logout</a>

        </div>
    </div>

    <!-- Display messages -->
    <?php if (!empty($response)): ?>
        <?php foreach ($response as $type => $message): ?>
            <div class="message <?= strpos($type, 'error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="section">
        <h2>Create User</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="create">Create User</button>
        </form>
    </div>

    <div class="section">
        <h2>Update User</h2>
        <form method="POST">
            <input type="email" name="update_email" placeholder="Current Email" required>
            <input type="email" name="new_email" placeholder="New Email (optional)">
            <input type="password" name="new_password" placeholder="New Password (optional)">
            <select name="new_role">
                <option value="">Select new role (optional)</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="update">Update User</button>
        </form>
    </div>

    <div class="section">
        <h2>Delete User</h2>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
            <input type="email" name="delete_email" placeholder="Email to delete" required>
            <button type="submit" name="delete">Delete User</button>
        </form>
    </div>

    <div class="section">
        <h2>User List</h2>
        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['Id']) ?></td>
                            <td><?= htmlspecialchars($user['Username']) ?></td>
                            <td><?= htmlspecialchars($user['Email']) ?></td>
                            <td><?= htmlspecialchars($user['Role']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>