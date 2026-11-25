<?php
require_once '../includes/auth.php';
if (!empty($_SESSION['user'])) log_activity('Logout');
$_SESSION = [];
session_destroy();
header('Location: login.php');
