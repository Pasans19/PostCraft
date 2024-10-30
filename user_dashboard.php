<?php
// Start the session
require 'session.php';

// Include database configuration
require_once 'config.php';

// Check if the user is logged in
requireLogin();

// Fetch news articles from the database
try {
    $stmt = $pdo->query("SELECT title, content, created_at FROM news ORDER BY created_at DESC");
    $newsArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - News Today</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container {
            padding: 20px;
            max-width: 1200px; /* Increased width for better grid layout */
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px; /* Added margin for spacing */
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between grid items */
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            font-size: 16px;
            text-decoration: none;
            background-color: #28a745; /* Green color for the button */
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* Spacing between the button and articles */
        }

        .btn:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .news-article {
            border: 1px solid #007bff; /* Blue border */
            border-radius: 5px; /* Rounded corners */
            padding: 15px; /* Padding for content */
            background-color: #e9f7fd; /* Light blue background for articles */
            transition: transform 0.3s; /* Animation effect */
        }

        .news-article:hover {
            transform: scale(1.02); /* Slight zoom on hover */
            background-color: #d1ecf1; /* Darker light blue on hover */
        }

        .news-article h2 {
            margin: 0 0 10px;
            font-size: 20px; /* Slightly smaller font size for titles */
            color: #333;
        }

        .news-article p {
            font-size: 14px; /* Slightly smaller font size for content */
            color: #666;
        }

        .news-article .date {
            font-size: 12px; /* Smaller font size for date */
            color: #999;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>News Today - User Dashboard</h1>
    <a href="logout.php" class="btn">Logout</a>
</div>

<div class="container">
    <?php if (!empty($newsArticles)): ?>
        <?php foreach ($newsArticles as $article): ?>
            <div class="news-article">
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
                <div class="date">Published on: <?php echo htmlspecialchars($article['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No news articles available.</p>
    <?php endif; ?>
</div>

<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> News Today. All rights reserved.</p>
</div>

</body>
</html>
