<?php
/**
 * BeCoffee — Physical Table QR Stickers & Acrylic Stand Generator
 * Restricted exclusively to Admin and SuperAdmin roles.
 * Design standard: .agents/rules/frontend.md & .agents/rules/UI_Always.md
 */

require_once __DIR__ . '/api/config.php';
$currentUser = requireRole(['admin', 'superadmin']);

$tableCount = (int) getSystemSetting('table_count', '10');
$qrEnabled  = getSystemSetting('table_qr_ordering_enabled', '1') === '1';

// Auto-detect best base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
$defaultBaseUrl = "{$protocol}://{$host}{$scriptDir}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>BeCoffee | Table QR Stickers Generator</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/system-dialog.css">
  
  <style>
    :root {
      --bg: #0E0A08;
      --surface: #140E0C;
      --surface-elevated: #1B1411;
      --border: rgba(255, 255, 255, 0.08);
      --border-subtle: rgba(255, 255, 255, 0.05);
      --accent: #E28743;
      --accent-hover: #EA9655;
      --accent-deep: #944D1C;
      --text: #F5EDE4;
      --muted: #8E7E73;
      --green: #10B981;
      --red: #EF4444;
      --font-sans: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-mono: 'JetBrains Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-sans);
      min-height: 100vh;
      padding-bottom: 3rem;
    }

    /* Top Studio Controls Toolbar (Screen Only) */
    .studio-header {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 0.65rem 1.25rem;
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.65rem;
    }

    .studio-header-right {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-left: auto;
    }

    /* Toolbar Inputs */
    .toolbar-control-group {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 0.3rem 0.65rem;
      font-size: 0.8rem;
      min-height: 36px;
      transition: border-color 0.15s ease;
    }

    .toolbar-control-group:focus-within {
      border-color: rgba(223, 155, 100, 0.4);
    }

    .toolbar-control-group label {
      color: var(--muted);
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      user-select: none;
      white-space: nowrap;
    }

    .toolbar-control-group input,
    .toolbar-control-group select {
      background: transparent;
      border: none;
      color: #FFF;
      font-size: 0.8rem;
      font-family: var(--font-mono);
      outline: none;
      cursor: pointer;
    }

    .toolbar-control-group input[type="text"] {
      cursor: text;
      min-width: 200px;
    }

    .toolbar-control-group select option {
      background: #191411;
      color: #FFF;
    }

    .btn-icon-tiny {
      background: transparent;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 0.15rem;
      border-radius: 3px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: color 0.15s ease;
    }

    .btn-icon-tiny:hover {
      color: #FFF;
    }

    /* Style Switcher */
    .style-switch-group {
      display: flex;
      align-items: center;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 2px;
      gap: 2px;
      min-height: 36px;
    }

    .style-switch-btn {
      padding: 0.35rem 0.75rem;
      border-radius: 3px;
      font-size: 0.76rem;
      font-weight: 600;
      color: var(--muted);
      background: transparent;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
      min-height: 30px;
    }

    .style-switch-btn:hover {
      color: #FFF;
      background: rgba(255, 255, 255, 0.04);
    }

    .style-switch-btn.active {
      background: #251D18;
      border-color: rgba(255, 255, 255, 0.12);
      color: #FFF;
      font-weight: 700;
    }

    .style-switch-btn:focus-visible,
    .btn-action:focus-visible {
      outline: 2px solid var(--accent);
      outline-offset: 2px;
    }

    /* Buttons */
    .btn-action {
      min-height: 36px;
      padding: 0.4rem 0.85rem;
      border-radius: 4px;
      font-family: var(--font-sans);
      font-weight: 600;
      font-size: 0.8rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.15s ease;
      border: 1px solid transparent;
      white-space: nowrap;
    }

    .btn-action-primary {
      background: #E28743;
      border-color: #E28743;
      color: #110D0B;
      font-weight: 700;
      box-shadow: none;
    }

    .btn-action-primary:hover {
      background: #EA9655;
      border-color: #EA9655;
      color: #110D0B;
    }

    .btn-action-secondary {
      background: var(--bg);
      color: #D6C7BC;
      border-color: var(--border);
    }

    .btn-action-secondary:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.16);
    }

    /* Master QR Toggle Button */
    .btn-qr-toggle {
      background: var(--bg);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.35);
      font-weight: 700;
    }

    .btn-qr-toggle:hover {
      background: rgba(16, 185, 129, 0.08);
      border-color: rgba(16, 185, 129, 0.5);
    }

    .btn-qr-toggle .qr-toggle-icon {
      color: #34D399;
      flex-shrink: 0;
      transition: color 0.15s ease;
    }

    .btn-qr-toggle.is-paused {
      background: var(--bg);
      border-color: rgba(239, 68, 68, 0.35);
      color: #F87171;
    }

    .btn-qr-toggle.is-paused:hover {
      background: rgba(239, 68, 68, 0.08);
      border-color: rgba(239, 68, 68, 0.5);
      color: #F87171;
    }

    .btn-qr-toggle.is-paused .qr-toggle-icon {
      color: #F87171;
    }

    /* Studio Subtitle / Guide Banner */
    .studio-guide-banner {
      background: rgba(255, 255, 255, 0.02);
      border-bottom: 1px solid var(--border-subtle);
      padding: 0.65rem 1.25rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.78rem;
      color: var(--muted);
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .studio-guide-banner strong {
      color: var(--text);
      font-weight: 600;
    }

    .studio-guide-banner span {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    /* Main Container */
    .studio-container {
      max-width: 1240px;
      margin: 1.5rem auto 0;
      padding: 0 1.25rem;
    }

    /* Sticker Sheet Grid */
    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.75rem;
      justify-items: center;
    }

    /* ==========================================================================
       Physical Sticker Card Architecture (Heritage Coffee Roastery Aesthetic)
       ========================================================================== */
    .sticker-card {
      width: 275px;
      border-radius: 6px;
      padding: 1.4rem 1.2rem 1.15rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      page-break-inside: avoid;
      break-inside: avoid;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-sizing: border-box;
    }

    .sticker-card:hover {
      transform: translateY(-2px);
    }

    /* Corner Crop Marks for Precision Cutting */
    .crop-mark {
      position: absolute;
      width: 8px;
      height: 8px;
      pointer-events: none;
      opacity: 0.4;
    }
    .crop-mark-tl { top: 6px; left: 6px; border-top: 1px solid currentColor; border-left: 1px solid currentColor; }
    .crop-mark-tr { top: 6px; right: 6px; border-top: 1px solid currentColor; border-right: 1px solid currentColor; }
    .crop-mark-bl { bottom: 6px; left: 6px; border-bottom: 1px solid currentColor; border-left: 1px solid currentColor; }
    .crop-mark-br { bottom: 6px; right: 6px; border-bottom: 1px solid currentColor; border-right: 1px solid currentColor; }

    /* Theme 1: Warm Crema Acrylic (Default Physical Print) */
    .sticker-card.theme-acrylic {
      background: #FAF7F2;
      color: #17120F;
      border: 1px solid #D5C9BC;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
      outline: 1px solid rgba(140, 106, 84, 0.22);
      outline-offset: -5px;
    }

    .sticker-card.theme-acrylic .sticker-brand-name { color: #17120F; }
    .sticker-card.theme-acrylic .sticker-brand-tag { color: #8C6A54; }
    .sticker-card.theme-acrylic .sticker-table-kicker { color: #8C6A54; }
    .sticker-card.theme-acrylic .sticker-table-number { color: #17120F; }
    .sticker-card.theme-acrylic .sticker-table-rule { background: #8C6A54; opacity: 0.35; }
    .sticker-card.theme-acrylic .sticker-qr-container {
      background: #FFFFFF;
      border: 1px solid #E2D9CF;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }
    .sticker-card.theme-acrylic .scan-bracket { color: #8C6A54; }
    .sticker-card.theme-acrylic .sticker-instruction-primary { color: #17120F; }
    .sticker-card.theme-acrylic .sticker-instruction-sub { color: #736861; }
    .sticker-card.theme-acrylic .sticker-url-pill {
      background: #EFE9E1;
      color: #4A3A31;
      border: 1px solid #DDD3C8;
    }
    .sticker-card.theme-acrylic .sticker-footer-note { color: #8C7B70; }
    .sticker-card.theme-acrylic .sticker-card-actions {
      border-top: 1px solid #E5DDD3;
    }
    .sticker-card.theme-acrylic .sticker-action-btn {
      background: #EFE9E1;
      color: #4A3A31;
      border: 1px solid #DCD2C7;
    }
    .sticker-card.theme-acrylic .sticker-action-btn:hover {
      background: #E5DDD3;
      color: #17120F;
    }

    /* Theme 2: Dark Espresso Tent */
    .sticker-card.theme-dark {
      background: #140E0C;
      color: #F5EBE1;
      border: 1px solid #33251E;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
      outline: 1px solid rgba(226, 135, 67, 0.2);
      outline-offset: -5px;
    }

    .sticker-card.theme-dark .sticker-brand-name { color: #FFFFFF; }
    .sticker-card.theme-dark .sticker-brand-tag { color: #DF9B64; }
    .sticker-card.theme-dark .sticker-table-kicker { color: #DF9B64; }
    .sticker-card.theme-dark .sticker-table-number { color: #FAF7F2; }
    .sticker-card.theme-dark .sticker-table-rule { background: #DF9B64; opacity: 0.35; }
    .sticker-card.theme-dark .sticker-qr-container {
      background: #FFFFFF;
      border: 1px solid #DF9B64;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    }
    .sticker-card.theme-dark .scan-bracket { color: #DF9B64; }
    .sticker-card.theme-dark .sticker-instruction-primary { color: #FAF7F2; }
    .sticker-card.theme-dark .sticker-instruction-sub { color: #A99B92; }
    .sticker-card.theme-dark .sticker-url-pill {
      background: #201713;
      color: #DF9B64;
      border: 1px solid #38271E;
    }
    .sticker-card.theme-dark .sticker-footer-note { color: #8C7B70; }
    .sticker-card.theme-dark .sticker-card-actions {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .sticker-card.theme-dark .sticker-action-btn {
      background: #1C1512;
      color: #D6C7BC;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .sticker-card.theme-dark .sticker-action-btn:hover {
      background: #281E19;
      color: #FFF;
    }

    /* Theme 3: Clean Monochrome (Laser & Thermal Friendly) */
    .sticker-card.theme-mono {
      background: #FFFFFF;
      color: #111111;
      border: 1.5px solid #111111;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      outline: 1px solid #CCCCCC;
      outline-offset: -5px;
    }

    .sticker-card.theme-mono .sticker-brand-name { color: #000000; }
    .sticker-card.theme-mono .sticker-brand-tag { color: #444444; }
    .sticker-card.theme-mono .sticker-table-kicker { color: #444444; }
    .sticker-card.theme-mono .sticker-table-number { color: #000000; }
    .sticker-card.theme-mono .sticker-table-rule { background: #000000; opacity: 0.4; }
    .sticker-card.theme-mono .sticker-qr-container {
      background: #FFFFFF;
      border: 1px solid #000000;
      box-shadow: none;
    }
    .sticker-card.theme-mono .scan-bracket { color: #000000; }
    .sticker-card.theme-mono .sticker-instruction-primary { color: #000000; }
    .sticker-card.theme-mono .sticker-instruction-sub { color: #444444; }
    .sticker-card.theme-mono .sticker-url-pill {
      background: #F3F4F6;
      color: #111111;
      border: 1px solid #D1D5DB;
    }
    .sticker-card.theme-mono .sticker-footer-note { color: #555555; }
    .sticker-card.theme-mono .sticker-card-actions {
      border-top: 1px solid #E5E7EB;
    }
    .sticker-card.theme-mono .sticker-action-btn {
      background: #F3F4F6;
      color: #111111;
      border: 1px solid #D1D5DB;
    }
    .sticker-card.theme-mono .sticker-action-btn:hover {
      background: #E5E7EB;
    }

    /* Card Elements Typography & Geometry */
    .sticker-brand-header {
      margin-bottom: 0.35rem;
    }

    .sticker-brand-icon {
      margin-bottom: 0.2rem;
      display: inline-block;
      opacity: 0.9;
    }

    .sticker-brand-name {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      line-height: 1.1;
    }

    .sticker-brand-tag {
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      display: block;
      margin-top: 0.15rem;
      font-family: var(--font-mono);
    }

    /* Table Numeral Callout */
    .sticker-table-box {
      margin: 0.45rem 0 0.65rem;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sticker-table-kicker {
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      font-family: var(--font-mono);
    }

    .sticker-table-number {
      font-family: var(--font-serif);
      font-size: 2.75rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: -0.03em;
      margin-top: 0.1rem;
    }

    .sticker-table-rule {
      width: 32px;
      height: 1px;
      margin-top: 0.35rem;
      border-radius: 1px;
    }

    /* QR Code Scan Zone */
    .sticker-qr-zone {
      position: relative;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sticker-qr-container {
      padding: 0.55rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 166px;
      height: 166px;
      box-sizing: border-box;
    }

    .sticker-qr-container img,
    .sticker-qr-container canvas {
      display: block;
      width: 150px !important;
      height: 150px !important;
    }

    /* Viewfinder Optical Scan Brackets */
    .scan-bracket {
      position: absolute;
      width: 10px;
      height: 10px;
      pointer-events: none;
    }
    .bracket-tl { top: -3px; left: -3px; border-top: 1.5px solid currentColor; border-left: 1.5px solid currentColor; border-top-left-radius: 2px; }
    .bracket-tr { top: -3px; right: -3px; border-top: 1.5px solid currentColor; border-right: 1.5px solid currentColor; border-top-right-radius: 2px; }
    .bracket-bl { bottom: -3px; left: -3px; border-bottom: 1.5px solid currentColor; border-left: 1.5px solid currentColor; border-bottom-left-radius: 2px; }
    .bracket-br { bottom: -3px; right: -3px; border-bottom: 1.5px solid currentColor; border-right: 1.5px solid currentColor; border-bottom-right-radius: 2px; }

    /* Scan Instructions */
    .sticker-instruction-primary {
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: -0.01em;
      margin-bottom: 0.15rem;
    }

    .sticker-instruction-sub {
      font-size: 0.7rem;
      font-weight: 400;
      margin-bottom: 0.55rem;
    }

    /* Monospace URL Fallback */
    .sticker-url-pill {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      font-weight: 500;
      padding: 0.25rem 0.55rem;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      max-width: 100%;
      min-width: 0;
      box-sizing: border-box;
      overflow: hidden;
      margin-bottom: 0.55rem;
    }

    .sticker-url-pill span {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      min-width: 0;
      display: block;
    }

    .sticker-url-pill svg {
      flex-shrink: 0;
      opacity: 0.7;
    }

    .sticker-footer-note {
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }

    .sticker-gcash-block {
      margin-top: 0.45rem;
      margin-bottom: 0.5rem;
      padding: 0.4rem 0.55rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px dashed rgba(223, 155, 100, 0.4);
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 0.55rem;
      text-align: left;
      width: 100%;
      box-sizing: border-box;
    }
    .sticker-gcash-qr-mini {
      width: 44px;
      height: 44px;
      border-radius: 4px;
      display: block;
      flex-shrink: 0;
      background: #FFF;
      padding: 2px;
      object-fit: contain;
    }
    .sticker-gcash-info {
      display: flex;
      flex-direction: column;
      gap: 1px;
      min-width: 0;
    }
    .sticker-gcash-kicker {
      font-size: 0.6rem;
      font-weight: 800;
      color: #E28743;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      line-height: 1.1;
    }
    .sticker-gcash-title {
      font-size: 0.68rem;
      font-weight: 700;
      color: #F5EDE4;
      line-height: 1.15;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .sticker-gcash-sub {
      font-size: 0.58rem;
      color: var(--muted);
      line-height: 1.2;
    }

    /* Card Interactive Action Buttons (Screen Only) */
    .sticker-card-actions {
      display: flex;
      gap: 0.4rem;
      width: 100%;
      margin-top: 0.75rem;
      padding-top: 0.65rem;
      justify-content: center;
    }

    .sticker-action-btn {
      font-size: 0.7rem;
      font-weight: 600;
      padding: 0.25rem 0.55rem;
      border-radius: 3px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    /* Studio Toast Notification */
    .studio-toast {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      background: #1B1411;
      border: 1px solid rgba(226, 135, 67, 0.4);
      border-radius: 6px;
      padding: 0.65rem 1rem;
      color: #FFF;
      font-size: 0.8rem;
      font-weight: 600;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
      display: none;
      align-items: center;
      gap: 0.45rem;
      z-index: 1000;
    }

    /* ==========================================================================
       Print Optimization Engine (@media print)
       ========================================================================== */
    @media print {
      @page {
        size: portrait;
        margin: 8mm;
      }

      body {
        background: #FFFFFF !important;
        background-image: none !important;
        color: #000000 !important;
        padding: 0 !important;
      }

      .studio-header,
      .studio-guide-banner,
      .studio-toast,
      .sticker-card-actions {
        display: none !important;
      }

      .studio-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      .sticker-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 5mm !important;
        padding: 0 !important;
      }

      .sticker-card {
        width: 100% !important;
        max-width: 65mm !important;
        height: auto !important;
        background: #FFFFFF !important;
        color: #000000 !important;
        border: 1.5pt solid #000000 !important;
        outline: 0.5pt solid #555555 !important;
        outline-offset: -4pt !important;
        box-shadow: none !important;
        transform: none !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        padding: 4mm 3mm !important;
      }

      .sticker-card .crop-mark {
        opacity: 0.8 !important;
        color: #666666 !important;
      }

      .sticker-card .sticker-brand-name { color: #000000 !important; }
      .sticker-card .sticker-brand-tag { color: #444444 !important; }
      .sticker-card .sticker-table-kicker { color: #444444 !important; }
      .sticker-card .sticker-table-number { color: #000000 !important; }
      .sticker-card .sticker-table-rule { background: #000000 !important; }

      .sticker-card .sticker-qr-container {
        border: 1pt solid #CCCCCC !important;
        box-shadow: none !important;
        width: 135px !important;
        height: 135px !important;
        padding: 3px !important;
      }

      .sticker-card .sticker-qr-container img,
      .sticker-card .sticker-qr-container canvas {
        width: 125px !important;
        height: 125px !important;
      }

      .sticker-card .scan-bracket { color: #000000 !important; }
      .sticker-card .sticker-instruction-primary { color: #000000 !important; }
      .sticker-card .sticker-instruction-sub { color: #555555 !important; }
      .sticker-card .sticker-url-pill {
        background: #F3F4F6 !important;
        color: #111111 !important;
        border: 0.5pt solid #CCCCCC !important;
      }
      .sticker-card .sticker-footer-note { color: #666666 !important; }
      .sticker-card .sticker-gcash-block {
        border: 0.75pt dashed #000000 !important;
        background: #F8F9FA !important;
      }
      .sticker-card .sticker-gcash-kicker { color: #000000 !important; }
      .sticker-card .sticker-gcash-title { color: #000000 !important; }
      .sticker-card .sticker-gcash-sub { color: #444444 !important; }
    }

    /* Responsive studio layout */
    @media (max-width: 900px) {
      .toolbar-control-group input[type="text"] {
        min-width: 140px;
      }
    }

    @media (max-width: 768px) {
      .studio-header {
        padding: 0.65rem 0.75rem;
        gap: 0.5rem;
      }
      .btn-qr-toggle {
        width: 100%;
        justify-content: center;
      }
      .toolbar-control-group {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
      }
      .toolbar-control-group input,
      .toolbar-control-group select {
        font-size: 16px;
      }
      .toolbar-control-group input[type="text"] {
        width: 100% !important;
        min-width: 0 !important;
      }
      .studio-header-right {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        margin-left: 0;
      }
      .style-switch-group {
        width: 100%;
        display: flex;
        overflow-x: auto;
      }
      .style-switch-btn {
        flex: 1 1 0;
        justify-content: center;
        text-align: center;
        padding: 0.35rem 0.2rem;
        font-size: 0.72rem;
      }
      .btn-action-primary {
        width: 100%;
        justify-content: center;
      }
      .studio-guide-banner {
        padding: 0.5rem 0.75rem;
        font-size: 0.74rem;
        flex-direction: column;
        align-items: flex-start;
      }
      .studio-container {
        padding: 0 0.5rem;
        margin-top: 1rem;
      }
      .sticker-grid {
        grid-template-columns: 1fr;
      }
      .sticker-card {
        width: min(280px, 100%);
        max-width: 100%;
      }
    }
  </style>

  <!-- QRCode.js Library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <!-- SystemDialog Engine -->
  <script src="js/system-dialog.js"></script>
</head>
<body>

  <!-- Top Studio Controls Toolbar -->
  <header class="studio-header">
    <!-- Master QR Toggle Button -->
    <button type="button" class="btn-action btn-qr-toggle <?= !$qrEnabled ? 'is-paused' : '' ?>" id="btnToggleQRMaster" title="Pause or Resume table QR ordering cafe-wide">
      <span class="qr-icon-holder" id="qrIconHolder">
        <?php if ($qrEnabled): ?>
          <svg class="qr-toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="10" y1="15" x2="10" y2="9"></line><line x1="14" y1="15" x2="14" y2="9"></line></svg>
        <?php else: ?>
          <svg class="qr-toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"></polygon></svg>
        <?php endif; ?>
      </span>
      <span id="btnToggleQRText"><?= $qrEnabled ? 'Pause Table QR' : 'Enable Table QR' ?></span>
    </button>

    <!-- Host Domain Config -->
    <div class="toolbar-control-group">
      <label for="baseUrlInput" title="Host Domain for QR codes">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
        Host:
      </label>
      <input type="text" id="baseUrlInput" value="<?= htmlspecialchars($defaultBaseUrl) ?>" spellcheck="false">
      <button type="button" class="btn-icon-tiny" id="btnResetHost" title="Reset to auto-detected default host">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
      </button>
    </div>

    <!-- Table Count Selector -->
    <div class="toolbar-control-group">
      <label for="tableCountSelect" title="Total cafe tables to generate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
        Tables:
      </label>
      <select id="tableCountSelect">
        <option value="5" <?= $tableCount == 5 ? 'selected' : '' ?>>5 Tables</option>
        <option value="10" <?= $tableCount == 10 ? 'selected' : '' ?>>10 Tables</option>
        <option value="15" <?= $tableCount == 15 ? 'selected' : '' ?>>15 Tables</option>
        <option value="20" <?= $tableCount == 20 ? 'selected' : '' ?>>20 Tables</option>
        <option value="25" <?= $tableCount == 25 ? 'selected' : '' ?>>25 Tables</option>
        <option value="30" <?= $tableCount == 30 ? 'selected' : '' ?>>30 Tables</option>
      </select>
    </div>

    <div class="studio-header-right">
      <!-- Dual GCash Stand Toggle -->
      <button type="button" class="btn-action btn-action-secondary" id="btnToggleGcashStand" title="Include GCash / QR Ph payment QR code on the acrylic stand" style="font-size: 0.76rem; border-color: rgba(223, 155, 100, 0.4); color: #F5A25D;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
        <span id="btnGcashStandText">✓ GCash Pay QR</span>
      </button>

      <!-- Card Style Switcher -->
      <div class="style-switch-group">
        <button type="button" class="style-switch-btn active" data-style="theme-acrylic" id="btnStyleAcrylic">Warm Crema</button>
        <button type="button" class="style-switch-btn" data-style="theme-dark" id="btnStyleDark">Dark Espresso</button>
        <button type="button" class="style-switch-btn" data-style="theme-mono" id="btnStyleMono">Clean Mono</button>
      </div>

      <!-- Print Action -->
      <button type="button" class="btn-action btn-action-primary" onclick="window.print()" title="Print complete sticker sheet (Ctrl + P)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        <span>Print Sheet</span>
      </button>
    </div>
  </header>

  <!-- Studio Subtitle / Guide Banner -->
  <div class="studio-guide-banner">
    <div>
      <span><strong>Physical Table QR Stickers</strong> &middot; Print-ready cards with precision crop marks for table acrylic stands & stickers.</span>
    </div>
    <div>
      <span>Tip: Click <em>Test Order</em> on any card to simulate customer table ordering session</span>
    </div>
  </div>

  <main class="studio-container">
    <!-- Sticker Sheet Grid Container -->
    <div class="sticker-grid" id="stickerGrid">
      <!-- Dynamically generated sticker cards -->
    </div>
  </main>

  <!-- Toast Notification -->
  <div class="studio-toast" id="studioToast">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="studioToastMsg">Notification</span>
  </div>

  <script>
    (function initStickerGenerator() {
      var grid = document.getElementById('stickerGrid');
      var baseUrlInput = document.getElementById('baseUrlInput');
      var tableCountSelect = document.getElementById('tableCountSelect');
      var btnToggleQR = document.getElementById('btnToggleQRMaster');
      var btnToggleQRText = document.getElementById('btnToggleQRText');
      var btnStyleAcrylic = document.getElementById('btnStyleAcrylic');
      var btnStyleDark = document.getElementById('btnStyleDark');
      var btnStyleMono = document.getElementById('btnStyleMono');
      var btnResetHost = document.getElementById('btnResetHost');
      var defaultHostUrl = <?= json_encode($defaultBaseUrl) ?>;

      var isQREnabled = <?= $qrEnabled ? 'true' : 'false' ?>;
      var activeCardTheme = 'theme-acrylic';
      var showGcashStand = true;

      var btnToggleGcash = document.getElementById('btnToggleGcashStand');
      if (btnToggleGcash) {
        btnToggleGcash.addEventListener('click', function() {
          showGcashStand = !showGcashStand;
          var btnText = document.getElementById('btnGcashStandText');
          if (btnText) btnText.textContent = showGcashStand ? '✓ GCash Pay QR' : '+ GCash Pay QR';
          btnToggleGcash.style.borderColor = showGcashStand ? 'rgba(223, 155, 100, 0.4)' : 'var(--border)';
          btnToggleGcash.style.color = showGcashStand ? '#F5A25D' : 'var(--muted)';
          renderStickers();
          showToast(showGcashStand ? 'GCash Pay QR added to table stands' : 'GCash Pay QR hidden from table stands');
        });
      }

      function showToast(msg) {
        var toast = document.getElementById('studioToast');
        var toastMsg = document.getElementById('studioToastMsg');
        if (!toast || !toastMsg) return;
        toastMsg.textContent = msg;
        toast.style.display = 'flex';
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(function() {
          toast.style.display = 'none';
        }, 2400);
      }

      // Style switch handlers
      [btnStyleAcrylic, btnStyleDark, btnStyleMono].forEach(function(btn) {
        if (!btn) return;
        btn.addEventListener('click', function() {
          [btnStyleAcrylic, btnStyleDark, btnStyleMono].forEach(function(b) {
            if (b) b.classList.toggle('active', b === btn);
          });
          activeCardTheme = btn.getAttribute('data-style');
          document.querySelectorAll('.sticker-card').forEach(function(card) {
            card.className = 'sticker-card ' + activeCardTheme;
          });
        });
      });

      if (btnResetHost) {
        btnResetHost.addEventListener('click', function() {
          baseUrlInput.value = defaultHostUrl;
          renderStickers();
          showToast('Host reset to default URL');
        });
      }

      function renderStickers() {
        var count = parseInt(tableCountSelect.value, 10) || 10;
        var rawBase = baseUrlInput.value.trim().replace(/\/+$/, '');
        grid.innerHTML = '';

        for (var i = 1; i <= count; i++) {
          var pad = (i < 10 ? '0' : '') + i;
          var tableUrl = rawBase + '/index.php?table=' + i;

          var card = document.createElement('div');
          card.className = 'sticker-card ' + activeCardTheme;
          card.innerHTML = `
            <!-- Corner Crop Marks for Precision Cutting -->
            <span class="crop-mark crop-mark-tl"></span>
            <span class="crop-mark crop-mark-tr"></span>
            <span class="crop-mark crop-mark-bl"></span>
            <span class="crop-mark crop-mark-br"></span>

            <!-- Brand Header -->
            <div class="sticker-brand-header">
              <div class="sticker-brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
              </div>
              <div class="sticker-brand-name">BeCoffee</div>
              <span class="sticker-brand-tag">Specialty Roastery &amp; Cafe</span>
            </div>

            <!-- Table Number Callout -->
            <div class="sticker-table-box">
              <span class="sticker-table-kicker">Table</span>
              <span class="sticker-table-number">${pad}</span>
              <span class="sticker-table-rule"></span>
            </div>

            <!-- QR Code Scan Zone with Viewfinder Brackets -->
            <div class="sticker-qr-zone">
              <span class="scan-bracket bracket-tl"></span>
              <span class="scan-bracket bracket-tr"></span>
              <span class="scan-bracket bracket-bl"></span>
              <span class="scan-bracket bracket-br"></span>
              <div class="sticker-qr-container" id="qrContainer_${i}"></div>
            </div>

            <!-- Instructions -->
            <div class="sticker-instruction-primary">Scan with Camera to Order</div>
            <div class="sticker-instruction-sub">Point phone camera · No app required</div>

            <!-- Direct Fallback Link -->
            <div class="sticker-url-pill" title="${tableUrl}">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
              <span>${tableUrl.replace(/^https?:\/\//, '')}</span>
            </div>

            ${showGcashStand ? `
              <!-- Dual Stand: GCash Pay QR -->
              <div class="sticker-gcash-block">
                <img src="images/qr/gcash_qr_sample.png" alt="Scan to pay via GCash" class="sticker-gcash-qr-mini">
                <div class="sticker-gcash-info">
                  <span class="sticker-gcash-kicker">Pay with GCash / QR Ph</span>
                  <span class="sticker-gcash-title">BeCoffee Roastery</span>
                  <span class="sticker-gcash-sub">0994 873 •••• · Scan &amp; show to server</span>
                </div>
              </div>
            ` : ''}

            <!-- Reassurance Footer -->
            <div class="sticker-footer-note">
              <span>Orders freshly crafted &amp; served to this table</span>
            </div>

            <!-- Interactive Screen Actions -->
            <div class="sticker-card-actions">
              <a href="${tableUrl}" target="_blank" class="sticker-action-btn" title="Open Table ${pad} ordering session in a new tab">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <span>Test Order</span>
              </a>
              <button type="button" class="sticker-action-btn btn-copy-card-link" data-url="${tableUrl}" data-table="${pad}" title="Copy direct link for Table ${pad}">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span>Copy Link</span>
              </button>
            </div>
          `;
          grid.appendChild(card);

          // Generate High-Density QR Code
          try {
            new QRCode(document.getElementById('qrContainer_' + i), {
              text: tableUrl,
              width: 150,
              height: 150,
              colorDark: "#110D0B",
              colorLight: "#FFFFFF",
              correctLevel: QRCode.CorrectLevel.M
            });
          } catch(e) {
            console.error('QR code generation error for table ' + i, e);
          }
        }

        // Bind copy link buttons
        grid.querySelectorAll('.btn-copy-card-link').forEach(function(btn) {
          btn.addEventListener('click', async function(e) {
            e.preventDefault();
            var url = btn.getAttribute('data-url');
            var tbl = btn.getAttribute('data-table');
            try {
              await navigator.clipboard.writeText(url);
              showToast(`Table ${tbl} link copied!`);
            } catch (err) {
              showToast(`Link: ${url}`);
            }
          });
        });
      }

      // Re-render when host or count changes
      baseUrlInput.addEventListener('change', renderStickers);
      tableCountSelect.addEventListener('change', async function() {
        renderStickers();
        try {
          await fetch('api/settings.php?action=update_table_count', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table_count: parseInt(tableCountSelect.value, 10) })
          });
          showToast(`Table count updated to ${tableCountSelect.value}`);
        } catch(e) {}
      });

      // Master Toggle Button with SystemDialog Confirmation
      btnToggleQR.addEventListener('click', async function() {
        var actionVerb = isQREnabled ? 'pause' : 'activate';
        var confirmed = await SystemDialog.confirm(
          `Are you sure you want to ${actionVerb} Table QR Ordering cafe-wide?` + 
          (isQREnabled ? ' Guests scanning stickers will be guided to order at the counter.' : ' Guests scanning stickers will immediately start table sessions.'),
          {
            title: isQREnabled ? 'Pause Table QR Ordering' : 'Activate Table QR Ordering',
            confirmText: isQREnabled ? 'Pause Ordering' : 'Activate Ordering',
            isDestructive: isQREnabled
          }
        );
        if (!confirmed) return;

        btnToggleQR.disabled = true;
        try {
          var res = await fetch('api/settings.php?action=toggle_qr', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
          });
          var data = await res.json();
          if (res.ok && data.success) {
            isQREnabled = data.table_qr_ordering_enabled;
            var iconHolder = document.getElementById('qrIconHolder');
            if (isQREnabled) {
              btnToggleQR.className = 'btn-action btn-qr-toggle';
              btnToggleQRText.textContent = 'Pause Table QR';
              if (iconHolder) {
                iconHolder.innerHTML = '<svg class="qr-toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="10" y1="15" x2="10" y2="9"></line><line x1="14" y1="15" x2="14" y2="9"></line></svg>';
              }
            } else {
              btnToggleQR.className = 'btn-action btn-qr-toggle is-paused';
              btnToggleQRText.textContent = 'Enable Table QR';
              if (iconHolder) {
                iconHolder.innerHTML = '<svg class="qr-toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"></polygon></svg>';
              }
            }
            await SystemDialog.alert(
              isQREnabled ? 'Table QR ordering is now LIVE cafe-wide.' : 'Table QR ordering has been PAUSED.',
              { title: 'Status Updated', type: isQREnabled ? 'success' : 'warning' }
            );
          } else {
            await SystemDialog.alert(data.error || 'Failed to toggle QR ordering.', { title: 'Update Failed', type: 'danger' });
          }
        } catch(e) {
          await SystemDialog.alert('Network error while toggling QR ordering.', { title: 'Network Error', type: 'danger' });
        } finally {
          btnToggleQR.disabled = false;
        }
      });

      // Initial Render
      renderStickers();
    })();
  </script>
</body>
</html>
