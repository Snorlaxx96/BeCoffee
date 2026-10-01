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

    /* KDS Top Bar (Editorial Anti-Slop) */
    .kds-header {
      background: #171210;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.6rem 1.25rem;
      min-height: 58px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      box-sizing: border-box;
    }
    .kds-brand {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .kds-logo {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: #E28743;
      color: #FFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-weight: 700;
      font-size: 1.15rem;
      flex-shrink: 0;
    }
    .kds-title-group h1 {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.1;
      letter-spacing: -0.01em;
      margin: 0;
    }
    .kds-subtitle {
      font-size: 0.68rem;
      color: #DF9B64;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    .kds-op-cluster {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      margin-left: 0.35rem;
    }
    .kds-clock {
      font-family: var(--font-mono);
      font-size: 0.84rem;
      font-weight: 600;
      color: #D6C7BC;
      letter-spacing: 0.03em;
      background: transparent;
      border: none;
      padding: 0.35rem 0.5rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      white-space: nowrap;
    }
    .kds-actions {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .kds-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
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
      box-sizing: border-box;
      white-space: nowrap;
    }
    .kds-btn:focus-visible {
      outline: 2px solid #DF9B64;
      outline-offset: 2px;
    }
    .kds-btn svg {
      flex-shrink: 0;
      stroke: currentColor;
      transition: transform 0.15s ease;
    }
    .kds-header-divider {
      width: 1px;
      height: 16px;
      background: rgba(255, 255, 255, 0.12);
      margin: 0 0.15rem;
      flex-shrink: 0;
    }

    /* Operational Buttons */
    .sound-btn {
      color: #8E8279;
    }
    .sound-btn.active {
      color: #DF9B64;
      font-weight: 600;
    }
    .sound-btn:hover {
      color: #FFF;
    }
    .sound-btn:hover svg {
      transform: scale(1.08);
    }
    .btn-kds-reset {
      color: #8E8279;
    }
    .btn-kds-reset:hover {
      color: #F87171;
    }
    .btn-kds-reset:hover svg {
      transform: translateY(-1px);
    }

    /* Right Action Buttons */
    .btn-kds-pos {
      color: #DF9B64;
      font-weight: 600;
      letter-spacing: 0.01em;
    }
    .btn-kds-pos:hover {
      color: #FFF;
    }
    .btn-kds-pos:hover svg {
      transform: translateY(-1px);
    }
    .btn-kds-signout {
      color: #8E8279;
      letter-spacing: 0.01em;
    }
    .btn-kds-signout:hover {
      color: #F87171;
    }
    .btn-kds-signout:hover svg {
      transform: translateX(1px);
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
        gap: 0.5rem;
        padding: 0.6rem 1rem;
      }
      .kds-actions {
        flex-wrap: wrap;
        gap: 0.2rem;
      }
    }
    @media (max-width: 768px) {
      .kds-op-cluster {
        gap: 0.15rem;
      }
      .kds-btn {
        padding: 0.25rem 0.45rem;
        font-size: 0.75rem;
        min-height: 38px;
      }
      .kds-header-divider {
        height: 14px;
        margin: 0 0.05rem;
      }
    }
    @media (max-width: 640px) {
      .kds-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
      }
      .kds-brand {
        justify-content: space-between;
        width: 100%;
        flex-wrap: wrap;
        gap: 0.4rem;
      }
      .kds-op-cluster {
        width: 100%;
        justify-content: flex-start;
        margin-left: 0;
        flex-wrap: wrap;
        gap: 0.15rem;
      }
      .kds-op-cluster > .kds-header-divider:first-child {
        display: none;
      }
      .kds-actions {
        width: 100%;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.2rem;
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

    /* Column Styles (Editorial Anti-Slop) */
    .kds-col {
      background: #171311;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 10px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: none;
    }
    .kds-col-header {
      padding: 0.85rem 1.15rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: transparent;
    }
    .kds-col-title {
      font-size: 0.8125rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      display: flex;
      align-items: center;
      gap: 0.45rem;
      color: #EDE3DA;
    }
    .col-pending .kds-col-title svg { color: #DF9B64; }
    .col-progress .kds-col-title svg { color: #A8988C; }
    .kds-badge-count {
      font-family: var(--font-mono);
      font-size: 0.8125rem;
      font-weight: 600;
      color: #8E8279;
      background: transparent;
      border: none;
      padding: 0;
      line-height: 1;
      letter-spacing: 0.02em;
    }
    .col-pending .kds-badge-count {
      color: #DF9B64;
    }
    .col-progress .kds-badge-count {
      color: #A8988C;
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

    /* Ticket Card - Streamlined Anti-Slop Layout */
    .kds-ticket-card {
      background: #1C1714;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 8px;
      padding: 0.65rem 0.75rem;
      cursor: pointer;
      transition: background-color 0.15s ease, border-color 0.15s ease;
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      min-width: 0;
      box-sizing: border-box;
    }
    .kds-ticket-card:hover {
      background: #231C18;
      border-color: rgba(255, 255, 255, 0.14);
    }
    .kds-ticket-card.selected {
      border-color: #DF9B64 !important;
      background: #251D18 !important;
    }
    .ticket-card-top {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 0.4rem;
      min-width: 0;
    }
    .ticket-queue-num {
      font-size: 1.25rem;
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
      white-space: nowrap;
      flex-shrink: 0;
    }
    .ticket-origin {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1.25;
    }
    .ticket-origin.origin-qr {
      color: #F5A25D;
    }
    .ticket-origin.origin-registrar {
      color: #93C5FD;
    }
    .ticket-origin.origin-online {
      color: #6EE7B7;
    }
    .ticket-count {
      font-size: 0.74rem;
      font-family: var(--font-mono);
      font-weight: 600;
      color: #A89A8E;
      line-height: 1.2;
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
      background: #171311;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 10px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: none;
    }
    .ws-header {
      padding: 0.85rem 1.15rem;
      background: transparent;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
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
      font-size: 1.75rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #FFF;
      line-height: 1;
      padding: 0;
      border-radius: 0;
      background: none;
      border: none;
      flex-shrink: 0;
      letter-spacing: -0.02em;
      box-shadow: none;
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
      align-items: baseline;
      gap: 0.45rem;
      flex-shrink: 0;
    }
    .ws-table-badge {
      font-size: 0.74rem;
      font-weight: 600;
      color: #EDE3DA;
      background: none;
      border: none;
      padding: 0;
      letter-spacing: 0.02em;
      white-space: nowrap;
      line-height: 1.2;
    }
    .origin-badge {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0;
      border: none;
      background: none;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
      line-height: 1.2;
    }
    .origin-badge.origin-qr {
      color: #F5A25D;
    }
    .origin-badge.origin-registrar {
      color: #93C5FD;
    }
    .origin-badge.origin-online {
      color: #6EE7B7;
    }
    .ws-order-meta-sep {
      color: rgba(255, 255, 255, 0.25);
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
      align-items: baseline;
      gap: 0.45rem;
      background: none;
      border: none;
      padding: 0;
      white-space: nowrap;
      line-height: 1;
      box-shadow: none;
    }
    .ws-pay-method {
      font-size: 0.74rem;
      font-weight: 700;
      color: var(--kds-amber);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      background: none;
      padding: 0;
      line-height: 1;
    }
    .ws-price-divider {
      color: rgba(255, 255, 255, 0.25);
      font-size: 0.85rem;
    }
    .ws-price-val {
      font-family: var(--font-mono);
      font-size: 1.32rem;
      font-weight: 800;
      color: #FFF;
      letter-spacing: -0.02em;
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

    /* Workstation Footer & Action Buttons (Anti-Slop Matte) */
    .ws-footer {
      padding: 0.75rem 1.15rem;
      background: transparent;
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      align-items: center;
      gap: 0.65rem;
    }
    .btn-action-primary {
      flex: 1;
      min-height: 44px;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      transition: all 0.18s ease;
      border: none;
      box-sizing: border-box;
      white-space: nowrap;
    }
    /* Locked state */
    .btn-action-primary.locked {
      background: #201A16;
      color: #6B5B50;
      border: 1px solid rgba(255, 255, 255, 0.06);
      cursor: not-allowed;
      pointer-events: none;
      box-shadow: none;
    }
    /* Unlocked Solid Emerald Green */
    .btn-action-primary.unlocked {
      background: #10B981;
      color: #FFF;
      box-shadow: none;
      cursor: pointer;
      pointer-events: auto;
    }
    .btn-action-primary.unlocked:hover {
      background: #059669;
    }
    /* Primary Start Order - Warm Coffee Amber */
    .btn-acknowledge {
      background: #E28743;
      color: #14100E;
      font-weight: 700;
      box-shadow: none;
    }
    .btn-acknowledge:hover {
      background: #F5A25D;
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
      width: 48px;
      height: 48px;
      background: transparent;
      border: none;
      color: #DF9B64;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 0.5rem;
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

    /* Urgency Timers - Clean Typography */
    .ticket-timer.warning {
      color: #FBBF24 !important;
      font-weight: 700 !important;
      background: none !important;
      border: none !important;
      padding: 0 !important;
    }
    .ticket-timer.urgent {
      color: #F87171 !important;
      font-weight: 700 !important;
      background: none !important;
      border: none !important;
      padding: 0 !important;
      box-shadow: none !important;
    }



    /* Bump Mode Switch Button - Clean Ghost Action */
    .bump-mode-btn-switch {
      font-size: 0.72rem;
      font-weight: 600;
      color: #A89A8E;
      background: none;
      border: none;
      padding: 0.15rem 0;
      cursor: pointer;
      transition: color 0.15s ease;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      text-decoration: none;
      line-height: 1.2;
    }
    .bump-mode-btn-switch:hover {
      background: none;
      border-color: transparent;
      color: var(--kds-amber);
      text-decoration: underline;
    }
    .bump-mode-btn-switch svg {
      color: currentColor;
      flex-shrink: 0;
    }

    .btn-fast-bump {
      background: #10B981;
      color: #FFF;
      box-shadow: none;
      font-weight: 600;
    }
    .btn-fast-bump:hover {
      background: #059669;
    }

    .btn-action-void {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #8E8279;
      font-size: 0.8125rem;
      font-weight: 500;
      padding: 0.5rem 0.95rem;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.18s ease;
      min-height: 44px;
      flex-shrink: 0;
      box-sizing: border-box;
      white-space: nowrap;
    }
    .btn-action-void:hover {
      background: rgba(239, 68, 68, 0.08);
      border-color: rgba(239, 68, 68, 0.35);
      color: #F87171;
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
      background: #1C1714;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 8px;
      padding: 0.85rem 1rem;
      text-align: left;
      cursor: pointer;
      transition: all 0.18s ease;
      color: #D6C7BC;
    }
    .btn-void-reason:hover {
      background: rgba(239, 68, 68, 0.08);
      border-color: rgba(239, 68, 68, 0.4);
      transform: translateY(-1px);
    }
    .btn-void-reason strong {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.88rem;
      margin-bottom: 0.2rem;
      color: #EDE3DA;
    }
    .btn-void-reason:hover strong {
      color: #F87171;
    }
    .btn-void-reason span {
      font-size: 0.75rem;
      color: #8E8279;
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
          var posBtn = document.getElementById('kdsPosBtn');
          if (posBtn && data && data.user) {
            posBtn.title = `Switch to Cashier POS (Signed in as ${data.user.name || 'Staff'})`;
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
        <h1>BeCoffee KDS</h1>
        <div class="kds-subtitle">Live Orders &amp; Preparation</div>
      </div>
      <div class="kds-op-cluster" aria-label="Operational Controls">
        <span class="kds-header-divider" aria-hidden="true"></span>
        <div class="kds-clock" id="kdsClock">00:00:00</div>
        <span class="kds-header-divider" aria-hidden="true"></span>
        <button type="button" class="kds-btn sound-btn active" id="kdsSoundToggle" title="Toggle audio chime alert on new incoming tickets">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
          <span id="soundLabel">Chime: ON</span>
        </button>
        <span class="kds-header-divider" aria-hidden="true"></span>
        <button type="button" class="kds-btn btn-kds-reset" id="kdsResetQueueBtn" title="Supervisor PIN required to reset active queue">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          <span>Reset Queue</span>
        </button>
      </div>
    </div>

    <div class="kds-actions" aria-label="Staff Navigation">
      <a href="index.php?mode=walkin" class="kds-btn btn-kds-pos" id="kdsPosBtn" title="Open Cashier Register to take customer orders">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span>Take Orders (POS)</span>
      </a>
      <span class="kds-header-divider" aria-hidden="true"></span>
      <button type="button" class="kds-btn btn-kds-signout" id="kdsSignOutBtn" onclick="handleKdsSignOut()" title="Sign out of Kitchen Screen">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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

  <!-- Modal 1: Supervisor Queue Reset Modal -->
  <div class="kds-modal-overlay" id="kdsResetModal" role="dialog" aria-modal="true" aria-labelledby="resetModalTitle">
    <div class="kds-modal-card" style="max-width: 440px;">
      <div class="kds-modal-head">
        <div class="kds-modal-title" id="resetModalTitle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color: var(--kds-amber);"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          <span>Reset Active Queue</span>
        </div>
        <button type="button" class="kds-modal-close" id="btnCloseResetModal" aria-label="Close dialog">&times;</button>
      </div>

      <div class="kds-modal-body" id="resetModalContent">
        <!-- Rendered PIN Keypad -->
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
            <strong>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
              Customer Cancelled
            </strong>
            <span>Customer left or cancelled ticket</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Duplicate Cashier Ring">
            <strong>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
              Duplicate Ring
            </strong>
            <span>Accidentally rung up twice</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Out of Stock / 86'd">
            <strong>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"></path><path d="m7.5 4.27 9 5.15"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><line x1="12" y1="22" x2="12" y2="12"></line><path d="m17 13 5 5m-5 0 5-5"></path></svg>
              Out of Stock
            </strong>
            <span>Milk, beans, or food item 86'd</span>
          </button>
          <button type="button" class="btn-void-reason" data-reason="Staff Mistake / Wrong Item">
            <strong>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
              Mistake / Wrong Item
            </strong>
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
      var bumpMode = localStorage.getItem('kds_bump_mode') || 'fast'; // 'fast' | 'qa'
      var currentVoidOrderId = null;
      var currentHistoryTab = 'completed';
      var enteredPin = '';

      function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
      }

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

        var pending = rawPending;
        var inProgress = rawInProgress;

        // Update column counts
        document.getElementById('pendingCount').textContent = pending.length;
        document.getElementById('inProgressCount').textContent = inProgress.length;

        // Render Pending Column
        var pendingList = document.getElementById('pendingTicketList');
        if (pending.length === 0) {
          pendingList.innerHTML = '<div class="kds-empty-notice">No pending orders right now.</div>';
        } else {
          pendingList.innerHTML = pending.map(function(ord) {
            return buildTicketCardHtml(ord);
          }).join('');
        }

        // Render In Progress Column
        var progressList = document.getElementById('inProgressTicketList');
        if (inProgress.length === 0) {
          progressList.innerHTML = '<div class="kds-empty-notice">No orders in progress right now.</div>';
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
          if (ord.table_number) {
            originText = `REGISTRAR · T#${ord.table_number}`;
          } else {
            var isGeneric = !ord.customer_name || /^(guest|customer|autotest|walk-in)/i.test(ord.customer_name.trim());
            originText = isGeneric ? 'REGISTRAR' : `CALL: ${escapeHtml(ord.customer_name.substring(0, 10))}`;
          }
        } else {
          originClass = 'origin-qr';
          originText = ord.table_number ? `QR / LINK · T#${ord.table_number}` : 'QR / LINK';
        }

        var gcashPill = (ord.payment_method === 'gcash')
          ? '<span style="font-size: 0.65rem; font-weight: 800; background: rgba(223, 155, 100, 0.2); color: #F5A25D; border: 1px solid rgba(223, 155, 100, 0.4); padding: 1px 5px; border-radius: 4px; letter-spacing: 0.04em;">GCASH</span>'
          : '';

        return `
          <div class="kds-ticket-card ${isSelected ? 'selected' : ''}" data-order-id="${ord.id}">
            <div class="ticket-card-top">
              <span class="ticket-queue-num">#${ord.queue_number}</span>
              <span class="${timerClass}">${formatElapsed(ord.elapsed_seconds || 0)}</span>
            </div>
            <div class="ticket-origin ${originClass}">${originText}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.25rem;">
              <span class="ticket-count">${totalQty} ${totalQty === 1 ? 'item' : 'items'}</span>
              ${gcashPill}
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
            : (nextPending.table_number ? 'Table #' + nextPending.table_number : 'Counter Pick-Up');
          var destLine = isGeneric
            ? destinationLabel
            : (nextPending.table_number
                ? `${escapeHtml(nextPending.customer_name)} · ${destinationLabel}`
                : `CALL: ${escapeHtml(nextPending.customer_name)} · ${destinationLabel}`);

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
          : (ord.table_number ? `Table #${ord.table_number}` : 'Dine-In (Counter Pick-Up)');

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

        var isWalkinCall = (ord.order_source === 'registrar' && !ord.table_number);
        var tags = document.getElementById('wsHeaderTags');
        if (tags) {
          var callBadge = isWalkinCall ? `
            <span class="ws-order-meta-sep">·</span>
            <span class="ws-table-badge" style="background: rgba(223, 155, 100, 0.2); color: #FDBA74; border: 1px solid rgba(223, 155, 100, 0.4); display: inline-flex; align-items: center; gap: 4px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
              <span>CALL NAME</span>
            </span>` : '';

          tags.innerHTML = `
            <span class="origin-badge ${originClass}">${originText}</span>
            <span class="ws-order-meta-sep">·</span>
            <span class="ws-table-badge">${destinationLabel}</span>
            ${callBadge}
          `;
          tags.style.display = 'inline-flex';
        }

        // Render Price and Payment Method in wsPriceBadge
        var priceBadge = document.getElementById('wsPriceBadge');
        if (priceBadge) {
          var payMethodEl = document.getElementById('wsPayMethod');
          var priceValEl = document.getElementById('wsPriceVal');
          if (payMethodEl) {
            payMethodEl.textContent = (ord.payment_method === 'gcash')
              ? (ord.table_number ? 'GCash · Verify Table' : 'GCash · Verify Counter')
              : 'Cash';
          }
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

        var gcashAlertHtml = (ord.payment_method === 'gcash') ? `
          <div style="background: rgba(223, 155, 100, 0.12); border: 1px solid rgba(223, 155, 100, 0.35); border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem; font-size: 0.78rem; color: #FAF7F2; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DF9B64" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12.01" y2="8"></line><polyline points="12 12 12 16 14 16"></polyline></svg>
            <span><strong>Verify GCash Receipt:</strong> Inspect customer phone (sent to <strong>BECOFFEE</strong> · <strong>₱${parseFloat(ord.grand_total).toFixed(2)}</strong>) before releasing drinks.</span>
          </div>
        ` : '';

        // PENDING STATE
        if (ord.status === 'pending') {
          wsBody.innerHTML = `
            <div>
              ${gcashAlertHtml}
              <div class="recipe-list-heading">Items to Prepare</div>
              <div class="recipe-list-items">
                ${recipesHtml}
              </div>
            </div>
          `;

          footer.innerHTML = `
            <button type="button" class="btn-action-void" id="btnVoidTicket" title="Cancel or void ticket with 1-tap reason">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
              <span>Void</span>
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
            ? `GCash receipt verified on phone (BECOFFEE · ₱${parseFloat(ord.grand_total).toFixed(2)})`
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
            : (ord.table_number
                ? `Ready for Table #${ord.table_number} (Glassware & tray prepared)`
                : `Ready for Counter Pick-Up (Call: ${escapeHtml(customerDisplayName)})`);

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
              ${gcashAlertHtml}
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
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>Void</span>
              </button>
              <button type="button" class="btn-action-primary btn-fast-bump" id="btnFastBumpOrder" style="flex: 1;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Fast Bump Order #${ord.queue_number}</span>
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
                  btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Fast Bump Order #${ord.queue_number}</span>`;
                }
              } catch (err) {
                await SystemDialog.alert('Error completing order: ' + err.message, { title: 'Completion Error', type: 'danger' });
                btn.disabled = false;
                btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Fast Bump Order #${ord.queue_number}</span>`;
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
      // Supervisor Queue Reset Tool Modal
      // =========================================================================
      var resetModal = document.getElementById('kdsResetModal');
      var resetBtn = document.getElementById('kdsResetQueueBtn');
      var btnCloseReset = document.getElementById('btnCloseResetModal');

      if (resetBtn && resetModal) {
        resetBtn.addEventListener('click', function() {
          resetModal.classList.add('active');
          renderResetPinKeypad();
        });
      }

      if (btnCloseReset && resetModal) {
        btnCloseReset.addEventListener('click', function() {
          resetModal.classList.remove('active');
        });
      }

      function renderResetPinKeypad() {
        enteredPin = '';
        var resetContent = document.getElementById('resetModalContent');
        if (!resetContent) return;

        resetContent.innerHTML = `
          <div style="text-align: center; max-width: 320px; margin: 0 auto; padding: 0.5rem 0;">
            <p style="font-size: 0.84rem; color: #FCA5A5; margin-bottom: 0.75rem; line-height: 1.35;">
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

        resetContent.querySelectorAll('.pin-key').forEach(function(key) {
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
                  resetModal.classList.remove('active');
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
