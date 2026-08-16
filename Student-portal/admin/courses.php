<?php
require_once __DIR__ . '/../includes/security_headers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
$user = require_admin();
$conn = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_POST['title'], $_POST['capacity'])) {
    require_csrf($user['email']);
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $capacity = (int)$_POST['capacity'];
    $stmt = $conn->prepare('INSERT INTO courses (code, title, capacity) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $code, $title, $capacity);
    $stmt->execute();
    $stmt->close();
    log_event('course_created', "code=$code", $user['email']);
    $message = 'Course added.';
}

$courses = $conn->query('SELECT id, code, title, capacity FROM courses ORDER BY code');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="index.php">&larr; Back</a>
    <h3 class="mt-2">Manage Courses</h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" class="row g-2 mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="col-auto"><input name="code" class="form-control" placeholder="Code" required></div>
        <div class="col-auto"><input name="title" class="form-control" placeholder="Title" required></div>
        <div class="col-auto"><input name="capacity" type="number" class="form-control" placeholder="Capacity" required></div>
        <div class="col-auto"><button class="btn btn-primary">Add Course</button></div>
    </form>
    <table class="table bg-white">
        <thead><tr><th>Code</th><th>Title</th><th>Capacity</th></tr></thead>
        <tbody>
        <?php while ($c = $courses->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['code']) ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= (int)$c['capacity'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
