<?php
// Start the session
require 'session.php';

// Include database configuration
require_once 'config.php';

// Check if the user is logged in
requireLogin();

// Fetch blog articles from the database
try {
    $stmt = $pdo->query("SELECT title, content, image_url, created_at FROM blog ORDER BY created_at DESC");
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
            background-color: #2b2b2b; /* Dark background color */
            color: #ffffff; /* Light text color */
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background-color: #2b2b2b;
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            border-bottom: 2px solid #444;
        }

        .header .btn {
            padding: 10px 20px;
            background-color: #4caf50; /* Green button color */
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            transition: background-color 0.3s;
        }

        .header .btn:hover {
            background-color: #45a049;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 20px;
            gap: 20px; /* Space between cards */
        }

        .news-article {
            background-color: #333333; /* Dark card background */
            border-radius: 10px;
            overflow: hidden;
            width: 280px;
            max-width: 100%;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .news-article:hover {
            transform: scale(1.05);
        }

        .news-article img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .news-article h2 {
            font-size: 18px;
            color: #ffffff;
            margin: 10px 0;
            text-align: center;
        }

        .news-article p {
            font-size: 14px;
            color: #cccccc;
            text-align: center;
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #222;
            color: #888;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

    </style>
</head>
<body>

<div class="header">
    <h1 style="margin-right: auto;">User Dashboard</h1>
    <a href="logout.php" class="btn">Logout</a>
    <a href="add_blog_user.php" class="btn">Add Blog</a>
</div>

<div class="container">
    <?php if (!empty($newsArticles)): ?>
        <?php foreach ($newsArticles as $article): ?>
            <div class="news-article">
                <?php if (!empty($article['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="Blog Image">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #aaa; text-align: center;">No news articles available.</p>
    <?php endif; ?>
</div>

<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> News Today. All rights reserved.</p>
</div>

</body>
</html>
