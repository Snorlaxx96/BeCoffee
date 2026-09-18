# PRODUCT.md — BeCoffee Product Truth

## Overview
- **Product Name:** BeCoffee (Philippine Artisanal Specialty Cafe & Roastery)
- **Status:** Active / Greenfield
- **Platform:** Web (Responsive Desktop & Mobile)
- **Stack:** Modern Semantic HTML5, Modular Vanilla CSS (Modern CSS Variables & Tokens), Vanilla ES6+ JavaScript. Designed for zero-dependency local delivery and instant Apache / XAMPP / Web Server deployment.
- **Location:** Bonifacio Global City (BGC), Taguig & Rizal Ridge, Philippines
- **Currency:** Philippine Peso (₱ / PHP)

## Mission & Positioning
BeCoffee is a premier Philippine third-wave cafe and specialty roastery celebrating hyper-local Philippine terroir (Sagada Arabica, Benguet Typica, Mt. Apo Natural, and Mt. Matutum Bourbon) alongside celebrated global single-origins. It merges Scandinavian minimalism with warm Philippine tropical-modern architectural aesthetics (warm teak wood, brushed brass, volcanic slate, and woven rattan textures).

## Target Audience
- Specialty coffee enthusiasts seeking cup profiles and origin transparency.
- Remote professionals, creatives, and digital nomads who value an inspiring space, high-speed Wi-Fi, and curated acoustic playlists.
- Urban diners looking for upscale cafe brunch, artisanal viennoiserie, and handcrafted specialty beverages.
- Coffee hobbyists looking for barista workshops, cupping sessions, and freshly roasted bean subscriptions.

## Core Offerings & Features
1. **Curated Cafe Menus (with interactive categorization & filter system):**
   - **Espresso Bar:** Single Origin & Signature Blend extractions (Espresso, Cortado, Flat White, Spanish Latte with muscovado).
   - **Philippine Terroir Pour-Overs:** Sagada Typica, Benguet Atok Honey, Mt. Apo anaerobic natural with tasting notes, altitude, and producer cards.
   - **Signatures & Cold Brews:** Pandan Sea Salt Cold Foam Brew, Calamansi Espresso Tonic, Tablea Mocha (Davao dark cacao).
   - **Artisanal Bakery & Brunch:** Sourdough toasts, Pain au Chocolat, Ube Basque Burnt Cheesecake, Truffle Mushroom Brioche.
2. **Other Offers & Experiences:**
   - **Roasted Whole Bean Subscriptions & Retail:** Bi-weekly fresh roasts shipped nationwide with grind customization.
   - **Private Tasting & Event Space:** Intimate roastery venue for launches, private dinners, and creative gatherings.
   - **Barista Guild Workshops:** Sensory cupping 101, home espresso calibration, and manual brewing mastery classes.
   - **Co-Working & Creative Sanctuary:** Ergonomic seating, gigabit fiber, and silent audio zones.
3. **Interactive Features:**
   - Real-time interactive menu filter (All, Philippine Single Origins, Espresso, Signature Drinks, Artisanal Brunch).
   - Interactive Coffee Origin Explorer / Flavor Wheel card modal.
   - Dynamic Table & Workshop Reservation / Inquiry modal with instant peso calculation and validation.
   - Live Cafe Status indicator (Open / Closed based on Philippine Standard Time GMT+8).
   - Quick bean order / bag builder cart drawer with instant ₱ calculation.

## Tone & Voice
Refined, warm, knowledgeable, culturally proud, unpretentious, and sensory-driven.

---

## Strategic Scope & Feature Priority Matrix

### P0 Features (Immediate Baseline Delivery)
* **Interactive Menu System**: Category filtering (All, House Coffee, Espresso Bar, Philippine Terroir Pour-Overs, Signatures, Artisanal Brunch), hot/iced size toggles, dynamic price adjustments in ₱ (PHP).
* **Live Cafe & Roastery Hours**: Automatic Philippine Standard Time (PST/PHT, UTC+8) open/closed computation with honest, non-simulated status badge.
* **Shopping Cart & Checkout Drawer**: LocalStorage-persisted cart, real-time item counter, line item addition with size/addon options, dynamic subtotal & tax calculation.
* **Responsive Layouts**: Full viewport adaptation from mobile (375px) to ultra-wide desktop (1440px+), 0 horizontal scroll overflow, >=44px touch targets.
* **Philippine Terroir Showcase**: Origin cards detailing elevation, processing method (Anaerobic, Natural, Washed), tasting notes, and producer stories for Benguet, Sagada, Mt. Apo, and Davao origins.

### P1 Features (Deferred Iteration)
* **Custom Bean Bag Builder**: Real-time whole-bean subscription configurator (grind profile, roast level, delivery frequency).
* **Table & Workshop Reservation Engine**: Multi-step booking modal for cupping sessions and slow-bar tasting seats.

### P2 & P3 Features (Backlog)
* **Roastery Loyalty & Barista Pass**: Digital loyalty stamp cards and tasting journal.
* **B2B Wholesale Portal**: Direct green coffee sourcing inquiries for commercial cafes.

---

## Core Data Entities
* **MenuItem**: `id`, `category`, `name`, `origin`, `elevation`, `price`, `priceIcedM`, `priceIcedL`, `priceHot`, `description`, `tags`, `image`, `flavors`.
* **CartItem**: `id`, `name`, `size`, `price`, `quantity`, `customization`.
* **Reservation**: `id`, `name`, `email`, `phone`, `date`, `time`, `partySize`, `location`, `specialRequests`.
