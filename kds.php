<?php
require_once __DIR__ . '/api/config.php';
$currentUser = getAuthenticatedUser();
if (!$currentUser) {
    header('Location: index.php?error=unauthorized');
    exit;
}
if ($currentUser['role'] === 'customer') {
    header('Location: index.php?notice=staff_only');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Barista KDS Station | Escobar Cafe Operations</title>
  <meta name="description" content="Staff Kitchen Display System and QA Lockout Terminal for Escobar Cafe / BeCoffee.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --kds-bg: #0D0A08;
      --kds-surface: #17120F;
      --kds-card: #201915;
      --kds-card-hover: #2A211C;
      --kds-border: rgba(223, 155, 100, 0.16);
      --kds-amber: #E28743;
      --kds-amber-glow: rgba(226, 135, 67, 0.25);
      --kds-red: #EF4444;
      --kds-red-glow: rgba(239, 68, 68, 0.3);
      --kds-blue: #3B82F6;
      --kds-green: #10B981;
      --kds-green-glow: rgba(16, 185, 129, 0.35);
      --kds-text: #F5EBE1;
      --kds-muted: #A39284;
      --font-sans: 'Outfit', system-ui, sans-serif;
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-mono: 'JetBrains Mono', monospace;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-tap-highlight-color: transparent;
    }
    body {
      background: var(--kds-bg);
      color: var(--kds-text);
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      user-select: none;
    }

    /* KDS Top Bar */
    .kds-header {
      background: rgba(23, 18, 15, 0.95);
      border-bottom: 1px solid var(--kds-border);
      padding: 0.75rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(12px);
    }
    .kds-brand {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .kds-logo {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-weight: 700;
      font-size: 1.25rem;
      box-shadow: 0 4px 12px var(--kds-amber-glow);
    }
    .kds-title-group h1 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.1;
      letter-spacing: -0.01em;
    }
    .kds-subtitle {
      font-size: 0.72rem;
      color: var(--kds-amber);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .kds-actions {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .kds-clock {
      font-family: var(--font-mono);
      font-size: 0.9rem;
      color: #D6C7BC;
      background: rgba(255, 255, 255, 0.05);
      padding: 0.4rem 0.85rem;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .kds-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.45rem 0.95rem;
      border-radius: 8px;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid var(--kds-border);
      background: rgba(255, 255, 255, 0.06);
      color: var(--kds-text);
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .kds-btn:hover {
      background: rgba(226, 135, 67, 0.15);
      border-color: var(--kds-amber);
      color: #FFF;
    }
    .sound-btn.active {
      background: rgba(16, 185, 129, 0.15);
      border-color: rgba(16, 185, 129, 0.4);
      color: #34D399;
    }

    /* KDS Main Multi-Column Board */
    .kds-board {
      flex: 1;
      display: grid;
      grid-template-columns: 340px 340px 1fr;
      gap: 1.25rem;
      padding: 1.25rem 1.5rem;
      min-height: calc(100vh - 65px);
    }
    @media (max-width: 1100px) {
      .kds-board {
        grid-template-columns: 300px 300px 1fr;
        padding: 1rem;
        gap: 1rem;
      }
    }
    @media (max-width: 900px) {
      .kds-board {
        grid-template-columns: 1fr;
        display: flex;
        flex-direction: column;
      }
    }
    @media (max-width: 640px) {
      .kds-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem 0.85rem;
      }
      .kds-actions {
        width: 100%;
        flex-wrap: wrap;
        gap: 0.4rem;
      }
      .kds-board {
        padding: 0.75rem 0.65rem;
        gap: 0.75rem;
      }
      .ws-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }
      .ws-body {
        padding: 1rem;
      }
      .ws-footer {
        padding: 1rem;
      }
      .qa-lockout-box {
        padding: 1rem;
      }
    }

    /* Column Styles */
    .kds-col {
      background: var(--kds-surface);
      border: 1px solid var(--kds-border);
      border-radius: 16px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
    }
    .kds-col-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--kds-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(0, 0, 0, 0.2);
    }
    .kds-col-title {
      font-size: 0.95rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .col-pending .kds-col-title { color: #F87171; }
    .col-progress .kds-col-title { color: #60A5FA; }
    .kds-badge-count {
      padding: 0.2rem 0.65rem;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 700;
      font-family: var(--font-mono);
    }
    .col-pending .kds-badge-count {
      background: rgba(239, 68, 68, 0.2);
      color: #F87171;
      border: 1px solid rgba(239, 68, 68, 0.4);
    }
    .col-progress .kds-badge-count {
      background: rgba(59, 130, 246, 0.2);
      color: #93C5FD;
      border: 1px solid rgba(59, 130, 246, 0.4);
    }

    /* Ticket List */
    .kds-ticket-list {
      flex: 1;
      overflow-y: auto;
      padding: 0.85rem;
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
    }
    .kds-empty-notice {
      padding: 3rem 1.5rem;
      text-align: center;
      color: var(--kds-muted);
      font-size: 0.88rem;
    }

    /* Ticket Card */
    .kds-ticket-card {
      background: var(--kds-card);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1rem;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
    }
    .kds-ticket-card:hover {
      background: var(--kds-card-hover);
      border-color: rgba(226, 135, 67, 0.35);
      transform: translateY(-2px);
    }
    .kds-ticket-card.selected {
      border-color: var(--kds-amber) !important;
      background: #2D231E !important;
      box-shadow: 0 0 0 2px var(--kds-amber-glow), 0 8px 24px rgba(0, 0, 0, 0.6);
    }

    /* Flashing Red Animation for New Pending Tickets */
    .kds-ticket-card.is-new-pending {
      animation: alertPulse 1.8s infinite alternate;
      border-left: 5px solid var(--kds-red);
    }
    @keyframes alertPulse {
      0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
        border-color: rgba(239, 68, 68, 0.5);
      }
      100% {
        box-shadow: 0 0 18px 4px rgba(239, 68, 68, 0.4);
        border-color: #EF4444;
      }
    }
    .col-progress .kds-ticket-card {
      border-left: 5px solid var(--kds-blue);
    }

    .ticket-head-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 0.4rem;
    }
    .ticket-queue-num {
      font-size: 1.35rem;
      font-weight: 800;
      color: #FFF;
      font-family: var(--font-mono);
      letter-spacing: -0.02em;
    }
    .ticket-timer {
      font-size: 0.78rem;
      font-family: var(--font-mono);
      font-weight: 600;
      color: var(--kds-amber);
    }
    .ticket-meta-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
      margin-bottom: 0.65rem;
    }
    .meta-pill {
      font-size: 0.72rem;
      padding: 0.2rem 0.55rem;
      border-radius: 6px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .pill-dinein {
      background: rgba(16, 185, 129, 0.15);
      color: #34D399;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .pill-takeout {
      background: rgba(245, 158, 11, 0.15);
      color: #FBBF24;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .pill-gcash {
      background: rgba(59, 130, 246, 0.15);
      color: #60A5FA;
      border: 1px solid rgba(59, 130, 246, 0.3);
    }
    .pill-cash {
      background: rgba(255, 255, 255, 0.08);
      color: #D1C5BD;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .ticket-preview-items {
      font-size: 0.85rem;
      color: #E2D5CC;
      line-height: 1.35;
    }

    /* Right Panel: Expanded Workstation & QA Lockout */
    .kds-workstation {
      background: var(--kds-surface);
      border: 1px solid var(--kds-border);
      border-radius: 16px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
    }
    .ws-header {
      padding: 1.25rem 1.75rem;
      background: rgba(0, 0, 0, 0.3);
      border-bottom: 1px solid var(--kds-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .ws-title-wrap {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .ws-queue-badge {
      font-size: 2.25rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1;
      padding: 0.4rem 0.85rem;
      border-radius: 12px;
      background: rgba(226, 135, 67, 0.15);
      border: 2px solid var(--kds-amber);
    }
    .ws-order-meta h2 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #FFF;
      margin-bottom: 0.2rem;
    }
    .ws-order-meta p {
      font-size: 0.82rem;
      color: var(--kds-muted);
    }

    .ws-body {
      flex: 1;
      padding: 1.75rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    /* Barista Recipe Cards */
    .recipe-list-heading {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--kds-amber);
      font-weight: 700;
      margin-bottom: 0.65rem;
    }
    .recipe-card {
      background: var(--kds-card);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }
    .recipe-title-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .recipe-name {
      font-size: 1.2rem;
      font-weight: 700;
      color: #FFF;
    }
    .recipe-qty-pill {
      font-family: var(--font-mono);
      font-size: 1rem;
      font-weight: 700;
      background: rgba(255, 255, 255, 0.1);
      padding: 0.25rem 0.65rem;
      border-radius: 8px;
      color: #FFF;
    }
    .recipe-specs-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .spec-tag {
      font-size: 0.82rem;
      font-weight: 600;
      padding: 0.35rem 0.75rem;
      border-radius: 8px;
    }
    .spec-milk {
      background: rgba(226, 135, 67, 0.2);
      color: #FDBA74;
      border: 1px solid rgba(226, 135, 67, 0.4);
    }
    .spec-sugar {
      background: rgba(168, 85, 247, 0.2);
      color: #D8B4FE;
      border: 1px solid rgba(168, 85, 247, 0.4);
    }
    .spec-temp {
      background: rgba(59, 130, 246, 0.2);
      color: #93C5FD;
      border: 1px solid rgba(59, 130, 246, 0.4);
    }
    .recipe-notes-callout {
      background: rgba(0, 0, 0, 0.35);
      border-left: 3px solid var(--kds-amber);
      padding: 0.65rem 0.85rem;
      border-radius: 4px;
      font-size: 0.88rem;
      color: #FCE7D0;
      font-style: italic;
    }

    /* THE QA LOCKOUT ("The Bleeding Neck Solution") */
    .qa-lockout-box {
      background: rgba(23, 18, 15, 0.85);
      border: 2px solid rgba(223, 155, 100, 0.25);
      border-radius: 16px;
      padding: 1.25rem 1.5rem;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.4);
    }
    .qa-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.65rem;
    }
    .qa-title {
      font-size: 0.95rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #FFF;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }
    .qa-shield-icon {
      color: var(--kds-amber);
    }
    .qa-gate-status {
      font-size: 0.78rem;
      font-family: var(--font-mono);
      font-weight: 700;
      color: #F87171;
    }
    .qa-gate-status.ready {
      color: #34D399;
    }

    .qa-checklist {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      margin-top: 0.85rem;
    }
    .qa-checkbox-label {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      background: var(--kds-card);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 0.85rem 1rem;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.92rem;
      font-weight: 500;
      color: #E2D5CC;
    }
    .qa-checkbox-label:hover {
      background: #281F1A;
      border-color: rgba(226, 135, 67, 0.3);
    }
    .qa-checkbox-label input[type="checkbox"] {
      appearance: none;
      width: 22px;
      height: 22px;
      border-radius: 6px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      background: transparent;
      cursor: pointer;
      display: grid;
      place-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .qa-checkbox-label input[type="checkbox"]:checked {
      background: var(--kds-green);
      border-color: var(--kds-green);
    }
    .qa-checkbox-label input[type="checkbox"]:checked::before {
      content: "";
      width: 10px;
      height: 6px;
      border-left: 2px solid #FFF;
      border-bottom: 2px solid #FFF;
      transform: rotate(-45deg) translate(1px, -1px);
    }
    .qa-checkbox-label.checked {
      background: rgba(16, 185, 129, 0.12);
      border-color: rgba(16, 185, 129, 0.4);
      color: #ECFDF5;
    }

    /* Workstation Footer & Action Buttons */
    .ws-footer {
      padding: 1.25rem 1.75rem;
      background: rgba(0, 0, 0, 0.4);
      border-top: 1px solid var(--kds-border);
      display: flex;
      gap: 1rem;
    }
    .btn-action-primary {
      flex: 1;
      min-height: 54px;
      border-radius: 12px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.25s ease;
      border: none;
    }
    /* Locked state */
    .btn-action-primary.locked {
      background: #261F1A;
      color: #6B5B50;
      border: 1px solid rgba(255, 255, 255, 0.05);
      cursor: not-allowed;
      pointer-events: none;
    }
    /* Unlocked Solid Green */
    .btn-action-primary.unlocked {
      background: linear-gradient(135deg, #10B981 0%, #059669 100%);
      color: #FFF;
      box-shadow: 0 4px 20px var(--kds-green-glow);
      cursor: pointer;
      pointer-events: auto;
    }
    .btn-action-primary.unlocked:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(16, 185, 129, 0.5);
    }
    .btn-acknowledge {
      background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
      color: #FFF;
      box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
    }
  </style>

  <script>
    (async function initKdsAuthGuard() {
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
        // Customers are strictly blocked from Kitchen Display
        if (data.user.role === 'customer') {
          window.location.replace('index.php?notice=staff_only');
          return;
        }
        // Allowed: staff, admin, superadmin
        window.__KDS_USER__ = data.user;
        
        window.handleKdsSignOut = async function() {
          try {
            await fetch('api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
          } catch(e) {}
          localStorage.removeItem('becoffee_demo_session');
          window.location.href = 'api/auth.php?action=logout';
        };

        function setupKdsHeader() {
          var staffPill = document.getElementById('kdsStaffPill');
          if (staffPill) {
            staffPill.innerHTML = `Staff: <strong>${data.user.name}</strong> <span style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">(${data.user.role})</span>`;
          }
          if (data.user.role === 'admin' || data.user.role === 'superadmin') {
            var adminLink = document.getElementById('kdsAdminLink');
            if (adminLink) adminLink.style.display = 'inline-flex';
            var posLink = document.getElementById('kdsPosLink');
            if (posLink) posLink.style.display = 'inline-flex';
          }
          var signoutBtn = document.getElementById('kdsSignOutBtn');
          if (signoutBtn) {
            signoutBtn.onclick = window.handleKdsSignOut;
          }
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', setupKdsHeader);
        } else {
          setupKdsHeader();
        }
      } catch (e) {
        console.warn('KDS auth guard check deferred:', e);
      }
    })();
  </script>
</head>
<body>

  <!-- KDS Header Bar -->
  <header class="kds-header">
    <div class="kds-brand">
      <div class="kds-logo">E</div>
      <div class="kds-title-group">
        <h1>Escobar Cafe · Barista KDS</h1>
        <div class="kds-subtitle">Staff Kitchen Display & QA Terminal</div>
      </div>
    </div>
    <div class="kds-actions">
      <div class="kds-clock" id="kdsClock">00:00:00</div>
      <div class="kds-clock" id="kdsStaffPill" style="font-size: 0.8rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); padding: 0.35rem 0.75rem; border-radius: 8px;">
        Staff Station
      </div>
      <button type="button" class="kds-btn sound-btn active" id="kdsSoundToggle" title="Toggle audio chime alert on new incoming tickets">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
        <span id="soundLabel">Chime: ON</span>
      </button>
      <a href="index.php?mode=staff" class="kds-btn" id="kdsPosLink" style="display: none; background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6EE7B7; text-decoration: none;">
        🛍️ Take Orders (Dine In / Take Out Order)
      </a>
      <a href="admin.php" class="kds-btn" id="kdsAdminLink" style="display: none; text-decoration: none;">
        📊 Admin Studio
      </a>
      <button type="button" class="kds-btn" id="kdsSignOutBtn" onclick="handleKdsSignOut()" style="cursor: pointer;">
        🚪 Sign Out
      </button>
    </div>
  </header>

  <!-- KDS Multi-Column Board -->
  <main class="kds-board">
    
    <!-- Column 1: Pending Orders -->
    <section class="kds-col col-pending">
      <div class="kds-col-header">
        <div class="kds-col-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          Pending Orders
        </div>
        <span class="kds-badge-count" id="pendingCount">0</span>
      </div>
      <div class="kds-ticket-list" id="pendingTicketList">
        <div class="kds-empty-notice">No pending orders. Station clear.</div>
      </div>
    </section>

    <!-- Column 2: In Progress -->
    <section class="kds-col col-progress">
      <div class="kds-col-header">
        <div class="kds-col-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
          In Progress
        </div>
        <span class="kds-badge-count" id="inProgressCount">0</span>
      </div>
      <div class="kds-ticket-list" id="inProgressTicketList">
        <div class="kds-empty-notice">No tickets currently brewing on bar.</div>
      </div>
    </section>

    <!-- Column 3: Workstation & QA Lockout -->
    <section class="kds-workstation" id="kdsWorkstation">
      <div class="ws-header">
        <div class="ws-title-wrap">
          <div class="ws-queue-badge" id="wsQueueBadge">#---</div>
          <div class="ws-order-meta">
            <h2 id="wsCustomerName">Select a ticket to begin preparation</h2>
            <p id="wsOrderRef">Order details will expand here</p>
          </div>
        </div>
        <div id="wsMetaPills"></div>
      </div>

      <div class="ws-body" id="wsBody">
        <div style="text-align: center; color: var(--kds-muted); margin: auto;">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" style="margin-bottom: 0.75rem;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
          <p>Tap any ticket in <strong>Pending Orders</strong> or <strong>In Progress</strong> to view exact recipe instructions and the 3-point QA Lockout.</p>
        </div>
      </div>

      <div class="ws-footer" id="wsFooter" style="display: none;">
        <!-- Dynamic Action Button: Acknowledge or Complete -->
      </div>
    </section>

  </main>

  <script>
    // =========================================================================
    // Escobar Cafe KDS Engine & Hardware-Free Web Audio Chime
    // =========================================================================
    (function initKdsEngine() {
      var audioCtx = null;
      var soundEnabled = true;
      var lastPendingIds = new Set();
      var selectedOrderId = null;
      var activeOrdersMap = new Map();

      // Digital Clock
      function updateClock() {
        var now = new Date();
        document.getElementById('kdsClock').textContent = now.toLocaleTimeString('en-US', { hour12: true });
      }
      setInterval(updateClock, 1000);
      updateClock();

      // Web Audio API Cafe Bell Synthesizer (Zero MP3 Dependencies)
      function playCafeChime() {
        if (!soundEnabled) return;
        try {
          if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          }
          if (audioCtx.state === 'suspended') {
            audioCtx.resume();
          }
          var now = audioCtx.currentTime;
          
          // Two-tone bell harmonic frequencies
          [587.33, 880].forEach(function(freq, idx) {
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, now + (idx * 0.08));
            
            gain.gain.setValueAtTime(0, now + (idx * 0.08));
            gain.gain.linearRampToValueAtTime(0.25, now + (idx * 0.08) + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + (idx * 0.08) + 1.2);
            
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(now + (idx * 0.08));
            osc.stop(now + (idx * 0.08) + 1.25);
          });
        } catch (e) {
          console.warn('Audio chime warning:', e);
        }
      }

      // Sound toggle
      var soundBtn = document.getElementById('kdsSoundToggle');
      var soundLabel = document.getElementById('soundLabel');
      soundBtn.addEventListener('click', function() {
        soundEnabled = !soundEnabled;
        soundBtn.classList.toggle('active', soundEnabled);
        soundLabel.textContent = soundEnabled ? 'Chime: ON' : 'Chime: OFF';
        if (soundEnabled && !audioCtx) {
          audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
      });

      // Poll KDS Queue from /api/kds.php
      async function fetchKdsQueue() {
        try {
          var res = await fetch('api/kds.php', { cache: 'no-store', credentials: 'include' });
          if (!res.ok) {
            if (res.status === 401 || res.status === 403) {
              console.warn('KDS: Unauthorized. Staff session required.');
            }
            return;
          }
          var data = await res.json();
          if (!data.success) return;

          renderBoard(data);
        } catch (err) {
          console.warn('KDS polling error:', err);
        }
      }

      function formatElapsed(seconds) {
        if (seconds < 60) return `${seconds}s ago`;
        var mins = Math.floor(seconds / 60);
        return `${mins}m ago`;
      }

      function renderBoard(data) {
        var pending = data.pending || [];
        var inProgress = data.in_progress || [];

        // Check for newly arrived pending orders to trigger chime
        var currentPendingIds = new Set(pending.map(function(o) { return o.id; }));
        var hasNewPending = false;
        currentPendingIds.forEach(function(id) {
          if (!lastPendingIds.has(id)) {
            hasNewPending = true;
          }
        });
        lastPendingIds = currentPendingIds;
        if (hasNewPending && pending.length > 0) {
          playCafeChime();
        }

        // Cache orders in memory map
        activeOrdersMap.clear();
        pending.forEach(function(o) { activeOrdersMap.set(o.id, o); });
        inProgress.forEach(function(o) { activeOrdersMap.set(o.id, o); });

        // Update counts
        document.getElementById('pendingCount').textContent = pending.length;
        document.getElementById('inProgressCount').textContent = inProgress.length;

        // Render Pending Column
        var pendingList = document.getElementById('pendingTicketList');
        if (pending.length === 0) {
          pendingList.innerHTML = '<div class="kds-empty-notice">No pending orders. Station clear.</div>';
        } else {
          pendingList.innerHTML = pending.map(function(ord) {
            var isSelected = (ord.id === selectedOrderId);
            var itemsSummary = ord.items.map(function(it) {
              return `${it.quantity}x ${it.item_name}`;
            }).join(', ');
            var typePill = (ord.order_type === 'take_out')
              ? '<span class="meta-pill pill-takeout">Take-out</span>'
              : `<span class="meta-pill pill-dinein">Table ${ord.table_number || '1'}</span>`;
            var payPill = (ord.payment_method === 'gcash')
              ? '<span class="meta-pill pill-gcash">GCash</span>'
              : '<span class="meta-pill pill-cash">Cash</span>';

            return `
              <div class="kds-ticket-card is-new-pending ${isSelected ? 'selected' : ''}" data-order-id="${ord.id}">
                <div class="ticket-head-row">
                  <span class="ticket-queue-num">#${ord.queue_number}</span>
                  <span class="ticket-timer">${formatElapsed(ord.elapsed_seconds || 0)}</span>
                </div>
                <div class="ticket-meta-pills">
                  ${typePill}
                  ${payPill}
                </div>
                <div class="ticket-preview-items">${itemsSummary}</div>
              </div>
            `;
          }).join('');
        }

        // Render In Progress Column
        var progressList = document.getElementById('inProgressTicketList');
        if (inProgress.length === 0) {
          progressList.innerHTML = '<div class="kds-empty-notice">No tickets currently brewing on bar.</div>';
        } else {
          progressList.innerHTML = inProgress.map(function(ord) {
            var isSelected = (ord.id === selectedOrderId);
            var itemsSummary = ord.items.map(function(it) {
              return `${it.quantity}x ${it.item_name}`;
            }).join(', ');
            var typePill = (ord.order_type === 'take_out')
              ? '<span class="meta-pill pill-takeout">Take-out</span>'
              : `<span class="meta-pill pill-dinein">Table ${ord.table_number || '1'}</span>`;
            var payPill = (ord.payment_method === 'gcash')
              ? '<span class="meta-pill pill-gcash">GCash</span>'
              : '<span class="meta-pill pill-cash">Cash</span>';

            return `
              <div class="kds-ticket-card ${isSelected ? 'selected' : ''}" data-order-id="${ord.id}">
                <div class="ticket-head-row">
                  <span class="ticket-queue-num">#${ord.queue_number}</span>
                  <span class="ticket-timer">${formatElapsed(ord.elapsed_seconds || 0)}</span>
                </div>
                <div class="ticket-meta-pills">
                  ${typePill}
                  ${payPill}
                </div>
                <div class="ticket-preview-items">${itemsSummary}</div>
              </div>
            `;
          }).join('');
        }

        // Attach Card Click Handlers
        document.querySelectorAll('.kds-ticket-card').forEach(function(card) {
          card.addEventListener('click', function() {
            var id = parseInt(card.getAttribute('data-order-id'), 10);
            selectOrder(id);
          });
        });

        // Re-render Workstation if an order is active
        if (selectedOrderId && activeOrdersMap.has(selectedOrderId)) {
          renderWorkstation(activeOrdersMap.get(selectedOrderId));
        } else if (selectedOrderId && !activeOrdersMap.has(selectedOrderId)) {
          // If selected order was completed/disappeared
          clearWorkstation();
        }
      }

      function selectOrder(orderId) {
        selectedOrderId = orderId;
        // Update selection UI highlight
        document.querySelectorAll('.kds-ticket-card').forEach(function(c) {
          c.classList.toggle('selected', parseInt(c.getAttribute('data-order-id'), 10) === orderId);
        });
        if (activeOrdersMap.has(orderId)) {
          renderWorkstation(activeOrdersMap.get(orderId));
        }
      }

      function clearWorkstation() {
        selectedOrderId = null;
        document.getElementById('wsQueueBadge').textContent = '#---';
        document.getElementById('wsCustomerName').textContent = 'Select a ticket to begin preparation';
        document.getElementById('wsOrderRef').textContent = 'Order details will expand here';
        document.getElementById('wsMetaPills').innerHTML = '';
        document.getElementById('wsBody').innerHTML = `
          <div style="text-align: center; color: var(--kds-muted); margin: auto;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" style="margin-bottom: 0.75rem;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            <p>Tap any ticket in <strong>Pending Orders</strong> or <strong>In Progress</strong> to view exact recipe instructions and the 3-point QA Lockout.</p>
          </div>
        `;
        document.getElementById('wsFooter').style.display = 'none';
      }

      // Render Expanded Workstation View & The QA Lockout
      function renderWorkstation(ord) {
        document.getElementById('wsQueueBadge').textContent = `#${ord.queue_number}`;
        document.getElementById('wsCustomerName').textContent = `${ord.customer_name} (${ord.customer_phone || ''})`;
        document.getElementById('wsOrderRef').textContent = `Ref: ${ord.order_reference} · Placed ${formatElapsed(ord.elapsed_seconds || 0)}`;

        var typePill = (ord.order_type === 'take_out')
          ? '<span class="meta-pill pill-takeout" style="font-size:0.8rem; padding: 0.35rem 0.75rem;">Take-out</span>'
          : `<span class="meta-pill pill-dinein" style="font-size:0.8rem; padding: 0.35rem 0.75rem;">Table #${ord.table_number || '1'}</span>`;
        var payPill = (ord.payment_method === 'gcash')
          ? '<span class="meta-pill pill-gcash" style="font-size:0.8rem; padding: 0.35rem 0.75rem;">GCash · ₱' + parseFloat(ord.grand_total).toFixed(2) + '</span>'
          : '<span class="meta-pill pill-cash" style="font-size:0.8rem; padding: 0.35rem 0.75rem;">Cash at Counter · ₱' + parseFloat(ord.grand_total).toFixed(2) + '</span>';

        document.getElementById('wsMetaPills').innerHTML = `${typePill} ${payPill}`;

        // Build Recipe Cards
        var recipesHtml = ord.items.map(function(it) {
          var specs = [];
          if (it.temperature) specs.push(`<span class="spec-tag spec-temp">${it.temperature}</span>`);
          if (it.milk_option) specs.push(`<span class="spec-tag spec-milk">${it.milk_option}</span>`);
          if (it.sweetness_level) specs.push(`<span class="spec-tag spec-sugar">${it.sweetness_level}</span>`);

          var notesHtml = it.custom_notes
            ? `<div class="recipe-notes-callout">Special note: "${it.custom_notes}"</div>`
            : '';

          return `
            <div class="recipe-card">
              <div class="recipe-title-row">
                <span class="recipe-name">${it.item_name}</span>
                <span class="recipe-qty-pill">${it.quantity}x</span>
              </div>
              <div class="recipe-specs-tags">
                ${specs.join('')}
              </div>
              ${notesHtml}
            </div>
          `;
        }).join('');

        // Dynamic QA Checkbox Labels based on Order Context
        var payLabel = (ord.payment_method === 'gcash')
          ? `GCash Payment Verified (₱${parseFloat(ord.grand_total).toFixed(2)})`
          : `Cash Payment Collected at Counter (₱${parseFloat(ord.grand_total).toFixed(2)})`;

        var customList = [];
        ord.items.forEach(function(it) {
          if (it.milk_option && it.milk_option !== 'Regular Milk') customList.push(it.milk_option);
          if (it.sweetness_level && it.sweetness_level !== 'Normal (100%)') customList.push(it.sweetness_level);
          if (it.custom_notes) customList.push(it.custom_notes);
        });
        var customLabel = customList.length > 0
          ? `Customizations Followed (${customList.join(', ')})`
          : `Customizations Followed (Standard recipes confirmed)`;

        var packLabel = (ord.order_type === 'take_out')
          ? `Take-out Packaging Secured (Lids tight, straws included)`
          : `Dine-in Presentation Ready (Glassware & tray prepared for Table #${ord.table_number || '1'})`;

        var wsBody = document.getElementById('wsBody');

        // If Pending: Show "Move to In Progress" button
        if (ord.status === 'pending') {
          wsBody.innerHTML = `
            <div>
              <div class="recipe-list-heading">Order Items & Customizations</div>
              <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                ${recipesHtml}
              </div>
            </div>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1.25rem; color: #FCA5A5; font-size: 0.9rem;">
              <strong>Pending Acknowledgment:</strong> Tap the button below to claim this ticket and move it to <em>In Progress</em> on the espresso bar.
            </div>
          `;

          var footer = document.getElementById('wsFooter');
          footer.style.display = 'flex';
          footer.innerHTML = `
            <button type="button" class="btn-action-primary btn-acknowledge" id="btnAcknowledge">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              Acknowledge & Start Ticket #${ord.queue_number}
            </button>
          `;

          document.getElementById('btnAcknowledge').addEventListener('click', async function() {
            try {
              var res = await fetch('api/kds.php?action=acknowledge', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: ord.id })
              });
              var result = await res.json();
              if (result.success) {
                fetchKdsQueue();
              }
            } catch (e) {
              alert('Error acknowledging ticket: ' + e.message);
            }
          });

        } else if (ord.status === 'in_progress') {
          // If In Progress: Display THE QA LOCKOUT ("The Bleeding Neck Solution")
          wsBody.innerHTML = `
            <div>
              <div class="recipe-list-heading">Order Preparation Instructions</div>
              <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                ${recipesHtml}
              </div>
            </div>

            <!-- THE QA LOCKOUT -->
            <div class="qa-lockout-box">
              <div class="qa-header-row">
                <div class="qa-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="qa-shield-icon"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                  QA Lockout Gate (Bleeding Neck Solution)
                </div>
                <span class="qa-gate-status" id="qaStatusNotice">0/3 Checked</span>
              </div>
              <p style="font-size: 0.8rem; color: var(--kds-muted); margin-bottom: 0.75rem;">
                All three verification gates must be checked to unlock order completion and ping customer for pickup:
              </p>

              <div class="qa-checklist">
                <label class="qa-checkbox-label" id="qaLabelPay">
                  <input type="checkbox" id="qaCheckPay">
                  <span>${payLabel}</span>
                </label>
                <label class="qa-checkbox-label" id="qaLabelCustom">
                  <input type="checkbox" id="qaCheckCustom">
                  <span>${customLabel}</span>
                </label>
                <label class="qa-checkbox-label" id="qaLabelPack">
                  <input type="checkbox" id="qaCheckPack">
                  <span>${packLabel}</span>
                </label>
              </div>
            </div>
          `;

          var footer = document.getElementById('wsFooter');
          footer.style.display = 'flex';
          footer.innerHTML = `
            <button type="button" class="btn-action-primary locked" id="btnCompleteOrder">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
              <span id="completeBtnText">Complete Order (Locked: 0/3 verified)</span>
            </button>
          `;

          // QA Checkbox Interaction Logic
          var checkPay = document.getElementById('qaCheckPay');
          var checkCustom = document.getElementById('qaCheckCustom');
          var checkPack = document.getElementById('qaCheckPack');
          var completeBtn = document.getElementById('btnCompleteOrder');
          var statusNotice = document.getElementById('qaStatusNotice');
          var completeBtnText = document.getElementById('completeBtnText');

          function evaluateQaLockout() {
            var c1 = checkPay.checked;
            var c2 = checkCustom.checked;
            var c3 = checkPack.checked;
            var total = (c1 ? 1 : 0) + (c2 ? 1 : 0) + (c3 ? 1 : 0);

            document.getElementById('qaLabelPay').classList.toggle('checked', c1);
            document.getElementById('qaLabelCustom').classList.toggle('checked', c2);
            document.getElementById('qaLabelPack').classList.toggle('checked', c3);

            if (total === 3) {
              completeBtn.classList.remove('locked');
              completeBtn.classList.add('unlocked');
              statusNotice.textContent = '3/3 Ready!';
              statusNotice.classList.add('ready');
              completeBtnText.textContent = `Complete & Clear Ticket #${ord.queue_number}`;
            } else {
              completeBtn.classList.add('locked');
              completeBtn.classList.remove('unlocked');
              statusNotice.textContent = `${total}/3 Checked`;
              statusNotice.classList.remove('ready');
              completeBtnText.textContent = `Complete Order (Locked: ${total}/3 verified)`;
            }
          }

          checkPay.addEventListener('change', evaluateQaLockout);
          checkCustom.addEventListener('change', evaluateQaLockout);
          checkPack.addEventListener('change', evaluateQaLockout);

          // Complete & Clear Execution
          completeBtn.addEventListener('click', async function() {
            if (!checkPay.checked || !checkCustom.checked || !checkPack.checked) return;

            try {
              completeBtn.disabled = true;
              completeBtnText.textContent = 'Finalizing...';

              var res = await fetch('api/kds.php?action=complete', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  order_id: ord.id,
                  qa_payment_verified: true,
                  qa_customizations_followed: true,
                  qa_packaging_secured: true
                })
              });
              var result = await res.json();
              if (result.success) {
                clearWorkstation();
                fetchKdsQueue();
              } else {
                alert('QA Lockout Error: ' + result.error);
                completeBtn.disabled = false;
              }
            } catch (err) {
              alert('Error completing order: ' + err.message);
              completeBtn.disabled = false;
            }
          });
        }
      }

      // Initial Fetch & Regular 3-second Polling
      fetchKdsQueue();
      setInterval(fetchKdsQueue, 3000);

    })();
  </script>
</body>
</html>
