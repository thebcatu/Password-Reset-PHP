<?php
session_start();
require_once 'config.php';

if (!isset($_GET['token'])) {
    header("Location: forget-password.php");
    exit();
}

$token = $_GET['token'];
$stmt = $conn->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expiry_time > NOW() LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: forget-password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            $error = "Password must be at least 8 characters long, contain 1 uppercase letter and 1 number.";
        } else {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                
                $_SESSION['success'] = "Password has been reset successfully.";
                header("Location: index.html");
                exit();
            }
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Annapurna Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Reset Password</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <ion-icon name="eye" class="icon" id="togglePassword"></ion-icon>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <ion-icon name="eye" class="icon" id="toggleConfirmPassword"></ion-icon>
            </div>
            
            <div class="password-requirements">
                Password must contain:
                <ul>
                    <li>At least 8 characters</li>
                    <li>One uppercase letter</li>
                    <li>One number</li>
                </ul>
            </div>
            
            <div class="progress">
                <div class="progress-bar" id="passwordStrength"></div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Reset Password
            </button>
        </form>
    </div>
    
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.setAttribute('name', type === 'password' ? 'eye' : 'eye-off');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const password = document.getElementById('confirm_password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.setAttribute('name', type === 'password' ? 'eye' : 'eye-off');
    });

    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        document.getElementById('passwordStrength').style.width = strength + '%';
        document.getElementById('passwordStrength').style.backgroundColor = 
            strength <= 25 ? '#dc3545' :
            strength <= 50 ? '#ffc107' :
            strength <= 75 ? '#17a2b8' : '#28a745';
    });
    </script>
</body>
</html>