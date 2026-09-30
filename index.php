<?php
require_once __DIR__ . '/api/config.php';
$currentUser = getAuthenticatedUser();
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
    .btn-leave-link {
      color: #FCA5A5 !important;
      border-color: rgba(239, 68, 68, 0.35) !important;
      background: rgba(239, 68, 68, 0.08) !important;
    }
    .btn-leave-link:hover {
      background: rgba(239, 68, 68, 0.2) !important;
      border-color: rgba(239, 68, 68, 0.6) !important;
      color: #FFF !important;
    }
    @media (max-width: 480px) {
      .order-header {
        padding: 0.55rem 0.65rem;
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
        gap: 0.3rem;
      }
      .btn-story-link {
        padding: 0.3rem 0.5rem;
        font-size: 0.72rem;
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
      top: 59px;
      z-index: 90;
      background: rgba(17, 13, 11, 0.92);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 0.65rem 1.25rem;
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
      padding: 0.45rem 1rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.04);
      color: #D1C5BD;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .cat-pill-btn.active,
    .cat-pill-btn:hover {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      border-color: #E28743;
      box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
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

    /* Drinks Grid */
    .order-catalog-wrap {
      max-width: 1200px;
      margin: 1.25rem auto;
      padding: 0 1.25rem;
      width: 100%;
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
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.25rem;
    }
    @media (max-width: 600px) {
      .catalog-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
    }

    .drink-card {
      background: #1C1613;
      border: 1px solid rgba(223, 155, 100, 0.16);
      border-radius: 18px;
      padding: 1.15rem;
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
    }
    .drink-card:hover {
      border-color: rgba(226, 135, 67, 0.4);
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
    }
    .drink-img-wrap {
      aspect-ratio: 16/10;
      border-radius: 12px;
      overflow: hidden;
      background: #251D18;
      position: relative;
    }
    .drink-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .drink-card:hover .drink-img-wrap img {
      transform: scale(1.04);
    }
    .drink-badge {
      position: absolute;
      top: 8px;
      left: 8px;
      background: rgba(17, 13, 11, 0.85);
      border: 1px solid rgba(226, 135, 67, 0.4);
      color: #FDBA74;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.2rem 0.55rem;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      backdrop-filter: blur(8px);
    }
    .drink-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .drink-name {
      font-size: 1.18rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.2;
    }
    .drink-price {
      font-family: var(--font-mono);
      font-size: 1.15rem;
      font-weight: 700;
      color: #DF9B64;
    }
    .drink-desc {
      font-size: 0.85rem;
      color: #A99B92;
      line-height: 1.4;
      flex: 1;
    }
    .btn-customize-add {
      min-height: 46px;
      border-radius: 12px;
      background: rgba(226, 135, 67, 0.15);
      border: 1px solid rgba(226, 135, 67, 0.35);
      color: #FDBA74;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
      width: 100%;
    }
    .btn-customize-add:hover {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      color: #FFF;
      border-color: #E28743;
      box-shadow: 0 4px 16px rgba(226, 135, 67, 0.35);
    }

    /* Floating Cart Bar (Sticky at bottom on mobile) */
    .floating-cart-bar {
      position: fixed;
      bottom: 1.25rem;
      left: 50%;
      transform: translateX(-50%);
      width: min(calc(100% - 2.5rem), 540px);
      background: linear-gradient(135deg, #221B17 0%, #15110E 100%);
      border: 1px solid rgba(223, 155, 100, 0.35);
      border-radius: 18px;
      padding: 0.85rem 1.25rem;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(226, 135, 67, 0.2);
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 99;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .floating-cart-bar:hover {
      transform: translateX(-50%) translateY(-2px);
      box-shadow: 0 20px 48px rgba(0, 0, 0, 0.75), 0 0 20px rgba(226, 135, 67, 0.3);
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

    /* Customization Options UI */
    .option-group-label {
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #DF9B64;
      font-weight: 700;
      margin: 1rem 0 0.5rem;
    }
    .pill-radio-group {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .pill-radio-opt {
      flex: 1;
      min-width: 100px;
      text-align: center;
      padding: 0.65rem 0.85rem;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      font-size: 0.85rem;
      font-weight: 600;
      color: #E2D5CC;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .pill-radio-opt.active {
      background: linear-gradient(135deg, #E28743 0%, #944D1C 100%);
      border-color: #E28743;
      color: #FFF;
      box-shadow: 0 4px 12px rgba(226, 135, 67, 0.3);
    }
    .pill-radio-opt.is-sold-out {
      opacity: 0.45 !important;
      background: rgba(239, 68, 68, 0.08) !important;
      border-color: rgba(239, 68, 68, 0.25) !important;
      color: #9CA3AF !important;
      cursor: not-allowed !important;
      pointer-events: none !important;
      text-decoration: line-through;
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
    .notes-textarea {
      width: 100%;
      background: rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 10px;
      padding: 0.75rem 1rem;
      color: #FFF;
      font-family: inherit;
      font-size: 0.88rem;
      resize: vertical;
      min-height: 70px;
    }
    .notes-textarea:focus {
      outline: none;
      border-color: #DF9B64;
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
  </style>
</head>
<body class="order-app-body">

  <!-- Direct Order Header -->
  <header class="order-header">
    <a href="index.php" class="order-brand-link">
      <div class="order-brand-logo">B</div>
      <div>
        <span class="order-brand-title">BeCoffee</span>
        <span class="order-brand-tag">Online Ordering Portal</span>
      </div>
    </a>

    <!-- Context Pill (Dine-in / Table / Take-out) -->
    <div class="table-context-pill" id="tableContextPill" title="Tap to switch Dine-in or Take-out">
      <span id="tableContextIcon">🪑</span>
      <span id="tableContextText">Table #01</span>
    </div>

    <!-- Right Actions -->
    <div class="order-top-actions">
      <?php if (!empty($currentUser)): ?>
        <a href="api/auth.php?action=logout&redirect=index.php" class="btn-story-link" style="color: #FCA5A5; border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1);" title="Sign out of account">🚪 Log Out</a>
      <?php else: ?>
        <a href="home.php" class="btn-story-link" title="Explore roastery background and story">Our Story</a>
        <button type="button" class="btn-story-link btn-leave-link" id="btnLeaveSession" title="Leave table ordering and exit" style="background: none; cursor: pointer;">🚪 Leave</button>
      <?php endif; ?>
      <?php if (!empty($currentUser) && in_array($currentUser['role'], ['staff', 'admin', 'superadmin'])): ?>
        <a href="kds.php" class="btn-story-link" style="color: #DF9B64; border-color: rgba(223, 155, 100, 0.3);">Staff KDS</a>
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
    <div class="order-prep-timer" id="orderPrepTimer" title="Remaining time to place your order" role="timer" aria-live="polite">
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

  <!-- Main Order Catalog -->
  <main class="order-catalog-wrap">
    <div id="catalogLoadingNotice" style="text-align: center; padding: 3rem 1rem; color: #A99B92;">
      Loading artisanal drink selection...
    </div>

    <div class="catalog-grid" id="drinksCatalogGrid" style="display: none;">
      <!-- Drink Cards injected dynamically -->
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
    <div class="oms-modal-box">
      <button type="button" class="oms-modal-close" id="closeCustomModalBtn" aria-label="Close Customizer">&times;</button>
      
      <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.25rem;">
        <img id="customItemImg" src="" alt="Drink preview" style="width: 70px; height: 70px; border-radius: 12px; object-fit: cover;">
        <div>
          <h3 id="customItemName" style="font-size: 1.25rem; font-weight: 700; color: #FFF; margin-bottom: 0.2rem;">Spanish Latte</h3>
          <p id="customItemBasePrice" style="font-family: var(--font-mono); font-size: 1.05rem; color: #DF9B64; font-weight: 700;">₱120.00</p>
        </div>
      </div>

      <!-- Temperature Choice -->
      <div class="option-group-label">TEMPERATURE</div>
      <div class="pill-radio-group" id="tempRadioGroup">
        <div class="pill-radio-opt active" data-val="Iced">Iced</div>
        <div class="pill-radio-opt" data-val="Hot">Hot</div>
      </div>

      <!-- Add-ons Selection (Replaced Milk Selection) -->
      <div class="option-group-label">ADD ONS</div>
      <div class="pill-radio-group" id="addonRadioGroup">
        <!-- Injected dynamically from live stock options -->
      </div>

      <!-- Sweetness Levels -->
      <div class="option-group-label">SWEETNESS / SUGAR LEVEL</div>
      <div class="pill-radio-group" id="sweetnessRadioGroup">
        <div class="pill-radio-opt" data-val="Normal (100%)">100% Normal</div>
        <div class="pill-radio-opt active" data-val="Less Sweet (75%)">75% Less Sweet</div>
        <div class="pill-radio-opt" data-val="Half Sweet (50%)">50% Half Sweet</div>
        <div class="pill-radio-opt" data-val="No Sugar (0%)">0% No Sugar</div>
      </div>

      <!-- Special Notes -->
      <div class="option-group-label">SPECIAL NOTES / INSTRUCTIONS</div>
      <textarea class="notes-textarea" id="customNotesInput" placeholder="e.g. Less sweet, extra ice, separate lid..."></textarea>

      <!-- Quantity & Submit -->
      <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
        <div style="display: flex; align-items: center; background: rgba(255,255,255,0.06); border-radius: 12px; border: 1px solid rgba(255,255,255,0.12); padding: 0.25rem 0.5rem;">
          <button type="button" id="btnQtyMinus" style="background: none; border: none; color: #FFF; font-size: 1.25rem; width: 32px; cursor: pointer;">-</button>
          <span id="customQtyDisplay" style="font-family: var(--font-mono); font-weight: 700; width: 28px; text-align: center;">1</span>
          <button type="button" id="btnQtyPlus" style="background: none; border: none; color: #FFF; font-size: 1.25rem; width: 32px; cursor: pointer;">+</button>
        </div>
        <button type="button" class="btn-customize-add" id="btnSubmitCustomItem" style="flex: 1; min-height: 52px; background: linear-gradient(135deg, #E28743 0%, #944D1C 100%); color: #FFF; font-size: 1rem;">
          Add to Cart — <span id="customModalTotalPrice">₱150.00</span>
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
      <div id="tableLockedBadge" style="display: none; margin-top: 0.85rem; background: rgba(223, 155, 100, 0.12); border: 1px solid rgba(223, 155, 100, 0.35); border-radius: 10px; padding: 0.75rem 0.85rem; align-items: center; gap: 0.75rem;">
        <div style="font-size: 1.4rem;">🪑</div>
        <div>
          <div style="font-size: 0.9rem; font-weight: 700; color: #DF9B64;">Seated at Table <span id="tableLockedNumber">1</span></div>
          <div style="font-size: 0.75rem; color: #A99B92;">Auto-detected from Table QR Sticker · Orders served directly to this table</div>
        </div>
      </div>

      <!-- Table Number (Shown if Dine-In and not arriving from table-locked QR) -->
      <div id="tableNumberWrap" style="margin-top: 0.85rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 0.75rem 0.85rem;">
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

      <div id="gcashNoticeWrap" style="display: none; background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.35); border-radius: 12px; padding: 0.85rem; margin-top: 0.75rem; font-size: 0.82rem; color: #93C5FD;">
        <strong>GCash Instructions:</strong> Send payment to <strong>0917 555 2026 (BeCoffee)</strong> upon placing order. Show your reference number to cashier at counter.
      </div>

      <!-- Customer Details -->
      <div class="option-group-label" style="margin-top: 1.25rem;">Customer Details</div>
      <div style="display: flex; flex-direction: column; gap: 0.65rem;">
        <input type="text" id="checkoutNameInput" class="notes-textarea" style="min-height: 44px; padding: 0.6rem 0.85rem;" placeholder="Your Name (e.g. Mark or Sarah)" value="Mark">
        <input type="tel" id="checkoutPhoneInput" class="notes-textarea" style="min-height: 44px; padding: 0.6rem 0.85rem;" placeholder="Mobile Number (+63 9XX XXX XXXX)" value="+63 917 555 2026">
      </div>

      <!-- Place Order CTA -->
      <button type="button" class="btn-customize-add" id="btnPlaceOrderFinal" style="margin-top: 1.5rem; min-height: 54px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: #FFF; font-size: 1.05rem; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.35);">
        Place Order — <span id="checkoutFinalTotal">₱0.00</span>
      </button>
    </div>
  </div>

  <!-- ACTIVE ORDER TICKET WAITING SCREEN (Starts Fresh at 00:00) -->
  <div class="ticket-screen-overlay" id="orderTicketScreen">
    <div class="ticket-container">
      <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: #DF9B64; font-weight: 700;">
        Active Order Ticket
      </div>

      <!-- Giant Queue Number -->
      <div class="ticket-giant-num" id="ticketQueueNum">#104</div>

      <!-- Dining Mode Confirmation Pill & Fallback Switcher -->
      <div id="ticketDiningWrap" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem; margin-bottom: 0.85rem;">
        <span id="ticketDiningTag" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.82rem; font-weight: 700; background: rgba(226, 135, 67, 0.18); color: #FDBA74; border: 1px solid rgba(226, 135, 67, 0.35);">
          🪑 Dine In · Table #4
        </span>
        <button type="button" id="btnSwitchDiningPostOrder" style="background: none; border: none; color: #DF9B64; font-size: 0.76rem; text-decoration: underline; cursor: pointer; padding: 0.2rem 0.5rem; transition: opacity 0.15s ease;" title="Change dining mode if selected by mistake">
          Accidentally chose Take Out? Switch to Dine In
        </button>
      </div>

      <!-- Live Elapsed Stopwatch (Fresh at 00:00) -->
      <div class="ticket-stopwatch-row">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        <span>Wait Time: <strong id="stopwatchDisplay">00:00</strong></span>
      </div>

      <!-- Dynamic Status Card -->
      <div class="ticket-status-card status-pending" id="ticketStatusCard">
        <div class="status-beacon-wrap">
          <span class="beacon-dot"></span>
          <span id="ticketStatusText">Order Sent – Waiting for Barista.</span>
        </div>
        <p style="font-size: 0.82rem; color: #A99B92; margin-top: 0.35rem;" id="ticketSubNotice">
          Your ticket is queued in the kitchen. The barista tablet has been alerted.
        </p>
      </div>

      <!-- Live Queue Depth Indicator -->
      <div style="font-size: 0.85rem; color: #D1C5BD; margin-bottom: 1.25rem;">
        Queue Status: <strong id="ordersAheadDisplay" style="color: #FDBA74;">0 orders ahead of you</strong>
      </div>

      <!-- Ticket Items Summary -->
      <div style="background: rgba(0,0,0,0.25); border-radius: 12px; padding: 1rem; text-align: left; font-size: 0.85rem; color: #E2D5CC; margin-bottom: 1.25rem;" id="ticketItemsSummary">
        <!-- Injected list of items -->
      </div>

      <!-- Pickup Announcement (Only active on Completed) -->
      <div id="pickupCelebrateWrap" style="display: none;">
        <h2 class="pickup-celebrate-title">Ready for Pickup! Please proceed to the counter.</h2>
        <p style="font-size: 0.9rem; color: #D1FAE5; margin-bottom: 1rem;">Your handcrafted order is freshly packaged and ready at the barista bar.</p>
        <button type="button" class="btn-pickup-dismiss" id="btnDismissPickup">Order Picked Up · Dismiss</button>
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
        var pillIcon = document.getElementById('tableContextIcon');
        var pillText = document.getElementById('tableContextText');
        if (currentOrderType === 'take_out') {
          pillIcon.textContent = '🛍️';
          pillText.textContent = 'Take-out';
        } else {
          pillIcon.textContent = '🪑';
          pillText.textContent = `Table #${currentTableNumber}`;
        }
        sessionStorage.setItem('becoffee_order_type', currentOrderType);
        if (currentOrderType === 'dine_in') {
          sessionStorage.setItem('becoffee_table_num', currentTableNumber);
        }
        updatePausedBanner();
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

        if (lockedNum) lockedNum.textContent = currentTableNumber;
        if (checkoutInput) checkoutInput.value = currentTableNumber;

        if (currentOrderType === 'dine_in') {
          if (isTableLocked) {
            if (lockedBadge) lockedBadge.style.display = 'flex';
            if (manualWrap) manualWrap.style.display = 'none';
          } else {
            if (lockedBadge) lockedBadge.style.display = 'none';
            if (manualWrap) manualWrap.style.display = 'block';
          }
        } else {
          if (lockedBadge) lockedBadge.style.display = 'none';
          if (manualWrap) manualWrap.style.display = 'none';
        }
      }

      // Tap context badge to toggle passively
      document.getElementById('tableContextPill').addEventListener('click', function() {
        currentOrderType = (currentOrderType === 'dine_in') ? 'take_out' : 'dine_in';
        updateContextBadge();
        updateCartUI();
        syncCheckoutTableDisplay();
      });

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
            { id: 'hc-spanish', category: 'house-coffee', name: 'Spanish Latte', price: 120, description: 'Velvety espresso combined with smooth fresh milk and rich condensed milk for a perfectly sweet kick.', image: 'images/menu/hc-spanish.webp', tags: ['Bestseller'] },
            { id: 'hc-salted-caramel', category: 'house-coffee', name: 'Salted Caramel', price: 120, description: 'Slow-cooked rich caramel paired with espresso, fresh milk, and a delicate touch of flaky sea salt.', image: 'images/menu/hc-salted-caramel.webp', tags: ['House Coffee'] },
            { id: 'mat-latte', category: 'matcha', name: 'Matcha Latte', price: 120, description: 'Stone-ground Uji green tea whisked fresh with silky milk for a soothing, umami-rich experience.', image: 'images/menu/mat-latte.webp', tags: ['Bestseller'] },
            { id: 'hs-choco', category: 'house-specials', name: 'Artisanal Choco', price: 90, description: 'Decadent, velvety chocolate milk made with pure cocoa and smooth fresh milk.', image: 'images/menu/hs-choco.webp', tags: ['House Specials'] }
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
          var tagBadge = (item.tags && item.tags.length > 0)
            ? `<span class="drink-badge">${item.tags[0]}</span>`
            : '';
          var buttonText = isItemSoldOut ? 'Sold Out' : 'Customize & Add';
          var cardClass = isItemSoldOut ? 'drink-card is-sold-out' : 'drink-card';
          var btnDisabled = isItemSoldOut ? 'disabled' : '';

          return `
            <div class="${cardClass}">
              <div class="drink-img-wrap">
                <img src="${item.image || 'images/menu/hc-spanish.webp'}" alt="${item.name}" loading="lazy">
                ${tagBadge}
              </div>
              <div class="drink-meta-row">
                <h4 class="drink-name">${item.name}</h4>
                <span class="drink-price">₱${parseFloat(item.price).toFixed(2)}</span>
              </div>
              <p class="drink-desc">${item.description || 'Specialty handcrafted cafe beverage.'}</p>
              <button type="button" class="btn-customize-add btn-open-custom" data-item-id="${item.id}" ${btnDisabled}>
                ${isItemSoldOut ? '🚫' : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>'}
                ${buttonText}
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

      // 10-Minute Table Ordering Session Countdown Timer & Inactivity Auto-Leave
      (function initOrderPrepTimer() {
        var clockEl = document.getElementById('prepCountdownClock');
        var timerWrap = document.getElementById('orderPrepTimer');
        if (!clockEl) return;

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
      var currentCustomAddon = 'Regular Milk';
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
            { option_key: 'Regular Milk', option_label: 'Regular Milk (+₱0)', surcharge: 0 },
            { option_key: 'Oat Milk', option_label: 'Oat Milk (+₱30)', surcharge: 30 },
            { option_key: 'Almond Milk', option_label: 'Almond Milk (+₱30)', surcharge: 30 }
          ];
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

        // Reset selections to defaults
        currentCustomTemp = 'Iced';
        currentCustomAddon = 'Regular Milk';
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

      function updateCartUI() {
        var totalQty = cart.reduce(function(sum, it) { return sum + it.quantity; }, 0);
        var subtotal = cart.reduce(function(sum, it) { return sum + it.subtotal; }, 0);
        var ecoFee = (currentOrderType === 'take_out') ? 15.00 : 0.00;
        var grandTotal = subtotal + ecoFee;

        var bar = document.getElementById('floatingCartBar');
        if (totalQty > 0) {
          bar.style.display = 'flex';
          document.getElementById('cartCountBadge').textContent = totalQty;
          document.getElementById('cartBarTotal').textContent = `₱${grandTotal.toFixed(2)}`;
          document.getElementById('cartBarSubtitle').textContent = `${totalQty} item${totalQty > 1 ? 's' : ''} in tray`;
        } else {
          bar.style.display = 'none';
        }

        // Render Cart Modal Items
        var listWrap = document.getElementById('cartItemsList');
        listWrap.innerHTML = cart.map(function(item, idx) {
          return `
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong style="color: #FFF; font-size: 0.95rem;">${item.quantity}x ${item.name}</strong>
                <div style="font-size: 0.75rem; color: #DF9B64; margin-top: 0.2rem;">
                  ${item.temperature} · ${item.milk_option} · ${item.sweetness_level}
                </div>
                ${item.custom_notes ? `<div style="font-size: 0.75rem; color: #A99B92; font-style: italic;">"${item.custom_notes}"</div>` : ''}
              </div>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-family: var(--font-mono); font-weight: 700; color: #FFF;">₱${item.subtotal.toFixed(2)}</span>
                <button type="button" class="btn-remove-item" data-idx="${idx}" style="background: none; border: none; color: #F87171; cursor: pointer; padding: 0.25rem;">&times;</button>
              </div>
            </div>
          `;
        }).join('');

        document.querySelectorAll('.btn-remove-item').forEach(function(b) {
          b.addEventListener('click', function() {
            var i = parseInt(b.getAttribute('data-idx'), 10);
            cart.splice(i, 1);
            updateCartUI();
          });
        });

        document.getElementById('cartSubtotalDisplay').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('cartEcoFeeDisplay').textContent = `₱${ecoFee.toFixed(2)}`;
        document.getElementById('ecoFeeLabel').textContent = (currentOrderType === 'take_out') ? 'Eco-Packaging Fee' : 'Dine-in Fee (Free)';
        document.getElementById('cartGrandTotalDisplay').textContent = `₱${grandTotal.toFixed(2)}`;
        var checkoutTotalEl = document.getElementById('checkoutFinalTotal');
        if (checkoutTotalEl) {
          checkoutTotalEl.textContent = `₱${grandTotal.toFixed(2)}`;
        }
      }

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

      document.getElementById('btnPlaceOrderFinal').addEventListener('click', async function() {
        if (cart.length === 0) return;

        if (currentOrderType === 'dine_in' && !isTableQREnabled) {
          await SystemDialog.alert('Table QR ordering is temporarily paused by cafe management. Please place your order at the counter or switch to Take Out.', { title: 'Ordering Paused', type: 'warning' });
          return;
        }

        var name = document.getElementById('checkoutNameInput').value.trim() || 'Guest Customer';
        var phone = document.getElementById('checkoutPhoneInput').value.trim() || '+63 900 000 0000';
        var table = (currentOrderType === 'dine_in') ? (currentTableNumber || document.getElementById('checkoutTableInput').value.trim() || '1') : null;

        var payload = {
          customer_name: name,
          customer_phone: phone,
          order_type: currentOrderType,
          table_number: table,
          payment_method: selectedPaymentMethod,
          items: cart
        };

        var submitBtn = document.getElementById('btnPlaceOrderFinal');
        submitBtn.disabled = true;
        var originalBtnHtml = submitBtn.innerHTML;
        submitBtn.textContent = 'Transmitting Order to Barista...';

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
            // Clear cart & checkout modal
            cart = [];
            updateCartUI();
            document.getElementById('checkoutModal').classList.remove('active');

            // Lock into Waiting Screen Starting Fresh at 00:00
            launchLiveTicketScreen(data.order_reference, data.queue_number, payload.items, payload.order_type, payload.table_number);
          } else {
            console.error('Order placement error:', data.error);
            await SystemDialog.alert('Order placement error: ' + (data.error || 'Server rejected request.'), { title: 'Order Failed', type: 'danger' });
          }
        } catch (err) {
          console.error('Network connection error:', err);
          await SystemDialog.alert('Network connection error: ' + err.message, { title: 'Connection Error', type: 'danger' });
        } finally {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      });

      // 7. Live Waiting Ticket Screen Logic
      function updateTicketDiningDisplay(type, table) {
        var tag = document.getElementById('ticketDiningTag');
        var btn = document.getElementById('btnSwitchDiningPostOrder');
        if (!tag) return;

        var isDine = (type === 'dine_in');
        if (isDine) {
          tag.innerHTML = `🪑 Dine In · Table #${table || '1'}`;
          tag.style.background = 'rgba(226, 135, 67, 0.18)';
          tag.style.color = '#FDBA74';
          tag.style.borderColor = 'rgba(226, 135, 67, 0.35)';
          if (btn) btn.textContent = 'Accidentally chose Dine In? Switch to Take Out';
        } else {
          tag.innerHTML = `🛍️ Take Out · Counter Pickup`;
          tag.style.background = 'rgba(16, 185, 129, 0.18)';
          tag.style.color = '#6EE7B7';
          tag.style.borderColor = 'rgba(16, 185, 129, 0.35)';
          if (btn) btn.textContent = 'Accidentally chose Take Out? Switch to Dine In';
        }
      }

      function launchLiveTicketScreen(orderRef, queueNum, items, initialType, initialTable) {
        activeOrderRef = orderRef;
        sessionStorage.setItem('active_ticket_ref', orderRef);
        sessionStorage.setItem('active_ticket_queue', queueNum);

        updateTicketDiningDisplay(initialType || currentOrderType, initialTable || currentTableNumber);

        // Reset and start stopwatch at 00:00
        stopwatchSeconds = 0;
        document.getElementById('stopwatchDisplay').textContent = '00:00';
        if (stopwatchTimer) clearInterval(stopwatchTimer);
        stopwatchTimer = setInterval(function() {
          stopwatchSeconds++;
          var m = Math.floor(stopwatchSeconds / 60);
          var s = stopwatchSeconds % 60;
          document.getElementById('stopwatchDisplay').textContent = 
            `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }, 1000);

        document.getElementById('ticketQueueNum').textContent = `#${queueNum}`;
        document.getElementById('ticketStatusText').textContent = 'Order Sent – Waiting for Barista.';
        document.getElementById('ticketStatusCard').className = 'ticket-status-card status-pending';
        document.getElementById('pickupCelebrateWrap').style.display = 'none';

        var summaryHtml = items.map(function(it) {
          return `<div>• ${it.quantity}x <strong>${it.name || it.item_name}</strong> (${it.temperature} · ${it.milk_option})</div>`;
        }).join('');
        document.getElementById('ticketItemsSummary').innerHTML = summaryHtml;

        var screen = document.getElementById('orderTicketScreen');
        screen.classList.remove('is-ready');
        screen.classList.add('active');

        // Start 2.5s Polling loop
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollTicketStatus, 2500);
      }

      async function pollTicketStatus() {
        if (!activeOrderRef) return;

        try {
          var res = await fetch(`api/orders.php?reference=${encodeURIComponent(activeOrderRef)}`, { cache: 'no-store' });
          if (!res.ok) return;
          var data = await res.json();
          if (!data.success || !data.order) return;

          var ord = data.order;
          updateTicketDiningDisplay(ord.order_type, ord.table_number);
          if (ord.status === 'completed' || ord.status === 'cancelled') {
            var switchBtn = document.getElementById('btnSwitchDiningPostOrder');
            if (switchBtn) switchBtn.style.display = 'none';
          }

          document.getElementById('ordersAheadDisplay').textContent = `${ord.orders_ahead} orders ahead of you`;

          // State 1: In Progress
          if (ord.status === 'in_progress') {
            document.getElementById('ticketStatusCard').className = 'ticket-status-card status-in_progress';
            document.getElementById('ticketStatusText').textContent = 'Brewing in Progress – Barista is crafting your order.';
            document.getElementById('ticketSubNotice').textContent = 'Your order is currently being prepared on the espresso bar.';
          }

          // State 2: Completed / Pickup Takeover
          if (ord.status === 'completed') {
            clearInterval(pollTimer);
            clearInterval(stopwatchTimer);

            var screen = document.getElementById('orderTicketScreen');
            screen.classList.add('is-ready');
            document.getElementById('ticketStatusCard').style.display = 'none';
            document.getElementById('pickupCelebrateWrap').style.display = 'block';

            // Audio notification ding
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

            // Haptic vibration on mobile
            if (navigator.vibrate) {
              navigator.vibrate([200, 100, 200]);
            }
          }
        } catch (e) {
          console.warn('Polling error:', e);
        }
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

      // Dismiss Pickup Screen
      document.getElementById('btnDismissPickup').addEventListener('click', function() {
        sessionStorage.removeItem('active_ticket_ref');
        sessionStorage.removeItem('active_ticket_queue');
        document.getElementById('orderTicketScreen').classList.remove('active', 'is-ready');
        document.getElementById('ticketStatusCard').style.display = 'block';
      });

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

      // Resume active ticket on page reload if present in sessionStorage
      var savedRef = sessionStorage.getItem('active_ticket_ref');
      var savedQueue = sessionStorage.getItem('active_ticket_queue');
      if (savedRef && savedQueue) {
        launchLiveTicketScreen(savedRef, savedQueue, []);
      }

      // Initial Menu Load
      loadMenu();

    })();
  </script>
</body>
</html>
