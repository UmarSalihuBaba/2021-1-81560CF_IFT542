<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
header('Location: ' . ($user ? 'dashboard.php' : 'login.php'));
exit;
