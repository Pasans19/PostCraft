<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Today - Login or Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            animation: fadeIn 1s ease-in-out; /* Animation for the body */
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-20px);
            animation: slideIn 0.5s forwards; /* Animation for the container */
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        p {
            color: #666;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            text-decoration: none;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease; /* Added transform transition */
        }

        .btn-login {
            background-color: #007bff;
            color: white;
        }

        .btn-login:hover {
            background-color: #0056b3;
            transform: scale(1.05); /* Scale animation on hover */
        }

        .btn-register {
            background-color: #28a745;
            color: white;
        }

        .btn-register:hover {
            background-color: #218838;
            transform: scale(1.05); /* Scale animation on hover */
        }

        .image-container {
            margin-top: 20px; /* Added margin for spacing */
        }

        .image-container img {
            width: 100%; /* Make images responsive */
            max-width: 80%; /* Limit the maximum width */
            border-radius: 8px; /* Rounded corners */
        }
    </style>
</head>
<body>

<div class="container">
<div class="image-container">
    </div>
    <a href="login.php" class="btn btn-login">Login</a>
    <a href="register.php" class="btn btn-register">Register</a>
</div>

</body>
</html>