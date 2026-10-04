<?php
/**
 * BeCoffee — Supabase Cloud Integration Service
 * 
 * Provides unified, enterprise-grade access to:
 * 1. Supabase Storage (Object Storage buckets for menu photos, receipts, and media)
 * 2. Supabase Auth (GoTrue REST API for email signup, login, JWT verification, and password recovery)
 * 3. Supabase Database REST / PostgreSQL integration helpers
 */

require_once __DIR__ . '/config.php';

class SupabaseService {
    private static ?string $url = null;
    private static ?string $anonKey = null;
    private static ?string $serviceRoleKey = null;
    private static ?string $storageBucket = null;

    /**
     * Initialize credentials from environment
     */
    private static function init(): void {
        if (self::$url === null) {
            $rawUrl = env('SUPABASE_URL', '');
            self::$url = !empty($rawUrl) ? rtrim($rawUrl, '/') : '';
            self::$anonKey = env('SUPABASE_ANON_KEY', '') ?: '';
            self::$serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY', '') ?: '';
            self::$storageBucket = env('SUPABASE_STORAGE_BUCKET', 'becoffee-storage');
        }
    }

    /**
     * Check if Supabase integration is active and properly configured
     */
    public static function isEnabled(): bool {
        self::init();
        return !empty(self::$url) && (!empty(self::$anonKey) || !empty(self::$serviceRoleKey));
    }

    public static function getUrl(): string {
        self::init();
        return self::$url ?: '';
    }

    public static function getAnonKey(): string {
        self::init();
        return self::$anonKey ?: '';
    }

    public static function getServiceRoleKey(): string {
        self::init();
        return self::$serviceRoleKey ?: '';
    }

    public static function getStorageBucket(): string {
        self::init();
        return self::$storageBucket ?: 'becoffee-storage';
    }

    /**
     * Determine best API key for requests (prefers service role for server mutations)
     */
    private static function getApiKey(bool $requireAdmin = false): string {
        self::init();
        if ($requireAdmin && !empty(self::$serviceRoleKey)) {
            return self::$serviceRoleKey;
        }
        return self::$serviceRoleKey ?: (self::$anonKey ?: '');
    }

    // =========================================================================
    // 1. SUPABASE STORAGE (DATA / FILE STORAGE)
    // =========================================================================

    /**
     * Upload a file or binary payload directly to Supabase Storage bucket
     *
     * @param string $remotePath Destination path inside bucket (e.g. "menu/americano_123.webp")
     * @param string $fileContentOrPath Binary file contents or path to local file
     * @param string $mimeType Valid MIME type (e.g. "image/webp")
     * @param bool   $isFilePath If true, $fileContentOrPath is treated as a local file path
     * @param string|null $bucket Optional bucket override (defaults to SUPABASE_STORAGE_BUCKET)
     * @return array ['success' => bool, 'public_url' => string, 'error' => ?string]
     */
    public static function uploadFile(
        string $remotePath,
        string $fileContentOrPath,
        string $mimeType = 'image/jpeg',
        bool $isFilePath = true,
        ?string $bucket = null
    ): array {
        if (!self::isEnabled()) {
            return [
                'success' => false,
                'error'   => 'Supabase is not configured (missing SUPABASE_URL or API keys).'
            ];
        }

        $bucket = $bucket ?: self::getStorageBucket();
        $remotePath = ltrim($remotePath, '/');

        if ($isFilePath) {
            if (!file_exists($fileContentOrPath)) {
                return ['success' => false, 'error' => "Source file not found at: {$fileContentOrPath}"];
            }
            $payload = file_get_contents($fileContentOrPath);
        } else {
            $payload = $fileContentOrPath;
        }

        $apiKey = self::getApiKey(true);
        $endpoint = self::$url . "/storage/v1/object/{$bucket}/{$remotePath}";

        $headers = [
            "Authorization: Bearer {$apiKey}",
            "apikey: {$apiKey}",
            "Content-Type: {$mimeType}",
            "x-upsert: true"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error'   => "Supabase Storage network error: {$curlError}"
            ];
        }

        $json = json_decode($response, true);

        // Supabase returns 200 on success
        if ($httpCode >= 200 && $httpCode < 300) {
            $publicUrl = self::getPublicUrl($remotePath, $bucket);
            return [
                'success'    => true,
                'public_url' => $publicUrl,
                'path'       => $remotePath,
                'bucket'     => $bucket,
                'response'   => $json
            ];
        }

