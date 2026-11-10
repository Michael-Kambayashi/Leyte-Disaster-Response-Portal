<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'admin') { header("Location: ../index.php"); exit; }
$page_title = "Manage Users";

// --- CREATE & UPDATE USER ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    
    if (!empty($_POST['user_id'])) {
        // UPDATE EXISTING USER
        $user_id = $_POST['user_id'];
        
        // If they typed a new password, update it. Otherwise, keep the old one.
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $full_name, $username, $role, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, role=? WHERE id=?");
            $stmt->bind_param("sssi", $full_name, $username, $role, $user_id);
        }
        $stmt->execute();
    } else {
        // CREATE NEW USER
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $username, $password, $role);
        $stmt->execute();
    }
    header("Location: manage_users.php"); exit;
}

// --- DELETE USER ---
if(isset($_POST['delete_user'])) {
    // Prevent admin from deleting themselves
    if((int)$_POST['delete_user'] !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $_POST['delete_user']);
        $stmt->execute();
    }
    header("Location: manage_users.php"); exit;
}

// --- FETCH DATA FOR EDITING ---
$edit_user = null;
if(isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

// Fetch all users for the table
$users = $conn->query("SELECT * FROM users ORDER BY role ASC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Users</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        
        <!-- Create / Edit User Form -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3><?= $edit_user ? 'Edit User' : 'Add New User or Volunteer' ?></h3>
            <p style="color:#666; margin-bottom:15px; font-size: 14px;">
                <?= $edit_user ? 'Update details below. Leave password blank if you do not want to change it.' : 'Create an account directly from the dashboard.' ?>
            </p>
            
            <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;"><?php csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?= e($edit_user['id'] ?? '') ?>">
                
                <div class="form-group" style="margin: 0;">
                    <input type="text" name="full_name" placeholder="Full Name" value="<?= e($edit_user['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <input type="text" name="username" placeholder="Username" value="<?= e($edit_user['username'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <input type="password" name="password" placeholder="<?= $edit_user ? 'New Password (Optional)' : 'Password' ?>" <?= $edit_user ? '' : 'required' ?>>
                </div>
                <div class="form-group" style="margin: 0;">
                    <select name="role" required>
                        <option value="volunteer" <?= (isset($edit_user) && $edit_user['role'] == 'volunteer') ? 'selected' : '' ?>>Volunteer</option>
                        <option value="admin" <?= (isset($edit_user) && $edit_user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" name="save_user" class="btn btn-blue"><?= $edit_user ? 'Update User' : 'Save New User' ?></button>
                    <?php if($edit_user): ?> 
                        <a href="manage_users.php" class="btn btn-red" style="text-decoration:none; line-height: 1.5;">Cancel Edit</a> 
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <h3>System Users Directory</h3>
        <table>
            <thead><tr><th>Full Name</th><th>Username</th><th>Role</th><th>Availability</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= e($row['full_name']) ?></strong>
                        <?php if($row['id'] == $_SESSION['user_id']) echo "<span style='font-size:10px; color:#666;'>(You)</span>"; ?>
                    </td>
                    <td><?= e($row['username']) ?></td>
                    <td><span class="btn <?= $row['role'] == 'admin' ? 'btn-red' : 'btn-green' ?>" style="font-size: 11px; padding: 4px 8px;"><?= e(strtoupper($row['role'])) ?></span></td>
                    <td><?= $row['role'] == 'volunteer' ? e($row['availability']) : 'N/A' ?></td>
                    <td>
                        <a href="?edit=<?= e($row['id']) ?>" class="btn btn-green" title="Edit"><i class="fas fa-edit"></i></a>
                        <?php if($row['id'] != $_SESSION['user_id']): // Prevent deleting yourself ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user?')"><?php csrf_field(); ?><button name="delete_user" value="<?= e($row['id']) ?>" class="btn btn-red" title="Delete"><i class="fas fa-trash"></i></button></form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>