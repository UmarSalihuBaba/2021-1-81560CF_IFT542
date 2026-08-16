<?php
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
start_secure_session();
$conn = get_db_connection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $matric = trim($_POST['matric_no'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
        log_event('registration_rejected_validation', 'invalid_email_format');
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
        log_event('registration_rejected_validation', 'password_too_short', $email);
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, matric_no) VALUES (?, ?, ?, "student", ?)'
        );
        $stmt->bind_param('ssss', $fullName, $email, $hash, $matric);
        if ($stmt->execute()) {
            $success = 'Account created. You can now log in.';
        } else {
            $error = 'Could not create account (email may already be registered).';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:460px; margin-top:60px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Create Student Account</h4>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Full Name</label><input name="full_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Matric No.</label><input name="matric_no" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                <button class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 small"><a href="login.php">Already have an account? Log in</a></p>
        </div>
    </div>
</div>
</body>
</html>
