<?php
/**
 * BeCoffee — Global Application & Environment Configuration
 */

// 1. Configure Default Timezone to Philippine Standard Time (UTC+8)
date_default_timezone_set('Asia/Manila');

// 1. Load Environment Variables from .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Strip wrapping quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(dirname(__DIR__) . '/.env');

// Helper to get environment variable with fallback
function env($key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $val;
}

// Suppress PHP display errors to prevent polluting JSON API responses
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// CORS & Preflight Options handling
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
if ($origin !== '*') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Configure Secure Session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 3. Common JSON API Response Helpers
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// 4. RBAC Session & Authorization Helpers
function getAuthenticatedUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cachedUser = null;
    if ($cachedUser !== null && (int)$cachedUser['id'] === (int)$_SESSION['user_id']) {
        return $cachedUser;
    }

    if (!class_exists('Database')) {
        require_once __DIR__ . '/db.php';
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cachedUser = $stmt->fetch() ?: null;
    return $cachedUser;
}

function requireAuth(): array {
    $user = getAuthenticatedUser();
    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'Authentication required.'], 401);
    }
    return $user;
}

function requireRole(array $allowedRoles): array {
    $user = requireAuth();
    if (!in_array($user['role'], $allowedRoles, true)) {
        jsonResponse([
            'success' => false,
            'error'   => 'Forbidden. Insufficient permissions for role: ' . $user['role']
        ], 403);
    }
    return $user;
}

function hasRole(string $role): bool {
    $user = getAuthenticatedUser();
    return $user !== null && $user['role'] === $role;
}

function getTargetViewForRole(string $role): string {
    return match ($role) {
        'superadmin' => 'admin.php?view=users',
        'admin'      => 'admin.php',
        'staff'      => 'kds.php',
        'customer'   => 'index.php',
        default      => 'index.php'
    };
}

function logAuditEvent(string $action, string $details = '', ?int $userId = null, ?string $userEmail = null, ?string $role = null): void {
    try {
        if (!class_exists('Database')) {
            require_once __DIR__ . '/db.php';
        }
        $db = Database::getConnection();

        if ($userId === null) {
            $currentUser = getAuthenticatedUser();
            if ($currentUser) {
                $userId = (int) $currentUser['id'];
                $userEmail = $currentUser['email'] ?? null;
                $role = $currentUser['role'] ?? 'system';
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("
            INSERT INTO system_audit_logs (user_id, user_email, role, action, details, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $userEmail,
            $role ?: 'system',
            $action,
            $details,
            $ip
        ]);
    } catch (\Throwable $e) {
        // Silently catch to not disrupt caller workflow
    }
}



