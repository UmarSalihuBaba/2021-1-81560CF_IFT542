<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
$conn = get_db_connection();

$logs = $conn->query('SELECT actor_email, action, details, ip_address, created_at FROM audit_log ORDER BY id DESC LIMIT 100');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="index.php">&larr; Back</a>
    <h3 class="mt-2">Audit Log (who / what / when)</h3>
    <table class="table table-sm bg-white">
        <thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        <?php while ($l = $logs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($l['created_at']) ?></td>
                <td><?= htmlspecialchars($l['actor_email'] ?? 'unknown') ?></td>
                <td><?= htmlspecialchars($l['action']) ?></td>
                <td><?= htmlspecialchars($l['details'] ?? '') ?></td>
                <td><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
