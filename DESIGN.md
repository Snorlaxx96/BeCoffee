# DESIGN.md — BeCoffee Design System & Aesthetic Directives

> **Design Read:**
> Reading this as: Premium artisanal consumer cafe & roastery web experience for coffee lovers, remote professionals, and diners in the Philippines, with a Warm Tropical-Minimalist & Editorial coffeehouse visual language, leaning toward bespoke typography, warm tactile earth tones, and restrained motion.

---

## Current State (Extracted)

### Extracted Design Tokens
* **Typography:**
  - Headers: `'Playfair Display', serif` (Weights: 400, 700, 900)
  - Body & UI: `'Plus Jakarta Sans', system-ui, sans-serif` (Weights: 400, 500, 600, 700)
  - Body font-size: `16px` with line-height `1.6`
* **Color Palette:**
  - Roasted obsidian (`#15110E`), espresso dark (`#221B17`), surface (`#2D231E`)
  - Crema amber (`#C88A58`), crema gold (`#DF9B64`), crema light (`#F5E9DF`)
  - Highland moss (`#4A5D43`), cacao terracotta (`#8D3D2B`)
  - Canvas background (`#FAF7F2`), card background (`#FFFFFF`), neutral subtle (`#F3ECE3`)
* **Radii & Elevation:**
  - Radii: `8px` (sm), `14px` (md), `20px` (lg), `9999px` (full)
  - Shadows: `0 2px 8px rgba(21, 17, 14, 0.04)`, `0 8px 24px rgba(21, 17, 14, 0.08)`, `0 16px 40px rgba(21, 17, 14, 0.12)`
* **Structural Components:**
  - Sticky announcement banner + header navigation
  - Split asymmetrical hero with origin counters
  - Tabbed interactive menu grid with size/price toggles
  - Terroir showcase grid with altitude cards
  - Experience modals (Workshops, Roastery booking, Order Drawer)
* **Impeccable Audit Findings (177 anti-patterns detected):**
  - Repeated icon-tile-stack (52×52px squircle icon cards)
  - Cramped text padding in bordered sections
  - Overused font: Plus Jakarta Sans
  - Kicker above heading pattern
  - Generic cream background `#FAF7F2` default
  - Pulsing status dot simulation

---

## Target Redesign Specification (Path B: Refined Terroir Baseline)

### The Three Canonical Dials
* **`DESIGN_VARIANCE: 7`** (Asymmetric hero framing, alternating editorial showcase modules, staggered grid menus, non-templated layouts)
* **`VISUAL_DENSITY: 3`** (Spacious, airy coffeehouse ambiance, deliberate breathing room, comfortable line heights, zero cramped padding)
* **`MOTION_INTENSITY: 5`** (Restrained, fluid 250ms–350ms transitions, smooth modal drawers, subtle image scale hovers)

### Target Typography Pairing
* **Display & Editorial Headings:** `'Playfair Display', Georgia, serif` (editorial craft, artisanal warmth)
* **Body, UI, & Metadata:** `'Outfit', system-ui, -apple-system, sans-serif` (replaces overused Plus Jakarta Sans with refined geometric clarity)
* **Accents & Notes:** `0.05em` letter-spacing, human-scale font sizes (14px–16px), zero all-caps on body text.

### Anti-Pattern Remedies (Impeccable Compliance)
1. **Font Replacement:** Upgrade body font to `'Outfit'` to clear `[overused-font]`.
2. **Palette Calibration:** Refine canvas from generic off-white `#FAF7F2` to bespoke Terroir Alabaster `#FBF8F4` and rich oat surface `#F4EDE4`.
3. **Eliminate Icon Tiles:** Remove 52×52px squircle icon containers above headings; present icons in organic visual flow or inline with titles.
4. **Remove Kicker Blocks:** Eliminate disjointed tracked uppercase kickers floating above headings; let headings speak with self-contained strength.
5. **Generous Inset Padding:** Enforce minimum 16px–24px interior padding on all cards, locations, and interactive wrappers to banish `[cramped-padding]`.
6. **Honest Live Status:** Replace decorative blinking/pulsing dot with clean status capsule (`● Open Today · 7:00 AM – 10:00 PM PHT`).
7. **Semantic Hierarchy:** Fix heading order from `h1` through `h4` with zero skipped levels.

---

