<?php
require_once __DIR__ . '/api/config.php';
$currentUser = getAuthenticatedUser();
if (!$currentUser) {
    header('Location: index.php?error=unauthorized');
    exit;
}
if ($currentUser['role'] === 'customer') {
    header('Location: index.php?notice=customer_restricted');
    exit;
}
if ($currentUser['role'] === 'staff') {
    header('Location: kds.php?notice=staff_restricted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Escobar Cafe | Admin Stock & Customization Control Portal</title>
  <meta name="description" content="Staff and Administrator Stock Management Portal for Escobar Cafe / BeCoffee. 86 and cancel out customization options in real-time.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=16.0">
  <style>
    /* Admin Order App Layout */
    body.admin-app-body {
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

    /* Admin Sticky Header */
    .admin-order-header {
      background: rgba(21, 17, 14, 0.96);
      border-bottom: 1px solid rgba(239, 68, 68, 0.35);
      padding: 0.75rem 1.25rem;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .admin-brand-wrap {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .admin-brand-logo {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    }
    .admin-brand-title {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.1;
    }
    .admin-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.7rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #F87171;
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      padding: 0.2rem 0.55rem;
      border-radius: 999px;
    }

    .admin-nav-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .admin-nav-btn {
      padding: 0.4rem 0.75rem;
      border-radius: 8px;
      font-size: 0.78rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: all 0.2s ease;
      cursor: pointer;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.05);
      color: #E2D5CC;
    }
    .admin-nav-btn:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.25);
    }
    .admin-nav-btn.primary {
      background: rgba(226, 135, 67, 0.2);
      border-color: rgba(226, 135, 67, 0.4);
      color: #FDBA74;
    }
    .admin-nav-btn.primary:hover {
      background: rgba(226, 135, 67, 0.3);
      color: #FFF;
    }

    /* Top Operational Notice Banner */
    .admin-banner-notice {
      background: linear-gradient(90deg, rgba(220, 38, 38, 0.15) 0%, rgba(185, 28, 28, 0.08) 100%);
      border-bottom: 1px solid rgba(239, 68, 68, 0.3);
      padding: 0.75rem 1.25rem;
      font-size: 0.85rem;
      color: #FECACA;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }
    .admin-banner-text {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .admin-reset-btn {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #FFF;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.3rem 0.65rem;
      border-radius: 6px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
    }
    .admin-reset-btn:hover {
      background: rgba(255, 255, 255, 0.18);
    }

    /* Category Navigation */
    .cat-nav-strip {
      padding: 0.85rem 1.25rem 0.4rem;
      display: flex;
      gap: 0.5rem;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .cat-nav-strip::-webkit-scrollbar { display: none; }
    .cat-pill-btn {
      padding: 0.45rem 1.1rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #C8B9AF;
      font-size: 0.82rem;
      font-weight: 600;
      white-space: nowrap;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .cat-pill-btn.active, .cat-pill-btn:hover {
      background: #E28743;
      border-color: #E28743;
      color: #FFF;
      box-shadow: 0 4px 12px rgba(226, 135, 67, 0.35);
    }

    /* Drinks Grid */
    .drinks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.25rem;
      padding: 1rem 1.25rem 2rem;
      max-width: 1280px;
      margin: 0 auto;
      width: 100%;
    }
    .drink-card {
      background: rgba(25, 20, 17, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      position: relative;
      transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .drink-card:hover {
      transform: translateY(-2px);
      border-color: rgba(223, 155, 100, 0.3);
    }
    .drink-img-wrap {
      position: relative;
      width: 100%;
      height: 180px;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 0.85rem;
      background: #1F1916;
    }
    .drink-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .drink-badge {
      position: absolute;
      top: 0.65rem;
      left: 0.65rem;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(4px);
      color: #DF9B64;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.25rem 0.65rem;
      border-radius: 999px;
      border: 1px solid rgba(223, 155, 100, 0.35);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .drink-stock-tag {
      position: absolute;
      top: 0.65rem;
      right: 0.65rem;
      font-size: 0.72rem;
      font-weight: 800;
      padding: 0.25rem 0.65rem;
      border-radius: 999px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .drink-stock-tag.in-stock {
      background: rgba(16, 185, 129, 0.85);
      color: #FFF;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }
    .drink-stock-tag.sold-out {
      background: rgba(239, 68, 68, 0.9);
      color: #FFF;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    }
    .drink-card.item-sold-out {
      opacity: 0.75;
      border-color: rgba(239, 68, 68, 0.3);
    }
    .drink-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 0.35rem;
    }
    .drink-name {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
    }
    .drink-price {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #DF9B64;
      font-size: 1.1rem;
    }
    .drink-desc {
      font-size: 0.82rem;
      color: #A99B92;
      line-height: 1.45;
      margin-bottom: 1rem;
      flex: 1;
    }
    .btn-manage-options {
      width: 100%;
      padding: 0.75rem;
      border-radius: 12px;
      border: 1px solid rgba(223, 155, 100, 0.3);
      background: linear-gradient(135deg, rgba(226, 135, 67, 0.2) 0%, rgba(148, 77, 28, 0.2) 100%);
      color: #FDBA74;
      font-size: 0.88rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-manage-options:hover {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      box-shadow: 0 4px 14px rgba(226, 135, 67, 0.35);
    }

    /* Modal Sheet Styling */
    .oms-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .oms-modal-overlay.active {
      display: flex;
    }
    .oms-modal-box {
      background: #1C1613;
      border: 1px solid rgba(223, 155, 100, 0.3);
      border-radius: 24px;
      width: 100%;
      max-width: 520px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 1.75rem;
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.85);
      position: relative;
      animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalSlideUp {
      from { transform: translateY(20px) scale(0.97); opacity: 0; }
      to { transform: translateY(0) scale(1); opacity: 1; }
    }
    .oms-modal-close {
      position: absolute;
      top: 1.25rem;
      right: 1.25rem;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: none;
      color: #D6C7BC;
      display: grid;
      place-content: center;
      cursor: pointer;
      font-size: 1.25rem;
    }
    .oms-modal-close:hover {
      background: rgba(255, 255, 255, 0.16);
      color: #FFF;
    }

    /* Option Group & Pills */
    .option-group-label {
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #DF9B64;
      font-weight: 700;
      margin: 1.15rem 0 0.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .pill-radio-group {
      display: flex;
      flex-wrap: wrap;
      gap: 0.65rem;
    }
    
    /* Interactive Admin Option Pill */
    .admin-option-pill {
      flex: 1;
      min-width: 120px;
      text-align: center;
      padding: 0.75rem 0.85rem;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      font-size: 0.85rem;
      font-weight: 600;
      color: #E2D5CC;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      user-select: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.25rem;
    }
    .admin-option-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    /* Option In-Stock State */
    .admin-option-pill.is-in-stock {
      background: rgba(16, 185, 129, 0.12);
      border-color: rgba(16, 185, 129, 0.4);
      color: #E2D5CC;
    }
    .admin-option-pill.is-in-stock:hover {
      background: rgba(16, 185, 129, 0.2);
      border-color: rgba(16, 185, 129, 0.6);
    }
    .status-indicator-tag {
      font-size: 0.68rem;
      font-weight: 800;
      padding: 0.15rem 0.5rem;
      border-radius: 999px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .tag-in-stock {
      background: rgba(16, 185, 129, 0.25);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.45);
    }

    /* Option Cancelled Out (86'd / Sold Out) State */
    .admin-option-pill.is-sold-out {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.5);
      color: #F87171;
    }
    .admin-option-pill.is-sold-out .option-title-text {
      text-decoration: line-through;
      opacity: 0.75;
    }
    .admin-option-pill.is-sold-out:hover {
      background: rgba(239, 68, 68, 0.25);
      border-color: rgba(239, 68, 68, 0.7);
    }
    .tag-sold-out {
      background: rgba(239, 68, 68, 0.3);
      color: #F87171;
      border: 1px solid rgba(239, 68, 68, 0.6);
    }

    /* Toast Notification */
    .admin-toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      z-index: 3000;
      background: #1C1613;
      border: 1px solid rgba(223, 155, 100, 0.4);
      border-radius: 12px;
      padding: 0.85rem 1.25rem;
      color: #FFF;
      font-size: 0.88rem;
      font-weight: 600;
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      gap: 0.65rem;
      opacity: 0;
      transform: translateY(12px);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: none;
    }
    .admin-toast.active {
      opacity: 1;
      transform: translateY(0);
    }
    .admin-toast.toast-error {
      border-color: rgba(239, 68, 68, 0.5);
      background: #201211;
      color: #FCA5A5;
    }
    .admin-toast.toast-success {
      border-color: rgba(16, 185, 129, 0.5);
      background: #101F18;
      color: #A7F3D0;
    }
  </style>
</head>
<body class="admin-app-body">

  <!-- Header -->
  <header class="admin-order-header">
    <div class="admin-brand-wrap">
      <div class="admin-brand-logo">B</div>
      <div>
        <div class="admin-brand-title">Escobar Cafe</div>
        <div class="admin-badge-pill">🛡️ Admin Stock Control Portal</div>
      </div>
    </div>

    <!-- Navigation Hub -->
    <div class="admin-nav-actions">
      <a href="kds.php" class="admin-nav-btn primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
        Barista KDS Tablet
      </a>
      <a href="admin.php" class="admin-nav-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        Daily Ledger
      </a>
      <a href="index.php" class="admin-nav-btn" target="_blank">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        Live Customer View
      </a>
    </div>
  </header>

  <!-- Banner Operational Notice -->
  <div class="admin-banner-notice">
    <div class="admin-banner-text">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
      <span><strong>Live Inventory Control:</strong> Tap any drink or click <strong>Manage Options</strong> to toggle stock availability. Cancelling out an option (86'ing) immediately disables it on customer mobile screens.</span>
    </div>
    <button type="button" class="admin-reset-btn" id="btnResetAllStock">
      ↺ Reset All to In-Stock
    </button>
  </div>

  <!-- Category Pills Navigation -->
  <nav class="cat-nav-strip" aria-label="Menu categories">
    <button type="button" class="cat-pill-btn active" data-cat="all">All Drinks</button>
    <button type="button" class="cat-pill-btn" data-cat="house-coffee">House Coffee</button>
    <button type="button" class="cat-pill-btn" data-cat="matcha">Matcha</button>
    <button type="button" class="cat-pill-btn" data-cat="house-specials">House Specials</button>
    <button type="button" class="cat-pill-btn" data-cat="yogurt-soda">Yogurt / Soda</button>
  </nav>

  <!-- Main Catalog Grid -->
  <main class="drinks-grid" id="drinksCatalogGrid">
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #A99B92;">
      Loading catalog & stock registry...
    </div>
  </main>

  <!-- ADMIN CUSTOMIZATION MODAL (Matching User Screenshot with Interactive Stock Controls) -->
  <div class="oms-modal-overlay" id="adminCustomModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box">
      <button type="button" class="oms-modal-close" id="closeAdminModalBtn" aria-label="Close">&times;</button>
      
      <!-- Drink Header Row -->
      <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.25rem;">
        <img id="adminCustomImg" src="" alt="Drink preview" style="width: 72px; height: 72px; border-radius: 12px; object-fit: cover;">
        <div style="flex: 1;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <h3 id="adminCustomName" style="font-size: 1.35rem; font-weight: 700; color: #FFF; margin-bottom: 0.2rem;">Americano</h3>
              <p id="adminCustomPrice" style="font-family: var(--font-mono); font-size: 1.05rem; color: #DF9B64; font-weight: 700;">₱120.00</p>
            </div>
            <!-- Entire Drink Stock Toggle Button -->
            <button type="button" id="btnToggleDrinkStock" style="padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s ease;">
              Drink Status: In Stock
            </button>
          </div>
        </div>
      </div>

      <!-- Instructions Callout -->
      <div style="background: rgba(223, 155, 100, 0.1); border: 1px dashed rgba(223, 155, 100, 0.35); border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #FDBA74; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.45rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span><strong>Tap any option pill below</strong> to toggle its available stock status (In Stock ⇄ Cancelled Out / Sold Out).</span>
      </div>

      <!-- TEMPERATURE -->
      <div class="option-group-label">
        <span>TEMPERATURE</span>
        <span style="font-size: 0.72rem; color: #A99B92; font-weight: 500;">Tap to toggle</span>
      </div>
      <div class="pill-radio-group" id="adminTempGroup">
        <div class="admin-option-pill is-in-stock" data-opt="Iced">
          <span class="option-title-text">Iced</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="Hot">
          <span class="option-title-text">Hot</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
      </div>

      <!-- MILK SELECTION -->
      <div class="option-group-label">
        <span>MILK SELECTION</span>
        <span style="font-size: 0.72rem; color: #A99B92; font-weight: 500;">Tap to toggle</span>
      </div>
      <div class="pill-radio-group" id="adminMilkGroup">
        <div class="admin-option-pill is-in-stock" data-opt="Regular Milk">
          <span class="option-title-text">Regular Milk (+₱0)</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="Oat Milk">
          <span class="option-title-text">Oat Milk (+₱30)</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="Almond Milk">
          <span class="option-title-text">Almond Milk (+₱30)</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
      </div>

      <!-- SWEETNESS / SUGAR LEVEL -->
      <div class="option-group-label">
        <span>SWEETNESS / SUGAR LEVEL</span>
        <span style="font-size: 0.72rem; color: #A99B92; font-weight: 500;">Tap to toggle</span>
      </div>
      <div class="pill-radio-group" id="adminSweetnessGroup">
        <div class="admin-option-pill is-in-stock" data-opt="Normal (100%)">
          <span class="option-title-text">100% Normal</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="Less Sweet (75%)">
          <span class="option-title-text">75% Less Sweet</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="Half Sweet (50%)">
          <span class="option-title-text">50% Half Sweet</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
        <div class="admin-option-pill is-in-stock" data-opt="No Sugar (0%)">
          <span class="option-title-text">0% No Sugar</span>
          <span class="status-indicator-tag tag-in-stock">In Stock</span>
        </div>
      </div>

      <!-- Special Notes Preview -->
      <div class="option-group-label" style="margin-top: 1.25rem;">
        <span>SPECIAL NOTES / INSTRUCTIONS</span>
      </div>
      <div style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 0.75rem 1rem; color: #A99B92; font-size: 0.85rem; font-style: italic;">
        Customers can enter free-text preparation notes here (e.g. "Less ice", "Extra napkin").
      </div>

      <!-- Done / Save Action Button -->
      <button type="button" class="btn-manage-options" id="btnDoneStockModal" style="margin-top: 1.5rem; min-height: 52px; background: linear-gradient(135deg, #E28743 0%, #944D1C 100%); color: #FFF; font-size: 1rem; box-shadow: 0 4px 16px rgba(226, 135, 67, 0.4);">
        ✓ Done Managing Stock for this Drink
      </button>
    </div>
  </div>

  <!-- Toast Notification Pop -->
  <div class="admin-toast toast-success" id="adminToast">
    <span id="toastMessage">Stock status updated.</span>
  </div>

  <!-- JavaScript Controller -->
  <script src="js/system-dialog.js"></script>
  <script>
    (function initAdminStockPortal() {
      var menuData = [];
      var stockAvailability = {};
      var itemAvailability = {};
      var activeItem = null;

      // Toast notification helper
      var toastTimer = null;
      function showToast(message, isError) {
        var toast = document.getElementById('adminToast');
        toast.className = isError ? 'admin-toast toast-error active' : 'admin-toast toast-success active';
        document.getElementById('toastMessage').textContent = message;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function() {
          toast.classList.remove('active');
        }, 3000);
      }

      // Fetch Stock Status & Menu Items
      async function loadStockData() {
        try {
          var res = await fetch('api/stock.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            stockAvailability = data.availability || {};
            itemAvailability = data.item_availability || {};
          }
        } catch (e) {
          console.warn('Stock status fetch error:', e);
        }
      }

      async function loadMenu() {
        await loadStockData();

        try {
          var res = await fetch('api/menu.php');
          if (res.ok) {
            var data = await res.json();
            menuData = data.items || [];
          }
        } catch (e) {
          console.warn('Menu fetch error:', e);
        }

        if (!menuData || menuData.length === 0) {
          menuData = [
            { id: 'hc-spanish', category: 'house-coffee', name: 'Spanish Latte', price: 120, description: 'Velvety espresso combined with smooth fresh milk and rich condensed milk for a perfectly sweet kick.', image: 'images/menu/hc-spanish.webp', tags: ['Bestseller'] },
            { id: 'hc-americano', category: 'house-coffee', name: 'Americano', price: 120, description: 'Rich espresso poured over pure mountain spring water for a crisp, intense roast profile.', image: 'images/menu/hc-americano.webp', tags: ['House Coffee'] },
            { id: 'hc-salted-caramel', category: 'house-coffee', name: 'Salted Caramel', price: 120, description: 'Slow-cooked rich caramel paired with espresso, fresh milk, and a delicate touch of flaky sea salt.', image: 'images/menu/hc-salted-caramel.webp', tags: ['House Coffee'] },
            { id: 'mat-latte', category: 'matcha', name: 'Matcha Latte', price: 120, description: 'Stone-ground Uji green tea whisked fresh with silky milk for a soothing, umami-rich experience.', image: 'images/menu/mat-latte.webp', tags: ['Bestseller'] },
            { id: 'hs-choco', category: 'house-specials', name: 'Artisanal Choco', price: 90, description: 'Decadent, velvety chocolate milk made with pure cocoa and smooth fresh milk.', image: 'images/menu/hs-choco.webp', tags: ['House Specials'] }
          ];
        }

        renderCatalog('all');
      }

      function renderCatalog(filterCat) {
        var grid = document.getElementById('drinksCatalogGrid');
        var filtered = (filterCat === 'all')
          ? menuData
          : menuData.filter(function(i) { return i.category === filterCat; });

        grid.innerHTML = filtered.map(function(item) {
          var isItemInStock = (itemAvailability[item.id] !== false);
          var tagBadge = (item.tags && item.tags.length > 0)
            ? `<span class="drink-badge">${item.tags[0]}</span>`
            : '';
          var stockTagClass = isItemInStock ? 'drink-stock-tag in-stock' : 'drink-stock-tag sold-out';
          var stockTagText = isItemInStock ? '● In Stock' : '✕ Sold Out';
          var cardClass = isItemInStock ? 'drink-card' : 'drink-card item-sold-out';

          return `
            <div class="${cardClass}" id="card_${item.id}">
              <div class="drink-img-wrap">
                <img src="${item.image || 'images/menu/hc-spanish.webp'}" alt="${item.name}" loading="lazy">
                ${tagBadge}
                <span class="${stockTagClass}" data-item-id="${item.id}" title="Tap to toggle entire drink stock">${stockTagText}</span>
              </div>
              <div class="drink-meta-row">
                <h4 class="drink-name">${item.name}</h4>
                <span class="drink-price">₱${parseFloat(item.price).toFixed(2)}</span>
              </div>
              <p class="drink-desc">${item.description || 'Specialty handcrafted cafe beverage.'}</p>
              <button type="button" class="btn-manage-options btn-open-manage" data-item-id="${item.id}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                Manage Customization Options
              </button>
            </div>
          `;
        }).join('');

        // Attach Card Triggers
        document.querySelectorAll('.btn-open-manage').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var itemId = btn.getAttribute('data-item-id');
            openAdminCustomizer(itemId);
          });
        });

        // Quick Drink Stock Badge Toggles
        document.querySelectorAll('.drink-stock-tag').forEach(function(tag) {
          tag.addEventListener('click', function(e) {
            e.stopPropagation();
            var itemId = tag.getAttribute('data-item-id');
            toggleDrinkStock(itemId);
          });
        });
      }

      // Filter tabs
      document.querySelectorAll('.cat-pill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.cat-pill-btn').forEach(function(b) { b.classList.remove('active'); });
          btn.classList.add('active');
          renderCatalog(btn.getAttribute('data-cat'));
        });
      });

      // Open Admin Customizer Modal
      async function openAdminCustomizer(itemId) {
        await loadStockData();
        activeItem = menuData.find(function(i) { return i.id === itemId; }) || menuData[0];

        document.getElementById('adminCustomName').textContent = activeItem.name;
        document.getElementById('adminCustomImg').src = activeItem.image || 'images/menu/hc-spanish.webp';
        document.getElementById('adminCustomPrice').textContent = `₱${parseFloat(activeItem.price).toFixed(2)}`;

        updateDrinkStockButton();
        syncAdminPills();
        document.getElementById('adminCustomModal').classList.add('active');
      }

      function updateDrinkStockButton() {
        if (!activeItem) return;
        var isAvailable = (itemAvailability[activeItem.id] !== false);
        var btn = document.getElementById('btnToggleDrinkStock');
        if (isAvailable) {
          btn.textContent = '● Drink: In Stock';
          btn.style.background = 'rgba(16, 185, 129, 0.2)';
          btn.style.color = '#34D399';
          btn.style.border = '1px solid rgba(16, 185, 129, 0.5)';
        } else {
          btn.textContent = '✕ Drink: Sold Out (86\'d)';
          btn.style.background = 'rgba(239, 68, 68, 0.2)';
          btn.style.color = '#F87171';
          btn.style.border = '1px solid rgba(239, 68, 68, 0.5)';
        }
      }

      // Sync Option Pills visually to current stockAvailability map
      function syncAdminPills() {
        document.querySelectorAll('.admin-option-pill').forEach(function(pill) {
          var optKey = pill.getAttribute('data-opt');
          var isAvailable = (stockAvailability[optKey] !== false);

          pill.classList.toggle('is-in-stock', isAvailable);
          pill.classList.toggle('is-sold-out', !isAvailable);

          var tag = pill.querySelector('.status-indicator-tag');
          if (tag) {
            tag.className = isAvailable ? 'status-indicator-tag tag-in-stock' : 'status-indicator-tag tag-sold-out';
            tag.textContent = isAvailable ? 'In Stock' : '✕ Sold Out (86\'d)';
          }
        });
      }

      // Toggle Option Availability via REST API
      async function toggleOptionStock(optionKey) {
        var current = (stockAvailability[optionKey] !== false);
        var next = !current;

        // Optimistic UI update
        stockAvailability[optionKey] = next;
        syncAdminPills();

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ option_key: optionKey, is_available: next ? 1 : 0 })
          });
          var data = await res.json();
          if (data.success) {
            var msg = next 
              ? `"${optionKey}" restored to IN STOCK.` 
              : `"${optionKey}" CANCELLED OUT (Marked Sold Out / 86'd).`;
            showToast(msg, !next);
          } else {
            // Revert on error
            stockAvailability[optionKey] = current;
            syncAdminPills();
            showToast('Failed to update stock: ' + data.error, true);
          }
        } catch (e) {
          stockAvailability[optionKey] = current;
          syncAdminPills();
          showToast('Connection error: ' + e.message, true);
        }
      }

      // Toggle Entire Drink Stock
      async function toggleDrinkStock(itemId) {
        var current = (itemAvailability[itemId] !== false);
        var next = !current;

        itemAvailability[itemId] = next;
        updateDrinkStockButton();
        renderCatalog(document.querySelector('.cat-pill-btn.active').getAttribute('data-cat') || 'all');

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, is_available: next ? 1 : 0 })
          });
          var data = await res.json();
          if (data.success) {
            var drinkObj = menuData.find(function(i) { return i.id === itemId; });
            var name = drinkObj ? drinkObj.name : itemId;
            var msg = next ? `"${name}" is now IN STOCK.` : `"${name}" is now marked SOLD OUT.`;
            showToast(msg, !next);
          }
        } catch (e) {
          console.warn('Drink toggle error:', e);
        }
      }

      // Attach Click Handlers to Option Pills
      document.querySelectorAll('.admin-option-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
          var opt = pill.getAttribute('data-opt');
          toggleOptionStock(opt);
        });
      });

      // Entire Drink Button inside Modal
      document.getElementById('btnToggleDrinkStock').addEventListener('click', function() {
        if (activeItem) {
          toggleDrinkStock(activeItem.id);
        }
      });

      // Close Modal triggers
      document.getElementById('closeAdminModalBtn').addEventListener('click', function() {
        document.getElementById('adminCustomModal').classList.remove('active');
      });
      document.getElementById('btnDoneStockModal').addEventListener('click', function() {
        document.getElementById('adminCustomModal').classList.remove('active');
      });

      // Reset All to In-Stock
      document.getElementById('btnResetAllStock').addEventListener('click', async function() {
        var confirmed = await SystemDialog.confirm('Are you sure you want to reset ALL customization options and drinks to IN STOCK?', {
          title: 'Reset All Stock',
          confirmText: 'Reset to In Stock',
          isDestructive: true
        });
        if (!confirmed) return;

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reset_all: true })
          });
          var data = await res.json();
          if (data.success) {
            showToast('All options and items set to In Stock.', false);
            await loadMenu();
            syncAdminPills();
          }
        } catch (e) {
          showToast('Reset failed: ' + e.message, true);
        }
      });

      // Initial Load
      loadMenu();
    })();
  </script>
</body>
</html>
