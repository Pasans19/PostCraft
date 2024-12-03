<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Today - Login or Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://source.unsplash.com/1600x900/?news,technology') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #ffffff;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h1 {
            margin-bottom: 20px;
            color: #f4f4f4;
            font-size: 2.2rem;
        }

        p {
            color: #ddd;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            text-decoration: none;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-login {
            background-color: #007bff;
            color: white;
        }

        .btn-login:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .btn-register {
            background-color: #28a745;
            color: white;
        }

        .btn-register:hover {
            background-color: #218838;
            transform: scale(1.1);
        }

        .image-container {
            margin-top: 20px;
        }

        .image-container img {
            width: 100%;
            max-width: 200px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
            animation: popIn 0.5s ease-in-out;
        }

        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="container">
<h1>Welcome to The Daily Digest</h1>
<p>Discover fresh insights and stories from our community. Sign in or join us to start exploring!</p>


    <div class="image-container">
        <img src="https://cdn.pixabay.com/photo/2015/02/01/21/11/blackboard-620314_1280.jpg" alt="News Icon">
    </div>
    <a href="login.php" class="btn btn-login">Login</a>
    <a href="register.php" class="btn btn-register">Register</a>
</div>

</body>
</html>
