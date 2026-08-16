<?php
/**
 * login.php — SECURE (Task 2 "after" version)
 *
 * Fixes applied vs. the baseline (see login_vulnerable.php for the "before"):
 *   1. Parameterized query (mysqli prepared statement) replaces string
 *      concatenation — user input is bound as DATA, never parsed as SQL.
 *   2. Password checked only with password_verify() against a bcrypt hash —
 *      no plaintext comparison path.
 *   3. Server-side email format validation before any DB query runs.
 *   4. Generic error message regardless of whether the email exists.
 *   5. Session ID regenerated on successful login (prevents session fixation).
 *   6. Account lockout after repeated failures (includes/login_security.php).
 *   7. IP-based rate limiting, independent of account lockout.
 *   8. Errors logged server-side only; nothing DB-related shown to the browser.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/login_security.php';
start_secure_session();

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $conn = get_db_connection();

    // --- Input validation ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 1) {
        $error = 'Invalid email or password.';
    }
    // --- IP rate limiting (control #1) ---
    elseif (is_ip_rate_limited($conn, $ip)) {
        $error = 'Too many login attempts from this location. Please try again later.';
        log_event('login_rate_limited', '', $email);
    } else {
        record_login_attempt($conn, $ip);

        // --- FIX: parameterized query, no string concatenation ---
        $stmt = $conn->prepare(
            'SELECT id, full_name, email, password_hash, role, failed_attempts, locked_until
             FROM users WHERE email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && is_account_locked($row)) {
            $error = 'This account is temporarily locked due to repeated failed attempts. Try again later.';
            log_event('login_blocked_locked_account', '', $email);
        }
        // --- FIX: verify hash with password_verify(), never plaintext compare ---
        elseif ($row && password_verify($password, $row['password_hash'])) {
            reset_failed_attempts($conn, (int)$row['id']);

            // --- Control: regenerate session ID on privilege change ---
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role'],
            ];
            log_event('login_success', '', $email);
            header('Location: dashboard.php');
            exit;
        } else {
            if ($row) {
                register_failed_attempt($conn, (int)$row['id'], (int)$row['failed_attempts']);
            }
            log_event('login_failed', '', $email);
            // --- FIX: identical generic message whether or not the account exists ---
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:420px; margin-top:80px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Student Login</h4>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 text-muted small">Test account: amina.bello@example.test / Password@123</p>
        </div>
    </div>
</div>
</body>
</html>
