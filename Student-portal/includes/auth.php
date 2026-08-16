<?php
require_once __DIR__ . '/db.php';

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax', // tightened to Strict on the CSRF-protected forms in Task 3
        ]);
        session_start();
    }
}

function current_user(): ?array {
    start_secure_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: /studentreg/login.php');
        exit;
    }
    return $user;
}

function require_admin(): array {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        log_event('authorization_denied', 'admin_area_attempted', $user['email']);
        http_response_code(403);
        die('Forbidden: admin access required.');
    }
    return $user;
}

function log_event(string $action, string $details = '', ?string $actorEmail = null): void {
    $conn = get_db_connection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare(
        'INSERT INTO audit_log (actor_email, action, details, ip_address) VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('ssss', $actorEmail, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
