<?php
require_once __DIR__ . '/api/config.php';
$currentUser = getAuthenticatedUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Escobar Cafe | Online Take Out Order</title>
  <meta name="description" content="Online advance take-out ordering for Escobar Cafe / BeCoffee. Order handcrafted drinks for pickup.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=16.0">
  <style>
    /* Direct Order App Layout */
    body.order-app-body {
      background: #110D0B;
      background-image: 
        radial-gradient(circle at 50% -12%, rgba(226, 135, 67, 0.14) 0%, transparent 55%),
        radial-gradient(circle at 90% 25%, rgba(148, 77, 28, 0.08) 0%, transparent 45%);
      color: #F5EBE1;
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-bottom: 90px;
    }

    /* Sticky Order App Header */
    .order-header {
      background: rgba(21, 17, 14, 0.94);
      border-bottom: 1px solid rgba(223, 155, 100, 0.18);
      padding: 0.75rem 1.25rem;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .order-brand-link {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      color: #FFF;
    }
    .order-brand-logo {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(226, 135, 67, 0.3);
    }
    .order-brand-title {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.1;
    }
    .order-brand-tag {
      font-size: 0.68rem;
      color: #DF9B64;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      display: block;
    }

    /* Context Pill (Table or Takeout) */
    .table-context-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.35rem 0.85rem;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 600;
      background: rgba(226, 135, 67, 0.15);
      border: 1px solid rgba(226, 135, 67, 0.35);
      color: #FDBA74;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .table-context-pill:hover {
      background: rgba(226, 135, 67, 0.25);
    }

    /* Top Action Links */
    .order-top-actions {
      display: flex;
      align-items: center;
      gap: 0.65rem;
    }
    .btn-story-link {
      font-size: 0.8rem;
      font-weight: 600;
      color: #D6C7BC;
      text-decoration: none;
      padding: 0.4rem 0.75rem;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      transition: all 0.2s ease;
    }
    .btn-story-link:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #FFF;
    }

    /* Sticky Category Nav */
    .order-category-nav {
      background: rgba(17, 13, 11, 0.92);
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      padding: 0.65rem 1.25rem;
      position: sticky;
      top: 57px;
      z-index: 90;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
    }
    .cat-pill-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      overflow-x: auto;
      scrollbar-width: none;
      -ms-overflow-style: none;
      flex: 1;
      min-width: 0;
      padding: 2px 0;
    }
    .cat-pill-group::-webkit-scrollbar { display: none; }
    .cat-pill-btn {
      white-space: nowrap;
      padding: 0.45rem 1rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #D6C7BC;
      cursor: pointer;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .cat-pill-btn.active {
      background: #E28743;
      border-color: #E28743;
      color: #110D0B;
      font-weight: 700;
      box-shadow: 0 2px 10px rgba(226, 135, 67, 0.4);
    }
    .order-prep-timer {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.8rem;
      color: #DF9B64;
      background: rgba(226, 135, 67, 0.1);
      border: 1px solid rgba(226, 135, 67, 0.28);
      padding: 0.35rem 0.8rem;
      border-radius: 999px;
      white-space: nowrap;
      flex-shrink: 0;
      margin-left: auto;
      font-variant-numeric: tabular-nums;
      user-select: none;
    }
    .order-prep-timer .timer-icon {
      color: #FDBA74;
      flex-shrink: 0;
    }
    .order-prep-timer .timer-label-short {
      display: none;
    }
    .order-prep-timer .timer-clock {
      color: #FFF;
      font-weight: 700;
      letter-spacing: 0.04em;
      font-family: var(--font-mono, monospace);
    }
    @media (max-width: 520px) {
      .order-category-nav {
        padding: 0.55rem 0.75rem;
        gap: 0.5rem;
      }
      .order-prep-timer {
        padding: 0.3rem 0.6rem;
        font-size: 0.72rem;
      }
      .order-prep-timer .timer-label-full {
        display: none;
      }
      .order-prep-timer .timer-label-short {
        display: inline;
      }
    }


    /* Catalog Container */
    .order-catalog-wrap {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 1.25rem 1rem 3rem;
      flex: 1;
    }
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.25rem;
    }
    .drink-card {
      background: #1B1512;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 16px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
      cursor: pointer;
    }
    .drink-card:hover {
      transform: translateY(-2px);
      border-color: rgba(226, 135, 67, 0.4);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }
    .drink-img-wrap {
      position: relative;
      height: 180px;
      background: #251D18;
      overflow: hidden;
    }
    .drink-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .drink-card:hover .drink-img {
      transform: scale(1.04);
    }
    .badge-bestseller {
      position: absolute;
      top: 0.75rem;
      left: 0.75rem;
      background: rgba(226, 135, 67, 0.95);
      color: #110D0B;
      font-size: 0.68rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      padding: 0.2rem 0.6rem;
      border-radius: 999px;
    }
    .badge-stock-out {
      position: absolute;
      top: 0.75rem;
      right: 0.75rem;
      background: rgba(239, 68, 68, 0.9);
      color: #FFF;
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      padding: 0.2rem 0.6rem;
      border-radius: 999px;
    }
    .drink-body {
      padding: 1rem;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .drink-title {
      font-family: var(--font-serif);
      font-size: 1.1rem;
      font-weight: 700;
      color: #FFF;
      margin-bottom: 0.35rem;
    }
    .drink-origin {
      font-size: 0.72rem;
      color: #DF9B64;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-weight: 600;
      margin-bottom: 0.4rem;
    }
    .drink-desc {
      font-size: 0.8rem;
      color: #A99B92;
      line-height: 1.45;
      margin-bottom: 1rem;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .drink-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      padding-top: 0.75rem;
    }
    .drink-price {
      font-family: var(--font-mono);
      font-size: 1.05rem;
      font-weight: 700;
      color: #FDBA74;
    }
    .btn-customize-cta {
      background: rgba(226, 135, 67, 0.15);
      border: 1px solid rgba(226, 135, 67, 0.35);
      color: #FDBA74;
      padding: 0.4rem 0.85rem;
      border-radius: 8px;
      font-size: 0.78rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: all 0.2s ease;
    }
    .drink-card:hover .btn-customize-cta {
      background: #E28743;
      border-color: #E28743;
      color: #110D0B;
    }

    /* Floating Cart Sticky Bar */
    .floating-cart-bar {
      position: fixed;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%);
      width: calc(100% - 2rem);
      max-width: 540px;
      background: #E28743;
      color: #110D0B;
      padding: 0.75rem 1.25rem;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(226, 135, 67, 0.35);
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      z-index: 95;
      transition: transform 0.2s ease;
    }
    .floating-cart-bar:hover {
      transform: translateX(-50%) scale(1.02);
    }
    .cart-bar-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .cart-bar-icon-wrap {
      position: relative;
    }
    .cart-bar-count-badge {
      position: absolute;
      top: -6px;
      right: -8px;
      background: #110D0B;
      color: #FFF;
      font-size: 0.68rem;
      font-weight: 700;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cart-bar-info {
      display: flex;
      flex-direction: column;
    }
    .cart-bar-title {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      opacity: 0.85;
    }
    .cart-bar-total {
      font-family: var(--font-mono);
      font-size: 1.15rem;
      font-weight: 800;
    }
    .cart-bar-cta {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-weight: 700;
      font-size: 0.9rem;
    }

    /* OMS Slide-over Cart & Modals */
    .oms-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 200;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .oms-modal-overlay.active {
      display: flex;
    }
    .oms-modal-box {
      background: #1B1512;
      border: 1px solid rgba(223, 155, 100, 0.25);
      border-radius: 20px;
      width: 100%;
      max-width: 520px;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
      animation: modalSlideUp 0.25s ease-out;
    }
    @keyframes modalSlideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .oms-modal-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .oms-modal-title {
      font-family: var(--font-serif);
      font-size: 1.2rem;
      font-weight: 700;
      color: #FFF;
    }
    .btn-modal-close {
      background: none;
      border: none;
      color: #A99B92;
      font-size: 1.5rem;
      cursor: pointer;
      line-height: 1;
    }
    .oms-modal-body {
      padding: 1.25rem;
      overflow-y: auto;
      flex: 1;
    }
    .custom-section-label {
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #DF9B64;
      margin-bottom: 0.6rem;
      display: block;
    }
    .pill-radio-group {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1.25rem;
    }
    .pill-radio-opt {
      padding: 0.45rem 0.85rem;
      border-radius: 10px;
      font-size: 0.8rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #D6C7BC;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .pill-radio-opt.active {
      background: rgba(226, 135, 67, 0.2);
      border-color: #E28743;
      color: #FDBA74;
      font-weight: 600;
    }
    .oms-modal-footer {
      padding: 1rem 1.25rem;
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(0, 0, 0, 0.2);
    }
    .qty-selector {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 8px;
      padding: 0.25rem 0.5rem;
    }
    .btn-qty {
      background: none;
      border: none;
      color: #FFF;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .qty-val {
      font-family: var(--font-mono);
      font-weight: 700;
      font-size: 0.9rem;
    }
    .btn-add-cart-primary {
      background: #E28743;
      color: #110D0B;
      border: none;
      padding: 0.65rem 1.4rem;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.9rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: opacity 0.2s ease;
    }

    /* Cart Slide-in Drawer */
    .cart-drawer-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      z-index: 200;
      display: none;
      justify-content: flex-end;
    }
    .cart-drawer-overlay.active {
      display: flex;
    }
    .cart-drawer {
      background: #191310;
      width: 100%;
      max-width: 440px;
      height: 100%;
      border-left: 1px solid rgba(223, 155, 100, 0.2);
      display: flex;
      flex-direction: column;
      animation: drawerSlide 0.25s ease-out;
    }
    @keyframes drawerSlide {
      from { transform: translateX(100%); }
      to { transform: translateX(0); }
    }
    .cart-drawer-header {
      padding: 1.25rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .cart-drawer-items {
      padding: 1.25rem;
      overflow-y: auto;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .cart-line-item {
      background: #211915;
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 0.85rem;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.75rem;
    }
    .cart-line-info {
      flex: 1;
    }
    .cart-line-title {
      font-weight: 700;
      font-size: 0.95rem;
      color: #FFF;
      margin-bottom: 0.2rem;
    }
    .cart-line-specs {
      font-size: 0.75rem;
      color: #A99B92;
      line-height: 1.35;
    }
    .cart-line-price {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #FDBA74;
      font-size: 0.95rem;
      margin-top: 0.35rem;
    }
    .btn-remove-item {
      background: none;
      border: none;
      color: #EF4444;
      font-size: 1.1rem;
      cursor: pointer;
      opacity: 0.7;
    }
    .btn-remove-item:hover { opacity: 1; }
    .cart-drawer-footer {
      padding: 1.25rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(0, 0, 0, 0.25);
    }
    .cart-calc-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      color: #A99B92;
      margin-bottom: 0.45rem;
    }
    .cart-calc-row.total {
      font-size: 1.15rem;
      font-weight: 800;
      color: #FFF;
      border-top: 1px dashed rgba(255, 255, 255, 0.12);
      padding-top: 0.65rem;
      margin-top: 0.65rem;
    }
    .btn-checkout-primary {
      width: 100%;
      background: #E28743;
      color: #110D0B;
      border: none;
      padding: 0.85rem;
      border-radius: 12px;
      font-weight: 800;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1rem;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      transition: opacity 0.2s ease;
    }
    .btn-checkout-primary:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Live Queue Ticket Card (Order Confirmation) */
    .ticket-card {
      background: #1B1512;
      border: 1px solid rgba(226, 135, 67, 0.3);
      border-radius: 20px;
      padding: 1.5rem;
      text-align: center;
      max-width: 480px;
      margin: 2rem auto;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
    }
    .ticket-queue-badge {
      font-family: var(--font-mono);
      font-size: 2.2rem;
      font-weight: 800;
      color: #FDBA74;
      background: rgba(226, 135, 67, 0.15);
      border: 1px solid rgba(226, 135, 67, 0.35);
      padding: 0.5rem 1.5rem;
      border-radius: 14px;
      display: inline-block;
      margin: 1rem 0;
    }
    .ticket-status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.3rem 0.85rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 1rem;
    }
    .status-pending { background: rgba(234, 179, 8, 0.2); color: #FDE047; border: 1px solid rgba(234, 179, 8, 0.4); }
    .status-in_progress { background: rgba(59, 130, 246, 0.2); color: #93C5FD; border: 1px solid rgba(59, 130, 246, 0.4); }
    .status-completed { background: rgba(16, 185, 129, 0.2); color: #6EE7B7; border: 1px solid rgba(16, 185, 129, 0.4); }
  </style>
</head>
<body class="order-app-body">

  <!-- Direct Order Header -->
  <header class="order-header">
    <a href="takeout.php" class="order-brand-link">
      <div class="order-brand-logo">E</div>
      <div>
        <span class="order-brand-title">Escobar Cafe</span>
        <span class="order-brand-tag">Online Take Out Order</span>
      </div>
    </a>

    <!-- Context Pill (Fixed to Take-out) -->
    <div class="table-context-pill" id="tableContextPill" title="Online Take Out Mode">
      <span id="tableContextIcon">🛍️</span>
      <span id="tableContextText">Take-out Pickup</span>
    </div>

    <!-- Right Actions -->
    <div class="order-top-actions">
      <a href="index.php?table=1" class="btn-story-link" style="color: #FDBA74; border-color: rgba(226, 135, 67, 0.4);" title="Switch to Dine-in Table Ordering">🪑 Dine In</a>
      <?php if (!empty($currentUser)): ?>
        <a href="api/auth.php?action=logout&redirect=home.php" class="btn-story-link" style="color: #FCA5A5; border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1);" title="Sign out of account">🚪 Log Out</a>
      <?php else: ?>
        <a href="home.php" class="btn-story-link" style="color: #DF9B64; border-color: rgba(223, 155, 100, 0.3);" title="Return to BeCoffee Storefront">🏠 Home</a>
      <?php endif; ?>
    </div>
  </header>



  <!-- Sticky Category Navigation -->
  <nav class="order-category-nav" id="orderCategoryNav" aria-label="Menu Categories">
    <div class="cat-pill-group" id="catPillGroup">
      <button type="button" class="cat-pill-btn active" data-cat="all">All Drinks</button>
      <button type="button" class="cat-pill-btn" data-cat="house-coffee">House Coffee</button>
      <button type="button" class="cat-pill-btn" data-cat="matcha">Matcha</button>
      <button type="button" class="cat-pill-btn" data-cat="house-specials">House Specials</button>
      <button type="button" class="cat-pill-btn" data-cat="yogurt-soda">Yogurt / Soda</button>
    </div>
  </nav>

  <!-- Main Order Catalog -->
  <main class="order-catalog-wrap">
    <div id="catalogLoadingNotice" style="text-align: center; padding: 3rem 1rem; color: #A99B92;">
      Loading artisanal drink selection...
    </div>

    <div class="catalog-grid" id="drinksCatalogGrid" style="display: none;">
      <!-- Drink Cards injected dynamically -->
    </div>

    <!-- Active Ticket Polling View -->
    <div id="activeTicketView" style="display: none;">
      <div class="ticket-card">
        <div style="font-size: 0.85rem; color: #A99B92; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">Live Kitchen Queue</div>
        <div class="ticket-queue-badge" id="ticketQueueNumber">#00</div>
        <div>
          <span class="ticket-status-pill status-pending" id="ticketStatusPill">● Pending Kitchen Ack</span>
        </div>
        <div style="font-size: 0.95rem; color: #F5EBE1; margin-bottom: 0.5rem;" id="ticketOrderRef">Ref: BC-000000</div>
        
        <div id="ticketDiningWrap" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem; margin-bottom: 0.85rem;">
          <span id="ticketDiningTag" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.82rem; font-weight: 700; background: rgba(226, 135, 67, 0.18); color: #FDBA74; border: 1px solid rgba(226, 135, 67, 0.35);">
            🛍️ Take-out Order
          </span>
          <button type="button" id="btnSwitchDiningPostOrder" style="background: none; border: none; color: #DF9B64; font-size: 0.76rem; text-decoration: underline; cursor: pointer; padding: 0.2rem 0.5rem; transition: opacity 0.15s ease;" title="Change dining mode if selected by mistake">
            Arrived at cafe? Switch to Dine-in Table
          </button>
        </div>

        <div style="font-size: 0.85rem; color: #DF9B64; margin-bottom: 1.25rem;" id="ticketEstWait">Orders ahead in queue: <strong>0</strong></div>

        <div style="text-align: left; background: #251D18; padding: 1rem; border-radius: 12px; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; font-size: 0.85rem; color: #DF9B64; margin-bottom: 0.5rem; text-transform: uppercase;">Ticket Items:</div>
          <div id="ticketItemsList" style="font-size: 0.82rem; line-height: 1.5; color: #D6C7BC;"></div>
        </div>

        <button type="button" id="btnPlaceNewOrder" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #FFF; padding: 0.55rem 1.25rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
          + Place Another Order
        </button>
      </div>
    </div>
  </main>

  <!-- Floating Sticky Cart Bar -->
  <div class="floating-cart-bar" id="floatingCartBar" style="display: none;">
    <div class="cart-bar-left">
      <div class="cart-bar-icon-wrap">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        <span class="cart-bar-count-badge" id="cartCountBadge">0</span>
      </div>
      <div class="cart-bar-info">
        <span class="cart-bar-title" id="cartBarSubtitle">Take-out Cart</span>
        <span class="cart-bar-total" id="cartBarTotal">₱0.00</span>
      </div>
    </div>
    <div class="cart-bar-cta">
      <span>Review Pickup</span>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </div>
  </div>

  <!-- Customization Modal Sheet -->
  <div class="oms-modal-overlay" id="customizationModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box">
      <div class="oms-modal-header">
        <span class="oms-modal-title" id="modalDrinkTitle">Custom Drink</span>
        <button type="button" class="btn-modal-close" id="btnModalClose">&times;</button>
      </div>
      <div class="oms-modal-body">
        <!-- Temperature Selection -->
        <span class="custom-section-label">Serving Temperature</span>
        <div class="pill-radio-group" id="tempRadioGroup">
          <div class="pill-radio-opt active" data-val="Iced">❄️ Iced Fresh</div>
          <div class="pill-radio-opt" data-val="Hot">🔥 Steamed Hot</div>
        </div>

        <!-- Cup Size Selection -->
        <span class="custom-section-label">Cup Size</span>
        <div class="pill-radio-group" id="sizeRadioGroup">
          <div class="pill-radio-opt active" data-val="Medium">Medium (16 oz)</div>
          <div class="pill-radio-opt" data-val="Large">Large (22 oz) +₱30</div>
        </div>

        <!-- Milk & Dairy Options -->
        <span class="custom-section-label">Milk / Dairy Base</span>
        <div class="pill-radio-group" id="milkRadioGroup">
          <div class="pill-radio-opt active" data-val="Regular Milk">Fresh Dairy</div>
          <div class="pill-radio-opt" data-val="Oat Milk">Oat Milk (+₱30)</div>
          <div class="pill-radio-opt" data-val="Soy Milk">Soy Milk (+₱25)</div>
          <div class="pill-radio-opt" data-val="Almond Milk">Almond Milk (+₱30)</div>
          <div class="pill-radio-opt" data-val="Breve">Breve / Half &amp; Half (+₱25)</div>
        </div>

        <!-- Sweetness Level -->
        <span class="custom-section-label">Sweetness Level</span>
        <div class="pill-radio-group" id="sweetnessRadioGroup">
          <div class="pill-radio-opt active" data-val="Normal (100%)">100% Standard</div>
          <div class="pill-radio-opt" data-val="75% Sweet">75% Balanced</div>
          <div class="pill-radio-opt" data-val="50% Less Sweet">50% Less Sweet</div>
          <div class="pill-radio-opt" data-val="25% Light Sweet">25% Light</div>
          <div class="pill-radio-opt" data-val="No Sugar (0%)">0% No Sugar</div>
        </div>

        <!-- Add-on Surcharges -->
        <span class="custom-section-label">Add-ons &amp; Boosters</span>
        <div class="pill-radio-group" id="addonRadioGroup">
          <!-- Populated dynamically from stock -->
        </div>

        <!-- Special Barista Note -->
        <span class="custom-section-label">Special Barista Note</span>
        <input type="text" id="customItemNote" placeholder="e.g. Extra espresso shot, separate syrup" style="width: 100%; background: #251D18; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.6rem; color: #FFF; font-size: 0.85rem; box-sizing: border-box;">
      </div>
      <div class="oms-modal-footer">
        <div class="qty-selector">
          <button type="button" class="btn-qty" id="btnModalQtyMinus">-</button>
          <span class="qty-val" id="modalQtyVal">1</span>
          <button type="button" class="btn-qty" id="btnModalQtyPlus">+</button>
        </div>
        <button type="button" class="btn-add-cart-primary" id="btnModalAddToCart">
          <span>Add to Takeout Cart</span>
          <span id="modalAddPrice">₱0.00</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Cart Slide-over Drawer -->
  <div class="cart-drawer-overlay" id="cartDrawerOverlay">
    <div class="cart-drawer">
      <div class="cart-drawer-header">
        <div style="font-family: var(--font-serif); font-size: 1.15rem; font-weight: 700; color: #FFF;">
          🛍️ Take-out Cart (<span id="cartDrawerTotalCount">0</span>)
        </div>
        <button type="button" class="btn-modal-close" id="btnCartClose">&times;</button>
      </div>
      <div class="cart-drawer-items" id="cartDrawerItems">
        <!-- Cart Line Items injected here -->
      </div>
      <div class="cart-drawer-footer">
        <div class="cart-calc-row">
          <span>Items Subtotal</span>
          <span id="cartSubtotalVal" style="font-family: var(--font-mono);">₱0.00</span>
        </div>
        <div class="cart-calc-row">
          <span id="ecoFeeLabel">Eco-Packaging Fee</span>
          <span id="cartEcoFeeVal" style="font-family: var(--font-mono);">₱15.00</span>
        </div>
        <div class="cart-calc-row total">
          <span>Grand Total</span>
          <span id="cartGrandTotalVal" style="font-family: var(--font-mono); color: #FDBA74;">₱0.00</span>
        </div>

        <!-- Customer Pickup Credentials -->
        <div style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 0.85rem;">
          <label style="display: block; font-size: 0.75rem; color: #DF9B64; font-weight: 700; text-transform: uppercase; margin-bottom: 0.35rem;">Customer Name for Pickup</label>
          <input type="text" id="checkoutCustName" placeholder="Full Name" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" style="width: 100%; background: #251D18; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.55rem; color: #FFF; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.65rem;" required>

          <label style="display: block; font-size: 0.75rem; color: #DF9B64; font-weight: 700; text-transform: uppercase; margin-bottom: 0.35rem;">Contact Number (+63)</label>
          <input type="tel" id="checkoutCustPhone" placeholder="0917 123 4567" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" style="width: 100%; background: #251D18; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.55rem; color: #FFF; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.65rem;" required>

          <!-- Payment Options -->
          <label style="display: block; font-size: 0.75rem; color: #DF9B64; font-weight: 700; text-transform: uppercase; margin-bottom: 0.35rem;">Payment Method</label>
          <div class="pill-radio-group" id="paymentRadioGroup" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-bottom: 0.85rem;">
            <div class="pill-radio-opt active" data-val="cash" style="text-align: center; font-weight: 600;">💵 Pay at Counter</div>
            <div class="pill-radio-opt" data-val="gcash" style="text-align: center; font-weight: 600;">📱 GCash / Maya</div>
          </div>
        </div>

        <button type="button" class="btn-checkout-primary" id="btnConfirmTakeoutOrder">
          <span>Confirm Take-out Order</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Application Logic -->
  <script src="js/system-dialog.js"></script>
  <script>
    (function initTakeoutPortal() {
      var currentOrderType = 'take_out';
      sessionStorage.setItem('becoffee_order_type', 'take_out');

      // 1. Fetch Menu Items & Stock
      var menuData = [];
      var stockOptions = [];
      var stockAvailability = {};
      var itemAvailability = {};

      async function loadStockAvailability() {
        try {
          var res = await fetch('api/stock.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            stockOptions = data.options || [];
            stockAvailability = data.availability || {};
            itemAvailability = data.item_availability || {};
          }
        } catch(e) {}
      }

      async function loadMenuCatalog() {
        try {
          await loadStockAvailability();
          var res = await fetch('api/menu.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            menuData = (data && data.items) ? data.items : [];
          }
        } catch(e) {}

        if (!menuData || menuData.length === 0) {
          // Fallback catalog
          menuData = [
            { id: 1, category: 'house-coffee', name: 'Spanish Latte', price: 160.00, priceIcedM: 160.00, priceIcedL: 190.00, priceHot: 150.00, description: 'Double shot espresso, fresh milk, sweetened condensed milk.', image: 'images/menu/spanish-latte.webp', origin: 'Mt. Apo', isBestseller: true, is_available: 1 },
            { id: 2, category: 'house-coffee', name: 'Honeycomb Cold Brew', price: 170.00, priceIcedM: 170.00, priceIcedL: 200.00, priceHot: null, description: '18-hour cold steeped single origin topped with homemade honey crunch.', image: 'images/menu/hc-coldbrew.webp', origin: 'Bukidnon', isBestseller: true, is_available: 1 },
            { id: 3, category: 'matcha', name: 'Ceremonial Matcha Latte', price: 180.00, priceIcedM: 180.00, priceIcedL: 210.00, priceHot: 170.00, description: 'Single-origin Uji Kyoto ceremonial matcha with creamy fresh milk.', image: 'images/menu/matcha-latte.webp', origin: 'Uji, Kyoto', isBestseller: true, is_available: 1 },
            { id: 4, category: 'house-specials', name: 'Sea Salt Caramel Latte', price: 175.00, priceIcedM: 175.00, priceIcedL: 205.00, priceHot: 165.00, description: 'Handcrafted salted caramel syrup with silky textured espresso cream.', image: 'images/menu/seasalt-latte.webp', origin: 'House Roast', isBestseller: false, is_available: 1 }
          ];
        }

        renderCatalog('all');
      }

      // 2. Render Drink Cards
      var grid = document.getElementById('drinksCatalogGrid');
      var loading = document.getElementById('catalogLoadingNotice');

      function renderCatalog(filterCat) {
        if (!grid) return;
        loading.style.display = 'none';
        grid.style.display = 'grid';
        grid.innerHTML = '';

        var filtered = menuData.filter(function(item) {
          return filterCat === 'all' || item.category === filterCat;
        });

        if (filtered.length === 0) {
          grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #A99B92;">No items found in this category.</div>';
          return;
        }

        filtered.forEach(function(item) {
          var isSoldOut = (itemAvailability[item.id] === false) || (item.is_available === 0);
          var card = document.createElement('div');
          card.className = 'drink-card';
          card.innerHTML = `
            <div class="drink-img-wrap">
              <img src="${item.image || 'images/menu/hc-classic.webp'}" alt="${item.name}" class="drink-img" loading="lazy" onerror="this.src='images/menu/hc-classic.webp'">
              ${item.isBestseller ? '<span class="badge-bestseller">Popular</span>' : ''}
              ${isSoldOut ? '<span class="badge-stock-out">Sold Out</span>' : ''}
            </div>
            <div class="drink-body">
              <span class="drink-origin">${item.origin || 'Artisanal Roast'}</span>
              <h3 class="drink-title">${item.name}</h3>
              <p class="drink-desc">${item.description || 'Specialty handcrafted beverage.'}</p>
              <div class="drink-footer">
                <span class="drink-price">₱${parseFloat(item.priceIcedM || item.price || 150).toFixed(2)}</span>
                <button type="button" class="btn-customize-cta" ${isSoldOut ? 'disabled style="opacity: 0.5;"' : ''}>
                  ${isSoldOut ? 'Unavailable' : 'Customize +'}
                </button>
              </div>
            </div>
          `;

          if (!isSoldOut) {
            card.onclick = function() { openCustomizationModal(item); };
          }
          grid.appendChild(card);
        });
      }

      // Category Switching
      document.querySelectorAll('#orderCategoryNav .cat-pill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('#orderCategoryNav .cat-pill-btn').forEach(function(b) { b.classList.remove('active'); });
          btn.classList.add('active');
          renderCatalog(btn.getAttribute('data-cat'));
        });
      });

      // 3. Customization Modal & Pricing Engine
      var currentItem = null;
      var activeModalPrice = 0;
      var modalQty = 1;
      var selectedAddons = new Set();

      function openCustomizationModal(item) {
        currentItem = item;
        modalQty = 1;
        selectedAddons.clear();
        document.getElementById('modalDrinkTitle').textContent = item.name;
        document.getElementById('modalQtyVal').textContent = '1';
        document.getElementById('customItemNote').value = '';

        // Reset pill radio buttons
        ['tempRadioGroup', 'sizeRadioGroup', 'milkRadioGroup', 'sweetnessRadioGroup'].forEach(function(gid) {
          var opts = document.querySelectorAll('#' + gid + ' .pill-radio-opt');
          opts.forEach(function(o, idx) { o.classList.toggle('active', idx === 0); });
        });

        // Add-ons list
        var addonGroup = document.getElementById('addonRadioGroup');
        addonGroup.innerHTML = '';
        var addons = stockOptions.filter(function(o) { return o.category_type === 'addon' && o.is_available; });
        if (addons.length === 0) {
          addons = [
            { option_key: 'espresso-shot', option_label: 'Extra Espresso Shot (+₱35)', surcharge: 35.00 },
            { option_key: 'honeycomb-crunch', option_label: 'Honeycomb Crunch (+₱30)', surcharge: 30.00 },
            { option_key: 'vanilla-syrup', option_label: 'Artisanal Vanilla (+₱25)', surcharge: 25.00 }
          ];
        }
        addons.forEach(function(add) {
          var pill = document.createElement('div');
          pill.className = 'pill-radio-opt';
          pill.setAttribute('data-val', add.option_key);
          pill.setAttribute('data-surcharge', add.surcharge || 0);
          pill.textContent = add.option_label;
          pill.onclick = function() {
            if (selectedAddons.has(add.option_key)) {
              selectedAddons.delete(add.option_key);
              pill.classList.remove('active');
            } else {
              selectedAddons.add(add.option_key);
              pill.classList.add('active');
            }
            recalcModalPrice();
          };
          addonGroup.appendChild(pill);
        });

        setupPillGroupClick('tempRadioGroup');
        setupPillGroupClick('sizeRadioGroup');
        setupPillGroupClick('milkRadioGroup');
        setupPillGroupClick('sweetnessRadioGroup');

        recalcModalPrice();
        document.getElementById('customizationModal').classList.add('active');
      }

      function setupPillGroupClick(gid) {
        document.querySelectorAll('#' + gid + ' .pill-radio-opt').forEach(function(pill) {
          pill.onclick = function() {
            document.querySelectorAll('#' + gid + ' .pill-radio-opt').forEach(function(p) { p.classList.remove('active'); });
            pill.classList.add('active');
            recalcModalPrice();
          };
        });
      }

      function recalcModalPrice() {
        if (!currentItem) return;
        var sizeOpt = document.querySelector('#sizeRadioGroup .pill-radio-opt.active');
        var tempOpt = document.querySelector('#tempRadioGroup .pill-radio-opt.active');
        var milkOpt = document.querySelector('#milkRadioGroup .pill-radio-opt.active');

        var size = sizeOpt ? sizeOpt.getAttribute('data-val') : 'Medium';
        var temp = tempOpt ? tempOpt.getAttribute('data-val') : 'Iced';
        var milk = milkOpt ? milkOpt.getAttribute('data-val') : 'Regular Milk';

        var base = parseFloat(currentItem.price || 150);
        if (temp === 'Hot' && currentItem.priceHot) {
          base = parseFloat(currentItem.priceHot);
        } else if (size === 'Large') {
          base = parseFloat(currentItem.priceIcedL || (base + 30));
        } else {
          base = parseFloat(currentItem.priceIcedM || base);
        }

        // Milk surcharges
        if (milk === 'Oat Milk' || milk === 'Almond Milk') base += 30;
        else if (milk === 'Soy Milk' || milk === 'Breve') base += 25;

        // Add-ons
        document.querySelectorAll('#addonRadioGroup .pill-radio-opt.active').forEach(function(addPill) {
          base += parseFloat(addPill.getAttribute('data-surcharge') || 0);
        });

        activeModalPrice = base;
        document.getElementById('modalAddPrice').textContent = '₱' + (activeModalPrice * modalQty).toFixed(2);
      }

      document.getElementById('btnModalQtyMinus').onclick = function() {
        if (modalQty > 1) {
          modalQty--;
          document.getElementById('modalQtyVal').textContent = modalQty;
          recalcModalPrice();
        }
      };
      document.getElementById('btnModalQtyPlus').onclick = function() {
        modalQty++;
        document.getElementById('modalQtyVal').textContent = modalQty;
        recalcModalPrice();
      };
      document.getElementById('btnModalClose').onclick = function() {
        document.getElementById('customizationModal').classList.remove('active');
      };

      // 4. Cart Management & Drawer
      var cart = [];
      try {
        cart = JSON.parse(sessionStorage.getItem('becoffee_takeout_cart') || '[]');
      } catch(e) { cart = []; }

      function saveCart() {
        sessionStorage.setItem('becoffee_takeout_cart', JSON.stringify(cart));
        updateCartUI();
      }

      document.getElementById('btnModalAddToCart').onclick = function() {
        var temp = document.querySelector('#tempRadioGroup .pill-radio-opt.active').getAttribute('data-val');
        var size = document.querySelector('#sizeRadioGroup .pill-radio-opt.active').getAttribute('data-val');
        var milk = document.querySelector('#milkRadioGroup .pill-radio-opt.active').getAttribute('data-val');
        var sweetness = document.querySelector('#sweetnessRadioGroup .pill-radio-opt.active').getAttribute('data-val');
        var note = document.getElementById('customItemNote').value.trim();

        var addonNames = [];
        document.querySelectorAll('#addonRadioGroup .pill-radio-opt.active').forEach(function(p) {
          addonNames.push(p.textContent.split(' (+')[0].trim());
        });

        cart.push({
          id: currentItem.id,
          name: currentItem.name,
          quantity: modalQty,
          unit_price: activeModalPrice,
          temperature: temp,
          size: size,
          milk_option: milk,
          sweetness_level: sweetness,
          addons: addonNames,
          custom_notes: note
        });

        saveCart();
        document.getElementById('customizationModal').classList.remove('active');
      };

      function updateCartUI() {
        var totalCount = cart.reduce(function(sum, item) { return sum + item.quantity; }, 0);
        var subtotal = cart.reduce(function(sum, item) { return sum + (item.unit_price * item.quantity); }, 0);
        var ecoFee = 15.00; // Always apply packaging fee for take-out
        var grandTotal = (totalCount > 0) ? (subtotal + ecoFee) : 0.00;

        document.getElementById('cartCountBadge').textContent = totalCount;
        document.getElementById('cartDrawerTotalCount').textContent = totalCount;
        document.getElementById('cartBarTotal').textContent = '₱' + grandTotal.toFixed(2);
        document.getElementById('cartSubtotalVal').textContent = '₱' + subtotal.toFixed(2);
        document.getElementById('cartGrandTotalVal').textContent = '₱' + grandTotal.toFixed(2);

        document.getElementById('floatingCartBar').style.display = (totalCount > 0) ? 'flex' : 'none';

        var itemsContainer = document.getElementById('cartDrawerItems');
        itemsContainer.innerHTML = '';

        if (cart.length === 0) {
          itemsContainer.innerHTML = '<div style="text-align: center; color: #A99B92; padding: 2rem;">Your take-out cart is empty.</div>';
          document.getElementById('btnConfirmTakeoutOrder').disabled = true;
          return;
        }

        document.getElementById('btnConfirmTakeoutOrder').disabled = false;

        cart.forEach(function(item, idx) {
          var div = document.createElement('div');
          div.className = 'cart-line-item';
          var addonText = (item.addons && item.addons.length > 0) ? ` · ${item.addons.join(', ')}` : '';
          div.innerHTML = `
            <div class="cart-line-info">
              <div class="cart-line-title">${item.quantity}x ${item.name}</div>
              <div class="cart-line-specs">${item.size} · ${item.temperature} · ${item.milk_option} · ${item.sweetness_level}${addonText}</div>
              ${item.custom_notes ? `<div class="cart-line-specs" style="color: #DF9B64; font-style: italic;">"${item.custom_notes}"</div>` : ''}
              <div class="cart-line-price">₱${(item.unit_price * item.quantity).toFixed(2)}</div>
            </div>
            <button type="button" class="btn-remove-item" title="Remove Item">&times;</button>
          `;
          div.querySelector('.btn-remove-item').onclick = function() {
            cart.splice(idx, 1);
            saveCart();
          };
          itemsContainer.appendChild(div);
        });
      }

      document.getElementById('floatingCartBar').onclick = function() {
        document.getElementById('cartDrawerOverlay').classList.add('active');
      };
      document.getElementById('btnCartClose').onclick = function() {
        document.getElementById('cartDrawerOverlay').classList.remove('active');
      };

      // 5. Confirm Order Action
      document.querySelectorAll('#paymentRadioGroup .pill-radio-opt').forEach(function(pill) {
        pill.onclick = function() {
          document.querySelectorAll('#paymentRadioGroup .pill-radio-opt').forEach(function(p) { p.classList.remove('active'); });
          pill.classList.add('active');
        };
      });

      var checkoutBtn = document.getElementById('btnConfirmTakeoutOrder');
      checkoutBtn.onclick = async function() {
        if (cart.length === 0) return;

        var nameInput = document.getElementById('checkoutCustName');
        var phoneInput = document.getElementById('checkoutCustPhone');
        var name = nameInput.value.trim();
        var phone = phoneInput.value.trim();
        var paymentOpt = document.querySelector('#paymentRadioGroup .pill-radio-opt.active');
        var payMethod = paymentOpt ? paymentOpt.getAttribute('data-val') : 'cash';

        if (!name || name.length < 2) {
          await SystemDialog.alert('Please enter your full name for order pickup.', { title: 'Name Required', type: 'warning' });
          nameInput.focus();
          return;
        }
        if (!phone || phone.length < 7) {
          await SystemDialog.alert('Please enter a valid mobile number (+63) for order alerts.', { title: 'Mobile Number Required', type: 'warning' });
          phoneInput.focus();
          return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<span>Transmitting Order...</span>';

        try {
          var payload = {
            customer_name: name,
            customer_phone: phone,
            order_type: 'take_out',
            table_number: null,
            order_source: 'online',
            payment_method: payMethod,
            items: cart
          };

          var res = await fetch('api/orders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });

          var data = await res.json();
          if (res.ok && data.success) {
            cart = [];
            saveCart();
            document.getElementById('cartDrawerOverlay').classList.remove('active');
            displayLiveTicket(data.order_reference, data.queue_number);
          } else {
            await SystemDialog.alert(data.error || 'Could not place order. Please try again.', { title: 'Order Issue', type: 'danger' });
          }
        } catch(e) {
          await SystemDialog.alert('Network connection failure. Please verify connection and try again.', { title: 'Connection Error', type: 'danger' });
        } finally {
          checkoutBtn.disabled = false;
          checkoutBtn.innerHTML = '<span>Confirm Take-out Order</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        }
      };

      // 6. Live Ticket Polling
      var activeRef = null;
      var pollTimer = null;

      function displayLiveTicket(ref, queue) {
        activeRef = ref;
        sessionStorage.setItem('becoffee_last_order_ref', ref);
        document.getElementById('activeTicketView').style.display = 'block';
        document.getElementById('catalogLoadingNotice').style.display = 'none';
        document.getElementById('drinksCatalogGrid').style.display = 'none';
        document.getElementById('floatingCartBar').style.display = 'none';

        document.getElementById('ticketQueueNumber').textContent = '#' + queue;
        document.getElementById('ticketOrderRef').textContent = 'Ref: ' + ref;

        pollTicketStatus();
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollTicketStatus, 5000);
      }

      async function pollTicketStatus() {
        if (!activeRef) return;
        try {
          var res = await fetch('api/orders.php?reference=' + encodeURIComponent(activeRef), { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            var ord = data.order;
            if (ord) {
              var pill = document.getElementById('ticketStatusPill');
              if (ord.status === 'completed') {
                pill.className = 'ticket-status-pill status-completed';
                pill.textContent = '✅ Order Ready for Pickup!';
              } else if (ord.status === 'in_progress') {
                pill.className = 'ticket-status-pill status-in_progress';
                pill.textContent = '☕ Barista Brewing Now';
              } else {
                pill.className = 'ticket-status-pill status-pending';
                pill.textContent = '● Pending Kitchen Queue';
              }

              document.getElementById('ticketEstWait').innerHTML = 'Orders ahead in queue: <strong>' + (ord.orders_ahead || 0) + '</strong>';

              var itemsList = document.getElementById('ticketItemsList');
              if (ord.items && ord.items.length > 0) {
                itemsList.innerHTML = ord.items.map(function(it) {
                  return `<div>• ${it.quantity}x ${it.item_name} (${it.temperature}, ${it.milk_option})</div>`;
                }).join('');
              }
            }
          }
        } catch(e) {}
      }

      document.getElementById('btnPlaceNewOrder').onclick = function() {
        if (pollTimer) clearInterval(pollTimer);
        activeRef = null;
        sessionStorage.removeItem('becoffee_last_order_ref');
        document.getElementById('activeTicketView').style.display = 'none';
        renderCatalog('all');
      };

      // Post-Order Switch to Dine-in if customer arrived at cafe
      document.getElementById('btnSwitchDiningPostOrder').onclick = async function() {
        if (!activeRef) return;
        var tableNum = await SystemDialog.prompt('What table are you seated at? (e.g. 1 - 10)', '1', {
          title: 'Table Assignment',
          placeholder: 'Table number'
        });
        if (!tableNum) return;

        try {
          var res = await fetch('api/orders.php?action=update_dining', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reference: activeRef, order_type: 'dine_in', table_number: tableNum })
          });
          var data = await res.json();
          if (res.ok && data.success) {
            document.getElementById('ticketDiningTag').textContent = '🪑 Dine-in (Table #' + tableNum + ')';
            document.getElementById('btnSwitchDiningPostOrder').style.display = 'none';
            await SystemDialog.alert('Dining preference updated to Table #' + tableNum + '. Your order will be served to your table!', { title: 'Table Assigned', type: 'success' });
          } else {
            await SystemDialog.alert(data.error || 'Could not update dining mode.', { title: 'Update Failed', type: 'danger' });
          }
        } catch(e) {
          await SystemDialog.alert('Network error updating dining mode.', { title: 'Network Error', type: 'danger' });
        }
      };

      // Check for existing active order on page load
      var savedRef = sessionStorage.getItem('becoffee_last_order_ref');
      if (savedRef) {
        displayLiveTicket(savedRef, '...');
      } else {
        loadMenuCatalog();
      }
    })();
  </script>
</body>
</html>
