<?php
require_once __DIR__ . '/api/config.php';
$currentUser = getAuthenticatedUser();
$isWalkin = (isset($_GET['mode']) && $_GET['mode'] === 'walkin');
$isTableQR = !empty($_GET['table']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>BeCoffee | Direct Mobile Ordering & Digital Menu</title>
  <meta name="description" content="Order handcrafted specialty coffee, pour-overs, and matcha directly from your table or dorm at BeCoffee.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=16.0">
  <?php if (!$currentUser && empty($_GET['table'])): ?>
  <script>
    window.location.replace('home.php');
  </script>
  <?php endif; ?>
  <style>
    /* Direct Order App Layout */
    body.order-app-body {
      --order-topbar-height: 64px;
      --order-catnav-height: 52px;
      --order-sticky-gap: 16px;
      background: #14100E;
      color: #F5EBE1;
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-bottom: 90px;
    }

    /* Sticky Order App Header */
    .order-header {
      background: #171210;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.6rem 1.25rem;
      height: var(--order-topbar-height);
      min-height: var(--order-topbar-height);
      box-sizing: border-box;
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
      border-radius: 8px;
      background: #E28743;
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
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

    /* Top Action Links (Editorial Anti-Slop) */
    .order-top-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .staff-header-actions {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .header-action-btn,
    .btn-story-link {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      min-height: 44px;
      padding: 0.35rem 0.65rem;
      background: transparent;
      border: none;
      font-family: inherit;
      font-size: 0.8125rem;
      font-weight: 500;
      line-height: 1;
      text-decoration: none;
      cursor: pointer;
      border-radius: 6px;
      color: #A8988C;
      transition: color 0.15s ease, opacity 0.15s ease;
      white-space: nowrap;
      box-sizing: border-box;
    }
    .header-action-btn:focus-visible,
    .btn-story-link:focus-visible {
      outline: 2px solid #DF9B64;
      outline-offset: 2px;
    }
    .header-action-btn svg {
      flex-shrink: 0;
      stroke: currentColor;
      transition: transform 0.15s ease;
    }
    .header-action-btn.action-kds {
      color: #DF9B64;
      font-weight: 600;
      letter-spacing: 0.01em;
    }
    .header-action-btn.action-kds:hover {
      color: #FFF;
    }
    .header-action-btn.action-kds:hover svg {
      transform: translateY(-1px);
    }
    .header-action-btn.action-logout {
      color: #8E8279;
      letter-spacing: 0.01em;
    }
    .header-action-btn.action-logout:hover {
      color: #F87171;
    }
    .header-action-btn.action-logout:hover svg {
      transform: translateX(1px);
    }
    .header-action-divider {
      width: 1px;
      height: 16px;
      background: rgba(255, 255, 255, 0.12);
      margin: 0 0.15rem;
      flex-shrink: 0;
    }
    .header-action-btn.action-story,
    .btn-story-link {
      color: #A8988C;
      font-weight: 500;
    }
    .header-action-btn.action-story:hover,
    .btn-story-link:hover {
      color: #FFF;
      background: transparent;
    }
    .header-action-btn.action-leave,
    .btn-leave-link {
      color: #8E8279 !important;
      border: none !important;
      background: transparent !important;
    }
    .header-action-btn.action-leave:hover,
    .btn-leave-link:hover {
      color: #F87171 !important;
      background: transparent !important;
      border: none !important;
    }
    @media (max-width: 480px) {
      body.order-app-body {
        --order-topbar-height: 54px;
        --order-catnav-height: 48px;
        --order-sticky-gap: 12px;
      }
      .order-header {
        padding: 0.45rem 0.65rem;
        gap: 0.35rem;
      }
      .order-brand-tag {
        display: none;
      }
      .order-brand-logo {
        width: 32px;
        height: 32px;
        font-size: 1rem;
      }
      .order-brand-title {
        font-size: 1rem;
      }
      .table-context-pill {
        padding: 0.25rem 0.5rem;
        font-size: 0.72rem;
      }
      .order-top-actions {
        gap: 0.2rem;
      }
      .staff-header-actions {
        gap: 0.15rem;
      }
      .header-action-btn,
      .btn-story-link {
        padding: 0.25rem 0.45rem;
        font-size: 0.75rem;
        gap: 0.3rem;
        min-height: 38px;
      }
      .header-action-divider {
        height: 14px;
        margin: 0 0.05rem;
      }
    }

    /* Table Inactivity Expiration Modal */
    .table-expired-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(17, 13, 11, 0.88);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      z-index: 999999;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    .table-expired-card {
      background: #1C1613;
      border: 1px solid rgba(226, 135, 67, 0.4);
      border-radius: 24px;
      padding: 2.25rem 2rem;
      max-width: 420px;
      width: 100%;
      text-align: center;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7);
      animation: modalFadeIn 0.3s ease;
    }
    .expired-icon {
      font-size: 2.8rem;
      margin-bottom: 0.85rem;
    }
    .expired-title {
      font-family: var(--font-serif);
      font-size: 1.5rem;
      color: #FFF;
      margin-bottom: 0.6rem;
    }
    .expired-desc {
      font-size: 0.9rem;
      color: #D1C5BD;
      line-height: 1.5;
      margin-bottom: 1.5rem;
    }
    .btn-expired-leave {
      width: 100%;
      padding: 0.8rem 1.5rem;
      border-radius: 999px;
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      border: none;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(226, 135, 67, 0.35);
      transition: all 0.2s ease;
    }
    .btn-expired-leave:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(226, 135, 67, 0.5);
    }

    /* Sticky Category Nav with Prep Timer */
    .order-category-nav {
      position: sticky;
      top: var(--order-topbar-height);
      height: var(--order-catnav-height);
      min-height: var(--order-catnav-height);
      box-sizing: border-box;
      z-index: 90;
      background: #171210;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.5rem 1.25rem;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      display: flex;
      justify-content: space-between;
      align-items: center;
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
    .cat-pill-group::-webkit-scrollbar {
      display: none;
    }
    .cat-pill-btn {
      padding: 0.4rem 0.95rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: transparent;
      color: #A99B92;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.15s ease;
      flex-shrink: 0;
    }
    .cat-pill-btn:hover {
      color: #FFF;
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.15);
    }
    .cat-pill-btn.active {
      background: #E28743;
      color: #FFF;
      border-color: #E28743;
      font-weight: 700;
      box-shadow: none;
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
        padding: 0.35rem 0.65rem;
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

    /* Main Layout: Split Screen on Desktop (Catalog + Kiosk Sidebar) */
    .order-app-container {
      max-width: 1440px;
      margin: var(--order-sticky-gap) auto;
      padding: 0 1.5rem;
      display: flex;
      gap: 1.75rem;
      align-items: flex-start;
      width: 100%;
      box-sizing: border-box;
    }
    .order-catalog-wrap {
      flex: 1;
      min-width: 0;
      width: auto;
      margin: 0;
      padding: 0;
      max-width: none;
    }
    .catalog-section-title {
      font-family: var(--font-serif);
      font-size: 1.45rem;
      color: #FFF;
      margin: 1.25rem 0 0.85rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.25rem;
    }
    @media (max-width: 600px) {
      .catalog-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
    }

    /* Kiosk Order Sidebar (Flat Matte Register Panel) */
    .kiosk-order-sidebar {
      width: 380px;
      flex-shrink: 0;
      position: sticky;
      top: calc(var(--order-topbar-height) + var(--order-catnav-height) + var(--order-sticky-gap));
      height: calc(100vh - (var(--order-topbar-height) + var(--order-catnav-height) + (var(--order-sticky-gap) * 2)));
      max-height: calc(100vh - (var(--order-topbar-height) + var(--order-catnav-height) + (var(--order-sticky-gap) * 2)));
      background: #181311;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-sizing: border-box;
      z-index: 80;
    }

    /* Kiosk Sidebar Panels */
    .kiosk-panel {
      display: flex;
      flex-direction: column;
      height: 100%;
      min-height: 0;
      flex: 1;
    }

    /* Kiosk Sidebar Header */
    .kiosk-sidebar-header {
      padding: 1rem 1.15rem 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: #181311;
      flex-shrink: 0;
    }
    .kiosk-header-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.65rem;
    }
    .kiosk-header-title-wrap {
      display: flex;
      align-items: baseline;
      gap: 0.4rem;
    }
    .kiosk-header-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #FFFFFF;
      margin: 0;
      letter-spacing: -0.01em;
    }
    .kiosk-item-badge {
      font-size: 0.85rem;
      font-family: inherit;
      font-weight: 500;
      color: #8C7E75;
      background: none;
      border: none;
      padding: 0;
    }
    .kiosk-btn-clear {
      background: none;
      border: none;
      color: #8C7E75;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      padding: 0.2rem 0.4rem;
      border-radius: 6px;
      transition: color 0.15s ease;
    }
    .kiosk-btn-clear:hover {
      color: #EF4444;
    }

    /* Segmented Dining Selector */
    .kiosk-segmented-dining {
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 8px;
      padding: 3px;
      gap: 3px;
    }
    .kiosk-seg-btn {
      background: none;
      border: none;
      color: #9E8E85;
      font-size: 0.82rem;
      font-weight: 600;
      padding: 0.45rem 0.5rem;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
      transition: all 0.15s ease;
    }
    .kiosk-seg-btn:hover:not(.active) {
      color: #FFF;
    }
    .kiosk-seg-btn.active {
      background: #E28743;
      color: #FFFFFF;
      font-weight: 700;
      box-shadow: none;
    }

    /* Scrollable Items Container */
    .kiosk-items-scroll {
      flex: 1;
      min-height: 0;
      overflow-y: auto;
      padding: 0.25rem 1.15rem;
      display: flex;
      flex-direction: column;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
    }
    .kiosk-items-scroll::-webkit-scrollbar {
      width: 4px;
    }
    .kiosk-items-scroll::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 4px;
    }

    /* Empty State */
    .kiosk-empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2.5rem 1.25rem;
      color: #8C7E75;
      margin: auto 0;
    }
    .kiosk-empty-icon {
      width: 32px;
      height: 32px;
      border-radius: 0;
      background: none;
      border: none;
      display: grid;
      place-content: center;
      color: #786D65;
      margin-bottom: 0.75rem;
    }
    .kiosk-empty-state h4 {
      font-size: 0.92rem;
      font-weight: 600;
      color: #D1C5BD;
      margin: 0 0 0.3rem;
    }
    .kiosk-empty-state p {
      font-size: 0.8rem;
      color: #7E7068;
      margin: 0;
      line-height: 1.4;
    }

    /* Individual Kiosk Order Card (Flat Row) */
    .kiosk-item-card {
      background: transparent;
      border: none;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 0;
      padding: 0.75rem 0;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    .kiosk-item-card:last-child {
      border-bottom: none;
    }
    .kiosk-item-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.5rem;
    }
    .kiosk-item-name {
      font-size: 0.92rem;
      font-weight: 700;
      color: #FFFFFF;
      line-height: 1.25;
    }
    .kiosk-item-price {
      font-family: var(--font-mono);
      font-weight: 700;
      font-size: 0.92rem;
      color: #E28743;
      white-space: nowrap;
    }
    .kiosk-item-specs {
      font-size: 0.75rem;
      color: #8C7E75;
      line-height: 1.35;
    }
    .kiosk-item-note {
      font-size: 0.72rem;
      color: #B5A8A0;
      background: rgba(0, 0, 0, 0.25);
      border-left: none;
      padding: 0.2rem 0.4rem;
      border-radius: 4px;
    }
    .kiosk-item-foot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 0.25rem;
      padding-top: 0.35rem;
      border-top: none;
    }
    .kiosk-item-stepper {
      display: flex;
      align-items: center;
      background: rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 2px 4px;
      gap: 2px;
    }
    .kiosk-step-btn {
      background: none;
      border: none;
      color: #FFF;
      width: 24px;
      height: 24px;
      display: grid;
      place-content: center;
      cursor: pointer;
      border-radius: 4px;
      transition: background 0.15s ease;
    }
    .kiosk-step-btn:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    .kiosk-step-val {
      font-family: var(--font-mono);
      font-size: 0.85rem;
      font-weight: 700;
      color: #FFF;
      width: 22px;
      text-align: center;
    }
    .kiosk-btn-remove {
      background: none;
      border: none;
      color: #786D65;
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      transition: color 0.15s ease;
    }
    .kiosk-btn-remove:hover {
      color: #EF4444;
    }

    /* Kiosk Footer: Totals & Primary CTA */
    .kiosk-sidebar-footer {
      padding: 0.9rem 1.15rem 1.1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: #181311;
      flex-shrink: 0;
      margin-top: auto;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }
    .kiosk-calc-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.82rem;
      color: #8C7E75;
    }
    .kiosk-calc-row.total {
      font-size: 0.92rem;
      font-weight: 700;
      color: #C8B9AF;
      padding-top: 0.65rem;
      border-top: 1px dashed rgba(255, 255, 255, 0.12);
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .kiosk-calc-total-val {
      font-family: var(--font-mono);
      font-size: 1.85rem;
      font-weight: 900;
      letter-spacing: -0.02em;
      color: #DF9B64;
      line-height: 1;
    }
    .btn-kiosk-checkout {
      width: 100%;
      min-height: 46px;
      background: #E28743;
      border: none;
      border-radius: 10px;
      color: #FFFFFF;
      font-size: 0.94rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: none;
      transition: background 0.15s ease;
    }
    .btn-kiosk-checkout:hover:not(:disabled) {
      background: #D97706;
    }
    .btn-kiosk-checkout:disabled {
      opacity: 0.35;
      cursor: not-allowed;
      background: #2E2420;
      color: #7E7068;
      box-shadow: none;
    }

    /* In-place Checkout Panel inside Kiosk Sidebar */
    .kiosk-checkout-panel {
      display: flex;
      flex-direction: column;
      flex: 1;
      min-height: 0;
      overflow-y: auto;
      padding: 1rem 1.15rem;
      gap: 0.85rem;
    }
    .kiosk-checkout-back-btn {
      align-self: flex-start;
      background: none;
      border: none;
      color: #E28743;
      font-size: 0.82rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0;
      margin-bottom: 0.35rem;
    }
    .kiosk-checkout-back-btn:hover {
      text-decoration: underline;
    }
    .kiosk-field-group {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    .kiosk-field-label {
      font-size: 0.74rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #E28743;
      font-weight: 700;
    }
    .kiosk-input {
      width: 100%;
      background: rgba(0, 0, 0, 0.4) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 8px;
      padding: 0.65rem 0.85rem;
      color: #FFF !important;
      font-family: inherit;
      font-size: 0.9rem;
      box-sizing: border-box;
      transition: border-color 0.15s ease;
    }
    .kiosk-input:focus {
      outline: none;
      background: #191310 !important;
      border-color: #E28743 !important;
      box-shadow: 0 0 0 2px rgba(226, 135, 67, 0.2) !important;
    }
    .kiosk-input-lock-wrap {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;
    }
    .kiosk-input.kiosk-input-locked {
      background: rgba(255, 255, 255, 0.03) !important;
      border-color: rgba(255, 255, 255, 0.08) !important;
      color: #FAF7F2 !important;
      font-weight: 700;
      cursor: default;
      user-select: none;
      padding-right: 2.4rem;
    }
    .kiosk-input.kiosk-input-locked:focus {
      background: rgba(255, 255, 255, 0.03) !important;
      border-color: rgba(255, 255, 255, 0.08) !important;
      box-shadow: none !important;
    }
    .kiosk-input-lock-icon {
      position: absolute;
      right: 0.85rem;
      display: inline-flex;
      align-items: center;
      color: #8E7E73;
      pointer-events: none;
    }
    .kiosk-payment-pills {
      display: grid;
      grid-template-columns: 1fr;
      gap: 0.45rem;
    }
    .kiosk-pay-pill {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 8px;
      padding: 0.55rem 0.75rem;
      color: #CFC4BC;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.15s ease;
    }
    .kiosk-pay-pill.active {
      background: rgba(226, 135, 67, 0.15);
      border-color: #E28743;
      color: #FFF;
      font-weight: 700;
    }
    .btn-kiosk-place-order {
      width: 100%;
      min-height: 48px;
      background: #10B981;
      border: none;
      border-radius: 10px;
      color: #FFF;
      font-size: 0.98rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: none;
      margin-top: 0.45rem;
      transition: background 0.15s ease;
    }
    .btn-kiosk-place-order:hover {
      background: #059669;
    }

    /* Responsive toggle between Kiosk Sidebar & Floating Bottom Bar */
    @media (min-width: 992px) {
      .floating-cart-bar {
        display: none !important;
      }
    }
    @media (max-width: 991px) {
      .order-app-container {
        display: block;
        padding: 0;
        margin: 0;
      }
      .order-catalog-wrap {
        max-width: 1200px;
        margin: 1.25rem auto;
        padding: 0 1.25rem;
      }
      .kiosk-order-sidebar {
        display: none !important;
      }
    }

    .drink-card {
      background: transparent;
      border: none;
      border-radius: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
      position: relative;
    }
    .drink-card:hover {
      border-color: transparent;
      transform: none;
      box-shadow: none;
    }
    .drink-img-wrap {
      aspect-ratio: 16/11;
      border-radius: 12px;
      overflow: hidden;
      background: #1C1613;
      position: relative;
    }
    .drink-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.25s ease;
    }
    .drink-card:hover .drink-img-wrap img {
      transform: scale(1.03);
    }
    .drink-badge {
      display: none !important;
    }
    .drink-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 0.5rem;
    }
    .drink-name {
      font-size: 1.05rem;
      font-weight: 700;
      color: #FFFFFF;
      line-height: 1.25;
      margin: 0;
    }
    .drink-price {
      font-family: var(--font-mono);
      font-size: 1.05rem;
      font-weight: 700;
      color: #E28743;
      white-space: nowrap;
    }
    .drink-desc {
      font-size: 0.82rem;
      color: #9E8E85;
      line-height: 1.4;
      margin: 0;
      flex: 1;
    }
    .btn-customize-add {
      min-height: 42px;
      border-radius: 10px;
      background: #241D19;
      border: 1px solid rgba(255, 255, 255, 0.09);
      color: #F5EBE1;
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      transition: all 0.15s ease;
      width: 100%;
    }
    .btn-customize-add:hover:not(:disabled) {
      background: #E28743;
      border-color: #E28743;
      color: #FFFFFF;
    }
    .btn-customize-add:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      background: rgba(255, 255, 255, 0.04);
      border-color: rgba(255, 255, 255, 0.06);
      color: #736760;
    }

    /* Floating Cart Bar (Sticky at bottom on mobile) */
    .floating-cart-bar {
      position: fixed;
      bottom: 1.25rem;
      left: 50%;
      transform: translateX(-50%);
      width: min(calc(100% - 2.5rem), 540px);
      background: #181311;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      padding: 0.85rem 1.25rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 99;
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .floating-cart-bar:hover {
      transform: translateX(-50%) translateY(-2px);
    }
    .cart-bar-left {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .cart-bar-icon-wrap {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .cart-bar-count-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: #EF4444;
      color: #FFF;
      font-size: 0.72rem;
      font-weight: 800;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: grid;
      place-content: center;
      border: 2px solid #110D0B;
    }
    .cart-bar-info {
      display: flex;
      flex-direction: column;
    }
    .cart-bar-title {
      font-size: 0.82rem;
      color: #D1C5BD;
      font-weight: 600;
    }
    .cart-bar-total {
      font-size: 1.15rem;
      font-weight: 800;
      color: #FFF;
      font-family: var(--font-mono);
    }
    .cart-bar-cta {
      padding: 0.55rem 1.15rem;
      border-radius: 10px;
      background: #E28743;
      color: #FFF;
      font-size: 0.88rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    /* Modal Sheet Styling */
    .oms-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
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
      border: 1px solid rgba(223, 155, 100, 0.25);
      border-radius: 24px;
      width: 100%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 1.75rem;
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.75);
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
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: none;
      color: #D6C7BC;
      display: grid;
      place-content: center;
      cursor: pointer;
    }
    .oms-modal-close:hover {
      background: rgba(255, 255, 255, 0.16);
      color: #FFF;
    }

    /* Customization Options & Elongated Modal UI */
    .oms-modal-box.custom-modal-long {
      max-width: 920px;
      width: min(920px, calc(100vw - 2.5rem));
      max-height: 88vh;
      padding: 1.75rem 2rem 1.25rem;
      background: linear-gradient(180deg, #1F1815 0%, #16110F 100%);
      border: 1px solid rgba(223, 155, 100, 0.3);
      border-radius: 24px;
      box-shadow: 0 28px 72px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(223, 155, 100, 0.12);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Modal Hero Section */
    .custom-modal-hero {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      padding-bottom: 1.1rem;
      padding-right: 2.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      margin-bottom: 1.1rem;
      flex-shrink: 0;
    }
    .custom-modal-hero-img-wrap {
      width: 84px;
      height: 84px;
      border-radius: 16px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1.5px solid rgba(223, 155, 100, 0.35);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
      background: #110D0B;
    }
    .custom-modal-hero-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .custom-modal-hero-info {
      flex: 1;
      min-width: 0;
    }
    .custom-modal-hero-tag {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      color: #DF9B64;
      text-transform: uppercase;
      margin-bottom: 0.25rem;
    }
    .custom-modal-hero-info h3 {
      font-size: 1.35rem;
      font-weight: 800;
      color: #FFFFFF;
      margin: 0 0 0.35rem;
      line-height: 1.25;
    }
    .custom-modal-hero-price-wrap {
      display: flex;
      align-items: baseline;
      gap: 0.4rem;
    }
    .custom-price-prefix {
      font-size: 0.8rem;
      color: #A99B92;
      font-weight: 600;
    }
    #customItemBasePrice {
      font-family: var(--font-mono);
      font-size: 1.15rem;
      color: #DF9B64;
      font-weight: 800;
    }

    /* Scrollable Options Body - Wide 2-Column Desktop Grid */
    .custom-modal-scrollable-body {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem 2rem;
      align-items: start;
      overflow-y: auto;
      flex: 1;
      min-height: 0;
      padding-right: 0.35rem;
      padding-bottom: 0.5rem;
      scrollbar-width: thin;
      scrollbar-color: rgba(223, 155, 100, 0.45) transparent;
    }
    .custom-modal-col {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }
    .custom-modal-scrollable-body::-webkit-scrollbar {
      width: 5px;
    }
    .custom-modal-scrollable-body::-webkit-scrollbar-track {
      background: transparent;
    }
    .custom-modal-scrollable-body::-webkit-scrollbar-thumb {
      background: rgba(223, 155, 100, 0.4);
      border-radius: 8px;
    }
    .custom-modal-scrollable-body::-webkit-scrollbar-thumb:hover {
      background: rgba(223, 155, 100, 0.65);
    }
    .custom-opt-section {
      display: flex;
      flex-direction: column;
    }

    /* Option Group Headers */
    .option-group-label {
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #DF9B64;
      font-weight: 800;
      margin: 0 0 0.6rem;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    /* Scoped Dark Pill Radios - Overriding all light leaks */
    #customizationModal .pill-radio-group {
      display: grid;
      gap: 0.65rem;
      width: 100%;
    }
    #customizationModal #tempRadioGroup {
      grid-template-columns: repeat(2, 1fr);
    }
    #customizationModal #addonRadioGroup {
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    #customizationModal #sweetnessRadioGroup {
      grid-template-columns: repeat(2, 1fr);
    }
    @media (max-width: 720px) {
      .custom-modal-scrollable-body {
        grid-template-columns: 1fr;
        gap: 1.25rem;
      }
    }
    @media (max-width: 600px) {
      #customizationModal #sweetnessRadioGroup {
        grid-template-columns: repeat(2, 1fr);
      }
      #customizationModal #addonRadioGroup {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      }
    }
    @media (max-width: 480px) {
      .oms-modal-box.custom-modal-long {
        padding: 1.25rem 1rem 1rem;
        max-height: 92vh;
        border-radius: 20px;
      }
      .custom-modal-hero {
        gap: 0.85rem;
        padding-bottom: 0.85rem;
        margin-bottom: 0.85rem;
        padding-right: 2.2rem;
      }
      .custom-modal-hero-img-wrap {
        width: 68px;
        height: 68px;
        border-radius: 12px;
      }
      .custom-modal-hero-info h3 {
        font-size: 1.15rem;
      }
      .custom-modal-sticky-footer {
        gap: 0.6rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
      }
      .custom-modal-qty-control {
        padding: 0.2rem 0.35rem;
      }
      .custom-qty-btn {
        width: 30px;
        height: 30px;
      }
      #customQtyDisplay {
        width: 22px;
        font-size: 1rem;
      }
      .btn-label-desktop {
        display: none !important;
      }
      .btn-label-mobile {
        display: inline !important;
      }
      .custom-submit-btn {
        min-height: 48px;
        font-size: 0.94rem !important;
        padding: 0 0.85rem !important;
        gap: 0.35rem !important;
        white-space: nowrap !important;
      }
    }
    .btn-label-mobile {
      display: none;
    }

    #customizationModal .pill-radio-opt {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      color: #E2D5CC !important;
      border-radius: 12px !important;
      padding: 0.75rem 0.9rem !important;
      font-size: 0.88rem !important;
      font-weight: 600 !important;
      min-height: 50px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      text-align: center !important;
      cursor: pointer !important;
      box-shadow: none !important;
      transform: none !important;
      transition: all 0.18s ease !important;
      user-select: none !important;
      gap: 0.45rem !important;
    }
    #customizationModal .pill-radio-opt:hover:not(.is-sold-out):not(.active) {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: rgba(223, 155, 100, 0.4) !important;
      color: #FFFFFF !important;
      transform: translateY(-1px) !important;
    }
    #customizationModal .pill-radio-opt.active {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%) !important;
      border: 1.5px solid #E28743 !important;
      color: #FFFFFF !important;
      font-weight: 700 !important;
      box-shadow: 0 4px 16px rgba(226, 135, 67, 0.4) !important;
      transform: translateY(-1px) !important;
    }
    #customizationModal .pill-radio-opt.is-sold-out {
      opacity: 0.4 !important;
      background: rgba(239, 68, 68, 0.06) !important;
      border-color: rgba(239, 68, 68, 0.2) !important;
      color: #9CA3AF !important;
      cursor: not-allowed !important;
      pointer-events: none !important;
      text-decoration: line-through;
    }

    .opt-emoji {
      font-size: 1.15rem;
      line-height: 1;
    }
    .opt-pct {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      font-weight: 700;
      opacity: 0.8;
      margin-right: 0.2rem;
    }

    .sold-out-badge {
      display: inline-block;
      text-decoration: none !important;
      font-size: 0.65rem;
      font-weight: 800;
      color: #F87171;
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid rgba(239, 68, 68, 0.4);
      padding: 0.1rem 0.4rem;
      border-radius: 4px;
      margin-left: 0.35rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      vertical-align: middle;
    }
    .drink-card.is-sold-out {
      opacity: 0.65;
    }
    .drink-card.is-sold-out .drink-img-wrap::after {
      content: 'SOLD OUT';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-6deg);
      background: rgba(220, 38, 38, 0.95);
      color: #FFF;
      font-weight: 900;
      font-size: 0.85rem;
      letter-spacing: 0.12em;
      padding: 0.35rem 0.85rem;
      border-radius: 6px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.6);
      z-index: 5;
    }
    .drink-card.is-sold-out .btn-customize-add {
      background: rgba(255, 255, 255, 0.08) !important;
      color: #9CA3AF !important;
      cursor: not-allowed !important;
      pointer-events: none !important;
    }
    .notes-textarea,
    body.order-app-body .notes-textarea {
      width: 100%;
      background: rgba(0, 0, 0, 0.45) !important;
      border: 1px solid rgba(255, 255, 255, 0.16) !important;
      border-radius: 12px;
      padding: 0.85rem 1rem;
      color: #FFFFFF !important;
      font-family: inherit;
      font-size: 0.92rem;
      resize: vertical;
      min-height: 80px;
      transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
      box-sizing: border-box;
    }
    .notes-textarea:focus,
    body.order-app-body .notes-textarea:focus {
      outline: none !important;
      background: #191310 !important;
      border-color: #DF9B64 !important;
      color: #FFFFFF !important;
      box-shadow: 0 0 0 3px rgba(223, 155, 100, 0.25) !important;
    }
    .notes-textarea::placeholder,
    body.order-app-body .notes-textarea::placeholder {
      color: #8C7E75 !important;
      font-size: 0.88rem;
    }

    /* Modal Sticky Bottom Action Bar */
    .custom-modal-sticky-footer {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      position: sticky;
      bottom: 0;
      width: 100%;
      box-sizing: border-box;
      flex-shrink: 0;
      background: linear-gradient(180deg, rgba(22, 17, 15, 0.85) 0%, rgba(22, 17, 15, 0.98) 100%);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 10;
    }
    .custom-modal-qty-control {
      display: flex;
      align-items: center;
      flex-shrink: 0;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      padding: 0.35rem 0.5rem;
      gap: 0.25rem;
    }
    .custom-qty-btn {
      background: none;
      border: none;
      color: #FFF;
      width: 36px;
      height: 36px;
      display: grid;
      place-content: center;
      cursor: pointer;
      border-radius: 8px;
      transition: background 0.15s ease;
    }
    .custom-qty-btn:hover {
      background: rgba(255, 255, 255, 0.12);
    }
    #customQtyDisplay {
      font-family: var(--font-mono);
      font-weight: 800;
      font-size: 1.15rem;
      width: 32px;
      text-align: center;
      color: #FFF;
    }
    .custom-submit-btn {
      flex: 1;
      width: auto !important;
      min-width: 0 !important;
      min-height: 52px;
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%) !important;
      border: none !important;
      border-radius: 12px !important;
      color: #FFFFFF !important;
      font-size: 1.02rem !important;
      font-weight: 700 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 0.6rem !important;
      box-shadow: 0 6px 20px rgba(226, 135, 67, 0.35) !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
    }
    .custom-submit-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 26px rgba(226, 135, 67, 0.5) !important;
    }
    .custom-submit-sep {
      opacity: 0.5;
      font-size: 0.8rem;
    }

    /* THE ORDER TICKET WAITING SCREEN (Starts Fresh at 00:00) */
    .ticket-screen-overlay {
      position: fixed;
      inset: 0;
      background: #110D0B;
      z-index: 2000;
      display: none;
      flex-direction: column;
      overflow-y: auto;
      padding: 1.5rem 1rem 3rem;
      align-items: center;
      justify-content: center;
    }
    .ticket-screen-overlay.active {
      display: flex;
    }

    .ticket-container {
      width: 100%;
      max-width: 440px;
      background: #1C1613;
      border: 2px solid rgba(223, 155, 100, 0.25);
      border-radius: 28px;
      padding: 2rem 1.75rem;
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.8);
      text-align: center;
      position: relative;
    }

    /* Giant Queue Number */
    .ticket-giant-num {
      font-size: clamp(3rem, 12vw, 4.25rem);
      font-weight: 800;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1;
      margin: 0.85rem 0 0.5rem;
      letter-spacing: -0.03em;
    }

    /* Fresh 00:00 Live Stopwatch */
    .ticket-stopwatch-row {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(226, 135, 67, 0.12);
      border: 1px solid rgba(226, 135, 67, 0.3);
      padding: 0.35rem 0.95rem;
      border-radius: 999px;
      font-family: var(--font-mono);
      font-size: 0.95rem;
      font-weight: 700;
      color: #FDBA74;
      margin-bottom: 1.25rem;
    }

    /* Live Pulsing Status Badge */
    .ticket-status-card {
      background: rgba(0, 0, 0, 0.35);
      border-radius: 16px;
      padding: 1.25rem;
      margin: 1.25rem 0;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .status-beacon-wrap {
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
      font-size: 1.05rem;
      font-weight: 700;
      margin-bottom: 0.35rem;
    }
    /* Pending Amber */
    .status-pending .status-beacon-wrap { color: #F59E0B; }
    .status-pending .beacon-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #F59E0B;
      box-shadow: 0 0 14px #F59E0B;
      animation: pulseGlow 1.4s infinite ease-in-out;
    }
    /* In Progress Blue */
    .status-in_progress .status-beacon-wrap { color: #60A5FA; }
    .status-in_progress .beacon-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #3B82F6;
      box-shadow: 0 0 14px #3B82F6;
      animation: pulseGlow 1.4s infinite ease-in-out;
    }
    @keyframes pulseGlow {
      0%, 100% { transform: scale(0.9); opacity: 0.7; }
      50% { transform: scale(1.2); opacity: 1; }
    }

    /* FULLSCREEN EMERALD GREEN PICKUP TAKEOVER */
    .ticket-screen-overlay.is-ready {
      background: linear-gradient(135deg, #064E3B 0%, #022C22 100%) !important;
    }
    .ticket-screen-overlay.is-ready .ticket-container {
      background: rgba(6, 78, 59, 0.95) !important;
      border-color: #34D399 !important;
      box-shadow: 0 0 60px rgba(52, 211, 153, 0.4) !important;
    }
    .ticket-screen-overlay.is-ready .ticket-giant-num {
      color: #A7F3D0 !important;
    }
    .ticket-screen-overlay.is-ready .pickup-celebrate-title {
      font-size: 1.55rem;
      font-weight: 800;
      color: #FFF;
      margin: 1rem 0 0.5rem;
    }
    .btn-pickup-dismiss {
      width: 100%;
      min-height: 52px;
      border-radius: 14px;
      background: #10B981;
      border: none;
      color: #FFF;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 1.25rem;
      box-shadow: 0 4px 18px rgba(16, 185, 129, 0.5);
    }

    /* 0.5s ANIMATED SUCCESS CHECKMARK HUD */
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

    /* DIGITAL PAID RECEIPT OVERLAY */
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
      background: rgba(223, 155, 100, 0.12);
      border: 1px solid rgba(223, 155, 100, 0.3);
      border-radius: 12px;
      padding: 0.75rem 0.85rem;
      text-align: center;
      font-size: 0.82rem;
      color: #FDBA74;
      line-height: 1.4;
    }
    .receipt-staff-notice strong {
      display: block;
      color: #FFF;
      margin-bottom: 0.2rem;
      font-size: 0.85rem;
    }
    .receipt-screenshot-banner {
      background: linear-gradient(135deg, rgba(223, 155, 100, 0.2) 0%, rgba(140, 83, 43, 0.12) 100%);
      border-bottom: 1px solid rgba(223, 155, 100, 0.3);
      padding: 0.85rem 1.15rem;
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }
    .receipt-screenshot-icon {
      width: 36px;
      height: 36px;
      min-width: 36px;
      background: rgba(223, 155, 100, 0.25);
      border: 1px solid rgba(223, 155, 100, 0.45);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #FDBA74;
    }
    .receipt-screenshot-content {
      flex: 1;
      min-width: 0;
    }
    .receipt-screenshot-title {
      font-size: 0.82rem;
      font-weight: 700;
      color: #FFF;
      letter-spacing: 0.02em;
      margin-bottom: 0.12rem;
    }
    .receipt-screenshot-sub {
      font-size: 0.72rem;
      color: #D6C7BE;
      line-height: 1.35;
      margin: 0;
    }
    .receipt-actions {
      padding: 0.85rem 1.25rem 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 0.55rem;
    }
    .btn-receipt-download {
      width: 100%;
      min-height: 46px;
      background: linear-gradient(135deg, #8C532B 0%, #6E3F1F 100%);
      border: 1px solid rgba(223, 155, 100, 0.4);
      border-radius: 12px;
      color: #FFF;
      font-weight: 700;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.55rem;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 14px rgba(140, 83, 43, 0.35);
    }
    .btn-receipt-download:hover {
      background: linear-gradient(135deg, #9C5E32 0%, #7E4924 100%);
      border-color: rgba(223, 155, 100, 0.6);
      transform: translateY(-1px);
    }
    .btn-receipt-download:active {
      transform: translateY(0);
    }
    .receipt-secondary-actions {
      display: flex;
      gap: 0.5rem;
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
      border: 1px solid rgba(239, 68, 68, 0.35);
      border-radius: 10px;
      color: #FCA5A5;
      font-weight: 600;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-receipt-exit:hover {
      background: rgba(239, 68, 68, 0.15);
    }

    /* GCASH PAYMENT MODAL SHEET */
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
    <a href="home.php" class="order-brand-link">
      <div class="order-brand-logo">B</div>
      <div>
        <span class="order-brand-title">BeCoffee</span>
        <span class="order-brand-tag">Online Ordering Portal</span>
      </div>
    </a>

    <!-- Right Actions -->
    <div class="order-top-actions">
      <?php if (!empty($currentUser)): ?>
        <div class="staff-header-actions" aria-label="Staff navigation">
          <?php if (in_array($currentUser['role'], ['staff', 'admin', 'superadmin'])): ?>
            <a href="kds.php" class="header-action-btn action-kds" title="Open Kitchen Display System">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                <line x1="8" x2="16" y1="21" y2="21"></line>
                <line x1="12" x2="12" y1="17" y2="21"></line>
              </svg>
              <span>Staff KDS</span>
            </a>
          <?php endif; ?>
          <span class="header-action-divider" aria-hidden="true"></span>
          <a href="api/auth.php?action=logout&redirect=home.php" class="header-action-btn action-logout" title="Sign out of account">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Log Out</span>
          </a>
        </div>
      <?php else: ?>
        <a href="home.php" class="btn-story-link header-action-btn action-story" title="Explore roastery background and story">Our Story</a>
        <button type="button" class="btn-story-link btn-leave-link header-action-btn action-leave" id="btnLeaveSession" title="Leave table ordering and exit" style="background: none; cursor: pointer;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Leave</span>
        </button>
      <?php endif; ?>
    </div>
  </header>

  <!-- Sticky Category Navigation with 10-Min Prep Timer -->
  <nav class="order-category-nav" id="orderCategoryNav" aria-label="Menu Categories">
    <div class="cat-pill-group" id="catPillGroup">
      <button type="button" class="cat-pill-btn active" data-cat="all">All Drinks</button>
      <button type="button" class="cat-pill-btn" data-cat="house-coffee">House Coffee</button>
      <button type="button" class="cat-pill-btn" data-cat="matcha">Matcha</button>
      <button type="button" class="cat-pill-btn" data-cat="house-specials">House Specials</button>
      <button type="button" class="cat-pill-btn" data-cat="yogurt-soda">Yogurt / Soda</button>
    </div>
    <div class="order-prep-timer" id="orderPrepTimer" title="Remaining time to place your order" role="timer" aria-live="polite" style="<?= ($isWalkin || !$isTableQR) ? 'display: none;' : '' ?>">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="timer-icon" aria-hidden="true">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>
      <span class="timer-label"><span class="timer-label-full">Time Left:</span><span class="timer-label-short">Time:</span></span>
      <strong class="timer-clock" id="prepCountdownClock">10:00</strong>
    </div>
  </nav>

  <!-- Operational Notice: Table QR Ordering Paused -->
  <div id="tableOrderingPausedBanner" style="display: none; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 12px; padding: 0.85rem 1.25rem; margin: 1rem auto 1rem; max-width: 1200px; color: #FCA5A5; font-size: 0.88rem; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.25);">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <span style="font-size: 1.4rem;">⏸️</span>
      <div>
        <strong style="color: #FFF; display: block; font-size: 0.95rem;">Table QR Ordering Paused</strong>
        <span>Direct table ordering is currently paused by cafe management. Please order at the cashier counter or switch to Take Out.</span>
      </div>
    </div>
    <button type="button" id="btnSwitchTakeoutPaused" style="white-space: nowrap; background: #DF9B64; color: #110D0B; border: none; border-radius: 8px; font-weight: 700; padding: 0.45rem 0.9rem; font-size: 0.82rem; cursor: pointer;">
      Switch to Take Out
    </button>
  </div>

  <!-- Main Order App Layout (Catalog + Desktop/Kiosk Order Sidebar) -->
  <div class="order-app-container">
    <main class="order-catalog-wrap">
      <div id="catalogLoadingNotice" style="text-align: center; padding: 3rem 1rem; color: #A99B92;">
        Loading artisanal drink selection...
      </div>

      <div class="catalog-grid" id="drinksCatalogGrid" style="display: none;">
        <!-- Drink Cards injected dynamically -->
      </div>
    </main>

    <!-- Kiosk / POS Register Right Sidebar (Active on Desktop/Tablet) -->
    <aside class="kiosk-order-sidebar" id="kioskOrderSidebar" aria-label="Order Register Tray">
      <!-- Panel 1: Tray Items & Summary -->
      <div class="kiosk-panel" id="kioskViewTray">
        <div class="kiosk-sidebar-header">
          <div class="kiosk-header-top">
            <div class="kiosk-header-title-wrap">
              <h3 class="kiosk-header-title">Order Tray</h3>
              <span class="kiosk-item-badge" id="kioskItemCountBadge">(0)</span>
            </div>
            <button type="button" class="kiosk-btn-clear" id="kioskBtnClearTray" title="Clear all items in tray" style="display: none;">
              Clear
            </button>
          </div>
          <!-- Dining Mode Segmented Switch -->
          <div class="kiosk-segmented-dining" id="kioskDiningSegmented">
            <button type="button" class="kiosk-seg-btn active" data-type="dine_in" id="kioskBtnDineIn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
              <span>Dine In</span>
            </button>
            <button type="button" class="kiosk-seg-btn" data-type="take_out" id="kioskBtnTakeOut">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
              <span>Take Out</span>
            </button>
          </div>
        </div>

        <!-- Scrollable Items List -->
        <div class="kiosk-items-scroll" id="kioskItemsList">
          <div class="kiosk-empty-state" id="kioskEmptyState">
            <div class="kiosk-empty-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
            </div>
            <h4>Your tray is empty</h4>
            <p>Tap any drink to add it to your order.</p>
          </div>
        </div>

        <!-- Sticky Footer -->
        <div class="kiosk-sidebar-footer">
          <div class="kiosk-calc-row">
            <span>Subtotal</span>
            <strong id="kioskSubtotalDisplay" style="color: #FFF; font-family: var(--font-mono);">₱0.00</strong>
          </div>
          <div class="kiosk-calc-row" id="kioskEcoRow" style="display: none;">
            <span>Packaging & Eco Fee</span>
            <strong id="kioskEcoDisplay" style="color: #DF9B64; font-family: var(--font-mono);">₱15.00</strong>
          </div>
          <div class="kiosk-calc-row total">
            <span>Total Amount</span>
            <span class="kiosk-calc-total-val" id="kioskGrandTotalDisplay">₱0.00</span>
          </div>
          <button type="button" class="btn-kiosk-checkout" id="btnKioskProceedCheckout" disabled>
            <span>Proceed to Checkout</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </div>
      </div>

      <!-- Panel 2: In-place Kiosk Checkout -->
      <div class="kiosk-panel" id="kioskViewCheckout" style="display: none;">
        <div class="kiosk-sidebar-header">
          <button type="button" class="kiosk-checkout-back-btn" id="btnKioskBackToTray">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
            <span>Back to Tray</span>
          </button>
          <h3 class="kiosk-header-title">Checkout</h3>
        </div>

        <div class="kiosk-checkout-panel">
          <!-- Table Number (Locked for Table QR Scan) -->
          <div class="kiosk-field-group" id="kioskTableGroup" style="<?= $isWalkin ? 'display: none !important;' : '' ?>">
            <label class="kiosk-field-label" for="kioskTableInput">Table Number</label>
            <div class="kiosk-input-lock-wrap">
              <input type="text" id="kioskTableInput" class="kiosk-input kiosk-input-locked" readonly placeholder="1" value="<?= htmlspecialchars($_GET['table'] ?? '1') ?>">
              <span class="kiosk-input-lock-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
            </div>
          </div>

          <!-- Customer Name (Primary Call-Out Identifier) -->
          <div class="kiosk-field-group">
            <label class="kiosk-field-label" for="kioskNameInput">Customer Name <span style="font-size: 0.72rem; color: #DF9B64; font-weight: normal;">(Called when ready)</span></label>
            <input type="text" id="kioskNameInput" class="kiosk-input" placeholder="e.g. John or Sarah" value="">
          </div>

          <!-- Payment Method -->
          <div class="kiosk-field-group">
            <label class="kiosk-field-label">Payment Method</label>
            <div class="kiosk-payment-pills" id="kioskPaymentPills">
              <div class="kiosk-pay-pill active" data-val="cash">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                <span>Pay at Counter (Cash)</span>
              </div>
              <div class="kiosk-pay-pill" data-val="gcash">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>GCash (QR Code)</span>
              </div>
            </div>
          </div>
        </div>

        <div class="kiosk-sidebar-footer">
          <div class="kiosk-calc-row total">
            <span>Total Due</span>
            <span class="kiosk-calc-total-val" id="kioskFinalTotalDisplay">₱0.00</span>
          </div>
          <button type="button" class="btn-kiosk-place-order" id="btnKioskPlaceOrderFinal">
            <span>Place Order Now</span>
          </button>
        </div>
      </div>
    </aside>
  </div>

  <!-- Floating Sticky Cart Bar -->
  <div class="floating-cart-bar" id="floatingCartBar" style="display: none;">
    <div class="cart-bar-left">
      <div class="cart-bar-icon-wrap">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        <span class="cart-bar-count-badge" id="cartCountBadge">0</span>
      </div>
      <div class="cart-bar-info">
        <span class="cart-bar-title" id="cartBarSubtitle">Cart Summary</span>
        <span class="cart-bar-total" id="cartBarTotal">₱0.00</span>
      </div>
    </div>
    <div class="cart-bar-cta">
      <span>View Order</span>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </div>
  </div>

  <!-- Customization Modal Sheet -->
  <div class="oms-modal-overlay" id="customizationModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box custom-modal-long">
      <button type="button" class="oms-modal-close" id="closeCustomModalBtn" aria-label="Close Customizer">&times;</button>
      
      <!-- Modern Hero Header -->
      <div class="custom-modal-hero">
        <div class="custom-modal-hero-img-wrap">
          <img id="customItemImg" src="" alt="Drink preview">
        </div>
        <div class="custom-modal-hero-info">
          <span class="custom-modal-hero-tag">Handcrafted Specialty</span>
          <h3 id="customItemName">Spanish Latte</h3>
          <div class="custom-modal-hero-price-wrap">
            <span class="custom-price-prefix">Base Price:</span>
            <span id="customItemBasePrice">₱120.00</span>
          </div>
        </div>
      </div>

      <!-- Wide 2-Column Options Body -->
      <div class="custom-modal-scrollable-body">
        <!-- Left Column: Core Formulation (Temperature & Sweetness) -->
        <div class="custom-modal-col">
          <!-- Temperature Choice -->
          <div class="custom-opt-section">
            <div class="option-group-label">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
              Temperature
            </div>
            <div class="pill-radio-group" id="tempRadioGroup">
              <div class="pill-radio-opt active" data-val="Iced">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="2" x2="12" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line><line x1="19.07" y1="4.93" x2="4.93" y2="19.07"></line></svg>
                <span>Iced</span>
              </div>
              <div class="pill-radio-opt" data-val="Hot">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                <span>Hot</span>
              </div>
            </div>
          </div>

          <!-- Sweetness Levels -->
          <div class="custom-opt-section">
            <div class="option-group-label">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>
              Sweetness / Sugar Level
            </div>
            <div class="pill-radio-group" id="sweetnessRadioGroup">
              <div class="pill-radio-opt" data-val="Normal (100%)">
                <span class="opt-pct">100%</span>
                <span>Normal</span>
              </div>
              <div class="pill-radio-opt active" data-val="Less Sweet (75%)">
                <span class="opt-pct">75%</span>
                <span>Less Sweet</span>
              </div>
              <div class="pill-radio-opt" data-val="Half Sweet (50%)">
                <span class="opt-pct">50%</span>
                <span>Half Sweet</span>
              </div>
              <div class="pill-radio-opt" data-val="No Sugar (0%)">
                <span class="opt-pct">0%</span>
                <span>No Sugar</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Add-ons & Special Instructions -->
        <div class="custom-modal-col">
          <!-- Add-ons Selection -->
          <div class="custom-opt-section">
            <div class="option-group-label">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"></path></svg>
              Add-ons & Options
            </div>
            <div class="pill-radio-group" id="addonRadioGroup">
              <!-- Injected dynamically from live stock options -->
            </div>
          </div>

          <!-- Special Notes -->
          <div class="custom-opt-section">
            <div class="option-group-label">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              Special Notes / Instructions
            </div>
            <textarea class="notes-textarea" id="customNotesInput" placeholder="e.g. Extra ice, separate lid, double cup..."></textarea>
          </div>
        </div>
      </div>

      <!-- Sticky Bottom Action Footer -->
      <div class="custom-modal-sticky-footer">
        <div class="custom-modal-qty-control">
          <button type="button" id="btnQtyMinus" class="custom-qty-btn" aria-label="Decrease quantity">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          </button>
          <span id="customQtyDisplay">1</span>
          <button type="button" id="btnQtyPlus" class="custom-qty-btn" aria-label="Increase quantity">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          </button>
        </div>
        <button type="button" class="btn-customize-add custom-submit-btn" id="btnSubmitCustomItem">
          <span class="btn-label-desktop">Add to Tray</span>
          <span class="btn-label-mobile">Add</span>
          <span class="custom-submit-sep">•</span>
          <span id="customModalTotalPrice">₱150.00</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Inactive Table Expiration Modal -->
  <div class="table-expired-modal-overlay" id="tableExpiredModal" style="display: none;" role="alertdialog" aria-modal="true" aria-labelledby="expiredTitle" aria-describedby="expiredDesc">
    <div class="table-expired-card">
      <div class="expired-icon">🚪</div>
      <h3 class="expired-title" id="expiredTitle">Session Expired</h3>
      <p class="expired-desc" id="expiredDesc">
        Your 10-minute table ordering window has expired due to inactivity. Exiting the web...
      </p>
      <button type="button" class="btn-expired-leave" id="btnExpiredLeaveNow">Exit Now</button>
    </div>
  </div>

  <!-- Cart Drawer Modal -->
  <div class="oms-modal-overlay" id="cartModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box">
      <button type="button" class="oms-modal-close" id="closeCartModalBtn">&times;</button>
      <h3 style="font-size: 1.35rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">Your Order Tray</h3>
      <p style="font-size: 0.82rem; color: #A99B92; margin-bottom: 1.25rem;">Review items and customized specifications.</p>

      <div id="cartItemsList" style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 45vh; overflow-y: auto; margin-bottom: 1.25rem;">
        <!-- Injected cart items -->
      </div>

      <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.45rem;">
        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #A99B92;">
          <span>Subtotal</span>
          <span id="cartSubtotalDisplay" style="font-family: var(--font-mono); color: #FFF;">₱0.00</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #A99B92;">
          <span id="ecoFeeLabel">Eco-Packaging Fee</span>
          <span id="cartEcoFeeDisplay" style="font-family: var(--font-mono); color: #FFF;">₱0.00</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: #FFF; margin-top: 0.5rem;">
          <span>Grand Total</span>
          <span id="cartGrandTotalDisplay" style="font-family: var(--font-mono); color: #DF9B64;">₱0.00</span>
        </div>
      </div>

      <button type="button" class="btn-customize-add" id="btnProceedToCheckout" style="margin-top: 1.25rem; min-height: 52px; background: linear-gradient(135deg, #E28743 0%, #944D1C 100%); color: #FFF; font-size: 1rem;">
        Proceed to Checkout
      </button>
    </div>
  </div>

  <!-- Checkout & Payment Modal -->
  <div class="oms-modal-overlay" id="checkoutModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box">
      <button type="button" class="oms-modal-close" id="closeCheckoutModalBtn">&times;</button>
      <h3 style="font-size: 1.35rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">Checkout & Fulfillment</h3>
      <p style="font-size: 0.82rem; color: #A99B92; margin-bottom: 1.25rem;">Specify dining preference and payment method.</p>

      <!-- Order Type -->
      <div class="option-group-label" style="display: flex; justify-content: space-between; align-items: baseline;">
        <span>Dining Preference</span>
        <span style="font-size: 0.74rem; color: #A99B92; font-weight: normal;">Select once for this order</span>
      </div>
      <div class="pill-radio-group" id="orderTypeRadioGroup" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
        <div class="pill-radio-opt active" data-val="dine_in" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.92rem; font-weight: 700;">🪑 Dine In</div>
        <div class="pill-radio-opt" data-val="take_out" style="justify-content: center; padding: 0.75rem 1rem; font-size: 0.92rem; font-weight: 700;">🛍️ Take Out</div>
      </div>

      <!-- Locked Table Badge (Auto-assigned via Table QR Sticker ?table=N) -->
      <div id="tableLockedBadge" style="<?= $isWalkin ? 'display: none !important;' : 'display: none;' ?> margin-top: 0.85rem; background: rgba(223, 155, 100, 0.12); border: 1px solid rgba(223, 155, 100, 0.35); border-radius: 10px; padding: 0.75rem 0.85rem; align-items: center; gap: 0.75rem;">
        <div style="font-size: 1.4rem;">🪑</div>
        <div>
          <div style="font-size: 0.9rem; font-weight: 700; color: #DF9B64;">Seated at Table <span id="tableLockedNumber">1</span></div>
          <div style="font-size: 0.75rem; color: #A99B92;">Auto-detected from Table QR Sticker · Orders served directly to this table</div>
        </div>
      </div>

      <!-- Table Number (Shown if Dine-In and not arriving from table-locked QR) -->
      <div id="tableNumberWrap" style="<?= $isWalkin ? 'display: none !important;' : '' ?>margin-top: 0.85rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 0.75rem 0.85rem;">
        <label for="checkoutTableInput" style="display: block; font-size: 0.78rem; color: #DF9B64; font-weight: 700; margin-bottom: 0.35rem;">Table Number</label>
        <div style="display: flex; align-items: center; gap: 0.6rem;">
          <input type="text" id="checkoutTableInput" class="notes-textarea" style="min-height: 40px; width: 90px; padding: 0.45rem 0.75rem; font-size: 1rem; font-weight: 700; color: #FFF; text-align: center;" placeholder="e.g. 4" value="4">
          <span style="font-size: 0.76rem; color: #A99B92; line-height: 1.3;">Seated at a table? Enter the number on your wooden table stand.</span>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="option-group-label" style="margin-top: 1.25rem;">Payment Method</div>
      <div class="pill-radio-group" id="paymentMethodRadioGroup">
        <div class="pill-radio-opt active" data-val="cash">💵 Cash at Counter</div>
        <div class="pill-radio-opt" data-val="gcash">📱 GCash</div>
      </div>

      <div id="gcashNoticeWrap" style="display: none; background: rgba(223, 155, 100, 0.1); border: 1px solid rgba(223, 155, 100, 0.3); border-radius: 10px; padding: 0.75rem 0.9rem; margin-top: 0.75rem; font-size: 0.8rem; color: #FAF7F2; line-height: 1.4;">
        <strong style="color: #DF9B64;">Table GCash Payment:</strong> Scan the GCash QR sticker on your table to pay. Show your GCash transaction receipt to the server when your drinks arrive.
      </div>

      <!-- Customer Details -->
      <div class="option-group-label" style="margin-top: 1.25rem;">Customer Details</div>
      <div style="display: flex; flex-direction: column; gap: 0.65rem;">
        <input type="text" id="checkoutNameInput" class="notes-textarea" style="min-height: 44px; padding: 0.6rem 0.85rem;" placeholder="<?= $isWalkin ? 'Customer Name (Called when ready)' : 'Your Name (e.g. Mark or Sarah)' ?>" value="<?= $isWalkin ? '' : 'Mark' ?>">
        <input type="tel" id="checkoutPhoneInput" class="notes-textarea" style="<?= $isWalkin ? 'display: none !important;' : '' ?>min-height: 44px; padding: 0.6rem 0.85rem;" placeholder="Mobile Number (+63 9XX XXX XXXX)" value="<?= $isWalkin ? '' : '+63 917 555 2026' ?>">
      </div>

      <!-- Place Order CTA -->
      <button type="button" class="btn-customize-add" id="btnPlaceOrderFinal" style="margin-top: 1.5rem; min-height: 54px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: #FFF; font-size: 1.05rem; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.35);">
        Place Order — <span id="checkoutFinalTotal">₱0.00</span>
      </button>
    </div>
  </div>

  <!-- 0.5s FAST SUCCESS CHECKMARK HUD -->
  <div class="order-success-hud" id="orderSuccessHud" role="status" aria-live="polite">
    <div class="hud-box">
      <svg class="hud-icon-svg" viewBox="0 0 52 52" aria-hidden="true">
        <circle class="hud-circle" cx="26" cy="26" r="23"/>
        <path class="hud-check" d="M14 27l8 8 16-17"/>
      </svg>
      <div class="hud-title" id="hudSuccessTitle">Order Transmitted!</div>
      <div class="hud-sub" id="hudSuccessSub">Queue #104 · Direct to KDS</div>
    </div>
  </div>

  <!-- DIGITAL PAID ORDER RECEIPT SCREEN (For Table QR & In-Store Orders) -->
  <div class="receipt-screen-overlay" id="orderReceiptScreen" role="dialog" aria-modal="true" aria-labelledby="receiptQueueNum">
    <div class="receipt-card">
      <!-- Screenshot & Download Callout Banner -->
      <div class="receipt-screenshot-banner" id="receiptScreenshotBanner">
        <div class="receipt-screenshot-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
          </svg>
        </div>
        <div class="receipt-screenshot-content">
          <div class="receipt-screenshot-title">📸 Take a Screenshot of This Receipt</div>
          <p class="receipt-screenshot-sub">Please screenshot this receipt or download it as a photo below. Present your queue number to our barista when claiming your drinks!</p>
        </div>
      </div>

      <div class="receipt-header-strip">
        <div class="receipt-brand">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
          <span>BeCoffee Roastery</span>
        </div>
        <div class="receipt-giant-queue" id="receiptQueueNum">#104</div>
        <div class="receipt-paid-pill" id="receiptPaidBadge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
          <span id="receiptPaidText">PAID · Cash at Counter</span>
        </div>
      </div>

      <div class="receipt-meta-grid">
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Location / Dining</span>
          <span class="receipt-meta-val" id="receiptDiningVal">Table #4 (Dine-In)</span>
        </div>
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Customer</span>
          <span class="receipt-meta-val" id="receiptCustomerVal">Mark</span>
        </div>
        <div class="receipt-meta-item">
          <span class="receipt-meta-label">Order Ref</span>
          <span class="receipt-meta-val" id="receiptRefVal" style="font-family: var(--font-mono); font-size: 0.78rem;">BEC-9988</span>
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
        <span class="receipt-total-num" id="receiptTotalVal">₱120.00</span>
      </div>

      <div class="receipt-staff-notice" id="receiptStaffNotice">
        <strong>📱 Pay via Table GCash Sticker</strong>
        Scan the GCash QR sticker on your table and present your transaction receipt to the server when your order arrives.
      </div>

      <div class="receipt-actions">
        <button type="button" class="btn-receipt-download" id="btnReceiptDownload">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          Download as Photo
        </button>
        <div class="receipt-secondary-actions">
          <button type="button" class="btn-receipt-order-more" id="btnReceiptOrderMore">Order More Drinks</button>
          <button type="button" class="btn-receipt-exit" id="btnReceiptExit">Exit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Application Logic -->
  <script src="js/system-dialog.js"></script>
  <script>
    (function initDirectOrderPortal() {
      // 1. URL Query Parameter & Session State Parsing (?table=4 or ?type=take_out)
      var urlParams = new URLSearchParams(window.location.search);
      var initialTable = urlParams.get('table');
      var initialType = urlParams.get('type');
      var isWalkin = urlParams.get('mode') === 'walkin';

      var savedType = sessionStorage.getItem('becoffee_order_type');
      var savedTable = sessionStorage.getItem('becoffee_table_num');
      var diningChosen = sessionStorage.getItem('becoffee_dining_chosen');

      var currentOrderType = 'dine_in';
      var currentTableNumber = initialTable || savedTable || '1';
      var isTableLocked = Boolean(initialTable || sessionStorage.getItem('becoffee_table_locked') === 'true');
      var isTableQREnabled = true;

      // If a specific table QR was scanned (e.g. ?table=4), immediately lock to Dine-In Table #N without prompting
      if (initialTable) {
        currentOrderType = 'dine_in';
        currentTableNumber = initialTable;
        sessionStorage.setItem('becoffee_dining_chosen', 'true');
        sessionStorage.setItem('becoffee_table_locked', 'true');
        isTableLocked = true;
      } else if (initialType) {
        currentOrderType = (initialType === 'take_out') ? 'take_out' : 'dine_in';
        sessionStorage.setItem('becoffee_dining_chosen', 'true');
      } else if (savedType) {
        currentOrderType = savedType;
      }

      function updatePausedBanner() {
        var banner = document.getElementById('tableOrderingPausedBanner');
        if (!banner) return;
        if (!isTableQREnabled && currentOrderType === 'dine_in') {
          banner.style.display = 'flex';
        } else {
          banner.style.display = 'none';
        }
      }

      function updateContextBadge() {
        sessionStorage.setItem('becoffee_order_type', currentOrderType);
        if (currentOrderType === 'dine_in') {
          sessionStorage.setItem('becoffee_table_num', currentTableNumber);
        }
        updatePausedBanner();
        var pill = document.getElementById('tableContextPill');
        if (pill) {
          pill.style.display = 'none';
        }
      }
      updateContextBadge();

      // Switch to take out from paused banner
      var btnSwitchTakeout = document.getElementById('btnSwitchTakeoutPaused');
      if (btnSwitchTakeout) {
        btnSwitchTakeout.addEventListener('click', function() {
          currentOrderType = 'take_out';
          sessionStorage.setItem('becoffee_order_type', 'take_out');
          sessionStorage.setItem('becoffee_dining_chosen', 'true');
          updateContextBadge();
          updateCartUI();
          syncCheckoutTableDisplay();
        });
      }

      // Sync Table Display in Checkout Modal
      function syncCheckoutTableDisplay() {
        var lockedBadge = document.getElementById('tableLockedBadge');
        var manualWrap = document.getElementById('tableNumberWrap');
        var lockedNum = document.getElementById('tableLockedNumber');
        var checkoutInput = document.getElementById('checkoutTableInput');
        var kioskTableGrp = document.getElementById('kioskTableGroup');
        var kioskPhoneGrp = document.getElementById('kioskPhoneGroup');

        if (isWalkin) {
          if (lockedBadge) lockedBadge.style.display = 'none';
          if (manualWrap) manualWrap.style.display = 'none';
          if (kioskTableGrp) kioskTableGrp.style.display = 'none';
          if (kioskPhoneGrp) kioskPhoneGrp.style.display = 'none';
          return;
        }

        if (lockedNum) lockedNum.textContent = currentTableNumber;
        if (checkoutInput) checkoutInput.value = currentTableNumber;
        var kioskTableInp = document.getElementById('kioskTableInput');
        if (kioskTableInp) {
          kioskTableInp.value = currentTableNumber;
          kioskTableInp.readOnly = true;
        }

        if (currentOrderType === 'dine_in') {
          if (isTableLocked) {
            if (lockedBadge) lockedBadge.style.display = 'flex';
            if (manualWrap) manualWrap.style.display = 'none';
          } else {
            if (lockedBadge) lockedBadge.style.display = 'none';
            if (manualWrap) manualWrap.style.display = 'block';
          }
          if (kioskTableGrp) kioskTableGrp.style.display = 'flex';
        } else {
          if (lockedBadge) lockedBadge.style.display = 'none';
          if (manualWrap) manualWrap.style.display = 'none';
          if (kioskTableGrp) kioskTableGrp.style.display = 'none';
        }
      }

      // Tap context badge to toggle passively
      var pillElem = document.getElementById('tableContextPill');
      if (pillElem) {
        pillElem.addEventListener('click', function() {
          currentOrderType = (currentOrderType === 'dine_in') ? 'take_out' : 'dine_in';
          updateContextBadge();
          updateCartUI();
          syncCheckoutTableDisplay();
        });
      }

      // 2. Fetch Menu Items & Live Stock Availability
      var menuData = [];
      var stockOptions = [];
      var stockAvailability = {};
      var itemAvailability = {};

      async function checkSystemSettings() {
        try {
          var res = await fetch('api/settings.php?action=status', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            if (data && data.settings) {
              isTableQREnabled = (data.settings.table_qr_ordering_enabled !== false);
            }
          }
        } catch (e) {
          console.warn('System settings check warning:', e);
        }
        updatePausedBanner();
      }

      async function loadStockAvailability() {
        try {
          var res = await fetch('api/stock.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            stockOptions = data.options || [];
            stockAvailability = data.availability || {};
            itemAvailability = data.item_availability || {};
          }
        } catch (e) {
          console.warn('Stock status fetch error:', e);
        }
      }

      async function loadMenu() {
        await checkSystemSettings();
        await loadStockAvailability();

        try {
          var res = await fetch('api/menu.php');
          if (res.ok) {
            var data = await res.json();
            menuData = data.items || [];
          }
        } catch (e) {
          console.warn('Live API fetch error, using cache:', e);
        }

        // Fallback default items if database is empty or offline
        if (!menuData || menuData.length === 0) {
          menuData = [
            { id: 'hc-spanish', category: 'house-coffee', name: 'Spanish Latte', price: 120, description: 'Espresso, condensed milk, fresh milk. Sweet and creamy.', image: 'images/menu/hc-spanish.webp', tags: [] },
            { id: 'hc-salted-caramel', category: 'house-coffee', name: 'Salted Caramel', price: 120, description: 'Espresso, house caramel syrup, sea salt, fresh milk.', image: 'images/menu/hc-salted-caramel.webp', tags: [] },
            { id: 'mat-latte', category: 'matcha', name: 'Matcha Latte', price: 120, description: 'Japanese Uji green tea whisked with fresh milk.', image: 'images/menu/mat-latte.webp', tags: [] },
            { id: 'hs-choco', category: 'house-specials', name: 'Dark Choco', price: 90, description: 'Dark cocoa whisked with fresh whole milk.', image: 'images/menu/hs-choco.webp', tags: [] }
          ];
        }

        document.getElementById('catalogLoadingNotice').style.display = 'none';
        document.getElementById('drinksCatalogGrid').style.display = 'grid';
        renderCatalog('all');
      }

      function renderCatalog(filterCat) {
        var grid = document.getElementById('drinksCatalogGrid');
        var filtered = (filterCat === 'all')
          ? menuData
          : menuData.filter(function(i) { return i.category === filterCat; });

        grid.innerHTML = filtered.map(function(item) {
          var isItemSoldOut = (itemAvailability[item.id] === false);
          var buttonText = isItemSoldOut ? 'Sold Out' : '+ Add';
          var cardClass = isItemSoldOut ? 'drink-card is-sold-out' : 'drink-card';
          var btnDisabled = isItemSoldOut ? 'disabled' : '';

          return `
            <div class="${cardClass}">
              <div class="drink-img-wrap">
                <img src="${item.image || 'images/menu/hc-spanish.webp'}" alt="${item.name}" loading="lazy">
              </div>
              <div class="drink-meta-row">
                <h4 class="drink-name">${item.name}</h4>
                <span class="drink-price">₱${parseFloat(item.price).toFixed(2)}</span>
              </div>
              <p class="drink-desc">${item.description || 'Fresh handcrafted beverage.'}</p>
              <button type="button" class="btn-customize-add btn-open-custom" data-item-id="${item.id}" ${btnDisabled}>
                ${isItemSoldOut ? '🚫' : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>'}
                <span>${buttonText}</span>
              </button>
            </div>
          `;
        }).join('');

        // Attach Customizer Click Triggers
        document.querySelectorAll('.btn-open-custom:not([disabled])').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var itemId = btn.getAttribute('data-item-id');
            openCustomizer(itemId);
          });
        });
      }

      // Category filter pills
      document.querySelectorAll('.cat-pill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.cat-pill-btn').forEach(function(b) { b.classList.remove('active'); });
          btn.classList.add('active');
          renderCatalog(btn.getAttribute('data-cat'));
        });
      });

      // Leave Ordering Session entirely (Closes tab/window or navigates to about:blank)
      function leaveWebEntirely() {
        try {
          sessionStorage.clear();
          localStorage.removeItem('becoffee_cart');
        } catch(e) {}

        // Attempt 1: Close window/tab
        window.close();

        // Attempt 2: If browser blocks window.close(), navigate to about:blank to exit website entirely
        setTimeout(function() {
          window.location.replace('about:blank');
        }, 300);
      }

      var btnLeaveSession = document.getElementById('btnLeaveSession');
      if (btnLeaveSession) {
        btnLeaveSession.addEventListener('click', function(e) {
          e.preventDefault();
          leaveWebEntirely();
        });
      }

      var btnExpiredLeaveNow = document.getElementById('btnExpiredLeaveNow');
      if (btnExpiredLeaveNow) {
        btnExpiredLeaveNow.addEventListener('click', function(e) {
          e.preventDefault();
          leaveWebEntirely();
        });
      }

      // 10-Minute Table Ordering Session Countdown Timer & Inactivity Auto-Leave (Only for Table QR scans)
      (function initOrderPrepTimer() {
        var clockEl = document.getElementById('prepCountdownClock');
        var timerWrap = document.getElementById('orderPrepTimer');
        if (!clockEl) return;

        // ONLY activate timer for Table QR scans (?table=N); remove in walk-in / POS mode
        if (isWalkin || !initialTable) {
          if (timerWrap) timerWrap.style.display = 'none';
          return;
        }
        if (timerWrap) timerWrap.style.display = 'inline-flex';

        var STORAGE_KEY = 'becoffee_order_prep_end';
        var TEN_MINUTES_MS = 10 * 60 * 1000;
        var now = Date.now();
        var rawEnd = sessionStorage.getItem(STORAGE_KEY);
        var endTime = rawEnd ? parseInt(rawEnd, 10) : null;

        var expiredHandled = false;

        if (!endTime || isNaN(endTime)) {
          endTime = now + TEN_MINUTES_MS;
          sessionStorage.setItem(STORAGE_KEY, endTime.toString());
        } else if (endTime <= now) {
          if (!sessionStorage.getItem('active_ticket_ref')) {
            expiredHandled = true;
            triggerAutoLeave();
            return;
          }
        }

        function updateClock() {
          // If an active order ticket has already been submitted, hide/stop the ordering limit timer
          if (sessionStorage.getItem('active_ticket_ref')) {
            if (timerWrap) timerWrap.style.display = 'none';
            return;
          }

          var remainingMs = Math.max(0, endTime - Date.now());
          var totalSec = Math.floor(remainingMs / 1000);
          var mins = Math.floor(totalSec / 60);
          var secs = totalSec % 60;
          clockEl.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

          // Visual warning when under 2 minutes
          if (totalSec <= 120 && totalSec > 0 && timerWrap) {
            timerWrap.style.borderColor = 'rgba(239, 68, 68, 0.6)';
            timerWrap.style.background = 'rgba(239, 68, 68, 0.15)';
            clockEl.style.color = '#FCA5A5';
          }

          if (remainingMs <= 0 && !expiredHandled) {
            expiredHandled = true;
            triggerAutoLeave();
          }
        }

        function triggerAutoLeave() {
          try {
            sessionStorage.removeItem('becoffee_order_type');
            sessionStorage.removeItem('becoffee_table_num');
            sessionStorage.removeItem('becoffee_table_locked');
            sessionStorage.removeItem('becoffee_dining_chosen');
            sessionStorage.removeItem('becoffee_cart');
            sessionStorage.removeItem('active_ticket_ref');
            sessionStorage.removeItem('active_ticket_queue');
            sessionStorage.removeItem(STORAGE_KEY);
          } catch(e) {}

          var modal = document.getElementById('tableExpiredModal');
          if (modal) {
            modal.style.display = 'flex';
            var leaveBtn = document.getElementById('btnExpiredLeaveNow');
            if (leaveBtn) {
              leaveBtn.onclick = function() { leaveWebEntirely(); };
            }
            setTimeout(function() {
              leaveWebEntirely();
            }, 2500);
          } else {
            leaveWebEntirely();
          }
        }

        updateClock();
        setInterval(updateClock, 1000);
      })();

      // 3. Customization Modal Logic & Stock Lockouts
      var activeCustomItem = null;
      var currentCustomTemp = 'Iced';
      var currentCustomAddon = 'No Add-on';
      var currentAddonSurcharge = 0;
      var currentCustomSweetness = 'Less Sweet (75%)';
      var currentCustomQty = 1;

      // Dynamic ADD ONS Renderer for Customer Modal
      function renderCustomizerAddons() {
        var container = document.getElementById('addonRadioGroup');
        if (!container) return;

        var addons = stockOptions.filter(function(o) { return o.category_type === 'addon'; });
        if (addons.length === 0) {
          addons = [
            { option_key: 'No Add-on', option_label: 'No Add-on (+₱0)', surcharge: 0 },
            { option_key: 'Regular Milk', option_label: 'Regular Milk (+₱0)', surcharge: 0 },
            { option_key: 'Oat Milk', option_label: 'Oat Milk (+₱30)', surcharge: 30 },
            { option_key: 'Almond Milk', option_label: 'Almond Milk (+₱30)', surcharge: 30 }
          ];
        } else {
          // Ensure No Add-on appears first if present
          addons.sort(function(a, b) {
            if (a.option_key === 'No Add-on') return -1;
            if (b.option_key === 'No Add-on') return 1;
            if (a.option_key === 'Regular Milk') return -1;
            if (b.option_key === 'Regular Milk') return 1;
            return 0;
          });
        }

        // Auto-select first available add-on if currently selected is missing or sold out
        var currentOpt = addons.find(function(a) { return a.option_key === currentCustomAddon; });
        if (!currentOpt || stockAvailability[currentCustomAddon] === false) {
          var firstAvail = addons.find(function(a) { return stockAvailability[a.option_key] !== false && a.is_available != 0; });
          if (firstAvail) {
            currentCustomAddon = firstAvail.option_key;
            currentAddonSurcharge = parseFloat(firstAvail.surcharge) || 0;
          }
        }

        container.innerHTML = addons.map(function(opt) {
          var isAvail = (stockAvailability[opt.option_key] !== false && opt.is_available != 0);
          var isSelected = (opt.option_key === currentCustomAddon);
          var cls = 'pill-radio-opt' + (isSelected ? ' active' : '') + (!isAvail ? ' is-sold-out' : '');
          var surchargeNum = parseFloat(opt.surcharge) || 0;
          return `
            <div class="${cls}" data-val="${opt.option_key}" data-surcharge="${surchargeNum}">
              ${opt.option_label}
              ${!isAvail ? '<span class="sold-out-badge">Sold Out</span>' : ''}
            </div>
          `;
        }).join('');

        // Attach click listeners to addon pills
        container.querySelectorAll('.pill-radio-opt').forEach(function(p) {
          p.addEventListener('click', function() {
            if (p.classList.contains('is-sold-out')) return;
            currentCustomAddon = p.getAttribute('data-val');
            currentAddonSurcharge = parseFloat(p.getAttribute('data-surcharge')) || 0;
            syncCustomizerPills();
            recalcCustomPrice();
          });
        });
      }

      function applyOptionStockAvailability() {
        // Temperature
        var availableTemp = null;
        document.querySelectorAll('#tempRadioGroup .pill-radio-opt').forEach(function(p) {
          var val = p.getAttribute('data-val');
          var isAvailable = (stockAvailability[val] !== false);
          p.classList.toggle('is-sold-out', !isAvailable);
          
          var oldBadge = p.querySelector('.sold-out-badge');
          if (oldBadge) oldBadge.remove();

          if (!isAvailable) {
            p.insertAdjacentHTML('beforeend', '<span class="sold-out-badge">Sold Out</span>');
          } else if (!availableTemp) {
            availableTemp = val;
          }
        });
        if (stockAvailability[currentCustomTemp] === false && availableTemp) {
          currentCustomTemp = availableTemp;
        }

        // Sweetness
        var availableSweetness = null;
        document.querySelectorAll('#sweetnessRadioGroup .pill-radio-opt').forEach(function(p) {
          var val = p.getAttribute('data-val');
          var isAvailable = (stockAvailability[val] !== false);
          p.classList.toggle('is-sold-out', !isAvailable);

          var oldBadge = p.querySelector('.sold-out-badge');
          if (oldBadge) oldBadge.remove();

          if (!isAvailable) {
            p.insertAdjacentHTML('beforeend', '<span class="sold-out-badge">Sold Out</span>');
          } else if (!availableSweetness) {
            availableSweetness = val;
          }
        });
        if (stockAvailability[currentCustomSweetness] === false && availableSweetness) {
          currentCustomSweetness = availableSweetness;
        }
      }

      async function openCustomizer(itemId) {
        await loadStockAvailability();
        activeCustomItem = menuData.find(function(i) { return i.id === itemId; }) || menuData[0];
        document.getElementById('customItemName').textContent = activeCustomItem.name;
        document.getElementById('customItemImg').src = activeCustomItem.image || 'images/menu/hc-spanish.webp';
        document.getElementById('customItemBasePrice').textContent = `₱${parseFloat(activeCustomItem.price).toFixed(2)}`;

        // Reset selections to defaults (No Add-on is default, Regular Milk available)
        currentCustomTemp = 'Iced';
        currentCustomAddon = 'No Add-on';
        currentAddonSurcharge = 0;
        currentCustomSweetness = 'Less Sweet (75%)';
        currentCustomQty = 1;
        document.getElementById('customNotesInput').value = '';
        document.getElementById('customQtyDisplay').textContent = '1';

        renderCustomizerAddons();
        applyOptionStockAvailability();
        syncCustomizerPills();
        recalcCustomPrice();
        document.getElementById('customizationModal').classList.add('active');
      }

      function syncCustomizerPills() {
        document.querySelectorAll('#tempRadioGroup .pill-radio-opt').forEach(function(p) {
          p.classList.toggle('active', p.getAttribute('data-val') === currentCustomTemp);
        });
        document.querySelectorAll('#addonRadioGroup .pill-radio-opt').forEach(function(p) {
          p.classList.toggle('active', p.getAttribute('data-val') === currentCustomAddon);
        });
        document.querySelectorAll('#sweetnessRadioGroup .pill-radio-opt').forEach(function(p) {
          p.classList.toggle('active', p.getAttribute('data-val') === currentCustomSweetness);
        });
      }

      function recalcCustomPrice() {
        var base = parseFloat(activeCustomItem.price) || 120;
        var unitTotal = base + currentAddonSurcharge;
        var lineTotal = unitTotal * currentCustomQty;
        document.getElementById('customModalTotalPrice').textContent = `₱${lineTotal.toFixed(2)}`;
      }

      // Option clicks (guarded against sold-out items)
      document.querySelectorAll('#tempRadioGroup .pill-radio-opt').forEach(function(p) {
        p.addEventListener('click', function() {
          if (p.classList.contains('is-sold-out')) return;
          currentCustomTemp = p.getAttribute('data-val');
          syncCustomizerPills();
          recalcCustomPrice();
        });
      });
      document.querySelectorAll('#sweetnessRadioGroup .pill-radio-opt').forEach(function(p) {
        p.addEventListener('click', function() {
          if (p.classList.contains('is-sold-out')) return;
          currentCustomSweetness = p.getAttribute('data-val');
          syncCustomizerPills();
        });
      });

      // Quantity buttons
      document.getElementById('btnQtyMinus').addEventListener('click', function() {
        if (currentCustomQty > 1) {
          currentCustomQty--;
          document.getElementById('customQtyDisplay').textContent = currentCustomQty;
          recalcCustomPrice();
        }
      });
      document.getElementById('btnQtyPlus').addEventListener('click', function() {
        currentCustomQty++;
        document.getElementById('customQtyDisplay').textContent = currentCustomQty;
        recalcCustomPrice();
      });

      document.getElementById('closeCustomModalBtn').addEventListener('click', function() {
        document.getElementById('customizationModal').classList.remove('active');
      });

      // 4. Shopping Cart System
      var cart = [];

      document.getElementById('btnSubmitCustomItem').addEventListener('click', function() {
        var notes = document.getElementById('customNotesInput').value.trim();
        var base = parseFloat(activeCustomItem.price) || 120;
        var unitPrice = base + currentAddonSurcharge;

        cart.push({
          id: activeCustomItem.id,
          name: activeCustomItem.name,
          temperature: currentCustomTemp,
          milk_option: currentCustomAddon,
          sweetness_level: currentCustomSweetness,
          custom_notes: notes,
          quantity: currentCustomQty,
          unit_price: unitPrice,
          subtotal: unitPrice * currentCustomQty
        });

        document.getElementById('customizationModal').classList.remove('active');
        updateCartUI();
      });

      function escapeHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function updateCartUI() {
        var totalQty = cart.reduce(function(sum, it) { return sum + it.quantity; }, 0);
        var subtotal = cart.reduce(function(sum, it) { return sum + it.subtotal; }, 0);
        var ecoFee = (currentOrderType === 'take_out') ? 15.00 : 0.00;
        var grandTotal = subtotal + ecoFee;

        // 1. Mobile Floating Cart Bar
        var bar = document.getElementById('floatingCartBar');
        if (bar) {
          if (totalQty > 0) {
            bar.style.display = 'flex';
            document.getElementById('cartCountBadge').textContent = totalQty;
            document.getElementById('cartBarTotal').textContent = `₱${grandTotal.toFixed(2)}`;
            document.getElementById('cartBarSubtitle').textContent = `${totalQty} item${totalQty > 1 ? 's' : ''} in tray`;
          } else {
            bar.style.display = 'none';
          }
        }

        // 2. Mobile Cart Modal Items
        var listWrap = document.getElementById('cartItemsList');
        if (listWrap) {
          listWrap.innerHTML = cart.map(function(item, idx) {
            return `
              <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <strong style="color: #FFF; font-size: 0.95rem;">${item.quantity}x ${escapeHtml(item.name)}</strong>
                  <div style="font-size: 0.75rem; color: #DF9B64; margin-top: 0.2rem;">
                    ${item.temperature} · ${item.milk_option} · ${item.sweetness_level}
                  </div>
                  ${item.custom_notes ? `<div style="font-size: 0.75rem; color: #A99B92; font-style: italic;">"${escapeHtml(item.custom_notes)}"</div>` : ''}
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                  <span style="font-family: var(--font-mono); font-weight: 700; color: #FFF;">₱${item.subtotal.toFixed(2)}</span>
                  <button type="button" class="btn-remove-item" data-idx="${idx}" style="background: none; border: none; color: #F87171; cursor: pointer; padding: 0.25rem;">&times;</button>
                </div>
              </div>
            `;
          }).join('');

          listWrap.querySelectorAll('.btn-remove-item').forEach(function(b) {
            b.addEventListener('click', function() {
              var i = parseInt(b.getAttribute('data-idx'), 10);
              cart.splice(i, 1);
              updateCartUI();
            });
          });
        }

        // 3. Desktop / Kiosk Order Sidebar
        var kioskBadge = document.getElementById('kioskItemCountBadge');
        if (kioskBadge) kioskBadge.textContent = `(${totalQty})`;
        
        var kioskClearBtn = document.getElementById('kioskBtnClearTray');
        if (kioskClearBtn) kioskClearBtn.style.display = totalQty > 0 ? 'inline-block' : 'none';

        var kioskBtnDine = document.getElementById('kioskBtnDineIn');
        var kioskBtnTake = document.getElementById('kioskBtnTakeOut');
        if (kioskBtnDine) kioskBtnDine.classList.toggle('active', currentOrderType === 'dine_in');
        if (kioskBtnTake) kioskBtnTake.classList.toggle('active', currentOrderType === 'take_out');

        var kioskList = document.getElementById('kioskItemsList');
        if (kioskList) {
          if (totalQty === 0) {
            kioskList.innerHTML = `
              <div class="kiosk-empty-state" id="kioskEmptyState">
                <div class="kiosk-empty-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                </div>
                <h4>Your tray is empty</h4>
                <p>Tap any drink to add it to your order.</p>
              </div>
            `;
            // Switch back to tray panel if empty
            var viewTray = document.getElementById('kioskViewTray');
            var viewCheckout = document.getElementById('kioskViewCheckout');
            if (viewTray && viewCheckout) {
              viewCheckout.style.display = 'none';
              viewTray.style.display = 'flex';
            }
          } else {
            kioskList.innerHTML = cart.map(function(item, idx) {
              return `
                <div class="kiosk-item-card" data-idx="${idx}">
                  <div class="kiosk-item-head">
                    <span class="kiosk-item-name">${item.quantity}x ${escapeHtml(item.name)}</span>
                    <span class="kiosk-item-price">₱${item.subtotal.toFixed(2)}</span>
                  </div>
                  <div class="kiosk-item-specs">
                    ${item.temperature} · ${item.sweetness_level} · ${item.milk_option}
                  </div>
                  ${item.custom_notes ? `<div class="kiosk-item-note">Note: "${escapeHtml(item.custom_notes)}"</div>` : ''}
                  <div class="kiosk-item-foot">
                    <div class="kiosk-item-stepper">
                      <button type="button" class="kiosk-step-btn btn-kiosk-minus" data-idx="${idx}" aria-label="Decrease quantity">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                      </button>
                      <span class="kiosk-step-val">${item.quantity}</span>
                      <button type="button" class="kiosk-step-btn btn-kiosk-plus" data-idx="${idx}" aria-label="Increase quantity">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                      </button>
                    </div>
                    <button type="button" class="kiosk-btn-remove btn-kiosk-remove" data-idx="${idx}" title="Remove item" aria-label="Remove item">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  </div>
                </div>
              `;
            }).join('');

            kioskList.querySelectorAll('.btn-kiosk-plus').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var i = parseInt(btn.getAttribute('data-idx'), 10);
                if (cart[i]) {
                  cart[i].quantity++;
                  cart[i].subtotal = cart[i].quantity * cart[i].unit_price;
                  updateCartUI();
                }
              });
            });

            kioskList.querySelectorAll('.btn-kiosk-minus').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var i = parseInt(btn.getAttribute('data-idx'), 10);
                if (cart[i]) {
                  cart[i].quantity--;
                  if (cart[i].quantity <= 0) {
                    cart.splice(i, 1);
                  } else {
                    cart[i].subtotal = cart[i].quantity * cart[i].unit_price;
                  }
                  updateCartUI();
                }
              });
            });

            kioskList.querySelectorAll('.btn-kiosk-remove').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var i = parseInt(btn.getAttribute('data-idx'), 10);
                cart.splice(i, 1);
                updateCartUI();
              });
            });
          }
        }

        // Totals in Kiosk & Modals
        var kioskProceedBtn = document.getElementById('btnKioskProceedCheckout');
        if (kioskProceedBtn) kioskProceedBtn.disabled = (totalQty === 0);

        var kSub = document.getElementById('kioskSubtotalDisplay');
        if (kSub) kSub.textContent = `₱${subtotal.toFixed(2)}`;

        var kEcoRow = document.getElementById('kioskEcoRow');
        var kEco = document.getElementById('kioskEcoDisplay');
        if (kEcoRow && kEco) {
          kEcoRow.style.display = (currentOrderType === 'take_out') ? 'flex' : 'none';
          kEco.textContent = `₱${ecoFee.toFixed(2)}`;
        }

        var kGrand = document.getElementById('kioskGrandTotalDisplay');
        if (kGrand) kGrand.textContent = `₱${grandTotal.toFixed(2)}`;

        var kFinal = document.getElementById('kioskFinalTotalDisplay');
        if (kFinal) kFinal.textContent = `₱${grandTotal.toFixed(2)}`;

        document.getElementById('cartSubtotalDisplay').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('cartEcoFeeDisplay').textContent = `₱${ecoFee.toFixed(2)}`;
        document.getElementById('ecoFeeLabel').textContent = (currentOrderType === 'take_out') ? 'Eco-Packaging Fee' : 'Dine-in Fee (Free)';
        document.getElementById('cartGrandTotalDisplay').textContent = `₱${grandTotal.toFixed(2)}`;
        var checkoutTotalEl = document.getElementById('checkoutFinalTotal');
        if (checkoutTotalEl) {
          checkoutTotalEl.textContent = `₱${grandTotal.toFixed(2)}`;
        }
      }

      // Kiosk Header Dining Mode Toggles
      var kioskBtnDine = document.getElementById('kioskBtnDineIn');
      var kioskBtnTake = document.getElementById('kioskBtnTakeOut');
      if (kioskBtnDine) {
        kioskBtnDine.addEventListener('click', function() {
          currentOrderType = 'dine_in';
          sessionStorage.setItem('becoffee_order_type', 'dine_in');
          updateContextBadge();
          updateCartUI();
          syncCheckoutTableDisplay();
        });
      }
      if (kioskBtnTake) {
        kioskBtnTake.addEventListener('click', function() {
          currentOrderType = 'take_out';
          sessionStorage.setItem('becoffee_order_type', 'take_out');
          updateContextBadge();
          updateCartUI();
          syncCheckoutTableDisplay();
        });
      }

      // Kiosk Clear Tray
      var kioskClearBtn = document.getElementById('kioskBtnClearTray');
      if (kioskClearBtn) {
        kioskClearBtn.addEventListener('click', function() {
          if (cart.length === 0) return;
          cart = [];
          updateCartUI();
        });
      }

      // Kiosk Proceed to In-Place Checkout
      var btnKioskCheckout = document.getElementById('btnKioskProceedCheckout');
      if (btnKioskCheckout) {
        btnKioskCheckout.addEventListener('click', function() {
          if (cart.length === 0) return;
          var viewTray = document.getElementById('kioskViewTray');
          var viewCheckout = document.getElementById('kioskViewCheckout');
          if (viewTray && viewCheckout) {
            viewTray.style.display = 'none';
            viewCheckout.style.display = 'flex';
          }
          // Sync table input
          var tableGrp = document.getElementById('kioskTableGroup');
          var tableInp = document.getElementById('kioskTableInput');
          if (tableGrp) tableGrp.style.display = (!isWalkin && currentOrderType === 'dine_in') ? 'flex' : 'none';
          if (tableInp) {
            if (currentTableNumber) tableInp.value = currentTableNumber;
            tableInp.readOnly = true;
          }
        });
      }

      // Kiosk Back to Tray
      var btnKioskBack = document.getElementById('btnKioskBackToTray');
      if (btnKioskBack) {
        btnKioskBack.addEventListener('click', function() {
          var viewTray = document.getElementById('kioskViewTray');
          var viewCheckout = document.getElementById('kioskViewCheckout');
          if (viewTray && viewCheckout) {
            viewCheckout.style.display = 'none';
            viewTray.style.display = 'flex';
          }
        });
      }

      // Kiosk Payment Method Pills
      document.querySelectorAll('#kioskPaymentPills .kiosk-pay-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
          document.querySelectorAll('#kioskPaymentPills .kiosk-pay-pill').forEach(function(p) { p.classList.remove('active'); });
          pill.classList.add('active');
          selectedPaymentMethod = pill.getAttribute('data-val');
          document.querySelectorAll('#paymentMethodRadioGroup .pill-radio-opt').forEach(function(p) {
            p.classList.toggle('active', p.getAttribute('data-val') === selectedPaymentMethod);
          });
        });
      });

      // Cart modal triggers
      document.getElementById('floatingCartBar').addEventListener('click', function() {
        document.getElementById('cartModal').classList.add('active');
      });
      document.getElementById('closeCartModalBtn').addEventListener('click', function() {
        document.getElementById('cartModal').classList.remove('active');
      });

      // 5. Checkout Modal Logic
      document.getElementById('btnProceedToCheckout').addEventListener('click', function() {
        document.getElementById('cartModal').classList.remove('active');
        
        // Sync Order Type Buttons & Table Display
        document.querySelectorAll('#orderTypeRadioGroup .pill-radio-opt').forEach(function(p) {
          p.classList.toggle('active', p.getAttribute('data-val') === currentOrderType);
        });
        syncCheckoutTableDisplay();

        document.getElementById('checkoutModal').classList.add('active');
      });

      document.getElementById('closeCheckoutModalBtn').addEventListener('click', function() {
        document.getElementById('checkoutModal').classList.remove('active');
      });

      // Checkout order type switcher
      document.querySelectorAll('#orderTypeRadioGroup .pill-radio-opt').forEach(function(p) {
        p.addEventListener('click', function() {
          document.querySelectorAll('#orderTypeRadioGroup .pill-radio-opt').forEach(function(b) { b.classList.remove('active'); });
          p.classList.add('active');
          currentOrderType = p.getAttribute('data-val');
          syncCheckoutTableDisplay();
          updateContextBadge();
          updateCartUI();
        });
      });

      // Sync Table input changes
      var checkoutTableInputEl = document.getElementById('checkoutTableInput');
      if (checkoutTableInputEl) {
        checkoutTableInputEl.addEventListener('input', function() {
          var val = this.value.trim();
          if (val) {
            currentTableNumber = val;
            updateContextBadge();
          }
        });
      }

      // Checkout payment switcher
      var selectedPaymentMethod = 'cash';
      document.querySelectorAll('#paymentMethodRadioGroup .pill-radio-opt').forEach(function(p) {
        p.addEventListener('click', function() {
          document.querySelectorAll('#paymentMethodRadioGroup .pill-radio-opt').forEach(function(b) { b.classList.remove('active'); });
          p.classList.add('active');
          selectedPaymentMethod = p.getAttribute('data-val');
          document.getElementById('gcashNoticeWrap').style.display = (selectedPaymentMethod === 'gcash') ? 'block' : 'none';
        });
      });

      // 6. Submit Order & Lock into Live Waiting Ticket (Starts Fresh at 00:00)
      var activeOrderRef = null;
      var pollTimer = null;
      var stopwatchTimer = null;
      var stopwatchSeconds = 0;


      async function executeOrderSubmission(fromKiosk, gcashRef) {
        if (cart.length === 0) return;

        if (currentOrderType === 'dine_in' && !isTableQREnabled) {
          await SystemDialog.alert('Table QR ordering is temporarily paused by cafe management. Please place your order at the counter or switch to Take Out.', { title: 'Ordering Paused', type: 'warning' });
          return;
        }

        var nameInput = fromKiosk ? document.getElementById('kioskNameInput') : document.getElementById('checkoutNameInput');
        var phoneInput = fromKiosk ? document.getElementById('kioskPhoneInput') : document.getElementById('checkoutPhoneInput');
        var tableInput = fromKiosk ? document.getElementById('kioskTableInput') : document.getElementById('checkoutTableInput');

        var name = (nameInput && nameInput.value.trim()) || (isWalkin ? 'Walk-in Customer' : 'Guest Customer');
        var phone = isWalkin ? '' : ((phoneInput && phoneInput.value.trim()) || '');
        var table = (!isWalkin && currentOrderType === 'dine_in') ? (currentTableNumber || (tableInput && tableInput.value.trim()) || '1') : null;

        var orderSource = isWalkin ? 'registrar' : (initialTable || isTableLocked || currentOrderType === 'dine_in' ? 'qr_link' : 'registrar');

        var payload = {
          customer_name: name,
          customer_phone: phone,
          order_type: currentOrderType,
          table_number: table,
          order_source: orderSource,
          payment_method: selectedPaymentMethod,
          payment_reference: gcashRef || '',
          items: cart
        };

        var submitBtn = fromKiosk ? document.getElementById('btnKioskPlaceOrderFinal') : document.getElementById('btnPlaceOrderFinal');
        if (submitBtn) submitBtn.disabled = true;
        var originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) submitBtn.textContent = 'Transmitting Order to Barista...';

        console.log('PLACE ORDER PAYLOAD:', JSON.stringify(payload));
        try {
          var res = await fetch('api/orders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
          var data = await res.json();
          console.log('PLACE ORDER RESPONSE:', res.status, data);

          if (data.success) {
            var placedItems = [...cart];
            var placedOrderType = payload.order_type;
            var placedTable = payload.table_number;
            var placedName = payload.customer_name;
            var placedPay = payload.payment_method;
            var placedRef = data.order_reference;
            var placedQueue = data.queue_number;
            var grandTotal = payload.items.reduce(function(s, it) { return s + ((parseFloat(it.unit_price) || 0) * (parseInt(it.quantity, 10) || 1)); }, 0);

            cart = [];
            updateCartUI();
            var checkoutModal = document.getElementById('checkoutModal');
            if (checkoutModal) checkoutModal.classList.remove('active');

            // 1. Snappy 0.5-second animated checkmark HUD
            triggerSuccessHud(placedQueue, isWalkin);

            if (isWalkin) {
              // POS WALK-IN MODE: Stay in POS, clear name field, return sidebar to fresh tray
              var nameInputKiosk = document.getElementById('kioskNameInput');
              if (nameInputKiosk) nameInputKiosk.value = '';
              var nameInputCheckout = document.getElementById('checkoutNameInput');
              if (nameInputCheckout) nameInputCheckout.value = '';

              var viewTray = document.getElementById('kioskViewTray');
              var viewCheckout = document.getElementById('kioskViewCheckout');
              if (viewTray && viewCheckout) {
                viewCheckout.style.display = 'none';
                viewTray.style.display = 'flex';
              }
              // Register is immediately ready for next walk-in customer!
            } else {
              // TABLE QR MODE: 0.5s HUD already confirmed placement.
              // Mark order active in sessionStorage to silence the 10-minute table ordering timer.
              sessionStorage.setItem('active_ticket_ref', placedRef);
              sessionStorage.setItem('active_ticket_queue', placedQueue);
            }
          } else {
            console.error('Order placement error:', data.error);
            await SystemDialog.alert('Order placement error: ' + (data.error || 'Server rejected request.'), { title: 'Order Failed', type: 'danger' });
          }
        } catch (err) {
          console.error('Network connection error:', err);
          await SystemDialog.alert('Network connection error: ' + err.message, { title: 'Connection Error', type: 'danger' });
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
          }
        }
      }

      function handleOrderPlacement(fromKiosk) {
        if (cart.length === 0) return;
        executeOrderSubmission(fromKiosk, '');
      }

      document.getElementById('btnPlaceOrderFinal').addEventListener('click', function() {
        handleOrderPlacement(false);
      });
      var btnKioskPlace = document.getElementById('btnKioskPlaceOrderFinal');
      if (btnKioskPlace) {
        btnKioskPlace.addEventListener('click', function() {
          handleOrderPlacement(true);
        });
      }

      // 7. 0.5s Checkmark HUD & Digital Paid Order Receipt Logic
      function triggerSuccessHud(queueNum, isWalkinMode) {
        var hud = document.getElementById('orderSuccessHud');
        var title = document.getElementById('hudSuccessTitle');
        var sub = document.getElementById('hudSuccessSub');
        if (!hud) return;

        if (title) title.textContent = isWalkinMode ? 'Order Transmitted to Kitchen!' : 'Order Placed & Sent!';
        if (sub) sub.textContent = `Queue #${queueNum} · Direct to KDS`;

        hud.classList.add('active');
        setTimeout(function() {
          hud.classList.remove('active');
        }, 550);
      }

      var currentReceiptData = null;

      function showDigitalReceipt(orderRef, queueNum, items, orderType, tableNum, custName, payMethod, total, gcashRef) {
        activeOrderRef = orderRef;
        currentReceiptData = {
          ref: orderRef,
          queue: queueNum,
          customerName: custName || 'Guest Customer',
          dining: (orderType === 'dine_in') ? (tableNum ? `Table #${tableNum} (Dine-In)` : 'Dine-In') : 'Take-Out Pickup',
          items: items,
          total: total,
          timePlaced: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };
        sessionStorage.setItem('active_ticket_ref', orderRef);
        sessionStorage.setItem('active_ticket_queue', queueNum);

        var receipt = document.getElementById('orderReceiptScreen');
        if (!receipt) return;

        var queueEl = document.getElementById('receiptQueueNum');
        if (queueEl) queueEl.textContent = `#${queueNum}`;

        var refEl = document.getElementById('receiptRefVal');
        if (refEl) refEl.textContent = orderRef || '—';

        var custEl = document.getElementById('receiptCustomerVal');
        if (custEl) custEl.textContent = custName || 'Guest Customer';

        var diningEl = document.getElementById('receiptDiningVal');
        if (diningEl) {
          diningEl.textContent = (orderType === 'dine_in')
            ? (tableNum ? `Table #${tableNum} (Dine-In)` : 'Dine-In (Counter Pick-Up)')
            : 'Take-Out (Counter Pickup)';
        }

        var payBadge = document.getElementById('receiptPaidBadge');
        var payText = document.getElementById('receiptPaidText');
        var isGcash = (payMethod === 'gcash');
        if (payBadge && payText) {
          if (isGcash) {
            payBadge.style.background = 'rgba(223, 155, 100, 0.2)';
            payBadge.style.borderColor = 'rgba(223, 155, 100, 0.45)';
            payBadge.style.color = '#FAF7F2';
            payText.textContent = 'GCASH · Pay via Table Sticker';
          } else {
            payBadge.style.background = 'rgba(16, 185, 129, 0.15)';
            payBadge.style.borderColor = 'rgba(16, 185, 129, 0.35)';
            payBadge.style.color = '#34D399';
            payText.textContent = 'CASH · Pay at Counter/Table';
          }
        }

        var noticeEl = document.getElementById('receiptStaffNotice');
        if (noticeEl) {
          noticeEl.innerHTML = isGcash
            ? `<strong>📱 Pay via Table GCash Sticker</strong>Please scan the GCash QR sticker on Table #${tableNum || 'Your Table'} to pay <strong>₱${parseFloat(total).toFixed(2)}</strong>. Show your GCash transaction receipt to the server when your drinks arrive!`
            : `<strong>💵 Cash Payment</strong>Please have <strong>₱${parseFloat(total).toFixed(2)}</strong> ready for the server when your order arrives at Table #${tableNum || 'Your Table'}, or pay at the counter.`;
        }

        var totalEl = document.getElementById('receiptTotalVal');
        if (totalEl) totalEl.textContent = `₱${parseFloat(total).toFixed(2)}`;

        var now = new Date();
        var timeEl = document.getElementById('receiptTimeVal');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        var itemsContainer = document.getElementById('receiptItemsBody');
        if (itemsContainer) {
          itemsContainer.innerHTML = items.map(function(it) {
            var specs = [it.temperature, it.milk_option, it.sweetness_level].filter(Boolean).join(' · ');
            var lineTotal = (parseFloat(it.unit_price) || 0) * (parseInt(it.quantity, 10) || 1);
            return `
              <div class="receipt-line">
                <div class="receipt-line-left">
                  <span class="receipt-line-name">${it.quantity}x ${escapeHtml(it.name || it.item_name)}</span>
                  <span class="receipt-line-specs">${escapeHtml(specs)}</span>
                </div>
                <span class="receipt-line-price">₱${lineTotal.toFixed(2)}</span>
              </div>
            `;
          }).join('');
        }

        receipt.classList.add('active');

        // Start background polling to play cafe chime and update badge when barista completes order
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollReceiptStatus, 2500);
      }

      async function pollReceiptStatus() {
        if (!activeOrderRef) return;
        try {
          var res = await fetch(`api/orders.php?reference=${encodeURIComponent(activeOrderRef)}`, { cache: 'no-store' });
          if (!res.ok) return;
          var data = await res.json();
          if (!data.success || !data.order) return;

          var ord = data.order;
          if (ord.status === 'completed') {
            clearInterval(pollTimer);
            var badge = document.getElementById('receiptPaidBadge');
            var text = document.getElementById('receiptPaidText');
            if (badge && text) {
              badge.style.background = 'rgba(16, 185, 129, 0.35)';
              badge.style.borderColor = '#10B981';
              text.textContent = '✓ ORDER READY & SERVED!';
            }
            // Gentle Cafe Chime
            try {
              var ctx = new (window.AudioContext || window.webkitAudioContext)();
              var osc = ctx.createOscillator();
              var g = ctx.createGain();
              osc.type = 'triangle';
              osc.frequency.setValueAtTime(659.25, ctx.currentTime);
              osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
              g.gain.setValueAtTime(0.2, ctx.currentTime);
              g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.8);
              osc.connect(g);
              g.connect(ctx.destination);
              osc.start();
              osc.stop(ctx.currentTime + 0.85);
            } catch (e) {}
            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
          }
        } catch (e) {
          console.warn('Receipt poll warning:', e);
        }
      }

      var btnMoreDrinks = document.getElementById('btnReceiptOrderMore');
      if (btnMoreDrinks) {
        btnMoreDrinks.addEventListener('click', function() {
          var receipt = document.getElementById('orderReceiptScreen');
          if (receipt) receipt.classList.remove('active');
        });
      }

      var btnExitReceipt = document.getElementById('btnReceiptExit');
      if (btnExitReceipt) {
        btnExitReceipt.addEventListener('click', function() {
          leaveWebEntirely();
        });
      }

      var btnDownload = document.getElementById('btnReceiptDownload');
      if (btnDownload) {
        btnDownload.addEventListener('click', function() {
          if (!currentReceiptData) return;
          try {
            var canvas = document.createElement('canvas');
            var dpr = 2;
            var width = 640;
            var itemsCount = (currentReceiptData.items || []).length;
            var height = 740 + (itemsCount * 54);
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            var ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);

            ctx.fillStyle = '#161210';
            ctx.fillRect(0, 0, width, height);

            ctx.strokeStyle = 'rgba(223, 155, 100, 0.35)';
            ctx.lineWidth = 2;
            ctx.strokeRect(16, 16, width - 32, height - 32);

            var grad = ctx.createLinearGradient(0, 16, 0, 175);
            grad.addColorStop(0, 'rgba(223, 155, 100, 0.22)');
            grad.addColorStop(1, 'rgba(223, 155, 100, 0.03)');
            ctx.fillStyle = grad;
            ctx.fillRect(18, 18, width - 36, 157);

            ctx.fillStyle = '#DF9B64';
            ctx.font = 'bold 15px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('BECOFFEE SPECIALTY ROASTERY · ZAMBOANGA', width / 2, 48);

            ctx.fillStyle = '#A99B92';
            ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('OFFICIAL ORDER RECEIPT', width / 2, 68);

            ctx.fillStyle = '#FFFFFF';
            ctx.font = '900 48px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace';
            ctx.fillText('#' + String(currentReceiptData.queue || '01').padStart(2, '0'), width / 2, 124);

            var badgeW = 220;
            var badgeH = 26;
            ctx.fillStyle = 'rgba(16, 185, 129, 0.2)';
            ctx.fillRect((width - badgeW) / 2, 138, badgeW, badgeH);
            ctx.strokeStyle = 'rgba(16, 185, 129, 0.55)';
            ctx.lineWidth = 1;
            ctx.strokeRect((width - badgeW) / 2, 138, badgeW, badgeH);

            ctx.fillStyle = '#6EE7B7';
            ctx.font = 'bold 11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('✓ CONFIRMED ORDER', width / 2, 155);

            function drawDash(yPos) {
              ctx.strokeStyle = 'rgba(255, 255, 255, 0.16)';
              ctx.lineWidth = 1;
              ctx.setLineDash([4, 4]);
              ctx.beginPath();
              ctx.moveTo(34, yPos);
              ctx.lineTo(width - 34, yPos);
              ctx.stroke();
              ctx.setLineDash([]);
            }
            drawDash(188);

            var y = 216;
            var col1X = 42;
            var col2X = 330;

            function drawPair(l1, v1, l2, v2, curY) {
              ctx.textAlign = 'left';
              ctx.fillStyle = '#8C7C72';
              ctx.font = 'bold 10px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
              ctx.fillText(l1.toUpperCase(), col1X, curY);
              ctx.fillText(l2.toUpperCase(), col2X, curY);

              ctx.fillStyle = '#FFFFFF';
              ctx.font = 'bold 13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
              ctx.fillText(val1 || '—', col1X, curY + 18);
              ctx.fillText(val2 || '—', col2X, curY + 18);
            }

            drawPair('Customer', currentReceiptData.customerName, 'Dining Option', currentReceiptData.dining, y);
            y += 44;
            drawPair('Order Reference', currentReceiptData.ref, 'Time Placed', currentReceiptData.timePlaced, y);
            y += 40;

            drawDash(y);
            y += 24;

            ctx.textAlign = 'left';
            ctx.fillStyle = '#DF9B64';
            ctx.font = 'bold 11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('ITEMIZED COFFEE SELECTION', 42, y);
            ctx.textAlign = 'right';
            ctx.fillText('AMOUNT', width - 42, y);
            y += 20;

            (currentReceiptData.items || []).forEach(function(it) {
              ctx.textAlign = 'left';
              ctx.fillStyle = '#FFFFFF';
              ctx.font = 'bold 13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
              var itemName = (it.quantity || it.qty || 1) + 'x ' + (it.name || it.item_name || 'Artisanal Drink');
              ctx.fillText(itemName, 42, y);

              ctx.textAlign = 'right';
              ctx.fillStyle = '#FDBA74';
              ctx.font = 'bold 13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace';
              var linePrice = (parseFloat(it.unit_price || it.price || 0) * (parseInt(it.quantity || it.qty, 10) || 1));
              ctx.fillText('₱' + linePrice.toFixed(2), width - 42, y);

              y += 16;
              ctx.textAlign = 'left';
              ctx.fillStyle = '#9C8E85';
              ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
              var specs = [it.temperature, it.size, it.milk_option, it.sweetness_level].filter(Boolean).join(' · ');
              ctx.fillText(specs || 'Standard Preparation', 42, y);
              y += 24;
            });

            drawDash(y);
            y += 22;

            ctx.textAlign = 'left';
            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 15px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('Total Amount', 42, y);

            ctx.textAlign = 'right';
            ctx.fillStyle = '#10B981';
            ctx.font = 'bold 18px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace';
            ctx.fillText('₱' + parseFloat(currentReceiptData.total || 0).toFixed(2), width - 42, y);
            y += 32;

            ctx.fillStyle = 'rgba(223, 155, 100, 0.12)';
            ctx.fillRect(34, y, width - 68, 50);
            ctx.strokeStyle = 'rgba(223, 155, 100, 0.3)';
            ctx.strokeRect(34, y, width - 68, 50);

            ctx.fillStyle = '#DF9B64';
            ctx.font = 'bold 11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('☕ PRESENT THIS RECEIPT AT THE BARISTA COUNTER', width / 2, y + 21);
            ctx.fillStyle = '#D6C7BE';
            ctx.font = '10px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('Show this screen or saved photo upon claiming.', width / 2, y + 38);

            var link = document.createElement('a');
            link.download = 'BeCoffee-Receipt-' + (currentReceiptData.ref || 'Order') + '.png';
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            if (typeof showSystemNotice === 'function') {
              showSystemNotice('Receipt photo downloaded!');
            }
          } catch(e) {
            console.error('Error downloading receipt photo:', e);
            alert('Receipt ready! Please take a screenshot of your screen.');
          }
        });
      }

      // 8. One-Click Dining Recovery Post-Order
      var btnSwitchDining = document.getElementById('btnSwitchDiningPostOrder');
      if (btnSwitchDining) {
        btnSwitchDining.addEventListener('click', async function() {
          if (!activeOrderRef) return;
          var tag = document.getElementById('ticketDiningTag');
          var isCurrentlyTakeout = tag && tag.textContent.includes('Take Out');

          if (isCurrentlyTakeout) {
            var tableInput = await SystemDialog.prompt('Enter your Table Number to switch to Dine In:', currentTableNumber || '1', {
              title: 'Switch to Dine In',
              confirmText: 'Assign Table',
              placeholder: 'Table number'
            });
            if (!tableInput || !tableInput.trim()) return;
            tableInput = tableInput.trim();

            try {
              var res = await fetch('api/orders.php?action=update_dining', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  reference: activeOrderRef,
                  order_type: 'dine_in',
                  table_number: tableInput
                })
              });
              var data = await res.json();
              if (data.success) {
                currentOrderType = 'dine_in';
                currentTableNumber = tableInput;
                updateContextBadge();
                updateTicketDiningDisplay('dine_in', tableInput);
                await SystemDialog.alert('Dining preference switched to Table #' + tableInput + '.', { title: 'Table Assigned', type: 'success' });
              } else {
                await SystemDialog.alert(data.error || 'Could not update dining mode.', { title: 'Update Failed', type: 'danger' });
              }
            } catch(e) {
              await SystemDialog.alert('Network error. Please inform counter staff.', { title: 'Network Error', type: 'danger' });
            }
          } else {
            var confirmed = await SystemDialog.confirm('Switch this order to Take Out (Counter Pickup)?', {
              title: 'Switch to Take Out',
              confirmText: 'Switch to Take Out'
            });
            if (!confirmed) return;
            try {
              var res = await fetch('api/orders.php?action=update_dining', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  reference: activeOrderRef,
                  order_type: 'take_out'
                })
              });
              var data = await res.json();
              if (data.success) {
                currentOrderType = 'take_out';
                updateContextBadge();
                updateTicketDiningDisplay('take_out', null);
                await SystemDialog.alert('Dining preference switched to Take Out pickup.', { title: 'Mode Updated', type: 'success' });
              } else {
                await SystemDialog.alert(data.error || 'Could not update dining mode.', { title: 'Update Failed', type: 'danger' });
              }
            } catch(e) {
              await SystemDialog.alert('Network error. Please inform counter staff.', { title: 'Network Error', type: 'danger' });
            }
          }
        });
      }

      // Dismiss Pickup Screen (Legacy guard)
      var btnDismiss = document.getElementById('btnDismissPickup');
      if (btnDismiss) {
        btnDismiss.addEventListener('click', function() {
          sessionStorage.removeItem('active_ticket_ref');
          sessionStorage.removeItem('active_ticket_queue');
          var oldScreen = document.getElementById('orderTicketScreen');
          if (oldScreen) oldScreen.classList.remove('active', 'is-ready');
        });
      }

      // Leave Ordering Session Button
      var btnLeave = document.getElementById('btnLeaveSession');
      if (btnLeave) {
        btnLeave.addEventListener('click', function() {
          try {
            sessionStorage.removeItem('becoffee_order_type');
            sessionStorage.removeItem('becoffee_table_num');
            sessionStorage.removeItem('becoffee_table_locked');
            sessionStorage.removeItem('becoffee_dining_chosen');
            sessionStorage.removeItem('becoffee_cart');
            sessionStorage.removeItem('active_ticket_ref');
            sessionStorage.removeItem('active_ticket_queue');
            sessionStorage.removeItem('becoffee_order_prep_end');
          } catch (err) {}
        });
      }


      // Initial Menu Load
      loadMenu();

    })();
  </script>
</body>
</html>
