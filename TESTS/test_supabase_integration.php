<?php
/**
 * BeCoffee Test Suite: Supabase Cloud Integration (Storage & Email Auth)
 * Tests service health, storage fallback, auth integration, and API endpoints.
 */

TestFramework::registerSuite('Supabase Cloud Integration & Storage', function(SuiteReporter $reporter) {
    $client = new TestClient();

    // 1. Supabase Management & Diagnostics Endpoint
    $res = $client->get('api/supabase.php?action=status');
    $reporter->assertEquals('api/supabase.php?action=status returns 200 OK', 200, $res['status']);
    $reporter->assertTrue('Status response indicates success', !empty($res['json']['success']));
    $reporter->assertEquals('Default bucket is becoffee-storage', 'becoffee-storage', $res['json']['storage_bucket'] ?? '');

    // 2. Public Client Config Endpoint
    $cfg = $client->get('api/supabase.php?action=config');
    $reporter->assertEquals('api/supabase.php?action=config returns 200 OK', 200, $cfg['status']);
    $reporter->assertTrue('Config response has enabled flag', isset($cfg['json']['enabled']));
    $reporter->assertEquals('Config returns correct storage bucket', 'becoffee-storage', $cfg['json']['storage_bucket'] ?? '');

    // 3. Setup Guide Endpoint
    $guide = $client->get('api/supabase.php?action=setup-guide');
    $reporter->assertEquals('api/supabase.php?action=setup-guide returns 200 OK', 200, $guide['status']);
    $reporter->assertTrue('Setup guide provides step-by-step instructions', count($guide['json']['instructions'] ?? []) >= 5);

    // 4. Auth Endpoint Public Config
    $authCfg = $client->get('api/auth.php?action=supabase-config');
    $reporter->assertEquals('api/auth.php?action=supabase-config returns 200 OK', 200, $authCfg['status']);
    $reporter->assertTrue('Auth config reports status', isset($authCfg['json']['enabled']));

    // 5. Client Token Exchange Validation
    $tokenRes = $client->post('api/auth.php?action=supabase-token', ['access_token' => 'invalid_test_jwt']);
    $reporter->assertTrue('Invalid Supabase token is rejected with 401 or 422', in_array($tokenRes['status'], [401, 422]));

    // 6. SupabaseService PHP Class Verification
    require_once dirname(__DIR__) . '/api/SupabaseService.php';
    $reporter->assertTrue('SupabaseService class is loaded', class_exists('SupabaseService'));
    $reporter->assertEquals('SupabaseService default bucket is becoffee-storage', 'becoffee-storage', SupabaseService::getStorageBucket());
    $reporter->assert('SupabaseService getPublicUrl computes correct CDN format', strpos(SupabaseService::getPublicUrl('menu/test.webp'), 'storage/v1/object/public/becoffee-storage/menu/test.webp') !== false);

    // 7. Supabase Email Verification Endpoints
    $resendRes = $client->post('api/auth.php?action=resend-verification', ['email' => 'maria.tester@gmail.com']);
    $reporter->assertEquals('api/auth.php?action=resend-verification returns 200 OK', 200, $resendRes['status']);
    $reporter->assertTrue('Resend response confirms queued/sent status', !empty($resendRes['json']['success']));

    $verifyRes = $client->get('api/auth.php?action=verify-email', ['email' => 'customer@example.com']);
    $reporter->assertEquals('api/auth.php?action=verify-email returns 200 OK', 200, $verifyRes['status']);
    $reporter->assertTrue('Verify response marks email verified', !empty($verifyRes['json']['success']));
});
