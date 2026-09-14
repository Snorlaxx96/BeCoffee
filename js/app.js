/**
 * BeCoffee — Interactive Application Logic
 * Philippine Specialty Coffee & Roastery Experience
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. State Management ---
  const state = {
    activeCategory: 'all',
    activeLocation: 'bgc',
    activeFlavorFilter: null,
    cart: JSON.parse(localStorage.getItem('becoffee_cart') || '[]'),
    menuItems: [
      // Philippine Single Origin Pour-Overs
      {
        id: 'terroir-sagada',
        category: 'terroir',
        name: 'Sagada Arabica Reserve',
        origin: 'Sagada, Mountain Province',
        elevation: '1,500m MASL',
        varietal: 'Typica & Bourbon',
        process: 'Washed Process',
        price: 240,
        description: 'Delicate floral aromas with bright notes of orange blossom, wild Cordillera mountain honey, and a clean brown sugar finish.',
        tags: ['Single Origin', 'Direct Trade', 'Light Roast'],
        image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'fruity-floral'
      },
      {
        id: 'terroir-benguet',
        category: 'terroir',
        name: 'Benguet Atok Single Estate',
        origin: 'Atok, Benguet Highlands',
        elevation: '1,650m MASL',
        varietal: 'Red Bourbon & San Ramon',
        process: 'Honey Process',
        price: 220,
        description: 'Crisp green apple acidity melting into roasted macadamia, buttery mouthfeel, and a lingering milk chocolate sweetness.',
        tags: ['Single Origin', 'High Elevation', 'Medium Roast'],
        image: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      },
      {
        id: 'terroir-apo',
        category: 'terroir',
        name: 'Mt. Apo Anaerobic Natural',
        origin: 'Bansalan, Davao del Sur',
        elevation: '1,600m MASL',
        varietal: 'Catimor & Yellow Bourbon',
        process: '72h Anaerobic Natural',
        price: 280,
        description: 'Intense aromatic explosion of ripe strawberry, passionfruit coulis, cacao nibs, and velvety red wine body.',
        tags: ['Award Winner', 'Micro-lot', 'Fruit Bomb'],
        image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'fruity-floral'
      },
      {
        id: 'terroir-matutum',
        category: 'terroir',
        name: 'Mt. Matutum Mountain Honey',
        origin: 'Tupi, South Cotabato',
        elevation: '1,400m MASL',
        varietal: 'Bourbon & Typica',
        process: 'Yellow Honey',
        price: 230,
        description: 'Jasmine floral perfume, ripe yellow peach, silky muscovado cane sugar, with an exceptionally balanced tea-like cup.',
        tags: ['Single Origin', 'Silky Body', 'Organic'],
        image: 'https://images.unsplash.com/photo-1518832553480-cd0e625ed3e6?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },

      // Espresso Bar
      {
        id: 'esp-house',
        category: 'espresso',
        name: 'BeCoffee Signature Espresso',
        origin: 'Benguet + Ethiopia Yirgacheffe',
        elevation: 'Blend',
        price: 160,
        description: 'Our award-winning flagship extraction with dense hazelnut crema, dark cacao, and candied bergamot citrus twist.',
        tags: ['Double Shot', 'House Roast'],
        image: 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      },
      {
        id: 'esp-spanish',
        category: 'espresso',
        name: 'Manila Muscovado Spanish Latte',
        origin: 'House Espresso & Oat Milk',
        elevation: 'Specialty',
        price: 210,
        description: 'Velvety microfoam latte infused with slow-simmered Negros Island unrefined muscovado sugar and a pinch of rock salt.',
        tags: ['Crowd Favorite', 'Oat Milk Available'],
        image: 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },
      {
        id: 'esp-horchata',
        category: 'espresso',
        name: 'House Dirty Horchata',
        origin: 'Toasted Rice & Cinnamon',
        elevation: 'Specialty',
        price: 230,
        description: 'Artisanal heirloom toasted rice milk with Mexican vanilla, Ceylon cinnamon, capped with a concentrated double ristretto.',
        tags: ['Dairy-Free', 'Signature'],
        image: 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },
      {
        id: 'esp-flatwhite',
        category: 'espresso',
        name: 'Highland Flat White',
        origin: 'Sagada Peaberry Extract',
        elevation: 'Double Ristretto',
        price: 180,
        description: 'Tight micro-textured steamed fresh milk folded into intense double ristretto shots for the ultimate tactile coffee experience.',
        tags: ['Classic', 'Barista Pick'],
        image: 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      },

      // Cold Brews & Signatures
      {
        id: 'cold-pandan',
        category: 'cold',
        name: 'Pandan Sea Salt Cold Foam Brew',
        origin: '18h Steeped Sagada Arabica',
        elevation: 'Cold Steeped',
        price: 240,
        description: 'Ultra-smooth slow drip cold brew topped with freshly whipped artisanal pandan cold foam and smoked Ilocos sea salt crystals.',
        tags: ['Signature Drink', 'Bestseller'],
        image: 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },
      {
        id: 'cold-calamansi',
        category: 'cold',
        name: 'Calamansi Espresso Tonic',
        origin: 'Native Philippine Citrus',
        elevation: 'Effervescent',
        price: 220,
        description: 'Fresh-pressed native calamansi cordial, botanical tonic water over crystal ice, crowned with a chilled Benguet espresso float.',
        tags: ['Refreshing', 'Citrus Sparkling'],
        image: 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'citrus-bright'
      },
      {
        id: 'cold-tablea',
        category: 'cold',
        name: 'Davao 70% Tablea Dark Mocha',
        origin: 'Malagos Davao Single Origin Cacao',
        elevation: 'Artisanal Cacao',
        price: 230,
        description: 'Direct-trade stone-ground Philippine dark chocolate whisked with fresh whole milk and a rich espresso shot. Served iced or hot.',
        tags: ['Single Origin Cacao', 'Rich & Decadent'],
        image: 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      },
      {
        id: 'cold-cascara',
        category: 'cold',
        name: 'Benguet Cascara Sparkling Spritz',
        origin: 'Sun-Dried Coffee Cherry Husk',
        elevation: 'Naturally Sweet',
        price: 190,
        description: 'Upcycled coffee cherry husk brewed into an amber tea with notes of rosehip, hibiscus, and tamarind, paired with sparkling tonic and lime.',
        tags: ['Antioxidant Rich', 'Low Caffeine', 'Eco Trade'],
        image: 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'citrus-bright'
      },

      // Artisanal Bakery & Philippine Brunch
      {
        id: 'brunch-cheesecake',
        category: 'brunch',
        name: 'Ube Halaya Basque Burnt Cheesecake',
        origin: 'Bohol Purple Yam & Cream Cheese',
        elevation: 'Baked In-House Daily',
        price: 260,
        description: 'Silky, deeply caramelized exterior with a molten center layered with homemade slow-stirred Boholano ube halaya jam.',
        tags: ['Dessert', 'House Specialty'],
        image: 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },
      {
        id: 'brunch-frenchtoast',
        category: 'brunch',
        name: 'Pan de Sal Brioche French Toast',
        origin: 'Local Bakery Craft',
        elevation: 'Brunch Classic',
        price: 320,
        description: 'Handcrafted brioche pan de sal soaked in vanilla custard, seared golden, served with whipped Davao honeycomb butter and Sagada honey.',
        tags: ['Breakfast Favorite', 'Vegetarian'],
        image: 'https://images.unsplash.com/photo-1484723091739-004a825eb2fb?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'sweet-caramel'
      },
      {
        id: 'brunch-tartine',
        category: 'brunch',
        name: 'Truffled Wild Mushroom Tartine',
        origin: 'Organic Tagaytay Fungi',
        elevation: 'Savory Artisanal',
        price: 380,
        description: 'Naturally leavened sourdough bread toasted with garlic confit, sautéed forest oyster mushrooms, organic soft poached egg, and white truffle oil.',
        tags: ['Savory', 'Vegetarian'],
        image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      },
      {
        id: 'brunch-croissant',
        category: 'brunch',
        name: 'Pain au Chocolat w/ Tablea Ganache',
        origin: 'French Butter & Davao Cacao',
        elevation: 'Viennoiserie',
        price: 190,
        description: '100% Normandy cultured butter pastry lamination filled with double batons of Davao dark chocolate tablea ganache.',
        tags: ['Pastry', 'Baked Daily 7AM'],
        image: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=700&q=80',
        flavorProfile: 'choc-nutty'
      }
    ]
  };

  // --- 2. DOM Elements ---
  const menuGrid = document.getElementById('menuGrid');
  const categoryTabs = document.querySelectorAll('.filter-tab');
  const flavorButtons = document.querySelectorAll('.flavor-btn');
  const cartToggleBtn = document.getElementById('cartToggleBtn');
  const cartDrawerOverlay = document.getElementById('cartDrawerOverlay');
  const closeDrawerBtn = document.getElementById('closeDrawerBtn');
  const cartItemsContainer = document.getElementById('cartItemsContainer');
  const cartBadge = document.getElementById('cartBadge');
  const cartSubtotalEl = document.getElementById('cartSubtotal');
  const cartEcoFeeEl = document.getElementById('cartEcoFee');
  const cartGrandTotalEl = document.getElementById('cartGrandTotal');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const mobileToggle = document.getElementById('mobileToggle');
  const navLinks = document.getElementById('navLinks');
  const liveStatusText = document.getElementById('liveStatusText');
  const siteHeader = document.getElementById('siteHeader');

  // Reservation Modal Elements
  const reserveModalOverlay = document.getElementById('reserveModalOverlay');
  const openReserveBtns = document.querySelectorAll('.open-reserve-trigger');
  const closeReserveBtn = document.getElementById('closeReserveBtn');
  const reservationForm = document.getElementById('reservationForm');
  const bookingTypeSelect = document.getElementById('bookingType');
  const bookingGuestsInput = document.getElementById('bookingGuests');
  const bookingPriceEstimate = document.getElementById('bookingPriceEstimate');

  // Location Outpost Tabs
  const locTabBtns = document.querySelectorAll('.loc-tab-btn');
  const locCardTitle = document.getElementById('locCardTitle');
  const locCardBadge = document.getElementById('locCardBadge');
  const locAddressText = document.getElementById('locAddressText');
  const locHoursText = document.getElementById('locHoursText');
  const locPhoneText = document.getElementById('locPhoneText');
  const locFeaturesText = document.getElementById('locFeaturesText');
  const locImage = document.getElementById('locImage');

  // --- 3. Format Currency in Philippine Pesos (₱) ---
  function formatPHP(amount) {
    return `₱${amount.toLocaleString('en-PH')}`;
  }

  // --- 4. Live Philippine Standard Time (PHT, UTC+8) Status ---
  function updatePhilippineHours() {
    try {
      const now = new Date();
      // Get Philippine Time
      const phtString = now.toLocaleString('en-US', { timeZone: 'Asia/Manila' });
      const phtDate = new Date(phtString);
      const hours = phtDate.getHours();

      // Open 7:00 AM to 10:00 PM (22:00)
      const isOpen = hours >= 7 && hours < 22;
      if (liveStatusText) {
        if (isOpen) {
          liveStatusText.innerHTML = `<span class="pulse-dot"></span> Open Today · 7:00 AM – 10:00 PM PHT`;
        } else {
          liveStatusText.innerHTML = `<span class="pulse-dot" style="background:#DF9B64;"></span> Opens 7:00 AM PHT · Roastery Resting`;
        }
      }
    } catch (e) {
      if (liveStatusText) {
        liveStatusText.innerHTML = `<span class="pulse-dot"></span> Open Today · 7:00 AM – 10:00 PM PHT`;
      }
    }
  }
  updatePhilippineHours();
  setInterval(updatePhilippineHours, 60000);

  // --- 5. Sticky Header Effect ---
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      siteHeader.classList.add('scrolled');
    } else {
      siteHeader.classList.remove('scrolled');
    }
  });

  // --- 6. Mobile Menu Navigation ---
  if (mobileToggle && navLinks) {
    mobileToggle.addEventListener('click', () => {
      mobileToggle.classList.toggle('active');
      navLinks.classList.toggle('active');
    });

    // Close when clicking link
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileToggle.classList.remove('active');
        navLinks.classList.remove('active');
      });
    });
  }

  // --- 7. Render Menu Items ---
  function renderMenu() {
    if (!menuGrid) return;

    let items = state.menuItems;

    // Filter by Category
    if (state.activeCategory !== 'all') {
      items = items.filter(item => item.category === state.activeCategory);
    }

    // Filter by Flavor Note Quiz
    if (state.activeFlavorFilter) {
      items = items.filter(item => item.flavorProfile === state.activeFlavorFilter);
    }

    if (items.length === 0) {
      menuGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: var(--color-text-secondary);">
          <p style="font-size: 1.2rem; font-family: var(--font-serif); margin-bottom: 0.5rem;">No menu offers matching this profile currently.</p>
          <button type="button" id="resetFiltersBtn" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Reset Filters</button>
        </div>
      `;
      const resetBtn = document.getElementById('resetFiltersBtn');
      if (resetBtn) {
        resetBtn.addEventListener('click', () => {
          state.activeCategory = 'all';
          state.activeFlavorFilter = null;
          categoryTabs.forEach(t => t.classList.toggle('active', t.dataset.category === 'all'));
          flavorButtons.forEach(b => b.classList.remove('active'));
          renderMenu();
        });
      }
      return;
    }

    menuGrid.innerHTML = items.map(item => `
      <article class="menu-card" data-id="${item.id}">
        <div class="menu-card-img-wrap">
          <img src="${item.image}" alt="${item.name}" loading="lazy" width="700" height="438">
          <span class="card-pill-tag">${item.tags[0] || 'Specialty'}</span>
        </div>
        <div class="menu-card-body">
          <div class="card-title-row">
            <h3 class="item-name">${item.name}</h3>
            <span class="item-price">${formatPHP(item.price)}</span>
          </div>
          <p class="item-desc">${item.description}</p>
          <div class="item-meta-tags">
            <span class="meta-pill">${item.origin}</span>
            <span class="meta-pill">${item.elevation}</span>
            ${item.tags[1] ? `<span class="meta-pill">${item.tags[1]}</span>` : ''}
          </div>
          <div class="card-action-row">
            <span class="item-origin-sub">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              PH Terroir
            </span>
            <button type="button" class="add-btn add-to-cart-trigger" data-id="${item.id}" aria-label="Add ${item.name} to order">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Add to Order
            </button>
          </div>
        </div>
      </article>
    `).join('');

    // Attach Click Events to Add Buttons
    menuGrid.querySelectorAll('.add-to-cart-trigger').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        addToCart(id);
      });
    });
  }

  // --- 8. Category Filter Tabs Event ---
  categoryTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      categoryTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      state.activeCategory = tab.dataset.category;
      renderMenu();
    });
  });

  // --- 9. Flavor Matcher Quiz Event ---
  flavorButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const flavor = btn.dataset.flavor;
      if (state.activeFlavorFilter === flavor) {
        state.activeFlavorFilter = null;
        btn.classList.remove('active');
      } else {
        flavorButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.activeFlavorFilter = flavor;
      }
      renderMenu();

      // Smooth scroll to menu
      const menuSec = document.getElementById('menu');
      if (menuSec) {
        menuSec.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // --- 10. Cart System & Calculations ---
  function saveCart() {
    localStorage.setItem('becoffee_cart', JSON.stringify(state.cart));
    updateCartUI();
  }

  function addToCart(itemId) {
    const item = state.menuItems.find(i => i.id === itemId);
    if (!item) return;

    const existing = state.cart.find(i => i.id === itemId);
    if (existing) {
      existing.qty += 1;
    } else {
      state.cart.push({
        id: item.id,
        name: item.name,
        price: item.price,
        qty: 1
      });
    }

    saveCart();
    showToast(`Added "${item.name}" to order (${formatPHP(item.price)})`);
  }

  function updateQty(itemId, change) {
    const item = state.cart.find(i => i.id === itemId);
    if (!item) return;

    item.qty += change;
    if (item.qty <= 0) {
      state.cart = state.cart.filter(i => i.id !== itemId);
    }
    saveCart();
  }

  function updateCartUI() {
    const totalCount = state.cart.reduce((sum, i) => sum + i.qty, 0);
    if (cartBadge) {
      cartBadge.textContent = totalCount;
      cartBadge.style.display = totalCount > 0 ? 'flex' : 'none';
    }

    if (!cartItemsContainer) return;

    if (state.cart.length === 0) {
      cartItemsContainer.innerHTML = `
        <div class="cart-empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
            <line x1="6" y1="1" x2="6" y2="4"></line>
            <line x1="10" y1="1" x2="10" y2="4"></line>
            <line x1="14" y1="1" x2="14" y2="4"></line>
          </svg>
          <p style="font-weight: 600; color: var(--color-roast-obsidian); margin-bottom: 0.25rem;">Your coffee order is empty</p>
          <p style="font-size: 0.85rem;">Explore our single-origins, espresso drinks, or bakery offerings.</p>
        </div>
      `;
      if (cartSubtotalEl) cartSubtotalEl.textContent = '₱0';
      if (cartEcoFeeEl) cartEcoFeeEl.textContent = '₱0';
      if (cartGrandTotalEl) cartGrandTotalEl.textContent = '₱0';
      if (checkoutBtn) checkoutBtn.disabled = true;
      return;
    }

    const subtotal = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const ecoFee = 25; // ₱25 sustainable bamboo packaging fee
    const grandTotal = subtotal + ecoFee;

    cartItemsContainer.innerHTML = state.cart.map(item => `
      <div class="cart-item-row" data-id="${item.id}">
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-price">${formatPHP(item.price)} each</div>
        </div>
        <div class="cart-qty-ctrl">
          <button type="button" class="qty-btn qty-minus" data-id="${item.id}" aria-label="Decrease quantity">−</button>
          <span class="qty-val">${item.qty}</span>
          <button type="button" class="qty-btn qty-plus" data-id="${item.id}" aria-label="Increase quantity">+</button>
        </div>
      </div>
    `).join('');

    // Attach events to qty buttons
    cartItemsContainer.querySelectorAll('.qty-minus').forEach(b => {
      b.addEventListener('click', () => updateQty(b.dataset.id, -1));
    });
    cartItemsContainer.querySelectorAll('.qty-plus').forEach(b => {
      b.addEventListener('click', () => updateQty(b.dataset.id, 1));
    });

    if (cartSubtotalEl) cartSubtotalEl.textContent = formatPHP(subtotal);
    if (cartEcoFeeEl) cartEcoFeeEl.textContent = formatPHP(ecoFee);
    if (cartGrandTotalEl) cartGrandTotalEl.textContent = formatPHP(grandTotal);
    if (checkoutBtn) checkoutBtn.disabled = false;
  }

  // Cart Drawer open/close
  if (cartToggleBtn && cartDrawerOverlay && closeDrawerBtn) {
    cartToggleBtn.addEventListener('click', () => {
      cartDrawerOverlay.classList.add('active');
    });

    closeDrawerBtn.addEventListener('click', () => {
      cartDrawerOverlay.classList.remove('active');
    });

    cartDrawerOverlay.addEventListener('click', (e) => {
      if (e.target === cartDrawerOverlay) {
        cartDrawerOverlay.classList.remove('active');
      }
    });
  }

  // Checkout order action
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if (state.cart.length === 0) return;
      const subtotal = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
      const grandTotal = subtotal + 25;

      const orderList = state.cart.map(i => `• ${i.qty}x ${i.name} (${formatPHP(i.price * i.qty)})`).join('\n');
      const orderMessage = `Mabuhay! Here is my BeCoffee order:\n\n${orderList}\n\nTotal: ${formatPHP(grandTotal)} (incl. ₱25 eco pack)\nPickup: BGC Flagship Sanctuary\n\nThank you!`;

      // Prompt user or copy to clipboard
      if (navigator.clipboard) {
        navigator.clipboard.writeText(orderMessage);
        showToast('Order summary copied to clipboard! Ready to send to barista.');
      } else {
        showToast(`Order total: ${formatPHP(grandTotal)}. Ready for pickup!`);
      }

      // Reset cart
      state.cart = [];
      saveCart();
      if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
    });
  }

  // --- 11. Reservations & Workshops Modal ---
  function updateReservationEstimate() {
    if (!bookingTypeSelect || !bookingGuestsInput || !bookingPriceEstimate) return;
    const type = bookingTypeSelect.value;
    const guests = Math.max(1, parseInt(bookingGuestsInput.value, 10) || 1);

    let price = 0;
    if (type === 'cupping') {
      price = 1850 * guests; // ₱1,850 per person
      bookingPriceEstimate.textContent = `${formatPHP(price)} (₱1,850 × ${guests} guests)`;
    } else if (type === 'espresso-class') {
      price = 2400 * guests; // ₱2,400 per person
      bookingPriceEstimate.textContent = `${formatPHP(price)} (₱2,400 × ${guests} guests)`;
    } else if (type === 'cowork') {
      price = 450 * guests;  // ₱450 per day pass
      bookingPriceEstimate.textContent = `${formatPHP(price)} (₱450 × ${guests} day passes)`;
    } else if (type === 'table') {
      bookingPriceEstimate.textContent = 'Complimentary table reservation (Order on arrival)';
    } else if (type === 'private-roastery') {
      bookingPriceEstimate.textContent = 'Starting at ₱15,000 for 3 hours (Custom Roaster Quote)';
    }
  }

  if (bookingTypeSelect && bookingGuestsInput) {
    bookingTypeSelect.addEventListener('change', updateReservationEstimate);
    bookingGuestsInput.addEventListener('input', updateReservationEstimate);
    updateReservationEstimate();
  }

  // Open Reservation Modal
  openReserveBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const presetType = btn.dataset.presetType;
      if (presetType && bookingTypeSelect) {
        bookingTypeSelect.value = presetType;
        updateReservationEstimate();
      }
      if (reserveModalOverlay) reserveModalOverlay.classList.add('active');
    });
  });

  // Close Reservation Modal
  if (closeReserveBtn && reserveModalOverlay) {
    closeReserveBtn.addEventListener('click', () => {
      reserveModalOverlay.classList.remove('active');
    });
    reserveModalOverlay.addEventListener('click', (e) => {
      if (e.target === reserveModalOverlay) {
        reserveModalOverlay.classList.remove('active');
      }
    });
  }

  // Form Submit
  if (reservationForm) {
    reservationForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('bookingName').value;
      const date = document.getElementById('bookingDate').value;
      const time = document.getElementById('bookingTime').value;

      showToast(`Reservation request received for ${name} on ${date} at ${time}! We will SMS confirm.`);
      reservationForm.reset();
      updateReservationEstimate();
      if (reserveModalOverlay) reserveModalOverlay.classList.remove('active');
    });
  }

  // --- 12. Philippine Outpost Tabs (BGC vs Benguet) ---
  const locationsData = {
    bgc: {
      name: 'BGC Flagship Sanctuary',
      badge: 'Metro Manila Flagship & Slow Bar',
      address: 'Upper Ground Floor, Bonifacio High Street Central, 7th Ave, BGC, Taguig, Metro Manila',
      hours: 'Mon – Sun: 7:00 AM – 10:00 PM PHT',
      phone: '+63 2 8888-2633 / +63 917 555 2333',
      features: 'High-speed 500Mbps Fiber, Outdoor Garden Seating, Slow Bar & Siphon, Pour-over Flights',
      image: 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80'
    },
    benguet: {
      name: 'Highland Roastery & Farm Lab',
      badge: 'Cordillera Terroir Outpost & Cupping Lab',
      address: 'Ambuclao Scenic Road, Tuba, Benguet (15 mins from Baguio City)',
      hours: 'Wed – Sun: 8:00 AM – 7:00 PM PHT (Closed Mon & Tue)',
      phone: '+63 74 442 8888 / +63 919 888 2333',
      features: 'Pine Forest Panorama, Direct Farm Cupping Pavilion, Solar Roasting Demonstration, Cold Mountain Air',
      image: 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=800&q=80'
    }
  };

  locTabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const locKey = btn.dataset.location;
      locTabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const data = locationsData[locKey];
      if (!data) return;

      if (locCardTitle) locCardTitle.textContent = data.name;
      if (locCardBadge) locCardBadge.textContent = data.badge;
      if (locAddressText) locAddressText.textContent = data.address;
      if (locHoursText) locHoursText.textContent = data.hours;
      if (locPhoneText) locPhoneText.textContent = data.phone;
      if (locFeaturesText) locFeaturesText.textContent = data.features;
      if (locImage) {
        locImage.src = data.image;
        locImage.alt = data.name;
      }
    });
  });

  // --- 13. Toast Notification System ---
  function showToast(message) {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C88A58" stroke-width="2.5" aria-hidden="true">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
      <span>${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(12px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  // --- 14. Initial Render ---
  renderMenu();
  updateCartUI();
});