## 2. Color System (Warm Espresso & Philippine Terroir)

```css
:root {
  /* Brand Foundations */
  --color-roast-obsidian: #15110E;    /* Deep roasted coffee bean / near black */
  --color-roast-dark:     #231B16;    /* Rich espresso core */
  --color-roast-surface:  #2D231E;    /* Dark card background */
  --color-crema-amber:    #C88A58;    /* Silky caramel crema accent */
  --color-crema-gold:     #DF9B64;    /* Hover glow & highlighted tag */
  --color-crema-light:    #F5E9DF;    /* Soft crema badge fill */

  /* Terroir Accents */
  --color-highland-moss:  #53634B;    /* Cordillera highland greenery */
  --color-highland-tint:  #EEF2EB;    /* Light organic badge */
  --color-cacao-terracotta:#9E4733;   /* Davao dark cacao warmth */

  /* Neutral Backgrounds (Light & Warm Mode) */
  --color-bg-canvas:      #FAF7F2;    /* Warm alabaster cream */
  --color-bg-card:        #FFFFFF;    /* Pure card white */
  --color-bg-subtle:      #F3ECE3;    /* Oat milk neutral */
  --color-bg-tint:        #ECE3D6;    /* Elevated surface */

  /* Typography Colors */
  --color-text-primary:   #1A1412;    /* Deep espresso ink (AAA contrast) */
  --color-text-secondary: #5E524C;    /* Roasted bean muted brown (AA contrast) */
  --color-text-tertiary:  #8C7E77;    /* Subtle metadata & notes */
  --color-text-inverse:   #FAF7F2;    /* Crisp cream on dark */
  --color-text-inverse-muted: #B3A7A0;/* Subdued on dark */

  /* Borders & Dividers */
  --color-border-light:   rgba(26, 20, 18, 0.08);
  --color-border-medium:  rgba(26, 20, 18, 0.15);
  --color-border-dark:    rgba(250, 247, 242, 0.12);

  /* Elevation & Shadows */
  --shadow-sm:  0 2px 8px rgba(21, 17, 14, 0.04);
  --shadow-md:  0 8px 24px rgba(21, 17, 14, 0.08);
  --shadow-lg:  0 16px 40px rgba(21, 17, 14, 0.12);
  --shadow-glow:0 0 24px rgba(200, 138, 88, 0.25);
}
```

---

## 3. Typography Hierarchy

- **Display & Section Headers:** `'Playfair Display', serif`
  - Expresses timeless craft, artisanal heritage, and editorial warmth.
- **Body, UI, & Numbers:** `'Plus Jakarta Sans', -apple-system, sans-serif`
  - High-legibility geometric sans with clear open apertures.
- **Micro Accents & Tags:** Uppercase letter-spacing (`0.12em`, `font-size: 0.75rem`, `font-weight: 600`).

---

## 4. Layout & Spacing Principles

- **Fluid Containment:** Max-width `1280px` with responsive padding `clamp(1.25rem, 5vw, 3.5rem)`.
- **Vertical Rhythm:**
  - Section Spacing: `clamp(4.5rem, 9vw, 8rem)`
  - Card Gap: `clamp(1.25rem, 2.5vw, 2rem)`
- **Proactive Defenses (per UI_Always):**
  - Zero horizontal overflow (`overflow-x: clip / hidden` on parents where appropriate).
  - Explicit `min-width: 0` on flex items containing text.
  - Form input minimum font size: `16px` (prevent iOS Safari zoom).
  - Interactive click target minimum: `44px × 44px`.
  - Accessible `:focus-visible` custom crema outline.

---

## 5. Micro-Interactions & Experience Details

1. **Active Live Hours Badge:** Computes Philippine Time (PHT, UTC+8) to show `● Open Today until 10:00 PM` or `○ Opens tomorrow at 7:00 AM`.
2. **Tabbed Menu Engine:** Real-time filter between All, Philippine Single Origins, Espresso, Signature Creations, and Artisanal Brunch with animated pill indicator.
3. **Currency Display:** Clean Philippine Peso format (`₱180`, `₱240`, `₱320`) with single-origin altitude & processing notes.
4. **Interactive Experience Cards:** Workshop sign-up, bean bag subscriber builder, and private roastery booking modal.
5. **Quick Cart / Order Drawer:** Slide-in drawer with instant line-item addition and total computation in PHP.
