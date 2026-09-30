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
$isStaff = ($currentUser['role'] === 'staff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>BeCoffee · Kitchen Screen</title>
  <meta name="description" content="Kitchen order management and preparation screen for BeCoffee Operations.">
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/system-dialog.css">
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
      background: rgba(226, 135, 67, 0.12);
      border-color: rgba(226, 135, 67, 0.3);
      color: #EDE3DA;
    }

    /* KDS Main Multi-Column Board */
    .kds-board {
      flex: 1;
      display: grid;
      grid-template-columns: minmax(280px, 1fr) minmax(280px, 1fr) minmax(440px, 1.55fr);
      gap: 1.15rem;
      padding: 1.15rem 1.35rem;
      min-height: calc(100vh - 65px);
    }
    @media (max-width: 1440px) {
      .kds-board {
        grid-template-columns: minmax(260px, 1fr) minmax(260px, 1fr) minmax(380px, 1.45fr);
        gap: 0.85rem;
        padding: 0.85rem 1rem;
      }
    }
    @media (max-width: 1180px) {
      .kds-board {
        grid-template-columns: 1fr;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
      }
    }
    @media (max-width: 1024px) {
      .kds-header {
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
      }
      .kds-actions {
        flex-wrap: wrap;
        gap: 0.4rem;
      }
    }
    @media (max-width: 640px) {
      .kds-header {
        flex-direction: column;
        align-items: stretch;
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
        padding: 0.85rem 1rem;
      }
      .ws-ops-col {
        margin-left: 0;
        align-items: flex-start;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
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
    .col-pending .kds-col-title { color: #EDE3DA; }
    .col-progress .kds-col-title { color: #EDE3DA; }
    .col-pending .kds-col-title svg { color: var(--kds-amber); }
    .col-progress .kds-col-title svg { color: #A89A8F; }
    .kds-badge-count {
      padding: 0.15rem 0.55rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 700;
      font-family: var(--font-mono);
    }
    .col-pending .kds-badge-count {
      background: rgba(226, 135, 67, 0.15);
      color: #F6AD55;
      border: 1px solid rgba(226, 135, 67, 0.3);
    }
    .col-progress .kds-badge-count {
      background: rgba(255, 255, 255, 0.08);
      color: #EDE3DA;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    /* Ticket List - 2 Orders Per Row Grid */
    .kds-ticket-list {
      flex: 1;
      overflow-y: auto;
      padding: 0.7rem 0.55rem;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.55rem;
      align-content: start;
    }
    @media (max-width: 580px) {
      .kds-ticket-list {
        grid-template-columns: 1fr;
      }
    }
    .kds-empty-notice {
      grid-column: 1 / -1;
      padding: 3rem 1.5rem;
      text-align: center;
      color: var(--kds-muted);
      font-size: 0.88rem;
    }

    /* Ticket Card - Bare Minimum */
    .kds-ticket-card {
      background: var(--kds-card);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 9px;
      padding: 0.55rem 0.65rem;
      cursor: pointer;
      transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      min-width: 0;
    }
    .kds-ticket-card:hover {
      background: #231B16;
      border-color: rgba(237, 227, 218, 0.25);
      transform: translateY(-1px);
    }
    .kds-ticket-card.selected {
      border-color: var(--kds-amber) !important;
      background: #251D18 !important;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.45);
    }
    .ticket-head-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .ticket-queue-num {
      font-size: 1.15rem;
      font-weight: 800;
      color: #FFF;
      font-family: var(--font-mono);
      letter-spacing: -0.02em;
      line-height: 1;
    }
    .ticket-timer {
      font-size: 0.72rem;
      font-family: var(--font-mono);
      font-weight: 600;
      color: #9C8E82;
    }
    .ticket-timer.urgent {
      color: #E07A5F;
      font-weight: 700;
    }
    .ticket-origin-row {
      display: flex;
      align-items: center;
    }
    .origin-badge {
      font-size: 0.66rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      text-transform: uppercase;
      line-height: 1.2;
    }
    .origin-qr {
      background: rgba(226, 135, 67, 0.14);
      color: #F5A25D;
      border: 1px solid rgba(226, 135, 67, 0.3);
    }
    .origin-registrar {
      background: rgba(184, 151, 126, 0.14);
      color: #D8C3B3;
      border: 1px solid rgba(184, 151, 126, 0.28);
    }
    .origin-online {
      background: rgba(82, 183, 136, 0.14);
      color: #74C69D;
      border: 1px solid rgba(82, 183, 136, 0.3);
    }
    .ticket-summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 0.3rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      font-size: 0.72rem;
    }
    .ticket-summary-count {
      font-family: var(--font-mono);
      font-weight: 600;
      color: #EDE3DA;
    }
    .ticket-summary-station {
      font-size: 0.68rem;
      color: #9C8E82;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .meta-pill {
      font-size: 0.66rem;
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      font-weight: 600;
      letter-spacing: 0.02em;
      background: rgba(255, 255, 255, 0.06);
      color: #C8BAAF;
      border: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }
    .pill-dinein, .pill-takeout, .pill-gcash, .pill-cash {
      background: rgba(255, 255, 255, 0.06);
      color: #C8BAAF;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Right Panel: Workstation & Preparation Checklist */
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
      padding: 0.85rem 1.25rem;
      background: rgba(18, 14, 12, 0.75);
      border-bottom: 1px solid var(--kds-border);
      flex-shrink: 0;
    }
    .ws-header-grid {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.85rem;
      flex-wrap: wrap;
      min-width: 0;
    }
    .ws-identity-col {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 0;
      flex: 1 1 auto;
    }
    .ws-queue-badge {
      font-size: 1.45rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1;
      padding: 0.4rem 0.75rem;
      border-radius: 8px;
      background: rgba(226, 135, 67, 0.16);
      border: 1px solid rgba(226, 135, 67, 0.42);
      flex-shrink: 0;
      letter-spacing: -0.02em;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      display: none;
      align-items: center;
      justify-content: center;
    }
    .ws-identity-text {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      min-width: 0;
    }
    .ws-customer-name {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      margin: 0;
      line-height: 1.2;
      letter-spacing: -0.01em;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 320px;
    }
    .ws-meta-row {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      flex-wrap: wrap;
      min-width: 0;
    }
    .ws-header-tags {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      flex-shrink: 0;
    }
    .ws-table-badge {
      font-size: 0.72rem;
      font-weight: 700;
      color: #EDE3DA;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 0.16rem 0.52rem;
      border-radius: 4px;
      letter-spacing: 0.02em;
      white-space: nowrap;
      line-height: 1.2;
    }
    .origin-badge {
      font-size: 0.68rem;
      font-weight: 700;
      padding: 0.16rem 0.48rem;
      border-radius: 4px;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      white-space: nowrap;
      line-height: 1.2;
    }
    .origin-qr {
      background: rgba(226, 135, 67, 0.18);
      color: #F8C39A;
      border: 1px solid rgba(226, 135, 67, 0.42);
    }
    .origin-registrar {
      background: rgba(59, 130, 246, 0.16);
      color: #93C5FD;
      border: 1px solid rgba(59, 130, 246, 0.35);
    }
    .origin-online {
      background: rgba(16, 185, 129, 0.16);
      color: #A7F3D0;
      border: 1px solid rgba(16, 185, 129, 0.35);
    }
    .ws-order-meta-sep {
      color: rgba(255, 255, 255, 0.22);
      font-size: 0.75rem;
      line-height: 1;
    }
    .ws-order-time {
      font-size: 0.75rem;
      color: #A89A8E;
      line-height: 1.2;
      white-space: nowrap;
    }
    .ws-ops-col {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.35rem;
      flex-shrink: 0;
      margin-left: auto;
    }
    .ws-price-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      background: rgba(226, 135, 67, 0.12);
      border: 1px solid rgba(226, 135, 67, 0.38);
      border-radius: 6px;
      padding: 0.22rem 0.65rem;
      white-space: nowrap;
      line-height: 1;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
    }
    .ws-pay-method {
      font-size: 0.7rem;
      font-weight: 800;
      color: #E28743;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      background: rgba(226, 135, 67, 0.22);
      padding: 0.12rem 0.38rem;
      border-radius: 4px;
      line-height: 1;
    }
    .ws-price-divider {
      color: rgba(255, 255, 255, 0.2);
    }
    .ws-price-val {
      font-family: var(--font-mono);
      font-size: 1.12rem;
      font-weight: 800;
      color: #FFF;
      letter-spacing: -0.01em;
      line-height: 1;
    }
    .ws-mode-switch-container {
      display: flex;
      align-items: center;
      justify-content: flex-end;
    }

    .ws-body {
      flex: 1;
      padding: 0.75rem 0.95rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }

    /* Barista Recipe Cards */
    .recipe-list-heading {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #C8BAAF;
      font-weight: 700;
      margin-bottom: 0.35rem;
    }
    .recipe-list-items {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 0.55rem;
    }
    .recipe-card {
      background: #1C1613;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 8px;
      padding: 0.65rem 0.8rem;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      min-width: 0;
    }
    .recipe-title-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .recipe-name {
      font-size: 0.92rem;
      font-weight: 600;
      color: #FFF;
    }
    .recipe-qty-pill {
      font-family: var(--font-mono);
      font-size: 0.8rem;
      font-weight: 700;
      background: rgba(226, 135, 67, 0.15);
      border: 1px solid rgba(226, 135, 67, 0.3);
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      color: #FDBA74;
    }
    .recipe-specs-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.3rem;
    }
    .spec-tag {
      font-size: 0.72rem;
      font-weight: 500;
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.06);
      color: #D6C7BC;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .spec-milk, .spec-sugar, .spec-temp {
      background: rgba(255, 255, 255, 0.06);
      color: #D6C7BC;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .recipe-notes-callout {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.35rem 0.6rem;
      border-radius: 5px;
      font-size: 0.76rem;
      color: #D6C7BC;
      font-style: italic;
    }

    /* Preparation Checklist Box */
    .qa-lockout-box {
      background: #1C1613;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 0.65rem 0.85rem;
    }
    .qa-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.35rem;
    }
    .qa-title {
      font-size: 0.82rem;
      font-weight: 700;
      color: #FFF;
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }
    .qa-checklist-icon {
      color: var(--kds-amber);
      flex-shrink: 0;
    }
    .qa-gate-status {
      font-size: 0.72rem;
      font-family: var(--font-mono);
      font-weight: 600;
      color: #C8BAAF;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
    }
    .qa-gate-status.ready {
      color: #34D399;
      background: rgba(52, 211, 153, 0.12);
      border-color: rgba(52, 211, 153, 0.25);
    }

    .qa-checklist {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      margin-top: 0.35rem;
    }
    .qa-checkbox-label {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 6px;
      padding: 0.45rem 0.65rem;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.8rem;
      font-weight: 500;
      color: #D6C7BC;
      min-height: 36px;
    }
    .qa-checkbox-label:hover {
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(255, 255, 255, 0.12);
    }
    .qa-checkbox-label input[type="checkbox"] {
      appearance: none;
      width: 18px;
      height: 18px;
      border-radius: 4px;
      border: 1.5px solid rgba(255, 255, 255, 0.25);
      background: transparent;
      cursor: pointer;
      display: grid;
      place-content: center;
      transition: all 0.15s ease;
      flex-shrink: 0;
    }
    .qa-checkbox-label input[type="checkbox"]:checked {
      background: #059669;
      border-color: #059669;
    }
    .qa-checkbox-label input[type="checkbox"]:checked::before {
      content: "";
      width: 8px;
      height: 5px;
      border-left: 2px solid #FFF;
      border-bottom: 2px solid #FFF;
      transform: rotate(-45deg) translate(1px, -1px);
    }
    .qa-checkbox-label.checked {
      background: rgba(5, 150, 105, 0.08);
      border-color: rgba(5, 150, 105, 0.3);
      color: #ECFDF5;
    }

    /* Workstation Footer & Action Buttons */
    .ws-footer {
      padding: 0.65rem 1rem;
      background: rgba(0, 0, 0, 0.3);
      border-top: 1px solid var(--kds-border);
      display: flex;
      gap: 0.75rem;
    }
    .btn-action-primary {
      flex: 1;
      min-height: 44px;
      border-radius: 8px;
      font-size: 0.92rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
      border: none;
    }
    /* Locked state */
    .btn-action-primary.locked {
      background: rgba(255, 255, 255, 0.05);
      color: #6B5B50;
      border: 1px solid rgba(255, 255, 255, 0.06);
      cursor: not-allowed;
      pointer-events: none;
    }
    /* Unlocked Solid Emerald Green */
    .btn-action-primary.unlocked {
      background: #059669;
      color: #FFF;
      box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
      cursor: pointer;
      pointer-events: auto;
    }
    .btn-action-primary.unlocked:hover {
      background: #10B981;
    }
    /* Primary Start Order - Warm Coffee Amber */
    .btn-acknowledge {
      background: var(--kds-amber);
      color: #110D0B;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(226, 135, 67, 0.3);
    }
    .btn-acknowledge:hover {
      background: #FDBA74;
    }

    /* Staff Pill & Signout Button */
    .kds-staff-pill {
      font-size: 0.82rem;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 0.38rem 0.85rem;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      color: #EDE3DA;
      font-weight: 600;
    }
    .btn-kds-signout {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.42rem 0.85rem;
      border-radius: 8px;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(255, 255, 255, 0.05);
      color: #C8BAAF;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-kds-signout:hover {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.35);
      color: #FCA5A5;
    }

    /* Workstation Standby Hub */
    .ws-standby-hub {
      display: flex;
      flex-direction: column;
      gap: 1.15rem;
      margin: auto 0;
      padding: 0.5rem 0;
    }
    .ws-standby-hero {
      text-align: center;
    }
    .ws-standby-icon {
      width: 58px;
      height: 58px;
      border-radius: 16px;
      background: rgba(226, 135, 67, 0.12);
      border: 1px solid rgba(226, 135, 67, 0.3);
      color: var(--kds-amber);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 0.75rem;
    }
    .ws-standby-action-card {
      background: #1C1613;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 0.95rem 1.1rem;
      display: flex;
      flex-direction: column;
      gap: 0.55rem;
      max-width: 520px;
      margin: 0 auto;
      width: 100%;
    }
    .ws-standby-action-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .ws-standby-badge {
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #C8BAAF;
    }
    .ws-standby-queue {
      font-size: 1.25rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1.1;
    }
    .ws-standby-timer {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 500;
      color: #9C8E82;
    }
    .ws-standby-summary {
      font-size: 0.84rem;
      color: #EDE3DA;
      font-weight: 600;
    }
    .ws-standby-items {
      font-size: 0.78rem;
      color: #C8BAAF;
      line-height: 1.35;
      background: rgba(255, 255, 255, 0.03);
      padding: 0.45rem 0.65rem;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Station Filter Navigation Pills */
    .kds-station-nav {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      background: rgba(0, 0, 0, 0.4);
      padding: 0.25rem 0.35rem;
      border-radius: 10px;
      border: 1px solid var(--kds-border);
      max-width: 100%;
      min-width: 0;
      flex-shrink: 1;
      overflow-x: auto;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }
    .kds-station-nav::-webkit-scrollbar {
      display: none;
    }
    .station-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.32rem 0.7rem;
      border-radius: 7px;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid transparent;
      background: transparent;
      color: var(--kds-muted);
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .station-pill:hover {
      color: var(--kds-text);
      background: rgba(255, 255, 255, 0.05);
    }
    .station-pill.active {
      background: rgba(226, 135, 67, 0.18);
      border-color: rgba(226, 135, 67, 0.45);
      color: #FFF;
      box-shadow: 0 2px 8px rgba(226, 135, 67, 0.2);
    }
    .station-count-pill {
      font-family: var(--font-mono);
      font-size: 0.7rem;
      padding: 0.08rem 0.4rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.08);
      color: inherit;
    }
    .station-pill.active .station-count-pill {
      background: rgba(226, 135, 67, 0.35);
      color: #FFDFBA;
    }

    /* Modifiers & Subtle Spec Tags */
    .mod-badge-oat,
    .mod-badge-almond,
    .mod-badge-soy,
    .mod-badge-coconut {
      background: rgba(255, 255, 255, 0.06) !important;
      color: #D6C7BC !important;
      font-weight: 500 !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      letter-spacing: 0.01em;
    }

    /* Urgency Timers */
    .ticket-timer.warning {
      color: var(--kds-amber) !important;
      font-weight: 600;
      background: rgba(226, 135, 67, 0.1);
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      border: 1px solid rgba(226, 135, 67, 0.2);
    }
    .ticket-timer.urgent {
      color: #E07A5F !important;
      font-weight: 600;
      background: rgba(224, 122, 95, 0.12) !important;
      padding: 0.12rem 0.45rem;
      border-radius: 4px;
      border: 1px solid rgba(224, 122, 95, 0.25);
      box-shadow: none;
    }

    /* Station Tag on Ticket & Recipe */
    .station-tag {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      padding: 0.1rem 0.35rem;
      border-radius: 4px;
      letter-spacing: 0.04em;
    }
    .station-tag.station-barista {
      background: rgba(226, 135, 67, 0.15);
      color: #FDBA74;
      border: 1px solid rgba(226, 135, 67, 0.3);
    }
    .station-tag.station-kitchen {
      background: rgba(16, 185, 129, 0.15);
      color: #6EE7B7;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    /* Bump Mode Switch Button */
    .bump-mode-btn-switch {
      font-size: 0.72rem;
      font-weight: 600;
      color: #D6C7BC;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 0.22rem 0.6rem;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      text-decoration: none;
      line-height: 1.2;
    }
    .bump-mode-btn-switch:hover {
      background: rgba(226, 135, 67, 0.18);
      border-color: rgba(226, 135, 67, 0.45);
      color: #FFF;
    }
    .bump-mode-btn-switch svg {
      color: var(--kds-amber);
      flex-shrink: 0;
    }

    .btn-fast-bump {
      background: #059669;
      color: #FFF;
      box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
      font-weight: 700;
      font-size: 0.92rem;
    }
    .btn-fast-bump:hover {
      background: #10B981;
    }

    .btn-action-void {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #FCA5A5;
      font-size: 0.82rem;
      font-weight: 600;
      padding: 0.5rem 0.85rem;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: all 0.2s ease;
      min-height: 44px;
      flex-shrink: 0;
    }
    .btn-action-void:hover {
      background: rgba(239, 68, 68, 0.25);
      border-color: #EF4444;
      color: #FFF;
    }

    /* Modals & Dialogs (History & Void & PIN) */
    .kds-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.82);
      backdrop-filter: blur(8px);
      z-index: 100;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .kds-modal-overlay.active {
      display: flex;
    }
    .kds-modal-card {
      background: #1A1410;
      border: 1px solid var(--kds-border);
      border-radius: 16px;
      max-width: 680px;
      width: 100%;
      max-height: 88vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
      overflow: hidden;
      animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.96); }
      to { opacity: 1; transform: scale(1); }
    }
    .kds-modal-head {
      padding: 1rem 1.35rem;
      background: rgba(0, 0, 0, 0.3);
      border-bottom: 1px solid var(--kds-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .kds-modal-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: #FFF;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .kds-modal-close {
      background: rgba(255, 255, 255, 0.08);
      border: none;
      color: var(--kds-muted);
      border-radius: 8px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1.1rem;
    }
    .kds-modal-close:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #FFF;
    }
    .kds-modal-tabs {
      display: flex;
      border-bottom: 1px solid var(--kds-border);
      background: rgba(0, 0, 0, 0.25);
    }
    .kds-modal-tab {
      flex: 1;
      padding: 0.75rem;
      text-align: center;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--kds-muted);
      background: transparent;
      border: none;
      border-bottom: 2px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .kds-modal-tab.active {
      color: var(--kds-amber);
      border-bottom-color: var(--kds-amber);
      background: rgba(226, 135, 67, 0.08);
    }
    .kds-modal-body {
      flex: 1;
      overflow-y: auto;
      padding: 1rem 1.35rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    .history-ticket-card {
      background: #241C17;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 0.85rem 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
    }
    .history-card-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .history-card-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-recall {
      background: rgba(226, 135, 67, 0.18);
      border: 1px solid rgba(226, 135, 67, 0.4);
      color: #FFDFBA;
      font-size: 0.78rem;
      font-weight: 600;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: all 0.15s ease;
    }
    .btn-recall:hover {
      background: var(--kds-amber);
      color: #000;
    }
    .void-reason-pill {
      font-size: 0.72rem;
      font-weight: 700;
      background: rgba(239, 68, 68, 0.18);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #FCA5A5;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
    }

    /* Void Sheet Reasons Grid */
    .void-reasons-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
      margin: 1rem 0;
    }
    @media (max-width: 520px) {
      .void-reasons-grid {
        grid-template-columns: 1fr;
      }
    }
    .btn-void-reason {
      background: #221A15;
      border: 1px solid rgba(239, 68, 68, 0.25);
      border-radius: 10px;
      padding: 0.95rem;
      text-align: left;
      cursor: pointer;
      transition: all 0.2s ease;
      color: #FFF;
    }
    .btn-void-reason:hover {
      background: rgba(239, 68, 68, 0.18);
      border-color: #EF4444;
      transform: translateY(-1px);
    }
    .btn-void-reason strong {
      display: block;
      font-size: 0.9rem;
      margin-bottom: 0.2rem;
      color: #FCA5A5;
    }
    .btn-void-reason span {
      font-size: 0.75rem;
      color: var(--kds-muted);
    }

    /* Supervisor PIN Keypad */
    .pin-input-display {
      font-family: var(--font-mono);
      font-size: 1.85rem;
      letter-spacing: 0.35em;
      text-align: center;
      padding: 0.65rem;
      background: #0D0A08;
      border: 1.5px solid var(--kds-border);
      border-radius: 10px;
      color: var(--kds-amber);
      margin: 1rem 0;
    }
    .pin-keypad {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.65rem;
      max-width: 280px;
      margin: 0 auto;
    }
    .pin-key {
      background: #241C17;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      font-size: 1.25rem;
      font-family: var(--font-mono);
      font-weight: 700;
      color: #FFF;
      padding: 0.75rem 0;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .pin-key:hover {
      background: rgba(226, 135, 67, 0.2);
      border-color: var(--kds-amber);
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
        if (data.user.role === 'staff') {
          document.body.classList.add('is-staff');
          document.body.setAttribute('data-role', 'staff');
        }
        
        window.handleKdsSignOut = async function() {
          try {
            await fetch('api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
          } catch(e) {}
          localStorage.removeItem('becoffee_demo_session');
          window.location.href = 'home.php';
        };

        function setupKdsHeader() {
          var staffPill = document.getElementById('kdsStaffPill');
          if (staffPill) {
            staffPill.innerHTML = `
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--kds-amber); flex-shrink: 0;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <span>${data.user.name}</span>
            `;
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
<body class="<?= !empty($isStaff) ? 'is-staff' : '' ?>" data-role="<?= htmlspecialchars($currentUser['role'] ?? '') ?>">

  <!-- KDS Header Bar -->
  <header class="kds-header">
    <div class="kds-brand">
      <div class="kds-logo">B</div>
      <div class="kds-title-group">
        <h1>BeCoffee · Kitchen & Barista KDS</h1>
        <div class="kds-subtitle">Live Orders & Station Preparation</div>
      </div>
    </div>

    <!-- Station Routing Filter Navigation -->
    <nav class="kds-station-nav" role="tablist" aria-label="Station Filter">
      <button type="button" class="station-pill active" data-station="all" id="stationPillAll" role="tab" aria-selected="true" title="View all beverage and food orders">
        <span>All Stations</span>
        <span class="station-count-pill" id="stationCountAll">0</span>
      </button>
      <button type="button" class="station-pill" data-station="barista" id="stationPillBarista" role="tab" aria-selected="false" title="View espresso and beverage drink orders">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        <span>Barista (Drinks)</span>
        <span class="station-count-pill" id="stationCountBarista">0</span>
      </button>
      <button type="button" class="station-pill" data-station="kitchen" id="stationPillKitchen" role="tab" aria-selected="false" title="View food prep, sandwiches, and pastries">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>
        <span>Kitchen (Food)</span>
        <span class="station-count-pill" id="stationCountKitchen">0</span>
      </button>
    </nav>

    <div class="kds-actions">
      <button type="button" class="kds-btn" id="kdsHistoryBtn" title="Recall completed orders or view voided tickets">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l4 2"></path></svg>
        <span>Recall / History</span>
      </button>
      <div class="kds-clock" id="kdsClock">00:00:00</div>
      <div class="kds-staff-pill" id="kdsStaffPill">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--kds-amber); flex-shrink: 0;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        <span>Barista</span>
      </div>
      <button type="button" class="kds-btn sound-btn active" id="kdsSoundToggle" title="Toggle audio chime alert on new incoming tickets">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
        <span id="soundLabel">Chime: ON</span>
      </button>
      <button type="button" class="kds-btn btn-kds-signout" id="kdsSignOutBtn" onclick="handleKdsSignOut()" title="Sign out of Kitchen Screen">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        <span>Sign Out</span>
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
        <div class="kds-empty-notice">No pending orders right now.</div>
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
        <div class="kds-empty-notice">No orders in progress right now.</div>
      </div>
    </section>

    <!-- Column 3: Workstation & Order Checklist -->
    <section class="kds-workstation" id="kdsWorkstation">
      <div class="ws-header">
        <div class="ws-header-grid">
          <!-- Left Column: Order Identity & Metadata -->
          <div class="ws-identity-col">
            <div class="ws-queue-badge" id="wsQueueBadge" style="display: none;"></div>
            <div class="ws-identity-text">
              <h2 class="ws-customer-name" id="wsCustomerName">Workstation Ready</h2>
              <div class="ws-meta-row" id="wsMetaRow">
                <div class="ws-header-tags" id="wsHeaderTags" style="display: none;"></div>
                <span class="ws-order-meta-sep" id="wsMetaSep" style="display: none;">•</span>
                <span class="ws-order-time" id="wsOrderRef">Select any order from the queue to start preparing</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Price & Mode Switch -->
          <div class="ws-ops-col" id="wsOpsCol">
            <div class="ws-price-badge" id="wsPriceBadge" style="display: none;">
              <span class="ws-pay-method" id="wsPayMethod">Cash</span>
              <span class="ws-price-val" id="wsPriceVal">₱0.00</span>
            </div>
            <div class="ws-mode-switch-container" id="wsModeSwitchContainer"></div>
          </div>
        </div>
      </div>

      <div class="ws-body" id="wsBody">
        <div class="ws-standby-hub">
          <div class="ws-standby-hero">
            <div class="ws-standby-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #FFF; margin-bottom: 0.25rem;">Kitchen Ready</h3>
            <p style="font-size: 0.82rem; color: var(--kds-muted); max-width: 380px; margin: 0 auto;">
              Connecting to live queue...
            </p>
          </div>
        </div>
      </div>

      <div class="ws-footer" id="wsFooter" style="display: none;">
        <!-- Dynamic Action Button: Start or Complete -->
      </div>
    </section>

  </main>

  <!-- Modal 1: Recall & Order History / Queue Tools Modal -->
  <div class="kds-modal-overlay" id="kdsHistoryModal" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
    <div class="kds-modal-card">
      <div class="kds-modal-head">
        <div class="kds-modal-title" id="historyModalTitle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color: var(--kds-amber);"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l4 2"></path></svg>
          <span>Ticket History & Queue Tools</span>
        </div>
        <button type="button" class="kds-modal-close" id="btnCloseHistoryModal" aria-label="Close dialog">&times;</button>
      </div>

      <div class="kds-modal-tabs" role="tablist">
        <button type="button" class="kds-modal-tab active" id="tabBtnCompleted" role="tab">
          Completed (<span id="tabCountCompleted">0</span>)
        </button>
        <button type="button" class="kds-modal-tab" id="tabBtnVoided" role="tab">
          Voided / Cancelled (<span id="tabCountVoided">0</span>)
        </button>
        <button type="button" class="kds-modal-tab" id="tabBtnReset" role="tab">
          🔒 Queue Reset
        </button>
      </div>

      <div class="kds-modal-body" id="historyModalContent">
        <!-- Dynamic: completed list, voided list, or pin reset -->
      </div>
    </div>
  </div>

  <!-- Modal 2: 1-Tap Void Reason Sheet -->
  <div class="kds-modal-overlay" id="kdsVoidModal" role="dialog" aria-modal="true" aria-labelledby="voidModalTitle">
    <div class="kds-modal-card" style="max-width: 520px;">
      <div class="kds-modal-head">
        <div class="kds-modal-title" id="voidModalTitle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
          <span>Void Ticket <span id="voidOrderQueueBadge" style="color: var(--kds-amber); font-family: var(--font-mono);">#--</span></span>
        </div>
        <button type="button" class="kds-modal-close" id="btnCloseVoidModal" aria-label="Close dialog">&times;</button>
      </div>
      <div class="kds-modal-body">
        <p style="font-size: 0.84rem; color: var(--kds-muted); margin-bottom: 0.5rem;">
          Select a 1-tap cancellation reason. The ticket will be archived in the <strong>Voided History</strong> tab with an audit trail:
        </p>
        <div class="void-reasons-grid">
          <button type="button" class="btn-void-reason" data-reason="Customer Walkout / Cancelled">
            <strong>🚶 Customer Cancelled</strong>
            <span>Customer left or cancelled ticket</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Duplicate Cashier Ring">
            <strong>🔁 Duplicate Ring</strong>
            <span>Accidentally rung up twice</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Out of Stock / 86'd">
            <strong>🚫 Out of Stock</strong>
            <span>Milk, beans, or food item 86'd</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Staff Mistake / Wrong Item">
            <strong>⚠️ Mistake / Wrong Item</strong>
            <span>Incorrect item or customization entered</span>
          </button>
        </div>
        <button type="button" class="kds-btn" id="btnCancelVoidModal" style="width: 100%; justify-content: center; padding: 0.65rem;">
          Keep Order (Dismiss)
        </button>
      </div>
    </div>
  </div>

  <script src="js/system-dialog.js"></script>
  <script>
    // =========================================================================
    // BeCoffee KDS Engine & Barista Production Rush System
    // =========================================================================
    (function initKdsEngine() {
      var audioCtx = null;
      var soundEnabled = true;
      var lastPendingIds = new Set();
      var selectedOrderId = null;
      var activeOrdersMap = new Map();
      var lastData = null;
      var stationFilter = localStorage.getItem('kds_station_filter') || 'all';
      var bumpMode = localStorage.getItem('kds_bump_mode') || 'fast'; // 'fast' | 'qa'
      var currentVoidOrderId = null;
      var currentHistoryTab = 'completed';
      var enteredPin = '';

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

      // Station Navigation Buttons
      document.querySelectorAll('.station-pill').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var target = btn.getAttribute('data-station');
          stationFilter = target;
          localStorage.setItem('kds_station_filter', target);
          document.querySelectorAll('.station-pill').forEach(function(p) {
            var isActive = (p.getAttribute('data-station') === target);
            p.classList.toggle('active', isActive);
            p.setAttribute('aria-selected', isActive ? 'true' : 'false');
          });
          if (lastData) {
            renderBoard(lastData);
          }
        });
      });

      // Restore saved station filter tab
      var activePill = document.querySelector(`.station-pill[data-station="${stationFilter}"]`);
      if (activePill) {
        document.querySelectorAll('.station-pill').forEach(function(p) { p.classList.remove('active'); });
        activePill.classList.add('active');
      }

      // Format elapsed time string
      function formatElapsed(seconds) {
        if (seconds < 60) return `${Math.max(1, Math.floor(seconds))}s`;
        var mins = Math.floor(seconds / 60);
        if (mins < 60) return `${mins}m`;
        var hours = Math.floor(mins / 60);
        var remMins = mins % 60;
        return `${hours}h ${remMins}m`;
      }

      // Urgency Timer Classification: Normal (<4m), Amber Warning (4-7m), Urgent Red Flash (7m+)
      function getTimerClass(seconds) {
        if (seconds >= 420) return 'ticket-timer urgent';
        if (seconds >= 240) return 'ticket-timer warning';
        return 'ticket-timer';
      }

      // Helper to generate high-contrast badge for milk alternatives
      function getMilkBadgeHtml(milkOption) {
        if (!milkOption || milkOption === 'Regular Milk') return '';
        var lower = milkOption.toLowerCase();
        if (lower.includes('oat')) {
          return `<span class="spec-tag mod-badge-oat">🥛 Oat Milk</span>`;
        }
        if (lower.includes('almond')) {
          return `<span class="spec-tag mod-badge-almond">🥛 Almond</span>`;
        }
        if (lower.includes('soy')) {
          return `<span class="spec-tag mod-badge-soy">🥛 Soy Milk</span>`;
        }
        if (lower.includes('coconut')) {
          return `<span class="spec-tag mod-badge-coconut">🥥 Coconut</span>`;
        }
        return `<span class="spec-tag mod-badge-oat">${milkOption}</span>`;
      }

      // Helper for customer preparation notes (clean and neutral)
      function getNotesBadgeHtml(customNotes) {
        if (!customNotes) return '';
        return `<div class="recipe-notes-callout">Note: "${customNotes}"</div>`;
      }

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

          lastData = data;
          renderBoard(data);
        } catch (err) {
          console.warn('KDS polling error:', err);
        }
      }

      // Render Multi-Column Board and Station Counts
      function renderBoard(data) {
        var rawPending = data.pending || [];
        var rawInProgress = data.in_progress || [];

        // Check for newly arrived pending orders to trigger chime
        var currentPendingIds = new Set(rawPending.map(function(o) { return o.id; }));
        var hasNewPending = false;
        currentPendingIds.forEach(function(id) {
          if (!lastPendingIds.has(id)) {
            hasNewPending = true;
          }
        });
        lastPendingIds = currentPendingIds;
        if (hasNewPending && rawPending.length > 0) {
          playCafeChime();
        }

        // Cache all active orders in memory map
        activeOrdersMap.clear();
        rawPending.forEach(function(o) { activeOrdersMap.set(o.id, o); });
        rawInProgress.forEach(function(o) { activeOrdersMap.set(o.id, o); });

        // Calculate Station Counts across all active tickets
        var totalActive = rawPending.length + rawInProgress.length;
        var baristaActive = 0;
        var kitchenActive = 0;

        var allActive = rawPending.concat(rawInProgress);
        allActive.forEach(function(o) {
          var hasBarista = (o.station === 'barista' || o.station === 'both');
          var hasKitchen = (o.station === 'kitchen' || o.station === 'both');
          if (hasBarista) baristaActive++;
          if (hasKitchen) kitchenActive++;
        });

        document.getElementById('stationCountAll').textContent = totalActive;
        document.getElementById('stationCountBarista').textContent = baristaActive;
        document.getElementById('stationCountKitchen').textContent = kitchenActive;

        // Apply Station Filter
        var pending = rawPending.filter(function(o) {
          if (stationFilter === 'barista') return (o.station === 'barista' || o.station === 'both');
          if (stationFilter === 'kitchen') return (o.station === 'kitchen' || o.station === 'both');
          return true;
        });

        var inProgress = rawInProgress.filter(function(o) {
          if (stationFilter === 'barista') return (o.station === 'barista' || o.station === 'both');
          if (stationFilter === 'kitchen') return (o.station === 'kitchen' || o.station === 'both');
          return true;
        });

        // Update column counts
        document.getElementById('pendingCount').textContent = pending.length;
        document.getElementById('inProgressCount').textContent = inProgress.length;

        // Render Pending Column
        var pendingList = document.getElementById('pendingTicketList');
        if (pending.length === 0) {
          var filterNote = (stationFilter !== 'all') ? ` for ${stationFilter === 'barista' ? 'Barista' : 'Kitchen'}` : '';
          pendingList.innerHTML = `<div class="kds-empty-notice">No pending orders${filterNote} right now.</div>`;
        } else {
          pendingList.innerHTML = pending.map(function(ord) {
            return buildTicketCardHtml(ord);
          }).join('');
        }

        // Render In Progress Column
        var progressList = document.getElementById('inProgressTicketList');
        if (inProgress.length === 0) {
          var filterNote2 = (stationFilter !== 'all') ? ` for ${stationFilter === 'barista' ? 'Barista' : 'Kitchen'}` : '';
          progressList.innerHTML = `<div class="kds-empty-notice">No orders in progress${filterNote2} right now.</div>`;
        } else {
          progressList.innerHTML = inProgress.map(function(ord) {
            return buildTicketCardHtml(ord);
          }).join('');
        }

        // Attach Card Click Handlers
        document.querySelectorAll('.kds-ticket-card').forEach(function(card) {
          card.addEventListener('click', function() {
            var id = parseInt(card.getAttribute('data-order-id'), 10);
            selectOrder(id);
          });
        });

        // Re-render Workstation if selected order is active
        if (selectedOrderId && activeOrdersMap.has(selectedOrderId)) {
          renderWorkstation(activeOrdersMap.get(selectedOrderId));
        } else {
          clearWorkstation();
        }
      }

      // Build HTML for a single Ticket Card on the rail (BARE MINIMUM)
      function buildTicketCardHtml(ord) {
        var isSelected = (ord.id === selectedOrderId);
        var timerClass = getTimerClass(ord.elapsed_seconds || 0);

        var totalQty = (ord.items || []).reduce(function(sum, it) {
          return sum + (parseInt(it.quantity, 10) || 1);
        }, 0);

        var originClass = 'origin-qr';
        var originText = 'QR / LINK';
        if (ord.order_source === 'online') {
          originClass = 'origin-online';
          originText = 'ONLINE';
        } else if (ord.order_source === 'registrar') {
          originClass = 'origin-registrar';
          originText = ord.table_number ? `REGISTRAR · T#${ord.table_number}` : 'REGISTRAR';
        } else {
          originClass = 'origin-qr';
          originText = ord.table_number ? `QR / LINK · T#${ord.table_number}` : 'QR / LINK';
        }

        var stationLabel = ord.station === 'barista'
          ? 'Barista'
          : (ord.station === 'kitchen' ? 'Kitchen' : 'Barista & Kitchen');

        return `
          <div class="kds-ticket-card ${isSelected ? 'selected' : ''}" data-order-id="${ord.id}">
            <div class="ticket-head-row">
              <span class="ticket-queue-num">#${ord.queue_number}</span>
              <span class="${timerClass}">${formatElapsed(ord.elapsed_seconds || 0)}</span>
            </div>
            <div class="ticket-origin-row">
              <span class="origin-badge ${originClass}">${originText}</span>
            </div>
            <div class="ticket-summary-row">
              <span class="ticket-summary-count">${totalQty} ${totalQty === 1 ? 'item' : 'items'}</span>
              <span class="ticket-summary-station">${stationLabel}</span>
            </div>
          </div>
        `;
      }

      function selectOrder(orderId) {
        selectedOrderId = orderId;
        document.querySelectorAll('.kds-ticket-card').forEach(function(c) {
          c.classList.toggle('selected', parseInt(c.getAttribute('data-order-id'), 10) === orderId);
        });
        if (activeOrdersMap.has(orderId)) {
          renderWorkstation(activeOrdersMap.get(orderId));
        }
      }

      function clearWorkstation() {
        selectedOrderId = null;
        var badge = document.getElementById('wsQueueBadge');
        if (badge) {
          badge.textContent = '';
          badge.style.display = 'none';
        }
        var tags = document.getElementById('wsHeaderTags');
        if (tags) {
          tags.innerHTML = '';
          tags.style.display = 'none';
        }

        var priceBadge = document.getElementById('wsPriceBadge');
        if (priceBadge) priceBadge.style.display = 'none';

        var modeContainer = document.getElementById('wsModeSwitchContainer');
        if (modeContainer) modeContainer.innerHTML = '';

        var sep = document.getElementById('wsMetaSep');
        if (sep) sep.style.display = 'none';

        document.getElementById('wsCustomerName').textContent = 'Workstation Ready';
        document.getElementById('wsOrderRef').textContent = 'Select any order from the queue to start preparing';

        var pendingOrders = Array.from(activeOrdersMap.values()).filter(function(o) { return o.status === 'pending'; });
        var nextPending = pendingOrders.length > 0 ? pendingOrders[0] : null;

        var quickActionHtml = '';
        if (nextPending) {
          var itemsList = nextPending.items.map(function(it) {
            return `${it.quantity}x ${it.item_name}`;
          }).join(', ');

          var isGeneric = !nextPending.customer_name || /^(guest|customer|autotest|walk-in)/i.test(nextPending.customer_name.trim());
          var destinationLabel = nextPending.order_type === 'take_out'
            ? 'Take-Out'
            : 'Table #' + (nextPending.table_number || '1');
          var destLine = isGeneric
            ? destinationLabel
            : `${nextPending.customer_name} · ${destinationLabel}`;

          quickActionHtml = `
            <div class="ws-standby-action-card">
              <div class="ws-standby-action-header">
                <div>
                  <span class="ws-standby-badge">Next Order Up</span>
                  <div class="ws-standby-queue">#${nextPending.queue_number}</div>
                </div>
                <div class="ws-standby-timer">${formatElapsed(nextPending.elapsed_seconds || 0)}</div>
              </div>
              <div class="ws-standby-summary">
                <strong>${destLine}</strong>
              </div>
              <div style="font-size: 0.8rem; color: #D6C7BC; line-height: 1.35;">${itemsList}</div>
              <button type="button" class="btn-action-primary btn-acknowledge" id="btnQuickStartNext" style="width: 100%; min-height: 44px; font-size: 0.9rem; margin-top: 0.35rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <span>Start Preparing #${nextPending.queue_number}</span>
              </button>
            </div>
          `;
        } else {
          quickActionHtml = `
            <div class="ws-standby-action-card" style="text-align: center; border-style: dashed; padding: 1.5rem 1rem;">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2" style="margin-bottom: 0.4rem;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
              <div style="font-weight: 700; color: #FFF; font-size: 0.95rem; margin-bottom: 0.2rem;">All caught up!</div>
              <div style="font-size: 0.8rem; color: var(--kds-muted);">Kitchen queue is clear. Ready for incoming orders.</div>
            </div>
          `;
        }

        document.getElementById('wsBody').innerHTML = `
          <div class="ws-standby-hub">
            ${quickActionHtml}
          </div>
        `;
        document.getElementById('wsFooter').style.display = 'none';

        var btnQuick = document.getElementById('btnQuickStartNext');
        if (btnQuick && nextPending) {
          btnQuick.addEventListener('click', function() {
            selectOrder(nextPending.id);
          });
        }
      }

      // Render Workstation View, Recipe Cards, Fast Bump, and Void Controls
      function renderWorkstation(ord) {
        var badge = document.getElementById('wsQueueBadge');
        if (badge) {
          badge.textContent = `#${ord.queue_number}`;
          badge.style.display = 'flex';
        }

        var isGenericName = !ord.customer_name || /^(guest|customer|autotest|walk-in)/i.test(ord.customer_name.trim());
        var customerDisplayName = isGenericName ? 'Guest Customer' : ord.customer_name;
        var destinationLabel = (ord.order_type === 'take_out')
          ? 'Take-Out'
          : `Table #${ord.table_number || '1'}`;

        document.getElementById('wsCustomerName').textContent = customerDisplayName;
        document.getElementById('wsOrderRef').textContent = `Placed ${formatElapsed(ord.elapsed_seconds || 0)} ago`;
        var sep = document.getElementById('wsMetaSep');
        if (sep) sep.style.display = 'inline-block';

        var originClass = 'origin-qr';
        var originText = 'QR / LINK';
        if (ord.order_source === 'online') {
          originClass = 'origin-online';
          originText = 'ONLINE';
        } else if (ord.order_source === 'registrar') {
          originClass = 'origin-registrar';
          originText = 'REGISTRAR';
        } else {
          originClass = 'origin-qr';
          originText = 'QR / LINK';
        }

        var tags = document.getElementById('wsHeaderTags');
        if (tags) {
          tags.innerHTML = `
            <span class="origin-badge ${originClass}">${originText}</span>
            <span class="ws-table-badge">${destinationLabel}</span>
          `;
          tags.style.display = 'inline-flex';
        }

        // Render Price and Payment Method in wsPriceBadge
        var priceBadge = document.getElementById('wsPriceBadge');
        if (priceBadge) {
          var payMethodEl = document.getElementById('wsPayMethod');
          var priceValEl = document.getElementById('wsPriceVal');
          if (payMethodEl) payMethodEl.textContent = (ord.payment_method === 'gcash') ? 'GCash' : 'Cash';
          if (priceValEl) priceValEl.textContent = `₱${parseFloat(ord.grand_total).toFixed(2)}`;
          priceBadge.style.display = 'inline-flex';
        }

        // Render Mode Switch Button directly alongside price badge
        var modeContainer = document.getElementById('wsModeSwitchContainer');
        if (modeContainer) {
          modeContainer.innerHTML = `
            <button type="button" class="bump-mode-btn-switch" id="btnSwitchBumpMode" title="Toggle between Fast Bump and QA Checklist modes">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
              <span>${bumpMode === 'fast' ? 'Switch to QA Mode' : 'Switch to Fast Mode'}</span>
            </button>
          `;
          document.getElementById('btnSwitchBumpMode').addEventListener('click', function() {
            bumpMode = (bumpMode === 'fast') ? 'qa' : 'fast';
            localStorage.setItem('kds_bump_mode', bumpMode);
            renderWorkstation(ord);
          });
        }

        // Build Recipe Cards (Clean & Neutral, No Redundant Station Badges)
        var recipesHtml = ord.items.map(function(it) {
          var specs = [];
          var isFood = (it.station === 'kitchen');

          // Drinks receive drink modifiers (temperature, milk, sweetness)
          if (!isFood) {
            if (it.temperature) specs.push(`<span class="spec-tag">${it.temperature}</span>`);
            if (it.milk_option) {
              specs.push(getMilkBadgeHtml(it.milk_option) || `<span class="spec-tag">${it.milk_option}</span>`);
            }
            if (it.sweetness_level) specs.push(`<span class="spec-tag">${it.sweetness_level}</span>`);
          }

          var notesHtml = getNotesBadgeHtml(it.custom_notes);

          return `
            <div class="recipe-card">
              <div class="recipe-title-row">
                <span class="recipe-name">${it.item_name}</span>
                <span class="recipe-qty-pill">${it.quantity}x</span>
              </div>
              ${specs.length > 0 ? `<div class="recipe-specs-tags">${specs.join('')}</div>` : ''}
              ${notesHtml}
            </div>
          `;
        }).join('');

        var wsBody = document.getElementById('wsBody');
        var footer = document.getElementById('wsFooter');
        footer.style.display = 'flex';

        // PENDING STATE
        if (ord.status === 'pending') {
          wsBody.innerHTML = `
            <div>
              <div class="recipe-list-heading">Items to Prepare</div>
              <div class="recipe-list-items">
                ${recipesHtml}
              </div>
            </div>
          `;

          footer.innerHTML = `
            <button type="button" class="btn-action-void" id="btnVoidTicket" title="Cancel or void ticket with 1-tap reason">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
              <span>Void Ticket</span>
            </button>
            <button type="button" class="btn-action-primary btn-acknowledge" id="btnAcknowledge" style="flex: 1;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              <span>Start Preparing #${ord.queue_number}</span>
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
              await SystemDialog.alert('Unable to start order: ' + e.message, { title: 'Order Notice', type: 'danger' });
            }
          });

          document.getElementById('btnVoidTicket').addEventListener('click', function() {
            openVoidModal(ord);
          });

        } else if (ord.status === 'in_progress') {
          // IN PROGRESS STATE: Support Fast Bump (Rush Mode) & 3-Step QA Mode
          var payLabel = (ord.payment_method === 'gcash')
            ? `Payment verified (GCash · ₱${parseFloat(ord.grand_total).toFixed(2)})`
            : `Cash payment received at counter (₱${parseFloat(ord.grand_total).toFixed(2)})`;

          var customList = [];
          ord.items.forEach(function(it) {
            if (it.milk_option && it.milk_option !== 'Regular Milk') customList.push(it.milk_option);
            if (it.sweetness_level && it.sweetness_level !== 'Normal (100%)') customList.push(it.sweetness_level);
            if (it.custom_notes) customList.push(it.custom_notes);
          });
          var customLabel = customList.length > 0
            ? `Customizations followed (${customList.join(', ')})`
            : `Standard recipe followed`;

          var packLabel = (ord.order_type === 'take_out')
            ? `Packed for take-out (Lids sealed, straw included)`
            : `Ready for Table #${ord.table_number || '1'} (Glassware & tray prepared)`;

          var qaBoxHtml = '';
          if (bumpMode === 'qa') {
            qaBoxHtml = `
              <div class="qa-lockout-box">
                <div class="qa-header-row">
                  <div class="qa-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="qa-checklist-icon"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1 2-2h11"></path></svg>
                    Preparation Checklist
                  </div>
                  <span class="qa-gate-status" id="qaStatusNotice">0 of 3 completed</span>
                </div>
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
          }

          wsBody.innerHTML = `
            <div>
              <div class="recipe-list-heading">Items to Prepare</div>
              <div class="recipe-list-items">
                ${recipesHtml}
              </div>
            </div>
            ${qaBoxHtml}
          `;

          // Footer buttons
          if (bumpMode === 'fast') {
            footer.innerHTML = `
              <button type="button" class="btn-action-void" id="btnVoidTicket" title="Cancel or void ticket with 1-tap reason">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>Void</span>
              </button>
              <button type="button" class="btn-action-primary btn-fast-bump" id="btnFastBumpOrder" style="flex: 1;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>⚡ Fast Bump Order #${ord.queue_number}</span>
              </button>
            `;

            document.getElementById('btnFastBumpOrder').addEventListener('click', async function() {
              var btn = this;
              btn.disabled = true;
              btn.innerHTML = 'Bumping...';
              try {
                var res = await fetch('api/kds.php?action=complete', {
                  method: 'PATCH',
                  credentials: 'include',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                    order_id: ord.id,
                    fast_bump: true
                  })
                });
                var result = await res.json();
                if (result.success) {
                  clearWorkstation();
                  fetchKdsQueue();
                } else {
                  await SystemDialog.alert('Unable to bump order: ' + (result.error || 'Server error'), { title: 'Bump Notice', type: 'warning' });
                  btn.disabled = false;
                  btn.innerHTML = `⚡ Fast Bump Order #${ord.queue_number}`;
                }
              } catch (err) {
                await SystemDialog.alert('Error completing order: ' + err.message, { title: 'Completion Error', type: 'danger' });
                btn.disabled = false;
                btn.innerHTML = `⚡ Fast Bump Order #${ord.queue_number}`;
              }
            });

          } else {
            // QA Checklist Mode Footer
            footer.innerHTML = `
              <button type="button" class="btn-action-void" id="btnVoidTicket" title="Cancel or void ticket with 1-tap reason">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>Void</span>
              </button>
              <button type="button" class="btn-action-primary locked" id="btnCompleteOrder" style="flex: 1;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span id="completeBtnText">Complete Order (3 checks remaining)</span>
              </button>
            `;

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
                statusNotice.textContent = '3 of 3 ready';
                statusNotice.classList.add('ready');
                completeBtnText.textContent = `Complete Order #${ord.queue_number} & Notify Customer`;
              } else {
                completeBtn.classList.add('locked');
                completeBtn.classList.remove('unlocked');
                var remaining = 3 - total;
                statusNotice.textContent = `${total} of 3 completed`;
                statusNotice.classList.remove('ready');
                completeBtnText.textContent = `Complete Order (${remaining} ${remaining === 1 ? 'check' : 'checks'} remaining)`;
              }
            }

            checkPay.addEventListener('change', evaluateQaLockout);
            checkCustom.addEventListener('change', evaluateQaLockout);
            checkPack.addEventListener('change', evaluateQaLockout);

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
                  await SystemDialog.alert('Please complete all 3 preparation checks.', { title: 'Checklist Incomplete', type: 'warning' });
                  completeBtn.disabled = false;
                }
              } catch (err) {
                await SystemDialog.alert('Error completing order: ' + err.message, { title: 'Completion Error', type: 'danger' });
                completeBtn.disabled = false;
              }
            });
          }

          document.getElementById('btnVoidTicket').addEventListener('click', function() {
            openVoidModal(ord);
          });
        }
      }

      // =========================================================================
      // 1-Tap Void Ticket Reason Modal
      // =========================================================================
      var voidModal = document.getElementById('kdsVoidModal');
      function openVoidModal(ord) {
        currentVoidOrderId = ord.id;
        document.getElementById('voidOrderQueueBadge').textContent = `#${ord.queue_number}`;
        voidModal.classList.add('active');
      }

      function closeVoidModal() {
        voidModal.classList.remove('active');
        currentVoidOrderId = null;
      }

      document.getElementById('btnCloseVoidModal').addEventListener('click', closeVoidModal);
      document.getElementById('btnCancelVoidModal').addEventListener('click', closeVoidModal);

      document.querySelectorAll('.btn-void-reason').forEach(function(btn) {
        btn.addEventListener('click', async function() {
          var targetOrderId = currentVoidOrderId || selectedOrderId;
          if (!targetOrderId) return;
          var reason = btn.getAttribute('data-reason');
          closeVoidModal();

          try {
            var res = await fetch(`api/kds.php?action=cancel&order_id=${targetOrderId}`, {
              method: 'PATCH',
              credentials: 'include',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                order_id: targetOrderId,
                reason: reason
              })
            });
            var result = await res.json();
            if (result.success) {
              clearWorkstation();
              fetchKdsQueue();
            } else {
              await SystemDialog.alert('Unable to void ticket: ' + (result.error || 'Server error'), { title: 'Void Notice', type: 'warning' });
            }
          } catch (e) {
            await SystemDialog.alert('Error voiding order: ' + e.message, { title: 'Void Error', type: 'danger' });
          }
        });
      });

      // =========================================================================
      // Recall & Order History / Queue Reset Tools Modal
      // =========================================================================
      var historyModal = document.getElementById('kdsHistoryModal');
      var historyBtn = document.getElementById('kdsHistoryBtn');
      var tabBtnCompleted = document.getElementById('tabBtnCompleted');
      var tabBtnVoided = document.getElementById('tabBtnVoided');
      var tabBtnReset = document.getElementById('tabBtnReset');
      var historyContent = document.getElementById('historyModalContent');

      historyBtn.addEventListener('click', function() {
        historyModal.classList.add('active');
        switchHistoryTab('completed');
      });

      document.getElementById('btnCloseHistoryModal').addEventListener('click', function() {
        historyModal.classList.remove('active');
      });

      tabBtnCompleted.addEventListener('click', function() { switchHistoryTab('completed'); });
      tabBtnVoided.addEventListener('click', function() { switchHistoryTab('voided'); });
      tabBtnReset.addEventListener('click', function() { switchHistoryTab('reset'); });

      async function switchHistoryTab(tab) {
        currentHistoryTab = tab;
        tabBtnCompleted.classList.toggle('active', tab === 'completed');
        tabBtnVoided.classList.toggle('active', tab === 'voided');
        tabBtnReset.classList.toggle('active', tab === 'reset');

        if (tab === 'reset') {
          renderResetPinKeypad();
          return;
        }

        historyContent.innerHTML = '<div style="text-align: center; color: var(--kds-muted); padding: 2rem;">Loading ticket history...</div>';

        try {
          var res = await fetch('api/kds.php?view=history', { credentials: 'include' });
          var data = await res.json();
          if (!data.success) {
            historyContent.innerHTML = '<div style="color: #F87171; text-align: center; padding: 2rem;">Failed to load history</div>';
            return;
          }

          var completedList = data.completed || [];
          var voidedList = data.cancelled || [];

          document.getElementById('tabCountCompleted').textContent = completedList.length;
          document.getElementById('tabCountVoided').textContent = voidedList.length;

          if (tab === 'completed') {
            renderCompletedHistory(completedList);
          } else if (tab === 'voided') {
            renderVoidedHistory(voidedList);
          }
        } catch (e) {
          historyContent.innerHTML = `<div style="color: #F87171; text-align: center; padding: 2rem;">Error: ${e.message}</div>`;
        }
      }

      function renderCompletedHistory(list) {
        if (!list.length) {
          historyContent.innerHTML = '<div style="text-align: center; color: var(--kds-muted); padding: 2.5rem 1rem;">No completed tickets in recent history.</div>';
          return;
        }

        historyContent.innerHTML = list.map(function(ord) {
          var itemsDesc = ord.items.map(function(it) {
            var milk = (it.milk_option && it.milk_option !== 'Regular Milk') ? ` (${it.milk_option})` : '';
            return `${it.quantity}x ${it.item_name}${milk}`;
          }).join(', ');

          var typeLabel = ord.order_type === 'take_out' ? 'Take-Out' : `Table #${ord.table_number || '1'}`;

          return `
            <div class="history-ticket-card">
              <div class="history-card-head">
                <div>
                  <strong style="color: #FFF; font-size: 0.95rem; font-family: var(--font-mono);">#${ord.queue_number}</strong>
                  <span style="color: #C8BAAF; font-size: 0.8rem; margin-left: 0.4rem;">${ord.customer_name}</span>
                  <span style="color: var(--kds-muted); font-size: 0.74rem;">· ${typeLabel}</span>
                </div>
                <div class="history-card-actions">
                  <span style="font-size: 0.72rem; color: #10B981; font-weight: 600;">Completed ${formatElapsed(ord.elapsed_seconds || 0)} ago</span>
                  <button type="button" class="btn-recall" data-order-id="${ord.id}" title="Pull ticket back into active In-Progress rail">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                    <span>Recall Order</span>
                  </button>
                </div>
              </div>
              <div style="font-size: 0.78rem; color: #D6C7BC; line-height: 1.3;">
                ${itemsDesc}
              </div>
            </div>
          `;
        }).join('');

        // Attach Recall Handlers
        historyContent.querySelectorAll('.btn-recall').forEach(function(btn) {
          btn.addEventListener('click', async function() {
            var orderId = parseInt(btn.getAttribute('data-order-id'), 10);
            btn.disabled = true;
            btn.innerHTML = 'Recalling...';
            try {
              var res = await fetch('api/kds.php?action=recall', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId })
              });
              var result = await res.json();
              if (result.success) {
                historyModal.classList.remove('active');
                await fetchKdsQueue();
                selectOrder(orderId);
              } else {
                await SystemDialog.alert('Unable to recall ticket: ' + (result.error || 'Server error'), { title: 'Recall Notice', type: 'warning' });
                btn.disabled = false;
                btn.innerHTML = 'Recall Order';
              }
            } catch (err) {
              await SystemDialog.alert('Recall error: ' + err.message, { title: 'Error', type: 'danger' });
              btn.disabled = false;
              btn.innerHTML = 'Recall Order';
            }
          });
        });
      }

      function renderVoidedHistory(list) {
        if (!list.length) {
          historyContent.innerHTML = '<div style="text-align: center; color: var(--kds-muted); padding: 2.5rem 1rem;">No voided or cancelled tickets in recent history.</div>';
          return;
        }

        historyContent.innerHTML = list.map(function(ord) {
          var itemsDesc = ord.items.map(function(it) {
            return `${it.quantity}x ${it.item_name}`;
          }).join(', ');

          var typeLabel = ord.order_type === 'take_out' ? 'Take-Out' : `Table #${ord.table_number || '1'}`;
          var voidReason = ord.void_reason || 'Cancelled';

          return `
            <div class="history-ticket-card">
              <div class="history-card-head">
                <div>
                  <strong style="color: #F87171; font-size: 0.95rem; font-family: var(--font-mono);">#${ord.queue_number}</strong>
                  <span style="color: #C8BAAF; font-size: 0.8rem; margin-left: 0.4rem;">${ord.customer_name}</span>
                  <span style="color: var(--kds-muted); font-size: 0.74rem;">· ${typeLabel}</span>
                </div>
                <div class="history-card-actions">
                  <span class="void-reason-pill">VOID: ${voidReason}</span>
                  <button type="button" class="btn-recall" data-order-id="${ord.id}" title="Restore ticket back to queue">
                    <span>Restore</span>
                  </button>
                </div>
              </div>
              <div style="font-size: 0.78rem; color: #D6C7BC; line-height: 1.3;">
                ${itemsDesc}
              </div>
            </div>
          `;
        }).join('');

        historyContent.querySelectorAll('.btn-recall').forEach(function(btn) {
          btn.addEventListener('click', async function() {
            var orderId = parseInt(btn.getAttribute('data-order-id'), 10);
            try {
              var res = await fetch('api/kds.php?action=recall', {
                method: 'PATCH',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId })
              });
              var result = await res.json();
              if (result.success) {
                historyModal.classList.remove('active');
                await fetchKdsQueue();
                selectOrder(orderId);
              }
            } catch (e) {}
          });
        });
      }

      function renderResetPinKeypad() {
        enteredPin = '';
        historyContent.innerHTML = `
          <div style="text-align: center; max-width: 320px; margin: 0 auto;">
            <p style="font-size: 0.84rem; color: #FCA5A5; margin-bottom: 0.5rem; line-height: 1.35;">
              <strong>Supervisor PIN Required:</strong> Full queue reset is protected to prevent accidental wipes during rush. Enter PIN (Default 1234):
            </p>
            <div class="pin-input-display" id="resetPinDisplay">----</div>
            <div class="pin-keypad">
              <button type="button" class="pin-key" data-digit="1">1</button>
              <button type="button" class="pin-key" data-digit="2">2</button>
              <button type="button" class="pin-key" data-digit="3">3</button>
              <button type="button" class="pin-key" data-digit="4">4</button>
              <button type="button" class="pin-key" data-digit="5">5</button>
              <button type="button" class="pin-key" data-digit="6">6</button>
              <button type="button" class="pin-key" data-digit="7">7</button>
              <button type="button" class="pin-key" data-digit="8">8</button>
              <button type="button" class="pin-key" data-digit="9">9</button>
              <button type="button" class="pin-key" data-digit="clr" style="font-size: 0.82rem; color: #FCA5A5;">CLR</button>
              <button type="button" class="pin-key" data-digit="0">0</button>
              <button type="button" class="pin-key" data-digit="ok" style="font-size: 0.82rem; color: #34D399; font-weight: 800;">WIPE</button>
            </div>
            <div id="pinErrorMsg" style="color: #EF4444; font-size: 0.8rem; margin-top: 0.75rem; min-height: 18px;"></div>
          </div>
        `;

        var display = document.getElementById('resetPinDisplay');
        var errEl = document.getElementById('pinErrorMsg');

        historyContent.querySelectorAll('.pin-key').forEach(function(key) {
          key.addEventListener('click', async function() {
            var d = key.getAttribute('data-digit');
            errEl.textContent = '';
            if (d === 'clr') {
              enteredPin = '';
            } else if (d === 'ok') {
              if (enteredPin.length < 4) {
                errEl.textContent = 'Please enter 4-digit PIN';
                return;
              }
              try {
                var res = await fetch('api/kds.php?action=clear_all', {
                  method: 'POST',
                  credentials: 'include',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ pin: enteredPin })
                });
                var result = await res.json();
                if (result.success) {
                  historyModal.classList.remove('active');
                  clearWorkstation();
                  await fetchKdsQueue();
                  await SystemDialog.alert('Active queue successfully cleared.', { title: 'Shift Reset', type: 'info' });
                } else {
                  errEl.textContent = result.error || 'Invalid supervisor PIN';
                  enteredPin = '';
                }
              } catch (e) {
                errEl.textContent = e.message;
              }
            } else {
              if (enteredPin.length < 6) {
                enteredPin += d;
              }
            }
            display.textContent = enteredPin ? '•'.repeat(enteredPin.length) : '----';
          });
        });
      }

      // Initial Fetch & Regular 3-second Polling
      fetchKdsQueue();
      setInterval(fetchKdsQueue, 3000);

    })();
  </script>
</body>
</html>
