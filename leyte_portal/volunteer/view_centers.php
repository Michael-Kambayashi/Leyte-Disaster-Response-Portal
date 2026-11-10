<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'volunteer') { header("Location: ../index.php"); exit; }
$page_title = "Evacuation Centers";
$my_id = $_SESSION['user_id'];

// Handle Sending a Request
if(isset($_POST['join_center'])) {
    $center_id = $_POST['center_id'];
    // Inserts with default status 'Pending'
    $stmt = $conn->prepare("INSERT IGNORE INTO center_assignments (center_id, volunteer_id, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("ii", $center_id, $my_id);
    $stmt->execute();
}

// Handle Canceling a Request or Withdrawing from a center
if(isset($_POST['leave_center'])) {
    $center_id = $_POST['center_id'];
    $stmt = $conn->prepare("DELETE FROM center_assignments WHERE center_id = ? AND volunteer_id = ?");
    $stmt->bind_param("ii", $center_id, $my_id);
    $stmt->execute();
}

// Fetch all centers and check the EXACT status of the volunteer's assignment
$centers = $conn->query("
    SELECT ec.*, 
    (SELECT status FROM center_assignments ca WHERE ca.center_id = ec.id AND ca.volunteer_id = $my_id) as assignment_status 
    FROM evacuation_centers ec
");
?>
<!DOCTYPE html>
<html>
<head><title>Evacuation Centers</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <p style="margin-bottom: 20px; color: #666;">Select an evacuation center to send a volunteer request to the Admin.</p>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
            <?php while($row = $centers->fetch_assoc()): ?>
            <div style="background: white; padding: 20px; border-radius: 10px; border-top: 5px solid #7e57c2;">
                <div style="display:flex; justify-content:space-between; margin-bottom: 10px;">
                    <h3 style="color:#7e57c2; margin: 0;"><i class="fas fa-home"></i> <?= e($row['name']) ?></h3>
                    
                    <!-- Show Status Badge -->
                    <?php if($row['assignment_status'] == 'Approved'): ?>
                        <span style="font-size: 12px; padding: 5px 10px; border-radius: 10px; background: #dcfce7; color: #166534; font-weight:bold;">Deployed Here</span>
                    <?php elseif($row['assignment_status'] == 'Pending'): ?>
                        <span style="font-size: 12px; padding: 5px 10px; border-radius: 10px; background: #fef08a; color: #854d0e; font-weight:bold;">Approval Pending</span>
                    <?php endif; ?>
                </div>
                
                <p><strong>Barangay:</strong> <?= e($row['barangay']) ?></p>
                <p style="margin-bottom: 15px;"><strong>Capacity:</strong> <?= e($row['capacity']) ?> people</p>
                
                <form method="POST"><?php csrf_field(); ?>
                    <input type="hidden" name="center_id" value="<?= e($row['id']) ?>">
                    <?php if($row['assignment_status'] == 'Approved'): ?>
                        <button type="submit" name="leave_center" class="btn btn-red" style="width:100%;"><i class="fas fa-times"></i> Request Reassignment</button>
                    <?php elseif($row['assignment_status'] == 'Pending'): ?>
                        <button type="submit" name="leave_center" class="btn btn-red" style="width:100%;"><i class="fas fa-times"></i> Cancel Request</button>
                    <?php else: ?>
                        <button type="submit" name="join_center" class="btn btn-blue" style="width:100%; background-color:#7e57c2;"><i class="fas fa-paper-plane"></i> Send Volunteer Request</button>
                    <?php endif; ?>
                </form>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>