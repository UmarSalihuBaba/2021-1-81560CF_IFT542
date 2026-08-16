<?php
// Restrictive Content Security Policy: only allow this origin plus the two
// CDNs actually used (Bootstrap CSS). No inline scripts, no third-party
// script origins, no framing by other sites (clickjacking defence).
$csp = "default-src 'self'; "
     . "style-src 'self' https://cdn.jsdelivr.net; "
     . "script-src 'self'; "
     . "img-src 'self' data:; "
     . "frame-ancestors 'none'; "
     . "base-uri 'self'; "
     . "form-action 'self';";

header("Content-Security-Policy: $csp");
header('X-Content-Type-Options: nosniff');      // stop MIME-sniffing based XSS
header('X-Frame-Options: DENY');                 // legacy clickjacking defence
header('Referrer-Policy: same-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Only send HSTS when actually served over HTTPS (sending it over plain HTTP
// on localhost would be a misconfiguration in itself).
if (!empty($_SERVER['HTTPS'])) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Remove/obscure the PHP version header if the SAPI allows it at runtime.
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
}