        $errorMsg = $json['message'] ?? $json['error'] ?? "HTTP {$httpCode}: {$response}";
        return [
            'success' => false,
            'error'   => "Supabase Storage upload failed: {$errorMsg}",
            'code'    => $httpCode
        ];
    }

    /**
     * Compute the public CDN URL for a stored object
     */
    public static function getPublicUrl(string $remotePath, ?string $bucket = null): string {
        self::init();
        $bucket = $bucket ?: self::getStorageBucket();
        $remotePath = ltrim($remotePath, '/');
        return self::$url . "/storage/v1/object/public/{$bucket}/{$remotePath}";
    }

    /**
     * Delete an object from Supabase Storage
     */
    public static function deleteFile(string $remotePath, ?string $bucket = null): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase is not configured.'];
        }

        $bucket = $bucket ?: self::getStorageBucket();
        $remotePath = ltrim($remotePath, '/');
        $apiKey = self::getApiKey(true);
        $endpoint = self::$url . "/storage/v1/object/{$bucket}/{$remotePath}";

        $headers = [
            "Authorization: Bearer {$apiKey}",
            "apikey: {$apiKey}"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'status'  => $httpCode
        ];
    }

    // =========================================================================
    // 2. SUPABASE AUTH (EMAIL SIGN-IN & REGISTRATION)
    // =========================================================================

    /**
     * Authenticate a user with Email and Password using Supabase GoTrue
     *
     * @param string $email User email
     * @param string $password User plaintext password
     * @return array ['success' => bool, 'data' => ?array, 'error' => ?string]
     */
    public static function signInWithEmail(string $email, string $password): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase Auth not configured.'];
        }

        $apiKey = self::$anonKey ?: self::getApiKey();
        $endpoint = self::$url . "/auth/v1/token?grant_type=password";

        $payload = json_encode([
            'email'    => trim(strtolower($email)),
            'password' => $password
        ]);

        $headers = [
            "apikey: {$apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => "Network error connecting to Supabase Auth: {$curlError}"];
        }

        $json = json_decode($response, true);

        if ($httpCode === 200 && !empty($json['access_token'])) {
            return [
                'success' => true,
                'data'    => $json,
                'user'    => $json['user'] ?? []
            ];
        }

        $msg = $json['error_description'] ?? $json['msg'] ?? $json['message'] ?? 'Authentication failed.';
        return [
            'success' => false,
            'error'   => $msg,
            'code'    => $httpCode
        ];
    }

    /**
     * Register a new user with Email and Password in Supabase GoTrue
     * Sends a verification confirmation email to user's Gmail address when email confirmation is enabled in Supabase.
     *
     * @param string $email
     * @param string $password
     * @param array  $metadata Additional profile metadata (name, phone, role)
     * @param string|null $redirectTo URL the user lands on after clicking the confirmation link in their Gmail
     * @return array
     */
    public static function signUpWithEmail(string $email, string $password, array $metadata = [], ?string $redirectTo = null): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase Auth not configured.'];
        }

        $apiKey = self::$anonKey ?: self::getApiKey();
        $endpoint = self::$url . "/auth/v1/signup";
        if (!empty($redirectTo)) {
            $endpoint .= "?redirect_to=" . urlencode($redirectTo);
        }

        $body = [
            'email'    => trim(strtolower($email)),
            'password' => $password,
            'data'     => $metadata
        ];

        $headers = [
            "apikey: {$apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => "Supabase signup error: {$curlError}"];
        }

        $json = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            $user = $json['user'] ?? $json;
            // Detect if Supabase is awaiting email confirmation (session is null or confirmed_at is null)
            $needsConfirmation = empty($json['session']) || empty($user['confirmed_at']);

            return [
                'success'               => true,
                'data'                  => $json,
                'user'                  => $user,
                'requires_verification' => $needsConfirmation,
                'confirmation_sent_at'  => $user['confirmation_sent_at'] ?? null
            ];
        }

        $msg = $json['error_description'] ?? $json['msg'] ?? $json['message'] ?? 'Registration failed in Supabase.';
        return [
            'success' => false,
            'error'   => $msg,
            'code'    => $httpCode
        ];
    }

    /**
     * Resend verification confirmation email via Supabase GoTrue
     *
     * @param string $email
     * @param string|null $redirectTo
     * @return array
     */
    public static function resendVerificationEmail(string $email, ?string $redirectTo = null): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase Auth not configured.'];
        }

        $apiKey = self::$anonKey ?: self::getApiKey();
        $endpoint = self::$url . "/auth/v1/resend";
        if (!empty($redirectTo)) {
            $endpoint .= "?redirect_to=" . urlencode($redirectTo);
        }

        $body = [
            'type'  => 'signup',
            'email' => trim(strtolower($email))
        ];

        $headers = [
            "apikey: {$apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => "Network error: {$curlError}"];
        }

        $json = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'Verification email resent successfully.'
            ];
        }

        $msg = $json['error_description'] ?? $json['msg'] ?? $json['message'] ?? 'Failed to resend verification email.';
        return [
            'success' => false,
            'error'   => $msg,
            'code'    => $httpCode
        ];
    }

    /**
     * Verify a Supabase JWT Access Token and retrieve the associated user profile
     *
     * @param string $accessToken Bearer JWT from client
     * @return array
     */
    public static function getUserFromToken(string $accessToken): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase Auth not configured.'];
        }

        $apiKey = self::$anonKey ?: self::getApiKey();
        $endpoint = self::$url . "/auth/v1/user";

        $headers = [
            "apikey: {$apiKey}",
            "Authorization: Bearer {$accessToken}"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($httpCode === 200 && !empty($json['id'])) {
            return [
                'success' => true,
                'user'    => $json
            ];
        }

        return [
            'success' => false,
            'error'   => $json['message'] ?? 'Invalid or expired Supabase token.'
        ];
    }

    /**
     * Send password recovery email via Supabase Auth
     */
    public static function sendPasswordResetEmail(string $email, ?string $redirectTo = null): array {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'Supabase Auth not configured.'];
        }

        $apiKey = self::$anonKey ?: self::getApiKey();
        $endpoint = self::$url . "/auth/v1/recover";

        $body = ['email' => trim(strtolower($email))];
        if (!empty($redirectTo)) {
            $endpoint .= '?redirect_to=' . urlencode($redirectTo);
        }

        $headers = [
            "apikey: {$apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'status'  => $httpCode
        ];
    }
}
