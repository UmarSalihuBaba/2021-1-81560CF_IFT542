<?php
/**
 * login.php — BASELINE (INTENTIONALLY VULNERABLE)
 * This is the "before" version used in Task 2 of the IFT542 assignment.
 * It demonstrates the unsafe pattern (raw string concatenation into SQL)
 * that must be located, explained, and fixed with parameterized queries.
 * DO NOT deploy this file as-is. See login_secure.php for the corrected version.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
start_secure_session();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = get_db_connection();

    // --- VULNERABLE PATTERN (Task 2 target) ---
    // Raw user input is concatenated directly into the SQL string.
    // An attacker-supplied email like:  ' OR '1'='1
    // changes the query's logic and can bypass authentication entirely.
    $sql = "SELECT id, full_name, email, password_hash, role
            FROM users
            WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Also vulnerable: no password hashing check shown here in baseline —
        // some early prototypes compare plaintext directly. Kept for the
        // Task 2 "before" evidence; real check should use password_verify().
        if ($password === $row['password_hash'] || password_verify($password, $row['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role'],
            ];
            header('Location: dashboard.php');
            exit;
        }
    }
    // Verbose error — reveals whether the account exists (Task 1, T5)
    $error = $result && $result->num_rows === 1 ? 'Incorrect password.' : 'No account found with that email.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login (baseline) — Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:420px; margin-top:80px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Student Login <span class="badge bg-danger">baseline / vulnerable</span></h4>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" required>
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
