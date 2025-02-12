<?php
session_start();
require_once 'config.php';

function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

function sendOTP($email, $otp) {
    $to = $email;
    $subject = "Password Reset OTP - Annapurna Hotel";
    $message = "Your OTP for password reset is: " . $otp . "\nThis OTP will expire in 1 minute.";
    $headers = "From: noreply@annapurnahotel.com";
    
    return mail($to, $subject, $message, $headers);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $otp = generateOTP();
            
            // Delete any existing OTPs for this email
            $stmt = $conn->prepare("DELETE FROM password_reset_otp WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            // Insert new OTP with proper UTC timestamp
            $expiry = date('Y-m-d H:i:s', strtotime('+1 minute'));
            $stmt = $conn->prepare("INSERT INTO password_reset_otp (email, otp, expiry_time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $otp, $expiry);
            
            if ($stmt->execute()) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['otp_created_at'] = time();
                
                if (sendOTP($email, $otp)) {
                    header("Location: verify-otp.php");
                    exit();
                }
            }
        }
        $error = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Annapurna Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Annapurna Hotel</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" required>
                <ion-icon name="mail" class="icon"></ion-icon>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Send Reset Instructions
            </button>
        </form>
        
        <div class="change-email">
            <a href="index.html">Back to Login</a>
        </div>
    </div>
</body>
</html>