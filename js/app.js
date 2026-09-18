/**
 * BeCoffee — Interactive Application Logic
 * Philippine Specialty Coffee & Roastery Experience
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. State Management ---
  const state = {
    currentUser: null,
    pendingCheckout: false,
    activeCategory: 'all',
    activeLocation: 'bgc',
    activeFlavorFilter: null,
    cart: JSON.parse(localStorage.getItem('becoffee_cart') || '[]'),
    menuItems: [
      // --- 1. HOUSE COFFEE (Iced M: 120 / L: 140 · Hot: 120) ---
      {
        id: 'hc-classic',
        category: 'house-coffee',
        name: 'Classic Coffee',
        origin: 'House Roast Blend',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Smooth, balanced house-brewed coffee with rich nutty undertones and a clean, satisfying finish.',
        tags: ['House Coffee', 'Daily Classic'],
        image: 'images/menu/hc-classic.jpg',
        flavors: ['bold-coffee'],
        flavorLabels: ['Bold & Classic Coffee']
      },
      {
        id: 'hc-spanish',
        category: 'house-coffee',
        name: 'Spanish Latte',
        origin: 'Espresso & Sweetened Milk',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Velvety espresso combined with smooth fresh milk and rich condensed milk for a perfectly sweet kick.',
        tags: ['House Coffee', 'Crowd Favorite'],
        image: 'images/menu/hc-spanish.jpg',
        flavors: ['sweet-caramel', 'bold-coffee'],
        flavorLabels: ['Sweet & Caramel', 'Bold Coffee']
      },
      {
        id: 'hc-americano',
        category: 'house-coffee',
        name: 'Americano',
        origin: 'Double Espresso & Water',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Bold, clean double shot of house espresso poured over hot water or crisp ice.',
        tags: ['House Coffee', 'Bold & Clean'],
        image: 'images/menu/hc-americano.jpg',
        flavors: ['bold-coffee'],
        flavorLabels: ['Bold & Classic Coffee']
      },
      {
        id: 'hc-french-vanilla',
        category: 'house-coffee',
        name: 'French Vanilla Latte',
        origin: 'Espresso & French Vanilla',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Silky espresso and textured milk infused with sweet, aromatic French vanilla bean syrup.',
        tags: ['House Coffee', 'Fragrant Vanilla'],
        image: 'images/menu/hc-french-vanilla.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },
      {
        id: 'hc-coffee-latte',
        category: 'house-coffee',
        name: 'Coffee Latte',
        origin: 'Espresso & Fresh Milk',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'The timeless coffeehouse essential with smooth textured milk folded into freshly pulled espresso.',
        tags: ['House Coffee', 'Smooth & Creamy'],
        image: 'images/menu/hc-coffee-latte.jpg',
        flavors: ['bold-coffee'],
        flavorLabels: ['Bold & Classic Coffee']
      },
      {
        id: 'hc-caramel-macchiato',
        category: 'house-coffee',
        name: 'Caramel Macchiato',
        origin: 'Espresso, Vanilla & Caramel',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Layered vanilla-infused milk crowned with rich espresso and golden buttery caramel drizzle.',
        tags: ['Bestseller', 'Caramel Drizzle'],
        image: 'images/menu/hc-caramel-macchiato.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },
      {
        id: 'hc-salted-caramel',
        category: 'house-coffee',
        name: 'Salted Caramel',
        origin: 'Espresso & Flaky Sea Salt Caramel',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Slow-cooked rich caramel paired with espresso, fresh milk, and a delicate touch of flaky sea salt.',
        tags: ['Bestseller', 'Sweet & Savory'],
        image: 'images/menu/hc-salted-caramel.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },
      {
        id: 'hc-sea-salt-latte',
        category: 'house-coffee',
        name: 'Sea Salt Latte',
        origin: 'Espresso & Sea Salt Cold Cream',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Rich, comforting latte topped with velvety lightly salted cream for a sublime sweet-savory contrast.',
        tags: ['House Coffee', 'Sea Salt Cream'],
        image: 'images/menu/hc-sea-salt-latte.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },
      {
        id: 'hc-ube-latte',
        category: 'house-coffee',
        name: 'Ube Latte',
        origin: 'Espresso & Purple Yam Jam',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Vibrant handcrafted ube halaya blend with fresh milk and a smooth espresso float.',
        tags: ['House Coffee', 'Handcrafted Ube'],
        image: 'images/menu/hc-ube-latte.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },
      {
        id: 'hc-mocha',
        category: 'house-coffee',
        name: 'Mocha',
        origin: 'Espresso & Rich Dark Cocoa',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Decadent dark cocoa melted into freshly pulled espresso and steamed milk.',
        tags: ['House Coffee', 'Rich Cocoa'],
        image: 'images/menu/hc-mocha.jpg',
        flavors: ['chocolate-malt', 'bold-coffee'],
        flavorLabels: ['Chocolate & Malt', 'Bold Coffee']
      },
      {
        id: 'hc-white-choco-mocha',
        category: 'house-coffee',
        name: 'White Chocolate Mocha',
        origin: 'Espresso & White Cacao',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Sweet, velvety white chocolate sauce paired with bold espresso and creamy textured milk.',
        tags: ['House Coffee', 'Sweet White Cocoa'],
        image: 'images/menu/hc-white-choco-mocha.jpg',
        flavors: ['sweet-caramel', 'chocolate-malt'],
        flavorLabels: ['Sweet & Caramel', 'White Chocolate']
      },
      {
        id: 'hc-coffee-milo',
        category: 'house-coffee',
        name: 'Coffee Milo',
        origin: 'Espresso & Malted Milo Milk',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'The ultimate power brew—rich espresso combined with hearty malted Milo chocolate milk.',
        tags: ['House Coffee', 'Malted Power Brew'],
        image: 'images/menu/hc-coffee-milo.jpg',
        flavors: ['chocolate-malt', 'bold-coffee'],
        flavorLabels: ['Chocolate & Malt', 'Bold Coffee']
      },
      {
        id: 'hc-biscoff-latte',
        category: 'house-coffee',
        name: 'Biscoff Latte',
        origin: 'Espresso & Speculoos Cookie Butter',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Spiced Belgian caramelized cookie spread melted into hot or iced espresso and milk.',
        tags: ['House Coffee', 'Cookie Butter'],
        image: 'images/menu/hc-biscoff-latte.jpg',
        flavors: ['sweet-caramel'],
        flavorLabels: ['Sweet & Caramel']
      },

      // --- 2. MATCHA (Iced M: 120 / L: 140 · Hot: 120) ---
      {
        id: 'mat-latte',
        category: 'matcha',
        name: 'Matcha Latte',
        origin: 'Uji Ceremonial Green Tea',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Stone-ground green tea whisked fresh with silky milk for a soothing, umami-rich experience.',
        tags: ['Bestseller', 'Pure Ceremonial'],
        image: 'images/menu/mat-latte.jpg',
        flavors: ['matcha'],
        flavorLabels: ['Ceremonial Matcha']
      },
      {
        id: 'mat-dirty',
        category: 'matcha',
        name: 'Dirty Matcha',
        origin: 'Uji Matcha & Espresso Shot',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Earthy ceremonial matcha latte topped with a concentrated shot of dark espresso.',
        tags: ['Matcha', 'Espresso Float'],
        image: 'images/menu/mat-dirty.jpg',
        flavors: ['matcha', 'bold-coffee'],
        flavorLabels: ['Ceremonial Matcha', 'Bold Coffee']
      },
      {
        id: 'mat-berry',
        category: 'matcha',
        name: 'Matcha Berry',
        origin: 'Uji Matcha & Wild Strawberry',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Layered handcrafted sweet berry compote with milk and crowned with frothy green matcha.',
        tags: ['Matcha', 'Sweet Berry Layer'],
        image: 'images/menu/mat-berry.jpg',
        flavors: ['matcha', 'fruity-berry'],
        flavorLabels: ['Ceremonial Matcha', 'Fruity & Berry']
      },
      {
        id: 'mat-ube',
        category: 'matcha',
        name: 'Matcha Ube',
        origin: 'Uji Matcha & Purple Yam Jam',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Vibrant dual-color fusion of velvety purple yam and green ceremonial matcha tea.',
        tags: ['Matcha', 'Ube Fusion'],
        image: 'images/menu/mat-ube.jpg',
        flavors: ['matcha', 'sweet-caramel'],
        flavorLabels: ['Ceremonial Matcha', 'Sweet & Caramel']
      },
      {
        id: 'mat-choco',
        category: 'matcha',
        name: 'Matchoco',
        origin: 'Uji Matcha & Cocoa Chocolate',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Deep cocoa chocolate swirled together with vibrant ceremonial green tea.',
        tags: ['Matcha', 'Choco Swirl'],
        image: 'images/menu/mat-choco.jpg',
        flavors: ['matcha', 'chocolate-malt'],
        flavorLabels: ['Ceremonial Matcha', 'Chocolate & Malt']
      },
      {
        id: 'mat-caramel',
        category: 'matcha',
        name: 'Matcharamel',
        origin: 'Uji Matcha & Golden Caramel',
        elevation: 'Hot / Iced',
        price: 120,
        priceIcedM: 120,
        priceIcedL: 140,
        priceHot: 120,
        description: 'Ceremonial matcha latte drizzled with luscious buttery golden caramel syrup.',
        tags: ['Matcha', 'Golden Caramel'],
        image: 'images/menu/mat-caramel.jpg',
        flavors: ['matcha', 'sweet-caramel'],
        flavorLabels: ['Ceremonial Matcha', 'Sweet & Caramel']
      },

      // --- 3. HOUSE SPECIALS (Iced M: 90 / L: 110 · Hot: 90) ---
      {
        id: 'hs-milo',
        category: 'house-specials',
        name: 'Milo',
        origin: 'Malted Chocolate Milk',
        elevation: 'Hot / Iced',
        price: 90,
        priceIcedM: 90,
        priceIcedL: 110,
        priceHot: 90,
        description: 'Rich, creamy malted chocolate beverage prepared hot or poured over cracked ice with malt powder.',
        tags: ['Bestseller', 'Nostalgia Classic'],
        image: 'images/menu/hs-milo.jpg',
        flavors: ['chocolate-malt'],
        flavorLabels: ['Chocolate & Malt']
      },
      {
        id: 'hs-choco',
        category: 'house-specials',
        name: 'Choco',
        origin: 'Classic Dark Chocolate Milk',
        elevation: 'Hot / Iced',
        price: 90,
        priceIcedM: 90,
        priceIcedL: 110,
        priceHot: 90,
        description: 'Decadent, velvety chocolate milk made with pure cocoa and smooth fresh milk.',
        tags: ['House Special', 'Pure Chocolate'],
        image: 'images/menu/hs-choco.jpg',
        flavors: ['chocolate-malt'],
        flavorLabels: ['Chocolate & Malt']
      },
      {
        id: 'hs-chocoberry',
        category: 'house-specials',
        name: 'Chocoberry',
        origin: 'Dark Chocolate & Berry Puree',
        elevation: 'Hot / Iced',
        price: 90,
        priceIcedM: 90,
        priceIcedL: 110,
        priceHot: 90,
        description: 'Indulgent sweet chocolate milk infused with real strawberry and mixed berry nectar.',
        tags: ['House Special', 'Berry & Choco'],
        image: 'images/menu/hs-chocoberry.jpg',
        flavors: ['chocolate-malt', 'fruity-berry'],
        flavorLabels: ['Chocolate & Malt', 'Fruity & Berry']
      },

      // --- 4. YOGURT / SODA (Iced M: 80 / L: 100) ---
      {
        id: 'ys-strawberry',
        category: 'yogurt-soda',
        name: 'Strawberry',
        origin: 'Sweet Strawberry Puree',
        elevation: 'Iced Only',
        price: 80,
        priceIcedM: 80,
        priceIcedL: 100,
        priceHot: null,
        description: 'Crisp, refreshing sparkling soda or creamy yogurt drink infused with ripe strawberry puree.',
        tags: ['Yogurt / Soda', 'Iced Only'],
        image: 'images/menu/ys-strawberry.jpg',
        flavors: ['fruity-berry'],
        flavorLabels: ['Fruity & Refreshing']
      },
      {
        id: 'ys-blueberry',
        category: 'yogurt-soda',
        name: 'Blueberry',
        origin: 'Wild Blueberry Puree',
        elevation: 'Iced Only',
        price: 80,
        priceIcedM: 80,
        priceIcedL: 100,
        priceHot: null,
        description: 'Tart and sweet wild blueberry syrup paired with effervescent soda or chilled probiotic yogurt.',
        tags: ['Yogurt / Soda', 'Iced Only'],
        image: 'images/menu/ys-blueberry.jpg',
        flavors: ['fruity-berry'],
        flavorLabels: ['Fruity & Refreshing']
      },
      {
        id: 'ys-mixed-berries',
        category: 'yogurt-soda',
        name: 'Mixed Berries',
        origin: 'Raspberry, Blackberry & Blueberry',
        elevation: 'Iced Only',
        price: 80,
        priceIcedM: 80,
        priceIcedL: 100,
        priceHot: null,
        description: 'Vibrant blend of summer berries sparkling with crisp botanical soda or smooth yogurt.',
        tags: ['Yogurt / Soda', 'Iced Only'],
        image: 'images/menu/ys-mixed-berries.jpg',
        flavors: ['fruity-berry'],
        flavorLabels: ['Fruity & Refreshing']
      },
      {
        id: 'ys-green-apple',
        category: 'yogurt-soda',
        name: 'Green Apple',
        origin: 'Crisp Green Apple Cordial',
        elevation: 'Iced Only',
        price: 80,
        priceIcedM: 80,
        priceIcedL: 100,
        priceHot: null,
        description: 'Zesty, bright green apple cordial served over ice with fizzy sparkling soda or creamy yogurt.',
        tags: ['Yogurt / Soda', 'Iced Only'],
        image: 'images/menu/ys-green-apple.jpg',
        flavors: ['fruity-berry'],
        flavorLabels: ['Fruity & Refreshing']
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

  // Order Customization Modal Elements (Hot/Iced & Medium/Small)
  const orderModalOverlay = document.getElementById('orderModalOverlay');
  const closeOrderModalBtn = document.getElementById('closeOrderModalBtn');
  const orderModalImg = document.getElementById('orderModalImg');
  const orderModalCategory = document.getElementById('orderModalCategory');
  const orderModalTitle = document.getElementById('orderModalTitle');
  const orderModalDesc = document.getElementById('orderModalDesc');
  const tempIcedBtn = document.getElementById('tempIcedBtn');
  const tempHotBtn = document.getElementById('tempHotBtn');
  const tempOptionBadge = document.getElementById('tempOptionBadge');
  const sizeSmallBtn = document.getElementById('sizeSmallBtn');
  const sizeMediumBtn = document.getElementById('sizeMediumBtn');
  const sizeSmallPrice = document.getElementById('sizeSmallPrice');
  const sizeMediumPrice = document.getElementById('sizeMediumPrice');
  const modalQtyMinus = document.getElementById('modalQtyMinus');
  const modalQtyPlus = document.getElementById('modalQtyPlus');
  const modalQtyVal = document.getElementById('modalQtyVal');
  const orderConfirmBtn = document.getElementById('orderConfirmBtn');
  const orderConfirmTotal = document.getElementById('orderConfirmTotal');

  let currentCustomizingItem = null;
  let customTemp = 'Iced';
  let customSize = 'Small';
  let customQty = 1;

  // Reservation Modal Elements
  const reserveModalOverlay = document.getElementById('reserveModalOverlay');
  const openReserveBtns = document.querySelectorAll('.open-reserve-trigger');
  const closeReserveBtn = document.getElementById('closeReserveBtn');
  const reservationForm = document.getElementById('reservationForm');
  const bookingTypeSelect = document.getElementById('bookingType');
  const bookingGuestsInput = document.getElementById('bookingGuests');
  const bookingPriceEstimate = document.getElementById('bookingPriceEstimate');

  // User Authentication & Account Elements
  const authModalOverlay = document.getElementById('authModalOverlay');
  const closeAuthModalBtn = document.getElementById('closeAuthModalBtn');
  const authOpenBtn = document.getElementById('authOpenBtn');
  const mobileAuthTrigger = document.getElementById('mobileAuthTrigger');
  const userProfilePill = document.getElementById('userProfilePill');
  const userNameDisplay = document.getElementById('userNameDisplay');
  const userAvatarBadge = document.getElementById('userAvatarBadge');
  const logoutBtn = document.getElementById('logoutBtn');
  const tabSignInBtn = document.getElementById('tabSignInBtn');
  const tabRegisterBtn = document.getElementById('tabRegisterBtn');
  const signInForm = document.getElementById('signInForm');
  const registerForm = document.getElementById('registerForm');
  const authAlertBox = document.getElementById('authAlertBox');
  const authModalTitle = document.getElementById('authModalTitle');
  const authModalSubtitle = document.getElementById('authModalSubtitle');

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

  // --- 4b. Top Announcement Pop-up Auto-Dismiss (4 seconds) ---
  const topBanner = document.getElementById('topBanner');
  const closeBannerBtn = document.getElementById('closeBannerBtn');

  if (topBanner) {
    let bannerDismissed = false;
    let dismissTimer = null;

    const dismissBanner = () => {
      if (bannerDismissed) return;
      bannerDismissed = true;
      topBanner.classList.add('banner-dismissed');
      setTimeout(() => {
        topBanner.style.display = 'none';
        topBanner.setAttribute('aria-hidden', 'true');
      }, 450);
    };

    // Auto dismiss after exactly 4 seconds
    dismissTimer = setTimeout(dismissBanner, 4000);

    // Manual dismiss button
    if (closeBannerBtn) {
      closeBannerBtn.addEventListener('click', () => {
        clearTimeout(dismissTimer);
        dismissBanner();
      });
    }

    // Pause countdown when user hovers to click/read, resume with 2s grace on mouse leave
    topBanner.addEventListener('mouseenter', () => {
      clearTimeout(dismissTimer);
    });

    topBanner.addEventListener('mouseleave', () => {
      if (!bannerDismissed) {
        dismissTimer = setTimeout(dismissBanner, 2000);
      }
    });
  }

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
      items = items.filter(item => item.flavors && item.flavors.includes(state.activeFlavorFilter));
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

    const categoryLabels = {
      'house-coffee': 'House Coffee',
      'matcha': 'Matcha',
      'house-specials': 'House Specials',
      'yogurt-soda': 'Yogurt / Soda'
    };

    menuGrid.innerHTML = items.map(item => `
      <article class="menu-card" data-id="${item.id}">
        <div class="menu-card-img-wrap">
          <img src="${item.image}?v=6.0" alt="${item.name}" loading="lazy" width="700" height="438">
          <span class="card-pill-tag">${item.tags[0] || 'Specialty'}</span>
        </div>
        <div class="menu-card-body">
          <div class="card-title-row">
            <h3 class="item-name">${item.name}</h3>
            <span class="item-price">${formatPHP(item.price)}</span>
          </div>
          <p class="item-desc">${item.description}</p>
          <div class="card-flavor-row">
            ${(item.flavorLabels || []).map(fl => `<span class="flavor-chip">${fl}</span>`).join('')}
          </div>
          <div class="item-meta-tags">
            ${item.priceHot ? `<span class="meta-pill">Hot / Iced</span>` : '<span class="meta-pill">Iced Only</span>'}
            <span class="meta-pill">Small: ${formatPHP(item.priceIcedM || item.price)}</span>
            ${item.priceIcedL ? `<span class="meta-pill">Medium: ${formatPHP(item.priceIcedL)}</span>` : ''}
          </div>
          <div class="card-action-row">
            <span class="item-origin-sub">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              ${categoryLabels[item.category] || 'Specialty'}
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

  }

  // Universal Click Delegation for Add-To-Cart Buttons (Menu Grid + Bestseller Cards)
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.add-to-cart-trigger');
    if (trigger && trigger.dataset.id) {
      e.preventDefault();
      e.stopPropagation();
      openOrderModal(trigger.dataset.id);
    }
  });

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

        // If current active category doesn't have any items with this flavor, reset category to 'all'
        if (state.activeCategory !== 'all') {
          const hasMatches = state.menuItems.some(
            item => item.category === state.activeCategory && item.flavors && item.flavors.includes(flavor)
          );
          if (!hasMatches) {
            state.activeCategory = 'all';
            categoryTabs.forEach(t => t.classList.toggle('active', t.dataset.category === 'all'));
          }
        }
      }
      renderMenu();

      // Smooth scroll to menu
      const menuSec = document.getElementById('menu');
      if (menuSec) {
        menuSec.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // --- 10. Order Customization Modal & Cart System ---
  function openOrderModal(itemId) {
    const item = state.menuItems.find(i => i.id === itemId);
    if (!item || !orderModalOverlay) return;

    currentCustomizingItem = item;
    customTemp = 'Iced';
    customSize = 'Small';
    customQty = 1;

    if (orderModalImg) {
      orderModalImg.src = `${item.image}?v=6.0`;
      orderModalImg.alt = item.name;
    }
    if (orderModalCategory) {
      const catLabels = {
        'house-coffee': 'House Coffee',
        'matcha': 'Matcha',
        'house-specials': 'House Specials',
        'yogurt-soda': 'Yogurt / Soda'
      };
      orderModalCategory.textContent = catLabels[item.category] || 'Specialty';
    }
    if (orderModalTitle) orderModalTitle.textContent = item.name;
    if (orderModalDesc) {
      orderModalDesc.textContent = item.description;
      orderModalDesc.title = item.description;
    }

    const smallPrice = item.priceIcedM || item.price || 120;
    const mediumPrice = item.priceIcedL || (item.price + 20) || 140;

    if (sizeSmallPrice) sizeSmallPrice.textContent = formatPHP(smallPrice);
    if (sizeMediumPrice) sizeMediumPrice.textContent = formatPHP(mediumPrice);

    // Temperature Availability: Yogurt/Soda are iced-only
    const allowHot = !!item.priceHot;
    if (tempHotBtn) {
      if (allowHot) {
        tempHotBtn.classList.remove('disabled');
        tempHotBtn.removeAttribute('aria-disabled');
        if (tempOptionBadge) tempOptionBadge.textContent = 'Select 1';
      } else {
        tempHotBtn.classList.add('disabled');
        tempHotBtn.setAttribute('aria-disabled', 'true');
        if (tempOptionBadge) tempOptionBadge.textContent = 'Iced Only';
      }
    }

    // Default Selection: Iced, Small, Qty 1
    if (tempIcedBtn) {
      tempIcedBtn.classList.add('active');
      tempIcedBtn.setAttribute('aria-checked', 'true');
    }
    if (tempHotBtn) {
      tempHotBtn.classList.remove('active');
      tempHotBtn.setAttribute('aria-checked', 'false');
    }

    if (sizeSmallBtn) {
      sizeSmallBtn.classList.add('active');
      sizeSmallBtn.setAttribute('aria-checked', 'true');
    }
    if (sizeMediumBtn) {
      sizeMediumBtn.classList.remove('active');
      sizeMediumBtn.setAttribute('aria-checked', 'false');
    }

    if (modalQtyVal) modalQtyVal.textContent = '1';

    updateOrderModalTotal();
    orderModalOverlay.classList.add('active');
  }

  function closeOrderModal() {
    if (orderModalOverlay) {
      orderModalOverlay.classList.remove('active');
    }
  }

  function updateOrderModalTotal() {
    if (!currentCustomizingItem) return;
    const smallPrice = currentCustomizingItem.priceIcedM || currentCustomizingItem.price || 120;
    const mediumPrice = currentCustomizingItem.priceIcedL || (currentCustomizingItem.price + 20) || 140;
    const unitPrice = (customSize === 'Medium') ? mediumPrice : smallPrice;
    const total = unitPrice * customQty;
    if (orderConfirmTotal) {
      orderConfirmTotal.textContent = `· ${formatPHP(total)}`;
    }
  }

  // Bind Order Modal Events
  if (closeOrderModalBtn && orderModalOverlay) {
    closeOrderModalBtn.addEventListener('click', closeOrderModal);
    orderModalOverlay.addEventListener('click', (e) => {
      if (e.target === orderModalOverlay) closeOrderModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && orderModalOverlay.classList.contains('active')) {
        closeOrderModal();
      }
    });
  }

  if (tempIcedBtn && tempHotBtn) {
    tempIcedBtn.addEventListener('click', () => {
      customTemp = 'Iced';
      tempIcedBtn.classList.add('active');
      tempIcedBtn.setAttribute('aria-checked', 'true');
      tempHotBtn.classList.remove('active');
      tempHotBtn.setAttribute('aria-checked', 'false');
      updateOrderModalTotal();
    });

    tempHotBtn.addEventListener('click', () => {
      if (tempHotBtn.classList.contains('disabled')) return;
      customTemp = 'Hot';
      tempHotBtn.classList.add('active');
      tempHotBtn.setAttribute('aria-checked', 'true');
      tempIcedBtn.classList.remove('active');
      tempIcedBtn.setAttribute('aria-checked', 'false');
      updateOrderModalTotal();
    });
  }

  if (sizeSmallBtn && sizeMediumBtn) {
    sizeSmallBtn.addEventListener('click', () => {
      customSize = 'Small';
      sizeSmallBtn.classList.add('active');
      sizeSmallBtn.setAttribute('aria-checked', 'true');
      sizeMediumBtn.classList.remove('active');
      sizeMediumBtn.setAttribute('aria-checked', 'false');
      updateOrderModalTotal();
    });

    sizeMediumBtn.addEventListener('click', () => {
      customSize = 'Medium';
      sizeMediumBtn.classList.add('active');
      sizeMediumBtn.setAttribute('aria-checked', 'true');
      sizeSmallBtn.classList.remove('active');
      sizeSmallBtn.setAttribute('aria-checked', 'false');
      updateOrderModalTotal();
    });
  }

  if (modalQtyMinus && modalQtyPlus && modalQtyVal) {
    modalQtyMinus.addEventListener('click', () => {
      if (customQty > 1) {
        customQty -= 1;
        modalQtyVal.textContent = customQty;
        updateOrderModalTotal();
      }
    });
    modalQtyPlus.addEventListener('click', () => {
      customQty += 1;
      modalQtyVal.textContent = customQty;
      updateOrderModalTotal();
    });
  }

  if (orderConfirmBtn) {
    orderConfirmBtn.addEventListener('click', () => {
      if (!currentCustomizingItem) return;
      addToCartCustomized(currentCustomizingItem, customTemp, customSize, customQty);
      closeOrderModal();
    });
  }

  function saveCart() {
    localStorage.setItem('becoffee_cart', JSON.stringify(state.cart));
    updateCartUI();
  }

  function addToCartCustomized(item, temperature, size, qty = 1) {
    const smallPrice = item.priceIcedM || item.price || 120;
    const mediumPrice = item.priceIcedL || (item.price + 20) || 140;
    const unitPrice = (size === 'Medium') ? mediumPrice : smallPrice;
    const cartItemId = `${item.id}-${temperature.toLowerCase()}-${size.toLowerCase()}`;

    const existing = state.cart.find(i => (i.cartItemId || i.id) === cartItemId);
    if (existing) {
      existing.qty += qty;
    } else {
      state.cart.push({
        cartItemId: cartItemId,
        id: item.id,
        name: item.name,
        temperature: temperature,
        size: size,
        price: unitPrice,
        qty: qty
      });
    }

    saveCart();

    // Automatically slide open the cart drawer so user sees customized item added
    if (cartDrawerOverlay) {
      cartDrawerOverlay.classList.add('active');
    }

    showToast(`Added "${item.name} (${temperature} · ${size})"${qty > 1 ? ` ×${qty}` : ''} to order (${formatPHP(unitPrice * qty)})`);
  }

  function updateQty(cartId, change) {
    const item = state.cart.find(i => (i.cartItemId || i.id) === cartId);
    if (!item) return;

    item.qty += change;
    if (item.qty <= 0) {
      state.cart = state.cart.filter(i => (i.cartItemId || i.id) !== cartId);
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
      if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Confirm Order Pickup (₱)';
      }
      const cartAuthHint = document.getElementById('cartAuthHint');
      if (cartAuthHint) cartAuthHint.style.display = 'none';
      return;
    }

    const subtotal = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const ecoFee = 25; // ₱25 sustainable bamboo packaging fee
    const grandTotal = subtotal + ecoFee;

    cartItemsContainer.innerHTML = state.cart.map(item => `
      <div class="cart-item-row" data-cart-id="${item.cartItemId || item.id}">
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <span class="cart-item-variant">${item.temperature || 'Iced'} · ${item.size || 'Small'}</span>
          <div class="cart-item-price">${formatPHP(item.price)} each</div>
        </div>
        <div class="cart-qty-ctrl">
          <button type="button" class="qty-btn qty-minus" data-cart-id="${item.cartItemId || item.id}" aria-label="Decrease quantity">−</button>
          <span class="qty-val">${item.qty}</span>
          <button type="button" class="qty-btn qty-plus" data-cart-id="${item.cartItemId || item.id}" aria-label="Increase quantity">+</button>
        </div>
      </div>
    `).join('');

    // Attach events to qty buttons
    cartItemsContainer.querySelectorAll('.qty-minus').forEach(b => {
      b.addEventListener('click', () => updateQty(b.dataset.cartId, -1));
    });
    cartItemsContainer.querySelectorAll('.qty-plus').forEach(b => {
      b.addEventListener('click', () => updateQty(b.dataset.cartId, 1));
    });

    if (cartSubtotalEl) cartSubtotalEl.textContent = formatPHP(subtotal);
    if (cartEcoFeeEl) cartEcoFeeEl.textContent = formatPHP(ecoFee);
    if (cartGrandTotalEl) cartGrandTotalEl.textContent = formatPHP(grandTotal);

    const cartAuthHint = document.getElementById('cartAuthHint');
    if (cartAuthHint) {
      cartAuthHint.style.display = !state.currentUser ? 'block' : 'none';
    }

    if (checkoutBtn) {
      checkoutBtn.disabled = false;
      if (!state.currentUser) {
        checkoutBtn.textContent = `Sign In to Order (${formatPHP(grandTotal)})`;
      } else {
        checkoutBtn.textContent = `Confirm Order Pickup (${formatPHP(grandTotal)})`;
      }
    }
  }

  // Cart update alias
  const updateCartDrawer = updateCartUI;

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

  // Checkout order action (Database-backed order creation)
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', async () => {
      if (state.cart.length === 0) return;

      // Cannot order without an account: automatically lead to login or create account
      if (!state.currentUser) {
        state.pendingCheckout = true;
        if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
        openAuthModal('signin');
        setAuthAlert('Please sign in or create an account to complete your order.', 'info');
        return;
      }
      
      const originalText = checkoutBtn.textContent;
      checkoutBtn.disabled = true;
      checkoutBtn.textContent = 'Processing Order...';

      try {
        const payload = {
          items: state.cart.map(i => ({
            id: i.id,
            quantity: i.qty,
            temperature: i.temperature || 'Iced',
            size: i.size || 'Small'
          })),
          customer_name: state.currentUser ? state.currentUser.name : '',
          customer_phone: state.currentUser && state.currentUser.phone ? state.currentUser.phone : ''
        };

        const res = await fetch('api/orders.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
          showToast(`Order ${data.order_reference} confirmed! Ready for pickup (${formatPHP(data.grand_total)})`);
          state.cart = [];
          saveCart();
          if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
        } else if (res.status === 401) {
          state.currentUser = null;
          updateAuthUI();
          state.pendingCheckout = true;
          if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
          openAuthModal('signin');
          setAuthAlert(data.error || 'Please sign in or create an account to complete your order.', 'info');
        } else {
          showToast(data.error || 'Unable to complete order. Please try again.');
        }
      } catch (err) {
        showToast('Server connection issue. Please try again in a moment.');
      } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = originalText;
      }
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
      if (state.currentUser) {
        const nameInput = document.getElementById('bookingName');
        const phoneInput = document.getElementById('bookingPhone');
        if (nameInput && !nameInput.value) nameInput.value = state.currentUser.name;
        if (phoneInput && !phoneInput.value && state.currentUser.phone) phoneInput.value = state.currentUser.phone;
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

  // Form Submit (Database-backed reservations)
  if (reservationForm) {
    reservationForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = reservationForm.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving Booking...';
      }

      const payload = {
        booking_type: document.getElementById('bookingType').value,
        location_code: document.getElementById('bookingLocation').value,
        guests: parseInt(document.getElementById('bookingGuests').value, 10),
        preferred_date: document.getElementById('bookingDate').value,
        preferred_time: document.getElementById('bookingTime').value,
        customer_name: document.getElementById('bookingName').value,
        customer_phone: document.getElementById('bookingPhone').value,
        notes: document.getElementById('bookingNotes').value
      };

      try {
        const res = await fetch('api/reservations.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message);
          reservationForm.reset();
          updateReservationEstimate();
          if (reserveModalOverlay) reserveModalOverlay.classList.remove('active');
        } else {
          showToast(data.error || 'Failed to save reservation.');
        }
      } catch (err) {
        showToast(`Reservation request noted for ${payload.customer_name} on ${payload.preferred_date}!`);
        reservationForm.reset();
        updateReservationEstimate();
        if (reserveModalOverlay) reserveModalOverlay.classList.remove('active');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }
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
      badge: 'Cordillera House Roastery & Cupping Lab',
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

  // --- 14. User Authentication & Account Management ---
  function updateAuthUI() {
    if (state.currentUser) {
      if (authOpenBtn) authOpenBtn.style.display = 'none';
      if (userProfilePill) userProfilePill.style.display = 'inline-flex';
      
      const firstName = state.currentUser.name.split(' ')[0];
      if (userNameDisplay) userNameDisplay.textContent = firstName;
      if (userAvatarBadge) {
        const parts = state.currentUser.name.trim().split(/\s+/);
        userAvatarBadge.textContent = parts.length > 1
          ? (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
          : parts[0].charAt(0).toUpperCase();
      }
      
      if (mobileAuthTrigger) {
        mobileAuthTrigger.textContent = `Sign Out (${firstName})`;
        mobileAuthTrigger.classList.remove('btn-secondary');
        mobileAuthTrigger.classList.add('btn-outline');
      }

      // Pre-fill reservation form if inputs are empty
      const bookingName = document.getElementById('bookingName');
      const bookingPhone = document.getElementById('bookingPhone');
      if (bookingName && !bookingName.value) bookingName.value = state.currentUser.name;
      if (bookingPhone && !bookingPhone.value && state.currentUser.phone) bookingPhone.value = state.currentUser.phone;
    } else {
      if (authOpenBtn) authOpenBtn.style.display = 'inline-flex';
      if (userProfilePill) userProfilePill.style.display = 'none';
      if (mobileAuthTrigger) {
        mobileAuthTrigger.textContent = 'Member Sign In';
        mobileAuthTrigger.classList.remove('btn-outline');
        mobileAuthTrigger.classList.add('btn-secondary');
      }
    }

    // Keep cart checkout button & hints updated with auth status
    if (typeof updateCartUI === 'function') {
      updateCartUI();
    }
  }

  async function checkAuthStatus() {
    try {
      const res = await fetch('api/auth.php?action=me');
      if (res.ok) {
        const data = await res.json();
        if (data.authenticated && data.user) {
          state.currentUser = data.user;
          updateAuthUI();
        } else {
          state.currentUser = null;
          updateAuthUI();
        }
      }
    } catch (err) {
      console.warn('Authentication status check offline/deferred.');
    }
  }

  function setAuthAlert(message, type = 'error') {
    if (!authAlertBox) return;
    if (!message) {
      authAlertBox.style.display = 'none';
      authAlertBox.textContent = '';
      return;
    }
    authAlertBox.className = `auth-alert ${type}`;
    authAlertBox.textContent = message;
    authAlertBox.style.display = 'block';
  }

  function switchAuthTab(tab) {
    setAuthAlert('');
    if (tab === 'signin') {
      if (tabSignInBtn) {
        tabSignInBtn.classList.add('active');
        tabSignInBtn.setAttribute('aria-selected', 'true');
      }
      if (tabRegisterBtn) {
        tabRegisterBtn.classList.remove('active');
        tabRegisterBtn.setAttribute('aria-selected', 'false');
      }
      if (signInForm) signInForm.style.display = 'block';
      if (registerForm) registerForm.style.display = 'none';
      if (authModalTitle) authModalTitle.textContent = 'Welcome Back to BeCoffee';
      if (authModalSubtitle) authModalSubtitle.textContent = 'Sign in to access saved roastery orders and cupping reservations.';
    } else {
      if (tabRegisterBtn) {
        tabRegisterBtn.classList.add('active');
        tabRegisterBtn.setAttribute('aria-selected', 'true');
      }
      if (tabSignInBtn) {
        tabSignInBtn.classList.remove('active');
        tabSignInBtn.setAttribute('aria-selected', 'false');
      }
      if (signInForm) signInForm.style.display = 'none';
      if (registerForm) registerForm.style.display = 'block';
      if (authModalTitle) authModalTitle.textContent = 'Join the BeCoffee Guild';
      if (authModalSubtitle) authModalSubtitle.textContent = 'Create your account to unlock personalized beans, orders, and table passes.';
    }
  }

  function openAuthModal(defaultTab = 'signin') {
    switchAuthTab(defaultTab);
    if (authModalOverlay) authModalOverlay.classList.add('active');
  }

  function closeAuthModal() {
    if (authModalOverlay) authModalOverlay.classList.remove('active');
    setAuthAlert('');
    if (signInForm) signInForm.reset();
    if (registerForm) registerForm.reset();
  }

  // Open / Close Auth Modal Events
  if (authOpenBtn) {
    authOpenBtn.addEventListener('click', () => openAuthModal('signin'));
  }

  if (mobileAuthTrigger) {
    mobileAuthTrigger.addEventListener('click', () => {
      if (state.currentUser) {
        handleLogout();
      } else {
        openAuthModal('signin');
      }
    });
  }

  if (closeAuthModalBtn) {
    closeAuthModalBtn.addEventListener('click', closeAuthModal);
  }

  if (authModalOverlay) {
    authModalOverlay.addEventListener('click', (e) => {
      if (e.target === authModalOverlay) closeAuthModal();
    });
  }

  // Tab Switching
  if (tabSignInBtn) {
    tabSignInBtn.addEventListener('click', () => switchAuthTab('signin'));
  }
  if (tabRegisterBtn) {
    tabRegisterBtn.addEventListener('click', () => switchAuthTab('register'));
  }

  // Handle Login Submit
  if (signInForm) {
    signInForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      setAuthAlert('');

      const email = document.getElementById('loginEmail').value.trim();
      const password = document.getElementById('loginPassword').value;
      const submitBtn = document.getElementById('loginSubmitBtn');

      const originalText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';
      }

      try {
        const res = await fetch('api/auth.php?action=login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });

        const data = await res.json();
        if (res.ok && data.success) {
          state.currentUser = data.user;
          setAuthAlert('Success! Welcome back.', 'success');
          showToast(`Welcome back, ${data.user.name}!`);
          try {
            updateAuthUI();
          } catch (uiErr) {
            console.warn('UI update notice after login:', uiErr);
          }
          setTimeout(() => {
            closeAuthModal();
            if (state.pendingCheckout && state.cart.length > 0) {
              state.pendingCheckout = false;
              if (cartDrawerOverlay) cartDrawerOverlay.classList.add('active');
              showToast('Account ready! Click below to confirm order pickup.');
            }
          }, 600);
        } else {
          setAuthAlert(data.error || 'Invalid email or password.');
        }
      } catch (err) {
        console.warn('Authentication request error:', err);
        setAuthAlert('Unable to reach authentication server. Please check your connection.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }
    });
  }

  // Handle Registration Submit
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      setAuthAlert('');

      const name = document.getElementById('regName').value.trim();
      const email = document.getElementById('regEmail').value.trim();
      const phone = document.getElementById('regPhone').value.trim();
      const password = document.getElementById('regPassword').value;
      const confirm = document.getElementById('regPasswordConfirm').value;
      const submitBtn = document.getElementById('registerSubmitBtn');

      if (password !== confirm) {
        setAuthAlert('Passwords do not match. Please verify.');
        return;
      }

      if (password.length < 8) {
        setAuthAlert('Password must be at least 8 characters long.');
        return;
      }

      const originalText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
      }

      try {
        const res = await fetch('api/auth.php?action=register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, phone, password })
        });

        const data = await res.json();
        if (res.ok && data.success) {
          state.currentUser = data.user;
          setAuthAlert('Account created successfully! Welcome to BeCoffee.', 'success');
          showToast(`Welcome to BeCoffee, ${data.user.name}!`);
          try {
            updateAuthUI();
          } catch (uiErr) {
            console.warn('UI update notice after registration:', uiErr);
          }
          setTimeout(() => {
            closeAuthModal();
            if (state.pendingCheckout && state.cart.length > 0) {
              state.pendingCheckout = false;
              if (cartDrawerOverlay) cartDrawerOverlay.classList.add('active');
              showToast('Account created! Click below to confirm order pickup.');
            }
          }, 600);
        } else {
          setAuthAlert(data.error || 'Registration failed. Please check your information.');
        }
      } catch (err) {
        console.warn('Registration request error:', err);
        setAuthAlert('Unable to reach registration server. Please check your connection.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }
    });
  }

  // Handle Logout
  async function handleLogout() {
    try {
      const res = await fetch('api/auth.php?action=logout', { method: 'POST' });
      const data = await res.json();
      state.currentUser = null;
      updateAuthUI();
      showToast(data.message || 'Signed out successfully.');
    } catch (err) {
      state.currentUser = null;
      updateAuthUI();
      showToast('Signed out.');
    }
  }

  if (logoutBtn) {
    logoutBtn.addEventListener('click', handleLogout);
  }

  // --- 15. Dynamic Menu Synchronization ---
  async function loadMenuFromAPI() {
    try {
      const res = await fetch('api/menu.php');
      if (res.ok) {
        const data = await res.json();
        if (data.success && Array.isArray(data.items) && data.items.length > 0) {
          state.menuItems = data.items;
          renderMenu();
        }
      }
    } catch (e) {
      console.warn('Using embedded menu state; API fetch deferred.');
    }
  }

  // --- 16. Initial Startup Sequence ---
  renderMenu();
  updateCartUI();
  checkAuthStatus();
  loadMenuFromAPI();
});
