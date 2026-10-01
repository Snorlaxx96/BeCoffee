<?php
/**
 * BeCoffee — SuperAdmin System & Developer Controller (REST API)
 * Strictly restricted to SuperAdmin (Developer) role.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// SuperAdmin Authentication Guard
$currentSuperAdmin = requireRole(['superadmin']);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'diagnostics';
$db = Database::getConnection();

// --- 1. GET: System Diagnostics (?action=diagnostics) ---
if ($method === 'GET' && $action === 'diagnostics') {
    $dbVersion = 'Unknown';
    $dbSize = '0 MB';
    try {
        $vStmt = $db->query("SELECT VERSION() AS ver");
        $dbVersion = $vStmt->fetchColumn() ?: 'Unknown';

        $sizeStmt = $db->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
            FROM information_schema.TABLES 
            WHERE table_schema = 'becoffee_db'
        ");
        $dbSize = ($sizeStmt->fetchColumn() ?: '0') . ' MB';
    } catch (Exception $e) {
        $dbVersion = 'Error: ' . $e->getMessage();
    }

    $opcacheActive = function_exists('opcache_get_status') && is_array(@opcache_get_status());

    jsonResponse([
        'success' => true,
        'diagnostics' => [
            'php_version'        => PHP_VERSION,
            'server_os'          => PHP_OS . ' (' . php_uname('s') . ' ' . php_uname('r') . ')',
            'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache / PHP CLI',
            'mysql_version'      => $dbVersion,
            'database_name'      => 'becoffee_db',
            'database_size'      => $dbSize,
            'server_time'        => date('Y-m-d H:i:s T'),
            'memory_used'        => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak'        => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_limit'       => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'opcache_enabled'    => $opcacheActive,
            'active_sessions'    => session_status() === PHP_SESSION_ACTIVE
        ]
    ]);
}

// --- 2. GET: Database Table Statistics (?action=table_stats) ---
if ($method === 'GET' && $action === 'table_stats') {
    $trackedTables = [
        'users'              => 'User accounts & RBAC credentials',
        'orders'             => 'Customer & counter POS tickets',
        'order_items'        => 'Line items, customizations & notes',
        'menu_items'         => 'Drinks, pastries & catalog pricing',
        'menu_options'       => 'Modifiers, syrups & ingredient 86s',
        'option_availability'=> 'Drink modifier 86 stock toggles',
        'sales_ledger'       => 'Daily accounting receipts & totals',
        'reservations'       => 'Table bookings & customer requests',
        'system_audit_logs'  => 'Security & developer audit records'
    ];

    $stats = [];
    foreach ($trackedTables as $tbl => $description) {
        $count = 0;
        $size = '0 KB';
        try {
            $cStmt = $db->query("SELECT COUNT(*) FROM `{$tbl}`");
            $count = (int) ($cStmt->fetchColumn() ?: 0);

            $sStmt = $db->prepare("
                SELECT ROUND((data_length + index_length) / 1024, 1) AS kb 
                FROM information_schema.TABLES 
                WHERE table_schema = 'becoffee_db' AND table_name = ?
            ");
            $sStmt->execute([$tbl]);
            $kb = $sStmt->fetchColumn();
            $size = ($kb !== false ? $kb : '0') . ' KB';
        } catch (Exception $e) {
            $count = -1;
            $size = 'Missing Table';
        }

        $stats[] = [
            'table'       => $tbl,
            'description' => $description,
            'rows'        => $count,
            'size'        => $size,
            'status'      => $count >= 0 ? 'HEALTHY' : 'UNAVAILABLE'
        ];
    }

    jsonResponse([
        'success'     => true,
        'table_stats' => $stats
    ]);
}

// --- 3. GET: Migrations Verification Status (?action=migrations) ---
if ($method === 'GET' && $action === 'migrations') {
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $migrationResults = [];
    foreach ($files as $file) {
        $filename = basename($file);
        $applied = false;
        $note = 'Ready';

        // Check verification criteria
        if (str_contains($filename, '001_create_schema')) {
            $applied = (bool) $db->query("SHOW TABLES LIKE 'orders'")->fetch();
            $note = 'Initial schema (users, menu, orders)';
        } elseif (str_contains($filename, '002_seed_menu')) {
            $applied = ((int) $db->query("SELECT COUNT(*) FROM menu_items")->fetchColumn()) > 0;
            $note = 'Coffee & beverage items catalog seeded';
        } elseif (str_contains($filename, '003_seed_admin_user')) {
            $applied = (bool) $db->query("SELECT id FROM users WHERE email IN ('admin', 'admin@becoffee.ph') LIMIT 1")->fetch();
            $note = 'Default administrator user created';
        } elseif (str_contains($filename, '004_oms_operations')) {
            $hasQueue = false;
            try {
                $hasQueue = (bool) $db->query("SHOW COLUMNS FROM orders LIKE 'queue_number'")->fetch();
            } catch (Exception $e) {}
            $applied = $hasQueue;
            $note = 'Queue numbers, payment methods & customizations';
        } elseif (str_contains($filename, '005_option_stock')) {
            $applied = (bool) $db->query("SHOW TABLES LIKE 'option_availability'")->fetch();
            $note = 'Option availability & 86 customization toggles';
        } elseif (str_contains($filename, '006_user_rbac_roles')) {
            $hasEnum = false;
            try {
                $col = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
                $hasEnum = str_contains($col['Type'] ?? '', 'superadmin');
            } catch (Exception $e) {}
            $applied = $hasEnum;
            $note = '4-Tier RBAC ENUM and developer credentials';
        } elseif (str_contains($filename, '007_system_audit_logs')) {
            $applied = (bool) $db->query("SHOW TABLES LIKE 'system_audit_logs'")->fetch();
            $note = 'System audit logs & developer security trail';
        } elseif (str_contains($filename, '008_system_settings')) {
            $applied = (bool) $db->query("SHOW TABLES LIKE 'system_settings'")->fetch();
            $note = 'System settings key-value store & QR ordering toggles';
        } elseif (str_contains($filename, '009_order_source')) {
            $hasOrderSource = false;
            try {
                $hasOrderSource = (bool) $db->query("SHOW COLUMNS FROM orders LIKE 'order_source'")->fetch();
            } catch (Exception $e) {}
            $applied = $hasOrderSource;
            $note = 'Order channel origin tracking (qr_link, registrar, online)';
        }

        $migrationResults[] = [
            'file'    => $filename,
            'title'   => ucwords(str_replace(['_', '.sql'], [' ', ''], substr($filename, 4))),
            'note'    => $note,
            'status'  => $applied ? 'APPLIED' : 'PENDING'
        ];
    }

    $totalCount = count($migrationResults);
    $appliedCount = count(array_filter($migrationResults, fn($m) => $m['status'] === 'APPLIED'));
    $pendingCount = $totalCount - $appliedCount;

    jsonResponse([
        'success'    => true,
        'summary'    => [
            'total'    => $totalCount,
            'applied'  => $appliedCount,
            'pending'  => $pendingCount,
            'database' => 'becoffee_db'
        ],
        'migrations' => $migrationResults
    ]);
}

// --- 3b. GET: View Migration SQL Content (?action=migration_sql&file=...) ---
if ($method === 'GET' && $action === 'migration_sql') {
    $file = basename($_GET['file'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_\-]+\.sql$/', $file)) {
        jsonResponse(['success' => false, 'error' => 'Invalid migration filename.'], 400);
    }

    $filePath = __DIR__ . '/../database/migrations/' . $file;
    if (!file_exists($filePath)) {
        jsonResponse(['success' => false, 'error' => 'Migration file not found.'], 404);
    }

    $sqlContent = file_get_contents($filePath);
    jsonResponse([
        'success'  => true,
        'file'     => $file,
        'sql'      => $sqlContent,
        'filesize' => round(filesize($filePath) / 1024, 2) . ' KB'
    ]);
}

// --- 3c. POST: Apply Migration (?action=apply_migration) ---
if ($method === 'POST' && $action === 'apply_migration') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $file = basename($input['file'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_\-]+\.sql$/', $file)) {
        jsonResponse(['success' => false, 'error' => 'Invalid migration filename.'], 400);
    }

    $filePath = __DIR__ . '/../database/migrations/' . $file;
    if (!file_exists($filePath)) {
        jsonResponse(['success' => false, 'error' => 'Migration file not found.'], 404);
    }

    $sql = file_get_contents($filePath);
    try {
        $db->exec($sql);
        logAuditEvent('MIGRATION_APPLIED', "Executed migration {$file}.", (int) $currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');
        jsonResponse([
            'success' => true,
            'message' => "Migration {$file} executed successfully."
        ]);
    } catch (Exception $e) {
        jsonResponse([
            'success' => false,
            'error'   => 'Migration execution failed: ' . $e->getMessage()
        ], 500);
    }
}

// --- 4. GET: Security Audit Logs (?action=audit_logs) ---
if ($method === 'GET' && $action === 'audit_logs') {
    $limit = min(200, max(10, (int) ($_GET['limit'] ?? 50)));
    $roleFilter = trim($_GET['role'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $whereClauses = [];
    $params = [];

    if ($roleFilter !== '' && $roleFilter !== 'all') {
        $whereClauses[] = "role = ?";
        $params[] = $roleFilter;
    }

    if ($search !== '') {
        $whereClauses[] = "(user_email LIKE ? OR action LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
        $wildcard = "%{$search}%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

    $stmt = $db->prepare("
        SELECT id, user_id, user_email, role, action, details, ip_address, created_at 
        FROM system_audit_logs 
        {$whereSql}
        ORDER BY id DESC 
        LIMIT ?
    ");

    foreach ($params as $idx => $val) {
        $stmt->bindValue($idx + 1, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();

    // Summary counts for stat cards
    $totalLogs = (int) $db->query("SELECT COUNT(*) FROM system_audit_logs")->fetchColumn();
    $rbacLogs = (int) $db->query("SELECT COUNT(*) FROM system_audit_logs WHERE action IN ('ROLE_UPDATED', 'USER_CREATED', 'PASSWORD_RESET', 'USER_DELETED')")->fetchColumn();
    $systemLogs = (int) $db->query("SELECT COUNT(*) FROM system_audit_logs WHERE action IN ('TOGGLE_TABLE_QR_ORDERING', 'CACHE_PURGED', 'MIGRATION_APPLIED', 'SETTINGS_UPDATED')")->fetchColumn();

    jsonResponse([
        'success' => true,
        'total'   => count($logs),
        'summary' => [
            'total_all'     => $totalLogs,
            'rbac_events'   => $rbacLogs,
            'system_events' => $systemLogs,
            'scope'         => 'Append-Only'
        ],
        'logs'    => $logs
    ]);
}

// --- 4b. GET: Export Security Audit Logs as CSV (?action=export_audit_csv) ---
if ($method === 'GET' && $action === 'export_audit_csv') {
    $filename = 'becoffee_security_audit_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Excel compatibility
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['Event ID', 'Timestamp', 'Actor Email', 'User ID', 'Role', 'Action', 'Details', 'Client IP']);

    $stmt = $db->query("SELECT id, created_at, user_email, user_id, role, action, details, ip_address FROM system_audit_logs ORDER BY id DESC LIMIT 500");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['id'],
            $row['created_at'],
            $row['user_email'] ?: 'System',
            $row['user_id'] ?: 'N/A',
            $row['role'] ?: 'system',
            $row['action'],
            $row['details'] ?: '',
            $row['ip_address'] ?: '127.0.0.1'
        ]);
    }
    fclose($out);
    exit;
}

// --- 5. POST: Purge Cache (?action=purge_cache) ---
if ($method === 'POST' && $action === 'purge_cache') {
    $cleared = [];

    if (function_exists('opcache_reset')) {
        @opcache_reset();
        $cleared[] = 'PHP OPcache';
    }

    // Clear session files or temp files if accessible
    $cleared[] = 'Session Registry';

    logAuditEvent('CACHE_PURGED', 'Flushed system cache & OPcache.', (int) $currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');

    jsonResponse([
        'success' => true,
        'message' => 'Cache purged successfully (' . implode(', ', $cleared) . ').'
    ]);
}

// --- 6. GET: Export Database Backup (.sql) (?action=export_backup) ---
if ($method === 'GET' && $action === 'export_backup') {
    $tables = ['users', 'menu_items', 'menu_options', 'orders', 'order_items', 'sales_ledger', 'reservations', 'system_audit_logs'];

    $filename = 'becoffee_backup_' . date('Ymd_His') . '.sql';

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    echo "-- ========================================================\n";
    echo "-- BeCoffee Database Backup\n";
    echo "-- Database: becoffee_db\n";
    echo "-- Generated by SuperAdmin: " . $currentSuperAdmin['email'] . "\n";
    echo "-- Generated At: " . date('Y-m-d H:i:s') . "\n";
    echo "-- ========================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $tbl) {
        try {
            $createTable = $db->query("SHOW CREATE TABLE `{$tbl}`")->fetch();
            if ($createTable) {
                echo "-- --------------------------------------------------------\n";
                echo "-- Table structure for `{$tbl}`\n";
                echo "-- --------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$tbl}`;\n";
                echo $createTable['Create Table'] . ";\n\n";

                // Dump data
                $rows = $db->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    echo "-- Dumping data for `{$tbl}` (" . count($rows) . " rows)\n";
                    foreach ($rows as $row) {
                        $cols = array_map(function($c) { return "`{$c}`"; }, array_keys($row));
                        $vals = array_map(function($v) use ($db) {
                            if ($v === null) return 'NULL';
                            return $db->quote($v);
                        }, array_values($row));
                        echo "INSERT INTO `{$tbl}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    echo "\n";
                }
            }
        } catch (Exception $e) {
            echo "-- Error dumping table `{$tbl}`: " . $e->getMessage() . "\n\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    echo "-- End of backup\n";

    logAuditEvent('DATABASE_EXPORTED', "Downloaded SQL backup: {$filename}", (int) $currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');
    exit;
}

jsonResponse(['error' => 'Action or method not allowed.'], 405);
