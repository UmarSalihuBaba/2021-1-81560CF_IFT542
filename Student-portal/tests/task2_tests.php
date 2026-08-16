<?php
/**
 * Run from the command line on the machine hosting the app:
 *  php tests/task2_tests.php
 */

require_once __DIR__ . '/../config.php';

$baseUrl = 'http://localhost/studentreg';
$pass = 0;
$fail = 0;

function post(string $url, array $fields): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . '/task2_cookies_' . uniqid() . '.txt',
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $response];
}

function check(string $label, bool $condition, int &$pass, int &$fail): void {
    if ($condition) {
        echo "[PASS] $label\n";
        $pass++;
    } else {
        echo "[FAIL] $label\n";
        $fail++;
    }
}

echo "=== Task 2 Authentication Test Results ===\n\n";

// --- Test 1: valid login succeeds (expect redirect to dashboard.php) ---
$r = post("$baseUrl/login.php", [
    'email' => 'amina.bello@example.test',
    'password' => 'Password@123',
]);
check(
    'Valid credentials redirect to dashboard.php (302/Location header)',
    $r['status'] === 302 && str_contains($r['body'], 'Location: dashboard.php'),
    $pass, $fail
);

// --- Test 2: invalid credentials are rejected with a generic message ---
$r = post("$baseUrl/login.php", [
    'email' => 'amina.bello@example.test',
    'password' => 'WrongPassword!',
]);
check(
    'Wrong password returns generic "Invalid email or password." (200, no redirect)',
    $r['status'] === 200 && str_contains($r['body'], 'Invalid email or password.'),
    $pass, $fail
);

// --- Test 3: unknown account gives the SAME generic message (no enumeration) ---
$r = post("$baseUrl/login.php", [
    'email' => 'no-such-user@example.test',
    'password' => 'WhateverPassword1',
]);
check(
    'Unknown account returns identical generic message (account enumeration closed)',
    $r['status'] === 200 && str_contains($r['body'], 'Invalid email or password.'),
    $pass, $fail
);

// --- Test 4: classic SQLi payload does not change query meaning ---
$r = post("$baseUrl/login.php", [
    'email' => "' OR '1'='1",
    'password' => 'anything',
]);
check(
    "SQLi payload ' OR '1'='1 is rejected as invalid email format, not logged in",
    $r['status'] === 200 && str_contains($r['body'], 'Invalid email or password.') && !str_contains($r['body'], 'Location: dashboard.php'),
    $pass, $fail
);

$r2 = post("$baseUrl/login.php", [
    'email' => "nonexistent@example.test' OR '1'='1",
    'password' => 'anything',
]);
check(
    'SQLi payload embedded in a syntactically-valid-looking email still fails to authenticate',
    $r2['status'] === 200 && !str_contains($r2['body'], 'Location: dashboard.php'),
    $pass, $fail
);

// --- Test 5: stored passwords are hashed, not plaintext ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $conn->query("SELECT email, password_hash FROM users LIMIT 5");
$allHashed = true;
while ($row = $res->fetch_assoc()) {
    // bcrypt hashes start with $2y$ or $2b$ and are 60 chars long
    if (!preg_match('/^\$2[aby]\$\d{2}\$.{53}$/', $row['password_hash'])) {
        $allHashed = false;
    }
}
check('All sampled password_hash values match the bcrypt format (no plaintext)', $allHashed, $pass, $fail);
$conn->close();

// --- Test 6: account lockout engages after repeated failures ---
$testEmail = 'john.okafor@example.test';
for ($i = 0; $i < 5; $i++) {
    post("$baseUrl/login.php", ['email' => $testEmail, 'password' => 'wrong-' . $i]);
}
$r = post("$baseUrl/login.php", ['email' => $testEmail, 'password' => 'Password@123']);
check(
    'Account is locked after 5 consecutive failures, even with the correct password',
    str_contains($r['body'], 'temporarily locked'),
    $pass, $fail
);

echo "\n=== Summary: $pass passed, $fail failed ===\n";
