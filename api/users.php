<?php
/**
 * BeCoffee — User & RBAC Management Controller (REST API)
 * Exclusive to SuperAdmin (Developer)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// SuperAdmin Only
$currentSuperAdmin = requireRole(['superadmin']);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = Database::getConnection();

// --- 1. GET: List All Users ---
if ($method === 'GET') {
    $roleFilter = trim($_GET['role'] ?? '');
    $allowedRoles = ['superadmin', 'admin', 'staff', 'customer'];

    if ($roleFilter !== '' && in_array($roleFilter, $allowedRoles, true)) {
        $stmt = $db->prepare("
            SELECT id, name, email, phone, role, created_at, updated_at 
            FROM users 
            WHERE role = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$roleFilter]);
    } else {
        $stmt = $db->query("
            SELECT id, name, email, phone, role, created_at, updated_at 
            FROM users 
            ORDER BY FIELD(role, 'superadmin', 'admin', 'staff', 'customer'), id ASC
        ");
    }

    $users = $stmt->fetchAll();

    // Summary counts per role
    $countStmt = $db->query("
        SELECT role, COUNT(*) AS count 
        FROM users 
        GROUP BY role
    ");
    $counts = [];
    foreach ($countStmt->fetchAll() as $c) {
        $counts[$c['role']] = (int) $c['count'];
    }

    jsonResponse([
        'success'      => true,
        'total'        => count($users),
        'role_counts'  => $counts,
        'users'        => $users
    ]);
}

// --- 2. POST: Create User with Explicit Role (?action=create) ---
if ($method === 'POST' && ($action === 'create' || $action === '')) {
    $input = getJsonInput();

    $name     = trim($input['name'] ?? '');
    $email    = trim(strtolower($input['email'] ?? ''));
    $phone    = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $role     = trim($input['role'] ?? 'staff');

    $allowedRoles = ['superadmin', 'admin', 'staff', 'customer'];
    if (!in_array($role, $allowedRoles, true)) {
        jsonResponse(['success' => false, 'error' => "Invalid role. Supported: " . implode(', ', $allowedRoles)], 422);
    }

    if (mb_strlen($name) < 2) {
        jsonResponse(['success' => false, 'error' => 'Full name must be at least 2 characters.'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => 'Please provide a valid email address.'], 422);
    }

    if (strlen($password) < 8) {
        jsonResponse(['success' => false, 'error' => 'Password must be at least 8 characters long.'], 422);
    }

    $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'An account with this email already exists.'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $insertStmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->execute([$name, $email, $hash, $phone ?: null, $role]);
    $newId = (int) $db->lastInsertId();

    logAuditEvent('USER_CREATED', "Created user '{$name}' ({$email}) with role '{$role}'.", (int)$currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');

    jsonResponse([
        'success' => true,
        'message' => "User '{$name}' created successfully with role '{$role}'.",
        'user'    => [
            'id'    => $newId,
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'role'  => $role
        ]
    ], 201);
}

// --- 3. PATCH: Update User Role (?action=update_role) ---
if (($method === 'PATCH' || $method === 'POST') && $action === 'update_role') {
    $input = getJsonInput();
    $targetId = (int) ($input['user_id'] ?? 0);
    $newRole  = trim($input['role'] ?? '');

    $allowedRoles = ['superadmin', 'admin', 'staff', 'customer'];
    if (!in_array($newRole, $allowedRoles, true)) {
        jsonResponse(['success' => false, 'error' => "Invalid role. Supported: " . implode(', ', $allowedRoles)], 422);
    }

    $checkStmt = $db->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $checkStmt->execute([$targetId]);
    $target = $checkStmt->fetch();

    if (!$target) {
        jsonResponse(['success' => false, 'error' => 'Target user not found.'], 404);
    }

    // Safety: Prevent removing last superadmin
    if ($target['role'] === 'superadmin' && $newRole !== 'superadmin') {
        $saCountStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'superadmin'");
        $saCount = (int) ($saCountStmt->fetchColumn() ?: 0);
        if ($saCount <= 1) {
            jsonResponse(['success' => false, 'error' => 'Cannot demote the last remaining SuperAdmin.'], 400);
        }
    }

    $updStmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $updStmt->execute([$newRole, $targetId]);

    logAuditEvent('ROLE_UPDATED', "Changed role of '{$target['name']}' (#{$targetId}) from '{$target['role']}' to '{$newRole}'.", (int)$currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');

    jsonResponse([
        'success'  => true,
        'message'  => "User '{$target['name']}' role updated to '{$newRole}'.",
        'user_id'  => $targetId,
        'new_role' => $newRole
    ]);
}

// --- 4. PATCH: Reset User Password (?action=reset_password) ---
if (($method === 'PATCH' || $method === 'POST') && $action === 'reset_password') {
    $input = getJsonInput();
    $targetId    = (int) ($input['user_id'] ?? 0);
    $newPassword = $input['new_password'] ?? '';

    if (strlen($newPassword) < 8) {
        jsonResponse(['success' => false, 'error' => 'New password must be at least 8 characters long.'], 422);
    }

    $checkStmt = $db->prepare("SELECT id, name FROM users WHERE id = ?");
    $checkStmt->execute([$targetId]);
    $target = $checkStmt->fetch();

    if (!$target) {
        jsonResponse(['success' => false, 'error' => 'Target user not found.'], 404);
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $updStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $updStmt->execute([$newHash, $targetId]);

    logAuditEvent('PASSWORD_RESET', "Reset password for '{$target['name']}' (#{$targetId}).", (int)$currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');

    jsonResponse([
        'success' => true,
        'message' => "Password for '{$target['name']}' has been reset successfully."
    ]);
}

// --- 5. DELETE: Delete User ---
if ($method === 'DELETE') {
    $input = getJsonInput();
    $targetId = (int) ($_GET['id'] ?? $input['user_id'] ?? 0);

    if ($targetId <= 0) {
        jsonResponse(['success' => false, 'error' => 'Valid user ID required for deletion.'], 400);
    }

    if ($targetId === (int) $currentSuperAdmin['id']) {
        jsonResponse(['success' => false, 'error' => 'Cannot delete your own authenticated superadmin account.'], 400);
    }

    $checkStmt = $db->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $checkStmt->execute([$targetId]);
    $target = $checkStmt->fetch();

    if (!$target) {
        jsonResponse(['success' => false, 'error' => 'User not found.'], 404);
    }

    // Safety: Prevent deleting the last superadmin
    if ($target['role'] === 'superadmin') {
        $saCountStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'superadmin'");
        $saCount = (int) ($saCountStmt->fetchColumn() ?: 0);
        if ($saCount <= 1) {
            jsonResponse(['success' => false, 'error' => 'Cannot delete the last remaining SuperAdmin.'], 400);
        }
    }

    $delStmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $delStmt->execute([$targetId]);

    logAuditEvent('USER_DELETED', "Deleted user '{$target['name']}' (#{$targetId}) with role '{$target['role']}'.", (int)$currentSuperAdmin['id'], $currentSuperAdmin['email'], 'superadmin');

    jsonResponse([
        'success' => true,
        'message' => "User '{$target['name']}' (ID: {$targetId}) has been deleted."
    ]);
}

jsonResponse(['error' => 'Method or action not allowed.'], 405);
