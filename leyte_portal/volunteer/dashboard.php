<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'volunteer') { header("Location: ../index.php"); exit; }
$page_title = "Volunteer Dashboard";

// Handle Availability Update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_availability'])) {
    $new_status = $_POST['availability'];
    $stmt = $conn->prepare("UPDATE users SET availability = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $_SESSION['user_id']);
    $stmt->execute();
    $msg = "Availability updated successfully!";
}

// Fetch current availability
$stmt = $conn->prepare("SELECT availability FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_status = $stmt->get_result()->fetch_assoc()['availability'];
?>
<!DOCTYPE html>
<html>
<head><title>Volunteer Dashboard</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <!-- Availability Quick Toggle -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3 style="margin-bottom: 15px;">My Status</h3>
            <?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
            <form method="POST" style="display: flex; gap: 10px; align-items: center;"><?php csrf_field(); ?>
                <select name="availability" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="Available" <?= $current_status == 'Available' ? 'selected' : '' ?>>🟢 Available</option>
                    <option value="Pre-occupied" <?= $current_status == 'Pre-occupied' ? 'selected' : '' ?>>🟡 Pre-occupied</option>
                    <option value="Busy" <?= $current_status == 'Busy' ? 'selected' : '' ?>>🔴 Busy</option>
                </select>
                <button type="submit" name="update_availability" class="btn btn-blue">Update</button>
            </form>
        </div>

        <div class="dashboard-grid">
            <a href="my_tasks.php" class="card-link">
                <div class="card bg-green">
                    <h3>My Assigned Tasks</h3>
                    <i class="fas fa-tasks"></i>
                </div>
            </a>
            
            <a href="view_centers.php" class="card-link">
                <div class="card bg-purple">
                    <h3>Evacuation Centers</h3>
                    <i class="fas fa-campground"></i>
                </div>
            </a>

            <a href="alerts.php" class="card-link" style="grid-column: span 2;">
                <div class="card bg-red">
                    <h3>Active Alerts</h3>
                    <i class="fas fa-broadcast-tower"></i>
                </div>
            </a>
        </div>
    </div>
</body>
</html>