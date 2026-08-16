<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
$conn = get_db_connection();

$users = $conn->query('SELECT id, full_name, email, role, matric_no, locked_until FROM users ORDER BY id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="index.php">&larr; Back</a>
    <h3 class="mt-2">Users</h3>
    <table class="table bg-white">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Matric No</th><th>Locked Until</th></tr></thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['matric_no'] ?? '-') ?></td>
                <td><?= htmlspecialchars($u['locked_until'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
