<?php
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand">Student Registration System</span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</nav>
<div class="container mt-4">
    <h3>Welcome, <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['role']) ?>)</h3>
    <div class="list-group mt-3" style="max-width:400px;">
        <a href="profile.php" class="list-group-item list-group-item-action">My Profile</a>
        <a href="courses.php" class="list-group-item list-group-item-action">Course Registration</a>
        <a href="upload.php" class="list-group-item list-group-item-action">Upload Document</a>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="admin/index.php" class="list-group-item list-group-item-action">Admin Panel</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
