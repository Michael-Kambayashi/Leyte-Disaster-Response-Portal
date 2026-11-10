<?php
require 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Public registration creates volunteer accounts only.
    $role = 'volunteer';
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $fullname, $role);
    
    if($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else { 
        $error = "Username already exists."; 
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Leyte Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="margin-bottom:20px; text-align:center;">Portal Registration</h2>
            
            <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
            
            <form method="POST"><?php csrf_field(); ?>
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <p style="margin-bottom:12px; color:#666;">Registration is for volunteers. Administrators are created separately.</p>

                <button type="submit" class="btn btn-blue" style="width:100%;">Register</button>
            </form>
            
            <p style="margin-top:15px; text-align:center;">Already have an account? <a href="index.php">Login here</a></p>
        </div>
    </div>
</body>
</html>