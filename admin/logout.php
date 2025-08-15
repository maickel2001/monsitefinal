<?php
require_once '../config/config.php';
require_once '../includes/Admin.php';

$admin = new Admin();

// Logout the admin
$admin->logout();

// Redirect to admin login
header('Location: login.php');
exit;