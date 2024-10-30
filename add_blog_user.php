<?php
// Start session and include database configuration
require 'session.php';
require 'config.php';


// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$blog = [];
$users = [];
$success = '';
$errors = [];
$editMode = false;
$editNews = ['id' => '', 'title' => '', 'content' => ''];


// Check if edit blog action is triggered
if (isset($_GET['action']) && $_GET['action'] === 'edit_blog' && isset($_GET['id'])) {
    $blogId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM blog WHERE id = :id");
        $stmt->execute([':id' => $blogId]);
        $editBlog = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($editBlog) {
            $editMode = true; // Flag to indicate we are in edit mode
        } else {
            $errors[] = 'Blog item not found.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Error fetching Blog for edit: ' . $e->getMessage();
    }
}

// Handle add blog
if (isset($_POST['add_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = trim($_POST['image_url']);

    if (empty($title) || empty($content)) {
        $errors[] = 'Title and content are required.';
    }

    if (empty($errors)) {
        // Add new blog with image URL
        try {
            $stmt = $pdo->prepare("INSERT INTO blog (title, content, image_url, created_at) VALUES (:title, :content, :image_url, NOW())");
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':image_url' => $image_url
            ]);
            $_SESSION['message'] = 'Blog added successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }

        // Redirect after adding the blog
        header('Location: user_dashboard.php');
        exit();
    }
}


// Display success message if set
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Clear the message after displaying
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Include internal CSS for styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-add {
            background-color: #007bff;
        }

        .btn-edit {
            background-color: #ffc107;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-submit {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #218838;
        }

        .error {
            color: #dc3545;
            margin-bottom: 20px;
        }

        .success {
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">

        <?php if ($message): ?>
            <div class="alert success" id="successMessage">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <script>
                // Automatically hide the success message after 3 seconds
                setTimeout(function () {
                    document.getElementById('successMessage').style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>

        <center>
            <h1>Add Blog</h1>
            <div class="form-group">
                <a href="user_dashboard.php" class="btn btn-delete">Back</a>
            </div>
        </center>

        <h2>Manage Blog</h2>

        <form method="POST">
            <input type="hidden" name="blog_id"
                value="<?php echo $editMode ? htmlspecialchars($editBlog['id']) : ''; ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title"
                    value="<?php echo $editMode ? htmlspecialchars($editBlog['title']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" rows="5"
                    required><?php echo $editMode ? htmlspecialchars($editBlog['content']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL:</label>
                <input type="text" name="image_url"
                    value="<?php echo $editMode ? htmlspecialchars($editBlog['image_url']) : ''; ?>">
            </div>

            <button type="submit" name="<?php echo $editMode ? 'update_blog' : 'add_blog'; ?>"
                class="btn-submit"><?php echo $editMode ? 'Update Blog' : 'Add Blog'; ?></button>
        </form>
    </div>
</body>

</html>