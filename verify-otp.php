<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forget-password.php");
    exit();
}

function generateResetToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['otp'])) {
        $otp = trim($_POST['otp']);
        $email = $_SESSION['reset_email'];
        
        // Debug logging (remove in production)
        error_log("Verifying OTP: " . $otp . " for email: " . $email);
        
        // Get current UTC timestamp
        $current_time = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("SELECT * FROM password_reset_otp 
                               WHERE email = ? 
                               AND otp = ? 
                               AND expiry_time > ? 
                               ORDER BY created_at DESC 
                               LIMIT 1");
        $stmt->bind_param("sss", $email, $otp, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Debug logging (remove in production)
        error_log("SQL Result rows: " . $result->num_rows);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Generate reset token
            $token = generateResetToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Clear old tokens for this email
            $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            // Insert new token
            $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expiry_time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiry);
            
            if ($stmt->execute()) {
                // Clear used OTP
                $stmt = $conn->prepare("DELETE FROM password_reset_otp WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                header("Location: reset-password.php?token=" . $token);
                exit();
            }
        }
        $error = "Invalid or expired OTP. Please check and try again.";
        
        // Debug logging (remove in production)
        error_log("OTP Validation Error for email: " . $email);
    }
}

// Calculate remaining time
$time_elapsed = isset($_SESSION['otp_created_at']) ? (time() - $_SESSION['otp_created_at']) : 60;
$time_remaining = max(0, 60 - $time_elapsed);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Annapurna Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Verify OTP</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="otpForm">
            <div class="form-group">
                <input type="text" name="otp" id="otp" placeholder="Enter 6-digit OTP" 
                       maxlength="6" pattern="\d{6}" inputmode="numeric" required 
                       autocomplete="off">
                <ion-icon name="key" class="icon"></ion-icon>
            </div>
            
            <div class="timer">
                Time remaining: <span id="countdown"><?php echo sprintf("%02d:%02d", floor($time_remaining/60), $time_remaining%60); ?></span>
            </div>
            
            <button type="submit" class="btn btn-primary" id="verifyBtn">
                Verify OTP
            </button>
        </form>
        
        <div class="change-email">
            <a href="forget-password.php">Change Email</a>
        </div>
    </div>
    
    <script>
    let timeLeft = <?php echo $time_remaining; ?>;
    const countdownEl = document.getElementById('countdown');
    const form = document.getElementById('otpForm');
    const verifyBtn = document.getElementById('verifyBtn');
    const otpInput = document.getElementById('otp');
    
    // Format input to only allow numbers
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    const countdown = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            verifyBtn.disabled = true;
            window.location.href = 'forget-password.php';
            return;
        }
        
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
    
    // Prevent form submission if timer has expired
    form.addEventListener('submit', function(e) {
        if (timeLeft <= 0) {
            e.preventDefault();
            alert('OTP has expired. Please request a new one.');
            window.location.href = 'forget-password.php';
        }
    });
    </script>
</body>
</html>
