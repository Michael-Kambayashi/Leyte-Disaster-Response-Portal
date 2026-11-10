<div class="sidebar">
    <h2>Leyte Portal</h2>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
        <a href="manage_tasks.php"><i class="fas fa-tasks"></i> Manage Tasks</a>
        <a href="manage_centers.php"><i class="fas fa-campground"></i> Evacuation Centers</a>
        <a href="manage_alerts.php"><i class="fas fa-bell"></i> Manage Alerts</a>
    <?php else: ?>
        <a href="my_tasks.php"><i class="fas fa-clipboard-list"></i> My Assigned Tasks</a>
        <a href="view_centers.php"><i class="fas fa-map-marker-alt"></i> Evacuation Centers</a>
    <?php endif; ?>
    
    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>