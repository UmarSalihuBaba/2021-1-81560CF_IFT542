<?php
/**
 * login_vulnerable.php — BASELINE (INTENTIONALLY VULNERABLE)
 * This is the "before" version used in Task 2 of the IFT542 assignment.
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
    // Raw user input is concatenated directly into the SQL string for BOTH
    // fields. An attacker-supplied email like:  ' OR '1'='1' -- | ' OR '1'='1' #
    // closes the string early, forces the condition true for every row,
    // and comments out the password check entirely.
    $sql = "SELECT id, full_name, email, role
            FROM users
            WHERE email = '$email' AND password_hash = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows >= 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user'] = [
            'id' => $row['id'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'],
        ];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
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
            <form method="post" action="login_vulnerable.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 text-muted small">
                Try email: <code>' OR '1'='1' -- </code> and any password, to demonstrate the bypass.
                (Legitimate logins with real passwords will NOT work on this page — see comment at top of file.)
            </p>
        </div>
    </div>
</div>
</body>
</html>
