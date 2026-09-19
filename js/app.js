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

  // User Account Settings Elements
  const userProfileTrigger = document.getElementById('userProfileTrigger');
  const mobileAccountSettingsItem = document.getElementById('mobileAccountSettingsItem');
  const mobileAccountSettingsTrigger = document.getElementById('mobileAccountSettingsTrigger');
  const accountSettingsModalOverlay = document.getElementById('accountSettingsModalOverlay');
  const closeAccountModalBtn = document.getElementById('closeAccountModalBtn');
  const accountAvatarLarge = document.getElementById('accountAvatarLarge');
  const accountRolePill = document.getElementById('accountRolePill');
  const accountModalTitle = document.getElementById('accountModalTitle');
  const accountModalEmail = document.getElementById('accountModalEmail');
  const accountAlertBox = document.getElementById('accountAlertBox');
  const tabProfileBtn = document.getElementById('tabProfileBtn');
  const tabPasswordBtn = document.getElementById('tabPasswordBtn');
  const updateProfileForm = document.getElementById('updateProfileForm');
  const changePasswordForm = document.getElementById('changePasswordForm');
  const accountNameInput = document.getElementById('accountNameInput');
  const accountEmailInput = document.getElementById('accountEmailInput');
  const accountPhoneInput = document.getElementById('accountPhoneInput');
  const saveProfileBtn = document.getElementById('saveProfileBtn');
  const currentPasswordInput = document.getElementById('currentPasswordInput');
  const newPasswordInput = document.getElementById('newPasswordInput');
  const confirmPasswordInput = document.getElementById('confirmPasswordInput');
  const savePasswordBtn = document.getElementById('savePasswordBtn');

  // Admin CMS Elements
  const adminCmsOpenBtn = document.getElementById('adminCmsOpenBtn');
  const mobileAdminCmsItem = document.getElementById('mobileAdminCmsItem');
  const mobileAdminCmsTrigger = document.getElementById('mobileAdminCmsTrigger');
  const adminCmsModalOverlay = document.getElementById('adminCmsModalOverlay');
  const closeAdminCmsBtn = document.getElementById('closeAdminCmsBtn');
  const cmsAddNewBtn = document.getElementById('cmsAddNewBtn');
  const cmsItemsContainer = document.getElementById('cmsItemsContainer');
  const cmsSearchInput = document.getElementById('cmsSearchInput');
  const cmsCategoryFilters = document.getElementById('cmsCategoryFilters');
  const cmsTotalCount = document.getElementById('cmsTotalCount');
  const cmsActiveCount = document.getElementById('cmsActiveCount');
  const cmsUnavailableCount = document.getElementById('cmsUnavailableCount');
  const productEditModalOverlay = document.getElementById('productEditModalOverlay');
  const closeProdEditModalBtn = document.getElementById('closeProdEditModalBtn');
  const cancelProdEditBtn = document.getElementById('cancelProdEditBtn');
  const productEditForm = document.getElementById('productEditForm');
  const prodModalTitle = document.getElementById('prodModalTitle');
  const prodModalSubtitle = document.getElementById('prodModalSubtitle');

  // Location Outpost Tabs
  const locTabBtns = document.querySelectorAll('.loc-tab-btn');
  const locCardTitle = document.getElementById('locCardTitle');
  const locCardBadge = document.getElementById('locCardBadge');
  const locAddressText = document.getElementById('locAddressText');
  const locHoursText = document.getElementById('locHoursText');
  const locPhoneText = document.getElementById('locPhoneText');
  const locFeaturesText = document.getElementById('locFeaturesText');
  const locImage = document.getElementById('locImage');
  const locDirectionsBtn = document.getElementById('locDirectionsBtn');

  // Helper to dynamically resolve API endpoints across localhost, 127.0.0.1, Live Server (5500), and file://
  function getApiUrl(endpoint) {
    if (window.location.protocol === 'file:') {
      return `http://localhost/BeCoffee/${endpoint}`;
    }
    if (window.location.port && window.location.port !== '80' && window.location.port !== '443') {
      return `http://localhost/BeCoffee/${endpoint}`;
    }
    return endpoint;
  }

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

    // Auto dismiss after exactly 5 seconds
    dismissTimer = setTimeout(dismissBanner, 5000);

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

    menuGrid.innerHTML = items.map(item => {
      const isAvailable = item.isAvailable !== false;
      return `
      <article class="menu-card ${isAvailable ? '' : 'item-unavailable'}" data-id="${item.id}">
        <div class="menu-card-img-wrap">
          <img src="${item.image}?v=7.0" alt="${item.name}" loading="lazy" width="700" height="438">
          ${isAvailable ? `<span class="card-pill-tag">${item.tags[0] || 'Specialty'}</span>` : '<span class="card-pill-tag tag-soldout">Sold Out</span>'}
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
            ${isAvailable ? `
            <button type="button" class="add-btn add-to-cart-trigger" data-id="${item.id}" aria-label="Add ${item.name} to order">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Add to Order
            </button>` : `
            <button type="button" class="add-btn" disabled aria-disabled="true">
              Sold Out
            </button>`}
          </div>
        </div>
      </article>
      `;
    }).join('');

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
      orderModalImg.src = `${item.image}?v=7.0`;
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

        const res = await fetch(getApiUrl('api/orders.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
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
        const res = await fetch(getApiUrl('api/reservations.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
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

  // --- 12. Philippine Outpost Tabs (Zamboanga vs Benguet) ---
  const locationsData = {
    bgc: {
      name: 'Zamboanga Flagship Sanctuary',
      badge: 'Zamboanga City Flagship & Slow Bar',
      address: 'RCDAO Village, Putik, Zamboanga City, 7000 Zamboanga Peninsula',
      hours: 'Mon – Sun: 7:00 AM – 10:00 PM PHT',
      phone: '+63 62 991 2633 / +63 917 555 2333',
      features: 'High-speed 500Mbps Fiber, Outdoor Garden Seating, Slow Bar & Siphon, Pour-over Flights',
      image: 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80',
      directionsUrl: 'https://maps.app.goo.gl/RHdya1FR7aJFRbDx5'
    },
    benguet: {
      name: 'Baliwasan Roastery & Slow Bar',
      badge: 'Zamboanga Peninsula Roastery & Brew Lab',
      address: 'San Jose Street, Baliwasan Grande, San Jose Gusu, Zamboanga City, 7000',
      hours: 'Wed – Sun: 8:00 AM – 8:00 PM PHT (Closed Mon & Tue)',
      phone: '+63 62 991 8888 / +63 919 888 2333',
      features: 'Direct Cupping Pavilion, Alfresco Seating, Artisan Roast Flights, High-Speed Fiber',
      image: 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=800&q=80',
      directionsUrl: 'https://maps.app.goo.gl/yeavai9XSWvr7YhN8'
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
      if (locDirectionsBtn && data.directionsUrl) {
        locDirectionsBtn.href = data.directionsUrl;
      }
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

      // Admin CMS button visibility
      const isAdmin = state.currentUser && state.currentUser.role === 'admin';
      if (adminCmsOpenBtn) adminCmsOpenBtn.style.display = isAdmin ? 'inline-flex' : 'none';
      if (mobileAdminCmsItem) {
        mobileAdminCmsItem.classList.toggle('is-admin', Boolean(isAdmin));
        mobileAdminCmsItem.style.display = '';
      }

      if (mobileAccountSettingsItem) {
        mobileAccountSettingsItem.classList.add('is-auth');
      }

      // Pre-fill reservation form if inputs are empty
      const bookingName = document.getElementById('bookingName');
      const bookingPhone = document.getElementById('bookingPhone');
      if (bookingName && !bookingName.value) bookingName.value = state.currentUser.name;
      if (bookingPhone && !bookingPhone.value && state.currentUser.phone) bookingPhone.value = state.currentUser.phone;
    } else {
      if (authOpenBtn) authOpenBtn.style.display = 'inline-flex';
      if (userProfilePill) userProfilePill.style.display = 'none';
      if (adminCmsOpenBtn) adminCmsOpenBtn.style.display = 'none';
      if (mobileAdminCmsItem) {
        mobileAdminCmsItem.classList.remove('is-admin');
        mobileAdminCmsItem.style.display = '';
      }
      if (mobileAccountSettingsItem) {
        mobileAccountSettingsItem.classList.remove('is-auth');
      }
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
    if (window.location.protocol === 'file:') {
      console.warn('Running via file://. Backend API calls are disabled. Open http://localhost/BeCoffee/ in your browser.');
      return;
    }
    try {
      const res = await fetch(getApiUrl('api/auth.php?action=me'), { credentials: 'include' });
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
      if (authModalSubtitle) authModalSubtitle.textContent = 'Create your account to track bean orders, curate cuppings, and earn member perks.';
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

      if (window.location.protocol === 'file:') {
        setAuthAlert('Cannot sign in via file://. Please open http://localhost/BeCoffee/ in your browser address bar.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
        return;
      }

      try {
        const res = await fetch(getApiUrl('api/auth.php?action=login'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
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
        if (window.location.protocol === 'file:') {
          setAuthAlert('Cannot connect via file://. Please open http://localhost/BeCoffee/ in your browser.');
        } else {
          setAuthAlert('Unable to reach authentication server. Please check that Apache is running at http://localhost/BeCoffee/.');
        }
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

      if (window.location.protocol === 'file:') {
        setAuthAlert('Cannot register via file://. Please open http://localhost/BeCoffee/ in your browser address bar.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
        return;
      }

      try {
        const res = await fetch(getApiUrl('api/auth.php?action=register'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
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
        if (window.location.protocol === 'file:') {
          setAuthAlert('Cannot connect via file://. Please open http://localhost/BeCoffee/ in your browser.');
        } else {
          setAuthAlert('Unable to reach registration server. Please check that Apache is running.');
        }
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
      const res = await fetch(getApiUrl('api/auth.php?action=logout'), { method: 'POST', credentials: 'include' });
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
      const res = await fetch(getApiUrl('api/menu.php'));
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

  // --- 17. Admin Menu CMS (Prices, Availability, Products) ---
  const cmsState = {
    items: [],
    categories: [],
    activeCategory: 'all',
    searchQuery: '',
    stats: { total: 0, active: 0, unavailable: 0 }
  };

  function openAdminCms() {
    if (!state.currentUser || state.currentUser.role !== 'admin') {
      showToast('Administrator privileges required.');
      return;
    }
    if (adminCmsModalOverlay) {
      adminCmsModalOverlay.classList.add('active');
      loadCmsData();
    }
  }

  function closeAdminCms() {
    if (adminCmsModalOverlay) {
      adminCmsModalOverlay.classList.remove('active');
    }
  }

  if (adminCmsOpenBtn) {
    adminCmsOpenBtn.addEventListener('click', openAdminCms);
  }

  if (mobileAdminCmsTrigger) {
    mobileAdminCmsTrigger.addEventListener('click', () => {
      if (mobileToggle) mobileToggle.classList.remove('active');
      if (navLinks) navLinks.classList.remove('active');
      openAdminCms();
    });
  }

  if (closeAdminCmsBtn) {
    closeAdminCmsBtn.addEventListener('click', closeAdminCms);
  }

  // Load CMS Data from backend
  async function loadCmsData() {
    if (!cmsItemsContainer) return;
    cmsItemsContainer.innerHTML = '<div class="cms-loading-state">Loading menu catalog from MySQL...</div>';

    try {
      const res = await fetch(getApiUrl('api/admin_menu.php'), { credentials: 'include' });
      const data = await res.json();
      if (res.ok && data.success) {
        cmsState.items = data.items || [];
        cmsState.categories = data.categories || [];
        cmsState.stats = data.stats || { total: 0, active: 0, unavailable: 0 };

        updateCmsKpis();
        renderCmsItems();
      } else {
        cmsItemsContainer.innerHTML = `<div class="cms-loading-state" style="color:#dc2626;">${data.error || 'Failed to load catalog.'}</div>`;
      }
    } catch (err) {
      cmsItemsContainer.innerHTML = `<div class="cms-loading-state" style="color:#dc2626;">Network error loading CMS catalog.</div>`;
    }
  }

  function updateCmsKpis() {
    if (cmsTotalCount) cmsTotalCount.textContent = cmsState.stats.total;
    if (cmsActiveCount) cmsActiveCount.textContent = cmsState.stats.active;
    if (cmsUnavailableCount) cmsUnavailableCount.textContent = cmsState.stats.unavailable;
  }

  function renderCmsItems() {
    if (!cmsItemsContainer) return;

    let items = cmsState.items;

    // Filter by Category
    if (cmsState.activeCategory !== 'all') {
      items = items.filter(item => item.category === cmsState.activeCategory);
    }

    // Filter by Search Query
    if (cmsState.searchQuery) {
      const q = cmsState.searchQuery.toLowerCase();
      items = items.filter(item => 
        item.name.toLowerCase().includes(q) || 
        item.id.toLowerCase().includes(q) ||
        (item.origin && item.origin.toLowerCase().includes(q))
      );
    }

    if (items.length === 0) {
      cmsItemsContainer.innerHTML = '<div class="cms-loading-state">No products matching your search filter.</div>';
      return;
    }

    cmsItemsContainer.innerHTML = items.map(item => `
      <div class="cms-item-row ${item.isAvailable ? '' : 'is-unavailable'}" data-id="${item.id}">
        <div class="cms-thumb-wrap">
          <img src="${item.image}?v=7.0" alt="${item.name}" loading="lazy">
        </div>

        <div class="cms-item-info">
          <div class="cms-item-name">
            <span>${item.name}</span>
            ${item.isBestseller ? '<span class="cms-bestseller-badge">★ Bestseller</span>' : ''}
          </div>
          <div class="cms-item-meta">
            <span class="cms-cat-badge">${item.categoryName || item.category}</span>
            <code class="cms-code-badge">${item.id}</code>
            ${(item.origin || item.elevation) ? `<span class="cms-origin-text">${item.origin || item.elevation}</span>` : ''}
          </div>
        </div>

        <div class="cms-price-col">
          <span class="cms-base-price">${formatPHP(item.price)}</span>
          <div class="cms-sub-prices">
            <span class="cms-sub-price-tag">M: ${formatPHP(item.priceIcedM || item.price)}</span>
            ${item.priceIcedL ? `<span class="cms-sub-price-tag">L: ${formatPHP(item.priceIcedL)}</span>` : ''}
            ${item.priceHot ? `<span class="cms-sub-price-tag hot">Hot: ${formatPHP(item.priceHot)}</span>` : ''}
          </div>
        </div>

        <div class="cms-status-col">
          <div class="cms-switch-wrap">
            <label class="cms-switch" title="Toggle Available / Sold Out">
              <input type="checkbox" class="cms-availability-toggle" data-id="${item.id}" ${item.isAvailable ? 'checked' : ''} aria-label="Toggle availability for ${item.name}">
              <span class="cms-slider"></span>
            </label>
          </div>
          <span class="cms-status-pill ${item.isAvailable ? 'avail' : 'unavail'}">
            <span class="cms-status-dot"></span>
            ${item.isAvailable ? 'In Stock' : 'Sold Out'}
          </span>
        </div>

        <div class="cms-actions-col">
          <button type="button" class="cms-action-icon-btn cms-edit-trigger" data-id="${item.id}" title="Edit Product" aria-label="Edit Product ${item.name}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M12 20h9"></path>
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
          </button>
          <button type="button" class="cms-action-icon-btn delete cms-delete-trigger" data-id="${item.id}" title="Delete Product" aria-label="Delete Product ${item.name}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>
        </div>
      </div>
    `).join('');
  }

  // Event Delegation for CMS Items (Toggle, Edit, Delete)
  if (cmsItemsContainer) {
    // 1. Toggle Availability Switch
    cmsItemsContainer.addEventListener('change', async (e) => {
      const toggle = e.target.closest('.cms-availability-toggle');
      if (toggle) {
        const id = toggle.dataset.id;
        const isAvailable = toggle.checked;

        // Optimistic UI update
        const item = cmsState.items.find(i => i.id === id);
        if (item) {
          item.isAvailable = isAvailable;
          if (isAvailable) {
            cmsState.stats.active++;
            cmsState.stats.unavailable = Math.max(0, cmsState.stats.unavailable - 1);
          } else {
            cmsState.stats.active = Math.max(0, cmsState.stats.active - 1);
            cmsState.stats.unavailable++;
          }
          updateCmsKpis();
          renderCmsItems();
        }

        try {
          const res = await fetch(getApiUrl('api/admin_menu.php?action=toggle_availability'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id, is_available: isAvailable })
          });
          const data = await res.json();
          if (res.ok && data.success) {
            showToast(data.message || 'Availability updated.');
            loadMenuFromAPI(); // sync public menu
          } else {
            showToast(data.error || 'Failed to update availability.');
            loadCmsData(); // rollback
          }
        } catch (err) {
          showToast('Network error updating availability.');
          loadCmsData(); // rollback
        }
      }
    });

    // 2. Edit Button
    cmsItemsContainer.addEventListener('click', (e) => {
      const editBtn = e.target.closest('.cms-edit-trigger');
      if (editBtn) {
        const id = editBtn.dataset.id;
        const item = cmsState.items.find(i => i.id === id);
        if (item) openProductModal('edit', item);
      }

      // 3. Delete Button
      const deleteBtn = e.target.closest('.cms-delete-trigger');
      if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        const item = cmsState.items.find(i => i.id === id);
        const name = item ? item.name : id;
        if (confirm(`Are you sure you want to remove "${name}" from the catalog?`)) {
          deleteProduct(id);
        }
      }
    });
  }

  // Search Filter
  if (cmsSearchInput) {
    cmsSearchInput.addEventListener('input', (e) => {
      cmsState.searchQuery = e.target.value.trim();
      renderCmsItems();
    });
  }

  // Category Filter Tabs
  if (cmsCategoryFilters) {
    cmsCategoryFilters.addEventListener('click', (e) => {
      const tab = e.target.closest('.cms-tab');
      if (tab) {
        cmsCategoryFilters.querySelectorAll('.cms-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        cmsState.activeCategory = tab.dataset.cat;
        renderCmsItems();
      }
    });
  }

  // Add Product Button
  if (cmsAddNewBtn) {
    cmsAddNewBtn.addEventListener('click', () => {
      openProductModal('create');
    });
  }

  // Open Product Modal (Create or Edit)
  function openProductModal(mode = 'create', item = null) {
    if (!productEditModalOverlay || !productEditForm) return;

    if (mode === 'create') {
      if (prodModalTitle) prodModalTitle.textContent = 'Add New Product';
      if (prodModalSubtitle) prodModalSubtitle.textContent = 'Enter beverage specifications and pricing.';
      productEditForm.reset();
      document.getElementById('editOriginalId').value = '';
      document.getElementById('editAvailable').checked = true;
      document.getElementById('editBestseller').checked = false;
      document.querySelectorAll('input[name="editFlavors"]').forEach(cb => cb.checked = false);
    } else if (mode === 'edit' && item) {
      if (prodModalTitle) prodModalTitle.textContent = `Edit ${item.name}`;
      if (prodModalSubtitle) prodModalSubtitle.textContent = `Update prices, description, and status for ${item.id}.`;
      document.getElementById('editOriginalId').value = item.id;
      document.getElementById('editCategory').value = item.categoryId || 1;
      document.getElementById('editName').value = item.name || '';
      document.getElementById('editSlug').value = item.id || '';
      document.getElementById('editPrice').value = item.price || '';
      document.getElementById('editPriceIcedM').value = item.priceIcedM || item.price || '';
      document.getElementById('editPriceIcedL').value = item.priceIcedL || '';
      document.getElementById('editPriceHot').value = item.priceHot || '';
      document.getElementById('editOrigin').value = item.origin || '';
      document.getElementById('editElevation').value = item.elevation || '';
      document.getElementById('editImage').value = item.image || '';
      document.getElementById('editDesc').value = item.description || '';
      document.getElementById('editBestseller').checked = Boolean(item.isBestseller);
      document.getElementById('editAvailable').checked = item.isAvailable !== false;

      // Flavor checkboxes
      const itemFlavors = item.flavors || [];
      document.querySelectorAll('input[name="editFlavors"]').forEach(cb => {
        cb.checked = itemFlavors.includes(cb.value);
      });
    }

    productEditModalOverlay.classList.add('active');
  }

  function closeProductModal() {
    if (productEditModalOverlay) {
      productEditModalOverlay.classList.remove('active');
    }
  }

  if (closeProdEditModalBtn) closeProdEditModalBtn.addEventListener('click', closeProductModal);
  if (cancelProdEditBtn) cancelProdEditBtn.addEventListener('click', closeProductModal);

  // Submit Product Form
  if (productEditForm) {
    productEditForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const originalId = document.getElementById('editOriginalId').value;
      const slugInput = document.getElementById('editSlug').value.trim();

      const selectedFlavors = Array.from(document.querySelectorAll('input[name="editFlavors"]:checked')).map(cb => cb.value);

      const payload = {
        id: slugInput || originalId || '',
        category_id: parseInt(document.getElementById('editCategory').value, 10),
        name: document.getElementById('editName').value.trim(),
        price: parseFloat(document.getElementById('editPrice').value),
        price_iced_m: parseFloat(document.getElementById('editPriceIcedM').value),
        price_iced_l: document.getElementById('editPriceIcedL').value ? parseFloat(document.getElementById('editPriceIcedL').value) : null,
        price_hot: document.getElementById('editPriceHot').value ? parseFloat(document.getElementById('editPriceHot').value) : null,
        origin: document.getElementById('editOrigin').value.trim(),
        elevation: document.getElementById('editElevation').value.trim(),
        image: document.getElementById('editImage').value.trim(),
        description: document.getElementById('editDesc').value.trim(),
        is_bestseller: document.getElementById('editBestseller').checked,
        is_available: document.getElementById('editAvailable').checked,
        flavors: selectedFlavors
      };

      const submitBtn = document.getElementById('saveProductBtn');
      const originalText = submitBtn ? submitBtn.textContent : 'Save';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
      }

      try {
        const res = await fetch(getApiUrl('api/admin_menu.php?action=save_item'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Product saved successfully!');
          closeProductModal();
          loadCmsData();
          loadMenuFromAPI();
        } else {
          showToast(data.error || 'Failed to save product.');
        }
      } catch (err) {
        showToast('Network error saving product.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      }
    });
  }

  // Delete Product
  async function deleteProduct(id) {
    try {
      const res = await fetch(getApiUrl('api/admin_menu.php?action=delete_item'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id })
      });
      const data = await res.json();
      if (res.ok && data.success) {
        showToast(data.message || 'Product deleted.');
        loadCmsData();
        loadMenuFromAPI();
      } else {
        showToast(data.error || 'Failed to delete product.');
      }
    } catch (err) {
      showToast('Network error deleting product.');
    }
  }

  // --- 18. User Account Settings (Profile & Password Change) ---
  function setAccountAlert(message, type = 'error') {
    if (!accountAlertBox) return;
    if (!message) {
      accountAlertBox.style.display = 'none';
      accountAlertBox.textContent = '';
      accountAlertBox.className = 'account-alert';
      return;
    }
    accountAlertBox.textContent = message;
    accountAlertBox.className = `account-alert ${type}`;
    accountAlertBox.style.display = 'block';
  }

  function openAccountModal() {
    if (!state.currentUser) {
      openAuthModal('signin');
      return;
    }

    setAccountAlert('');
    if (accountModalTitle) accountModalTitle.textContent = state.currentUser.name || 'Account Settings';
    if (accountModalEmail) accountModalEmail.textContent = state.currentUser.email || '';
    
    const isAdmin = state.currentUser.role === 'admin';
    if (accountRolePill) {
      accountRolePill.textContent = isAdmin ? 'Administrator' : 'Customer Member';
      accountRolePill.className = `account-role-pill ${isAdmin ? 'admin' : 'customer'}`;
    }

    if (accountAvatarLarge) {
      const parts = (state.currentUser.name || 'Member').trim().split(/\s+/);
      accountAvatarLarge.textContent = parts.length > 1
        ? (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
        : parts[0].charAt(0).toUpperCase();
    }

    if (accountNameInput) accountNameInput.value = state.currentUser.name || '';
    if (accountEmailInput) accountEmailInput.value = state.currentUser.email || '';
    if (accountPhoneInput) accountPhoneInput.value = state.currentUser.phone || '';

    if (changePasswordForm) changePasswordForm.reset();

    // Default to Profile tab
    switchAccountTab('profile');

    if (accountSettingsModalOverlay) {
      accountSettingsModalOverlay.classList.add('active');
    }
  }

  function closeAccountModal() {
    if (accountSettingsModalOverlay) {
      accountSettingsModalOverlay.classList.remove('active');
    }
    setAccountAlert('');
  }

  function switchAccountTab(tabName) {
    setAccountAlert('');
    if (tabName === 'profile') {
      if (tabProfileBtn) {
        tabProfileBtn.classList.add('active');
        tabProfileBtn.setAttribute('aria-selected', 'true');
      }
      if (tabPasswordBtn) {
        tabPasswordBtn.classList.remove('active');
        tabPasswordBtn.setAttribute('aria-selected', 'false');
      }
      if (updateProfileForm) updateProfileForm.style.display = 'flex';
      if (changePasswordForm) changePasswordForm.style.display = 'none';
    } else {
      if (tabPasswordBtn) {
        tabPasswordBtn.classList.add('active');
        tabPasswordBtn.setAttribute('aria-selected', 'true');
      }
      if (tabProfileBtn) {
        tabProfileBtn.classList.remove('active');
        tabProfileBtn.setAttribute('aria-selected', 'false');
      }
      if (updateProfileForm) updateProfileForm.style.display = 'none';
      if (changePasswordForm) changePasswordForm.style.display = 'flex';
    }
  }

  if (userProfileTrigger) userProfileTrigger.addEventListener('click', openAccountModal);
  if (mobileAccountSettingsTrigger) {
    mobileAccountSettingsTrigger.addEventListener('click', () => {
      openAccountModal();
      if (navLinks) navLinks.classList.remove('active');
      if (mobileToggle) mobileToggle.classList.remove('active');
    });
  }
  if (closeAccountModalBtn) closeAccountModalBtn.addEventListener('click', closeAccountModal);

  if (tabProfileBtn) tabProfileBtn.addEventListener('click', () => switchAccountTab('profile'));
  if (tabPasswordBtn) tabPasswordBtn.addEventListener('click', () => switchAccountTab('password'));

  // Submit Profile & Username Changes
  if (updateProfileForm) {
    updateProfileForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      setAccountAlert('');
      const name = accountNameInput ? accountNameInput.value.trim() : '';
      const email = accountEmailInput ? accountEmailInput.value.trim().toLowerCase() : '';
      const phone = accountPhoneInput ? accountPhoneInput.value.trim() : '';

      if (name.length < 2) {
        setAccountAlert('Name must be at least 2 characters.', 'error');
        return;
      }

      if (!email || !email.includes('@')) {
        setAccountAlert('Please enter a valid email address.', 'error');
        return;
      }

      if (saveProfileBtn) {
        saveProfileBtn.disabled = true;
        saveProfileBtn.textContent = 'Saving...';
      }

      try {
        const res = await fetch(getApiUrl('api/auth.php?action=update_profile'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({ name, email, phone })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          state.currentUser = data.user;
          updateAuthUI();
          if (accountModalTitle) accountModalTitle.textContent = data.user.name;
          if (accountModalEmail) accountModalEmail.textContent = data.user.email;
          if (accountAvatarLarge) {
            const parts = data.user.name.trim().split(/\s+/);
            accountAvatarLarge.textContent = parts.length > 1
              ? (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
              : parts[0].charAt(0).toUpperCase();
          }
          setAccountAlert(data.message || 'Profile updated successfully!', 'success');
          showToast(data.message || 'Profile updated successfully!');
        } else {
          setAccountAlert(data.error || 'Failed to update profile.', 'error');
        }
      } catch (err) {
        setAccountAlert('Network error while updating profile.', 'error');
      } finally {
        if (saveProfileBtn) {
          saveProfileBtn.disabled = false;
          saveProfileBtn.textContent = 'Save Profile Changes';
        }
      }
    });
  }

  // Submit Password Change
  if (changePasswordForm) {
    changePasswordForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      setAccountAlert('');
      const currentPassword = currentPasswordInput ? currentPasswordInput.value : '';
      const newPassword = newPasswordInput ? newPasswordInput.value : '';
      const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

      if (newPassword.length < 8) {
        setAccountAlert('New password must be at least 8 characters long.', 'error');
        return;
      }

      if (newPassword !== confirmPassword) {
        setAccountAlert('New password confirmation does not match.', 'error');
        return;
      }

      if (savePasswordBtn) {
        savePasswordBtn.disabled = true;
        savePasswordBtn.textContent = 'Updating...';
      }

      try {
        const res = await fetch(getApiUrl('api/auth.php?action=change_password'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          changePasswordForm.reset();
          setAccountAlert(data.message || 'Password changed successfully!', 'success');
          showToast(data.message || 'Password changed successfully!');
        } else {
          setAccountAlert(data.error || 'Failed to change password.', 'error');
        }
      } catch (err) {
        setAccountAlert('Network error while changing password.', 'error');
      } finally {
        if (savePasswordBtn) {
          savePasswordBtn.disabled = false;
          savePasswordBtn.textContent = 'Update Password';
        }
      }
    });
  }

  // --- 16. Initial Startup Sequence ---
  renderMenu();
  updateCartUI();
  checkAuthStatus();
  loadMenuFromAPI();
});
