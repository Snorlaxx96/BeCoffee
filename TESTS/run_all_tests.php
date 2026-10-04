<?php
/**
 * BeCoffee — Master Backend Test Runner
 * Orchestrates and executes all functional, button, role routing, and persistence test suites.
 * Can be executed via CLI: `php TESTS/run_all_tests.php`
 * Or in Browser: `http://localhost/YEAR%204/OMS%20-%20CAFE/TESTS/run_all_tests.php`
 */

require_once __DIR__ . '/TestFramework.php';

// Load All Modular Test Suites
require_once __DIR__ . '/test_user_roles_routing.php';
require_once __DIR__ . '/test_buttons_ordering.php';
require_once __DIR__ . '/test_buttons_kds.php';
require_once __DIR__ . '/test_buttons_menu_cms.php';
require_once __DIR__ . '/test_buttons_stock.php';
require_once __DIR__ . '/test_superadmin_users_system.php';
require_once __DIR__ . '/test_logout_workflows.php';
require_once __DIR__ . '/test_customer_flow.php';
require_once __DIR__ . '/test_walkin_registrar_no_table_phone.php';
require_once __DIR__ . '/test_sms_and_receipt_flow.php';
require_once __DIR__ . '/test_backend_transactions.php';
require_once __DIR__ . '/test_supabase_integration.php';

// Execute All Registered Suites
TestFramework::runAll();
