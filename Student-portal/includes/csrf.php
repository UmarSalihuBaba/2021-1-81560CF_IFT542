<?php
require_once __DIR__ . '/auth.php';

/**
 * Returns the current CSRF token for this session, generating one if absent.
 * Include the returned value in a hidden form field named csrf_token.
 */
function csrf_token(): string {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies a submitted token against the session token using a
 * constant-time comparison
 */
function verify_csrf_token(?string $submitted): bool {
    start_secure_session();
    if (empty($_SESSION['csrf_token']) || empty($submitted)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

/**
 * Convenience guard: verifies the token from $_POST['csrf_token'] and logs +
 * aborts with 403 if it is missing or wrong.
 */
function require_csrf(string $actorEmail = ''): void {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        log_event('csrf_validation_failed', 'missing_or_invalid_token', $actorEmail ?: null);
        http_response_code(403);
        die('Request rejected: invalid or missing security token. Please reload the page and try again.');
    }
}
