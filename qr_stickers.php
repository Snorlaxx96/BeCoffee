<?php
/**
 * Escobar Cafe / BeCoffee — Physical Table QR Stickers & Acrylic Stand Generator
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
  <title>Escobar Cafe | Table QR Stickers Generator</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/system-dialog.css">
  
  <style>
    :root {
      --bg: #110D0B;
      --surface: #181310;
      --surface-elevated: #221B17;
      --border: rgba(223, 155, 100, 0.2);
      --border-subtle: rgba(255, 255, 255, 0.08);
      --accent: #E28743;
      --accent-hover: #FDBA74;
      --accent-deep: #944D1C;
      --text: #F5EBE1;
      --muted: #A99B92;
      --green: #10B981;
      --red: #EF4444;
      --font-sans: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-mono: 'JetBrains Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      background-image: 
        radial-gradient(circle at 50% 0%, rgba(226, 135, 67, 0.12) 0%, transparent 55%),
        radial-gradient(circle at 100% 30%, rgba(148, 77, 28, 0.08) 0%, transparent 45%);
      color: var(--text);
      font-family: var(--font-sans);
      min-height: 100vh;
      padding-bottom: 4rem;
    }

    /* Top Studio Controls Toolbar (Screen Only) */
    .studio-header {
      background: rgba(19, 15, 12, 0.94);
      border-bottom: 1px solid var(--border);
      padding: 0.65rem 1rem;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem;
    }

    .studio-header .btn-action-primary {
      margin-left: auto;
    }

    /* Toolbar Inputs */
    .toolbar-control-group {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      background: var(--surface-elevated);
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 0.35rem 0.55rem;
      font-size: 0.8rem;
      min-height: 40px;
      transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .toolbar-control-group:focus-within {
      border-color: var(--accent);
      box-shadow: 0 0 0 2px rgba(226, 135, 67, 0.2);
    }

    .toolbar-control-group label {
      color: var(--muted);
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      user-select: none;
    }

    .toolbar-control-group input,
    .toolbar-control-group select {
      background: transparent;
      border: none;
      color: #FFF;
      font-size: 0.82rem;
      font-family: inherit;
      outline: none;
      cursor: pointer;
    }

    .toolbar-control-group input[type="text"] {
      cursor: text;
    }

    .toolbar-control-group select option {
      background: #191411;
      color: #FFF;
    }

    /* Style Switcher */
    .style-switch-group {
      display: flex;
      align-items: center;
      background: var(--surface-elevated);
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 3px;
      gap: 2px;
      min-height: 40px;
    }

    .style-switch-btn {
      padding: 0.3rem 0.6rem;
      border-radius: 7px;
      font-size: 0.76rem;
      font-weight: 600;
      color: var(--muted);
      background: transparent;
      border: none;
      cursor: pointer;
      transition: all 0.18s ease;
      white-space: nowrap;
      min-height: 32px;
    }

    .style-switch-btn.active {
      background: var(--accent);
      color: #110D0B;
      font-weight: 700;
    }

    .style-switch-btn:focus-visible,
    .btn-action:focus-visible {
      outline: 2px solid var(--accent);
      outline-offset: 2px;
    }

    /* Buttons */
    .btn-action {
      min-height: 40px;
      padding: 0.45rem 0.8rem;
      border-radius: 9px;
      font-family: var(--font-sans);
      font-weight: 700;
      font-size: 0.82rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.18s ease;
      border: 1px solid transparent;
      white-space: nowrap;
    }

    .btn-action-primary {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #110D0B;
      box-shadow: 0 4px 14px rgba(226, 135, 67, 0.35);
    }

    .btn-action-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(226, 135, 67, 0.5);
      background: linear-gradient(135deg, #FDBA74 0%, #E28743 100%);
    }

    .btn-action-secondary {
      background: var(--surface-elevated);
      color: #D6C7BC;
      border-color: var(--border);
    }

    .btn-action-secondary:hover {
      background: rgba(226, 135, 67, 0.15);
      color: #FFF;
      border-color: var(--accent);
    }

    /* Master QR Toggle Button */
    .btn-qr-toggle {
      background: var(--surface-elevated);
      color: var(--text);
      border: 1px solid var(--border);
    }

    .btn-qr-toggle:hover {
      background: rgba(226, 135, 67, 0.12);
      border-color: var(--accent);
      color: #FFF;
      transform: translateY(-1px);
    }

    .btn-qr-toggle .qr-toggle-icon {
      color: var(--accent);
      flex-shrink: 0;
      transition: color 0.18s ease;
    }

    .btn-qr-toggle:hover .qr-toggle-icon {
      color: var(--accent-hover);
    }

    .btn-qr-toggle.is-paused {
      background: rgba(239, 68, 68, 0.08);
      border-color: rgba(239, 68, 68, 0.35);
      color: #FCA5A5;
    }

    .btn-qr-toggle.is-paused:hover {
      background: rgba(239, 68, 68, 0.16);
      border-color: #EF4444;
      color: #FFF;
    }

    .btn-qr-toggle.is-paused .qr-toggle-icon {
      color: #F87171;
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
      gap: 2rem;
      justify-items: center;
    }

    /* ==========================================================================
       Physical Sticker Card Architecture (Heritage Coffee Roastery Aesthetic)
       ========================================================================== */
    .sticker-card {
      width: 280px;
      border-radius: 18px;
      padding: 1.6rem 1.25rem 1.35rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      page-break-inside: avoid;
      break-inside: avoid;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sticker-card:hover {
      transform: translateY(-2px);
    }

    /* Corner Crop Marks for Precision Cutting */
    .crop-mark {
      position: absolute;
      width: 10px;
      height: 10px;
      pointer-events: none;
      opacity: 0.45;
    }
    .crop-mark-tl { top: 8px; left: 8px; border-top: 1.5px solid currentColor; border-left: 1.5px solid currentColor; }
    .crop-mark-tr { top: 8px; right: 8px; border-top: 1.5px solid currentColor; border-right: 1.5px solid currentColor; }
    .crop-mark-bl { bottom: 8px; left: 8px; border-bottom: 1.5px solid currentColor; border-left: 1.5px solid currentColor; }
    .crop-mark-br { bottom: 8px; right: 8px; border-bottom: 1.5px solid currentColor; border-right: 1.5px solid currentColor; }

    /* Theme 1: Warm Crema Acrylic (Default Physical Print) */
    .sticker-card.theme-acrylic {
      background: #FFFDF9;
      color: #17120F;
      border: 1.5px solid #281F1A;
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
      outline: 1px solid rgba(148, 77, 28, 0.4);
      outline-offset: -5px;
    }

    .sticker-card.theme-acrylic .sticker-brand-name {
      color: #17120F;
    }

    .sticker-card.theme-acrylic .sticker-brand-tag {
      color: #944D1C;
    }

    .sticker-card.theme-acrylic .sticker-table-kicker {
      color: #7D3E12;
    }

    .sticker-card.theme-acrylic .sticker-table-number {
      color: #17120F;
    }

    .sticker-card.theme-acrylic .sticker-qr-container {
      background: #FFFFFF;
      border: 1px solid #E5E7EB;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .sticker-card.theme-acrylic .scan-bracket {
      color: #944D1C;
    }

    .sticker-card.theme-acrylic .sticker-instruction-primary {
      color: #17120F;
    }

    .sticker-card.theme-acrylic .sticker-instruction-sub {
      color: #6B7280;
    }

    .sticker-card.theme-acrylic .sticker-url-pill {
      background: #F4EFEB;
      color: #4A3A31;
      border: 1px solid #E4D8CE;
    }

    .sticker-card.theme-acrylic .sticker-footer-note {
      color: #8C7B70;
    }

    /* Theme 2: Dark Espresso Tent */
    .sticker-card.theme-dark {
      background: #17120F;
      color: #F5EBE1;
      border: 1.5px solid rgba(226, 135, 67, 0.5);
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
      outline: 1px solid rgba(226, 135, 67, 0.2);
      outline-offset: -5px;
    }

    .sticker-card.theme-dark .sticker-brand-name {
      color: #FFF;
    }

    .sticker-card.theme-dark .sticker-brand-tag {
      color: #FDBA74;
    }

    .sticker-card.theme-dark .sticker-table-kicker {
      color: #DF9B64;
    }

    .sticker-card.theme-dark .sticker-table-number {
      color: #FFF;
    }

    .sticker-card.theme-dark .sticker-qr-container {
      background: #FFFFFF;
      border: 2px solid #E28743;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
    }

    .sticker-card.theme-dark .scan-bracket {
      color: #FDBA74;
    }

    .sticker-card.theme-dark .sticker-instruction-primary {
      color: #FFF;
    }

    .sticker-card.theme-dark .sticker-instruction-sub {
      color: #A99B92;
    }

    .sticker-card.theme-dark .sticker-url-pill {
      background: rgba(226, 135, 67, 0.12);
      color: #FDBA74;
      border: 1px solid rgba(226, 135, 67, 0.3);
    }

    .sticker-card.theme-dark .sticker-footer-note {
      color: #A99B92;
    }

    /* Card Elements Typography & Geometry */
    .sticker-brand-header {
      margin-bottom: 0.4rem;
    }

    .sticker-brand-icon {
      margin-bottom: 0.25rem;
      display: inline-block;
    }

    .sticker-brand-name {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      line-height: 1.1;
    }

    .sticker-brand-tag {
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      display: block;
      margin-top: 0.15rem;
    }

    /* Table Numeral Callout */
    .sticker-table-box {
      margin: 0.5rem 0 0.85rem;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sticker-table-kicker {
      font-size: 0.68rem;
      font-weight: 800;
      letter-spacing: 0.2em;
      text-transform: uppercase;
    }

    .sticker-table-number {
      font-family: var(--font-serif);
      font-size: 2.5rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: -0.02em;
      margin-top: 0.1rem;
    }

    .sticker-table-rule {
      width: 38px;
      height: 2px;
      background: var(--accent);
      border-radius: 999px;
      margin-top: 0.35rem;
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
      padding: 0.65rem;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 172px;
      height: 172px;
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
      width: 12px;
      height: 12px;
      pointer-events: none;
    }
    .bracket-tl { top: -4px; left: -4px; border-top: 2px solid currentColor; border-left: 2px solid currentColor; border-top-left-radius: 4px; }
    .bracket-tr { top: -4px; right: -4px; border-top: 2px solid currentColor; border-right: 2px solid currentColor; border-top-right-radius: 4px; }
    .bracket-bl { bottom: -4px; left: -4px; border-bottom: 2px solid currentColor; border-left: 2px solid currentColor; border-bottom-left-radius: 4px; }
    .bracket-br { bottom: -4px; right: -4px; border-bottom: 2px solid currentColor; border-right: 2px solid currentColor; border-bottom-right-radius: 4px; }

    /* Scan Instructions */
    .sticker-instruction-primary {
      font-size: 0.88rem;
      font-weight: 800;
      letter-spacing: 0.01em;
      margin-bottom: 0.15rem;
    }

    .sticker-instruction-sub {
      font-size: 0.72rem;
      font-weight: 500;
      margin-bottom: 0.65rem;
    }

    /* Monospace URL Fallback */
    .sticker-url-pill {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      font-weight: 600;
      padding: 0.3rem 0.65rem;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      margin-bottom: 0.75rem;
    }

    .sticker-url-pill svg {
      flex-shrink: 0;
      opacity: 0.7;
    }

    .sticker-footer-note {
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }

    /* ==========================================================================
       Print Optimization Engine (@media print)
       ========================================================================== */
    @media print {
      @page {
        size: portrait;
        margin: 10mm;
      }

      body {
        background: #FFFFFF !important;
        background-image: none !important;
        color: #000000 !important;
        padding: 0 !important;
      }

      .studio-header,
      .setup-guide-bar,
      .sys-modal-overlay {
        display: none !important;
      }

      .studio-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      .sticker-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 6mm !important;
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
        color: #888888 !important;
      }

      .sticker-card .sticker-brand-name {
        color: #000000 !important;
      }

      .sticker-card .sticker-brand-tag {
        color: #444444 !important;
      }

      .sticker-card .sticker-table-kicker {
        color: #444444 !important;
      }

      .sticker-card .sticker-table-number {
        color: #000000 !important;
      }

      .sticker-card .sticker-table-rule {
        background: #000000 !important;
      }

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

      .sticker-card .scan-bracket {
        color: #000000 !important;
      }

      .sticker-card .sticker-instruction-primary {
        color: #000000 !important;
      }

      .sticker-card .sticker-instruction-sub {
        color: #555555 !important;
      }

      .sticker-card .sticker-url-pill {
        background: #F3F4F6 !important;
        color: #111111 !important;
        border: 0.5pt solid #CCCCCC !important;
      }

      .sticker-card .sticker-footer-note {
        color: #666666 !important;
      }
    }

    /* Responsive studio layout */
    @media (max-width: 768px) {
      .studio-header {
        padding: 0.65rem 1rem;
        gap: 0.5rem;
      }
      .toolbar-control-group {
        flex: 1 1 auto;
      }
      .toolbar-control-group input,
      .toolbar-control-group select {
        font-size: 16px;
      }
      .toolbar-control-group input[type="text"] {
        width: 100% !important;
        min-width: 120px;
      }
      .studio-container {
        padding: 0 0.75rem;
        margin-top: 1.25rem;
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
      <input type="text" id="baseUrlInput" value="<?= htmlspecialchars($defaultBaseUrl) ?>" style="width: 140px;">
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
        <option value="30" <?= $tableCount == 30 ? 'selected' : '' ?>>30 Tables</option>
      </select>
    </div>

    <!-- Card Style Switcher -->
    <div class="style-switch-group">
      <button type="button" class="style-switch-btn active" data-style="theme-acrylic" id="btnStyleAcrylic">Warm Acrylic</button>
      <button type="button" class="style-switch-btn" data-style="theme-dark" id="btnStyleDark">Dark Tent</button>
    </div>

    <!-- Print Action -->
    <button type="button" class="btn-action btn-action-primary" onclick="window.print()" title="Print complete sticker sheet (Ctrl + P)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
      <span>Print Sheet</span>
    </button>
  </header>

  <main class="studio-container">
    <!-- Sticker Sheet Grid Container -->
    <div class="sticker-grid" id="stickerGrid">
      <!-- Dynamically generated sticker cards -->
    </div>
  </main>

  <script>
    (function initStickerGenerator() {
      var grid = document.getElementById('stickerGrid');
      var baseUrlInput = document.getElementById('baseUrlInput');
      var tableCountSelect = document.getElementById('tableCountSelect');
      var btnToggleQR = document.getElementById('btnToggleQRMaster');
      var btnToggleQRText = document.getElementById('btnToggleQRText');
      var btnStyleAcrylic = document.getElementById('btnStyleAcrylic');
      var btnStyleDark = document.getElementById('btnStyleDark');

      var isQREnabled = <?= $qrEnabled ? 'true' : 'false' ?>;
      var activeCardTheme = 'theme-acrylic';

      // Style switch handlers
      [btnStyleAcrylic, btnStyleDark].forEach(function(btn) {
        btn.addEventListener('click', function() {
          btnStyleAcrylic.classList.toggle('active', btn === btnStyleAcrylic);
          btnStyleDark.classList.toggle('active', btn === btnStyleDark);
          activeCardTheme = btn.getAttribute('data-style');
          document.querySelectorAll('.sticker-card').forEach(function(card) {
            card.className = 'sticker-card ' + activeCardTheme;
          });
        });
      });

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
              <div class="sticker-brand-name">Escobar Cafe</div>
              <span class="sticker-brand-tag">Specialty Roastery</span>
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
            <div class="sticker-instruction-sub">Point phone camera · No app download required</div>

            <!-- Direct Fallback Link -->
            <div class="sticker-url-pill" title="${tableUrl}">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
              <span>${tableUrl.replace(/^https?:\/\//, '')}</span>
            </div>

            <!-- Reassurance Footer -->
            <div class="sticker-footer-note">
              <span>Orders freshly crafted & served to this table</span>
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
        } catch(e) {}
      });

      // Master Toggle Button with SystemDialog Confirmation
      btnToggleQR.addEventListener('click', async function() {
        var actionVerb = isQREnabled ? 'pause' : 'activate';
        var confirmed = await SystemDialog.confirm(
          `Are you sure you want to ${actionVerb} Table QR Ordering cafe-wide?` + 
          (isQREnabled ? ' Guests will be prompted to order at the counter.' : ' Guests scanning stickers will immediately start table sessions.'),
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
