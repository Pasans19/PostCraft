<?php
session_start(); // Start the session

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get the currently logged-in user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Login a user
function loginUser($userId) {
    $_SESSION['user_id'] = $userId;
    // Optionally set other session variables, e.g., user role
    // $_SESSION['user_role'] = $userRole;
}

// Logout a user
function logoutUser() {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php'); // Redirect to login page
        exit();
    }
}
