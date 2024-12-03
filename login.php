<?php
// Start the session
require 'session.php';

// Include database configuration
require_once 'config.php';

// Initialize variables
$errors = [];
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // If there are no errors, proceed with login
    if (empty($errors)) {
        try {
            // Fetch user details from the database
            $stmt = $pdo->prepare("SELECT id, password, salt, role, name FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verify password
                $pepper = PEPPER; // Retrieve pepper from config file
                $hashed_password = hash('sha256', $user['salt'] . $password . $pepper);

                if ($hashed_password === $user['password']) {
                    // Login successful, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name']; // Optional: add user's name to session

                    // Redirect based on user role
                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: user_dashboard.php');
                    }
                    exit();
                } else {
                    $errors[] = 'Invalid email or password.';
                }
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Daily Digest</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5faf6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .wrapper {
            display: flex;
            width: 800px;
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .image-section {
            width: 50%;
            background-image: url('https://cdn.pixabay.com/photo/2024/02/24/20/55/cards-8594729_1280.jpg');
            /* Add your image path here */
            background-size: cover;
            background-position: center;
        }

        .form-section {
            width: 50%;
            padding: 40px;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            color: #f5faf6;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ccc;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #444;
            color: #fff;
            font-size: 16px;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            background-color: #555;
        }

        .btn {
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            margin-top: 10px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btnback {
            padding: 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            margin-top: 30px;
            /* Increased margin-top */
            width: 30%;
            text-align: center;
            transition: background-color 0.3s;
        }

        .btnback:hover {
            background-color: #c82333;
        }

        .link {
            display: block;
            margin-top: 15px;
            color: #f8f9fa;
            text-align: center;
            text-decoration: none;
            transition: color 0.3s;
        }

        .link:hover {
            color: #adb5bd;
        }

        .error {
            color: #f44336;
            font-size: 14px;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="image-section"></div>

        <div class="form-section">
            <h2>Login</h2>

            <!-- Display errors from server-side -->
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                </div>
            <?php endif; ?>

            <!-- Display success message -->
            <?php if ($success): ?>
                <div class="success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>

                <a href="forgot_password.php" class="link">Forgot Password?</a>

                <!-- Centered Back Button -->
                
            </form>
            <a href="logout.php" class="btnback">Back</a>
        </div>
    </div>

</body>

</html>