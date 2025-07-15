<?php
session_start();
include("database.php");

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle task status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $task_id = $conn->real_escape_string($_POST['task_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE tasks SET Status = ? WHERE Id = ? AND Assigned_to = ?");
    $stmt->bind_param("sii", $new_status, $task_id, $user_id);
    
    if ($stmt->execute()) {
        $message = "Task status updated successfully!";
        $message_class = "alert-success";
    } else {
        $message = "Error updating task status: " . $stmt->error;
        $message_class = "alert-danger";
    }
    $stmt->close();
}

// Get all tasks assigned to this user
$tasks = [];
$stmt = $conn->prepare("SELECT t.Id, t.Title, t.Description, t.Deadline, t.Status, 
                               u.Username AS admin_name 
                        FROM tasks t
                        JOIN users u ON t.Created_by = u.Id
                        WHERE t.Assigned_to = ?
                        ORDER BY t.Deadline ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="user_dashboard.php" rel="stylesheet"/>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Task Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Login.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Your Tasks</h2>
        
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">
                You currently have no tasks assigned to you.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tasks as $task): ?>
                    <?php
                    // Determine deadline class
                    $deadline_class = 'deadline-normal';
                    $deadline = strtotime($task['Deadline']);
                    $today = strtotime('today');
                    $diff = ($deadline - $today) / (60 * 60 * 24);
                    
                    if ($diff < 0) {
                        $deadline_class = 'deadline-urgent';
                        $deadline_text = 'Overdue!';
                    } elseif ($diff <= 2) {
                        $deadline_class = 'deadline-near';
                        $deadline_text = 'Due soon';
                    } else {
                        $deadline_text = 'Due in ' . $diff . ' days';
                    }
                    ?>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card task-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($task['Title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($task['Description']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $task['Status'])); ?>">
                                        <?php echo $task['Status']; ?>
                                    </span>
                                    <span class="<?php echo $deadline_class; ?>">
                                        <?php echo date('M j, Y', $deadline); ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">
                                        Assigned by: <?php echo htmlspecialchars($task['admin_name']); ?>
                                    </small>
                                    <small class="<?php echo $deadline_class; ?>">
                                        <?php echo $deadline_text; ?>
                                    </small>
                                </div>
                                
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="task_id" value="<?php echo $task['Id']; ?>">
                                    <div class="input-group">
                                        <select class="form-select" name="status">
                                            <option value="Pending" <?php echo $task['Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="In Progress" <?php echo $task['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="Complete" <?php echo $task['Status'] == 'Complete' ? 'selected' : ''; ?>>Complete</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>