<?php
/**
 * BeCoffee — Operational Settings Controller (REST API)
 * Public GET for customer storefront & QR validation.
 * Mutation endpoints strictly restricted to Admin & SuperAdmin roles.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'get';
$db = Database::getConnection();

// --- 1. GET Settings (Public / Storefront) ---
if ($method === 'GET' && ($action === 'get' || $action === 'status')) {
    $qrEnabled = getSystemSetting('table_qr_ordering_enabled', '1') === '1';
    $tableCount = (int) getSystemSetting('table_count', '10');

    jsonResponse([
        'success'  => true,
        'settings' => [
            'table_qr_ordering_enabled' => $qrEnabled,
            'table_count'               => $tableCount
        ]
    ]);
}

// --- 2. POST Toggle Table QR Ordering (Admin & SuperAdmin only) ---
if ($method === 'POST' && ($action === 'toggle_qr' || $action === 'toggle_table_qr')) {
    $user = requireRole(['admin', 'superadmin']);

    $currentVal = getSystemSetting('table_qr_ordering_enabled', '1');
    $newVal = ($currentVal === '1') ? '0' : '1';

    $input = getJsonInput();
    if (isset($input['enabled'])) {
        $newVal = $input['enabled'] ? '1' : '0';
    }

    setSystemSetting('table_qr_ordering_enabled', $newVal);

    logAuditEvent(
        'TOGGLE_TABLE_QR_ORDERING',
        "Table QR Ordering switched to: " . ($newVal === '1' ? 'ENABLED' : 'PAUSED'),
        $user['id'],
        $user['email'],
        $user['role']
    );

    jsonResponse([
        'success'                   => true,
        'table_qr_ordering_enabled' => ($newVal === '1'),
        'message'                   => 'Table QR ordering is now ' . ($newVal === '1' ? 'ACTIVE' : 'PAUSED') . '.'
    ]);
}

// --- 3. POST Update Table Count (Admin & SuperAdmin only) ---
if ($method === 'POST' && $action === 'update_table_count') {
    $user = requireRole(['admin', 'superadmin']);
    $input = getJsonInput();
    $count = max(1, min(50, (int) ($input['table_count'] ?? 10)));

    setSystemSetting('table_count', (string) $count);

    logAuditEvent(
        'UPDATE_TABLE_COUNT',
        "Active cafe dining tables set to: {$count}",
        $user['id'],
        $user['email'],
        $user['role']
    );

    jsonResponse([
        'success'     => true,
        'table_count' => $count,
        'message'     => "Table count updated to {$count}."
    ]);
}

jsonResponse(['success' => false, 'error' => 'Invalid action or method.'], 400);
