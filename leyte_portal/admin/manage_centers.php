<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'admin') { header("Location: ../index.php"); exit; }
$page_title = "Manage Centers";

// --- CREATE & UPDATE CENTER ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_center'])) {
    $name = $_POST['name']; $barangay = $_POST['barangay']; $capacity = $_POST['capacity']; $status = $_POST['status'];
    if(!empty($_POST['id'])) {
        $stmt = $conn->prepare("UPDATE evacuation_centers SET name=?, barangay=?, capacity=?, status=? WHERE id=?");
        $stmt->bind_param("ssisi", $name, $barangay, $capacity, $status, $_POST['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO evacuation_centers (name, barangay, capacity, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $barangay, $capacity, $status);
    }
    $stmt->execute(); header("Location: manage_centers.php"); exit;
}

// --- DELETE CENTER ---
if (isset($_POST['delete_center'])) {
    $stmt = $conn->prepare("DELETE FROM evacuation_centers WHERE id=?");
    $stmt->bind_param("i", $_POST['delete_center']);
    $stmt->execute(); header("Location: manage_centers.php"); exit;
}

// --- APPROVE / REJECT REQUESTS ---
if (isset($_POST['approve_req'])) {
    $stmt = $conn->prepare("UPDATE center_assignments SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param('i', $_POST['approve_req']);
    $stmt->execute();
    header("Location: manage_centers.php"); exit;
}
if (isset($_POST['reject_req'])) {
    $stmt = $conn->prepare('DELETE FROM center_assignments WHERE id = ?');
    $stmt->bind_param('i', $_POST['reject_req']);
    $stmt->execute();
    header("Location: manage_centers.php"); exit;
}

// --- ADMIN MANUALLY ASSIGNS ---
if (isset($_POST['assign_volunteer'])) {
    $stmt = $conn->prepare("INSERT IGNORE INTO center_assignments (center_id, volunteer_id, status) VALUES (?, ?, 'Approved')");
    $stmt->bind_param("ii", $_POST['center_id'], $_POST['volunteer_id']);
    $stmt->execute(); header("Location: manage_centers.php"); exit;
}

// --- FETCH DATA ---
$edit_data = null;
if(isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM evacuation_centers WHERE id=?");
    $stmt->bind_param("i", $_GET['edit']); $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

$volunteers = $conn->query("SELECT id, full_name FROM users WHERE role='volunteer'");
$raw_centers = $conn->query("SELECT * FROM evacuation_centers");

// Fetch ONLY PENDING requests
$pending_requests = $conn->query("
    SELECT ca.id as req_id, u.full_name, ec.name as center_name 
    FROM center_assignments ca 
    JOIN users u ON ca.volunteer_id = u.id 
    JOIN evacuation_centers ec ON ca.center_id = ec.id 
    WHERE ca.status = 'Pending'
");

// Fetch centers AND a list of ONLY APPROVED assigned volunteers
$centers = $conn->query("
    SELECT ec.*, 
    (SELECT GROUP_CONCAT(u.full_name SEPARATOR ', ') 
     FROM center_assignments ca 
     JOIN users u ON ca.volunteer_id = u.id 
     WHERE ca.center_id = ec.id AND ca.status = 'Approved') as assigned_vols
    FROM evacuation_centers ec
");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Centers</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Form for Create/Update Center (SAME AS BEFORE) -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h3><?= $edit_data ? 'Edit Center' : 'Add New Center' ?></h3>
                <form method="POST"><?php csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= e($edit_data['id'] ?? '') ?>">
                    <div class="form-group"><input type="text" name="name" placeholder="Center Name" value="<?= e($edit_data['name'] ?? '') ?>" required></div>
                    <div class="form-group"><input type="text" name="barangay" placeholder="Barangay" value="<?= e($edit_data['barangay'] ?? '') ?>" required></div>
                    <div class="form-group"><input type="number" name="capacity" placeholder="Capacity" value="<?= e($edit_data['capacity'] ?? '') ?>" required></div>
                    <div class="form-group">
                        <select name="status">
                            <option value="Available" <?= (isset($edit_data) && $edit_data['status']=='Available') ? 'selected' : '' ?>>Available</option>
                            <option value="Full" <?= (isset($edit_data) && $edit_data['status']=='Full') ? 'selected' : '' ?>>Full</option>
                            <option value="Closed" <?= (isset($edit_data) && $edit_data['status']=='Closed') ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" name="save_center" class="btn btn-blue" style="background-color: #7e57c2;"><?= $edit_data ? 'Update' : 'Save Center' ?></button>
                    <?php if($edit_data): ?> <a href="manage_centers.php" class="btn btn-red">Cancel</a> <?php endif; ?>
                </form>
            </div>

            <!-- Pending Requests & Manual Assign Panel -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- NEW: Pending Requests Table -->
                <div style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #fef08a;">
                    <h3 style="color: #854d0e;"><i class="fas fa-clock"></i> Pending Requests (<?= $pending_requests->num_rows ?>)</h3>
                    <?php if($pending_requests->num_rows == 0): ?>
                        <p style="color: #666; font-size: 14px;">No pending volunteer requests.</p>
                    <?php else: ?>
                        <table style="margin-top: 10px; font-size: 14px;">
                            <thead><tr><th>Volunteer</th><th>Center</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php while($req = $pending_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= e($req['full_name']) ?></strong></td>
                                    <td><?= e($req['center_name']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" ><?php csrf_field(); ?><button name="approve_req" value="<?= e($req['req_id']) ?>" class="btn btn-green" style="padding:5px 10px;"><i class="fas fa-check"></i></button></form>
                                        <form method="POST" style="display:inline;" ><?php csrf_field(); ?><button name="reject_req" value="<?= e($req['req_id']) ?>" class="btn btn-red" style="padding:5px 10px;"><i class="fas fa-times"></i></button></form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Manual Assign (SAME AS BEFORE) -->
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <h3>Directly Deploy Volunteer</h3>
                    <form method="POST"><?php csrf_field(); ?>
                        <div class="form-group">
                            <select name="volunteer_id" required>
                                <option value="" disabled selected>Select Volunteer...</option>
                                <?php while($v = $volunteers->fetch_assoc()): ?>
                                    <option value="<?= e($v['id']) ?>"><?= e($v['full_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="center_id" required>
                                <option value="" disabled selected>Select Center...</option>
                                <?php while($c = $raw_centers->fetch_assoc()): ?>
                                    <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="assign_volunteer" class="btn btn-blue">Assign (Auto-Approve)</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Centers Table -->
        <table style="margin-top: 30px;">
            <thead>
                <tr><th>Name</th><th>Barangay</th><th>Capacity</th><th>Status</th><th>Active Volunteers</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($row = $centers->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= e($row['name']) ?></strong></td>
                    <td><?= e($row['barangay']) ?></td>
                    <td><?= e($row['capacity']) ?></td>
                    <td><?= e($row['status']) ?></td>
                    <!-- Only shows Approved Volunteers -->
                    <td style="color: #16a34a; font-size: 14px;"><strong><?= !empty($row['assigned_vols']) ? e($row['assigned_vols']) : 'None' ?></strong></td>
                    <td>
                        <a href="?edit=<?= e($row['id']) ?>" class="btn btn-green"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete center?')"><?php csrf_field(); ?><button name="delete_center" value="<?= e($row['id']) ?>" class="btn btn-red"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>