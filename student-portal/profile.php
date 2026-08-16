<?php
/**
 * profile.php — SECURE (Task 3 "after" version)
 * Compare to profile_vulnerable.php for the "before" evidence.
 *
 * Fixes applied:
 *   1. Access control: the target row is ALWAYS $user['id'] from the
 *      server-side session — the client-supplied user_id field is ignored
 *      for authorization purposes (closes the elevation-of-privilege flaw).
 *   2. Stored XSS: the bio field is encoded with htmlspecialchars() on every
 *      output, using ENT_QUOTES so it is safe inside both HTML text and
 *      attribute contexts. Combined with the restrictive CSP in
 *      includes/security_headers.php (no inline/third-party scripts allowed).
 *   3. CSRF: the update form now carries and validates a per-session token.
 */
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$user = require_login();
$conn = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf($user['email']);

    // --- FIX: identity is derived ONLY from the session, never from POST ---
    $targetId = $user['id'];
    $fullName = trim($_POST['full_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $stmt = $conn->prepare('UPDATE users SET full_name = ?, bio = ? WHERE id = ?');
    $stmt->bind_param('ssi', $fullName, $bio, $targetId);
    $stmt->execute();
    $stmt->close();
    log_event('profile_updated', '', $user['email']);

    $_SESSION['user']['full_name'] = $fullName;
    $user = $_SESSION['user'];
    $message = 'Profile updated.';
}

$stmt = $conn->prepare('SELECT bio FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$bio = $stmt->get_result()->fetch_assoc()['bio'] ?? '';
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:500px;">
    <a href="dashboard.php">&larr; Back</a>
    <h3 class="mt-2">My Profile</h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control"><?= htmlspecialchars($bio, ENT_QUOTES) ?></textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
    <hr>
    <h6>Public bio preview (encoded output):</h6>
    <!-- FIX: contextual output encoding — any <script> a user typed is
         displayed as inert text, never executed -->
    <div class="border p-2 bg-white"><?= htmlspecialchars($bio, ENT_QUOTES) ?></div>
</div>
</body>
</html>
