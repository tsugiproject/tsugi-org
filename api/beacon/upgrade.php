<?php

/**
 * POST /api/beacon/upgrade
 *
 * One unauthenticated JSON body from admin/upgrade.php. 2xx only after the
 * row is stored. The raw body is not written to the log.
 */

require_once dirname(__DIR__, 2) . '/lib/beacon_store.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$raw = file_get_contents('php://input');
if ( $raw === false ) $raw = '';

try {
    list($status, $payload) = beacon_handle($method, $raw);
} catch ( Throwable $e ) {
    error_log('tsugi beacon store failed');
    $status = 500;
    $payload = array('error' => 'store failed');
}

http_response_code($status);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo json_encode($payload);
exit;
