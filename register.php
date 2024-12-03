<?php

// Start the session
require 'session.php';

// Include database configuration
require 'config.php';

// Redirect to homepage if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Initialize an empty array for storing errors
$errors = [];
$success = '';

// Include database configuration
require 'config.php'; // Ensure this path is correct

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Strong password validation
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($passwordRegex, $password)) {
        $errors[] = 'Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if the email already exists in the database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $errors[] = 'This email address is already registered.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        // Secure password handling
        $salt = bin2hex(random_bytes(16)); // Generate random salt
        $pepper = PEPPER; // Retrieve pepper from config file

        if (empty($pepper)) {
            $errors[] = 'Pepper value is missing in the config file.';
        } else {
            $hashed_password = hash('sha256', $salt . $password . $pepper); // SHA-256 hashing with salt and pepper

            // Insert into the database (using prepared statements to prevent SQL injection)
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, salt, role) VALUES (:name, :email, :password, :salt, 'user')");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':salt' => $salt,
                ]);
                $success = "Registration successful!";
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-image: url('your-background-image.jpg'); /* Set your background image URL here */
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .signup-container {
            display: flex;
            width: 800px;
            max-width: 100%;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        .left-section {
            width: 50%;
            background-image: url('https://cdn.pixabay.com/photo/2019/07/19/03/30/landscape-4347888_1280.jpg'); /* Set the left side image URL here */
            background-size: cover;
            background-position: center;
        }

        .right-section {
            width: 50%;
            padding: 30px;
            background-color: #333;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            color: #fff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #bbb;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #444;
            background-color: #222;
            color: #fff;
            border-radius: 5px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #008080; /* Teal color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #006666;
        }

        .btnlogin {
            background-color: #333;
            color: #fff;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btnlogin:hover {
            background-color: #555;
        }

        .error, .success {
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        .error {
            color: #ff4d4d;
        }

        .success {
            color: #4caf50;
        }
    </style>
</head>

<body>

    <div class="signup-container">
        <!-- Left section with background image -->
        <div class="left-section"></div>

        <!-- Right section with form -->
        <div class="right-section">
            <h2>Signup</h2>

            <!-- Display errors from server-side -->
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

            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">Signup</button>

                <a href="login.php" class="btnlogin">Login</a>
            </form>
        </div>
    </div>

</body>

</html>
