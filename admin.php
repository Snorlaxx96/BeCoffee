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
  <title>BeCoffee | Admin Operations & Analytics Studio</title>
  <meta name="description" content="Operations studio for BeCoffee: Analytics, Revenue Ledger, Product Catalog, and Stock Customization Control.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=16.0">
  <style>
    /* Root Variables & Reset */
    :root {
      --admin-sidebar-w: 260px;
      --admin-bg: #110D0B;
      --admin-card: rgba(25, 20, 17, 0.95);
      --admin-border: rgba(223, 155, 100, 0.16);
      --admin-accent: #E28743;
      --admin-accent-glow: rgba(226, 135, 67, 0.35);
      --admin-green: #10B981;
      --admin-red: #EF4444;
      --admin-muted: #A99B92;
    }

    body.admin-hub-body {
      background: var(--admin-bg);
      background-image: 
        radial-gradient(circle at 15% 10%, rgba(226, 135, 67, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 85% 60%, rgba(148, 77, 28, 0.06) 0%, transparent 45%);
      color: #F5EBE1;
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      margin: 0;
      overflow-x: hidden;
    }

    /* Ergonomic Fixed Left Sidebar (Anti-Overfitting & Token Calibrated) */
    .admin-sidebar {
      width: var(--admin-sidebar-w);
      height: 100vh;
      height: 100dvh;
      position: fixed;
      top: 0;
      left: 0;
      background: #140F0D;
      border-right: 1px solid rgba(223, 155, 100, 0.12);
      display: flex;
      flex-direction: column;
      z-index: 100;
      padding: 1.15rem 0.9rem 0.9rem;
      box-sizing: border-box;
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      overflow: hidden;
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      text-decoration: none;
      color: #FAF7F2;
      margin-bottom: 0.85rem;
      padding: 0.2rem 0.35rem 0.5rem;
      flex-shrink: 0;
    }
    .sidebar-logo {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: #E28743;
      color: #140F0D;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.2rem;
      font-weight: 800;
      flex-shrink: 0;
    }
    .sidebar-brand-name {
      font-family: var(--font-serif);
      font-size: 1.12rem;
      font-weight: 700;
      line-height: 1.15;
      color: #FAF7F2;
    }
    .sidebar-brand-tag {
      font-size: 0.65rem;
      color: #DF9B64;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      display: inline-block;
      margin-top: 0.15rem;
      transition: all 0.2s ease;
    }
    .sidebar-brand-tag.role-superadmin {
      color: #FDBA74;
      background: rgba(226, 135, 67, 0.16);
      border: 1px solid rgba(226, 135, 67, 0.35);
      padding: 0.15rem 0.45rem;
      border-radius: 4px;
    }
    .sidebar-brand-tag.role-admin {
      color: #A99B92;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.15rem 0.45rem;
      border-radius: 4px;
    }

    /* Navigation Menu (Scrollable Flex Chamber) */
    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      flex: 1;
      min-height: 0;
      overflow-y: auto;
      overflow-x: hidden;
      padding-right: 0.2rem;
      margin-right: -0.2rem;
    }
    .sidebar-nav::-webkit-scrollbar {
      width: 4px;
    }
    .sidebar-nav::-webkit-scrollbar-track {
      background: transparent;
    }
    .sidebar-nav::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.12);
      border-radius: 4px;
    }
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.22);
    }

    .nav-section-title {
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #8C7E77;
      font-weight: 700;
      padding: 0.45rem 0.5rem 0.2rem;
    }
    .nav-section-title.superadmin-only-nav {
      display: none;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      margin-top: 0.65rem;
      padding-top: 0.6rem;
      color: #A99B92;
    }

    .sidebar-nav-btn {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      padding: 0.55rem 0.75rem;
      border-radius: 8px;
      font-size: 0.83rem;
      font-weight: 500;
      color: #C5BAAF;
      text-decoration: none;
      background: transparent;
      border: 1px solid transparent;
      border-left: 3px solid transparent;
      cursor: pointer;
      width: 100%;
      text-align: left;
      transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
      box-sizing: border-box;
    }
    .sidebar-nav-btn:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #FAF7F2;
    }
    .sidebar-nav-btn.active {
      background: rgba(226, 135, 67, 0.12);
      border-color: transparent;
      border-left: 3px solid var(--admin-accent);
      color: #FAF7F2;
      font-weight: 600;
      box-shadow: none;
    }
    .sidebar-nav-btn svg {
      flex-shrink: 0;
      opacity: 0.85;
    }
    .sidebar-nav-btn.active svg {
      color: var(--admin-accent);
      opacity: 1;
    }

    /* Secondary Switcher Links at Bottom (Zero Emojis, Clean SVG) */
    .sidebar-bottom-links {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding-top: 0.65rem;
      margin-top: 0.45rem;
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
      flex-shrink: 0;
    }
    .sidebar-quick-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem 0.75rem;
      border-radius: 8px;
      font-size: 0.78rem;
      font-weight: 500;
      color: #A99B92;
      text-decoration: none;
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
      cursor: pointer;
      box-sizing: border-box;
      width: 100%;
    }
    .sidebar-quick-link:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #FAF7F2;
      border-color: rgba(255, 255, 255, 0.12);
    }
    .sidebar-quick-link.active {
      background: rgba(226, 135, 67, 0.16);
      border-color: rgba(226, 135, 67, 0.45);
      color: #FAF7F2;
      font-weight: 600;
    }
    .sidebar-quick-link.active .quick-link-label svg {
      color: var(--admin-accent);
    }
    .quick-link-badge {
      font-size: 0.62rem;
      letter-spacing: 0.06em;
      font-weight: 700;
      padding: 0.15rem 0.45rem;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.08);
      color: #D1C5BD;
      border: 1px solid rgba(255, 255, 255, 0.08);
      flex-shrink: 0;
      text-transform: uppercase;
    }
    .sidebar-quick-link.active .quick-link-badge {
      background: rgba(226, 135, 67, 0.3);
      color: #FDBA74;
      border-color: rgba(226, 135, 67, 0.5);
    }
    .sidebar-quick-link .quick-link-label {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      min-width: 0;
    }
    .sidebar-quick-link .quick-link-label svg {
      flex-shrink: 0;
      color: #C5BAAF;
    }
    .sidebar-quick-link:hover .quick-link-label svg {
      color: #FAF7F2;
    }
    .sidebar-quick-link .quick-link-arrow {
      opacity: 0.4;
      flex-shrink: 0;
      transition: opacity 0.15s ease, transform 0.15s ease;
    }
    .sidebar-quick-link:hover .quick-link-arrow {
      opacity: 0.9;
      transform: translate(1px, -1px);
    }
    .sidebar-quick-link.signout-link {
      color: #E0948F;
      background: rgba(239, 68, 68, 0.05);
      border-color: rgba(239, 68, 68, 0.12);
    }
    .sidebar-quick-link.signout-link .quick-link-label svg {
      color: #EF4444;
    }
    .sidebar-quick-link.signout-link:hover {
      background: rgba(239, 68, 68, 0.12);
      color: #FCA5A5;
      border-color: rgba(239, 68, 68, 0.25);
    }

    /* Main Workspace Canvas */
    .admin-main-canvas {
      margin-left: var(--admin-sidebar-w);
      flex: 1;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      box-sizing: border-box;
    }

    /* Top Canvas Bar */
    .canvas-topbar {
      padding: 0.9rem 1.75rem;
      border-bottom: 1px solid var(--admin-border);
      background: rgba(20, 15, 13, 0.92);
      backdrop-filter: blur(14px);
      position: sticky;
      top: 0;
      z-index: 90;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.85rem;
    }
    .canvas-title-wrap {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .sidebar-toggle-btn {
      display: none;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      min-width: 44px;
      min-height: 44px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #FFF;
      cursor: pointer;
      transition: all 0.2s ease;
      padding: 0;
    }
    .sidebar-toggle-btn:hover {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(226, 135, 67, 0.45);
      color: #FDBA74;
    }
    .sidebar-toggle-btn:focus-visible {
      outline: 2px solid #E28743;
      outline-offset: 2px;
    }
    .sidebar-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(10, 7, 5, 0.7);
      backdrop-filter: blur(6px);
      z-index: 99;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }
    .sidebar-backdrop.active {
      opacity: 1;
      pointer-events: auto;
    }

    .canvas-title-group h2 {
      font-size: 1.35rem;
      font-weight: 800;
      color: #FFF;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      line-height: 1.2;
    }
    .canvas-title-group p {
      font-size: 0.82rem;
      color: var(--admin-muted);
      margin: 0.25rem 0 0;
    }

    .canvas-actions-bar {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      flex-wrap: wrap;
    }

    /* Date Selector & Controls */
    .date-control-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .date-presets-strip {
      display: inline-flex;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 2px;
      gap: 2px;
    }
    .date-preset-btn {
      padding: 0.35rem 0.75rem;
      border-radius: 4px;
      border: 1px solid transparent;
      background: transparent;
      color: #A8988C;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s ease;
      min-height: 34px;
      display: inline-flex;
      align-items: center;
    }
    .date-preset-btn:hover {
      color: #FFF;
      background: rgba(255, 255, 255, 0.06);
    }
    .date-preset-btn.active {
      background: rgba(223, 155, 100, 0.16);
      color: #F5EDE4;
      border-color: rgba(223, 155, 100, 0.3);
      font-weight: 700;
    }

    .date-input-clean {
      background: #140F0D;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      color: #F5EDE4;
      font-family: var(--font-mono);
      font-size: 0.82rem;
      padding: 0.35rem 0.65rem;
      min-height: 38px;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.15s ease;
    }
    .date-input-clean:focus {
      border-color: #DF9B64;
    }

    .admin-action-btn {
      padding: 0.45rem 0.85rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.15s ease;
      border: 1px solid rgba(255, 255, 255, 0.1);
      text-decoration: none;
      min-height: 38px;
      box-sizing: border-box;
    }
    .admin-action-btn:focus-visible {
      outline: 2px solid #DF9B64;
      outline-offset: 2px;
    }
    .btn-action-accent {
      background: #DF9B64;
      border-color: #DF9B64;
      color: #140F0D;
      font-weight: 700;
    }
    .btn-action-accent:hover {
      background: #E8AA79;
      border-color: #E8AA79;
    }
    .btn-action-secondary {
      background: rgba(255, 255, 255, 0.04);
      border-color: rgba(255, 255, 255, 0.1);
      color: #E6DDD5;
    }
    .btn-action-secondary:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.2);
    }

    /* Content Sections */
    .admin-view-panel {
      padding: 2rem;
      display: none;
      flex-direction: column;
      gap: 1.75rem;
      max-width: 1400px;
      width: 100%;
      box-sizing: border-box;
      min-width: 0;
    }
    .admin-view-panel.active {
      display: flex;
    }

    /* Embedded Full-Canvas App Panels (POS, KDS, Storefront) */
    .admin-view-panel.embedded-app-panel {
      padding: 1rem 1.5rem 1.5rem;
      max-width: none;
      width: 100%;
      height: calc(100vh - 84px);
      box-sizing: border-box;
      display: none;
      flex-direction: column;
    }
    .admin-view-panel.embedded-app-panel.active {
      display: flex;
    }
    .embedded-iframe-container {
      flex: 1;
      width: 100%;
      height: 100%;
      min-height: 0;
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid var(--admin-border);
      background: #0F0C0A;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      position: relative;
      margin: 0 auto;
      transition: max-width 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .embedded-iframe-container iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
      background: #0F0C0A;
    }

    /* Live Full-Screen KDS Mode */
    .admin-view-panel.embedded-app-panel.kds-live-panel {
      padding: 0 !important;
      height: 100vh !important;
      max-height: 100vh !important;
    }
    .admin-view-panel.embedded-app-panel.kds-live-panel .embedded-iframe-container {
      border-radius: 0 !important;
      border: none !important;
      box-shadow: none !important;
      max-width: 100% !important;
      height: 100% !important;
    }
    .kds-floating-nav-btn {
      display: none;
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 999;
      align-items: center;
      gap: 0.45rem;
      padding: 0.6rem 1rem;
      background: rgba(23, 18, 15, 0.95);
      border: 1px solid rgba(223, 155, 100, 0.4);
      border-radius: 9999px;
      color: #FFF;
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(10px);
      transition: all 0.2s ease;
    }
    .kds-floating-nav-btn:hover {
      background: rgba(226, 135, 67, 0.25);
      border-color: #E28743;
      transform: translateY(-1px);
    }
    @media (max-width: 1023px) {
      .admin-view-panel.active.kds-live-panel .kds-floating-nav-btn {
        display: inline-flex;
      }
    }

    /* Persona Preview Ribbon & Viewport Controls */
    .preview-persona-badge {
      display: none !important;
    }
    .preview-device-strip, .preview-variant-strip {
      display: inline-flex;
      align-items: center;
      background: var(--surface-elevated, #221B17);
      border: 1px solid var(--admin-border, rgba(223, 155, 100, 0.2));
      border-radius: 10px;
      padding: 3px;
      gap: 3px;
      box-sizing: border-box;
    }
    .preview-device-btn, .preview-variant-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      padding: 0.35rem 0.75rem;
      border-radius: 7px;
      border: 1px solid transparent;
      background: transparent;
      color: var(--admin-muted, #A89A90);
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.18s ease;
      white-space: nowrap;
      min-height: 38px;
      box-sizing: border-box;
      line-height: 1;
    }
    .preview-device-btn:hover, .preview-variant-btn:hover {
      color: #FFF;
      background: rgba(255, 255, 255, 0.06);
    }
    .preview-device-btn.active, .preview-variant-btn.active {
      background: rgba(226, 135, 67, 0.22);
      color: #FDBA74;
      border-color: rgba(226, 135, 67, 0.4);
      font-weight: 700;
    }
    .preview-device-btn svg, .preview-variant-btn svg {
      flex-shrink: 0;
    }

    /* Executive Analytics KPI Tiles (3-Card Executive Strip) */
    .kpi-tiles-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.85rem;
      width: 100%;
      box-sizing: border-box;
    }
    .kpi-stat-card {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1.15rem 1.25rem;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-width: 0;
      box-shadow: none;
      transition: border-color 0.15s ease;
    }
    .kpi-stat-card:hover {
      border-color: rgba(223, 155, 100, 0.3);
    }
    .kpi-stat-label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #A8988C;
      font-weight: 700;
      margin-bottom: 0.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .kpi-stat-label svg {
      color: #7D6F64;
      flex-shrink: 0;
      opacity: 0.75;
    }
    .kpi-stat-value {
      font-family: var(--font-mono);
      font-size: 1.65rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.15;
      letter-spacing: -0.02em;
      word-break: normal;
    }
    .kpi-stat-value.kpi-stat-value-text {
      font-size: 1.15rem;
      white-space: nowrap;
      font-weight: 600;
    }
    .kpi-stat-sub {
      font-size: 0.72rem;
      color: #8E7E73;
      margin-top: 0.45rem;
      font-weight: 500;
      line-height: 1.35;
    }

    /* Visual Analytics Bento Grid */
    .analytics-visual-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 0.75rem;
      width: 100%;
      box-sizing: border-box;
      margin-top: 0.75rem;
    }
    .viz-card {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1.25rem 1.35rem;
      display: flex;
      flex-direction: column;
      box-sizing: border-box;
      min-width: 0;
      box-shadow: none;
    }
    .viz-card.span-4 { grid-column: span 4; }
    .viz-card.span-8 { grid-column: span 8; }
    .viz-card.span-5 { grid-column: span 5; }
    .viz-card.span-7 { grid-column: span 7; }
    .viz-card.span-3 { grid-column: span 3; }

    .viz-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.1rem;
      gap: 0.75rem;
    }
    .viz-card-title {
      font-size: 0.92rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      letter-spacing: -0.01em;
    }
    .viz-card-title svg {
      color: #DF9B64;
      flex-shrink: 0;
    }
    .viz-card-badge {
      font-family: var(--font-mono);
      font-size: 0.75rem;
      color: #DF9B64;
      background: transparent;
      border: none;
      padding: 0;
      font-weight: 600;
      white-space: nowrap;
    }

    /* Split Bar & Payment Distribution */
    .payment-split-bar {
      display: flex;
      height: 6px;
      border-radius: 2px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.06);
      margin: 0.25rem 0 1.25rem;
    }
    .split-bar-gcash {
      background: #3B82F6;
      transition: width 0.4s ease;
    }
    .split-bar-cash {
      background: #DF9B64;
      transition: width 0.4s ease;
    }
    .payment-legend-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
      margin-top: 0.25rem;
      padding-top: 0.85rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }
    .payment-legend-box {
      background: transparent;
      border: none;
      border-radius: 0;
      padding: 0;
    }
    .payment-legend-box:last-child {
      border-left: 1px solid rgba(255, 255, 255, 0.06);
      padding-left: 1.25rem;
    }
    .payment-legend-header {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.68rem;
      color: #A8988C;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .dot-gcash { width: 6px; height: 6px; border-radius: 50%; background: #3B82F6; flex-shrink: 0; }
    .dot-cash { width: 6px; height: 6px; border-radius: 50%; background: #DF9B64; flex-shrink: 0; }
    .payment-legend-amount {
      font-family: var(--font-mono);
      font-size: 1.35rem;
      font-weight: 700;
      color: #FFF;
      letter-spacing: -0.02em;
      margin: 0.35rem 0 0.15rem;
    }
    .payment-legend-share {
      font-size: 0.72rem;
      color: #8E7E73;
      font-weight: 500;
    }

    /* Hourly Histogram Visuals */
    .hourly-chart-container {
      width: 100%;
      height: 155px;
      position: relative;
      margin-top: auto;
    }
    .hourly-svg-chart {
      width: 100%;
      height: 100%;
      overflow: visible;
    }

    /* Top Selling Drinks Ranking */
    .rank-list {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      flex: 1;
      justify-content: center;
    }
    .rank-item {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    .rank-item-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.84rem;
    }
    .rank-item-name {
      color: #FFF;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.84rem;
    }
    .rank-item-num {
      color: #7D6F64;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      font-weight: 700;
    }
    .rank-item-stats {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      color: #DF9B64;
      font-weight: 600;
    }
    .rank-progress-track {
      height: 4px;
      border-radius: 2px;
      background: rgba(255, 255, 255, 0.06);
      overflow: hidden;
    }
    .rank-progress-fill {
      height: 100%;
      border-radius: 2px;
      background: #DF9B64;
      transition: width 0.4s ease;
    }

    /* Modifier Intelligence List */
    .modifier-group-strip {
      display: flex;
      flex-direction: column;
      gap: 0;
      flex: 1;
      justify-content: center;
    }
    .modifier-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: transparent;
      border: none;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 0;
      padding: 0.75rem 0;
      font-size: 0.82rem;
    }
    .modifier-row:last-child {
      border-bottom: none;
    }
    .modifier-name {
      color: #E6DDD5;
      font-weight: 500;
    }
    .modifier-count {
      font-family: var(--font-mono);
      font-weight: 600;
      color: #DF9B64;
      font-size: 0.84rem;
    }

    /* QA Lockout Health Meters */
    .qa-health-meters {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      flex: 1;
      justify-content: center;
    }
    .qa-meter-row {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    .qa-meter-header {
      display: flex;
      justify-content: space-between;
      font-size: 0.76rem;
      font-weight: 600;
      color: #C8B9AF;
    }
    .qa-meter-header span:last-child {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #10B981;
    }
    .qa-meter-bar-track {
      height: 4px;
      border-radius: 2px;
      background: rgba(255, 255, 255, 0.06);
      overflow: hidden;
    }
    .qa-meter-bar-fill {
      height: 100%;
      border-radius: 2px;
      background: #10B981;
      transition: width 0.4s ease;
    }

    /* Ledger Table Section & Filter Toolbar */
    .ledger-table-wrap {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1.25rem 1.4rem;
      box-sizing: border-box;
      min-width: 0;
      box-shadow: none;
    }
    .ledger-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .ledger-title-wrap h3 {
      font-size: 1.05rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
      letter-spacing: -0.01em;
    }
    .ledger-title-wrap span {
      font-size: 0.78rem;
      color: #A8988C;
      margin-top: 0.2rem;
      display: block;
    }

    .ledger-search-box {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: #140F0D;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      padding: 0.35rem 0.75rem;
      min-width: 240px;
      max-width: 340px;
      min-height: 38px;
      box-sizing: border-box;
      flex: 1;
      transition: border-color 0.15s ease;
    }
    .ledger-search-box:focus-within {
      border-color: #DF9B64;
    }
    .ledger-search-box svg {
      color: #7D6F64;
      flex-shrink: 0;
    }
    .ledger-search-box input {
      background: transparent;
      border: none;
      color: #FFF;
      font-size: 0.82rem;
      font-family: inherit;
      outline: none;
      width: 100%;
    }
    .ledger-search-box input::placeholder {
      color: #7D6F64;
    }

    .ledger-filter-toolbar {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 1.25rem;
    }
    .filter-chip-group {
      display: inline-flex;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 2px;
      gap: 2px;
    }
    .filter-chip-btn {
      padding: 0.3rem 0.65rem;
      border-radius: 4px;
      border: 1px solid transparent;
      background: transparent;
      color: #A8988C;
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s ease;
      min-height: 32px;
      display: inline-flex;
      align-items: center;
      white-space: nowrap;
    }
    .filter-chip-btn:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #FFF;
    }
    .filter-chip-btn.active {
      background: rgba(223, 155, 100, 0.16);
      border-color: rgba(223, 155, 100, 0.3);
      color: #F5EDE4;
      font-weight: 700;
      box-shadow: none;
    }

    .ledger-table-scroll {
      width: 100%;
      overflow-x: auto;
      border-radius: 4px;
    }
    .ledger-desktop-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.84rem;
      text-align: left;
    }
    .ledger-desktop-table th {
      padding: 0.75rem 0.65rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #A8988C;
      font-weight: 700;
      white-space: nowrap;
      background: #140F0D;
    }
    .ledger-desktop-table td {
      padding: 0.85rem 0.65rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #E2D5CC;
      vertical-align: middle;
      line-height: 1.35;
    }
    .ledger-desktop-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    /* Order Status Tags */
    .order-status-tag {
      display: inline-flex;
      align-items: center;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      line-height: 1;
      white-space: nowrap;
    }
    .status-finished {
      background: rgba(16, 185, 129, 0.12);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .status-progress {
      background: rgba(223, 155, 100, 0.14);
      color: #F5EDE4;
      border: 1px solid rgba(223, 155, 100, 0.3);
    }
    .status-waiting {
      background: rgba(255, 255, 255, 0.05);
      color: #A8988C;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .status-cancelled {
      background: rgba(239, 68, 68, 0.12);
      color: #FCA5A5;
      border: 1px solid rgba(239, 68, 68, 0.25);
    }

    /* Dining, Payment & QA Badges */
    .dining-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.18rem 0.45rem;
      border-radius: 4px;
      font-size: 0.72rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-dine-in {
      background: rgba(223, 155, 100, 0.12);
      color: #F5EDE4;
      border: 1px solid rgba(223, 155, 100, 0.25);
    }
    .badge-takeout {
      background: rgba(245, 158, 11, 0.12);
      color: #FBBF24;
      border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .payment-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.18rem 0.45rem;
      border-radius: 4px;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      white-space: nowrap;
    }
    .badge-gcash {
      background: rgba(96, 165, 250, 0.12);
      color: #93C5FD;
      border: 1px solid rgba(96, 165, 250, 0.25);
    }
    .badge-cash {
      background: rgba(223, 155, 100, 0.12);
      color: #DF9B64;
      border: 1px solid rgba(223, 155, 100, 0.25);
    }
    .qa-badge-passed {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.18rem 0.45rem;
      border-radius: 4px;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 700;
      background: rgba(16, 185, 129, 0.12);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.25);
      white-space: nowrap;
    }
    .qa-badge-pending {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.18rem 0.45rem;
      border-radius: 4px;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.05);
      color: #A8988C;
      border: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
    }
    .order-ref-code {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      font-weight: 600;
      color: #E2D5CC;
      display: block;
      word-break: break-all;
    }
    .order-customer-sub {
      font-size: 0.75rem;
      color: #8E7E73;
      margin-top: 0.15rem;
      display: block;
    }
    .prep-time-meta {
      font-size: 0.72rem;
      color: #8E7E73;
      font-family: var(--font-mono);
      margin-top: 0.2rem;
    }
    .order-total-cell {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #FFF;
      font-size: 0.95rem;
      white-space: nowrap;
    }
    .btn-inspect-order {
      padding: 0.28rem 0.5rem;
      border-radius: 4px;
      font-size: 0.72rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      background: rgba(223, 155, 100, 0.12);
      color: #DF9B64;
      border: 1px solid rgba(223, 155, 100, 0.25);
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn-inspect-order:hover {
      background: rgba(223, 155, 100, 0.22);
      border-color: #DF9B64;
      color: #FFF;
    }

    #viewAuditTrail .diag-desktop-table th,
    #viewAuditTrail .diag-desktop-table td {
      padding: 0.65rem 0.5rem;
    }

    /* Mobile Ticket Cards Container */
    .ledger-mobile-cards {
      display: none;
      flex-direction: column;
      gap: 0.75rem;
      width: 100%;
      box-sizing: border-box;
    }
    .mobile-order-ticket {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1rem 1.15rem;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }
    .mobile-ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      padding-bottom: 0.5rem;
    }
    .mobile-ticket-queue {
      font-family: var(--font-mono);
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }
    .mobile-ticket-amount {
      font-family: var(--font-mono);
      font-size: 1.1rem;
      font-weight: 700;
      color: #FFF;
    }
    .mobile-ticket-items {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      font-size: 0.82rem;
    }
    .mobile-ticket-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.35rem;
      padding-top: 0.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Menu & Stock Control Styles */
    .stock-controls-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
      background: #140E0C;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 0.65rem 1rem;
      margin-bottom: 1.25rem;
    }
    .cat-nav-strip {
      display: flex;
      gap: 0.35rem;
      align-items: center;
      overflow-x: auto;
      scrollbar-width: none;
      background: #0E0A08;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 0.25rem;
    }
    .cat-nav-strip::-webkit-scrollbar { display: none; }
    .cat-pill-btn {
      padding: 0.4rem 0.85rem;
      border-radius: 4px;
      background: transparent;
      border: 1px solid transparent;
      color: #9E8E81;
      font-size: 0.8rem;
      font-weight: 600;
      white-space: nowrap;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .cat-pill-btn:hover {
      color: #FFF;
      background: rgba(255, 255, 255, 0.04);
    }
    .cat-pill-btn.active {
      background: #251D18;
      border-color: rgba(255, 255, 255, 0.12);
      color: #FFF;
      font-weight: 700;
    }
    .search-input-wrap {
      display: flex;
      align-items: center;
      background: #0E0A08;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 0.4rem 0.75rem;
      gap: 0.5rem;
      transition: border-color 0.15s ease;
    }
    .search-input-wrap:focus-within {
      border-color: rgba(223, 155, 100, 0.4);
    }
    .search-input-wrap svg {
      color: #7D6F64;
      flex-shrink: 0;
    }
    .search-input-wrap input {
      background: transparent;
      border: none;
      color: #FFF;
      font-family: inherit;
      font-size: 0.82rem;
      outline: none;
      width: 200px;
    }

    /* Drinks Catalog Grid */
    .drinks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.25rem;
      width: 100%;
    }
    .drink-card {
      background: #140E0C;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      position: relative;
      transition: border-color 0.15s ease;
    }
    .drink-card:hover {
      border-color: rgba(255, 255, 255, 0.18);
    }
    .drink-img-wrap {
      position: relative;
      width: 100%;
      height: 165px;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 0.85rem;
      background: #0E0A08;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }
    .drink-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .drink-card.item-sold-out {
      opacity: 0.8;
      border-color: rgba(239, 68, 68, 0.25);
    }
    .drink-card.item-sold-out .drink-img-wrap img {
      filter: grayscale(30%);
    }
    .drink-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 0.35rem;
    }
    .drink-name {
      font-size: 1.05rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
    }
    .drink-price {
      font-family: var(--font-mono);
      font-weight: 700;
      color: #FFF;
      font-size: 1.05rem;
    }
    .drink-desc {
      font-size: 0.8rem;
      color: #8E7E73;
      line-height: 1.45;
      margin-bottom: 0.85rem;
      flex: 1;
      min-height: 2.4em;
    }
    .drink-card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.65rem;
      margin-top: auto;
      padding-top: 0.75rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }
    .drink-card-footer .btn-manage-options {
      flex: 1;
    }
    .drink-status-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      font-size: 0.72rem;
      font-family: var(--font-mono);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      white-space: nowrap;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 4px;
      padding: 0.25rem 0.5rem;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .drink-status-indicator:hover {
      background: rgba(255, 255, 255, 0.06);
    }
    .drink-status-indicator.is-available,
    .drink-status-indicator.in-stock {
      color: #34D399;
    }
    .drink-status-indicator.is-soldout,
    .drink-status-indicator.sold-out {
      color: #F87171;
    }
    .status-dot-sm {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: currentColor;
      display: inline-block;
    }
    .btn-manage-options {
      min-height: 36px;
      padding: 0.45rem 0.75rem;
      border-radius: 4px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(255, 255, 255, 0.04);
      color: #F5EDE4;
      font-size: 0.78rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn-manage-options:hover {
      background: rgba(223, 155, 100, 0.15);
      border-color: rgba(223, 155, 100, 0.35);
      color: #FFF;
    }

    /* Modal Styling */
    .oms-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.82);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.25rem;
    }
    .oms-modal-overlay.active {
      display: flex;
    }
    .oms-modal-box {
      background: #140E0C;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      width: 100%;
      max-width: 520px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 1.75rem;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.9);
      position: relative;
      animation: modalSlideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .oms-modal-box.wide-modal {
      width: min(1040px, calc(100vw - 2.5rem));
      max-width: min(1040px, calc(100vw - 2.5rem));
      height: min(730px, calc(100vh - 2.5rem));
      max-height: min(730px, calc(100vh - 2.5rem));
      padding: 1.5rem 1.75rem;
      box-sizing: border-box;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .modal-dialog-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding-bottom: 0.85rem;
      margin-bottom: 1.15rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      flex-shrink: 0;
    }
    .modal-dialog-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
    }
    .modal-dialog-subtitle {
      font-size: 0.78rem;
      color: #8E7E73;
      margin: 0.2rem 0 0;
    }
    .modal-two-col {
      display: grid;
      grid-template-columns: 340px 1fr;
      gap: 1.75rem;
      flex: 1;
      min-height: 0;
      align-items: stretch;
      overflow: hidden;
    }
    .modal-col-left {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      height: 100%;
      overflow-y: auto;
      padding-right: 0.5rem;
    }
    .modal-col-right {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      height: 100%;
      border-left: 1px solid rgba(255, 255, 255, 0.08);
      padding-left: 1.75rem;
      overflow-y: auto;
    }
    .modal-options-header {
      margin-bottom: 0.25rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .modal-options-header h4 {
      font-size: 0.95rem;
      font-weight: 700;
      color: #FFF;
      margin: 0 0 0.15rem;
    }
    .modal-options-header p {
      font-size: 0.75rem;
      color: #8E7E73;
      margin: 0;
    }
    #modalTempGroup,
    #modalAddonsGroup,
    #modalSweetnessGroup {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.55rem;
    }
    @media (max-width: 960px) {
      .modal-two-col {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        height: auto;
        overflow: visible;
      }
      .modal-col-left {
        overflow: visible;
        padding-right: 0;
        height: auto;
      }
      .modal-col-right {
        border-left: none !important;
        padding-left: 0 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 1.5rem;
        overflow: visible;
        height: auto;
      }
      .oms-modal-box.wide-modal {
        width: 96vw;
        max-width: 96vw;
        height: auto;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.25rem;
      }
    }
    .drink-img-preview-box {
      position: relative;
      width: 100%;
      height: 155px;
      border-radius: 4px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #0E0A08;
      flex-shrink: 0;
    }
    .drink-img-preview-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .btn-change-photo-badge {
      position: absolute;
      bottom: 0.65rem;
      right: 0.65rem;
      background: rgba(18, 13, 11, 0.88);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #FAF7F2;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 0.35rem 0.65rem;
      border-radius: 5px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.15s ease;
    }
    .btn-change-photo-badge:hover {
      background: rgba(30, 22, 19, 0.95);
      border-color: rgba(223, 155, 100, 0.35);
      color: #FFF;
    }
    .btn-delete-addon {
      background: transparent;
      border: none;
      color: #7D6F64;
      cursor: pointer;
      padding: 0.25rem;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
    }
    .btn-delete-addon:hover {
      color: #F87171;
      background: rgba(239, 68, 68, 0.12);
    }
    @keyframes modalSlideUp {
      from { transform: translateY(12px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .oms-modal-close {
      width: 28px;
      height: 28px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: #C8B9AF;
      display: grid;
      place-content: center;
      cursor: pointer;
      font-size: 1.15rem;
      line-height: 1;
      transition: all 0.15s ease;
      flex-shrink: 0;
    }
    .oms-modal-close:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #FFF;
    }

    /* Modal Form Inputs */
    .modal-field-group {
      margin-bottom: 0.65rem;
    }
    .modal-field-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9E8E81;
      font-weight: 700;
      display: block;
      margin-bottom: 0.3rem;
    }
    .modal-text-input {
      width: 100%;
      background: #0E0A08;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 4px;
      padding: 0.5rem 0.65rem;
      color: #FFF;
      font-family: inherit;
      font-size: 0.84rem;
      box-sizing: border-box;
      transition: border-color 0.15s ease;
    }
    .modal-text-input:focus {
      outline: none;
      border-color: rgba(223, 155, 100, 0.5);
    }

    /* Option Group & Flat Items (No Cards Inside Cards) */
    .option-group-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9E8E81;
      font-weight: 700;
      margin: 0.85rem 0 0.45rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-modifier-toggle {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #A8988C;
      font-size: 0.7rem;
      font-weight: 600;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-modifier-toggle:hover {
      background: rgba(255, 255, 255, 0.04);
      color: #FAF7F2;
      border-color: rgba(255, 255, 255, 0.2);
    }
    .btn-modifier-add-extra {
      background: rgba(223, 155, 100, 0.1);
      border: 1px solid rgba(223, 155, 100, 0.25);
      color: #DF9B64;
      font-size: 0.7rem;
      font-weight: 600;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-modifier-add-extra:hover {
      background: rgba(223, 155, 100, 0.18);
      border-color: rgba(223, 155, 100, 0.4);
      color: #FDBA74;
    }
    .new-addon-form-card {
      display: none;
      background: #140E0C;
      border: 1px solid rgba(223, 155, 100, 0.25);
      border-radius: 6px;
      padding: 0.85rem;
      margin-bottom: 0.75rem;
    }
    .admin-option-pill {
      min-width: 0;
      width: 100%;
      box-sizing: border-box;
      padding: 0.65rem 0.85rem;
      border-radius: 6px;
      background: #140E0C;
      border: 1px solid rgba(255, 255, 255, 0.06);
      font-size: 0.82rem;
      font-weight: 600;
      color: #FAF7F2;
      cursor: pointer;
      transition: all 0.15s ease;
      user-select: none;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      gap: 0.5rem;
    }
    .admin-option-pill:hover {
      border-color: rgba(223, 155, 100, 0.3);
      background: #19120F;
    }
    .admin-option-pill.is-in-stock {
      border-color: rgba(255, 255, 255, 0.08);
      background: #140E0C;
    }
    .admin-option-pill.is-sold-out {
      border-color: rgba(255, 255, 255, 0.04);
      background: rgba(255, 255, 255, 0.02);
      color: #7D6F64;
      opacity: 0.7;
    }
    .admin-option-pill.is-sold-out .option-title-text {
      text-decoration: line-through;
      color: #8E7E73;
    }
    /* Anti-slop status indicator tags — zero background/border boxes behind text */
    .status-indicator-tag {
      font-size: 0.7rem;
      font-family: var(--font-mono);
      font-weight: 600;
      letter-spacing: 0.04em;
      padding: 0;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      white-space: nowrap;
      text-transform: uppercase;
      background: transparent !important;
      border: none !important;
    }
    .tag-in-stock {
      color: #6EE7B7 !important;
      background: none !important;
      border: none !important;
    }
    .tag-in-stock::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 5px rgba(16, 185, 129, 0.4);
    }
    .tag-sold-out {
      color: #F87171 !important;
      background: none !important;
      border: none !important;
    }
    .tag-sold-out::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #EF4444;
      box-shadow: 0 0 5px rgba(239, 68, 68, 0.4);
    }
    .admin-drink-status-btn {
      width: 100%;
      min-height: 36px;
      padding: 0.45rem 0.75rem;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
      background: #0E0A08;
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #FAF7F2;
    }
    .admin-drink-status-btn:hover {
      border-color: rgba(255, 255, 255, 0.25);
    }
    .status-btn-available {
      border-color: rgba(255, 255, 255, 0.12);
      background: #0E0A08;
      color: #FAF7F2;
    }
    .status-btn-soldout {
      border-color: rgba(255, 255, 255, 0.12);
      background: #0E0A08;
      color: #A8988C;
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
      transition: all 0.25s ease;
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

    /* ==========================================================================
       Users & Access Management (SuperAdmin)
       ========================================================================== */
    .users-roles-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.85rem;
      margin-bottom: 1.5rem;
    }
    .role-stat-card {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1rem 1.15rem;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      transition: border-color 0.15s ease, background-color 0.15s ease;
      position: relative;
    }
    .role-stat-card:hover {
      border-color: rgba(223, 155, 100, 0.3);
      background: #1C1613;
    }
    .role-stat-label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: var(--font-mono);
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #A8988C;
      font-weight: 600;
    }
    .role-stat-pill {
      font-size: 0.62rem;
      font-weight: 700;
      padding: 0.15rem 0.45rem;
      border-radius: 3px;
      letter-spacing: 0.05em;
    }
    .pill-superadmin { background: rgba(167, 139, 250, 0.12); color: #C4B5FD; border: 1px solid rgba(167, 139, 250, 0.25); }
    .pill-admin { background: rgba(223, 155, 100, 0.12); color: #DF9B64; border: 1px solid rgba(223, 155, 100, 0.25); }
    .pill-staff { background: rgba(16, 185, 129, 0.12); color: #6EE7B7; border: 1px solid rgba(16, 185, 129, 0.25); }
    .pill-customer { background: rgba(148, 163, 184, 0.12); color: #CBD5E1; border: 1px solid rgba(148, 163, 184, 0.25); }

    .role-stat-value {
      font-family: var(--font-serif);
      font-size: 1.85rem;
      font-weight: 700;
      line-height: 1.1;
      letter-spacing: -0.02em;
      color: #FAF7F2;
      margin: 0.15rem 0;
    }
    .role-stat-sub {
      font-size: 0.74rem;
      color: #8E7E73;
      line-height: 1.35;
    }

    .users-table-wrap {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1.25rem 1.4rem;
      box-sizing: border-box;
    }
    .users-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .users-title-wrap h3 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      margin: 0 0 0.2rem 0;
      letter-spacing: -0.01em;
    }
    .users-title-wrap p {
      font-size: 0.82rem;
      color: #8E7E73;
      margin: 0;
    }

    .users-filter-toolbar {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
      align-items: center;
    }
    .users-search-box {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: #140F0D;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 4px;
      padding: 0.45rem 0.75rem;
      flex: 1;
      min-width: 200px;
      max-width: 320px;
      box-sizing: border-box;
      transition: border-color 0.15s ease;
    }
    .users-search-box svg {
      color: #8E7E73;
      flex-shrink: 0;
    }
    .users-search-box input {
      background: transparent;
      border: none;
      outline: none;
      color: #FFF;
      font-size: 0.82rem;
      width: 100%;
    }
    .users-search-box input::placeholder {
      color: #8E7E73;
    }
    .users-search-box:focus-within {
      border-color: #DF9B64;
    }
    .users-role-select-filter {
      background: #140F0D;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 4px;
      padding: 0.45rem 0.75rem;
      color: #E2D5CC;
      font-size: 0.82rem;
      cursor: pointer;
      outline: none;
      min-width: 130px;
      box-sizing: border-box;
      transition: border-color 0.15s ease;
    }
    .users-role-select-filter:focus {
      border-color: #DF9B64;
    }

    .users-table-scroll {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
    }
    .users-desktop-table {
      width: 100%;
      min-width: 700px;
      border-collapse: collapse;
      font-size: 0.84rem;
      text-align: left;
    }
    .users-desktop-table th {
      padding: 0.75rem 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #8E7E73;
      font-weight: 700;
      background: #140E0C;
      white-space: nowrap;
    }
    .users-desktop-table td {
      padding: 0.85rem 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #E2D5CC;
      vertical-align: middle;
    }
    .users-desktop-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    .user-id-code {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: #8E7E73;
    }
    .user-name-cell {
      font-weight: 600;
      color: #FFF;
      display: block;
    }
    .user-email-cell {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      color: #C5BAAF;
    }

    /* Role Selector & Indicator inside Table */
    .user-role-selector-wrap {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: #140E0C;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 4px;
      padding: 0.28rem 0.55rem;
      transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .user-role-selector-wrap:hover {
      border-color: rgba(255, 255, 255, 0.22);
    }
    .user-role-selector-wrap:focus-within {
      border-color: #DF9B64;
      background: #181210;
    }
    .role-status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      flex-shrink: 0;
    }
    .role-dot-superadmin { background: #C4B5FD; box-shadow: 0 0 5px rgba(196, 181, 253, 0.35); }
    .role-dot-admin { background: #DF9B64; box-shadow: 0 0 5px rgba(223, 155, 100, 0.35); }
    .role-dot-staff { background: #10B981; box-shadow: 0 0 5px rgba(16, 185, 129, 0.35); }
    .role-dot-customer { background: #94A3B8; box-shadow: 0 0 5px rgba(148, 163, 184, 0.35); }

    .user-role-select {
      background: transparent;
      border: none;
      color: #FAF7F2;
      font-size: 0.8rem;
      font-weight: 500;
      cursor: pointer;
      outline: none;
      padding: 0;
      font-family: inherit;
    }
    .user-role-select option {
      background: #181310;
      color: #FAF7F2;
    }

    /* Legacy badges maintained for Audit & Logs */
    .user-role-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.2rem 0.55rem;
      border-radius: 4px;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .role-badge-superadmin {
      background: rgba(196, 181, 253, 0.12);
      color: #C4B5FD;
      border: 1px solid rgba(196, 181, 253, 0.25);
    }
    .role-badge-admin {
      background: rgba(223, 155, 100, 0.12);
      color: #DF9B64;
      border: 1px solid rgba(223, 155, 100, 0.25);
    }
    .role-badge-staff {
      background: rgba(16, 185, 129, 0.12);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .role-badge-customer {
      background: rgba(148, 163, 184, 0.12);
      color: #CBD5E1;
      border: 1px solid rgba(148, 163, 184, 0.25);
    }

    /* Clean, focused table action buttons */
    .users-actions-group {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      justify-content: flex-end;
    }
    .btn-user-action {
      padding: 0.35rem 0.65rem;
      border-radius: 4px;
      font-size: 0.76rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn-reset-pw {
      background: rgba(223, 155, 100, 0.08);
      border: 1px solid rgba(223, 155, 100, 0.22);
      color: #DF9B64;
    }
    .btn-reset-pw:hover {
      background: rgba(223, 155, 100, 0.18);
      border-color: rgba(223, 155, 100, 0.4);
      color: #FFF;
    }
    .btn-delete-user {
      background: rgba(239, 68, 68, 0.08);
      border: 1px solid rgba(239, 68, 68, 0.22);
      color: #F87171;
    }
    .btn-delete-user:hover {
      background: rgba(239, 68, 68, 0.18);
      border-color: rgba(239, 68, 68, 0.4);
      color: #FFF;
    }

    /* ==========================================================================
       System Diagnostics & Health (SuperAdmin)
       ========================================================================== */
    .diag-health-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.85rem;
      margin-bottom: 1.5rem;
    }
    .diag-health-card {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1rem 1.15rem;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      transition: border-color 0.15s ease, background-color 0.15s ease;
      position: relative;
    }
    .diag-health-card:hover {
      border-color: rgba(223, 155, 100, 0.3);
      background: #1C1613;
    }
    .diag-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: var(--font-mono);
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #A8988C;
      font-weight: 600;
    }
    .diag-card-pill {
      font-size: 0.62rem;
      font-weight: 700;
      padding: 0.15rem 0.45rem;
      border-radius: 3px;
      letter-spacing: 0.05em;
    }
    .pill-neutral { background: rgba(255, 255, 255, 0.06); color: #C5BAAF; border: 1px solid rgba(255, 255, 255, 0.12); }
    .pill-accent { background: rgba(223, 155, 100, 0.12); color: #DF9B64; border: 1px solid rgba(223, 155, 100, 0.25); }
    .pill-success { background: rgba(16, 185, 129, 0.12); color: #6EE7B7; border: 1px solid rgba(16, 185, 129, 0.25); }
    .pill-info { background: rgba(148, 163, 184, 0.12); color: #CBD5E1; border: 1px solid rgba(148, 163, 184, 0.25); }

    .diag-card-value {
      font-family: var(--font-mono);
      font-size: 1.25rem;
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: -0.01em;
      color: #FAF7F2;
      margin: 0.15rem 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .diag-card-meta {
      font-family: var(--font-mono);
      font-size: 0.73rem;
      color: #8E7E73;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .diag-table-wrap {
      background: #181310;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      padding: 1.25rem 1.4rem;
      box-sizing: border-box;
    }
    .diag-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .diag-title-wrap h3 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      margin: 0 0 0.2rem 0;
      letter-spacing: -0.01em;
    }
    .diag-title-wrap p {
      font-size: 0.82rem;
      color: #8E7E73;
      margin: 0;
    }
    .diag-actions-group {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      flex-wrap: wrap;
    }

    .diag-table-scroll {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 6px;
    }
    .diag-desktop-table {
      width: 100%;
      min-width: 680px;
      border-collapse: collapse;
      font-size: 0.84rem;
      text-align: left;
    }
    .diag-desktop-table th {
      padding: 0.75rem 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #8E7E73;
      font-weight: 700;
      background: #140E0C;
      white-space: nowrap;
    }
    .diag-desktop-table td {
      padding: 0.85rem 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #E2D5CC;
      vertical-align: middle;
    }
    .diag-desktop-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    /* Unboxed table name code — clean monospace without artificial background */
    .table-name-code {
      font-family: var(--font-mono);
      font-weight: 600;
      font-size: 0.82rem;
      color: #FAF7F2;
      background: transparent;
      border: none;
      padding: 0;
    }
    .table-purpose-cell {
      font-size: 0.8rem;
      color: #A8988C;
    }
    .table-rows-cell {
      font-family: var(--font-mono);
      font-weight: 600;
      font-size: 0.82rem;
      color: #FAF7F2;
    }
    .table-size-cell {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      color: #8E7E73;
    }

    /* Modern dot-indicator status indicators */
    .table-status-tag,
    .status-badge-applied {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
      color: #6EE7B7;
      background: none;
      border: none;
      padding: 0;
    }
    .table-status-tag::before,
    .status-badge-applied::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 5px rgba(16, 185, 129, 0.4);
    }
    .status-badge-pending {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
      color: #DF9B64;
      background: none;
      border: none;
      padding: 0;
    }
    .status-badge-pending::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #DF9B64;
      box-shadow: 0 0 5px rgba(223, 155, 100, 0.4);
    }
    .status-badge-unavailable {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
      color: #F87171;
      background: none;
      border: none;
      padding: 0;
    }
    .status-badge-unavailable::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #EF4444;
      box-shadow: 0 0 5px rgba(239, 68, 68, 0.4);
    }

    .role-badge-system {
      background: rgba(148, 163, 184, 0.12);
      color: #CBD5E1;
      border: 1px solid rgba(148, 163, 184, 0.25);
    }

    /* Unboxed audit action tag — clean monospace without dark border/background */
    .audit-action-tag {
      display: inline-flex;
      align-items: center;
      font-family: var(--font-mono);
      font-size: 0.78rem;
      font-weight: 600;
      color: #FAF7F2;
      background: transparent;
      border: none;
      padding: 0;
      white-space: nowrap;
    }
    .audit-timestamp-cell {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: #8E7E73;
      white-space: nowrap;
    }
    .audit-actor-wrap {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .audit-actor-name {
      font-weight: 600;
      color: #FFF;
      font-size: 0.82rem;
      word-break: break-all;
    }
    .audit-ip-cell {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: #8E7E73;
      white-space: nowrap;
    }

    /* Mobile & Multi-Viewport Responsiveness */
    @media (max-width: 1200px) {
      .kpi-tiles-grid {
        grid-template-columns: repeat(3, 1fr);
      }
      .viz-card.span-8 { grid-column: span 12; }
      .viz-card.span-4 { grid-column: span 12; }
      .viz-card.span-5 { grid-column: span 6; }
      .viz-card.span-3 { grid-column: span 12; }
    }

    @media (max-width: 900px) {
      .kpi-tiles-grid {
        grid-template-columns: 1fr;
      }
      .sidebar-toggle-btn {
        display: flex;
      }
      .admin-sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.75);
      }
      .admin-sidebar.mobile-open {
        transform: translateX(0);
      }
      .admin-main-canvas {
        margin-left: 0;
      }
      .canvas-topbar {
        padding: 1rem 1.25rem;
      }
      .admin-view-panel {
        padding: 1.25rem;
      }
      .users-roles-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .diag-health-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .kpi-tiles-grid {
        grid-template-columns: 1fr;
      }
      .kpi-stat-card:last-child {
        grid-column: span 1;
      }
      .analytics-visual-grid > * {
        grid-column: span 12 !important;
      }
      .ledger-table-wrap {
        padding: 1.15rem;
      }
      .ledger-desktop-table,
      #viewAuditTrail .diag-table-scroll {
        display: none;
      }
      .ledger-mobile-cards,
      #viewAuditTrail .ledger-mobile-cards {
        display: flex;
      }
      .canvas-title-group h2 {
        font-size: 1.15rem;
      }
      .date-control-group {
        width: 100%;
        justify-content: space-between;
      }
      .date-presets-strip {
        width: 100%;
        justify-content: space-around;
      }
      .date-preset-btn {
        padding: 0.35rem 0.55rem;
        font-size: 0.75rem;
        flex: 1;
        justify-content: center;
      }
      input[type="date"] {
        font-size: 16px !important; /* Prevents iOS auto-zoom */
      }
    }

    @media (max-width: 480px) {
      .kpi-tiles-grid {
        grid-template-columns: 1fr;
      }
      .kpi-stat-card:last-child {
        grid-column: span 1;
      }
      .payment-legend-row {
        grid-template-columns: 1fr;
      }
      .canvas-actions-bar {
        width: 100%;
      }
      .admin-action-btn {
        flex: 1;
        justify-content: center;
      }
      .ledger-filter-toolbar {
        gap: 0.5rem;
        width: 100%;
      }
      .filter-chip-group {
        width: 100%;
        display: flex;
        box-sizing: border-box;
      }
      .filter-chip-group .filter-chip-btn {
        flex: 1 1 0;
        justify-content: center;
        text-align: center;
        font-size: 0.72rem;
        padding: 0.3rem 0.25rem;
      }
      .ledger-search-box {
        max-width: 100%;
        width: 100%;
      }
      .users-roles-grid {
        grid-template-columns: 1fr;
      }
      .users-search-box {
        max-width: 100%;
        width: 100%;
      }
      .users-toolbar {
        flex-direction: column;
        align-items: stretch;
      }
      .users-filter-toolbar {
        flex-direction: column;
        align-items: stretch;
      }
      .users-role-select-filter {
        width: 100%;
      }
      .diag-health-grid {
        grid-template-columns: 1fr;
      }
      .diag-toolbar {
        flex-direction: column;
        align-items: stretch;
      }
      .diag-actions-group {
        width: 100%;
      }
      .diag-actions-group .admin-action-btn {
        flex: 1;
        justify-content: center;
      }
      #modalSqlContent {
        max-height: 180px;
      }
      .oms-modal-box {
        padding: 1.15rem 1rem;
        max-height: 86vh;
      }
    }
  </style>

  <script>
    (async function initAdminAuthGuard() {
      try {
        var res = await fetch('api/auth.php?action=me', { credentials: 'include' });
        if (!res.ok) {
          window.location.replace('index.php?error=unauthorized');
          return;
        }
        var data = await res.json();
        if (!data.authenticated || !data.user) {
          window.location.replace('index.php?error=unauthorized');
          return;
        }
        if (data.user.role === 'customer') {
          window.location.replace('index.php?notice=customer_restricted');
          return;
        }
        if (data.user.role === 'staff') {
          window.location.replace('kds.php?notice=staff_restricted');
          return;
        }
        // Save authenticated session for studio
        window.__CURRENT_USER__ = data.user;
        localStorage.setItem('becoffee_demo_session', JSON.stringify(data.user));

        if (typeof window.renderRoleSpecificElements === 'function') {
          window.renderRoleSpecificElements();
        } else {
          document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.renderRoleSpecificElements === 'function') {
              window.renderRoleSpecificElements();
            }
          });
        }
      } catch (e) {
        console.warn('Admin guard check deferred:', e);
      }
    })();
  </script>
</head>
<body class="admin-hub-body">

  <!-- 1. Sleek Left Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand -->
    <a href="home.php" class="sidebar-brand">
      <div class="sidebar-logo">B</div>
      <div>
        <div class="sidebar-brand-name">BeCoffee</div>
        <span class="sidebar-brand-tag">Cafe Admin</span>
      </div>
    </a>

    <!-- Navigation Hub -->
    <nav class="sidebar-nav">
      <div class="nav-section-title">Main Menu</div>
      
      <!-- Analytics Tab (Sales & Reports) -->
      <button type="button" class="sidebar-nav-btn active" id="navBtnAnalytics" data-view="analytics">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        <span>Sales & Reports</span>
      </button>

      <!-- Customer Orders Tab -->
      <button type="button" class="sidebar-nav-btn" id="navBtnAuditTrail" data-view="audit-trail">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Customer Orders</span>
      </button>

      <!-- Menu & Stock Tab -->
      <button type="button" class="sidebar-nav-btn" id="navBtnStockControl" data-view="stock-control">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        <span>Menu & Stock</span>
      </button>

      <!-- Table QR Stickers (Admin & SuperAdmin) -->
      <button type="button" class="sidebar-nav-btn" id="navBtnQRStickers" data-view="qr-stickers" title="Open printable table QR stickers generator">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Table QR Stickers</span>
      </button>

      <!-- SuperAdmin Section (Secret / Developer Only) -->
      <div class="nav-section-title superadmin-only-nav" id="developerSectionTitle">
        Developer Suite
      </div>

      <!-- 1. Users & Access -->
      <button type="button" class="sidebar-nav-btn superadmin-only-nav" id="navBtnUsers" data-view="users">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Users & Access</span>
      </button>

      <!-- 2. System Diagnostics -->
      <button type="button" class="sidebar-nav-btn superadmin-only-nav" id="navBtnDiagnostics" data-view="diagnostics">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
        <span>System Diagnostics</span>
      </button>

      <!-- 3. Database & Migrations -->
      <button type="button" class="sidebar-nav-btn superadmin-only-nav" id="navBtnDatabase" data-view="database">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
        <span>Database & Schema</span>
      </button>

      <!-- 4. Security Audit Trail -->
      <button type="button" class="sidebar-nav-btn superadmin-only-nav" id="navBtnAuditLogs" data-view="audit-logs">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        <span>Audit & Security Logs</span>
      </button>
    </nav>

    <!-- Secondary Switcher Links -->
    <div class="sidebar-bottom-links">
      <div class="nav-section-title">Quick Links</div>
      <button type="button" class="sidebar-quick-link" id="quickLinkKds" data-view="kds">
        <span class="quick-link-label">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="3" rx="2"></rect><line x1="8" x2="16" y1="21" y2="21"></line><line x1="12" x2="12" y1="17" y2="21"></line></svg>
          <span>Kitchen Screen</span>
        </span>
        <span class="quick-link-badge">LIVE</span>
      </button>
      <button type="button" class="sidebar-quick-link signout-link" id="adminSignOutBtn">
        <span class="quick-link-label">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
          <span>Sign Out</span>
        </span>
      </button>
    </div>
  </aside>

  <!-- Mobile Sidebar Backdrop Overlay -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- 2. Main Workspace Canvas -->
  <div class="admin-main-canvas">
    
    <!-- Top Canvas Header -->
    <header class="canvas-topbar" id="adminCanvasHeader">
      <div class="canvas-title-wrap">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle navigation drawer" aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="canvas-title-group">
          <h2 id="currentCanvasTitle">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            Sales & Reports
          </h2>
          <p id="currentCanvasSubtitle">Check your daily sales, payment methods, busiest hours, and popular drinks.</p>
        </div>
      </div>

      <div class="canvas-actions-bar" id="canvasActionsBar">
        <!-- Date Selector & Quick Presets (for Analytics) -->
        <div id="analyticsHeaderControls" class="date-control-group">
          <div class="date-presets-strip" role="group" aria-label="Date range selector">
            <button type="button" class="date-preset-btn active" id="presetTodayBtn">Today</button>
            <button type="button" class="date-preset-btn" id="presetYesterdayBtn">Yesterday</button>
          </div>
          <input type="date" id="ledgerDatePicker" class="date-input-clean" aria-label="Select ledger date">
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshLedger" title="Refresh data">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            <span>Refresh Data</span>
          </button>
        </div>

        <!-- Stock Controls (for Menu & Stock View) -->
        <div id="stockHeaderControls" style="display: none; gap: 0.65rem; align-items: center;">
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnResetAllStockTop" title="Reset all drinks and options to in stock">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            <span>Restock All</span>
          </button>
          <button type="button" class="admin-action-btn btn-action-accent" id="btnOpenAddDrinkModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Add Drink</span>
          </button>
        </div>

        <!-- Embedded Quick Links Controls (Device Viewport Simulator & Frame Actions) -->
        <div id="embeddedHeaderControls" style="display: none; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
          <!-- Persona Badge (Hidden from UI, retained for test compatibility) -->
          <span id="previewPersonaBadge" class="preview-persona-badge" style="display: none !important;" aria-hidden="true"></span>

          <!-- Viewport Simulator Controls -->
          <div class="preview-device-strip" id="previewDeviceStrip" role="group" aria-label="Device viewport simulator">
            <button type="button" class="preview-device-btn" data-width="375px" title="Mobile Viewport (375px)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>
              <span>Mobile</span>
            </button>
            <button type="button" class="preview-device-btn" data-width="768px" title="Tablet Viewport (768px)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>
              <span>Tablet</span>
            </button>
            <button type="button" class="preview-device-btn active" data-width="100%" title="Desktop Full Width">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
              <span>Desktop</span>
            </button>
          </div>

          <!-- Action Buttons -->
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnTopReloadFrame" title="Reload active screen">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
            <span>Reload</span>
          </button>
          <a href="#" target="_blank" rel="noopener" class="admin-action-btn btn-action-secondary" id="btnTopPopOutFrame" title="Open in new window">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            <span>Pop Out</span>
          </a>
        </div>
      </div>
    </header>

    <!-- VIEW 1: Sales & Reports -->
    <section class="admin-view-panel active" id="viewAnalytics">
      <!-- 1. Executive Performance Summary Cards (3 Cards) -->
      <div class="kpi-tiles-grid">
        <div class="kpi-stat-card">
          <div class="kpi-stat-label">
            <span>Total Sales</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          </div>
          <div class="kpi-stat-value" id="ledgerGrossRevenue">₱0.00</div>
          <div class="kpi-stat-sub" id="ledgerGrossSub">Money made from finished orders</div>
        </div>

        <div class="kpi-stat-card">
          <div class="kpi-stat-label">
            <span>Today's Orders</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          </div>
          <div class="kpi-stat-value" id="ledgerPipelineValue">0 Total</div>
          <div class="kpi-stat-sub" id="ledgerPipelineSub">0 finished · 0 being made</div>
        </div>

        <div class="kpi-stat-card">
          <div class="kpi-stat-label">
            <span>Average Spend</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          </div>
          <div class="kpi-stat-value" id="ledgerAovValue">₱0.00</div>
          <div class="kpi-stat-sub" id="ledgerAovSub">Average money per customer</div>
        </div>
      </div>

      <!-- 2. Visual Analytics Bento Grid -->
      <div class="analytics-visual-grid">
        <!-- Panel A: Busiest Hours Today (span 8) -->
        <div class="viz-card span-8">
          <div class="viz-card-header">
            <h3 class="viz-card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
              Busiest Hours Today
            </h3>
            <span class="viz-card-badge" id="peakRushBadge">Busiest Time: Calculating...</span>
          </div>

          <div class="hourly-chart-container" id="hourlyChartContainer">
            <!-- Dynamic SVG Chart Injected via JavaScript -->
            <svg class="hourly-svg-chart" id="hourlySvgChart" viewBox="0 0 600 130" preserveAspectRatio="none">
              <!-- Gridlines and bars rendered here -->
            </svg>
          </div>
        </div>

        <!-- Panel B: How Customers Paid (span 4) -->
        <div class="viz-card span-4">
          <div class="viz-card-header">
            <h3 class="viz-card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
              How Customers Paid
            </h3>
            <span class="viz-card-badge" id="paymentTotalBadge">₱0.00 Total</span>
          </div>

          <div class="payment-split-bar">
            <div class="split-bar-gcash" id="splitBarGcash" style="width: 50%;" title="GCash Share"></div>
            <div class="split-bar-cash" id="splitBarCash" style="width: 50%;" title="Cash Share"></div>
          </div>

          <div class="payment-legend-row">
            <div class="payment-legend-box">
              <div class="payment-legend-header">
                <span class="dot-gcash"></span>
                <span>GCash QR</span>
              </div>
              <div class="payment-legend-amount" id="ledgerGcashTotal">₱0.00</div>
              <div class="payment-legend-share" id="ledgerGcashShare">0% of total sales</div>
            </div>

            <div class="payment-legend-box">
              <div class="payment-legend-header">
                <span class="dot-cash"></span>
                <span>Cash at Counter</span>
              </div>
              <div class="payment-legend-amount" id="ledgerCashTotal">₱0.00</div>
              <div class="payment-legend-share" id="ledgerCashShare">0% of total sales</div>
            </div>
          </div>
        </div>

        <!-- Panel C: Best Selling Drinks (span 5) -->
        <div class="viz-card span-5">
          <div class="viz-card-header">
            <h3 class="viz-card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
              Best Selling Drinks
            </h3>
            <span class="viz-card-badge">Most Ordered</span>
          </div>
          <div class="rank-list" id="topDrinksRankList">
            <div style="text-align: center; padding: 2rem; color: var(--admin-muted); font-size: 0.85rem;">
              No drinks sold today yet.
            </div>
          </div>
        </div>

        <!-- Panel D: Customer Preferences & Modifiers (span 4) -->
        <div class="viz-card span-4">
          <div class="viz-card-header">
            <h3 class="viz-card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
              Customer Preferences
            </h3>
            <span class="viz-card-badge">Popular Choices</span>
          </div>
          <div class="modifier-group-strip" id="modifierSummaryStrip">
            <div class="modifier-row">
              <span class="modifier-name">Oat Milk Chosen</span>
              <span class="modifier-count" id="modOatMilkCount">0 orders</span>
            </div>
            <div class="modifier-row">
              <span class="modifier-name">Less Sweet Chosen</span>
              <span class="modifier-count" id="modLessSweetCount">0 orders</span>
            </div>
            <div class="modifier-row">
              <span class="modifier-name">Iced Drinks Chosen</span>
              <span class="modifier-count" id="modIcedCount">0 orders</span>
            </div>
          </div>
        </div>

        <!-- Panel E: Kitchen Checklist Score (span 3) -->
        <div class="viz-card span-3">
          <div class="viz-card-header">
            <h3 class="viz-card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
              Kitchen Checklist Score
            </h3>
            <span class="viz-card-badge" id="qaOverallRate">100%</span>
          </div>
          <div class="qa-health-meters">
            <div class="qa-meter-row">
              <div class="qa-meter-header">
                <span>Payment Confirmed</span>
                <span id="qaPaymentRate">100%</span>
              </div>
              <div class="qa-meter-bar-track">
                <div class="qa-meter-bar-fill" id="qaPaymentBar" style="width: 100%;"></div>
              </div>
            </div>

            <div class="qa-meter-row">
              <div class="qa-meter-header">
                <span>Recipe Followed</span>
                <span id="qaCustomRate">100%</span>
              </div>
              <div class="qa-meter-bar-track">
                <div class="qa-meter-bar-fill" id="qaCustomBar" style="width: 100%;"></div>
              </div>
            </div>

            <div class="qa-meter-row">
              <div class="qa-meter-header">
                <span>Cup & Bag Sealed</span>
                <span id="qaPackageRate">100%</span>
              </div>
              <div class="qa-meter-bar-track">
                <div class="qa-meter-bar-fill" id="qaPackageBar" style="width: 100%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- VIEW 2: Customer Orders -->
    <section class="admin-view-panel" id="viewAuditTrail">
      <!-- Orders Health / KPI Summary Strip (4 Cards) -->
      <div class="diag-health-grid" style="margin-bottom: 1.5rem;">
        <div class="diag-health-card diag-card-server">
          <div class="diag-card-header">
            <span>Total Orders Today</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
          <div class="diag-card-value" id="statOrdersTotal">0</div>
          <div class="diag-card-meta" id="statOrdersTotalMeta">All orders placed today</div>
        </div>

        <div class="diag-health-card diag-card-opcache">
          <div class="diag-card-header">
            <span>Completed &amp; Served</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div class="diag-card-value" id="statOrdersCompleted" style="color: #34D399;">0</div>
          <div class="diag-card-meta" id="statOrdersCompletedMeta">Successfully delivered</div>
        </div>

        <div class="diag-health-card diag-card-php">
          <div class="diag-card-header">
            <span>Active in Kitchen</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="diag-card-value" id="statOrdersActive" style="color: #F59E0B;">0</div>
          <div class="diag-card-meta" id="statOrdersActiveMeta">In prep or awaiting prep</div>
        </div>

        <div class="diag-health-card diag-card-mysql">
          <div class="diag-card-header">
            <span>Gross Orders Value</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="diag-card-value" id="statOrdersRevenue">₱0.00</div>
          <div class="diag-card-meta" id="statOrdersRevenueMeta">Daily gross order volume</div>
        </div>
      </div>

      <!-- Main Customer Orders Table Wrap -->
      <div class="diag-table-wrap">
        <div class="diag-toolbar">
          <div class="diag-title-wrap">
            <h3>Customer Orders Ledger</h3>
            <p>Chronological stream of dine-in and takeout tickets, customizations, and kitchen QA status.</p>
          </div>
          <div class="diag-actions-group">
            <span id="ledgerRowCountNotice" style="font-size: 0.78rem; color: #8E7E73; font-weight: 500;">Showing 0 orders</span>
            <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshLedgerOrders" title="Refresh order stream">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
              <span>Refresh Orders</span>
            </button>
          </div>
        </div>

        <!-- Filter & Search Sub-Bar -->
        <div class="users-filter-toolbar" style="margin-bottom: 1.25rem;">
          <div class="users-search-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="ledgerSearchInput" placeholder="Filter by customer, phone, order #, or drink..." aria-label="Search order records">
          </div>
          <div class="users-filter-controls">
            <select id="ledgerStatusFilter" class="users-role-select-filter" aria-label="Filter by order status">
              <option value="all" selected>All Statuses</option>
              <option value="completed">Completed</option>
              <option value="active">In Progress / Active</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <select id="ledgerDiningFilter" class="users-role-select-filter" aria-label="Filter by dining mode">
              <option value="all" selected>All Dining</option>
              <option value="dine_in">Dine-In</option>
              <option value="take_out">Take-Out</option>
            </select>
            <select id="ledgerPaymentFilter" class="users-role-select-filter" aria-label="Filter by payment method">
              <option value="all" selected>All Payments</option>
              <option value="gcash">GCash</option>
              <option value="cash">Cash</option>
            </select>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="diag-table-scroll">
          <table class="diag-desktop-table">
            <thead>
              <tr>
                <th style="width: 55px;">Order #</th>
                <th style="width: 145px;">Receipt &amp; Customer</th>
                <th>Drinks &amp; Customizations</th>
                <th style="width: 75px;">Dining</th>
                <th style="width: 65px;">Payment</th>
                <th style="width: 75px;">Kitchen QA</th>
                <th style="width: 95px;">Status &amp; Prep</th>
                <th style="width: 75px; text-align: right;">Total</th>
                <th style="width: 68px; text-align: right;">Inspect</th>
              </tr>
            </thead>
            <tbody id="ledgerTableBody">
              <tr>
                <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">Loading orders...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View (< 768px) -->
        <div class="ledger-mobile-cards" id="ledgerMobileCards">
          <!-- Dynamically populated ticket cards -->
        </div>
      </div>
    </section>

    <!-- VIEW 2: Menu & Stock Control (Merged Product Catalog + Stock Option Control) -->
    <section class="admin-view-panel" id="viewStockControl">
      

      <!-- Toolbar: Categories & Search -->
      <div class="stock-controls-toolbar">
        <nav class="cat-nav-strip">
          <button type="button" class="cat-pill-btn active" data-cat="all">All Drinks</button>
          <button type="button" class="cat-pill-btn" data-cat="house-coffee">House Coffee</button>
          <button type="button" class="cat-pill-btn" data-cat="matcha">Matcha</button>
          <button type="button" class="cat-pill-btn" data-cat="house-specials">House Specials</button>
          <button type="button" class="cat-pill-btn" data-cat="yogurt-soda">Yogurt / Soda</button>
        </nav>

        <div class="search-input-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="drinkSearchInput" placeholder="Search drinks by name...">
        </div>
      </div>

      <!-- Drink Catalog Cards Grid -->
      <div class="drinks-grid" id="drinksCatalogGrid">
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--admin-muted);">
          Loading drinks and stock availability...
        </div>
      </div>
    </section>

    <!-- VIEW 3: Users & Access (SuperAdmin Only) -->
    <section class="admin-view-panel" id="viewUsers">
      <!-- Role Distribution Stat Strip (4 Cards) -->
      <div class="users-roles-grid">
        <div class="role-stat-card role-card-superadmin">
          <div class="role-stat-label">
            <span>SuperAdmins</span>
            <span class="role-stat-pill pill-superadmin">Root</span>
          </div>
          <div class="role-stat-value" id="statSuperadminCount">0</div>
          <div class="role-stat-sub">Developer & system security access</div>
        </div>

        <div class="role-stat-card role-card-admin">
          <div class="role-stat-label">
            <span>Admins</span>
            <span class="role-stat-pill pill-admin">Manager</span>
          </div>
          <div class="role-stat-value" id="statAdminCount">0</div>
          <div class="role-stat-sub">Sales reports & catalog pricing</div>
        </div>

        <div class="role-stat-card role-card-staff">
          <div class="role-stat-label">
            <span>Staff</span>
            <span class="role-stat-pill pill-staff">Operator</span>
          </div>
          <div class="role-stat-value" id="statStaffCount">0</div>
          <div class="role-stat-sub">Register POS & kitchen workstation</div>
        </div>

        <div class="role-stat-card role-card-customer">
          <div class="role-stat-label">
            <span>Customers</span>
            <span class="role-stat-pill pill-customer">User</span>
          </div>
          <div class="role-stat-value" id="statCustomerCount">0</div>
          <div class="role-stat-sub">Storefront & dine-in registered</div>
        </div>
      </div>

      <!-- Main Directory Section -->
      <div class="users-table-wrap">
        <div class="users-toolbar">
          <div class="users-title-wrap">
            <h3>User Directory & Permissions</h3>
            <p>Modify role assignments, enforce password resets, and provision system accounts.</p>
          </div>
          <button type="button" class="admin-action-btn btn-action-accent" id="btnOpenCreateUserModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create Account</span>
          </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="users-filter-toolbar">
          <div class="users-search-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="userSearchInput" placeholder="Search by name or email..." aria-label="Search users by name or email">
          </div>
          <select id="userRoleFilterSelect" class="users-role-select-filter" aria-label="Filter users by role">
            <option value="">All Roles</option>
            <option value="superadmin">SuperAdmin</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
          </select>
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshUsers" title="Refresh user list">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
            <span>Refresh</span>
          </button>
        </div>

        <!-- Users Desktop Table -->
        <div class="users-table-scroll">
          <table class="users-desktop-table">
            <thead>
              <tr>
                <th style="width: 65px;">ID</th>
                <th style="min-width: 170px;">Full Name</th>
                <th style="min-width: 210px;">Email / Login</th>
                <th style="width: 170px;">Assigned Role</th>
                <th style="text-align: right; width: 170px;">Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <tr>
                <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">Loading system users...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- VIEW 4: System Diagnostics (SuperAdmin Only) -->
    <section class="admin-view-panel" id="viewDiagnostics">
      <!-- 4 Health Cards Grid -->
      <div class="diag-health-grid">
        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>PHP Runtime</span>
            <span class="diag-card-pill pill-info">Runtime</span>
          </div>
          <div class="diag-card-value" id="diagPhpVersion">Loading...</div>
          <div class="diag-card-meta" id="diagMemory">Memory Peak: --</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>MySQL Database</span>
            <span class="diag-card-pill pill-success">Database</span>
          </div>
          <div class="diag-card-value" id="diagMysqlVersion">Loading...</div>
          <div class="diag-card-meta" id="diagDbSize">Storage: --</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Server Host</span>
            <span class="diag-card-pill pill-accent">Host OS</span>
          </div>
          <div class="diag-card-value" id="diagServerOs" title="Host Operating System">Loading...</div>
          <div class="diag-card-meta" id="diagServerTime">Server Time: --</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Execution State</span>
            <span class="diag-card-pill pill-neutral">Cache</span>
          </div>
          <div class="diag-card-value" id="diagOpcache">Loading...</div>
          <div class="diag-card-meta" id="diagMaxExec">Max Exec: --</div>
        </div>
      </div>

      <!-- Database Table Metrics Table -->
      <div class="diag-table-wrap">
        <div class="diag-toolbar">
          <div class="diag-title-wrap">
            <h3>Database Table Metrics</h3>
            <p>Row distribution, storage footprints, and engine health across active schema tables.</p>
          </div>
          <div class="diag-actions-group">
            <button type="button" class="admin-action-btn btn-action-secondary" id="btnPurgeCache" title="Purge system OPcache and temporary cache files">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              <span>Purge Cache</span>
            </button>
            <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshDiagnostics" title="Refresh environment metrics">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
              <span>Refresh Health</span>
            </button>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="diag-table-scroll">
          <table class="diag-desktop-table">
            <thead>
              <tr>
                <th style="width: 160px;">Table Name</th>
                <th>System Purpose</th>
                <th style="width: 120px;">Row Count</th>
                <th style="width: 120px;">Storage Size</th>
                <th style="width: 100px;">Status</th>
              </tr>
            </thead>
            <tbody id="tableStatsBody">
              <tr>
                <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">Loading table statistics...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- VIEW 5: Database & Schema (SuperAdmin Only) -->
    <section class="admin-view-panel" id="viewDatabase">
      <!-- Database & Schema Stat Strip (4 Cards) -->
      <div class="diag-health-grid" style="margin-bottom: 1.5rem;">
        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Applied Migrations</span>
            <span class="diag-card-pill pill-success">Ledger</span>
          </div>
          <div class="diag-card-value" id="statAppliedMigrations">-- / --</div>
          <div class="diag-card-meta" id="statMigrationSync">Verifying ledger...</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Pending Executions</span>
            <span class="diag-card-pill pill-accent">Queue</span>
          </div>
          <div class="diag-card-value" id="statPendingMigrations">0</div>
          <div class="diag-card-meta" id="statPendingMeta">Ledger is up-to-date</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Active Database</span>
            <span class="diag-card-pill pill-info">Schema</span>
          </div>
          <div class="diag-card-value" id="statDbName">becoffee_db</div>
          <div class="diag-card-meta" id="statDbEngine">InnoDB · utf8mb4</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>SQL Snapshot</span>
            <span class="diag-card-pill pill-neutral">Backup</span>
          </div>
          <div class="diag-card-value" id="statBackupStatus">Ready</div>
          <div class="diag-card-meta" id="statBackupTables">9 tracked tables</div>
        </div>
      </div>

      <!-- Main Schema Migration Ledger -->
      <div class="diag-table-wrap">
        <div class="diag-toolbar">
          <div class="diag-title-wrap">
            <h3>Database Schema & Migrations</h3>
            <p>Cryptographic migration ledger, verified table integrity, and full SQL backups.</p>
          </div>
          <div class="diag-actions-group">
            <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshMigrations" title="Refresh migration verification status">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
              <span>Refresh Status</span>
            </button>
            <a href="api/system.php?action=export_backup" class="admin-action-btn btn-action-accent" id="btnDownloadBackup" style="text-decoration: none;" title="Export full SQL snapshot of becoffee_db">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span>Export SQL Backup (.sql)</span>
            </a>
          </div>
        </div>

        <!-- Migrations Ledger Table -->
        <div class="diag-table-scroll">
          <table class="diag-desktop-table">
            <thead>
              <tr>
                <th style="width: 190px;">Migration File</th>
                <th style="width: 210px;">Milestone Title</th>
                <th>Applied Scope & Objectives</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 105px; text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody id="migrationsTableBody">
              <tr>
                <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">Verifying schema migrations...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- VIEW 6: Security Audit Trail (SuperAdmin Only) -->
    <section class="admin-view-panel" id="viewAuditLogs">
      <!-- Audit & Security Stat Strip (4 Cards) -->
      <div class="diag-health-grid" style="margin-bottom: 1.5rem;">
        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Total Logged Events</span>
            <span class="diag-card-pill pill-accent">Events</span>
          </div>
          <div class="diag-card-value" id="statTotalAuditEvents">--</div>
          <div class="diag-card-meta" id="statTotalAuditMeta">Cryptographic audit stream</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>RBAC & Auth Mutations</span>
            <span class="diag-card-pill pill-info">Security</span>
          </div>
          <div class="diag-card-value" id="statRbacAuditEvents">--</div>
          <div class="diag-card-meta" id="statRbacAuditMeta">Role & credential changes</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>System & Operations</span>
            <span class="diag-card-pill pill-neutral">Operations</span>
          </div>
          <div class="diag-card-value" id="statSystemAuditEvents">--</div>
          <div class="diag-card-meta" id="statSystemAuditMeta">Config & schema events</div>
        </div>

        <div class="diag-health-card">
          <div class="diag-card-header">
            <span>Trail Integrity</span>
            <span class="diag-card-pill pill-success">Integrity</span>
          </div>
          <div class="diag-card-value" id="statAuditIntegrity">Append-Only</div>
          <div class="diag-card-meta" id="statAuditIntegrityMeta">Immutable audit ledger</div>
        </div>
      </div>

      <!-- Main Audit Trail Table Wrap -->
      <div class="diag-table-wrap">
        <div class="diag-toolbar">
          <div class="diag-title-wrap">
            <h3>Security & Audit Log Trail</h3>
            <p>Chronological stream of administrative operations, privilege modifications, and security actions.</p>
          </div>
          <div class="diag-actions-group">
            <button type="button" class="admin-action-btn btn-action-secondary" id="btnRefreshAuditLogs" title="Refresh audit log stream">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
              <span>Refresh Logs</span>
            </button>
            <a href="api/system.php?action=export_audit_csv" class="admin-action-btn btn-action-secondary" id="btnExportAuditCsv" style="text-decoration: none;" title="Download audit trail as CSV for compliance archiving">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              <span>Export CSV</span>
            </a>
          </div>
        </div>

        <!-- Filter & Search Sub-Bar -->
        <div class="users-filter-toolbar" style="margin-bottom: 1.25rem;">
          <div class="users-search-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="auditSearchInput" placeholder="Filter by actor, action, details, or IP..." aria-label="Filter audit logs">
          </div>
          <div class="users-filter-controls">
            <select id="auditRoleFilter" class="users-role-select-filter" aria-label="Filter by actor role">
              <option value="all">All Roles</option>
              <option value="superadmin">SuperAdmin</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="customer">Customer</option>
              <option value="system">System</option>
            </select>
            <select id="auditLimitSelect" class="users-role-select-filter" aria-label="Filter by row limit">
              <option value="50" selected>Latest 50</option>
              <option value="100">Latest 100</option>
              <option value="200">Latest 200</option>
            </select>
          </div>
        </div>

        <!-- Audit Log Table -->
        <div class="diag-table-scroll">
          <table class="diag-desktop-table" style="min-width: 820px;">
            <thead>
              <tr>
                <th style="width: 145px;">Timestamp</th>
                <th style="width: 140px;">Actor &amp; Role</th>
                <th style="width: 175px;">Action Event</th>
                <th>Event Details</th>
                <th style="width: 85px;">Client IP</th>
                <th style="width: 80px; text-align: right;">Inspect</th>
              </tr>
            </thead>
            <tbody id="auditLogsTableBody">
              <tr>
                <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">Loading audit logs...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- 9. Embedded Workspace: Kitchen Display System (KDS) Live Screen -->
    <section class="admin-view-panel embedded-app-panel kds-live-panel" id="viewKds">
      <button type="button" class="kds-floating-nav-btn" id="kdsMobileNavToggle" aria-label="Open navigation menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        <span>Menu</span>
      </button>
      <div class="embedded-iframe-container">
        <iframe id="iframeKds" title="Kitchen Screen KDS Live" data-src="kds.php"></iframe>
      </div>
    </section>

    <!-- 11. Embedded Workspace: Table QR Stickers Generator -->
    <section class="admin-view-panel embedded-app-panel" id="viewQRStickers">
      <div class="embedded-iframe-container">
        <iframe id="iframeQRStickers" title="Table QR Stickers Sheet & Ordering Control" data-src="qr_stickers.php"></iframe>
      </div>
    </section>
  </div>

  <!-- CREATE USER MODAL -->
  <div class="oms-modal-overlay" id="adminCreateUserModal" aria-modal="true" role="dialog" aria-labelledby="createUserModalTitle">
    <div class="oms-modal-box" style="max-width: 460px;">
      <div class="modal-dialog-header">
        <div>
          <h3 class="modal-dialog-title" id="createUserModalTitle">Create User Account</h3>
          <p class="modal-dialog-subtitle">Provision staff, manager, or developer credentials with direct role assignment.</p>
        </div>
        <button type="button" class="oms-modal-close" id="btnCloseCreateUserModal" aria-label="Close dialog">&times;</button>
      </div>
      
      <div class="modal-field-group">
        <label for="newUserName" class="modal-field-label">Full Name</label>
        <input type="text" id="newUserName" class="modal-text-input" placeholder="e.g. Counter Staff Alice" autocomplete="off">
      </div>
      <div class="modal-field-group">
        <label for="newUserEmail" class="modal-field-label">Email / Login</label>
        <input type="email" id="newUserEmail" class="modal-text-input" placeholder="e.g. alice@becoffee.ph" autocomplete="off">
      </div>
      <div class="modal-field-group">
        <label for="newUserPassword" class="modal-field-label">Password (Min 8 characters)</label>
        <input type="password" id="newUserPassword" class="modal-text-input" placeholder="••••••••" autocomplete="new-password">
      </div>
      <div class="modal-field-group">
        <label for="newUserRole" class="modal-field-label">Assigned Role</label>
        <select id="newUserRole" class="modal-text-input">
          <option value="staff" selected>Staff (Counter & KDS Workstation)</option>
          <option value="admin">Admin (Sales Reports & Menu CMS)</option>
          <option value="superadmin">SuperAdmin (Developer & System Controls)</option>
          <option value="customer">Customer (Diner Storefront)</option>
        </select>
      </div>

      <div style="display: flex; gap: 0.65rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 0.85rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
        <button type="button" class="admin-action-btn btn-action-secondary" onclick="document.getElementById('adminCreateUserModal').classList.remove('active')">
          Cancel
        </button>
        <button type="button" class="admin-action-btn btn-action-accent" id="btnSubmitNewUser">
          Create Account
        </button>
      </div>
    </div>
  </div>

  <!-- MIGRATION SQL PREVIEW MODAL -->
  <div class="oms-modal-overlay" id="migrationSqlModal" aria-modal="true" role="dialog" aria-labelledby="modalSqlFileName">
    <div class="oms-modal-box" style="max-width: 680px; width: 92%; display: flex; flex-direction: column; max-height: 85vh;">
      <div class="modal-dialog-header">
        <div>
          <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.2rem; flex-wrap: wrap;">
            <h3 id="modalSqlFileName" class="modal-dialog-title" style="font-family: var(--font-mono);">001_create_schema.sql</h3>
            <span id="modalSqlStatusBadge" class="status-badge-applied">APPLIED</span>
          </div>
          <p id="modalSqlDescription" class="modal-dialog-subtitle">Initial database schema creation</p>
        </div>
        <button type="button" class="oms-modal-close" id="btnCloseMigrationSqlModal" aria-label="Close dialog">&times;</button>
      </div>
      
      <div style="position: relative; flex: 1; min-height: 0; display: flex; flex-direction: column;">
        <pre id="modalSqlContent" style="background: #0E0A08; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 6px; padding: 1rem; color: #FAF7F2; font-family: var(--font-mono); font-size: 0.78rem; line-height: 1.5; max-height: 380px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; margin: 0;">Loading SQL content...</pre>
      </div>

      <div style="display: flex; gap: 0.65rem; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid rgba(255, 255, 255, 0.08); flex-wrap: wrap; flex-shrink: 0;">
        <span id="modalSqlFileSize" style="font-family: var(--font-mono); font-size: 0.75rem; color: #8E7E73;">Size: -- KB</span>
        <div style="display: flex; gap: 0.65rem;">
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnCopyMigrationSql">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
            <span id="btnCopyMigrationSqlText">Copy SQL</span>
          </button>
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnCloseMigrationSqlModalBtn">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- SECURITY AUDIT EVENT INSPECT MODAL -->
  <div class="oms-modal-overlay" id="auditDetailModal" aria-modal="true" role="dialog" aria-labelledby="modalAuditDialogTitle">
    <div class="oms-modal-box" style="max-width: 640px; width: 92%; display: flex; flex-direction: column; max-height: 85vh;">
      <div class="modal-dialog-header">
        <div>
          <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.2rem; flex-wrap: wrap;">
            <h3 id="modalAuditDialogTitle" class="modal-dialog-title">
              Audit Event #<span id="modalAuditId" style="font-family: var(--font-mono); color: var(--admin-accent);">--</span>
            </h3>
            <span id="modalAuditActionTag" class="audit-action-tag">ACTION</span>
            <span id="modalAuditRoleBadge" class="user-role-badge role-badge-superadmin">superadmin</span>
          </div>
          <p class="modal-dialog-subtitle">
            Recorded timestamp: <span id="modalAuditTimestamp" style="font-family: var(--font-mono); color: #FFF;">--</span>
          </p>
        </div>
        <button type="button" class="oms-modal-close" id="btnCloseAuditDetailModal" aria-label="Close dialog">&times;</button>
      </div>

      <div style="overflow-y: auto; flex: 1; min-height: 0; padding-right: 0.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <!-- Two-box summary grid for Actor and Network context -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
          <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 6px; padding: 0.85rem 1rem;">
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.35rem; font-weight: 700;">Initiating Actor</div>
            <div id="modalAuditActor" style="font-weight: 600; color: #FFF; font-size: 0.85rem; word-break: break-all;">System</div>
            <div id="modalAuditActorSub" style="font-size: 0.75rem; color: #8E7E73; margin-top: 0.2rem; font-family: var(--font-mono);">ID: --</div>
          </div>
          <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 6px; padding: 0.85rem 1rem;">
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.35rem; font-weight: 700;">Client Context</div>
            <div style="font-family: var(--font-mono); font-size: 0.85rem; color: #DF9B64; display: flex; align-items: center; gap: 0.4rem;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              <span id="modalAuditIp">127.0.0.1</span>
            </div>
            <div style="font-size: 0.75rem; color: #8E7E73; margin-top: 0.2rem;">Origin IP Address</div>
          </div>
        </div>

        <!-- Details / Payload -->
        <div>
          <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.45rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center;">
            <span>Operation Payload &amp; Mutation Details</span>
            <span style="font-size: 0.7rem; color: #8E7E73; font-weight: 400; text-transform: none;">Raw audit string / JSON</span>
          </div>
          <pre id="modalAuditDetails" style="background: #0E0A08; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 6px; padding: 1rem; color: #FAF7F2; font-family: var(--font-mono); font-size: 0.8rem; line-height: 1.5; max-height: 260px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; margin: 0;">--</pre>
        </div>
      </div>

      <div style="display: flex; gap: 0.65rem; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid rgba(255, 255, 255, 0.08); flex-wrap: wrap; flex-shrink: 0;">
        <span style="font-family: var(--font-mono); font-size: 0.75rem; color: #8E7E73; display: flex; align-items: center; gap: 0.35rem;">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Immutable Log Ledger
        </span>
        <div style="display: flex; gap: 0.65rem;">
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnCopyAuditPayload">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
            <span id="btnCopyAuditPayloadText">Copy Details</span>
          </button>
          <button type="button" class="admin-action-btn btn-action-secondary" id="btnCloseAuditDetailModalBtn">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- CUSTOMER ORDER INSPECT MODAL -->
  <div class="oms-modal-overlay" id="orderDetailModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box" style="max-width: 640px; width: 92%; display: flex; flex-direction: column; max-height: 85vh;">
      <button type="button" class="oms-modal-close" id="btnCloseOrderDetailModal" aria-label="Close dialog">&times;</button>
      
      <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem; flex-wrap: wrap; flex-shrink: 0;">
        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #FFF;">
          Order #<span id="modalOrderQueue" style="font-family: var(--font-mono); color: var(--admin-accent);">--</span>
        </h3>
        <span id="modalOrderStatusBadge" class="order-status-tag status-waiting">Waiting</span>
        <span id="modalOrderDiningBadge" class="dining-badge badge-dine-in">Table 1</span>
        <span id="modalOrderPaymentBadge" class="payment-badge badge-gcash">GCASH</span>
      </div>
      <p style="font-size: 0.82rem; color: var(--admin-muted); margin-bottom: 1.25rem; flex-shrink: 0;">
        Receipt: <span id="modalOrderRef" style="font-family: var(--font-mono); color: #FFF;">--</span>
        · <span id="modalOrderTimestamp">--</span>
      </p>

      <div style="overflow-y: auto; flex: 1; min-height: 0; padding-right: 0.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <!-- Two-box summary grid for Customer & Kitchen QA Context -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
          <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 6px; padding: 0.75rem 0.85rem;">
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.35rem; font-weight: 700;">Customer Information</div>
            <div id="modalOrderCustomer" style="font-weight: 600; color: #FFF; font-size: 0.85rem;">Walk-in Customer</div>
            <div id="modalOrderPhone" style="font-size: 0.75rem; color: #8E7E73; margin-top: 0.2rem; font-family: var(--font-mono);">No phone recorded</div>
          </div>
          <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 6px; padding: 0.75rem 0.85rem;">
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.35rem; font-weight: 700;">Prep &amp; Kitchen QA</div>
            <div id="modalOrderPrepTime" style="font-family: var(--font-mono); font-size: 0.85rem; color: #FBBF24;">Being prepared</div>
            <div id="modalOrderQaBadges" style="font-size: 0.75rem; color: #8E7E73; margin-top: 0.2rem;">QA Checks</div>
          </div>
        </div>

        <!-- Ordered Items List -->
        <div>
          <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin-bottom: 0.45rem; font-weight: 700;">
            Order Items &amp; Customizations
          </div>
          <div id="modalOrderItemsList" style="display: flex; flex-direction: column; gap: 0.45rem;">
            <!-- Line items injected dynamically -->
          </div>
        </div>

        <!-- Pricing Summary -->
        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 6px; padding: 0.85rem; font-size: 0.82rem;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; color: #A8988C;">
            <span>Subtotal</span>
            <span id="modalOrderSubtotal" style="font-family: var(--font-mono); color: #FFF;">₱0.00</span>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; color: #A8988C;">
            <span>Packaging &amp; Eco Fee</span>
            <span id="modalOrderEcoFee" style="font-family: var(--font-mono); color: #FFF;">₱0.00</span>
          </div>
          <div style="display: flex; justify-content: space-between; padding-top: 0.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08); font-weight: 700; font-size: 0.95rem; color: #FFF;">
            <span>Grand Total</span>
            <span id="modalOrderGrandTotal" style="font-family: var(--font-mono); color: #DF9B64;">₱0.00</span>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 0.65rem; justify-content: flex-end; align-items: center; margin-top: 1.25rem; flex-shrink: 0;">
        <button type="button" class="admin-action-btn btn-action-secondary" id="btnCloseOrderDetailModalBtn">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- 3. THE "MANAGE DRINK OPTIONS" MODAL -->
  <div class="oms-modal-overlay" id="adminManageModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box wide-modal">
      <div class="modal-dialog-header">
        <div>
          <h3 class="modal-dialog-title">Edit Drink &amp; Modifier Options</h3>
          <p class="modal-dialog-subtitle">Configure pricing, details, and live customer ordering availability</p>
        </div>
        <button type="button" class="oms-modal-close" id="btnCloseManageModal" aria-label="Close">&times;</button>
      </div>
      
      <div class="modal-two-col">
        <!-- LEFT COLUMN: Drink Image & Base Details -->
        <div class="modal-col-left">
          
          <!-- Large Drink Photo & Interactive Changer -->
          <div class="drink-img-preview-box">
            <img id="modalDrinkImg" src="" alt="Drink preview">
            <label for="modalDrinkFileInput" class="btn-change-photo-badge" title="Upload new photo from your device">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
              <span>Change Photo</span>
            </label>
            <input type="file" id="modalDrinkFileInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
          </div>

          <!-- Fields -->
          <div style="display: flex; flex-direction: column; gap: 0.65rem; flex: 1;">
            <!-- Row 1: Drink Name & Category -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 0.65rem;">
              <div class="modal-field-group" style="margin-bottom: 0;">
                <label for="modalDrinkName" class="modal-field-label">Drink Name</label>
                <input type="text" id="modalDrinkName" class="modal-text-input" style="font-weight: 700;" placeholder="Drink Name">
              </div>
              <div class="modal-field-group" style="margin-bottom: 0;">
                <label for="modalDrinkCategory" class="modal-field-label">Category</label>
                <select id="modalDrinkCategory" class="modal-text-input">
                  <option value="house-coffee">House Coffee</option>
                  <option value="matcha">Matcha</option>
                  <option value="house-specials">House Specials</option>
                  <option value="yogurt-soda">Yogurt / Soda</option>
                </select>
              </div>
            </div>

            <!-- Row 2: Base Price & Drink Status -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
              <div class="modal-field-group" style="margin-bottom: 0;">
                <label for="modalBasePrice" class="modal-field-label">Base Price (₱)</label>
                <input type="number" id="modalBasePrice" class="modal-text-input" step="5" min="1" style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #FFF;" value="120">
              </div>
              <div class="modal-field-group" style="margin-bottom: 0;">
                <span class="modal-field-label">Drink Status</span>
                <button type="button" id="modalDrinkStockToggle" class="admin-drink-status-btn status-btn-available" title="Toggle overall availability">
                  Available
                </button>
              </div>
            </div>

            <!-- Row 3: Direct Image URL / Path Input -->
            <div class="modal-field-group" style="margin-bottom: 0;">
              <label for="modalDrinkImgUrl" class="modal-field-label" style="display: flex; justify-content: space-between;">
                <span>Image Path or URL</span>
                <span style="font-size: 0.68rem; color: #8E7E73; text-transform: none; font-weight: 400;">Local file or URL</span>
              </label>
              <input type="text" id="modalDrinkImgUrl" class="modal-text-input" style="font-size: 0.8rem;" placeholder="images/menu/...">
            </div>

            <!-- Row 4: Description -->
            <div class="modal-field-group" style="margin-bottom: 0;">
              <label for="modalDrinkDesc" class="modal-field-label">Description</label>
              <textarea id="modalDrinkDesc" class="modal-text-input" style="min-height: 54px; max-height: 64px; font-size: 0.8rem; resize: none;" placeholder="Description shown to customers..."></textarea>
            </div>
          </div>

          <!-- Bottom: Left Actions -->
          <div style="display: flex; gap: 0.65rem; margin-top: auto; padding-top: 0.5rem;">
            <button type="button" class="admin-action-btn btn-action-accent" id="btnSaveDrinkChanges" style="flex: 1; min-height: 40px; justify-content: center; font-size: 0.84rem; font-weight: 700;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
              <span>Save Drink Details</span>
            </button>
            <button type="button" class="admin-action-btn" id="btnDeleteDrink" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #F87171; min-height: 40px; padding: 0 0.85rem; border-radius: 4px;" title="Delete this drink">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- RIGHT COLUMN: Customization Options & Extras -->
        <div class="modal-col-right">
          <!-- Header Row -->
          <div class="modal-options-header">
            <h4>Modifier Availability &amp; Add-ons</h4>
            <p>Toggle option stock state or add new modifiers for this cafe item.</p>
          </div>

          <!-- Group 1: TEMPERATURE -->
          <div class="option-group-label" style="margin-top: 0.25rem;">
            <span>Drink Temperature</span>
            <button type="button" class="btn-cat-toggle-stock btn-modifier-toggle" data-cat-type="temperature">
              Toggle All
            </button>
          </div>
          <div class="pill-radio-group" id="modalTempGroup">
            <div class="admin-option-pill is-in-stock" data-opt="Iced">
              <span class="option-title-text">Iced</span>
              <span class="status-indicator-tag tag-in-stock">In Stock</span>
            </div>
            <div class="admin-option-pill is-in-stock" data-opt="Hot">
              <span class="option-title-text">Hot</span>
              <span class="status-indicator-tag tag-in-stock">In Stock</span>
            </div>
          </div>

          <!-- Group 2: MILK & EXTRAS -->
          <div class="option-group-label">
            <span>Milk &amp; Extras</span>
            <div style="display: flex; gap: 0.4rem; align-items: center;">
              <button type="button" class="btn-cat-toggle-stock btn-modifier-toggle" data-cat-type="addon">
                Toggle All
              </button>
              <button type="button" id="btnShowAddAddonForm" class="btn-modifier-add-extra">
                + Add Extra
              </button>
            </div>
          </div>

          <!-- Inline New Add-on Creator Form -->
          <div id="newAddonFormWrap" class="new-addon-form-card">
            <div style="font-size: 0.72rem; font-weight: 700; color: #FFF; margin-bottom: 0.45rem; text-transform: uppercase; letter-spacing: 0.05em;">Create Extra Option</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <input type="text" id="inputNewAddonName" class="modal-text-input" style="flex: 2; min-width: 140px; padding: 0.4rem 0.6rem; font-size: 0.8rem;" placeholder="e.g. Vanilla Syrup">
              <div style="display: flex; align-items: center; gap: 0.25rem;">
                <span style="color: #9E8E81; font-weight: 700; font-size: 0.8rem;">+₱</span>
                <input type="number" id="inputNewAddonPrice" class="modal-text-input" style="width: 65px; padding: 0.4rem 0.5rem; font-family: var(--font-mono); font-size: 0.8rem;" value="30" step="5" min="0">
              </div>
              <button type="button" id="btnSubmitNewAddon" class="admin-action-btn btn-action-accent" style="padding: 0.4rem 0.75rem; font-size: 0.75rem;">
                Save Extra
              </button>
              <button type="button" id="btnCancelNewAddon" class="admin-action-btn btn-action-secondary" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                Cancel
              </button>
            </div>
          </div>

          <!-- Dynamic Add-ons List -->
          <div class="pill-radio-group" id="modalAddonsGroup">
            <!-- Injected from stock options where category_type = 'addon' -->
          </div>

          <!-- Group 3: SWEETNESS LEVEL -->
          <div class="option-group-label">
            <span>Sweetness Level</span>
            <button type="button" class="btn-cat-toggle-stock btn-modifier-toggle" data-cat-type="sweetness">
              Toggle All
            </button>
          </div>
          <div class="pill-radio-group" id="modalSweetnessGroup">
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
        </div>
      </div>
    </div>
  </div>

  <!-- 4. Add New Drink Modal -->
  <div class="oms-modal-overlay" id="adminAddDrinkModal" aria-modal="true" role="dialog">
    <div class="oms-modal-box">
      <div class="modal-dialog-header">
        <div>
          <h3 class="modal-dialog-title">Add New Drink</h3>
          <p class="modal-dialog-subtitle">Add a new handcrafted beverage to your cafe menu.</p>
        </div>
        <button type="button" class="oms-modal-close" id="btnCloseAddModalBtn" aria-label="Close">&times;</button>
      </div>

      <div class="modal-field-group">
        <label for="newDrinkName" class="modal-field-label">Drink Name</label>
        <input type="text" id="newDrinkName" class="modal-text-input" placeholder="e.g. Vanilla Cold Foam">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.85rem;">
        <div class="modal-field-group" style="margin-bottom: 0;">
          <label for="newDrinkCategory" class="modal-field-label">Category</label>
          <select id="newDrinkCategory" class="modal-text-input">
            <option value="house-coffee">House Coffee</option>
            <option value="matcha">Matcha</option>
            <option value="house-specials">House Specials</option>
            <option value="yogurt-soda">Yogurt / Soda</option>
          </select>
        </div>
        <div class="modal-field-group" style="margin-bottom: 0;">
          <label for="newDrinkPrice" class="modal-field-label">Base Price (₱)</label>
          <input type="number" id="newDrinkPrice" class="modal-text-input" value="130" step="5">
        </div>
      </div>

      <div class="modal-field-group">
        <label for="newDrinkDesc" class="modal-field-label">Description</label>
        <textarea id="newDrinkDesc" class="modal-text-input" placeholder="Tasting notes and ingredients..."></textarea>
      </div>

      <button type="button" class="admin-action-btn btn-action-accent" id="btnSubmitNewDrink" style="width: 100%; min-height: 44px; justify-content: center; font-size: 0.9rem; margin-top: 1rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Save New Drink</span>
      </button>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div class="admin-toast toast-success" id="adminToast">
    <span id="toastMessage">Action completed successfully.</span>
  </div>

  <!-- JavaScript Controller -->
  <script src="js/system-dialog.js"></script>
  <script>
    (function initAdminOperationsHub() {
      // 1. Sidebar Tab Navigation & Mobile Drawer Controller
      var adminSidebar = document.getElementById('adminSidebar');
      var sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
      var sidebarBackdrop = document.getElementById('sidebarBackdrop');

      var navBtnAnalytics = document.getElementById('navBtnAnalytics');
      var navBtnAuditTrail = document.getElementById('navBtnAuditTrail');
      var navBtnStock = document.getElementById('navBtnStockControl');
      var navBtnUsers = document.getElementById('navBtnUsers');
      var navBtnDiagnostics = document.getElementById('navBtnDiagnostics');
      var navBtnDatabase = document.getElementById('navBtnDatabase');
      var quickLinkKds = document.getElementById('quickLinkKds');

      var viewAnalytics = document.getElementById('viewAnalytics');
      var viewAuditTrail = document.getElementById('viewAuditTrail');
      var viewStock = document.getElementById('viewStockControl');
      var viewUsers = document.getElementById('viewUsers');
      var viewDiagnostics = document.getElementById('viewDiagnostics');
      var viewDatabase = document.getElementById('viewDatabase');
      var viewAuditLogs = document.getElementById('viewAuditLogs');

      var viewKds = document.getElementById('viewKds');
      var viewQRStickers = document.getElementById('viewQRStickers');

      var iframeKds = document.getElementById('iframeKds');
      var iframeQRStickers = document.getElementById('iframeQRStickers');
      var navBtnQRStickers = document.getElementById('navBtnQRStickers');

      var currentTitle = document.getElementById('currentCanvasTitle');
      var currentSubtitle = document.getElementById('currentCanvasSubtitle');
      var adminCanvasHeader = document.getElementById('adminCanvasHeader');
      var kdsMobileNavToggle = document.getElementById('kdsMobileNavToggle');
      var analyticsHeaderControls = document.getElementById('analyticsHeaderControls');
      var stockHeaderControls = document.getElementById('stockHeaderControls');
      var embeddedHeaderControls = document.getElementById('embeddedHeaderControls');
      var btnTopReloadFrame = document.getElementById('btnTopReloadFrame');
      var btnTopPopOutFrame = document.getElementById('btnTopPopOutFrame');

      function openMobileSidebar() {
        if (adminSidebar) adminSidebar.classList.add('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('active');
        if (sidebarToggleBtn) sidebarToggleBtn.setAttribute('aria-expanded', 'true');
      }

      function closeMobileSidebar() {
        if (adminSidebar) adminSidebar.classList.remove('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('active');
        if (sidebarToggleBtn) sidebarToggleBtn.setAttribute('aria-expanded', 'false');
      }

      if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          if (adminSidebar && adminSidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
          } else {
            openMobileSidebar();
          }
        });
      }

      if (kdsMobileNavToggle) {
        kdsMobileNavToggle.addEventListener('click', function(e) {
          e.stopPropagation();
          openMobileSidebar();
        });
      }

      if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeMobileSidebar);
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && adminSidebar && adminSidebar.classList.contains('mobile-open')) {
          closeMobileSidebar();
        }
      });

      function switchView(target) {
        closeMobileSidebar();

        // Clear all active tabs and views
        var allNavBtns = [
          navBtnAnalytics, navBtnAuditTrail, navBtnStock, navBtnQRStickers, navBtnUsers,
          navBtnDiagnostics, navBtnDatabase, navBtnAuditLogs,
          quickLinkKds
        ];
        var allViews = [
          viewAnalytics, viewAuditTrail, viewStock, viewQRStickers, viewUsers,
          viewDiagnostics, viewDatabase, viewAuditLogs,
          viewKds
        ];

        allNavBtns.forEach(function(btn) { if (btn) btn.classList.remove('active'); });
        allViews.forEach(function(v) { if (v) v.classList.remove('active'); });

        if (adminCanvasHeader) adminCanvasHeader.style.display = '';
        if (stockHeaderControls) stockHeaderControls.style.display = 'none';
        if (analyticsHeaderControls) analyticsHeaderControls.style.display = 'none';
        if (embeddedHeaderControls) embeddedHeaderControls.style.display = 'none';

        if (target === 'users' || target === 'developer-hub') {
          if (navBtnUsers) navBtnUsers.classList.add('active');
          if (viewUsers) viewUsers.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Users & Access Management
          `;
          currentSubtitle.textContent = 'Manage staff permissions, promote accounts, reset passwords, and oversee credentials.';
          loadUsersData();

        } else if (target === 'diagnostics') {
          if (navBtnDiagnostics) navBtnDiagnostics.classList.add('active');
          if (viewDiagnostics) viewDiagnostics.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            System Diagnostics & Health
          `;
          currentSubtitle.textContent = 'Real-time PHP environment status, memory consumption, MySQL throughput, and table metrics.';
          loadDiagnosticsData();

        } else if (target === 'database') {
          if (navBtnDatabase) navBtnDatabase.classList.add('active');
          if (viewDatabase) viewDatabase.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
            Database Schema & Migrations
          `;
          currentSubtitle.textContent = 'Verify schema migration versions (001–007), inspect table integrity, and export backups.';
          loadMigrationsData();

        } else if (target === 'audit-logs') {
          if (navBtnAuditLogs) navBtnAuditLogs.classList.add('active');
          if (viewAuditLogs) viewAuditLogs.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Security & Audit Logs
          `;
          currentSubtitle.textContent = 'Chronological ledger of admin logins, privilege modifications, price edits, and schema events.';
          loadAuditLogsData();

        } else if (target === 'audit-trail') {
          if (navBtnAuditTrail) navBtnAuditTrail.classList.add('active');
          if (viewAuditTrail) viewAuditTrail.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Customer Orders
          `;
          currentSubtitle.textContent = 'View all customer orders, custom requests, and kitchen prep progress.';
          if (analyticsHeaderControls) analyticsHeaderControls.style.display = 'flex';
          loadLedgerData();

        } else if (target === 'stock-control') {
          if (navBtnStock) navBtnStock.classList.add('active');
          if (viewStock) viewStock.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
            Menu & Stock
          `;
          currentSubtitle.textContent = 'Update drink prices, stock availability, and turn ingredient options on or off.';
          if (stockHeaderControls) stockHeaderControls.style.display = 'flex';
          loadStockMenu();

        } else if (target === 'kds') {
          if (quickLinkKds) quickLinkKds.classList.add('active');
          if (viewKds) viewKds.classList.add('active');
          if (adminCanvasHeader) adminCanvasHeader.style.display = 'none';

          if (btnTopPopOutFrame) btnTopPopOutFrame.href = 'kds.php';
          if (iframeKds && (!iframeKds.getAttribute('src') || iframeKds.getAttribute('src') === 'about:blank')) {
            iframeKds.setAttribute('src', iframeKds.getAttribute('data-src') || 'kds.php');
          }

        } else if (target === 'qr-stickers') {
          if (navBtnQRStickers) navBtnQRStickers.classList.add('active');
          if (viewQRStickers) viewQRStickers.classList.add('active');
          if (embeddedHeaderControls) embeddedHeaderControls.style.display = 'flex';
          var personaBadge = document.getElementById('previewPersonaBadge');
          if (personaBadge) personaBadge.style.display = 'none';

          if (btnTopPopOutFrame) btnTopPopOutFrame.href = 'qr_stickers.php';
          if (iframeQRStickers && (!iframeQRStickers.getAttribute('src') || iframeQRStickers.getAttribute('src') === 'about:blank')) {
            iframeQRStickers.setAttribute('src', iframeQRStickers.getAttribute('data-src') || 'qr_stickers.php');
          }

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Table QR Stickers
          `;
          currentSubtitle.textContent = 'Generate printable table QR stickers, manage direct order table numbers, and control master ordering status.';

        } else {
          // Default: Sales & Reports
          if (navBtnAnalytics) navBtnAnalytics.classList.add('active');
          if (viewAnalytics) viewAnalytics.classList.add('active');

          currentTitle.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            Sales & Reports
          `;
          currentSubtitle.textContent = 'Check your daily sales, payment methods, busiest hours, and popular drinks.';
          if (analyticsHeaderControls) analyticsHeaderControls.style.display = 'flex';
          loadLedgerData();
        }

        // Reset device viewport simulator to Desktop 100% on view switch
        document.querySelectorAll('.preview-device-btn').forEach(function(b) {
          b.classList.toggle('active', b.getAttribute('data-width') === '100%');
        });
        document.querySelectorAll('.embedded-iframe-container').forEach(function(c) {
          c.style.maxWidth = '100%';
          c.style.border = '1px solid var(--admin-border)';
          c.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.4)';
        });

        try {
          if (window.history && window.history.replaceState) {
            var newUrl = window.location.pathname + '?view=' + encodeURIComponent(target);
            window.history.replaceState({ view: target }, '', newUrl);
          }
        } catch (e) {}
      }

      if (navBtnAnalytics) navBtnAnalytics.addEventListener('click', function() { switchView('analytics'); });
      if (navBtnAuditTrail) navBtnAuditTrail.addEventListener('click', function() { switchView('audit-trail'); });
      if (navBtnStock) navBtnStock.addEventListener('click', function() { switchView('stock-control'); });
      if (navBtnUsers) navBtnUsers.addEventListener('click', function() { switchView('users'); });
      if (navBtnDiagnostics) navBtnDiagnostics.addEventListener('click', function() { switchView('diagnostics'); });
      if (navBtnDatabase) navBtnDatabase.addEventListener('click', function() { switchView('database'); });
      if (navBtnAuditLogs) navBtnAuditLogs.addEventListener('click', function() { switchView('audit-logs'); });
      if (navBtnQRStickers) navBtnQRStickers.addEventListener('click', function() { switchView('qr-stickers'); });

      if (quickLinkKds) quickLinkKds.addEventListener('click', function() { switchView('kds'); });

      // Viewport Simulator Switcher
      document.querySelectorAll('.preview-device-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.preview-device-btn').forEach(function(b) { b.classList.remove('active'); });
          btn.classList.add('active');
          var width = btn.getAttribute('data-width');
          document.querySelectorAll('.embedded-iframe-container').forEach(function(c) {
            c.style.maxWidth = width;
            c.style.border = (width === '100%') ? '1px solid var(--admin-border)' : '2px solid rgba(226, 135, 67, 0.5)';
            c.style.boxShadow = (width === '100%') ? '0 8px 32px rgba(0, 0, 0, 0.4)' : '0 12px 40px rgba(0, 0, 0, 0.7)';
          });
        });
      });

      // Unified Topbar Frame Reload button
      if (btnTopReloadFrame) {
        btnTopReloadFrame.addEventListener('click', function() {
          if (viewKds && viewKds.classList.contains('active') && iframeKds) {
            iframeKds.src = 'kds.php';
          } else if (viewQRStickers && viewQRStickers.classList.contains('active') && iframeQRStickers) {
            iframeQRStickers.src = 'qr_stickers.php';
          }
        });
      }

      window.switchView = switchView;

      function renderRoleSpecificElements() {
        var user = window.__CURRENT_USER__;
        if (!user) {
          try {
            user = JSON.parse(localStorage.getItem('becoffee_demo_session') || '{}');
          } catch(e){}
        }
        if (!user || !user.role) return;

        var roleTag = document.querySelector('.sidebar-brand-tag');

        if (user.role === 'superadmin') {
          if (roleTag) {
            roleTag.textContent = 'SuperAdmin / Dev';
            roleTag.className = 'sidebar-brand-tag role-superadmin';
            roleTag.removeAttribute('style');
          }
          document.querySelectorAll('.superadmin-only-nav').forEach(function(el) {
            el.style.display = el.tagName === 'BUTTON' ? 'flex' : 'block';
          });
        } else {
          if (roleTag) {
            roleTag.textContent = 'Manager Admin';
            roleTag.className = 'sidebar-brand-tag role-admin';
            roleTag.removeAttribute('style');
          }
          document.querySelectorAll('.superadmin-only-nav').forEach(function(el) {
            el.style.display = 'none';
          });
        }
      }

      window.renderRoleSpecificElements = renderRoleSpecificElements;
      renderRoleSpecificElements();

      // Read initial URL view parameter or default to role landing
      var urlParams = new URLSearchParams(window.location.search);
      var initialView = urlParams.get('view');
      if (initialView) {
        if (initialView === 'developer' || initialView === 'users') switchView('users');
        else if (initialView === 'diagnostics') switchView('diagnostics');
        else if (initialView === 'database') switchView('database');
        else if (initialView === 'audit' || initialView === 'audit-logs') switchView('audit-logs');
        else if (initialView === 'stock' || initialView === 'stock-control') switchView('stock-control');
        else if (initialView === 'orders' || initialView === 'audit-trail') switchView('audit-trail');
        else if (initialView === 'kds' || initialView === 'kitchen') switchView('kds');
        else if (initialView === 'qr-stickers' || initialView === 'stickers' || initialView === 'qr') switchView('qr-stickers');
      } else {
        var u = window.__CURRENT_USER__;
        if (!u) {
          try { u = JSON.parse(localStorage.getItem('becoffee_demo_session') || '{}'); } catch(e){}
        }
        if (u && u.role === 'superadmin') {
          switchView('users');
        }
      }

      // Sign out button with server logout
      document.getElementById('adminSignOutBtn').addEventListener('click', async function() {
        var confirmed = await SystemDialog.confirm('Sign out of Cafe Management?', {
          title: 'Sign Out',
          confirmText: 'Sign Out',
          isDestructive: true
        });
        if (confirmed) {
          try {
            await fetch('api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
          } catch(e){}
          localStorage.removeItem('becoffee_demo_session');
          window.location.href = 'home.php';
        }
      });

      // Toast Notification
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

      // ==========================================
      // DEVELOPER & USER MANAGEMENT (SuperAdmin)
      // ==========================================
      var allLoadedUsers = [];

      function sanitizeHtml(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
      }

      function renderUsersTable(usersToRender) {
        var tbody = document.getElementById('usersTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!usersToRender || usersToRender.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">No matching users found.</td></tr>';
          return;
        }

        usersToRender.forEach(function(u) {
          var tr = document.createElement('tr');
          var roleKey = (u.role || 'customer').toLowerCase();

          tr.innerHTML = `
            <td><span class="user-id-code">#${u.id}</span></td>
            <td><strong class="user-name-cell">${sanitizeHtml(u.name || '')}</strong></td>
            <td><span class="user-email-cell">${sanitizeHtml(u.email || '')}</span></td>
            <td>
              <div class="user-role-selector-wrap">
                <span class="role-status-dot role-dot-${roleKey}"></span>
                <select class="user-role-select" data-user-id="${u.id}" aria-label="Change role for ${sanitizeHtml(u.name || '')}">
                  <option value="superadmin" ${u.role === 'superadmin' ? 'selected' : ''}>SuperAdmin</option>
                  <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>Admin</option>
                  <option value="staff" ${u.role === 'staff' ? 'selected' : ''}>Staff</option>
                  <option value="customer" ${u.role === 'customer' ? 'selected' : ''}>Customer</option>
                </select>
              </div>
            </td>
            <td style="text-align: right;">
              <div class="users-actions-group">
                <button type="button" class="btn-user-action btn-reset-pw" data-user-id="${u.id}" data-user-name="${sanitizeHtml(u.name || '')}" title="Reset Password" aria-label="Reset password for ${sanitizeHtml(u.name || '')}">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 2l-2 2m-1.5 1.5L16 7l-1.5-1.5M16 7l-4.5 4.5a5 5 0 1 1-7.07 7.07 5 5 0 0 1 7.07-7.07L16 7z"/></svg>
                  <span>Reset</span>
                </button>
                <button type="button" class="btn-user-action btn-delete-user" data-user-id="${u.id}" data-user-name="${sanitizeHtml(u.name || '')}" title="Delete User" aria-label="Delete user ${sanitizeHtml(u.name || '')}">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  <span>Delete</span>
                </button>
              </div>
            </td>
          `;
          tbody.appendChild(tr);
        });

        // Bind role change listeners
        tbody.querySelectorAll('.user-role-select').forEach(function(sel) {
          sel.addEventListener('change', async function() {
            var uid = this.getAttribute('data-user-id');
            var newRole = this.value;
            var confirmed = await SystemDialog.confirm(`Change role of user #${uid} to '${newRole}'?`, {
              title: 'Change User Role',
              confirmText: 'Update Role'
            });
            if (!confirmed) {
              loadUsersData();
              return;
            }
            try {
              var updateRes = await fetch('api/users.php?action=update_role', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: parseInt(uid, 10), role: newRole })
              });
              var updateData = await updateRes.json();
              if (updateData.success) {
                showToast(updateData.message || 'User role updated.');
                loadUsersData();
              } else {
                showToast(updateData.error || 'Failed to update role.', true);
                loadUsersData();
              }
            } catch(e) {
              showToast('Network error updating role.', true);
            }
          });
        });

        // Bind password reset listeners
        tbody.querySelectorAll('.btn-reset-pw').forEach(function(btn) {
          btn.addEventListener('click', async function() {
            var uid = this.getAttribute('data-user-id');
            var uname = this.getAttribute('data-user-name');
            var newPass = await SystemDialog.prompt(`Enter new password for ${uname} (minimum 8 characters):`, '', {
              title: 'Reset Password',
              inputType: 'password',
              placeholder: 'Minimum 8 characters'
            });
            if (!newPass) return;
            if (newPass.length < 8) {
              await SystemDialog.alert('Password must be at least 8 characters long.', { title: 'Password Too Short', type: 'warning' });
              return;
            }
            try {
              var res = await fetch('api/users.php?action=reset_password', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: parseInt(uid, 10), new_password: newPass })
              });
              var data = await res.json();
              if (data.success) {
                showToast(data.message || 'Password reset successfully.');
              } else {
                showToast(data.error || 'Failed to reset password.', true);
              }
            } catch(e) {
              showToast('Network error resetting password.', true);
            }
          });
        });

        // Bind delete listeners
        tbody.querySelectorAll('.btn-delete-user').forEach(function(btn) {
          btn.addEventListener('click', async function() {
            var uid = this.getAttribute('data-user-id');
            var uname = this.getAttribute('data-user-name');
            var confirmed = await SystemDialog.confirm(`Are you sure you want to permanently delete account '${uname}' (#${uid})?`, {
              title: 'Delete User Account',
              confirmText: 'Delete Permanently',
              isDestructive: true
            });
            if (!confirmed) return;
            try {
              var res = await fetch(`api/users.php?id=${uid}`, {
                method: 'DELETE',
                credentials: 'include'
              });
              var data = await res.json();
              if (data.success) {
                showToast(data.message || 'User deleted.');
                loadUsersData();
              } else {
                showToast(data.error || 'Failed to delete user.', true);
              }
            } catch(e) {
              showToast('Network error deleting user.', true);
            }
          });
        });
      }

      function filterAndRenderUsers() {
        var query = (document.getElementById('userSearchInput')?.value || '').toLowerCase().trim();
        var role = document.getElementById('userRoleFilterSelect')?.value || '';

        var filtered = allLoadedUsers.filter(function(u) {
          var matchQuery = !query || (u.name || '').toLowerCase().includes(query) || (u.email || '').toLowerCase().includes(query);
          var matchRole = !role || u.role === role;
          return matchQuery && matchRole;
        });
        renderUsersTable(filtered);
      }

      document.getElementById('userSearchInput')?.addEventListener('input', filterAndRenderUsers);
      document.getElementById('userRoleFilterSelect')?.addEventListener('change', filterAndRenderUsers);
      document.getElementById('btnRefreshUsers')?.addEventListener('click', loadUsersData);

      async function loadUsersData() {
        try {
          var res = await fetch('api/users.php', { cache: 'no-store', credentials: 'include' });
          if (!res.ok) {
            if (res.status === 403) showToast('SuperAdmin permissions required.', true);
            return;
          }
          var data = await res.json();
          if (!data.success) return;

          allLoadedUsers = data.users || [];
          var counts = data.role_counts || {};
          document.getElementById('statSuperadminCount').textContent = counts.superadmin || 0;
          document.getElementById('statAdminCount').textContent = counts.admin || 0;
          document.getElementById('statStaffCount').textContent = counts.staff || 0;
          document.getElementById('statCustomerCount').textContent = counts.customer || 0;

          filterAndRenderUsers();

        } catch (err) {
          console.warn('Error loading users:', err);
        }
      }

      // ==========================================
      // SYSTEM DIAGNOSTICS CONTROLLER (SuperAdmin)
      // ==========================================
      async function loadDiagnosticsData() {
        try {
          // 1. Diagnostics Overview
          var diagRes = await fetch('api/system.php?action=diagnostics', { credentials: 'include' });
          if (diagRes.ok) {
            var diagData = await diagRes.json();
            if (diagData.success && diagData.diagnostics) {
              var d = diagData.diagnostics;
              document.getElementById('diagPhpVersion').textContent = 'PHP ' + d.php_version;
              document.getElementById('diagMemory').textContent = 'Used: ' + d.memory_used + ' / Peak: ' + d.memory_peak;
              document.getElementById('diagMysqlVersion').textContent = d.mysql_version;
              document.getElementById('diagDbSize').textContent = 'Database: ' + d.database_size;
              document.getElementById('diagServerOs').textContent = d.server_os;
              document.getElementById('diagServerTime').textContent = d.server_time;
              document.getElementById('diagOpcache').textContent = d.opcache_enabled ? '● OPcache Active' : '○ OPcache Off';
              document.getElementById('diagMaxExec').textContent = 'Max Exec: ' + d.max_execution_time + ' | Upload: ' + d.upload_max_filesize;
            }
          }

          // 2. Table Metrics
          var tblRes = await fetch('api/system.php?action=table_stats', { credentials: 'include' });
          if (tblRes.ok) {
            var tblData = await tblRes.json();
            var tbody = document.getElementById('tableStatsBody');
            if (tbody && tblData.success) {
              tbody.innerHTML = '';
              (tblData.table_stats || []).forEach(function(t) {
                var isHealthy = (t.status || '').toLowerCase() === 'healthy';
                var statusClass = isHealthy ? 'table-status-tag' : 'status-badge-unavailable';
                var tr = document.createElement('tr');
                tr.innerHTML = `
                  <td><code class="table-name-code">${sanitizeHtml(t.table)}</code></td>
                  <td><span class="table-purpose-cell">${sanitizeHtml(t.description)}</span></td>
                  <td><span class="table-rows-cell">${sanitizeHtml(t.rows)}</span></td>
                  <td><span class="table-size-cell">${sanitizeHtml(t.size)}</span></td>
                  <td>
                    <span class="${statusClass}">
                      ${sanitizeHtml(t.status)}
                    </span>
                  </td>
                `;
                tbody.appendChild(tr);
              });
            }
          }
        } catch(e) {
          console.warn('Diagnostics fetch error:', e);
        }
      }

      document.getElementById('btnRefreshDiagnostics')?.addEventListener('click', loadDiagnosticsData);
      document.getElementById('btnPurgeCache')?.addEventListener('click', async function() {
        try {
          var res = await fetch('api/system.php?action=purge_cache', { method: 'POST', credentials: 'include' });
          var data = await res.json();
          if (data.success) {
            showToast(data.message || 'Cache purged.');
            loadDiagnosticsData();
          } else {
            showToast(data.error || 'Failed to purge cache.', true);
          }
        } catch(e) {
          showToast('Network error purging cache.', true);
        }
      });

      // ==========================================
      // DATABASE MIGRATIONS CONTROLLER (SuperAdmin)
      // ==========================================
      var currentLoadedSql = '';

      async function openMigrationSqlModal(file, title, note, status) {
        var modal = document.getElementById('migrationSqlModal');
        var fileEl = document.getElementById('modalSqlFileName');
        var descEl = document.getElementById('modalSqlDescription');
        var statusEl = document.getElementById('modalSqlStatusBadge');
        var contentEl = document.getElementById('modalSqlContent');
        var sizeEl = document.getElementById('modalSqlFileSize');
        var copyBtnText = document.getElementById('btnCopyMigrationSqlText');

        if (!modal) return;
        if (fileEl) fileEl.textContent = file;
        if (descEl) descEl.textContent = title + (note ? ' — ' + note : '');
        if (statusEl) {
          statusEl.textContent = status;
          statusEl.className = status === 'APPLIED' ? 'status-badge-applied' : 'status-badge-pending';
        }
        if (contentEl) contentEl.textContent = 'Loading SQL definition from server...';
        if (sizeEl) sizeEl.textContent = 'Size: -- KB';
        if (copyBtnText) copyBtnText.textContent = 'Copy SQL';
        currentLoadedSql = '';

        modal.classList.add('active');

        try {
          var res = await fetch('api/system.php?action=migration_sql&file=' + encodeURIComponent(file), { credentials: 'include' });
          var data = await res.json();
          if (data.success && data.sql) {
            currentLoadedSql = data.sql;
            if (contentEl) contentEl.textContent = data.sql;
            if (sizeEl) sizeEl.textContent = 'Size: ' + (data.filesize || '-- KB');
          } else {
            if (contentEl) contentEl.textContent = 'Error: ' + (data.error || 'Unable to load SQL file.');
          }
        } catch(e) {
          if (contentEl) contentEl.textContent = 'Network error fetching SQL content.';
        }
      }

      // Close modal handlers
      document.getElementById('btnCloseMigrationSqlModal')?.addEventListener('click', function() {
        document.getElementById('migrationSqlModal')?.classList.remove('active');
      });
      document.getElementById('btnCloseMigrationSqlModalBtn')?.addEventListener('click', function() {
        document.getElementById('migrationSqlModal')?.classList.remove('active');
      });
      document.getElementById('migrationSqlModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
      });

      // Copy SQL button
      document.getElementById('btnCopyMigrationSql')?.addEventListener('click', async function() {
        if (!currentLoadedSql) return;
        try {
          await navigator.clipboard.writeText(currentLoadedSql);
          var txt = document.getElementById('btnCopyMigrationSqlText');
          if (txt) {
            txt.textContent = 'Copied!';
            setTimeout(function() { txt.textContent = 'Copy SQL'; }, 2000);
          }
        } catch(e) {
          showToast('Failed to copy to clipboard', true);
        }
      });

      async function loadMigrationsData() {
        try {
          var res = await fetch('api/system.php?action=migrations', { credentials: 'include' });
          if (!res.ok) return;
          var data = await res.json();

          // Populate stat strip if summary metadata is returned
          if (data.summary) {
            var appliedEl = document.getElementById('statAppliedMigrations');
            var syncEl = document.getElementById('statMigrationSync');
            var pendingEl = document.getElementById('statPendingMigrations');
            var pendingMetaEl = document.getElementById('statPendingMeta');
            var dbNameEl = document.getElementById('statDbName');

            if (appliedEl) appliedEl.textContent = data.summary.applied + ' / ' + data.summary.total;
            if (syncEl) {
              syncEl.textContent = data.summary.pending === 0 
                ? '100% Schema Synced' 
                : data.summary.pending + ' migrations pending execution';
            }
            if (pendingEl) pendingEl.textContent = data.summary.pending;
            if (pendingMetaEl) {
              pendingMetaEl.textContent = data.summary.pending === 0 
                ? 'Ledger is up-to-date' 
                : 'Pending execution required';
            }
            if (dbNameEl && data.summary.database) {
              dbNameEl.textContent = data.summary.database;
            }
          }

          var tbody = document.getElementById('migrationsTableBody');
          if (tbody && data.success) {
            tbody.innerHTML = '';
            (data.migrations || []).forEach(function(m) {
              var tr = document.createElement('tr');
              var isApplied = m.status === 'APPLIED';
              var statusBadge = isApplied 
                ? '<span class="status-badge-applied">APPLIED</span>'
                : '<span class="status-badge-pending">PENDING</span>';

              tr.innerHTML = `
                <td><span class="table-name-code">${sanitizeHtml(m.file)}</span></td>
                <td><strong style="color: #FFF; font-size: 0.82rem; font-weight: 600;">${sanitizeHtml(m.title)}</strong></td>
                <td><span class="table-purpose-cell">${sanitizeHtml(m.note)}</span></td>
                <td>${statusBadge}</td>
                <td style="text-align: right;">
                  <button type="button" class="btn-user-action btn-action-secondary btn-view-sql" 
                    data-file="${sanitizeHtml(m.file)}" 
                    data-title="${sanitizeHtml(m.title)}" 
                    data-note="${sanitizeHtml(m.note)}" 
                    data-status="${m.status}" 
                    title="Inspect SQL DDL statements">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View SQL</span>
                  </button>
                </td>
              `;
              tbody.appendChild(tr);
            });

            // Wire click handlers for View SQL buttons
            tbody.querySelectorAll('.btn-view-sql').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var file = this.getAttribute('data-file');
                var title = this.getAttribute('data-title');
                var note = this.getAttribute('data-note');
                var status = this.getAttribute('data-status');
                openMigrationSqlModal(file, title, note, status);
              });
            });
          }
        } catch(e) {
          console.warn('Migrations fetch error:', e);
        }
      }

      document.getElementById('btnRefreshMigrations')?.addEventListener('click', loadMigrationsData);

      // ==========================================
      // SECURITY AUDIT LOGS CONTROLLER (SuperAdmin)
      // ==========================================
      var currentAuditRecordPayload = '';

      function openAuditDetailModal(id, action, role, email, userId, ip, timestamp, details) {
        var modal = document.getElementById('auditDetailModal');
        var idEl = document.getElementById('modalAuditId');
        var actionEl = document.getElementById('modalAuditActionTag');
        var roleBadgeEl = document.getElementById('modalAuditRoleBadge');
        var timeEl = document.getElementById('modalAuditTimestamp');
        var actorEl = document.getElementById('modalAuditActor');
        var actorSubEl = document.getElementById('modalAuditActorSub');
        var ipEl = document.getElementById('modalAuditIp');
        var detailsEl = document.getElementById('modalAuditDetails');
        var copyBtnText = document.getElementById('btnCopyAuditPayloadText');

        if (!modal) return;
        if (idEl) idEl.textContent = id;
        if (actionEl) actionEl.textContent = action;
        if (roleBadgeEl) {
          roleBadgeEl.textContent = role || 'system';
          roleBadgeEl.className = 'user-role-badge role-badge-' + (role || 'system');
        }
        if (timeEl) timeEl.textContent = timestamp;
        if (actorEl) actorEl.textContent = email || 'System';
        if (actorSubEl) actorSubEl.textContent = 'User ID: ' + (userId && userId !== 'N/A' ? userId : 'System Process');
        if (ipEl) ipEl.textContent = ip || '127.0.0.1';

        // Format details if JSON
        var formattedDetails = details || '—';
        try {
          var parsed = JSON.parse(details);
          formattedDetails = JSON.stringify(parsed, null, 2);
        } catch(e) {
          // not json, keep raw text
        }
        if (detailsEl) detailsEl.textContent = formattedDetails;
        currentAuditRecordPayload = JSON.stringify({
          id: id,
          timestamp: timestamp,
          action: action,
          role: role,
          actor: email,
          user_id: userId,
          ip_address: ip,
          details: details
        }, null, 2);

        if (copyBtnText) copyBtnText.textContent = 'Copy Details';
        modal.classList.add('active');
      }

      // Modal close handlers
      document.getElementById('btnCloseAuditDetailModal')?.addEventListener('click', function() {
        document.getElementById('auditDetailModal')?.classList.remove('active');
      });
      document.getElementById('btnCloseAuditDetailModalBtn')?.addEventListener('click', function() {
        document.getElementById('auditDetailModal')?.classList.remove('active');
      });
      document.getElementById('auditDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
      });

      // Copy payload button handler
      document.getElementById('btnCopyAuditPayload')?.addEventListener('click', async function() {
        if (!currentAuditRecordPayload) return;
        try {
          await navigator.clipboard.writeText(currentAuditRecordPayload);
          var txt = document.getElementById('btnCopyAuditPayloadText');
          if (txt) {
            txt.textContent = 'Copied!';
            setTimeout(function() { txt.textContent = 'Copy Details'; }, 2000);
          }
        } catch(e) {
          showToast('Failed to copy to clipboard', true);
        }
      });

      async function loadAuditLogsData() {
        var searchInput = document.getElementById('auditSearchInput');
        var roleSelect = document.getElementById('auditRoleFilter');
        var limitSelect = document.getElementById('auditLimitSelect');

        var search = (searchInput?.value || '').trim();
        var role = roleSelect?.value || 'all';
        var limit = limitSelect?.value || '50';

        var queryParams = new URLSearchParams({
          action: 'audit_logs',
          limit: limit,
          role: role,
          search: search
        });

        try {
          var res = await fetch('api/system.php?' + queryParams.toString(), { credentials: 'include' });
          if (!res.ok) return;
          var data = await res.json();

          // Populate stat cards
          if (data.summary) {
            var totalEl = document.getElementById('statTotalAuditEvents');
            var rbacEl = document.getElementById('statRbacAuditEvents');
            var sysEl = document.getElementById('statSystemAuditEvents');
            var integEl = document.getElementById('statAuditIntegrity');

            if (totalEl) totalEl.textContent = data.summary.total_all ?? '--';
            if (rbacEl) rbacEl.textContent = data.summary.rbac_events ?? '--';
            if (sysEl) sysEl.textContent = data.summary.system_events ?? '--';
            if (integEl) integEl.textContent = data.summary.scope ?? 'Append-Only';
          }

          var tbody = document.getElementById('auditLogsTableBody');
          if (tbody && data.success) {
            tbody.innerHTML = '';
            if (!data.logs || data.logs.length === 0) {
              tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">No audit log events match current criteria.</td></tr>';
              return;
            }

            data.logs.forEach(function(l) {
              var tr = document.createElement('tr');
              var roleClass = 'role-badge-' + (l.role || 'system');

              tr.innerHTML = `
                <td><span class="audit-timestamp-cell">${sanitizeHtml(l.created_at)}</span></td>
                <td>
                  <div class="audit-actor-wrap">
                    <span class="audit-actor-name">${sanitizeHtml(l.user_email || 'System')}</span>
                    <div><span class="user-role-badge ${roleClass}">${sanitizeHtml(l.role || 'system')}</span></div>
                  </div>
                </td>
                <td>
                  <span class="audit-action-tag">${sanitizeHtml(l.action)}</span>
                </td>
                <td style="color: #E2D5CC; font-size: 0.82rem; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${sanitizeHtml(l.details || '')}">
                  ${sanitizeHtml(l.details || '—')}
                </td>
                <td><span class="audit-ip-cell">${sanitizeHtml(l.ip_address || '127.0.0.1')}</span></td>
                <td style="text-align: right;">
                  <button type="button" class="btn-user-action btn-action-secondary btn-inspect-audit" 
                    data-id="${l.id}" 
                    data-action="${sanitizeHtml(l.action)}" 
                    data-role="${sanitizeHtml(l.role || 'system')}" 
                    data-email="${sanitizeHtml(l.user_email || 'System')}" 
                    data-userid="${l.user_id || 'N/A'}" 
                    data-ip="${sanitizeHtml(l.ip_address || '127.0.0.1')}" 
                    data-timestamp="${sanitizeHtml(l.created_at)}" 
                    data-details="${encodeURIComponent(l.details || '')}" 
                    title="Inspect full audit record">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Inspect</span>
                  </button>
                </td>
              `;
              tbody.appendChild(tr);
            });

            // Wire inspect buttons
            tbody.querySelectorAll('.btn-inspect-audit').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var action = this.getAttribute('data-action');
                var role = this.getAttribute('data-role');
                var email = this.getAttribute('data-email');
                var userId = this.getAttribute('data-userid');
                var ip = this.getAttribute('data-ip');
                var timestamp = this.getAttribute('data-timestamp');
                var details = decodeURIComponent(this.getAttribute('data-details') || '');
                openAuditDetailModal(id, action, role, email, userId, ip, timestamp, details);
              });
            });
          }
        } catch(e) {
          console.warn('Audit logs fetch error:', e);
        }
      }

      document.getElementById('btnRefreshAuditLogs')?.addEventListener('click', loadAuditLogsData);

      // Wire search and filter listeners
      var auditSearchTimer = null;
      document.getElementById('auditSearchInput')?.addEventListener('input', function() {
        clearTimeout(auditSearchTimer);
        auditSearchTimer = setTimeout(loadAuditLogsData, 250);
      });
      document.getElementById('auditRoleFilter')?.addEventListener('change', loadAuditLogsData);
      document.getElementById('auditLimitSelect')?.addEventListener('change', loadAuditLogsData);

      // Create User Modal Handlers
      var adminCreateUserModal = document.getElementById('adminCreateUserModal');
      var btnOpenCreateUserModal = document.getElementById('btnOpenCreateUserModal');
      var btnCloseCreateUserModal = document.getElementById('btnCloseCreateUserModal');
      var btnSubmitNewUser = document.getElementById('btnSubmitNewUser');

      if (btnOpenCreateUserModal && adminCreateUserModal) {
        btnOpenCreateUserModal.addEventListener('click', function() {
          adminCreateUserModal.classList.add('active');
        });
      }
      if (btnCloseCreateUserModal && adminCreateUserModal) {
        btnCloseCreateUserModal.addEventListener('click', function() {
          adminCreateUserModal.classList.remove('active');
        });
      }
      if (btnSubmitNewUser) {
        btnSubmitNewUser.addEventListener('click', async function() {
          var name = (document.getElementById('newUserName').value || '').trim();
          var email = (document.getElementById('newUserEmail').value || '').trim();
          var password = (document.getElementById('newUserPassword').value || '').trim();
          var role = document.getElementById('newUserRole').value;

          if (!name || !email || !password) {
            await SystemDialog.alert('Please fill in all account fields.', { title: 'Missing Information', type: 'warning' });
            return;
          }
          if (password.length < 8) {
            await SystemDialog.alert('Password must be at least 8 characters long.', { title: 'Password Too Short', type: 'warning' });
            return;
          }

          btnSubmitNewUser.disabled = true;
          btnSubmitNewUser.textContent = 'Creating...';

          try {
            var createRes = await fetch('api/users.php?action=create', {
              method: 'POST',
              credentials: 'include',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ name: name, email: email, password: password, role: role })
            });
            var createData = await createRes.json();
            if (createData.success) {
              showToast(createData.message || 'Account created successfully.');
              adminCreateUserModal.classList.remove('active');
              document.getElementById('newUserName').value = '';
              document.getElementById('newUserEmail').value = '';
              document.getElementById('newUserPassword').value = '';
              loadUsersData();
            } else {
              await SystemDialog.alert(createData.error || 'Failed to create user.', { title: 'Creation Failed', type: 'danger' });
            }
          } catch(e) {
            await SystemDialog.alert('Network error creating user.', { title: 'Network Error', type: 'danger' });
          } finally {
            btnSubmitNewUser.disabled = false;
            btnSubmitNewUser.textContent = 'Create Account';
          }
        });
      }

      // ==========================================
      // 2. ANALYTICS & DAILY LEDGER CONTROLLER
      // ==========================================
      var ledgerDatePicker = document.getElementById('ledgerDatePicker');
      var presetTodayBtn = document.getElementById('presetTodayBtn');
      var presetYesterdayBtn = document.getElementById('presetYesterdayBtn');

      function getLocalDateStr(offsetDays = 0) {
        var d = new Date();
        if (offsetDays !== 0) {
          d.setDate(d.getDate() - offsetDays);
        }
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
      }

      ledgerDatePicker.value = getLocalDateStr(0);

      if (presetTodayBtn) {
        presetTodayBtn.addEventListener('click', function() {
          presetTodayBtn.classList.add('active');
          if (presetYesterdayBtn) presetYesterdayBtn.classList.remove('active');
          ledgerDatePicker.value = getLocalDateStr(0);
          loadLedgerData();
        });
      }

      if (presetYesterdayBtn) {
        presetYesterdayBtn.addEventListener('click', function() {
          presetYesterdayBtn.classList.add('active');
          if (presetTodayBtn) presetTodayBtn.classList.remove('active');
          ledgerDatePicker.value = getLocalDateStr(1);
          loadLedgerData();
        });
      }

      ledgerDatePicker.addEventListener('change', function() {
        var todayStr = getLocalDateStr(0);
        var yestStr = getLocalDateStr(1);
        if (ledgerDatePicker.value === todayStr) {
          if (presetTodayBtn) presetTodayBtn.classList.add('active');
          if (presetYesterdayBtn) presetYesterdayBtn.classList.remove('active');
        } else if (ledgerDatePicker.value === yestStr) {
          if (presetYesterdayBtn) presetYesterdayBtn.classList.add('active');
          if (presetTodayBtn) presetTodayBtn.classList.remove('active');
        } else {
          if (presetTodayBtn) presetTodayBtn.classList.remove('active');
          if (presetYesterdayBtn) presetYesterdayBtn.classList.remove('active');
        }
        loadLedgerData();
      });

      document.getElementById('btnRefreshLedger').addEventListener('click', function() {
        loadLedgerData();
        showToast('Ledger refreshed.');
      });

      // Data state & active filters
      var rawLedgerOrders = [];
      var activeFilters = {
        search: '',
        status: 'all',
        dining: 'all',
        payment: 'all'
      };

      // Filter button listener
      document.querySelectorAll('.filter-chip-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var type = btn.getAttribute('data-filter');
          var val = btn.getAttribute('data-val');
          document.querySelectorAll(`.filter-chip-btn[data-filter="${type}"]`).forEach(function(b) {
            b.classList.remove('active');
          });
          btn.classList.add('active');
          activeFilters[type] = val;
          renderLedgerOrders();
        });
      });

      var ledgerSearchInput = document.getElementById('ledgerSearchInput');
      if (ledgerSearchInput) {
        ledgerSearchInput.addEventListener('input', function() {
          activeFilters.search = ledgerSearchInput.value.toLowerCase().trim();
          renderLedgerOrders();
        });
      }

      var ledgerStatusFilter = document.getElementById('ledgerStatusFilter');
      if (ledgerStatusFilter) {
        ledgerStatusFilter.addEventListener('change', function() {
          activeFilters.status = ledgerStatusFilter.value;
          renderLedgerOrders();
        });
      }

      var ledgerDiningFilter = document.getElementById('ledgerDiningFilter');
      if (ledgerDiningFilter) {
        ledgerDiningFilter.addEventListener('change', function() {
          activeFilters.dining = ledgerDiningFilter.value;
          renderLedgerOrders();
        });
      }

      var ledgerPaymentFilter = document.getElementById('ledgerPaymentFilter');
      if (ledgerPaymentFilter) {
        ledgerPaymentFilter.addEventListener('change', function() {
          activeFilters.payment = ledgerPaymentFilter.value;
          renderLedgerOrders();
        });
      }

      var btnRefreshLedgerOrders = document.getElementById('btnRefreshLedgerOrders');
      if (btnRefreshLedgerOrders) {
        btnRefreshLedgerOrders.addEventListener('click', function() {
          loadLedgerData();
          showToast('Orders refreshed.');
        });
      }

      async function loadLedgerData() {
        var dateVal = (ledgerDatePicker && ledgerDatePicker.value) ? ledgerDatePicker.value : getLocalDateStr(0);
        try {
          var res = await fetch(`api/ledger.php?date=${encodeURIComponent(dateVal)}`, { cache: 'no-store', credentials: 'include' });
          if (!res.ok) return;
          var data = await res.json();
          if (!data.success) return;

          var kpi = data.kpi || {};
          rawLedgerOrders = data.orders || [];

          // 1. Executive Performance Strip Updates
          var totalRev = parseFloat(kpi.total_revenue || 0);
          var completedCount = parseInt(kpi.completed_orders || 0, 10);
          var inProgressCount = parseInt(kpi.in_progress_orders || 0, 10);
          var pendingCount = parseInt(kpi.pending_orders || 0, 10);
          var cancelledCount = parseInt(kpi.cancelled_orders || 0, 10);
          var dineInCount = parseInt(kpi.dine_in_count || 0, 10);
          var takeOutCount = parseInt(kpi.take_out_count || 0, 10);
          var totalDining = dineInCount + takeOutCount;
          var totalTickets = rawLedgerOrders.length;
          var activeTotal = inProgressCount + pendingCount;

          var aov = (completedCount > 0) ? (totalRev / completedCount) : 0;
          var avgPrep = parseFloat(kpi.avg_prep_minutes || 0);
          var dineInPct = (totalDining > 0) ? Math.round((dineInCount / totalDining) * 100) : 0;

          // Customer Orders 4 Stat Cards
          var statOrdersTotalEl = document.getElementById('statOrdersTotal');
          if (statOrdersTotalEl) statOrdersTotalEl.textContent = totalTickets;
          var statOrdersTotalMetaEl = document.getElementById('statOrdersTotalMeta');
          if (statOrdersTotalMetaEl) statOrdersTotalMetaEl.textContent = `${completedCount} completed · ${activeTotal} active`;

          var statOrdersCompletedEl = document.getElementById('statOrdersCompleted');
          if (statOrdersCompletedEl) statOrdersCompletedEl.textContent = completedCount;

          var statOrdersActiveEl = document.getElementById('statOrdersActive');
          if (statOrdersActiveEl) statOrdersActiveEl.textContent = activeTotal;

          var statOrdersRevenueEl = document.getElementById('statOrdersRevenue');
          if (statOrdersRevenueEl) statOrdersRevenueEl.textContent = `₱${totalRev.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

          // Sales & Reports Summary Cards (Safe Checks)
          var grossRevEl = document.getElementById('ledgerGrossRevenue');
          if (grossRevEl) grossRevEl.textContent = `₱${totalRev.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
          var grossSubEl = document.getElementById('ledgerGrossSub');
          if (grossSubEl) grossSubEl.textContent = `${completedCount} finished order${completedCount === 1 ? '' : 's'} today`;

          var aovValEl = document.getElementById('ledgerAovValue');
          if (aovValEl) aovValEl.textContent = `₱${aov.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
          var aovSubEl = document.getElementById('ledgerAovSub');
          if (aovSubEl) aovSubEl.textContent = (completedCount > 0) ? `From ${completedCount} customer order${completedCount === 1 ? '' : 's'}` : 'No sales recorded yet';

          var pipelineValEl = document.getElementById('ledgerPipelineValue');
          if (pipelineValEl) pipelineValEl.textContent = `${totalTickets} Total`;
          var pipelineSubEl = document.getElementById('ledgerPipelineSub');
          if (pipelineSubEl) pipelineSubEl.textContent = `${completedCount} finished · ${activeTotal} being made · ${cancelledCount} cancelled`;

          var avgPrepEl = document.getElementById('ledgerAvgPrepValue');
          if (avgPrepEl) avgPrepEl.textContent = (avgPrep > 0) ? `${avgPrep.toFixed(1)}m` : '—';

          var diningSplitValEl = document.getElementById('ledgerDiningSplitValue');
          if (diningSplitValEl) diningSplitValEl.textContent = (totalDining > 0) ? `${dineInPct}% Dine-In` : 'No orders yet';
          var diningSplitSubEl = document.getElementById('ledgerDiningSplitSub');
          if (diningSplitSubEl) diningSplitSubEl.textContent = `${dineInCount} Dine-In · ${takeOutCount} Take-Out`;

          // 2. Payment Breakdown Card
          var gcashRev = parseFloat(kpi.gcash_revenue || 0);
          var cashRev = parseFloat(kpi.cash_revenue || 0);
          var gcashPct = (totalRev > 0) ? Math.round((gcashRev / totalRev) * 100) : 0;
          var cashPct = (totalRev > 0) ? (100 - gcashPct) : 0;

          var paymentTotalBadge = document.getElementById('paymentTotalBadge');
          if (paymentTotalBadge) paymentTotalBadge.textContent = `₱${totalRev.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
          var ledgerGcashTotal = document.getElementById('ledgerGcashTotal');
          if (ledgerGcashTotal) ledgerGcashTotal.textContent = `₱${gcashRev.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
          var ledgerGcashShare = document.getElementById('ledgerGcashShare');
          if (ledgerGcashShare) ledgerGcashShare.textContent = `${gcashPct}% of total sales`;
          var ledgerCashTotal = document.getElementById('ledgerCashTotal');
          if (ledgerCashTotal) ledgerCashTotal.textContent = `₱${cashRev.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
          var ledgerCashShare = document.getElementById('ledgerCashShare');
          if (ledgerCashShare) ledgerCashShare.textContent = `${cashPct}% of total sales`;

          var splitBarGcash = document.getElementById('splitBarGcash');
          var splitBarCash = document.getElementById('splitBarCash');
          if (splitBarGcash && splitBarCash) {
            if (totalRev > 0) {
              splitBarGcash.style.width = `${gcashPct}%`;
              splitBarCash.style.width = `${cashPct}%`;
            } else {
              splitBarGcash.style.width = '50%';
              splitBarCash.style.width = '50%';
            }
          }

          // 3. Hourly Order Velocity Histogram
          renderHourlyHistogram(rawLedgerOrders);

          // 4. Top Selling Drinks & Modifiers Aggregation
          renderTopDrinksAndModifiers(rawLedgerOrders);

          // 5. 3-Point QA Compliance Health
          renderQaHealthMeters(rawLedgerOrders);

          // 6. Render Filterable Order Audit Records
          renderLedgerOrders();

        } catch (e) {
          console.warn('Ledger load error:', e);
        }
      }

      function renderHourlyHistogram(orders) {
        var chartSvg = document.getElementById('hourlySvgChart');
        var peakBadge = document.getElementById('peakRushBadge');
        if (!chartSvg) return;

        // Default window: 7 AM to 10 PM, or dynamically expanded to include any orders outside standard hours
        var minHr = 7;
        var maxHr = 22;

        orders.forEach(function(ord) {
          if (!ord.created_at) return;
          var timeStr = ord.created_at.replace(' ', 'T');
          var dt = new Date(timeStr);
          var hr = dt.getHours();
          if (hr < minHr) minHr = hr;
          if (hr > maxHr) maxHr = hr;
        });

        var hoursMap = {};
        for (var h = minHr; h <= maxHr; h++) {
          hoursMap[h] = 0;
        }

        orders.forEach(function(ord) {
          if (!ord.created_at) return;
          var timeStr = ord.created_at.replace(' ', 'T');
          var dt = new Date(timeStr);
          var hr = dt.getHours();
          if (hoursMap[hr] !== undefined) {
            hoursMap[hr]++;
          }
        });

        var hoursArr = Object.keys(hoursMap).map(function(k) {
          return { hour: parseInt(k, 10), count: hoursMap[k] };
        });

        var maxCount = Math.max(1, ...hoursArr.map(h => h.count));
        var peakItem = hoursArr.reduce((prev, curr) => (curr.count > prev.count ? curr : prev), hoursArr[0]);

        if (peakBadge) {
          if (peakItem && peakItem.count > 0) {
            var formatHr = (h) => {
              var suffix = h >= 12 ? 'PM' : 'AM';
              var hr12 = h % 12 || 12;
              return `${hr12}:00 ${suffix}`;
            };
            peakBadge.textContent = `Busiest Time: ${formatHr(peakItem.hour)} (${peakItem.count} order${peakItem.count === 1 ? '' : 's'})`;
          } else {
            peakBadge.textContent = 'Busiest Time: No orders yet';
          }
        }

        // Generate SVG markup
        var svgW = 600;
        var svgH = 130;
        var bottomPadding = 24;
        var topPadding = 16;
        var plotH = svgH - bottomPadding - topPadding;
        var slotW = svgW / hoursArr.length;
        var barW = Math.max(10, Math.min(22, slotW * 0.7));

        var svgContent = `
          <!-- Baseline Axis -->
          <line x1="0" y1="${svgH - bottomPadding}" x2="${svgW}" y2="${svgH - bottomPadding}" stroke="rgba(255,255,255,0.12)" stroke-width="1" />
        `;

        hoursArr.forEach(function(item, idx) {
          var x = idx * slotW + (slotW - barW) / 2;
          var barH = (item.count / maxCount) * plotH;
          if (barH < 4 && item.count > 0) barH = 4;
          var y = (svgH - bottomPadding) - barH;
          var isPeak = (item.count > 0 && item.count === peakItem.count);

          var fillColor = isPeak ? '#E28743' : (item.count > 0 ? '#DF9B64' : 'rgba(255,255,255,0.06)');
          var labelText = (item.hour % 2 === 0 || item.hour === minHr || item.hour === maxHr) 
            ? `${item.hour % 12 || 12}${item.hour >= 12 ? 'p' : 'a'}` 
            : '';

          svgContent += `
            <g class="histogram-bar-group">
              <rect x="${x}" y="${y}" width="${barW}" height="${barH}" rx="4" fill="${fillColor}">
                <title>${item.count} order(s) at ${item.hour % 12 || 12}:00 ${item.hour >= 12 ? 'PM' : 'AM'}</title>
              </rect>
              ${item.count > 0 ? `<text x="${x + barW / 2}" y="${y - 4}" fill="${isPeak ? '#FDBA74' : '#C8B9AF'}" font-size="9" font-family="monospace" font-weight="700" text-anchor="middle">${item.count}</text>` : ''}
              <text x="${x + barW / 2}" y="${svgH - 6}" fill="rgba(255,255,255,0.4)" font-size="9" font-family="monospace" text-anchor="middle">${labelText}</text>
            </g>
          `;
        });

        chartSvg.innerHTML = svgContent;
      }

      function renderTopDrinksAndModifiers(orders) {
        var drinksRankList = document.getElementById('topDrinksRankList');
        var modOatMilkCount = document.getElementById('modOatMilkCount');
        var modLessSweetCount = document.getElementById('modLessSweetCount');
        var modIcedCount = document.getElementById('modIcedCount');

        var drinkMap = {};
        var oatMilkOrders = 0;
        var lessSweetOrders = 0;
        var icedOrders = 0;

        orders.forEach(function(ord) {
          if (!ord.items) return;
          ord.items.forEach(function(it) {
            var name = it.item_name || 'Handcrafted Beverage';
            var qty = parseInt(it.quantity || 1, 10);
            var subtotal = parseFloat(it.subtotal || it.unit_price || 0);

            if (!drinkMap[name]) {
              drinkMap[name] = { name: name, quantity: 0, revenue: 0 };
            }
            drinkMap[name].quantity += qty;
            drinkMap[name].revenue += subtotal;

            // Modifiers
            if (it.milk_option && it.milk_option.toLowerCase().includes('oat')) {
              oatMilkOrders += qty;
            }
            if (it.sweetness_level && (it.sweetness_level.includes('75') || it.sweetness_level.includes('50') || it.sweetness_level.toLowerCase().includes('less'))) {
              lessSweetOrders += qty;
            }
            if (it.temperature && it.temperature.toLowerCase().includes('iced')) {
              icedOrders += qty;
            }
          });
        });

        if (modOatMilkCount) modOatMilkCount.textContent = `${oatMilkOrders} item${oatMilkOrders === 1 ? '' : 's'}`;
        if (modLessSweetCount) modLessSweetCount.textContent = `${lessSweetOrders} item${lessSweetOrders === 1 ? '' : 's'}`;
        if (modIcedCount) modIcedCount.textContent = `${icedOrders} item${icedOrders === 1 ? '' : 's'}`;

        var sortedDrinks = Object.values(drinkMap).sort((a, b) => b.quantity - a.quantity);

        if (!drinksRankList) return;

        if (sortedDrinks.length === 0) {
          drinksRankList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--admin-muted); font-size: 0.85rem;">No drinks sold today yet.</div>';
          return;
        }

        var maxItemQty = sortedDrinks[0].quantity;

        drinksRankList.innerHTML = sortedDrinks.slice(0, 5).map(function(item, idx) {
          var pct = Math.round((item.quantity / maxItemQty) * 100);
          return `
            <div class="rank-item">
              <div class="rank-item-meta">
                <span class="rank-item-name">
                  <span class="rank-item-num">#${idx + 1}</span>
                  <span>${item.name}</span>
                </span>
                <span class="rank-item-stats">${item.quantity} sold · ₱${item.revenue.toFixed(2)} made</span>
              </div>
              <div class="rank-progress-track">
                <div class="rank-progress-fill" style="width: ${pct}%;"></div>
              </div>
            </div>
          `;
        }).join('');
      }

      function renderQaHealthMeters(orders) {
        var total = orders.length;
        if (total === 0) {
          document.getElementById('qaOverallRate').textContent = '—';
          document.getElementById('qaPaymentRate').textContent = '—';
          document.getElementById('qaCustomRate').textContent = '—';
          document.getElementById('qaPackageRate').textContent = '—';
          return;
        }

        var payVerified = orders.filter(o => o.qa_payment_verified).length;
        var custFollowed = orders.filter(o => o.qa_customizations_followed).length;
        var packSecured = orders.filter(o => o.qa_packaging_secured).length;

        var payPct = Math.round((payVerified / total) * 100);
        var custPct = Math.round((custFollowed / total) * 100);
        var packPct = Math.round((packSecured / total) * 100);
        var overallPct = Math.round((payPct + custPct + packPct) / 3);

        document.getElementById('qaOverallRate').textContent = `${overallPct}%`;
        document.getElementById('qaPaymentRate').textContent = `${payPct}%`;
        document.getElementById('qaPaymentBar').style.width = `${payPct}%`;

        document.getElementById('qaCustomRate').textContent = `${custPct}%`;
        document.getElementById('qaCustomBar').style.width = `${custPct}%`;

        document.getElementById('qaPackageRate').textContent = `${packPct}%`;
        document.getElementById('qaPackageBar').style.width = `${packPct}%`;
      }

      function renderLedgerOrders() {
        var tbody = document.getElementById('ledgerTableBody');
        var mobileCards = document.getElementById('ledgerMobileCards');
        var rowCountNotice = document.getElementById('ledgerRowCountNotice');

        var query = activeFilters.search;
        var statusFilter = activeFilters.status;
        var diningFilter = activeFilters.dining;
        var paymentFilter = activeFilters.payment;

        var filtered = rawLedgerOrders.filter(function(ord) {
          // Status filter
          if (statusFilter === 'completed' && ord.status !== 'completed') return false;
          if (statusFilter === 'active' && ord.status !== 'pending' && ord.status !== 'in_progress') return false;
          if (statusFilter === 'cancelled' && ord.status !== 'cancelled') return false;

          // Dining filter
          if (diningFilter !== 'all' && ord.order_type !== diningFilter) return false;

          // Payment filter
          if (paymentFilter !== 'all' && ord.payment_method !== paymentFilter) return false;

          // Search query
          if (query) {
            var itemsText = (ord.items || []).map(i => i.item_name).join(' ').toLowerCase();
            var searchTarget = `${ord.customer_name || ''} ${ord.queue_number || ''} ${ord.order_reference || ''} ${ord.customer_phone || ''} ${itemsText}`.toLowerCase();
            if (!searchTarget.includes(query)) return false;
          }

          return true;
        });

        if (rowCountNotice) {
          rowCountNotice.textContent = `Showing ${filtered.length} of ${rawLedgerOrders.length} order${rawLedgerOrders.length === 1 ? '' : 's'}`;
        }

        if (filtered.length === 0) {
          var emptyMsg = rawLedgerOrders.length === 0 
            ? 'No orders recorded for this date.' 
            : 'No orders match your search or filter.';
          if (tbody) tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 3rem; color: var(--admin-muted); font-size: 0.88rem;">${emptyMsg}</td></tr>`;
          if (mobileCards) mobileCards.innerHTML = `<div style="text-align: center; padding: 2rem; color: var(--admin-muted); font-size: 0.85rem;">${emptyMsg}</div>`;
          return;
        }

        // Render Desktop Table Rows
        if (tbody) {
          tbody.innerHTML = filtered.map(function(ord) {
            var itemsHtml = (ord.items || []).map(function(it) {
              var customParts = [];
              var nameLower = (it.item_name || '').toLowerCase();
              if (it.temperature && !nameLower.includes(it.temperature.toLowerCase())) {
                customParts.push(sanitizeHtml(it.temperature));
              }
              if (it.milk_option && it.milk_option !== 'Standard' && it.milk_option !== 'Regular Milk') {
                customParts.push(sanitizeHtml(it.milk_option));
              }
              if (it.sweetness_level && it.sweetness_level !== '100%' && it.sweetness_level !== 'Normal (100%)') {
                customParts.push(sanitizeHtml(it.sweetness_level));
              }
              var customStr = customParts.length > 0 ? ` <span style="font-size: 0.73rem; color: #A8988C;">(${customParts.join(' · ')})</span>` : '';
              return `<div style="margin-bottom: 0.15rem; line-height: 1.3;"><strong>${it.quantity}× ${sanitizeHtml(it.item_name)}</strong>${customStr}</div>`;
            }).join('');
            if (!itemsHtml) {
              itemsHtml = '<span style="color: var(--admin-muted); font-size: 0.78rem;">No line items</span>';
            }

            var diningBadge = ord.order_type === 'take_out'
              ? '<span class="dining-badge badge-takeout">Take-Out</span>'
              : `<span class="dining-badge badge-dine-in">Table ${sanitizeHtml(String(ord.table_number || '1'))}</span>`;

            var paymentBadge = ord.payment_method === 'gcash'
              ? '<span class="payment-badge badge-gcash">GCASH</span>'
              : '<span class="payment-badge badge-cash">CASH</span>';

            var qaPassed = (Number(ord.qa_payment_verified) === 1 && Number(ord.qa_customizations_followed) === 1 && Number(ord.qa_packaging_secured) === 1);
            var qaScore = (Number(ord.qa_payment_verified) || 0) + (Number(ord.qa_customizations_followed) || 0) + (Number(ord.qa_packaging_secured) || 0);
            var qaBadge = qaPassed
              ? `<span class="qa-badge-passed" title="Payment, customizations, and packaging all verified"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> 3/3 QA</span>`
              : `<span class="qa-badge-pending" title="${qaScore} of 3 kitchen QA checks passed">${qaScore}/3 QA</span>`;

            var statusTag = (ord.status === 'completed')
              ? '<span class="order-status-tag status-finished">Finished</span>'
              : (ord.status === 'in_progress')
              ? '<span class="order-status-tag status-progress">In Progress</span>'
              : (ord.status === 'cancelled')
              ? '<span class="order-status-tag status-cancelled">Cancelled</span>'
              : '<span class="order-status-tag status-waiting">Waiting</span>';

            var prepTimeStr = ord.completed_at
              ? `${Math.round((ord.elapsed_seconds || 0) / 60)}m prep`
              : (ord.status === 'cancelled' ? 'Cancelled' : 'Preparing');

            return `
              <tr>
                <td style="font-family: var(--font-mono); font-weight: 700; color: #FFF; font-size: 1.05rem;">#${sanitizeHtml(String(ord.queue_number))}</td>
                <td>
                  <span class="order-ref-code">${sanitizeHtml(String(ord.order_reference))}</span>
                  <span class="order-customer-sub"><strong style="color: #FFF;">${sanitizeHtml(ord.customer_name || 'Walk-in')}</strong>${ord.customer_phone ? ' · ' + sanitizeHtml(ord.customer_phone) : ''}</span>
                </td>
                <td>${itemsHtml}</td>
                <td>${diningBadge}</td>
                <td>${paymentBadge}</td>
                <td>${qaBadge}</td>
                <td>
                  ${statusTag}
                  <div class="prep-time-meta">${prepTimeStr}</div>
                </td>
                <td style="text-align: right;"><span class="order-total-cell">₱${parseFloat(ord.grand_total || 0).toFixed(2)}</span></td>
                <td style="text-align: right;">
                  <button type="button" class="btn-inspect-order" data-order-id="${ord.id}" title="Inspect full ticket details">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span>Inspect</span>
                  </button>
                </td>
              </tr>
            `;
          }).join('');
        }

        // Render Mobile Cards
        if (mobileCards) {
          mobileCards.innerHTML = filtered.map(function(ord) {
            var itemsList = (ord.items || []).map(function(it) {
              return `<div style="margin-bottom: 0.2rem;"><strong>${it.quantity}× ${sanitizeHtml(it.item_name)}</strong> <span style="color: #A8988C;">(${sanitizeHtml(it.temperature || 'Iced')} · ${sanitizeHtml(it.milk_option || 'Standard')})</span></div>`;
            }).join('');

            var statusTag = (ord.status === 'completed')
              ? '<span class="order-status-tag status-finished">Finished</span>'
              : (ord.status === 'in_progress')
              ? '<span class="order-status-tag status-progress">In Progress</span>'
              : (ord.status === 'cancelled')
              ? '<span class="order-status-tag status-cancelled">Cancelled</span>'
              : '<span class="order-status-tag status-waiting">Waiting</span>';

            var diningText = ord.order_type === 'take_out' ? 'Take-Out' : `Table ${ord.table_number || '1'}`;
            var qaPassed = (Number(ord.qa_payment_verified) === 1 && Number(ord.qa_customizations_followed) === 1 && Number(ord.qa_packaging_secured) === 1);
            var qaStatus = qaPassed
              ? '<span style="color: #10B981; font-size: 0.72rem; font-weight: 700;">● Checks Complete</span>'
              : '<span style="color: #7D6F64; font-size: 0.72rem; font-weight: 600;">● Checks Incomplete</span>';

            return `
              <div class="mobile-order-ticket">
                <div class="mobile-ticket-header">
                  <div class="mobile-ticket-queue">
                    <span>#${sanitizeHtml(String(ord.queue_number))}</span>
                    <span style="font-size: 0.75rem; color: #DF9B64; font-weight: 600;">(${diningText})</span>
                  </div>
                  <div class="mobile-ticket-amount">₱${parseFloat(ord.grand_total || 0).toFixed(2)}</div>
                </div>

                <div style="font-size: 0.82rem; color: #FFF; font-weight: 700;">
                  ${sanitizeHtml(ord.customer_name || 'Walk-in')} <span style="font-size: 0.75rem; color: #8E7E73; font-weight: normal;">${sanitizeHtml(ord.customer_phone || '')}</span>
                </div>

                <div class="mobile-ticket-items">
                  ${itemsList}
                </div>

                <div class="mobile-ticket-footer">
                  <div style="display: flex; gap: 0.45rem; align-items: center;">
                    <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: ${ord.payment_method === 'gcash' ? '#60A5FA' : '#DF9B64'};">${(ord.payment_method || '').toUpperCase()}</span>
                    <span style="font-size: 0.75rem; color: #8E7E73;">·</span>
                    ${qaStatus}
                  </div>
                  <div style="display: flex; gap: 0.5rem; align-items: center;">
                    ${statusTag}
                    <button type="button" class="btn-inspect-order" data-order-id="${ord.id}" title="Inspect full ticket details">
                      <span>Inspect</span>
                    </button>
                  </div>
                </div>
              </div>
            `;
          }).join('');
        }

        // Attach Inspect click listeners to all buttons
        document.querySelectorAll('.btn-inspect-order').forEach(function(btn) {
          btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var orderId = parseInt(btn.getAttribute('data-order-id'), 10);
            var ord = rawLedgerOrders.find(function(o) { return parseInt(o.id, 10) === orderId; });
            if (ord) {
              openOrderDetailModal(ord);
            }
          });
        });
      }

      function openOrderDetailModal(ord) {
        var modal = document.getElementById('orderDetailModal');
        if (!modal) return;

        var qEl = document.getElementById('modalOrderQueue');
        if (qEl) qEl.textContent = ord.queue_number || '--';

        var refEl = document.getElementById('modalOrderRef');
        if (refEl) refEl.textContent = ord.order_reference || '--';

        var timeEl = document.getElementById('modalOrderTimestamp');
        if (timeEl) {
          timeEl.textContent = ord.created_at ? new Date(ord.created_at.replace(' ', 'T')).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true }) : '--';
        }

        var statusBadge = document.getElementById('modalOrderStatusBadge');
        if (statusBadge) {
          statusBadge.className = 'order-status-tag';
          if (ord.status === 'completed') {
            statusBadge.classList.add('status-finished');
            statusBadge.textContent = 'Finished';
          } else if (ord.status === 'in_progress') {
            statusBadge.classList.add('status-progress');
            statusBadge.textContent = 'In Progress';
          } else if (ord.status === 'cancelled') {
            statusBadge.classList.add('status-cancelled');
            statusBadge.textContent = 'Cancelled';
          } else {
            statusBadge.classList.add('status-waiting');
            statusBadge.textContent = 'Waiting';
          }
        }

        var diningBadge = document.getElementById('modalOrderDiningBadge');
        if (diningBadge) {
          diningBadge.className = 'dining-badge';
          if (ord.order_type === 'take_out') {
            diningBadge.classList.add('badge-takeout');
            diningBadge.textContent = 'Take-Out';
          } else {
            diningBadge.classList.add('badge-dine-in');
            diningBadge.textContent = `Table ${ord.table_number || '1'}`;
          }
        }

        var paymentBadge = document.getElementById('modalOrderPaymentBadge');
        if (paymentBadge) {
          paymentBadge.className = 'payment-badge';
          if (ord.payment_method === 'gcash') {
            paymentBadge.classList.add('badge-gcash');
            paymentBadge.textContent = 'GCASH';
          } else {
            paymentBadge.classList.add('badge-cash');
            paymentBadge.textContent = 'CASH';
          }
        }

        var custEl = document.getElementById('modalOrderCustomer');
        if (custEl) custEl.textContent = ord.customer_name || 'Walk-in Customer';

        var phoneEl = document.getElementById('modalOrderPhone');
        if (phoneEl) phoneEl.textContent = ord.customer_phone ? `Phone: ${ord.customer_phone}` : 'No phone recorded';

        var prepEl = document.getElementById('modalOrderPrepTime');
        if (prepEl) {
          if (ord.completed_at) {
            prepEl.textContent = `Completed in ${Math.round((ord.elapsed_seconds || 0) / 60)} min`;
            prepEl.style.color = '#34D399';
          } else if (ord.status === 'cancelled') {
            prepEl.textContent = 'Order Cancelled';
            prepEl.style.color = '#FCA5A5';
          } else {
            prepEl.textContent = 'In Kitchen Preparation';
            prepEl.style.color = '#FBBF24';
          }
        }

        var qaBox = document.getElementById('modalOrderQaBadges');
        if (qaBox) {
          var pOk = Number(ord.qa_payment_verified) === 1;
          var rOk = Number(ord.qa_customizations_followed) === 1;
          var kOk = Number(ord.qa_packaging_secured) === 1;
          qaBox.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:0.25rem; margin-top:0.35rem; font-size:0.75rem; font-family:var(--font-mono);">
              <span style="color:${pOk ? '#34D399' : '#8E7E73'};">${pOk ? '✓' : '○'} Payment Verification (${pOk ? 'Passed' : 'Pending'})</span>
              <span style="color:${rOk ? '#34D399' : '#8E7E73'};">${rOk ? '✓' : '○'} Custom Recipe & Temperature (${rOk ? 'Followed' : 'Pending'})</span>
              <span style="color:${kOk ? '#34D399' : '#8E7E73'};">${kOk ? '✓' : '○'} Packaging & Eco-Seal (${kOk ? 'Secured' : 'Pending'})</span>
            </div>
          `;
        }

        // Items list
        var itemsContainer = document.getElementById('modalOrderItemsList');
        if (itemsContainer) {
          var items = ord.items || [];
          if (items.length === 0) {
            itemsContainer.innerHTML = '<div style="color:var(--admin-muted); font-size:0.82rem; padding:0.5rem 0;">No items found.</div>';
          } else {
            itemsContainer.innerHTML = items.map(function(it) {
              var customDetails = [];
              if (it.temperature) customDetails.push(it.temperature);
              if (it.milk_option && it.milk_option !== 'Standard') customDetails.push(`Milk: ${it.milk_option}`);
              if (it.sweetness_level && it.sweetness_level !== '100%') customDetails.push(`Sweetness: ${it.sweetness_level}`);
              if (it.custom_notes) customDetails.push(`Notes: "${sanitizeHtml(it.custom_notes)}"`);

              return `
                <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:5px; padding:0.6rem 0.75rem; display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                  <div>
                    <div style="font-weight:600; color:#FFF; font-size:0.85rem;">
                      ${it.quantity}× ${sanitizeHtml(it.item_name)}
                      <span style="font-family:var(--font-mono); font-size:0.75rem; color:#8E7E73; margin-left:0.35rem;">@ ₱${parseFloat(it.unit_price || 0).toFixed(2)}</span>
                    </div>
                    ${customDetails.length > 0 ? `<div style="font-size:0.75rem; color:#A8988C; margin-top:0.25rem;">${customDetails.join(' · ')}</div>` : ''}
                  </div>
                  <div style="font-family:var(--font-mono); font-weight:700; color:#FFF; font-size:0.85rem; white-space:nowrap;">
                    ₱${parseFloat(it.subtotal || 0).toFixed(2)}
                  </div>
                </div>
              `;
            }).join('');
          }
        }

        // Subtotal, Eco Fee, Grand Total
        var subtotalEl = document.getElementById('modalOrderSubtotal');
        if (subtotalEl) subtotalEl.textContent = `₱${parseFloat(ord.subtotal || 0).toFixed(2)}`;

        var ecoEl = document.getElementById('modalOrderEcoFee');
        if (ecoEl) ecoEl.textContent = `₱${parseFloat(ord.eco_fee || 0).toFixed(2)}`;

        var grandEl = document.getElementById('modalOrderGrandTotal');
        if (grandEl) grandEl.textContent = `₱${parseFloat(ord.grand_total || 0).toFixed(2)}`;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      function closeOrderDetailModal() {
        var modal = document.getElementById('orderDetailModal');
        if (modal) {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }

      document.getElementById('btnCloseOrderDetailModal')?.addEventListener('click', closeOrderDetailModal);
      document.getElementById('btnCloseOrderDetailModalBtn')?.addEventListener('click', closeOrderDetailModal);
      document.getElementById('orderDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeOrderDetailModal();
      });

      // ==========================================
      // 3. MENU & STOCK CONTROL CONTROLLER
      // ==========================================
      var menuData = [];
      var stockOptions = [];
      var stockAvailability = {};
      var itemAvailability = {};
      var activeEditingItem = null;
      var currentCategoryFilter = 'all';

      async function loadStockStatus() {
        try {
          var res = await fetch('api/stock.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            stockOptions = data.options || [];
            stockAvailability = data.availability || {};
            itemAvailability = data.item_availability || {};
          }
        } catch (e) {
          console.warn('Stock fetch error:', e);
        }
      }

      async function loadStockMenu() {
        await loadStockStatus();
        try {
          var res = await fetch('api/menu.php', { cache: 'no-store' });
          if (res.ok) {
            var data = await res.json();
            menuData = data.items || [];
          }
        } catch (e) {
          console.warn('Menu fetch error:', e);
        }

        renderStockCatalog();
      }

      function renderStockCatalog() {
        var grid = document.getElementById('drinksCatalogGrid');
        var query = (document.getElementById('drinkSearchInput').value || '').toLowerCase().trim();

        var filtered = menuData.filter(function(item) {
          var matchesCat = (currentCategoryFilter === 'all' || item.category === currentCategoryFilter);
          var matchesQuery = (!query || item.name.toLowerCase().includes(query) || (item.description || '').toLowerCase().includes(query));
          return matchesCat && matchesQuery;
        });

        if (filtered.length === 0) {
          grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--admin-muted);">No drinks match the selected filter.</div>';
          return;
        }

        grid.innerHTML = filtered.map(function(item) {
          var isItemInStock = (itemAvailability[item.id] !== false && item.isAvailable !== false);
          var cardClass = isItemInStock ? 'drink-card' : 'drink-card item-sold-out';

          return `
            <div class="${cardClass}" id="card_${item.id}">
              <div class="drink-img-wrap">
                <img src="${item.image || 'images/menu/hc-spanish.webp'}" alt="${item.name}" loading="lazy">
              </div>
              <div class="drink-meta-row">
                <h4 class="drink-name">${item.name}</h4>
                <span class="drink-price">₱${parseFloat(item.price).toFixed(2)}</span>
              </div>
              <p class="drink-desc">${item.description || 'Specialty handcrafted cafe beverage.'}</p>
              <div class="drink-card-footer">
                <button type="button" class="drink-status-indicator ${isItemInStock ? 'in-stock' : 'sold-out'}" data-item-id="${item.id}" title="Click to toggle drink availability">
                  <span class="status-dot-sm"></span>
                  <span>${isItemInStock ? 'Available' : 'Sold Out'}</span>
                </button>
                <button type="button" class="btn-manage-options btn-open-manage" data-item-id="${item.id}">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                  <span>Edit Drink</span>
                </button>
              </div>
            </div>
          `;
        }).join('');

        // Attach Manage Customization Clicks
        document.querySelectorAll('.btn-open-manage').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var itemId = btn.getAttribute('data-item-id');
            openManageModal(itemId);
          });
        });

        // Quick drink stock status toggles
        document.querySelectorAll('.drink-status-indicator').forEach(function(tag) {
          tag.addEventListener('click', function(e) {
            e.stopPropagation();
            var itemId = tag.getAttribute('data-item-id');
            quickToggleDrinkStock(itemId);
          });
        });
      }

      // Filter Categories
      document.querySelectorAll('.cat-pill-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.cat-pill-btn').forEach(function(b) { b.classList.remove('active'); });
          btn.classList.add('active');
          currentCategoryFilter = btn.getAttribute('data-cat');
          renderStockCatalog();
        });
      });

      // Search input filter
      document.getElementById('drinkSearchInput').addEventListener('input', function() {
        renderStockCatalog();
      });

      // Quick Drink Stock Toggle from Card
      async function quickToggleDrinkStock(itemId) {
        var current = (itemAvailability[itemId] !== false);
        var next = !current;
        itemAvailability[itemId] = next;
        renderStockCatalog();

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, is_available: next ? 1 : 0 })
          });
          var data = await res.json();
          if (data.success) {
            showToast(next ? 'Drink marked AVAILABLE.' : 'Drink marked SOLD OUT.', !next);
          }
        } catch (e) {
          console.warn('Quick toggle error:', e);
        }
      }

      // ==========================================
      // 4. THE "MANAGE DRINK OPTIONS" MODAL
      // ==========================================
      var manageModal = document.getElementById('adminManageModal');

      async function openManageModal(itemId) {
        await loadStockStatus();
        activeEditingItem = menuData.find(function(i) { return i.id === itemId; });
        if (!activeEditingItem) return;

        var imgUrl = activeEditingItem.image || 'images/menu/hc-spanish.webp';
        document.getElementById('modalDrinkImg').src = imgUrl;
        document.getElementById('modalDrinkImgUrl').value = activeEditingItem.image || '';
        document.getElementById('modalDrinkName').value = activeEditingItem.name;
        document.getElementById('modalDrinkCategory').value = activeEditingItem.category || 'house-coffee';
        document.getElementById('modalBasePrice').value = parseFloat(activeEditingItem.price).toFixed(2);
        document.getElementById('modalDrinkDesc').value = activeEditingItem.description || '';

        // Reset and hide new add-on form
        var newAddonWrap = document.getElementById('newAddonFormWrap');
        if (newAddonWrap) {
          newAddonWrap.style.display = 'none';
          document.getElementById('inputNewAddonName').value = '';
          document.getElementById('inputNewAddonPrice').value = '30';
        }

        updateModalDrinkStockButton();
        renderModalAddons();
        syncOptionPillsInModal();
        manageModal.classList.add('active');
      }

      function updateModalDrinkStockButton() {
        if (!activeEditingItem) return;
        var isAvail = (itemAvailability[activeEditingItem.id] !== false);
        var btn = document.getElementById('modalDrinkStockToggle');
        if (!btn) return;
        btn.className = 'admin-drink-status-btn ' + (isAvail ? 'status-btn-available' : 'status-btn-soldout');
        btn.innerHTML = `<span class="status-dot-sm" style="margin-right: 0.4rem; background: ${isAvail ? '#34D399' : '#F87171'};"></span>` + (isAvail ? 'Available' : 'Sold Out');
      }

      // Toggle modal drink stock button
      document.getElementById('modalDrinkStockToggle').addEventListener('click', async function() {
        if (!activeEditingItem) return;
        var current = (itemAvailability[activeEditingItem.id] !== false);
        var next = !current;
        itemAvailability[activeEditingItem.id] = next;
        updateModalDrinkStockButton();
        renderStockCatalog();

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: activeEditingItem.id, is_available: next ? 1 : 0 })
          });
          var data = await res.json();
          if (data.success) {
            showToast(next ? 'Drink marked AVAILABLE.' : 'Drink marked SOLD OUT.', !next);
          }
        } catch (e) {
          console.warn('Drink stock toggle error:', e);
        }
      });

      // Photo Changer A: Local Device File Upload
      var modalFileInput = document.getElementById('modalDrinkFileInput');
      if (modalFileInput) {
        modalFileInput.addEventListener('change', async function() {
          var file = modalFileInput.files[0];
          if (!file) return;

          var formData = new FormData();
          formData.append('image', file);

          showToast('Uploading new photo...', false);
          try {
            var res = await fetch('api/upload.php', {
              method: 'POST',
              body: formData
            });
            var data = await res.json();
            if (data.success && data.image_url) {
              document.getElementById('modalDrinkImg').src = data.image_url;
              document.getElementById('modalDrinkImgUrl').value = data.image_url;
              showToast('Photo uploaded successfully! Tap "Save Drink Details" to persist.', false);
            } else {
              showToast('Upload error: ' + (data.error || 'Failed'), true);
            }
          } catch (e) {
            showToast('Upload network error: ' + e.message, true);
          }
        });
      }

      // Photo Changer B: Live Image URL Input Preview
      var modalUrlInput = document.getElementById('modalDrinkImgUrl');
      if (modalUrlInput) {
        modalUrlInput.addEventListener('input', function() {
          var val = modalUrlInput.value.trim();
          document.getElementById('modalDrinkImg').src = val || 'images/menu/hc-spanish.webp';
        });
      }

      // Render Dynamic Add-ons List with Delete Control
      function renderModalAddons() {
        var container = document.getElementById('modalAddonsGroup');
        if (!container) return;

        var addons = stockOptions.filter(function(o) { return o.category_type === 'addon'; });
        if (addons.length === 0) {
          container.innerHTML = '<div style="color: var(--admin-muted); font-size: 0.8rem; padding: 0.5rem 0;">No extras created yet. Click "+ Add Extra" above to add one.</div>';
          return;
        }

        container.innerHTML = addons.map(function(opt) {
          var isAvail = (stockAvailability[opt.option_key] === true);
          return `
            <div class="admin-option-pill ${isAvail ? 'is-in-stock' : 'is-sold-out'}" data-opt="${opt.option_key}">
              <span class="option-title-text">${opt.option_label}</span>
              <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span class="status-indicator-tag ${isAvail ? 'tag-in-stock' : 'tag-sold-out'}">${isAvail ? 'In Stock' : 'Sold Out'}</span>
                <button type="button" class="btn-delete-addon" data-opt="${opt.option_key}" title="Delete option" aria-label="Delete option">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
              </div>
            </div>
          `;
        }).join('');

        // Pill Stock Toggle Clicks
        container.querySelectorAll('.admin-option-pill').forEach(function(pill) {
          pill.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-addon')) return;
            var optKey = pill.getAttribute('data-opt');
            toggleOptionStock(optKey);
          });
        });

        // Pill Delete Clicks
        container.querySelectorAll('.btn-delete-addon').forEach(function(btn) {
          btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            var optKey = btn.getAttribute('data-opt');
            var confirmed = await SystemDialog.confirm(`Are you sure you want to permanently delete "${optKey}"?`, {
              title: 'Delete Option',
              confirmText: 'Delete Option',
              isDestructive: true
            });
            if (!confirmed) return;

            try {
              var res = await fetch('api/stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_option', option_key: optKey })
              });
              var data = await res.json();
              if (data.success) {
                showToast(`Option "${optKey}" deleted.`, false);
                await loadStockStatus();
                renderModalAddons();
              } else {
                showToast('Delete error: ' + (data.error || 'Failed'), true);
              }
            } catch (err) {
              showToast('Delete error: ' + err.message, true);
            }
          });
        });
      }

      // Add-on Form Toggle & Submit Handlers
      var newAddonWrap = document.getElementById('newAddonFormWrap');
      document.getElementById('btnShowAddAddonForm').addEventListener('click', function() {
        var isHidden = (newAddonWrap.style.display === 'none' || !newAddonWrap.style.display);
        newAddonWrap.style.display = isHidden ? 'block' : 'none';
        if (isHidden) {
          document.getElementById('inputNewAddonName').focus();
        }
      });

      document.getElementById('btnCancelNewAddon').addEventListener('click', function() {
        newAddonWrap.style.display = 'none';
        document.getElementById('inputNewAddonName').value = '';
      });

      document.getElementById('btnSubmitNewAddon').addEventListener('click', async function() {
        var name = document.getElementById('inputNewAddonName').value.trim();
        var price = parseFloat(document.getElementById('inputNewAddonPrice').value) || 0;

        if (!name) {
          await SystemDialog.alert('Please enter an option name (e.g. Cinnamon Syrup, Caramel Drizzle).', { title: 'Option Name Required', type: 'warning' });
          return;
        }

        var btn = document.getElementById('btnSubmitNewAddon');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'add_option',
              category_type: 'addon',
              option_key: name,
              surcharge: price
            })
          });
          var data = await res.json();
          if (data.success) {
            showToast(`Extra "${name}" (+₱${price}) created successfully.`, false);
            document.getElementById('inputNewAddonName').value = '';
            document.getElementById('inputNewAddonPrice').value = '30';
            newAddonWrap.style.display = 'none';
            await loadStockStatus();
            renderModalAddons();
          } else {
            showToast('Error: ' + (data.error || 'Failed to add option'), true);
          }
        } catch (e) {
          showToast('Connection error: ' + e.message, true);
        } finally {
          btn.disabled = false;
          btn.textContent = 'Save Extra';
        }
      });

      // General Option Stock Toggle (Individual Pill Click)
      async function toggleOptionStock(optKey) {
        var current = (stockAvailability[optKey] === true);
        var next = !current;

        stockAvailability[optKey] = next;

        // Synchronize in-memory stockOptions array item
        var optObj = stockOptions.find(function(o) { return o.option_key === optKey; });
        if (optObj) {
          optObj.is_available = next ? 1 : 0;
        }

        syncOptionPillsInModal();
        renderModalAddons();

        try {
          var res = await fetch('api/stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ option_key: optKey, is_available: next ? 1 : 0 })
          });
          var data = await res.json();
          if (data.success) {
            var msg = next 
              ? `"${optKey}" is now IN STOCK.` 
              : `"${optKey}" marked as SOLD OUT.`;
            showToast(msg, !next);
          }
        } catch (e) {
          console.warn('Option toggle error:', e);
        }
      }

      // Sync Option Pills inside modal (Temperature & Sweetness)
      function syncOptionPillsInModal() {
        document.querySelectorAll('#modalTempGroup .admin-option-pill, #modalSweetnessGroup .admin-option-pill').forEach(function(pill) {
          var optKey = pill.getAttribute('data-opt');
          var isAvailable = (stockAvailability[optKey] === true);

          pill.classList.toggle('is-in-stock', isAvailable);
          pill.classList.toggle('is-sold-out', !isAvailable);

          var tag = pill.querySelector('.status-indicator-tag');
          if (tag) {
            tag.className = isAvailable ? 'status-indicator-tag tag-in-stock' : 'status-indicator-tag tag-sold-out';
            tag.textContent = isAvailable ? 'In Stock' : 'Sold Out';
          }
        });
      }

      // Category Batch Stock Toggles (Temperature, Add-ons, Sweetness)
      async function toggleCategoryAllStock(categoryType) {
        var keysToToggle = [];
        if (categoryType === 'temperature') {
          keysToToggle = ['Iced', 'Hot'];
        } else if (categoryType === 'addon') {
          keysToToggle = stockOptions
            .filter(function(o) { return o.category_type === 'addon'; })
            .map(function(o) { return o.option_key; });
        } else if (categoryType === 'sweetness') {
          keysToToggle = ['Normal (100%)', 'Less Sweet (75%)', 'Half Sweet (50%)', 'No Sugar (0%)'];
        }

        if (keysToToggle.length === 0) return;

        // If any option in this category is currently sold out, mark all as IN STOCK; otherwise mark all as SOLD OUT
        var anySoldOut = keysToToggle.some(function(k) { return stockAvailability[k] !== true; });
        var nextState = anySoldOut ? true : false;

        keysToToggle.forEach(function(k) {
          stockAvailability[k] = nextState;
          var optObj = stockOptions.find(function(o) { return o.option_key === k; });
          if (optObj) optObj.is_available = nextState ? 1 : 0;
        });

        syncOptionPillsInModal();
        renderModalAddons();

        try {
          await Promise.all(keysToToggle.map(function(k) {
            return fetch('api/stock.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ option_key: k, is_available: nextState ? 1 : 0 })
            });
          }));
          var catLabel = categoryType.charAt(0).toUpperCase() + categoryType.slice(1);
          showToast(`${catLabel} options marked ${nextState ? 'ALL IN STOCK' : 'ALL SOLD OUT'}.`, !nextState);
        } catch (e) {
          console.warn('Category batch toggle error:', e);
        }
      }

      // Attach Category Batch Toggle Buttons
      document.querySelectorAll('.btn-cat-toggle-stock').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
          e.stopPropagation();
          var catType = btn.getAttribute('data-cat-type');
          toggleCategoryAllStock(catType);
        });
      });

      // Tap Static Option Pills (Temperature, Sweetness)
      document.querySelectorAll('#modalTempGroup .admin-option-pill, #modalSweetnessGroup .admin-option-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
          var optKey = pill.getAttribute('data-opt');
          toggleOptionStock(optKey);
        });
      });

      // Save Drink Details (Name, Price, Category, Description, and Image URL)
      document.getElementById('btnSaveDrinkChanges').addEventListener('click', async function() {
        if (!activeEditingItem) return;

        var name = document.getElementById('modalDrinkName').value.trim();
        var price = parseFloat(document.getElementById('modalBasePrice').value) || 0;
        var category = document.getElementById('modalDrinkCategory').value;
        var desc = document.getElementById('modalDrinkDesc').value.trim();
        var imageUrl = document.getElementById('modalDrinkImgUrl').value.trim();

        if (!name) {
          await SystemDialog.alert('Please enter a drink name.', { title: 'Name Required', type: 'warning' });
          return;
        }
        if (price <= 0) {
          await SystemDialog.alert('Price must be greater than 0.', { title: 'Invalid Price', type: 'warning' });
          return;
        }

        var saveBtn = document.getElementById('btnSaveDrinkChanges');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
          var res = await fetch('api/menu.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              id: activeEditingItem.id,
              name: name,
              price: price,
              category: category,
              description: desc,
              image_url: imageUrl,
              is_available: itemAvailability[activeEditingItem.id] !== false ? 1 : 0
            })
          });
          var data = await res.json();
          if (data.success) {
            showToast('Drink details & photo saved successfully.', false);
            manageModal.classList.remove('active');
            await loadStockMenu();
          } else {
            showToast('Failed to save: ' + (data.error || 'Unknown error'), true);
          }
        } catch (e) {
          showToast('Save error: ' + e.message, true);
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <span>Save Drink Details</span>
          `;
        }
      });

      // Delete Drink
      document.getElementById('btnDeleteDrink').addEventListener('click', async function() {
        if (!activeEditingItem) return;
        var confirmed = await SystemDialog.confirm(`Are you sure you want to delete "${activeEditingItem.name}" from the menu?`, {
          title: 'Delete Drink',
          confirmText: 'Delete from Menu',
          isDestructive: true
        });
        if (!confirmed) return;

        try {
          var res = await fetch(`api/menu.php?id=${encodeURIComponent(activeEditingItem.id)}`, {
            method: 'DELETE'
          });
          var data = await res.json();
          if (data.success) {
            showToast(`"${activeEditingItem.name}" was removed.`, false);
            manageModal.classList.remove('active');
            await loadStockMenu();
          } else {
            showToast('Delete error: ' + (data.error || 'Failed'), true);
          }
        } catch (e) {
          showToast('Connection error: ' + e.message, true);
        }
      });

      document.getElementById('btnCloseManageModal').addEventListener('click', function() {
        manageModal.classList.remove('active');
      });


      // ==========================================
      // 5. ADD NEW DRINK MODAL
      // ==========================================
      var addModal = document.getElementById('adminAddDrinkModal');
      document.getElementById('btnOpenAddDrinkModal').addEventListener('click', function() {
        addModal.classList.add('active');
      });
      document.getElementById('btnCloseAddModalBtn').addEventListener('click', function() {
        addModal.classList.remove('active');
      });

      document.getElementById('btnSubmitNewDrink').addEventListener('click', async function() {
        var name = document.getElementById('newDrinkName').value.trim();
        var cat = document.getElementById('newDrinkCategory').value;
        var price = parseFloat(document.getElementById('newDrinkPrice').value) || 0;
        var desc = document.getElementById('newDrinkDesc').value.trim();

        if (!name) {
          await SystemDialog.alert('Please enter a drink name.', { title: 'Name Required', type: 'warning' });
          return;
        }

        try {
          var res = await fetch('api/menu.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              name: name,
              category: cat,
              price: price,
              description: desc,
              is_available: 1
            })
          });
          var data = await res.json();
          if (data.success) {
            showToast(`"${name}" created successfully.`, false);
            addModal.classList.remove('active');
            document.getElementById('newDrinkName').value = '';
            document.getElementById('newDrinkDesc').value = '';
            await loadStockMenu();
          } else {
            showToast('Creation failed: ' + (data.error || 'Unknown error'), true);
          }
        } catch (e) {
          showToast('Connection error: ' + e.message, true);
        }
      });

      // Reset All Stock
      document.getElementById('btnResetAllStockTop').addEventListener('click', async function() {
        var confirmed = await SystemDialog.confirm('Are you sure you want to set ALL drinks and options back to IN STOCK?', {
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
            showToast('All drinks and options set to IN STOCK.', false);
            await loadStockMenu();
          }
        } catch (e) {
          showToast('Reset failed: ' + e.message, true);
        }
      });

      // Initial View Load (Analytics Ledger by default)
      loadLedgerData();

    })();
  </script>
</body>
</html>
