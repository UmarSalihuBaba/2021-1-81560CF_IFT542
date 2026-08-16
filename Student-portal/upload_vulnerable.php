<?php
/**
 * upload.php — BASELINE
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$conn = get_db_connection();
$message = '';

$uploadDir = __DIR__ . '/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['document']['name'])) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $safeName = 'u' . $user['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $safeName);
            $message = 'File uploaded: ' . $safeName;
        } else {
            $message = 'File type not allowed.';
        }
    } elseif (!empty($_POST['import_url'])) {
        // VULNERABLE: fetches any attacker-supplied URL server-side, with no
        // allowlist and no block on internal/loopback/metadata addresses.
        $url = $_POST['import_url'];
        $data = @file_get_contents($url);
        if ($data !== false) {
            $safeName = 'u' . $user['id'] . '_' . time() . '.bin';
            file_put_contents($uploadDir . $safeName, $data);
            $message = 'Imported from URL as ' . $safeName;
        } else {
            $message = 'Could not fetch URL.';
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
    <h3 class="mt-2">Upload Document <span class="badge bg-danger">import-by-URL is baseline/vulnerable</span></h3>
    <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4">
        <label class="form-label">Upload a file (PDF/JPG/PNG)</label>
        <input type="file" name="document" class="form-control mb-2">
        <button class="btn btn-primary">Upload</button>
    </form>

    <form method="post">
        <label class="form-label">Or import a document from a URL</label>
        <input type="text" name="import_url" class="form-control mb-2" placeholder="https://example.test/file.pdf">
        <button class="btn btn-outline-secondary">Import</button>
    </form>
</div>
</body>
</html>
