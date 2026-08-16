<?php
/**
 * upload.php — SECURE (Task 3 "after" version, SSRF fix)
 * Compare to upload_vulnerable.php for the "before" evidence.
 *
 * Fix: the URL-import feature now validates the destination through
 * includes/url_safety.php before ever calling file_get_contents(). This
 * blocks disallowed schemes, hosts outside the allowlist, and any hostname
 * that resolves to a loopback/private/link-local/metadata address.
 */
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/url_safety.php';
$user = require_login();
$conn = get_db_connection();
$message = '';

$uploadDir = __DIR__ . '/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf($user['email']);

    if (!empty($_FILES['document']['name'])) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $safeName = 'u' . $user['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $safeName);
            log_event('document_uploaded', $safeName, $user['email']);
            $message = 'File uploaded: ' . $safeName;
        } else {
            $message = 'File type not allowed.';
            log_event('upload_rejected_validation', "ext=$ext", $user['email']);
        }
    } elseif (!empty($_POST['import_url'])) {
        $url = trim($_POST['import_url']);

        // --- FIX: allowlist + private-range check before any request is made ---
        if (!is_url_safe($url, $user['email'])) {
            $message = 'That URL is not permitted. Only approved document hosts can be imported.';
        } else {
            $context = stream_context_create(['http' => ['timeout' => 5], 'https' => ['timeout' => 5]]);
            $data = @file_get_contents($url, false, $context);
            if ($data !== false) {
                $safeName = 'u' . $user['id'] . '_' . time() . '.bin';
                file_put_contents($uploadDir . $safeName, $data);
                log_event('document_imported', "url_host=" . parse_url($url, PHP_URL_HOST), $user['email']);
                $message = 'Imported as ' . $safeName;
            } else {
                $message = 'Could not fetch the document from that URL.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:500px;">
    <a href="dashboard.php">&larr; Back</a>
    <h3 class="mt-2">Upload Document</h3>
    <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label class="form-label">Upload a file (PDF/JPG/PNG)</label>
        <input type="file" name="document" class="form-control mb-2">
        <button class="btn btn-primary">Upload</button>
    </form>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label class="form-label">Or import a document from an approved host</label>
        <input type="text" name="import_url" class="form-control mb-2" placeholder="https://files.example-university.test/...">
        <div class="form-text mb-2">Only pre-approved document hosts are allowed (SSRF protection).</div>
        <button class="btn btn-outline-secondary">Import</button>
    </form>
</div>
</body>
</html>
