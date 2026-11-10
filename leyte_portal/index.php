<?php
require 'config.php';
if(isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'volunteer/dashboard.php'));
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        if(password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: " . ($row['role'] == 'admin' ? 'admin/dashboard.php' : 'volunteer/dashboard.php'));
            exit;
        } else { $error = "Invalid password!"; }
    } else { $error = "User not found!"; }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login - Leyte Portal</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="margin-bottom:20px; text-align:center;">Leyte Portal</h2>
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST"><?php csrf_field(); ?>
                <div class="form-group"><input type="text" name="username" placeholder="Username" required></div>
                <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
                <button type="submit" class="btn btn-blue" style="width:100%;">Login</button>
            </form>
            <p style="margin-top:15px; text-align:center;">No account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>