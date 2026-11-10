<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'volunteer') { header("Location: ../index.php"); exit; }
$page_title = "My Tasks";

// Update task status
if(isset($_POST['update_status'])) {
    $allowed = ['Pending', 'In Progress', 'Completed'];
    $status = $_POST['status'] ?? '';
    $task_id = (int)($_POST['task_id'] ?? 0);
    $volunteer_id = (int)$_SESSION['user_id'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE tasks t JOIN task_assignments ta ON ta.task_id = t.id SET t.status = ? WHERE t.id = ? AND ta.volunteer_id = ?");
        $stmt->bind_param("sii", $status, $task_id, $volunteer_id);
        $stmt->execute();
    }
}

$my_id = $_SESSION['user_id'];
$tasks = $conn->query("SELECT t.* FROM tasks t 
                       JOIN task_assignments ta ON t.id = ta.task_id 
                       WHERE ta.volunteer_id = $my_id");
?>
<!DOCTYPE html>
<html>
<head><title>My Tasks</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
            <?php while($row = $tasks->fetch_assoc()): ?>
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #2563eb;">
                <h4><?= e($row['title']) ?></h4>
                <p style="color: #666; margin: 10px 0;"><?= e($row['description']) ?></p>
                <form method="POST" style="display:flex; gap: 10px;"><?php csrf_field(); ?>
                    <input type="hidden" name="task_id" value="<?= e($row['id']) ?>">
                    <select name="status">
                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $row['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-blue">Update Status</button>
                </form>
            </div>
            <?php endwhile; ?>
            <?php if($tasks->num_rows == 0) echo "<p>No tasks assigned yet.</p>"; ?>
        </div>
    </div>
</body>
</html>