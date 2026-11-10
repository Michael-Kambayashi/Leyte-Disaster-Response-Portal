<?php
require '../config.php';
if(($_SESSION['role'] ?? null) !== 'admin') { header("Location: ../index.php"); exit; }
$page_title = "Manage Alerts";

// Create Alert
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_alert'])) {
    $message = $_POST['message'];
    $level = $_POST['alert_level'];
    
    $stmt = $conn->prepare("INSERT INTO alerts (message, alert_level) VALUES (?, ?)");
    $stmt->bind_param("ss", $message, $level);
    $stmt->execute();
    header("Location: manage_alerts.php"); exit;
}

// Delete Alert
if(isset($_POST['delete_alert'])) {
    $stmt = $conn->prepare("DELETE FROM alerts WHERE id = ?");
    $stmt->bind_param('i', $_POST['delete_alert']);
    $stmt->execute();
    header("Location: manage_alerts.php"); exit;
}

// Fetch Alerts
$alerts = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Alerts</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>

        <!-- Create Alert Form -->
        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <h3>Create Manual Alert</h3>
            <p style="margin-bottom: 15px; color: #666;">Push an emergency alert to all volunteers.</p>
            <form method="POST"><?php csrf_field(); ?>
                <div class="form-group">
                    <textarea name="message" placeholder="Type the alert message here..." required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; min-height: 80px;"></textarea>
                </div>
                <div class="form-group" style="display: flex; gap: 10px; align-items: center;">
                    <select name="alert_level" required style="width: 200px;">
                        <option value="Info">🔵 Info (General)</option>
                        <option value="Warning">🟠 Warning (Caution)</option>
                        <option value="Critical">🔴 Critical (Emergency)</option>
                    </select>
                    <button type="submit" name="create_alert" class="btn btn-red">Push Alert</button>
                </div>
            </form>
        </div>

        <!-- Alert History -->
        <h3>Alert History</h3>
        <table>
            <thead><tr><th>Date & Time</th><th>Level</th><th>Message</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($row = $alerts->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                    <td>
                        <?php 
                            if($row['alert_level'] == 'Critical') echo "<strong style='color: #dc2626;'>Critical</strong>";
                            elseif($row['alert_level'] == 'Warning') echo "<strong style='color: #ea580c;'>Warning</strong>";
                            else echo "<strong style='color: #2563eb;'>Info</strong>";
                        ?>
                    </td>
                    <td><?= e($row['message']) ?></td>
                    <td><form method="POST" style="display:inline;" onsubmit="return confirm('Delete this alert log?')"><?php csrf_field(); ?><button class="btn btn-red" name="delete_alert" value="<?= e($row['id']) ?>">Delete</button></form></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>