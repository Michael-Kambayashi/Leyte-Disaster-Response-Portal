<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'admin') { header("Location: ../index.php"); exit; }
$page_title = "Manage Tasks";

// 1. HANDLE CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_task'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $volunteer_id = $_POST['volunteer_id'];
    $status = $_POST['status'];

    if(!empty($_POST['task_id'])) {
        // UPDATE Existing
        $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, status=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $desc, $status, $_POST['task_id']);
        $stmt->execute();
        // Update assignment
        $stmt_remove = $conn->prepare("DELETE FROM task_assignments WHERE task_id = ?");
        $stmt_remove->bind_param("i", $_POST['task_id']);
        $stmt_remove->execute();
        $stmt2 = $conn->prepare("INSERT INTO task_assignments (task_id, volunteer_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $_POST['task_id'], $volunteer_id);
        $stmt2->execute();
    } else {
        // CREATE New
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $desc, $status);
        $stmt->execute();
        $new_id = $conn->insert_id;
        // Assign to volunteer
        $stmt2 = $conn->prepare("INSERT INTO task_assignments (task_id, volunteer_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $new_id, $volunteer_id);
        $stmt2->execute();
    }
    header("Location: manage_tasks.php"); exit;
}

// 2. HANDLE DELETE
if(isset($_POST['delete_task'])) {
    $stmt = $conn->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->bind_param('i', $_POST['delete_task']);
    $stmt->execute();
    header("Location: manage_tasks.php"); exit;
}

// 3. FETCH DATA
$volunteers = $conn->query("SELECT id, full_name FROM users WHERE role='volunteer'");
$tasks = $conn->query("SELECT t.*, u.full_name as assigned_to, u.id as v_id FROM tasks t 
                       LEFT JOIN task_assignments ta ON t.id = ta.task_id 
                       LEFT JOIN users u ON ta.volunteer_id = u.id");

$edit_task = null;
if(isset($_GET['edit'])) {
    $res = $conn->query("SELECT t.*, ta.volunteer_id FROM tasks t LEFT JOIN task_assignments ta ON t.id = ta.task_id WHERE t.id = ".(int)$_GET['edit']);
    $edit_task = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head><title>Manage Tasks</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <h3><?= $edit_task ? 'Edit Task' : 'Create & Assign Task' ?></h3>
            <form method="POST"><?php csrf_field(); ?>
                <input type="hidden" name="task_id" value="<?= e($edit_task['id'] ?? '') ?>">
                <div class="form-group">
                    <input type="text" name="title" placeholder="Task Title" value="<?= e($edit_task['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <textarea name="description" placeholder="Task Description" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;"><?= e($edit_task['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Assign to Volunteer:</label>
                    <select name="volunteer_id" required>
                        <?php while($v = $volunteers->fetch_assoc()): ?>
                            <option value="<?= e($v['id']) ?>" <?= (isset($edit_task) && $edit_task['volunteer_id'] == $v['id']) ? 'selected' : '' ?>>
                                <?= e($v['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="status">
                        <option value="Pending" <?= (isset($edit_task) && $edit_task['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= (isset($edit_task) && $edit_task['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= (isset($edit_task) && $edit_task['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <button type="submit" name="save_task" class="btn btn-blue"><?= $edit_task ? 'Update Task' : 'Create Task' ?></button>
            </form>
        </div>

        <table>
            <thead><tr><th>Title</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $tasks->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= e($row['title']) ?></strong><br><small><?= e($row['description']) ?></small></td>
                    <td><?= e($row['assigned_to'] ?? 'Unassigned') ?></td>
                    <td><?= e($row['status']) ?></td>
                    <td>
                        <a href="?edit=<?= e($row['id']) ?>" class="btn btn-green">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete task?')"><?php csrf_field(); ?><button class="btn btn-red" name="delete_task" value="<?= e($row['id']) ?>">Delete</button></form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>