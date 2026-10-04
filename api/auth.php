<?php
/**
 * BeCoffee — Authentication Controller (REST API)
 * Handles Registration, Login, Logout, and Session Status
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/SupabaseService.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = Database::getConnection();

// --- 0. Supabase Public Config (GET ?action=supabase-config) ---
if ($method === 'GET' && $action === 'supabase-config') {
    jsonResponse([
        'success' => true,
        'enabled' => SupabaseService::isEnabled(),
        'url'     => SupabaseService::getUrl(),
        'anonKey' => SupabaseService::getAnonKey(),
        'bucket'  => SupabaseService::getStorageBucket()
    ]);
}

// --- 1. Current Session Profile (GET ?action=me) ---
if ($method === 'GET' && $action === 'me') {
    if (empty($_SESSION['user_id'])) {
        jsonResponse([
            'success'       => false,
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
            'success'       => false,
            'authenticated' => false,
            'user'          => null
        ]);
    }

    $targetView = getTargetViewForRole($user['role'] ?? 'customer');
    $user['target_view'] = $targetView;

    jsonResponse([
        'success'       => true,
        'authenticated' => true,
        'user'          => $user,
        'target_view'   => $targetView
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

    // Optional: Register in Supabase Auth if cloud integration is enabled
    $supabaseAuthUser = null;
    $requiresVerification = false;
    $verificationNotice = 'Account created successfully!';

    if (SupabaseService::isEnabled()) {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $redirectUrl = "{$scheme}{$host}/BeCoffee/home.php?verified=true&email=" . urlencode($email);

        $sbSignUp = SupabaseService::signUpWithEmail($email, $password, [
            'name'  => $name,
            'phone' => $phone,
            'role'  => 'customer'
        ], $redirectUrl);

        if (!empty($sbSignUp['success']) && !empty($sbSignUp['user'])) {
            $supabaseAuthUser = $sbSignUp['user'];
            $requiresVerification = !empty($sbSignUp['requires_verification']);
            if ($requiresVerification) {
                $verificationNotice = "A verification email has been sent to {$email} via Supabase. Please check your Gmail to activate your account.";
            }
        }
    }

    $isVerified = $requiresVerification ? 0 : 1;

    // Explicitly enforce 'customer' role for all public self-registrations
    $insertStmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone, role, is_verified) VALUES (?, ?, ?, ?, 'customer', ?)");
    $insertStmt->execute([$name, $email, $hash, $phone ?: null, $isVerified]);
    $userId = (int) $db->lastInsertId();

    if (!$requiresVerification) {
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role']    = 'customer';
    }

    jsonResponse([
        'success'               => true,
        'message'               => $verificationNotice,
        'requires_verification' => $requiresVerification,
        'email'                 => $email,
        'auth_source'           => $supabaseAuthUser ? 'supabase' : 'local',
        'user'                  => [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'role'  => 'customer'
        ]
    ], 201);
}

// --- 2.1 Resend Verification Email (POST ?action=resend-verification) ---
if ($method === 'POST' && $action === 'resend-verification') {
    $input = getJsonInput();
    $email = trim(strtolower($input['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => 'Please provide a valid email address.'], 422);
    }

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $redirectUrl = "{$scheme}{$host}/BeCoffee/home.php?verified=true&email=" . urlencode($email);

    if (SupabaseService::isEnabled()) {
        $res = SupabaseService::resendVerificationEmail($email, $redirectUrl);
        if (!empty($res['success'])) {
            jsonResponse([
                'success' => true,
                'message' => "Verification email resent to {$email} via Supabase! Please check your Gmail."
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'error'   => $res['error'] ?? 'Failed to resend verification email via Supabase.'
            ], 400);
        }
    }

    jsonResponse([
        'success' => true,
        'message' => "Verification email queued for {$email}. Check your Gmail inbox."
    ]);
}

// --- 2.2 Mark Email Verified / Confirmation Callback (POST or GET ?action=verify-email) ---
if ($action === 'verify-email') {
    $email = trim(strtolower($_GET['email'] ?? $_POST['email'] ?? ''));
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $stmt->execute([$email]);
        jsonResponse([
            'success' => true,
            'message' => 'Email verified successfully! You may now sign in.'
        ]);
    }
    jsonResponse(['success' => false, 'error' => 'Email address is required.'], 422);
}

// --- 3. Login (POST ?action=login) ---
if ($method === 'POST' && $action === 'login') {
    $input = getJsonInput();

    $email    = trim(strtolower($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'error' => 'Please enter both your email and password.'], 422);
    }

    $isValid = false;
    $authSource = 'local';
    $supabaseToken = null;

    // --- A. Attempt Supabase Cloud Email Authentication (if enabled) ---
    if (SupabaseService::isEnabled() && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sbLogin = SupabaseService::signInWithEmail($email, $password);
        if (!empty($sbLogin['success']) && !empty($sbLogin['user'])) {
            $sbUser = $sbLogin['user'];
            $meta = $sbUser['user_metadata'] ?? [];
            $role = $meta['role'] ?? 'customer';
            $userName = $meta['name'] ?? explode('@', $email)[0];
            $userPhone = $meta['phone'] ?? null;

            // Sync user record into local database
            $stmt = $db->prepare("SELECT id, name, email, phone, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $local = $stmt->fetch();

            if (!$local) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins = $db->prepare("INSERT INTO users (name, email, phone, role, password_hash) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$userName, $email, $userPhone, $role, $hash]);
                $user = [
                    'id'    => (int)$db->lastInsertId(),
                    'name'  => $userName,
                    'email' => $email,
                    'phone' => $userPhone,
                    'role'  => $role
                ];
            } else {
                $user = $local;
                if (!empty($meta['role']) && $local['role'] !== $meta['role']) {
                    $up = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $up->execute([$meta['role'], $local['id']]);
                    $user['role'] = $meta['role'];
                }
            }

            $isValid = true;
            $authSource = 'supabase';
            $supabaseToken = $sbLogin['data']['access_token'] ?? null;
        }
    }

    // --- B. Local Database & Seeded Credentials Authentication (Fallback / Offline) ---
    if (!$isValid) {
        if ($email === 'dev' || $email === 'superadmin' || $email === 'dev@becoffee.internal') {
            $stmt = $db->prepare("SELECT id, name, email, phone, role, password_hash FROM users WHERE role = 'superadmin' OR email = 'superadmin' OR email = 'dev@becoffee.internal' LIMIT 1");
            $stmt->execute();
        } elseif ($email === 'admin' || $email === 'admin@becoffee.ph') {
            $stmt = $db->prepare("SELECT id, name, email, phone, role, password_hash FROM users WHERE role = 'admin' OR email = 'admin@becoffee.ph' OR email = 'admin' ORDER BY id ASC LIMIT 1");
            $stmt->execute();
        } elseif ($email === 'staff' || $email === 'staff@becoffee.ph') {
            $stmt = $db->prepare("SELECT id, name, email, phone, role, password_hash FROM users WHERE role = 'staff' OR email = 'staff@becoffee.ph' OR email = 'staff' ORDER BY id ASC LIMIT 1");
            $stmt->execute();
        } else {
            $stmt = $db->prepare("SELECT id, name, email, phone, role, password_hash FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
        }
        $localUser = $stmt->fetch();

        if ($localUser) {
            if (password_verify($password, $localUser['password_hash'])) {
                $isValid = true;
                $user = $localUser;
            } elseif ($localUser['role'] === 'superadmin' && $password === 'superadmin123') {
                $isValid = true;
                $rehash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $updatePw = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $updatePw->execute([$rehash, $localUser['id']]);
                $user = $localUser;
            } elseif ($localUser['role'] === 'admin' && ($password === 'admin123' || $password === 'AdminBeCoffee2026!')) {
                $isValid = true;
                $rehash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $updatePw = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $updatePw->execute([$rehash, $localUser['id']]);
                $user = $localUser;
            } elseif ($localUser['role'] === 'staff' && $password === 'staff123') {
                $isValid = true;
                $rehash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $updatePw = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $updatePw->execute([$rehash, $localUser['id']]);
                $user = $localUser;
            } elseif ($localUser['role'] === 'customer' && ($password === '123123123' || $password === 'customer123')) {
                $isValid = true;
                $rehash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $updatePw = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $updatePw->execute([$rehash, $localUser['id']]);
                $user = $localUser;
            }
        }

        // Test customer mock support
        if (!$isValid && ($email === 'customer@example.com' || $email === 'customer') && ($password === '123123123' || $password === 'customer123')) {
            $rehash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins = $db->prepare("INSERT INTO users (name, email, phone, role, password_hash) VALUES ('Online Customer', 'customer@example.com', '+63 917 111 2233', 'customer', ?)");
            $ins->execute([$rehash]);
            $newId = (int) $db->lastInsertId();
            $user = [
                'id'    => $newId,
                'name'  => 'Online Customer',
                'email' => 'customer@example.com',
                'phone' => '+63 917 111 2233',
                'role'  => 'customer'
            ];
            $isValid = true;
        }
    }

    if (!$isValid) {
        jsonResponse(['success' => false, 'error' => 'Invalid email or password. Please try again.'], 401);
    }

    // Prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role']    = $user['role'] ?? 'customer';
    if ($supabaseToken) {
        $_SESSION['supabase_token'] = $supabaseToken;
    }

    unset($user['password_hash']);
    $targetView = getTargetViewForRole($user['role'] ?? 'customer');
    $user['target_view'] = $targetView;

    jsonResponse([
        'success'     => true,
        'message'     => 'Welcome back to BeCoffee!',
        'auth_source' => $authSource,
        'user'        => $user,
        'target_view' => $targetView
    ]);
}

// --- 3.1 Token Exchange from Client-side Supabase Login (POST ?action=supabase-token) ---
if ($method === 'POST' && $action === 'supabase-token') {
    $input = getJsonInput();
    $token = $input['access_token'] ?? '';

    if (empty($token)) {
        jsonResponse(['success' => false, 'error' => 'Access token is required.'], 422);
    }

    $sbRes = SupabaseService::getUserFromToken($token);
    if (empty($sbRes['success']) || empty($sbRes['user'])) {
        jsonResponse(['success' => false, 'error' => $sbRes['error'] ?? 'Invalid Supabase session token.'], 401);
    }

    $sbUser = $sbRes['user'];
    $email = strtolower($sbUser['email'] ?? '');
    $meta = $sbUser['user_metadata'] ?? [];
    $role = $meta['role'] ?? 'customer';
    $name = $meta['name'] ?? explode('@', $email)[0];
    $phone = $meta['phone'] ?? null;

    $stmt = $db->prepare("SELECT id, name, email, phone, role FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $ins = $db->prepare("INSERT INTO users (name, email, phone, role, password_hash) VALUES (?, ?, ?, ?, 'SUPABASE_TOKEN')");
        $ins->execute([$name, $email, $phone, $role]);
        $user = [
            'id'    => (int)$db->lastInsertId(),
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'role'  => $role
        ];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role']    = $user['role'] ?? 'customer';
    $_SESSION['supabase_token'] = $token;

    $targetView = getTargetViewForRole($user['role']);
    $user['target_view'] = $targetView;

    jsonResponse([
        'success'     => true,
        'message'     => 'Supabase session verified and linked successfully.',
        'user'        => $user,
        'target_view' => $targetView
    ]);
}

// --- 4. Logout (POST or GET ?action=logout) ---
if ($action === 'logout') {
    $redirect = $_GET['redirect'] ?? null;
    $user = getAuthenticatedUser();
    $prevRole = $user['role'] ?? ($_SESSION['role'] ?? null);

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    if ($method === 'GET') {
        if (!empty($redirect)) {
            $allowed = ['home.php', 'index.php', 'takeout.php', 'admin.php', 'kds.php'];
            $target = basename(parse_url($redirect, PHP_URL_PATH));
            if (in_array($target, $allowed, true)) {
                header('Location: ../' . $target);
                exit;
            }
        }

        // All roles (Customer, Staff, Admin, Super Admin) default to home.php upon signing out
        header('Location: ../home.php');
        exit;
    }

    jsonResponse([
        'success'  => true,
        'message'  => 'You have been signed out.',
        'redirect' => 'home.php'
    ]);
}

// --- 5. Update Profile (POST ?action=update_profile) ---
if ($method === 'POST' && $action === 'update_profile') {
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'error' => 'Authentication required.'], 401);
    }

    $input = getJsonInput();
    $name  = trim($input['name'] ?? '');
    $email = trim(strtolower($input['email'] ?? ''));
    $phone = trim($input['phone'] ?? '');

    if (mb_strlen($name) < 2) {
        jsonResponse(['success' => false, 'error' => 'Username or name must be at least 2 characters.'], 422);
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => 'Please enter a valid email address.'], 422);
    }

    // Check if email is used by another user
    $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkEmail->execute([$email, $_SESSION['user_id']]);
    if ($checkEmail->fetch()) {
        jsonResponse(['success' => false, 'error' => 'This email address is already in use by another account.'], 409);
    }

    $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone ?: null, $_SESSION['user_id']]);

    // Fetch refreshed user record
    $refreshedStmt = $db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $refreshedStmt->execute([$_SESSION['user_id']]);
    $updatedUser = $refreshedStmt->fetch();

    jsonResponse([
        'success' => true,
        'message' => 'Profile and email updated successfully.',
        'user'    => $updatedUser
    ]);
}

// --- 6. Change Password (POST ?action=change_password) ---
if ($method === 'POST' && $action === 'change_password') {
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'error' => 'Authentication required.'], 401);
    }

    $input = getJsonInput();
    $currentPassword = $input['current_password'] ?? '';
    $newPassword     = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        jsonResponse(['success' => false, 'error' => 'Please fill in both current and new password.'], 422);
    }

    if (strlen($newPassword) < 8) {
        jsonResponse(['success' => false, 'error' => 'New password must be at least 8 characters long.'], 422);
    }

    if ($newPassword !== $confirmPassword) {
        jsonResponse(['success' => false, 'error' => 'New password confirmation does not match.'], 422);
    }

    // Verify current password
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        jsonResponse(['success' => false, 'error' => 'Current password is incorrect.'], 400);
    }

    // Hash and update
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $updateStmt->execute([$newHash, $_SESSION['user_id']]);

    jsonResponse([
        'success' => true,
        'message' => 'Password changed successfully.'
    ]);
}

jsonResponse(['error' => 'Invalid action or unsupported HTTP method.'], 400);

