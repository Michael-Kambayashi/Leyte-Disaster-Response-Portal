<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'admin') { header("Location: ../index.php"); exit; }
$page_title = "Admin Dashboard";
?>
<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="dashboard-grid">
            <a href="manage_users.php" class="card-link">
                <div class="card bg-green">
                    <h3>Volunteers & Users</h3>
                    <i class="fas fa-hands-helping"></i>
                </div>
            </a>
            <a href="manage_alerts.php" class="card-link">
                <div class="card bg-red">
                    <h3>Manage Alerts</h3>
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </a>
            <a href="manage_centers.php" class="card-link" style="grid-column: span 2;">
                <div class="card bg-purple">
                    <h3>Evacuation Centers</h3>
                    <i class="fas fa-home"></i>
                </div>
            </a>
            <a href="manage_tasks.php" class="card-link" style="grid-column: span 2;">
                <div class="card bg-blue" style="background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);">
                    <h3>Manage Tasks</h3>
                    <i class="fas fa-tasks"></i>
                </div>
            </a>
        </div>
    </div>
</body>
</html>