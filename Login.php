<?php
session_start();
include("database.php");

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    // Prepare SQL to prevent SQL injection
    $stmt = $conn->prepare("SELECT Id, Username, Password, Role FROM users WHERE Username = ? AND Role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify the password against the hashed version
        if (password_verify($password, $user['Password'])) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            
            // Redirect based on role
            if ($user['Role'] == 'admin') {
                header("Location: Administrator.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or role selected.";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="Login.css" rel="stylesheet" /> 
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($login_error)): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>