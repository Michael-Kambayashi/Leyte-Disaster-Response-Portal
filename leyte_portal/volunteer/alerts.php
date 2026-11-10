<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'volunteer') { header("Location: ../index.php"); exit; }
$page_title = "Active Alerts";

// Fetch Alerts
$alerts = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Active Alerts</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        
        <p style="margin-bottom: 20px; color: #666;">Stay updated with the latest emergency broadcasts.</p>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php if($alerts->num_rows == 0): ?>
                <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 40px; color: #16a34a; margin-bottom: 10px;"></i>
                    <h3>No Active Alerts</h3>
                    <p>It's quiet right now. Stay safe!</p>
                </div>
            <?php endif; ?>

            <?php while($row = $alerts->fetch_assoc()): 
                // Determine border color based on alert level
                $border_color = '#2563eb'; // Info (Blue)
                $icon = 'fa-info-circle';
                if($row['alert_level'] == 'Warning') { $border_color = '#ea580c'; $icon = 'fa-exclamation-triangle'; }
                if($row['alert_level'] == 'Critical') { $border_color = '#dc2626'; $icon = 'fa-radiation-alt'; }
            ?>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 6px solid <?= $border_color ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="color: <?= $border_color ?>; margin: 0;">
                        <i class="fas <?= $icon ?>"></i> <?= e(strtoupper($row['alert_level'])) ?> ALERT
                    </h4>
                    <span style="font-size: 12px; color: #888;"><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></span>
                </div>
                <p style="margin: 0; font-size: 16px;"><?= e($row['message']) ?></p>
            </div>
            
            <?php endwhile; ?>
        </div>

    </div>
</body>
</html>