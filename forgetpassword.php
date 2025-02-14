<?php
session_start();
require_once 'config/database.php';

function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function sanitizeInput($input) {
    if (!is_string($input)) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sendResetEmail($email, $otp, $token) {
    $resetLink = "https://mysite.com/resetpassword.php?token=" . $token;
    $to = $email;
    $subject = "Password Reset Request - Annapurna Hotel";
    $message = "Your OTP for password reset is: $otp\n\n";
    $message .= "Or click the following link to reset your password:\n$resetLink\n\n";
    $message .= "This OTP and link will expire in 1 minute.";
    $headers = "From: noreply@annapurnahotel.com";

    return mail($to, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'request_otp':
                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                
                if (empty($email)) {
                    $response['message'] = 'Invalid email address';
                    break;
                }

                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("SELECT created_at FROM password_resets 
                        WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
                    $stmt->execute([$email]);
                    
                    if ($stmt->rowCount() === 0) {
                        $otp = generateOTP();
                        $token = generateToken();
                        
                        $stmt = $pdo->prepare("INSERT INTO password_resets 
                            (email, otp, token, expiry) VALUES 
                            (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 MINUTE))");
                        
                        if ($stmt->execute([$email, $otp, $token])) {
                            if (sendResetEmail($email, $otp, $token)) {
                                $_SESSION['reset_email'] = $email;
                                $response = [
                                    'success' => true,
                                    'message' => 'OTP sent successfully'
                                ];
                            } else {
                                $response['message'] = 'Failed to send email';
                            }
                        } else {
                            $response['message'] = 'Database error occurred';
                        }
                    } else {
                        $response['message'] = 'Please wait 1 minute before requesting another OTP';
                    }
                } else {
                    $response['message'] = 'Email not found';
                }
                break;

            case 'verify_otp':
                if (!isset($_SESSION['reset_email'])) {
                    $response['message'] = 'Session expired. Please try again';
                    break;
                }

                $email = $_SESSION['reset_email'];
                $otp = sanitizeInput($_POST['otp'] ?? '');
                
                if (strlen($otp) !== 6 || !ctype_digit($otp)) {
                    $response['message'] = 'Invalid OTP format';
                    break;
                }

                $stmt = $pdo->prepare("SELECT id, token FROM password_resets 
                    WHERE email = ? AND otp = ? AND expiry > NOW() AND used = 0 
                    ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$email, $otp]);
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 
                        WHERE id = ?");
                    $stmt->execute([$row['id']]);
                    
                    $_SESSION['reset_token'] = $row['token'];
                    
                    $response = [
                        'success' => true,
                        'message' => 'OTP verified successfully'
                    ];
                } else {
                    $response['message'] = 'Invalid or expired OTP';
                }
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Annapurna Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/css/ionicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/forget-password.css">
</head>
<body>
    <div class="forget-container">
        <div class="forget-card">
            <div class="forget-header">
                <h1>Forgot Password</h1>
                <p id="forget-status-message"></p>
            </div>

            <div id="forget-email-section" class="forget-section active">
                <div class="forget-input-group">
                    <i class="icon ion-ios-mail"></i>
                    <input type="email" id="forget-email" placeholder="Enter your email address">
                </div>
                <button id="forget-submit-email" class="forget-button">
                    Get OTP
                    <i class="icon ion-ios-arrow-forward"></i>
                </button>
                <a href="login.php" class="forget-login-link">
                    <i class="icon ion-ios-arrow-back"></i>
                    Back to Login
                </a>
            </div>

            <div id="forget-otp-section" class="forget-section">
                <div class="forget-email-display">
                    <span id="forget-display-email"></span>
                    <button id="forget-change-email" class="forget-link-button">Change</button>
                </div>
                <div class="forget-otp-boxes">
                    <input type="text" maxlength="1" class="forget-otp-input">
                    <input type="text" maxlength="1" class="forget-otp-input">
                    <input type="text" maxlength="1" class="forget-otp-input">
                    <input type="text" maxlength="1" class="forget-otp-input">
                    <input type="text" maxlength="1" class="forget-otp-input">
                    <input type="text" maxlength="1" class="forget-otp-input">
                </div>
                <div class="forget-timer">
                    Time remaining: <span id="forget-countdown">01:00</span>
                </div>
                <button id="forget-verify-otp" class="forget-button">
                    Verify OTP
                    <i class="icon ion-ios-checkmark"></i>
                </button>
                <button id="forget-resend-otp" class="forget-button forget-secondary" disabled>
                    Resend OTP
                    <i class="icon ion-ios-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    <script src="js/forget-password.js"></script>
</body>
</html>