<?php
session_start();
include("database.php");

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit();
}

// Initialize response array
$response = [];

// Assign new task
if (isset($_POST['assign_task'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $assigned_to = $conn->real_escape_string($_POST['assigned_to']);
    $deadline = $conn->real_escape_string($_POST['deadline']);
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO tasks (Title, Description, Assigned_to, Created_by, Deadline) 
            VALUES ('$title', '$description', $assigned_to, $created_by, '$deadline')";

    if ($conn->query($sql)) {
        $response['success'] = "Task assigned successfully!";
    } else {
        $response['error'] = "Error: " . $conn->error;
    }
}

// Update task status
if (isset($_POST['update_status'])) {
    $task_id = $conn->real_escape_string($_POST['task_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    $sql = "UPDATE tasks SET Status = '$new_status' WHERE Id = $task_id";

    if ($conn->query($sql)) {
        $response['success'] = "Task status updated!";
    } else {
        $response['error'] = "Error: " . $conn->error;
    }
}

// Get all users for dropdown
$users = [];
$user_result = $conn->query("SELECT Id, Username FROM users WHERE Role = 'user'");
if ($user_result && $user_result->num_rows > 0) {
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get all tasks with user details
$tasks = [];
$task_query = "SELECT t.*, u1.Username AS Assignee, u2.Username AS Creator 
               FROM tasks t
               JOIN users u1 ON t.Assigned_to = u1.Id
               JOIN users u2 ON t.Created_by = u2.Id
               ORDER BY t.Deadline ASC";
$task_result = $conn->query($task_query);
if ($task_result && $task_result->num_rows > 0) {
    while ($row = $task_result->fetch_assoc()) {
        $tasks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link href="Task.css" rel="stylesheet" />    
</head>
<body>
    <div class="container">
         <div class="header-container">
            <h1>Assign Task View</h1>
            <div class="nav">
                <a href="Administrator.php">Back</a>
                <a href="Login.php?logout=1">Logout</a>
            </div>
         </div>
       
        
        <?php if (!empty($response)): ?>
            <div class="message <?php echo isset($response['success']) ? 'success' : 'error'; ?>">
                <?php echo $response['success'] ?? $response['error']; ?>
            </div>
        <?php endif; ?>
        
        <h2>Assign New Task</h2>
        <form method="POST">
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="assigned_to">Assign To</label>
                <select id="assigned_to" name="assigned_to" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['Id']; ?>"><?php echo htmlspecialchars($user['Username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <button type="submit" name="assign_task">Assign Task</button>
        </form>
        
        <h2>Task List</h2>
        <?php if (!empty($tasks)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>Created By</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['Title']); ?></td>
                            <td><?php echo htmlspecialchars($task['Description']); ?></td>
                            <td><?php echo htmlspecialchars($task['Assignee']); ?></td>
                            <td><?php echo htmlspecialchars($task['Creator']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($task['Deadline'])); ?></td>
                            <td class="status-<?php echo strtolower(str_replace(' ', '-', $task['Status'])); ?>">
                                <?php echo $task['Status']; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['Id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo $task['Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $task['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Complete" <?php echo $task['Status'] == 'Complete' ? 'selected' : ''; ?>>Complete</option>
                                    </select>
                                    <input type="submit" name="update_status" style="display: none;">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks found.</p>
        <?php endif; ?>
    </div>
</body>
</html>