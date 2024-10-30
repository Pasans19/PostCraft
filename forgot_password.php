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
        }else {
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

            // Send TOTP email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'prasadkau97@gmail.com';
                $mail->Password = 'jtwo hxnw rqcw muih';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('prasadkau97@gmail.com', 'News Today');
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
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4a90e2;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #4a90e2;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            background-color: #357abd;
        }

        .timer {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
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
            const interval = setInterval(function() {
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
        window.onload = function() {
            startTimer(timerDuration, timerElement);
        };
    </script>

</body>

</html>