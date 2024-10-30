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
    <title>Login - News Today</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #ff7e5f;
            outline: none;
        }

        .btn {
            padding: 10px 20px;
            background-color: #ff7e5f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #feb47b;
        }

        .btnback {
            padding: 10px 20px;
            background-color: #FF5733;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btnback:hover {
            background-color: #FFC000;
        }

        .link {
            display: block;
            margin-top: 15px;
            color: #007bff;
            text-align: center;
            text-decoration: none;
            transition: color 0.3s;
        }

        .link:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .error {
            color: #d9534f;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
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

            <div class="form-group">
                <button type="submit" class="btn">Login</button><br>
            </div>
            
            <a href="forgot_password.php" class="link">Forgot Password?</a>

            <div class="form-group">
                <a href="logout.php" class="btnback">Back</a>
            </div>
        </form>
    </div>

</body>

</html>