<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand">Admin Panel</span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</nav>
<div class="container mt-4">
    <div class="list-group" style="max-width:400px;">
        <a href="courses.php" class="list-group-item list-group-item-action">Manage Courses</a>
        <a href="users.php" class="list-group-item list-group-item-action">Manage Users</a>
        <a href="logs.php" class="list-group-item list-group-item-action">View Audit Log</a>
        <a href="../dashboard.php" class="list-group-item list-group-item-action">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
