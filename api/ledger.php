<?php
/**
 * BeCoffee — Owner Daily Revenue & Audit Ledger (REST API)
 * Aggregates financial totals, payment breakdowns, and completed order history
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
requireRole(['admin', 'superadmin']);

$db = Database::getConnection();

if ($method === 'GET') {
    $date = trim($_GET['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    // 1. Calculate Aggregate Financial KPIs
    $kpiStmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed_orders,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_orders,
            COUNT(CASE WHEN status = 'in_progress' THEN 1 END) AS in_progress_orders,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS cancelled_orders,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN grand_total ELSE 0 END), 0) AS total_revenue,
            COALESCE(SUM(CASE WHEN status = 'completed' AND payment_method = 'cash' THEN grand_total ELSE 0 END), 0) AS cash_revenue,
            COALESCE(SUM(CASE WHEN status = 'completed' AND payment_method = 'gcash' THEN grand_total ELSE 0 END), 0) AS gcash_revenue,
            COUNT(CASE WHEN status = 'completed' AND order_type = 'dine_in' THEN 1 END) AS dine_in_count,
            COUNT(CASE WHEN status = 'completed' AND order_type = 'take_out' THEN 1 END) AS take_out_count,
            COALESCE(ROUND(AVG(CASE WHEN status = 'completed' AND completed_at IS NOT NULL 
                THEN TIMESTAMPDIFF(SECOND, created_at, completed_at) / 60 ELSE NULL END), 1), 0) AS avg_prep_minutes
        FROM orders
        WHERE DATE(created_at) = ?
    ");
    $kpiStmt->execute([$date]);
    $kpi = $kpiStmt->fetch();

    // 2. Fetch Detailed Chronological Audit Log
    $orderStmt = $db->prepare("
        SELECT id, queue_number, order_reference, order_type, table_number, payment_method, payment_status,
               customer_name, customer_phone, subtotal, eco_fee, grand_total, status,
               qa_payment_verified, qa_customizations_followed, qa_packaging_secured,
               created_at, completed_at,
               TIMESTAMPDIFF(SECOND, created_at, COALESCE(completed_at, NOW())) AS elapsed_seconds
        FROM orders
        WHERE DATE(created_at) = ?
        ORDER BY id DESC
    ");
    $orderStmt->execute([$date]);
    $orders = $orderStmt->fetchAll();

    // Fetch line items for each order in the ledger
    $itemStmt = $db->prepare("
        SELECT id, order_id, item_name, temperature, milk_option, sweetness_level, custom_notes, quantity, unit_price, subtotal
        FROM order_items
        WHERE order_id = ?
    ");

    foreach ($orders as &$ord) {
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();
    }

    jsonResponse([
        'success'       => true,
        'selected_date' => $date,
        'kpi'           => [
            'total_revenue'        => (float) $kpi['total_revenue'],
            'cash_revenue'         => (float) $kpi['cash_revenue'],
            'gcash_revenue'        => (float) $kpi['gcash_revenue'],
            'completed_orders'     => (int) $kpi['completed_orders'],
            'pending_orders'       => (int) $kpi['pending_orders'],
            'in_progress_orders'   => (int) $kpi['in_progress_orders'],
            'cancelled_orders'     => (int) $kpi['cancelled_orders'],
            'dine_in_count'        => (int) $kpi['dine_in_count'],
            'take_out_count'       => (int) $kpi['take_out_count'],
            'avg_prep_minutes'     => (float) $kpi['avg_prep_minutes']
        ],
        'orders'        => $orders
    ]);
}

jsonResponse(['error' => 'Method not allowed.'], 405);
