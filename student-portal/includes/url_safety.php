<?php
/**
 * includes/url_safety.php — Task 3 SSRF hardening for the document-import feature.
 *
 * Defence approach: allowlist of permitted destination hosts, reject anything
 * else outright; additionally resolve the hostname and reject any address
 * that falls in a loopback, private, link-local, or cloud-metadata range,
 * even if the hostname itself looked allowed (defence against DNS rebinding).
 */

require_once __DIR__ . '/auth.php';

// Only these hosts may be fetched from. Add real, trusted document hosts here.
const IMPORT_HOST_ALLOWLIST = [
    'files.example-university.test',
    'documents.example-university.test',
];

function is_url_safe(string $url, string $actorEmail = ''): bool {
    $parts = parse_url($url);

    if (!$parts || empty($parts['host']) || empty($parts['scheme'])) {
        return false;
    }

    // Only plain HTTP(S) — no file://, gopher://, ftp://, etc.
    if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        log_event('ssrf_blocked', "disallowed_scheme={$parts['scheme']}", $actorEmail ?: null);
        return false;
    }

    $host = strtolower($parts['host']);

    // Allowlist check first — cheapest and clearest signal.
    if (!in_array($host, IMPORT_HOST_ALLOWLIST, true)) {
        log_event('ssrf_blocked', "host_not_in_allowlist host=$host", $actorEmail ?: null);
        return false;
    }

    // Resolve and re-check the actual IP address(es) to defend against
    // DNS rebinding (an allowlisted hostname later resolving to a private IP).
    $ips = gethostbynamel($host);
    if ($ips === false) {
        log_event('ssrf_blocked', "dns_resolution_failed host=$host", $actorEmail ?: null);
        return false;
    }

    foreach ($ips as $ip) {
        if (is_private_or_reserved_ip($ip)) {
            log_event('ssrf_blocked', "resolved_to_private_ip host=$host ip=$ip", $actorEmail ?: null);
            return false;
        }
    }

    return true;
}

function is_private_or_reserved_ip(string $ip): bool {
    // FILTER_FLAG_NO_PRIV_RANGE / NO_RES_RANGE reject RFC1918, loopback,
    // link-local (which covers the 169.254.169.254 cloud metadata address),
    // and other reserved ranges for both IPv4 and IPv6.
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}
