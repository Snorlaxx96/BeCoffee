<?php
/**
 * BeCoffee — Authentication Controller (REST API)
 * Handles Registration, Login, Logout, and Session Status
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = Database::getConnection();

// --- 1. Current Session Profile (GET ?action=me) ---
if ($method === 'GET' && $action === 'me') {
    if (empty($_SESSION['user_id'])) {
        jsonResponse([
            'authenticated' => false,
            'user'          => null
        ]);
    }

    $stmt = $db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        jsonResponse([
            'authenticated' => false,
            'user'          => null
        ]);
    }

    jsonResponse([
        'authenticated' => true,
        'user'          => $user
    ]);
}

// --- 2. Registration (POST ?action=register) ---
if ($method === 'POST' && $action === 'register') {
    $input = getJsonInput();

    $name     = trim($input['name'] ?? '');
    $email    = trim(strtolower($input['email'] ?? ''));
    $phone    = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';

    // Validation
    if (mb_strlen($name) < 2) {
        jsonResponse(['success' => false, 'error' => 'Please provide your full name (minimum 2 characters).'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => 'Please enter a valid email address.'], 422);
    }

    if (strlen($password) < 8) {
        jsonResponse(['success' => false, 'error' => 'Password must be at least 8 characters long.'], 422);
    }

    // Check email uniqueness
    $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'An account with this email address already exists.'], 409);
    }

    // Hash password with standard bcrypt
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $insertStmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$name, $email, $hash, $phone ?: null]);
    $userId = (int) $db->lastInsertId();

    // Prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    jsonResponse([
        'success' => true,
        'message' => 'Account created successfully!',
        'user'    => [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'role'  => 'customer'
        ]
    ], 201);
}

// --- 3. Login (POST ?action=login) ---
if ($method === 'POST' && $action === 'login') {
    $input = getJsonInput();

    $email    = trim(strtolower($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'error' => 'Please enter both your email and password.'], 422);
    }

    $stmt = $db->prepare("SELECT id, name, email, phone, role, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid email or password. Please try again.'], 401);
    }

    // Prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    unset($user['password_hash']);

    jsonResponse([
        'success' => true,
        'message' => 'Welcome back to BeCoffee!',
        'user'    => $user
    ]);
}

// --- 4. Logout (POST ?action=logout) ---
if ($method === 'POST' && $action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    jsonResponse([
        'success' => true,
        'message' => 'You have been signed out.'
    ]);
}

jsonResponse(['error' => 'Invalid action or unsupported HTTP method.'], 400);
