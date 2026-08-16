<?php
/**
 * profile_vulnerable.php — BASELINE (kept for before/after evidence)
 * Demonstrates TWO issues fixed in the secure version:
 *   1. Elevation of privilege: trusts $_POST['user_id'] instead of the session.
 *   2. Stored XSS: the bio field is echoed back WITHOUT output encoding, so a
 *      script tag saved once is executed on every future page view.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$conn = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = $_POST['user_id'] ?? $user['id']; // VULNERABLE: client-controlled
    $fullName = trim($_POST['full_name'] ?? '');
    $bio = $_POST['bio'] ?? '';

    $stmt = $conn->prepare('UPDATE users SET full_name = ?, bio = ? WHERE id = ?');
    $stmt->bind_param('ssi', $fullName, $bio, $targetId);
    $stmt->execute();
    $stmt->close();
    $message = 'Profile updated.';

    if ((int)$targetId === (int)$user['id']) {
        $_SESSION['user']['full_name'] = $fullName;
        $user = $_SESSION['user'];
    }
}

// re-fetch bio for display
$stmt = $conn->prepare('SELECT bio FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$bio = $stmt->get_result()->fetch_assoc()['bio'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile (baseline)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:500px;">
    <a href="dashboard.php">&larr; Back</a>
    <h3 class="mt-2">My Profile <span class="badge bg-danger">baseline / vulnerable</span></h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control"><?= htmlspecialchars($bio) ?></textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
    <hr>
    <h6>Public bio preview (VULNERABLE — raw echo, no encoding):</h6>
    <!-- VULNERABLE: bio is echoed without htmlspecialchars(), enabling stored XSS -->
    <div class="border p-2 bg-white"><?= $bio ?></div>
</div>
</body>
</html>
