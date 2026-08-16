<?php
require_once __DIR__ . '/db.php';

const MAX_FAILED_ATTEMPTS = 5;      // per-account lockout threshold
const LOCKOUT_MINUTES = 15;         // account lockout duration
const IP_RATE_LIMIT = 10;           // max attempts per IP...
const IP_RATE_WINDOW_MINUTES = 5;   // ...within this many minutes

/**
 * IP-based rate limiting (Task 2 additional control #1).
 * Independent of account lockout, so it also slows down attackers who
 * spray many different email addresses from one IP.
 */
function is_ip_rate_limited(mysqli $conn, string $ip): bool {
    $stmt = $conn->prepare(
        'SELECT COUNT(*) AS c FROM login_attempts
         WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)'
    );
    $stmt->bind_param('si', $ip, $window);
    $window = IP_RATE_WINDOW_MINUTES;
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $count >= IP_RATE_LIMIT;
}

function record_login_attempt(mysqli $conn, string $ip): void {
    $stmt = $conn->prepare('INSERT INTO login_attempts (ip_address) VALUES (?)');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Account-level lockout (Task 2 additional control #2).
 * Returns true if the account is currently locked.
 */
function is_account_locked(array $row): bool {
    return !empty($row['locked_until']) && strtotime($row['locked_until']) > time();
}

function register_failed_attempt(mysqli $conn, int $userId, int $currentFailedAttempts): void {
    $newCount = $currentFailedAttempts + 1;
    if ($newCount >= MAX_FAILED_ATTEMPTS) {
        $stmt = $conn->prepare(
            'UPDATE users SET failed_attempts = ?, locked_until = (NOW() + INTERVAL ? MINUTE) WHERE id = ?'
        );
        $lockoutMinutes = LOCKOUT_MINUTES;
        $stmt->bind_param('iii', $newCount, $lockoutMinutes, $userId);
    } else {
        $stmt = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
        $stmt->bind_param('ii', $newCount, $userId);
    }
    $stmt->execute();
    $stmt->close();
}

function reset_failed_attempts(mysqli $conn, int $userId): void {
    $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}
