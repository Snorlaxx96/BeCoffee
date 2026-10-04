<?php
/**
 * BeCoffee — Menu Item Picture Upload Handler
 * Accepts multipart image uploads, validates types, and saves to images/menu/
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SupabaseService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['success' => false, 'error' => 'No image file uploaded or upload error occurred.'], 400);
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes, true)) {
    jsonResponse(['success' => false, 'error' => 'Invalid file format. Allowed formats: JPG, PNG, WEBP.'], 422);
}

$ext = match ($mimeType) {
    'image/jpeg', 'image/jpg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    default => 'jpg'
};

$cleanBase = preg_replace('/[^a-zA-Z0-9_\-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
$cleanBase = substr($cleanBase, 0, 24) ?: 'item';
$filename = $cleanBase . '_' . time() . '.' . $ext;

// --- 1. Supabase Cloud Storage (Primary when configured) ---
if (SupabaseService::isEnabled()) {
    $remotePath = 'menu/' . $filename;
    $uploadRes = SupabaseService::uploadFile($remotePath, $file['tmp_name'], $mimeType, true);

    if (!empty($uploadRes['success'])) {
        jsonResponse([
            'success'   => true,
            'message'   => 'Image uploaded successfully to Supabase Storage.',
            'image_url' => $uploadRes['public_url'],
            'storage'   => 'supabase',
            'bucket'    => $uploadRes['bucket'] ?? 'becoffee-storage',
            'path'      => $remotePath
        ]);
    }

    // Log fallback note if Supabase storage upload hit an error
    error_log("Supabase storage upload error: " . ($uploadRes['error'] ?? 'Unknown') . ". Falling back to local storage.");
}

// --- 2. Local Disk Storage Fallback ---
$uploadDir = dirname(__DIR__) . '/images/menu';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    jsonResponse(['success' => false, 'error' => 'Failed to save image to server.'], 500);
}

$webPath = 'images/menu/' . $filename;

jsonResponse([
    'success'   => true,
    'message'   => 'Image uploaded successfully.',
    'image_url' => $webPath,
    'storage'   => 'local'
]);
