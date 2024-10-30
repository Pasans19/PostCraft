<?php
// Start session and include database configuration
require 'session.php';
require 'config.php';


// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$news = [];
$users = [];
$success = '';
$errors = [];
$editMode = false;
$editNews = ['id' => '', 'title' => '', 'content' => ''];

// Function to fetch blog items
function fetchBlog($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM blog");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Error fetching news: ' . $e->getMessage()];
    }
}

// Function to fetch users
function fetchUsers($pdo)
{
    try {
        $stmt = $pdo->query("SELECT id, name, email, role FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Error fetching users: ' . $e->getMessage()];
    }
}

// Fetch blog and users
$blog = fetchBlog($pdo);
$users = fetchUsers($pdo);

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

// Handle add or update blog
if (isset($_POST['add_blog']) || isset($_POST['update_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $blogId = $_POST['blog_id'] ?? '';

    if (empty($title) || empty($content)) {
        $errors[] = 'Title and content are required.';
    }

    if (empty($errors)) {
        if (isset($_POST['update_blog']) && $blogId) {
            // Update blog
            try {
                $stmt = $pdo->prepare("UPDATE blog SET title = :title, content = :content WHERE id = :id");
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content,
                    ':id' => $blogId
                ]);
                $_SESSION['message'] = 'Blog updated successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        } else {
            // Add new blog
            try {
                $stmt = $pdo->prepare("INSERT INTO blog (title, content, created_at) VALUES (:title, :content, NOW())");
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content
                ]);
                $_SESSION['message'] = 'Blog added successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }

        // Redirect after adding or updating blog
        header('Location: admin_dashboard.php');
        exit();
    }
}

// Handle delete blog
if (isset($_GET['action']) && $_GET['action'] === 'delete_blog' && isset($_GET['id'])) {
    $blogId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM blog WHERE id = :id");
        $stmt->execute([':id' => $blogId]);
        $_SESSION['message'] = 'Blog deleted successfully!';
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }

    // Redirect after deleting blog
    header('Location: admin_dashboard.php');
    exit();
}

// Handle delete user
if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $_SESSION['message'] = 'User deleted successfully!';
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }

    // Redirect after deleting user
    header('Location: admin_dashboard.php');
    exit();
}

// Handle change user role
if (isset($_POST['change_role'])) {
    $userId = $_POST['user_id'];
    $newRole = trim($_POST['role']);

    if (empty($newRole) || !in_array($newRole, ['user', 'admin'])) {
        $errors[] = 'Invalid role.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([
                ':role' => $newRole,
                ':id' => $userId
            ]);
            $_SESSION['message'] = 'User role updated successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }

        // Redirect after deleting user
        header('Location: admin_dashboard.php');
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
                setTimeout(function() {
                    document.getElementById('successMessage').style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>

        <center>
            <h1>Admin Dashboard</h1>
            <div class="form-group">
                <a href="logout.php" class="btn btn-delete">Log out</a>
            </div>
        </center>
        <a href="logout.php" class="btn">Logout</a>

        <h2>Manage Blog</h2>

        <form method="POST">
            <input type="hidden" name="blog_id" value="<?php echo $editMode ? htmlspecialchars($editBlog['id']) : ''; ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" value="<?php echo $editMode ? htmlspecialchars($editBlog['title']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" rows="5" required><?php echo $editMode ? htmlspecialchars($editBlog['content']) : ''; ?></textarea>
            </div>
            <button type="submit" name="<?php echo $editMode ? 'update_blog' : 'add_blog'; ?>" class="btn-submit"><?php echo $editMode ? 'Update Blog' : 'Add Blog'; ?></button>
        </form>

        <h2>Current Blog</h2>
        <table border="1" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blog as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['content']); ?></td>
                        <td>
                            <a href="?action=edit_blog&id=<?php echo $item['id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="?action=delete_blog&id=<?php echo $item['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Manage Users</h2>
        <table border="1" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" required>
                                    <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                                    <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <button type="submit" name="change_role" class="btn btn-edit">Change Role</button>
                            </form>
                        </td>
                        <td>
                            <a href="?action=delete_user&id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table><br>

    </div>
</body>

</html>