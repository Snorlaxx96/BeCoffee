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
  <?php if (!$currentUser): ?>
  <script>
    window.location.replace('home.php?action=login&origin=takeout');
  </script>
  <?php endif; ?>
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

    /* 0.5s QUICK ORDER SUCCESS HUD */
    .order-success-hud {
      position: fixed;
      inset: 0;
      z-index: 3500;
      background: rgba(17, 13, 11, 0.82);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease-out;
    }
    .order-success-hud.active {
      opacity: 1;
      pointer-events: auto;
    }
    .hud-box {
      background: #1C1613;
      border: 1.5px solid rgba(16, 185, 129, 0.5);
      border-radius: 24px;
      padding: 1.75rem 2.25rem;
      text-align: center;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.25);
      transform: scale(0.85);
      transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .order-success-hud.active .hud-box {
      transform: scale(1);
    }
    .hud-icon-svg {
      width: 56px;
      height: 56px;
      margin: 0 auto 0.75rem;
      display: block;
    }
    .hud-circle {
      stroke: #10B981;
      stroke-width: 3.5;
      fill: none;
    }
    .hud-check {
      stroke: #10B981;
      stroke-width: 4;
      stroke-linecap: round;
      stroke-linejoin: round;
      fill: none;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: hudCheckAnim 0.35s 0.08s ease-out forwards;
    }
    @keyframes hudCheckAnim {
      to { stroke-dashoffset: 0; }
    }
    .hud-title {
      font-size: 1.25rem;
      font-weight: 800;
      color: #FFF;
      margin-bottom: 0.25rem;
      letter-spacing: -0.01em;
    }
    .hud-sub {
      font-size: 0.88rem;
      color: #6EE7B7;
      font-weight: 700;
      font-family: var(--font-mono);
    }

    /* DIGITAL PAID RECEIPT / PICKUP OVERLAY */
    .receipt-screen-overlay {
      position: fixed;
      inset: 0;
      background: rgba(17, 13, 11, 0.95);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      z-index: 2800;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.25rem;
      overflow-y: auto;
    }
    .receipt-screen-overlay.active {
      display: flex;
    }
    .receipt-card {
      width: 100%;
      max-width: 420px;
      background: #1C1613;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 22px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .receipt-header-strip {
      background: linear-gradient(135deg, rgba(223, 155, 100, 0.16) 0%, rgba(223, 155, 100, 0.04) 100%);
      border-bottom: 1px dashed rgba(255, 255, 255, 0.15);
      padding: 1.35rem 1.25rem 1rem;
      text-align: center;
    }
    .receipt-brand {
      font-size: 0.82rem;
      font-weight: 800;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #DF9B64;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
    }
    .receipt-giant-queue {
      font-size: 2.85rem;
      font-weight: 900;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1;
      margin: 0.6rem 0 0.4rem;
      letter-spacing: -0.03em;
    }
    .receipt-paid-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(16, 185, 129, 0.18);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #6EE7B7;
      padding: 0.3rem 0.75rem;
      border-radius: 999px;
      font-size: 0.76rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .receipt-meta-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.65rem;
      padding: 0.9rem 1.25rem;
      border-bottom: 1px dashed rgba(255, 255, 255, 0.12);
      font-size: 0.82rem;
    }
    .receipt-meta-item {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
    }
    .receipt-meta-label {
      color: #8C7C72;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .receipt-meta-val {
      color: #FFF;
      font-weight: 700;
    }
    .receipt-items-body {
      padding: 1rem 1.25rem;
      max-height: 180px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }
    .receipt-line {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      font-size: 0.85rem;
    }
    .receipt-line-left {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
      padding-right: 0.5rem;
    }
    .receipt-line-name {
      color: #FFF;
      font-weight: 600;
    }
    .receipt-line-specs {
      font-size: 0.74rem;
      color: #A99B92;
    }
    .receipt-line-price {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #FDBA74;
      white-space: nowrap;
    }
    .receipt-total-row {
      padding: 0.85rem 1.25rem;
      background: rgba(0, 0, 0, 0.25);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.95rem;
      font-weight: 700;
      color: #FFF;
    }
    .receipt-total-num {
      font-family: var(--font-mono);
      font-size: 1.25rem;
      color: #10B981;
    }
    .receipt-staff-notice {
      margin: 0.85rem 1.25rem 0.5rem;
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.35);
      border-radius: 12px;
      padding: 0.75rem 0.85rem;
      text-align: center;
      font-size: 0.82rem;
      color: #A7F3D0;
      line-height: 1.4;
    }
    .receipt-staff-notice strong {
      display: block;
      color: #6EE7B7;
      margin-bottom: 0.2rem;
      font-size: 0.85rem;
    }
    .receipt-actions {
      padding: 0.85rem 1.25rem 1.25rem;
      display: flex;
      gap: 0.65rem;
    }
    .btn-receipt-order-more {
      flex: 1;
      min-height: 44px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 10px;
      color: #FFF;
      font-weight: 600;
      font-size: 0.85rem;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .btn-receipt-order-more:hover {
      background: rgba(255, 255, 255, 0.14);
    }
    .btn-receipt-exit {
      min-height: 44px;
      padding: 0 1.1rem;
      background: none;
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 10px;
      color: #A99B92;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: color 0.15s ease, border-color 0.15s ease;
    }
    .btn-receipt-exit:hover {
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.35);
    }

    /* GCASH PAYMENT MODAL SHEET (WIDE HORIZONTAL LANDSCAPE LAYOUT) */
    .gcash-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(14, 10, 8, 0.94);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      z-index: 2900;
      display: none;
      align-items: flex-start;
      justify-content: center;
      padding: 1.25rem;
      overflow-y: auto;
    }
    .gcash-modal-overlay.active {
      display: flex;
    }
    .gcash-modal-card {
      margin: auto;
      width: 100%;
      max-width: 720px;
      background: #16100D;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9), 0 0 20px rgba(0, 0, 0, 0.5);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .gcash-card-header {
      background: #130D0A;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      padding: 0.9rem 1.35rem;
      color: #FFF;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .gcash-header-brand {
      display: flex;
      align-items: center;
      gap: 0.65rem;
    }
    .gcash-header-brand h3 {
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: -0.01em;
      margin: 0;
      color: #FAF7F2;
    }
    .gcash-badge-pill {
      background: rgba(0, 125, 254, 0.12);
      border: 1px solid rgba(0, 125, 254, 0.3);
      color: #60A5FA;
      border-radius: 999px;
      font-size: 0.68rem;
      font-weight: 700;
      padding: 0.12rem 0.5rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .gcash-badge-instapay {
      background: rgba(223, 155, 100, 0.12);
      border: 1px solid rgba(223, 155, 100, 0.3);
      color: #DF9B64;
      border-radius: 999px;
      font-size: 0.68rem;
      font-weight: 700;
      padding: 0.12rem 0.5rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .gcash-btn-close {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      color: #C8B9AF;
      width: 28px;
      height: 28px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      line-height: 1;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .gcash-btn-close:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.2);
    }
    .gcash-modal-grid {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 1.5rem;
      padding: 1.35rem 1.5rem;
      align-items: stretch;
    }
    /* Left Column: QR Code Display */
    .gcash-grid-col-qr {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      background: #110B09;
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 0.85rem;
    }
    .gcash-qr-showcase {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .gcash-qr-img {
      width: 100%;
      max-width: 220px;
      height: auto;
      display: block;
      border-radius: 8px;
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.6);
    }
    .gcash-qr-caption {
      font-size: 0.74rem;
      color: #9E8E81;
      text-align: center;
      font-weight: 600;
      letter-spacing: 0.02em;
    }
    /* Right Column: Amount, Channels, Auto-detect & Action */
    .gcash-grid-col-info {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 1rem;
    }
    .gcash-amount-block {
      background: #110B09;
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 1rem 1.15rem;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .gcash-amount-label {
      font-size: 0.72rem;
      color: #9E8E81;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 700;
    }
    .gcash-amount-val {
      font-family: var(--font-mono);
      font-size: 2.35rem;
      font-weight: 800;
      color: #FAF7F2;
      line-height: 1;
      letter-spacing: -0.02em;
    }
    .gcash-channels-wrap {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }
    .gcash-channels-title {
      font-size: 0.7rem;
      font-weight: 700;
      color: #8E7E73;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .gcash-channels-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }
    .channel-chip {
      font-size: 0.72rem;
      color: #C8B9AF;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      padding: 0.22rem 0.55rem;
      border-radius: 4px;
      font-weight: 600;
    }
    .gcash-autodetect-hint {
      background: rgba(223, 155, 100, 0.08);
      border: 1px solid rgba(223, 155, 100, 0.2);
      border-radius: 10px;
      padding: 0.75rem 0.9rem;
      font-size: 0.78rem;
      color: #E2D5CC;
      line-height: 1.4;
      display: flex;
      align-items: flex-start;
      gap: 0.55rem;
      box-sizing: border-box;
    }
    .gcash-autodetect-hint strong {
      color: #DF9B64;
    }
    .btn-confirm-gcash {
      width: 100%;
      min-height: 48px;
      background: var(--brand-accent, #DF9B64);
      border: none;
      border-radius: 10px;
      color: #140E0C;
      font-size: 0.92rem;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.15s ease;
      box-shadow: 0 4px 18px rgba(223, 155, 100, 0.25);
    }
    .btn-confirm-gcash:hover {
      background: #E8A876;
      transform: translateY(-1px);
    }
    @media (max-width: 640px) {
      .gcash-modal-card {
        max-width: 440px;
      }
      .gcash-modal-grid {
        grid-template-columns: 1fr;
        gap: 1.15rem;
        padding: 1.15rem;
      }
      .gcash-qr-img {
        max-width: 170px;
      }
      .gcash-badge-pill,
      .gcash-badge-instapay {
        display: none;
      }
      .gcash-amount-val {
        font-size: 1.95rem;
      }
    }
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

  </main>
  
  <!-- 0.5s QUICK ORDER SUCCESS HUD -->
  <div class="order-success-hud" id="orderSuccessHud" aria-live="assertive" role="alert">
    <div class="hud-box">
      <svg class="hud-icon-svg" viewBox="0 0 52 52" aria-hidden="true">
        <circle class="hud-circle" cx="26" cy="26" r="23"/>
        <path class="hud-check" d="M14 27l8 8 16-17"/>
      </svg>
      <div class="hud-title" id="hudSuccessTitle">Order Transmitted!</div>
      <div class="hud-sub" id="hudSuccessSub">Queue #00 · Direct to Barista</div>
    </div>
  </div>

  <!-- DIGITAL ORDER RECEIPT SCREEN (Take-Out Pickup & SMS Notification) -->
  <div class="receipt-screen-overlay" id="orderReceiptScreen" role="dialog" aria-modal="true" aria-labelledby="receiptQueueNum">
    <div class="receipt-card">
      <div class="receipt-header-strip">
        <div class="receipt-brand">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
          <span>BeCoffee Roastery · Take-Out</span>
        </div>
        <div class="receipt-giant-queue" id="receiptQueueNum">#00</div>
        <div class="receipt-paid-pill" id="receiptPaidBadge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
          <span id="receiptPaidText">PAID · Take-Out Pickup</span>
        </div>
      </div>

      <div class="receipt-meta-grid">
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Customer</span>
          <span class="receipt-meta-val" id="receiptCustomerVal">—</span>
        </div>
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Contact Phone</span>
          <span class="receipt-meta-val" id="receiptPhoneVal" style="font-family: var(--font-mono); font-size: 0.8rem;">—</span>
        </div>
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Order Ref</span>
          <span class="receipt-meta-val" id="receiptRefVal" style="font-family: var(--font-mono); font-size: 0.78rem;">—</span>
        </div>
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Time Placed</span>
          <span class="receipt-meta-val" id="receiptTimeVal">Just now</span>
        </div>
      </div>

      <div class="receipt-items-body" id="receiptItemsBody">
        <!-- Injected line items -->
      </div>

      <div class="receipt-total-row">
        <span>Total Amount</span>
        <span class="receipt-total-num" id="receiptTotalVal">₱0.00</span>
      </div>

      <div class="receipt-staff-notice" id="receiptStaffNotice">
        <strong>📱 Cellular SMS Notification Active</strong>
        We will text your phone (<span id="receiptSmsPhoneNotice">09...</span>) as soon as your drinks are freshly prepared and ready for pickup at the counter!
      </div>

      <div class="receipt-actions">
        <button type="button" class="btn-receipt-order-more" id="btnReceiptOrderMore">Order More Drinks</button>
        <button type="button" class="btn-receipt-exit" id="btnReceiptExit">Exit</button>
      </div>
    </div>
  </div>

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
          <div class="pill-radio-opt active" data-val="No Add-on">No Add-on (+₱0)</div>
          <div class="pill-radio-opt" data-val="Regular Milk">Regular Milk (+₱0)</div>
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
        var milk = milkOpt ? milkOpt.getAttribute('data-val') : 'No Add-on';

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

      // 5. Confirm Order Action with GCash Auto-Advance Support
      var gcashAwaitingReturn = false;
      var gcashHasLeftTab = false;
      document.querySelectorAll('#paymentRadioGroup .pill-radio-opt').forEach(function(pill) {
        pill.onclick = function() {
          document.querySelectorAll('#paymentRadioGroup .pill-radio-opt').forEach(function(p) { p.classList.remove('active'); });
          pill.classList.add('active');
        };
      });

      var checkoutBtn = document.getElementById('btnConfirmTakeoutOrder');
      checkoutBtn.onclick = function() {
        if (cart.length === 0) return;

        var nameInput = document.getElementById('checkoutCustName');
        var phoneInput = document.getElementById('checkoutCustPhone');
        var name = nameInput.value.trim();
        var phone = phoneInput.value.trim();
        var paymentOpt = document.querySelector('#paymentRadioGroup .pill-radio-opt.active');
        var payMethod = paymentOpt ? paymentOpt.getAttribute('data-val') : 'cash';

        if (!name || name.length < 2) {
          SystemDialog.alert('Please enter your full name for order pickup.', { title: 'Name Required', type: 'warning' });
          nameInput.focus();
          return;
        }
        if (!phone || phone.length < 7) {
          SystemDialog.alert('Please enter a valid mobile number (+63) for order alerts.', { title: 'Mobile Number Required', type: 'warning' });
          phoneInput.focus();
          return;
        }

        var subtotal = cart.reduce(function(sum, item) { return sum + (item.unit_price * item.quantity); }, 0);
        var grandTotal = subtotal + 15.00;

        executeTakeoutSubmission(name, phone, payMethod, '', grandTotal);
      };

      async function executeTakeoutSubmission(name, phone, payMethod, gcashRef, grandTotal) {
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
            payment_reference: gcashRef || '',
            items: cart
          };

          var cartSnapshot = JSON.parse(JSON.stringify(cart));

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
            triggerSuccessHud(data.queue_number);
            setTimeout(function() {
              showTakeoutReceipt(data.order_reference, data.queue_number, name, phone, payMethod, cartSnapshot, grandTotal, gcashRef);
            }, 500);
          } else {
            await SystemDialog.alert(data.error || 'Could not place order. Please try again.', { title: 'Order Issue', type: 'danger' });
          }
        } catch(e) {
          await SystemDialog.alert('Network connection failure. Please verify connection and try again.', { title: 'Connection Error', type: 'danger' });
        } finally {
          checkoutBtn.disabled = false;
          checkoutBtn.innerHTML = '<span>Confirm Take-out Order</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        }
      }

      // 6. 0.5s HUD & Digital Take-Out Receipt Card Logic
      function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
      }

      function triggerSuccessHud(queueNum) {
        var hud = document.getElementById('orderSuccessHud');
        var sub = document.getElementById('hudSuccessSub');
        if (!hud) return;
        if (sub) sub.textContent = `Queue #${queueNum} · Direct to Barista`;
        hud.classList.add('active');
        setTimeout(function() {
          hud.classList.remove('active');
        }, 550);
      }

      var activeRef = null;
      var pollTimer = null;

      function showTakeoutReceipt(ref, queue, name, phone, payMethod, items, total, gcashRef) {
        activeRef = ref;
        sessionStorage.setItem('becoffee_last_order_ref', ref);
        sessionStorage.setItem('becoffee_last_order_queue', queue);

        var receipt = document.getElementById('orderReceiptScreen');
        if (!receipt) return;

        var queueEl = document.getElementById('receiptQueueNum');
        if (queueEl) queueEl.textContent = '#' + queue;

        var refEl = document.getElementById('receiptRefVal');
        if (refEl) refEl.textContent = ref || '—';

        var custEl = document.getElementById('receiptCustomerVal');
        if (custEl) custEl.textContent = name || 'Valued Guest';

        var phoneEl = document.getElementById('receiptPhoneVal');
        if (phoneEl) phoneEl.textContent = phone || '—';

        var isGcash = (payMethod === 'gcash');
        var payBadge = document.getElementById('receiptPaidBadge');
        var payText = document.getElementById('receiptPaidText');
        if (payBadge && payText) {
          if (isGcash) {
            payBadge.style.background = 'rgba(223, 155, 100, 0.2)';
            payBadge.style.borderColor = 'rgba(223, 155, 100, 0.45)';
            payBadge.style.color = '#FAF7F2';
            payText.textContent = 'GCASH · Pay upon Pickup';
          } else {
            payBadge.style.background = 'rgba(16, 185, 129, 0.15)';
            payBadge.style.borderColor = 'rgba(16, 185, 129, 0.35)';
            payBadge.style.color = '#34D399';
            payText.textContent = 'CASH · Pay at Counter';
          }
        }

        var noticeEl = document.getElementById('receiptStaffNotice');
        if (noticeEl) {
          noticeEl.innerHTML = isGcash
            ? `<strong>📱 Pay via GCash at Counter</strong>Please send <strong>₱${parseFloat(total).toFixed(2)}</strong> via GCash (0994 873 •••• · BeCoffee Roastery). Show your GCash transaction receipt to the barista when claiming your take-out order at the counter!`
            : `<strong>📱 Cellular SMS Notification Active</strong>We will text your phone (<strong>${escapeHtml(phone || '')}</strong>) as soon as your drinks are freshly prepared and ready for pickup at the counter!`;
        }

        var totalEl = document.getElementById('receiptTotalVal');
        if (totalEl) totalEl.textContent = '₱' + parseFloat(total).toFixed(2);

        var now = new Date();
        var timeEl = document.getElementById('receiptTimeVal');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        var itemsContainer = document.getElementById('receiptItemsBody');
        if (itemsContainer && items && items.length > 0) {
          itemsContainer.innerHTML = items.map(function(it) {
            var specs = [it.temperature, it.size, it.milk_option || it.milk, it.sweetness_level || it.sweetness].filter(Boolean).join(' · ');
            var linePrice = (parseFloat(it.unit_price || it.price) || 0) * (parseInt(it.quantity, 10) || 1);
            return `
              <div class="receipt-line">
                <div class="receipt-line-left">
                  <span class="receipt-line-name">${it.quantity}x ${escapeHtml(it.name || it.item_name)}</span>
                  <span class="receipt-line-specs">${escapeHtml(specs)}</span>
                </div>
                <span class="receipt-line-price">₱${linePrice.toFixed(2)}</span>
              </div>
            `;
          }).join('');
        }

        receipt.classList.add('active');

        // Start background polling to notify user if they keep the tab open
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollReceiptStatus, 3000);
      }

      async function pollReceiptStatus() {
        if (!activeRef) return;
        try {
          var res = await fetch('api/orders.php?reference=' + encodeURIComponent(activeRef), { cache: 'no-store' });
          if (!res.ok) return;
          var data = await res.json();
          if (!data.order) return;
          var ord = data.order;
          if (ord.status === 'completed') {
            if (pollTimer) clearInterval(pollTimer);
            var badge = document.getElementById('receiptPaidBadge');
            var text = document.getElementById('receiptPaidText');
            if (badge && text) {
              badge.style.background = 'rgba(16, 185, 129, 0.35)';
              badge.style.borderColor = '#10B981';
              text.textContent = '✓ ORDER READY FOR PICKUP!';
            }
            // Gentle chime
            try {
              var ctx = new (window.AudioContext || window.webkitAudioContext)();
              var osc = ctx.createOscillator();
              var g = ctx.createGain();
              osc.type = 'triangle';
              osc.frequency.setValueAtTime(659.25, ctx.currentTime);
              osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
              g.gain.setValueAtTime(0.2, ctx.currentTime);
              g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
              osc.connect(g);
              g.connect(ctx.destination);
              osc.start();
              osc.stop(ctx.currentTime + 0.62);
            } catch(e) {}
          }
        } catch(e) {}
      }

      var btnMore = document.getElementById('btnReceiptOrderMore');
      if (btnMore) {
        btnMore.onclick = function() {
          if (pollTimer) clearInterval(pollTimer);
          var receipt = document.getElementById('orderReceiptScreen');
          if (receipt) receipt.classList.remove('active');
          renderCatalog('all');
        };
      }

      var btnExit = document.getElementById('btnReceiptExit');
      if (btnExit) {
        btnExit.onclick = function() {
          if (pollTimer) clearInterval(pollTimer);
          var receipt = document.getElementById('orderReceiptScreen');
          if (receipt) receipt.classList.remove('active');
        };
      }

      // Check for existing active order on page load
      loadMenuCatalog();
      var savedRef = sessionStorage.getItem('becoffee_last_order_ref');
      var savedQueue = sessionStorage.getItem('becoffee_last_order_queue');
      if (savedRef && savedQueue) {
        fetch('api/orders.php?reference=' + encodeURIComponent(savedRef), { cache: 'no-store' })
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data && data.order && data.order.status !== 'completed' && data.order.status !== 'cancelled') {
              showTakeoutReceipt(data.order.order_reference, data.order.queue_number, data.order.customer_name, data.order.customer_phone, data.order.payment_method, data.order.items, data.order.total_amount);
            }
          }).catch(function() {});
      }
    })();
  </script>
</body>
</html>
