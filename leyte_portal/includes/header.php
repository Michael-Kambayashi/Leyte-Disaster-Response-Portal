<?php 
// Fetch user details
$header_user_id = $_SESSION['user_id'];
$header_stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$header_stmt->bind_param("i", $header_user_id);
$header_stmt->execute();
$header_user = $header_stmt->get_result()->fetch_assoc();

// Determine where the bell icon should go based on role
$alert_link = ($_SESSION['role'] == 'admin') ? 'manage_alerts.php' : 'alerts.php';
?>

<div class="header-top">
    <h2><?= isset($page_title) ? $page_title : 'Leyte Portal' ?></h2>
    
    <div style="display: flex; align-items: center; gap: 15px;">
        
        <!-- Day/Night Mode Toggle -->
        <i class="fas fa-moon" id="theme-icon" onclick="toggleTheme()" style="color: #a0aec0; cursor: pointer; font-size: 20px;" title="Toggle Day/Night Mode"></i>
        
        <!-- Notification Bell (Clickable) -->
        <a href="<?= $alert_link ?>" style="text-decoration: none;">
            <i class="fas fa-bell" style="color: #fc8181; cursor: pointer; font-size: 20px;" title="View Alerts"></i>
        </a>
        
        <span style="font-weight: 600; color: #4a5568;">
            <?= e($header_user['full_name']) ?> (<?= ucfirst($_SESSION['role']) ?>)
        </span>
        
        <i class="fas fa-user-circle" style="font-size: 35px; color: #4a5568;"></i>
    </div>
</div>

<!-- JavaScript for Day/Night Mode -->
<script>
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const icon = document.getElementById('theme-icon');
    
    if(document.body.classList.contains('dark-mode')) {
        icon.classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem('theme', 'dark');
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem('theme', 'light');
    }
}

// Check local storage on page load so it remembers the user's choice
document.addEventListener("DOMContentLoaded", function() {
    if(localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
    }
});
</script>