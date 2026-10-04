<?php
/**
 * BeCoffee — Supabase Management & Health Endpoint
 * 
 * Provides runtime diagnostics, storage bucket health checks,
 * and client SDK configuration bootstrap for frontend applications.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SupabaseService.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'status';

// --- 1. Client Configuration Bootstrap (Public) ---
if ($action === 'config') {
    jsonResponse([
        'success'        => true,
        'enabled'        => SupabaseService::isEnabled(),
        'supabase_url'   => SupabaseService::getUrl(),
        'anon_key'       => SupabaseService::getAnonKey(),
        'storage_bucket' => SupabaseService::getStorageBucket()
    ]);
}

// --- 2. Runtime Status & Health Check ---
if ($action === 'status') {
    $enabled = SupabaseService::isEnabled();
    $url = SupabaseService::getUrl();
    $bucket = SupabaseService::getStorageBucket();
    $hasAnon = !empty(SupabaseService::getAnonKey());
    $hasServiceRole = !empty(SupabaseService::getServiceRoleKey());

    $statusReport = [
        'success'               => true,
        'configured'            => $enabled,
        'supabase_url'          => $url ? substr($url, 0, 32) . '...' : null,
        'storage_bucket'        => $bucket,
        'has_anon_key'          => $hasAnon,
        'has_service_role_key'  => $hasServiceRole,
        'features'              => [
            'email_auth'     => $enabled,
            'object_storage' => $enabled,
            'auto_fallback'  => true
        ]
    ];

    // If enabled, attempt quick ping to storage public root
    if ($enabled) {
        $pingUrl = "{$url}/storage/v1/bucket";
        $ch = curl_init($pingUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: " . SupabaseService::getAnonKey(),
            "Authorization: Bearer " . (SupabaseService::getServiceRoleKey() ?: SupabaseService::getAnonKey())
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $statusReport['storage_api_reachable'] = ($code >= 200 && $code < 400);
        $statusReport['storage_api_http_code'] = $code;
    }

    jsonResponse($statusReport);
}

// --- 3. Storage Setup Diagnostic Guide ---
if ($action === 'setup-guide') {
    jsonResponse([
        'success' => true,
        'bucket'  => SupabaseService::getStorageBucket(),
        'instructions' => [
            '1. Go to your Supabase Project Dashboard -> Storage -> Create New Bucket.',
            '2. Name the bucket: "' . SupabaseService::getStorageBucket() . '".',
            '3. Toggle "Public Bucket" to ON so menu images and receipts can be served publicly via CDN.',
            '4. Set "Allowed MIME types" to: image/jpeg, image/png, image/webp.',
            '5. Add your SUPABASE_URL and SUPABASE_ANON_KEY (and SUPABASE_SERVICE_ROLE_KEY) to your .env file.',
            '6. Run database/migrations/supabase_complete_schema.sql in the Supabase SQL Editor.'
        ]
    ]);
}

jsonResponse(['success' => false, 'error' => "Action '{$action}' not supported."], 400);
