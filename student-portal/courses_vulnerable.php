<?php
/**
 * courses.php — BASELINE
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$conn = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    $stmt = $conn->prepare('INSERT IGNORE INTO enrolments (user_id, course_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $user['id'], $courseId);
    $stmt->execute();
    $stmt->close();
    log_event('course_registration', "course_id=$courseId", $user['email']);
    $message = 'Registration submitted.';
}

$courses = $conn->query('SELECT id, code, title, capacity FROM courses ORDER BY code');
$myEnrolments = [];
$res = $conn->prepare('SELECT course_id FROM enrolments WHERE user_id = ? AND status = "registered"');
$res->bind_param('i', $user['id']);
$res->execute();
$rows = $res->get_result();
while ($r = $rows->fetch_assoc()) {
    $myEnrolments[] = (int)$r['course_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:600px;">
    <a href="dashboard.php">&larr; Back</a>
    <h3 class="mt-2">Course Registration</h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <table class="table bg-white">
        <thead><tr><th>Code</th><th>Title</th><th>Capacity</th><th></th></tr></thead>
        <tbody>
        <?php while ($c = $courses->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['code']) ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= (int)$c['capacity'] ?></td>
                <td>
                    <?php if (in_array((int)$c['id'], $myEnrolments)): ?>
                        <span class="badge bg-success">Registered</span>
                    <?php else: ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                            <button class="btn btn-sm btn-outline-primary">Register</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
