<?php
/**
 * BeCoffee — Reservations Controller (REST API)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

if ($method === 'POST') {
    $input = getJsonInput();

    $bookingType   = trim($input['booking_type'] ?? 'table');
    $locationCode  = trim($input['location_code'] ?? 'bgc');
    $guests        = max(1, (int) ($input['guests'] ?? 2));
    $preferredDate = trim($input['preferred_date'] ?? '');
    $preferredTime = trim($input['preferred_time'] ?? '');
    $customerName  = trim($input['customer_name'] ?? '');
    $customerPhone = trim($input['customer_phone'] ?? '');
    $notes         = trim($input['notes'] ?? '');

    if (empty($preferredDate) || empty($preferredTime) || empty($customerName) || empty($customerPhone)) {
        jsonResponse(['success' => false, 'error' => 'Please fill in all required reservation fields.'], 422);
    }

    $userId = $_SESSION['user_id'] ?? null;

    $stmt = $db->prepare("
        INSERT INTO reservations (user_id, booking_type, location_code, guests, preferred_date, preferred_time, customer_name, customer_phone, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");

    $stmt->execute([
        $userId,
        $bookingType,
        $locationCode,
        $guests,
        $preferredDate,
        $preferredTime,
        $customerName,
        $customerPhone,
        $notes ?: null
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Your reservation request has been confirmed! We will send a confirmation SMS to ' . htmlspecialchars($customerPhone) . '.'
    ], 201);
}

jsonResponse(['error' => 'Method not allowed.'], 405);
