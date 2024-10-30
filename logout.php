<?php
require 'session.php'; // Session management

// Logout the user
logoutUser();

// Redirect to login page or homepage
header('Location: index.php');
exit();
?>
