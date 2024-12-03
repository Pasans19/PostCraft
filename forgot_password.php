<?php
session_start();
$errors = [];
$success = '';
$totpSent = false; // Track if TOTP has been sent
$totpVerified = false; // Track if TOTP has been verified
$totp = '';
$totpExpirationTime = 300; // TOTP validity in seconds

// Include database configuration
require 'config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include Composer's autoloader

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Case 1: Send TOTP
    if (isset($_POST['send_totp'])) {
        // Sanitize and validate email
        $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } else {
            // Check if the email exists in the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $errors[] = 'Email does not exist in the system.';
            }
        }

        if (empty($errors)) {
            // Generate and store TOTP
            $totp = rand(100000, 999999); // Generate a 6-digit TOTP
            $_SESSION['totp'] = $totp;
            $_SESSION['totp_expiration'] = time() + $totpExpirationTime;
            $_SESSION['email'] = $email;

            // Generate token for password reset
            $token = bin2hex(random_bytes(16)); // Secure random 32-character token
            $expiresAt = date("Y-m-d H:i:s", time() + $totpExpirationTime); // Set expiration to 5 minutes

            // Insert the token and expiration into the password_resets table
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $stmt->execute([
                ':user_id' => $user['id'], // Assuming the user ID is available from the $user fetched earlier
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);

            // Save token in the session for further use if needed
            $_SESSION['reset_token'] = $token;

            // Send TOTP email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ransikameedum@gmail.com';
                $mail->Password = 'nmsa joez quuy kzqc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('ransikameedum@gmail.com', 'PostCraft');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your TOTP Code for Password Reset';
                $mail->Body = "Your TOTP code is <strong>$totp</strong>. It will expire in 5 minutes.";

                $mail->send();
                $success = 'TOTP has been sent to your email.';
                $totpSent = true;
            } catch (Exception $e) {
                $errors[] = "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }

    // Case 2: Verify TOTP
    if (isset($_POST['verify_totp'])) {
        $enteredTotp = isset($_POST['totp']) ? trim($_POST['totp']) : '';

        if ($enteredTotp == $_SESSION['totp'] && time() <= $_SESSION['totp_expiration']) {
            $totpVerified = true;
            $_SESSION['totp_verified'] = true;
            $success = 'TOTP verified. You can now reset your password.';
        } else {
            $errors[] = 'Invalid or expired TOTP.';
            unset($_SESSION['totp'], $_SESSION['totp_expiration'], $_SESSION['totp_verified']);
        }
    }

    // Case 3: Reset password after TOTP verification
    if (isset($_POST['reset_password'])) {
        if (isset($_SESSION['totp_verified']) && $_SESSION['totp_verified'] === true) {
            $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
            $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

            // Strong password validation before checking confirmation
            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

            if (!preg_match($passwordRegex, $newPassword)) {
                $errors[] = 'Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
            } elseif ($newPassword !== $confirmPassword) {
                // Check if passwords match after regex validation
                $errors[] = 'Passwords do not match.';
            } elseif (empty($newPassword) || empty($confirmPassword)) {
                $errors[] = 'Both password fields are required.';
            } else {
                // Hash the new password with SHA-256 using salt and pepper
                $salt = bin2hex(random_bytes(16)); // Generate random salt
                $pepper = PEPPER; // Retrieve the pepper from the config file
                $hashedPassword = hash('sha256', $salt . $newPassword . $pepper);

                // Get the stored email
                $email = $_SESSION['email'];

                // Update the password and salt in the database
                $stmt = $pdo->prepare("UPDATE users SET password = :password, salt = :salt WHERE email = :email");
                $stmt->execute([
                    ':password' => $hashedPassword,
                    ':salt' => $salt,
                    ':email' => $email
                ]);

                $success = 'Your password has been reset successfully.';

                // Clear session variables related to the reset process
                unset($_SESSION['totp'], $_SESSION['totp_expiration'], $_SESSION['totp_verified'], $_SESSION['email']);
                header('Location: login.php');
                exit();
            }
        } else {
            $errors[] = 'Please verify your TOTP first.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #0F2027 0%, #203A43 50%, #2C5364 100%);
        min-height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .container {
        max-width: 450px;
        width: 90%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        position: relative;
        overflow: hidden;
    }

    .container::before {
        content: '';
        position: absolute;
        top: 0;
        left: -50%;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, #764ba2, transparent);
        animation: loading 2s linear infinite;
    }

    @keyframes loading {
        0% { left: -50%; }
        100% { left: 100%; }
    }

    h2 {
        color: #764ba2;
        font-size: 2em;
        margin-bottom: 30px;
        text-align: center;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        color: #764ba2;
        font-size: 0.9em;
        font-weight: 500;
        display: block;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 1em;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-group input:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
        outline: none;
    }

    .error {
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%);
        color: #721c24;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 0.9em;
        box-shadow: 0 4px 15px rgba(255, 154, 158, 0.2);
    }

    .success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #1a5928;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 0.9em;
        box-shadow: 0 4px 15px rgba(132, 250, 176, 0.2);
    }

    .btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-size: 1em;
        font-weight: 500;
        cursor: pointer;
        width: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
    }

    .timer {
        text-align: center;
        color: #764ba2;
        font-size: 0.9em;
        margin-top: 15px;
        font-weight: 500;
    }

    /* Animation for form transitions */
    form {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .container {
            padding: 25px;
        }

        h2 {
            font-size: 1.5em;
        }
    }
</style>
</head>

<body>

    <div class="container">
        <h2>Reset Password</h2>

        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if ($success): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!$totpSent && !$totpVerified): ?>

            <!-- TOTP sending form -->
            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Enter Your Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <button type="submit" name="send_totp" class="btn">Send TOTP</button>
            </form>
        <?php elseif ($totpSent && !$totpVerified): ?>

            <!-- TOTP verification form -->
            <form action="" method="post" id="totpForm">
                <div class="form-group">
                    <label for="totp">Enter TOTP:</label>
                    <input type="text" name="totp" id="totp" required>
                </div>
                <button type="submit" name="verify_totp" class="btn">Verify TOTP</button>
                <div class="timer" id="totpTimer">Time remaining: 5:00</div>
            </form>
            <form action="" method="post" style="display: none;" id="resendForm">
                <button type="submit" name="send_totp" class="btn">Resend TOTP</button>
            </form>

        <?php elseif ($totpVerified): ?>

            <!-- Password reset form -->
            <form action="" method="post">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="reset_password" class="btn">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Timer countdown for TOTP expiration
        let timerDuration = <?php echo $totpExpirationTime; ?>;
        const timerElement = document.getElementById('totpTimer');
        const totpForm = document.getElementById('totpForm');
        const resendForm = document.getElementById('resendForm');

        function startTimer(duration, display) {
            let timer = duration,
                minutes, seconds;
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = `Time remaining: ${minutes}:${seconds}`;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "TOTP Expired. Resend TOTP.";
                    totpForm.style.display = 'none'; // Hide TOTP form
                    resendForm.style.display = 'block'; // Show resend TOTP form
                }
            }, 1000);
        }

        // Start the timer when the page loads
        window.onload = function () {
            startTimer(timerDuration, timerElement);
        };
    </script>

</body>

</html>