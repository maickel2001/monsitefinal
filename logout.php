<?php
require_once 'config/config.php';
require_once 'includes/User.php';

$user = new User();

// Logout the user
$user->logout();

// Redirect to home page
header('Location: index.php');
exit;