/**
 * BeCoffee — Interactive Application Logic & Offline Demo Engine
 * Philippine Specialty Coffee & Roastery Experience
 */

// ==============================================================================
// 0. Cloud Demo Mode & Client-Side Mock API Engine (Netlify / Jamstack / Offline)
// ==============================================================================
(function initDemoEngine() {
  const isStaticPlatform =
    window.location.hostname.includes('netlify.app') ||
    window.location.hostname.includes('github.io') ||
    window.location.hostname.includes('vercel.app') ||
    window.location.hostname.includes('surge.sh') ||
    window.location.protocol === 'file:' ||
    window.location.search.includes('demo=1') ||
    window.location.search.includes('demo=true') ||
    localStorage.getItem('becoffee_demo_active') === 'true';

  window.BECOFFEE_DEMO_ACTIVE = isStaticPlatform;

  const DEMO_USERS_KEY = 'becoffee_demo_users';
  const DEMO_SESSION_KEY = 'becoffee_demo_session';
  const DEMO_ORDERS_KEY = 'becoffee_demo_orders';
  const DEMO_RESERVATIONS_KEY = 'becoffee_demo_reservations';
  const DEMO_MENU_KEY = 'becoffee_demo_menu';

  function getDemoUsers() {
    try {
      const stored = JSON.parse(localStorage.getItem(DEMO_USERS_KEY) || '[]');
      const defaultUsers = [
        {
          id: 1,
          name: 'BeCoffee Administrator',
          email: 'admin',
          phone: '+63 917 555 2026',
          role: 'admin',
          password: 'admin123'
        },
        {
          id: 3,
          name: 'BeCoffee SuperAdmin (Developer)',
          email: 'superadmin',
          phone: '+63 917 555 2000',
          role: 'superadmin',
          password: 'superadmin123'
        },
        {
          id: 4,
          name: 'COUNTER POS',
          email: 'staff',
          phone: '+63 917 555 2001',
          role: 'staff',
          password: 'staff123'
        }
      ];

      defaultUsers.forEach(def => {
        const idx = stored.findIndex(u => u.role === def.role || (u.email || '').toLowerCase() === def.email.toLowerCase());
        if (idx === -1) {
          stored.push(def);
        } else {
          stored[idx] = Object.assign({}, stored[idx], def);
        }
      });

      localStorage.setItem(DEMO_USERS_KEY, JSON.stringify(stored));
      return stored;
    } catch (e) {
      return [
        { id: 1, name: 'BeCoffee Administrator', email: 'admin', phone: '+63 917 555 2026', role: 'admin', password: 'admin123' },
        { id: 3, name: 'BeCoffee SuperAdmin (Developer)', email: 'superadmin', phone: '+63 917 555 2000', role: 'superadmin', password: 'superadmin123' },
        { id: 4, name: 'COUNTER POS', email: 'staff', phone: '+63 917 555 2001', role: 'staff', password: 'staff123' }
      ];
    }
  }

  // Pre-seed demo users
  getDemoUsers();

  function makeJsonResponse(data, status = 200) {
    const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
    return new Response(blob, {
      status,
      statusText: status === 200 ? 'OK' : 'Error',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
        'X-BeCoffee-Demo': 'true'
      }
    });
  }

  async function handleDemoRequest(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    let body = {};
    if (options.body) {
      try {
        body = typeof options.body === 'string' ? JSON.parse(options.body) : options.body;
      } catch (e) {
        body = {};
      }
    }

    let pathname = '';
    let action = '';
    try {
      const parsed = new URL(url, window.location.href);
      pathname = parsed.pathname;
      action = parsed.searchParams.get('action') || '';
    } catch (e) {
      pathname = url;
    }

    // 1. Auth: Login
    if (pathname.includes('auth.php') && action === 'login' && method === 'POST') {
      const email = (body.email || '').trim().toLowerCase();
      const password = body.password || '';

      if (!email || !password) {
        return makeJsonResponse({ success: false, error: 'Please enter both your email and password.' }, 422);
      }

      const users = getDemoUsers();
      const user = users.find(u => {
        const uEmail = (u.email || '').toLowerCase();
        if (uEmail === email) return true;
        if ((email === 'superadmin' || email === 'dev' || email === 'dev@becoffee.internal') && (u.role === 'superadmin' || uEmail === 'superadmin')) return true;
        if ((email === 'admin' || email === 'admin@becoffee.ph') && (u.role === 'admin' || uEmail === 'admin')) return true;
        if ((email === 'staff' || email === 'staff@becoffee.ph') && (u.role === 'staff' || uEmail === 'staff')) return true;
        return false;
      });

      let isValid = false;
      if (user) {
        if (user.password === password) {
          isValid = true;
        } else if (user.role === 'superadmin' && password === 'superadmin123') {
          isValid = true;
        } else if (user.role === 'admin' && (password === 'admin123' || password === 'AdminBeCoffee2026!')) {
          isValid = true;
        } else if (user.role === 'staff' && password === 'staff123') {
          isValid = true;
        }
      }

      if (!isValid) {
        return makeJsonResponse({ success: false, error: 'Invalid email or password. Please try again.' }, 401);
      }

      let targetView = 'index.php';
      if (user.role === 'superadmin') targetView = 'admin.php?view=developer';
      else if (user.role === 'admin') targetView = 'admin.php';
      else if (user.role === 'staff') targetView = 'kds.php';

      const safeUser = {
        id: user.id,
        name: user.name,
        email: user.email,
        phone: user.phone || '+63 917 555 2026',
        role: user.role,
        target_view: targetView
      };

      localStorage.setItem(DEMO_SESSION_KEY, JSON.stringify(safeUser));
      return makeJsonResponse({
        success: true,
        message: 'Welcome back to BeCoffee! (Demo Mode)',
        user: safeUser,
        target_view: targetView
      });
    }

    // 2. Auth: Register
    if (pathname.includes('auth.php') && action === 'register' && method === 'POST') {
      const name = (body.name || '').trim();
      const email = (body.email || '').trim().toLowerCase();
      const phone = (body.phone || '').trim();
      const password = body.password || '';

      if (!name || !email || !password) {
        return makeJsonResponse({ success: false, error: 'Please fill in all required fields.' }, 422);
      }

      const users = getDemoUsers();
      if (users.some(u => (u.email || '').toLowerCase() === email)) {
        return makeJsonResponse({ success: false, error: 'An account with this email address already exists.' }, 409);
      }

      const newUser = {
        id: Date.now(),
        name,
        email,
        phone: phone || '+63 917 555 0000',
        role: 'customer',
        password
      };
      users.push(newUser);
      localStorage.setItem(DEMO_USERS_KEY, JSON.stringify(users));

      const safeUser = { id: newUser.id, name, email, phone: newUser.phone, role: 'customer' };
      localStorage.setItem(DEMO_SESSION_KEY, JSON.stringify(safeUser));

      return makeJsonResponse({
        success: true,
        message: 'Account created successfully! Welcome to BeCoffee (Demo Mode).',
        user: safeUser
      });
    }

    // 3. Auth: Current Session (me)
    if (pathname.includes('auth.php') && (action === 'me' || !action) && method === 'GET') {
      try {
        const session = JSON.parse(localStorage.getItem(DEMO_SESSION_KEY) || 'null');
        if (session && session.id) {
          return makeJsonResponse({ success: true, user: session });
        }
      } catch (e) {}
      return makeJsonResponse({ success: false, user: null }, 401);
    }

    // 4. Auth: Logout
    if (pathname.includes('auth.php') && action === 'logout' && method === 'POST') {
      localStorage.removeItem(DEMO_SESSION_KEY);
      return makeJsonResponse({ success: true, message: 'Logged out successfully.' });
    }

    // 5. Auth: Update Profile
    if (pathname.includes('auth.php') && action === 'update_profile' && method === 'POST') {
      let session = null;
      try { session = JSON.parse(localStorage.getItem(DEMO_SESSION_KEY)); } catch (e) {}
      if (!session) return makeJsonResponse({ success: false, error: 'Authentication required.' }, 401);

      session.name = (body.name || session.name).trim();
      session.email = (body.email || session.email).trim();
      session.phone = (body.phone || session.phone).trim();

      localStorage.setItem(DEMO_SESSION_KEY, JSON.stringify(session));
      const users = getDemoUsers().map(u => u.id === session.id ? { ...u, ...session } : u);
      localStorage.setItem(DEMO_USERS_KEY, JSON.stringify(users));

      return makeJsonResponse({ success: true, message: 'Profile updated successfully!', user: session });
    }

    // 6. Auth: Change Password
    if (pathname.includes('auth.php') && action === 'change_password' && method === 'POST') {
      let session = null;
      try { session = JSON.parse(localStorage.getItem(DEMO_SESSION_KEY)); } catch (e) {}
      if (!session) return makeJsonResponse({ success: false, error: 'Authentication required.' }, 401);

      const { current_password, new_password, confirm_password } = body;
      if (!current_password || !new_password) {
        return makeJsonResponse({ success: false, error: 'Please fill in both current and new password.' }, 422);
      }
      if (new_password.length < 8) {
        return makeJsonResponse({ success: false, error: 'New password must be at least 8 characters long.' }, 422);
      }
      if (new_password !== confirm_password) {
        return makeJsonResponse({ success: false, error: 'Password confirmation does not match.' }, 422);
      }

      const users = getDemoUsers();
      const user = users.find(u => u.id === session.id);
      if (user && user.password !== current_password && current_password !== 'admin123' && current_password !== 'superadmin123' && current_password !== 'staff123') {
        return makeJsonResponse({ success: false, error: 'Current password is incorrect.' }, 400);
      }

      if (user) {
        user.password = new_password;
        localStorage.setItem(DEMO_USERS_KEY, JSON.stringify(users));
      }
      return makeJsonResponse({ success: true, message: 'Password changed successfully!' });
    }

    // 7. Orders: Place Order
    if (pathname.includes('orders.php') && method === 'POST') {
      const orderCode = 'BC-' + Math.floor(100000 + Math.random() * 900000);
      const orders = JSON.parse(localStorage.getItem(DEMO_ORDERS_KEY) || '[]');
      const newOrder = {
        id: Date.now(),
        order_code: orderCode,
        created_at: new Date().toISOString(),
        ...body
      };
      orders.push(newOrder);
      localStorage.setItem(DEMO_ORDERS_KEY, JSON.stringify(orders));

      return makeJsonResponse({
        success: true,
        message: 'Order placed successfully! (Demo Mode)',
        order: { id: newOrder.id, order_code: orderCode }
      });
    }

    // 8. Reservations: Book Table
    if (pathname.includes('reservations.php') && method === 'POST') {
      const resCode = 'RES-' + Math.floor(100000 + Math.random() * 900000);
      const resList = JSON.parse(localStorage.getItem(DEMO_RESERVATIONS_KEY) || '[]');
      const newRes = {
        id: Date.now(),
        code: resCode,
        created_at: new Date().toISOString(),
        ...body
      };
      resList.push(newRes);
      localStorage.setItem(DEMO_RESERVATIONS_KEY, JSON.stringify(resList));

      return makeJsonResponse({
        success: true,
        message: 'Table reservation confirmed! (Demo Mode)',
        reservation: { id: newRes.id, code: resCode }
      });
    }

    // 9. Admin Menu & Catalog CMS
    if (pathname.includes('admin_menu.php')) {
      let currentItems = JSON.parse(localStorage.getItem(DEMO_MENU_KEY) || 'null');
      if (!Array.isArray(currentItems) || !currentItems.length) {
        currentItems = window.DEFAULT_BECOFFEE_MENU ? JSON.parse(JSON.stringify(window.DEFAULT_BECOFFEE_MENU)) : [];
        if (currentItems.length) {
          localStorage.setItem(DEMO_MENU_KEY, JSON.stringify(currentItems));
        }
      }

      if (action === 'toggle_availability' && method === 'POST') {
        const { item_id, is_available } = body;
        currentItems = currentItems.map(it => it.id === item_id ? { ...it, isAvailable: Boolean(is_available) } : it);
        localStorage.setItem(DEMO_MENU_KEY, JSON.stringify(currentItems));
        return makeJsonResponse({ success: true, message: 'Availability updated.' });
      }

      if (action === 'save_item' && method === 'POST') {
        const itemIndex = currentItems.findIndex(it => it.id === body.id);
        if (itemIndex >= 0) {
          currentItems[itemIndex] = { ...currentItems[itemIndex], ...body };
        } else {
          currentItems.push({ id: body.id || ('custom-' + Date.now()), ...body });
        }
        localStorage.setItem(DEMO_MENU_KEY, JSON.stringify(currentItems));
        return makeJsonResponse({ success: true, message: 'Item saved successfully.' });
      }

      if (action === 'delete_item' && method === 'POST') {
        currentItems = currentItems.filter(it => it.id !== body.item_id);
        localStorage.setItem(DEMO_MENU_KEY, JSON.stringify(currentItems));
        return makeJsonResponse({ success: true, message: 'Item deleted.' });
      }

      // Default GET for Admin CMS
      const categories = [
        { id: 1, slug: 'house-coffee', name: 'House Coffee' },
        { id: 2, slug: 'matcha', name: 'Ceremonial Matcha' },
        { id: 3, slug: 'house-specials', name: 'House Specials' },
        { id: 4, slug: 'yogurt-soda', name: 'Yogurt / Soda' }
      ];
      return makeJsonResponse({
        success: true,
        stats: {
          total: currentItems.length,
          active: currentItems.filter(i => i.isAvailable !== false).length,
          unavailable: currentItems.filter(i => i.isAvailable === false).length
        },
        categories,
        items: currentItems
      });
    }

    // 10. Menu Catalog Fetch
    if (pathname.includes('menu.php') && method === 'GET') {
      let menu = JSON.parse(localStorage.getItem(DEMO_MENU_KEY) || 'null');
      if (!Array.isArray(menu) || !menu.length) {
        menu = window.DEFAULT_BECOFFEE_MENU ? JSON.parse(JSON.stringify(window.DEFAULT_BECOFFEE_MENU)) : [];
        if (menu.length) {
          localStorage.setItem(DEMO_MENU_KEY, JSON.stringify(menu));
        }
      }
      return makeJsonResponse({ success: true, count: menu.length, items: menu });
    }

    return makeJsonResponse({ success: false, error: 'Endpoint not supported in demo mode' }, 404);
  }

  // Hook into window.fetch
  const originalFetch = window.fetch;
  window.fetch = async function (input, init = {}) {
    const urlString = typeof input === 'string' ? input : (input && input.url ? input.url : '');
    const isApiRequest = urlString.includes('api/') || urlString.includes('.php');

    if (!isApiRequest) {
      return originalFetch.apply(this, arguments);
    }

    // Static host or demo mode actively engaged
    if (window.BECOFFEE_DEMO_ACTIVE) {
      return handleDemoRequest(urlString, init);
    }

    // Attempt real backend first (e.g. local XAMPP)
    try {
      const res = await originalFetch.apply(this, arguments);
      const cType = (res.headers.get('content-type') || '').toLowerCase();
      // If server returns static PHP source or HTML error page on static host
      if (cType.includes('application/x-php') || (!res.ok && cType.includes('text/html'))) {
        console.warn('Backend unavailable or static host detected. Switching to Demo Mode for:', urlString);
        window.BECOFFEE_DEMO_ACTIVE = true;
        renderDemoBadge();
        return handleDemoRequest(urlString, init);
      }
      return res;
    } catch (err) {
      console.warn('Network error reaching backend. Switching to Demo Mode for:', urlString);
      window.BECOFFEE_DEMO_ACTIVE = true;
      renderDemoBadge();
      return handleDemoRequest(urlString, init);
    }
  };

  function renderDemoBadge() {
    // Only render on explicitly static demo preview environments without PHP backend
    if (!window.BECOFFEE_DEMO_ACTIVE) return;
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') return;
    if (document.getElementById('becoffeeDemoBadge')) return;
    const badge = document.createElement('div');
    badge.id = 'becoffeeDemoBadge';
    badge.className = 'demo-mode-badge';
    badge.setAttribute('title', 'BeCoffee Client Demonstration Preview');
    badge.innerHTML = `
      <span class="demo-pulse"></span>
      <span>Cloud Demo Preview</span>
    `;
    badge.addEventListener('click', () => {
      if (typeof showToast === 'function') {
        showToast('Running in static client preview mode.');
      }
    });
    document.body.appendChild(badge);
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (window.BECOFFEE_DEMO_ACTIVE) {
      renderDemoBadge();
    }
  });
})();

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. State Management ---
  const state = {
    currentUser: null,
    pendingCheckout: false,
    activeCategory: 'house-coffee',
    activeLocation: 'putik',
    activeFlavorFilter: null,
    cart: JSON.parse(localStorage.getItem('becoffee_cart') || '[]'),
    menuItems: window.DEFAULT_BECOFFEE_MENU ? JSON.parse(JSON.stringify(window.DEFAULT_BECOFFEE_MENU)) : []
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

  // Order Customization Modal Elements (Temperature, Add Ons, Sweetness, Notes)
  const orderModalOverlay = document.getElementById('orderModalOverlay');
  const closeOrderModalBtn = document.getElementById('closeOrderModalBtn');
  const orderModalImg = document.getElementById('orderModalImg');
  const orderModalTitle = document.getElementById('orderModalTitle');
  const orderModalCategory = document.getElementById('orderModalCategory');
  const orderModalDesc = document.getElementById('orderModalDesc');
  const orderModalBasePrice = document.getElementById('orderModalBasePrice');
  const tempIcedBtn = document.getElementById('tempIcedBtn');
  const tempHotBtn = document.getElementById('tempHotBtn');
  const tempOptionsGroup = document.getElementById('tempOptionsGroup');
  const addonOptionsGroup = document.getElementById('addonOptionsGroup');
  const sweetnessOptionsGroup = document.getElementById('sweetnessOptionsGroup');
  const orderModalNotesInput = document.getElementById('orderModalNotesInput');
  const modalQtyMinus = document.getElementById('modalQtyMinus');
  const modalQtyPlus = document.getElementById('modalQtyPlus');
  const modalQtyVal = document.getElementById('modalQtyVal');
  const orderConfirmBtn = document.getElementById('orderConfirmBtn');
  const orderConfirmTotal = document.getElementById('orderConfirmTotal');

  let currentCustomizingItem = null;
  let customTemp = 'Iced';
  let customAddon = 'Regular Milk';
  let customAddonSurcharge = 0;
  let customSweetness = 'Less Sweet (75%)';
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

  // Guest & Member Checkout Inputs
  const orderCustomerName = document.getElementById('orderCustomerName');
  const orderCustomerPhone = document.getElementById('orderCustomerPhone');
  const orderCustomerNotes = document.getElementById('orderCustomerNotes');
  const orderArrivalTime = document.getElementById('orderArrivalTime');
  const orderCustomTimeInput = document.getElementById('orderCustomTimeInput');
  const orderCustomTimeWrap = document.getElementById('orderCustomTimeWrap');
  const checkoutModeLabel = document.getElementById('checkoutModeLabel');

  // Location Outpost Tabs
  const locTabBtns = document.querySelectorAll('.loc-tab-btn');
  const locCardTitle = document.getElementById('locCardTitle');
  const locCardBadge = document.getElementById('locCardBadge');
  const locAddressText = document.getElementById('locAddressText');
  const locHoursText = document.getElementById('locHoursText');
  const locPhoneWrap = document.getElementById('locPhoneWrap');
  const locPhoneText = document.getElementById('locPhoneText');
  const locFeaturesText = document.getElementById('locFeaturesText');
  const locImage = document.getElementById('locImage');
  const locDirectionsBtn = document.getElementById('locDirectionsBtn');

  // Helper to dynamically resolve API endpoints across localhost, 127.0.0.1, Live Server (5500), and file://
  function getApiUrl(endpoint) {
    if (window.BECOFFEE_DEMO_ACTIVE) {
      return endpoint;
    }
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

  // --- 4b. Top Announcement Pop-up Auto-Dismiss (6 seconds) ---
  const topBanner = document.getElementById('topBanner');
  const closeBannerBtn = document.getElementById('closeBannerBtn');

  if (topBanner) {
    let bannerDismissed = false;
    let dismissTimer = null;

    const dismissBanner = () => {
      if (bannerDismissed) return;
      bannerDismissed = true;
      if (dismissTimer) clearTimeout(dismissTimer);
      topBanner.style.animation = 'none';
      topBanner.classList.add('banner-dismissed');
      setTimeout(() => {
        topBanner.style.display = 'none';
        topBanner.setAttribute('aria-hidden', 'true');
      }, 280);
    };

    // Auto dismiss after 6 seconds
    dismissTimer = setTimeout(dismissBanner, 6000);

    // Manual dismiss button - binds click, pointerdown, and touchstart
    if (closeBannerBtn) {
      const handleClose = (e) => {
        if (e) {
          e.preventDefault();
          e.stopPropagation();
        }
        if (dismissTimer) clearTimeout(dismissTimer);
        dismissBanner();
      };
      closeBannerBtn.addEventListener('click', handleClose);
      closeBannerBtn.addEventListener('pointerdown', handleClose);
      closeBannerBtn.addEventListener('touchstart', handleClose, { passive: false });
    }

    // Pause countdown when user hovers to click/read, resume with grace on mouse leave
    topBanner.addEventListener('mouseenter', () => {
      if (dismissTimer) clearTimeout(dismissTimer);
    });

    topBanner.addEventListener('mouseleave', () => {
      if (!bannerDismissed) {
        dismissTimer = setTimeout(dismissBanner, 2500);
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
    if (state.activeCategory) {
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
          state.activeCategory = 'house-coffee';
          state.activeFlavorFilter = null;
          categoryTabs.forEach(t => t.classList.toggle('active', t.dataset.category === 'house-coffee'));
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
            <span class="meta-pill">Medium: ${formatPHP(item.priceIcedM || item.price)}</span>
            ${item.priceIcedL ? `<span class="meta-pill">Large: ${formatPHP(item.priceIcedL)}</span>` : ''}
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

        // If current active category doesn't have any items with this flavor, switch to first category that does
        if (state.activeCategory) {
          const hasMatches = state.menuItems.some(
            item => item.category === state.activeCategory && item.flavors && item.flavors.includes(flavor)
          );
          if (!hasMatches) {
            const matchItem = state.menuItems.find(
              item => item.flavors && item.flavors.includes(flavor)
            );
            if (matchItem) {
              state.activeCategory = matchItem.category;
              categoryTabs.forEach(t => t.classList.toggle('active', t.dataset.category === matchItem.category));
            }
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
    const item = (state.menuItems && state.menuItems.find(i => i.id === itemId))
      || (typeof MENU_ITEMS !== 'undefined' && MENU_ITEMS.find(i => i.id === itemId))
      || (typeof menuData !== 'undefined' && menuData.find(i => i.id === itemId));
    if (!item || !orderModalOverlay) return;

    currentCustomizingItem = item;
    customTemp = 'Iced';
    customAddon = 'Regular Milk';
    customAddonSurcharge = 0;
    customSweetness = 'Less Sweet (75%)';
    customQty = 1;

    if (orderModalImg) {
      orderModalImg.src = item.image ? `${item.image}?v=7.0` : 'images/menu/hc-caramel-macchiato.webp';
      orderModalImg.alt = item.name;
    }
    if (orderModalTitle) orderModalTitle.textContent = item.name;
    if (orderModalCategory) {
      const catName = (item.category || 'COFFEE').toUpperCase().replace(/-/g, ' ');
      orderModalCategory.textContent = catName;
    }
    if (orderModalDesc) {
      orderModalDesc.textContent = item.notes || item.description || 'Crafted fresh to order with premium artisan ingredients.';
    }
    if (orderModalBasePrice) {
      orderModalBasePrice.textContent = `₱${parseFloat(item.price || 120).toFixed(2)}`;
    }
    if (orderModalNotesInput) {
      orderModalNotesInput.value = '';
    }

    // Temperature Availability: Yogurt/Soda are iced-only
    const isIcedOnly = item.category === 'yogurt-soda' || (item.elevation && item.elevation.includes('Iced Only')) || item.priceHot === null;
    if (tempHotBtn) {
      if (!isIcedOnly) {
        tempHotBtn.classList.remove('is-sold-out', 'disabled');
        tempHotBtn.removeAttribute('aria-disabled');
      } else {
        tempHotBtn.classList.add('is-sold-out');
        tempHotBtn.setAttribute('aria-disabled', 'true');
      }
    }

    // Default Selection: Iced, Regular Milk (+₱0), 75% Less Sweet, Qty 1
    if (tempIcedBtn) {
      tempIcedBtn.classList.add('active');
      tempIcedBtn.setAttribute('aria-checked', 'true');
    }
    if (tempHotBtn) {
      tempHotBtn.classList.remove('active');
      tempHotBtn.setAttribute('aria-checked', 'false');
    }

    // Reset Add-ons pills to Regular Milk active
    if (addonOptionsGroup) {
      addonOptionsGroup.querySelectorAll('.pill-radio-opt').forEach(opt => {
        const isReg = opt.getAttribute('data-val') === 'Regular Milk';
        opt.classList.toggle('active', isReg);
        opt.setAttribute('aria-checked', isReg ? 'true' : 'false');
      });
    }

    // Reset Sweetness pills to 75% Less Sweet active
    if (sweetnessOptionsGroup) {
      sweetnessOptionsGroup.querySelectorAll('.pill-radio-opt').forEach(opt => {
        const isLess = opt.getAttribute('data-val') === 'Less Sweet (75%)';
        opt.classList.toggle('active', isLess);
        opt.setAttribute('aria-checked', isLess ? 'true' : 'false');
      });
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
    const basePrice = parseFloat(currentCustomizingItem.price) || 120;
    const unitPrice = basePrice + customAddonSurcharge;
    const total = unitPrice * customQty;
    if (orderConfirmTotal) {
      orderConfirmTotal.textContent = `₱${total.toFixed(2)}`;
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
      if (tempHotBtn.classList.contains('is-sold-out') || tempHotBtn.classList.contains('disabled')) return;
      customTemp = 'Hot';
      tempHotBtn.classList.add('active');
      tempHotBtn.setAttribute('aria-checked', 'true');
      tempIcedBtn.classList.remove('active');
      tempIcedBtn.setAttribute('aria-checked', 'false');
      updateOrderModalTotal();
    });
  }

  if (addonOptionsGroup) {
    addonOptionsGroup.addEventListener('click', (e) => {
      const opt = e.target.closest('.pill-radio-opt');
      if (!opt || opt.classList.contains('is-sold-out')) return;
      customAddon = opt.getAttribute('data-val') || 'Regular Milk';
      customAddonSurcharge = parseFloat(opt.getAttribute('data-surcharge')) || 0;
      addonOptionsGroup.querySelectorAll('.pill-radio-opt').forEach(p => {
        p.classList.toggle('active', p === opt);
        p.setAttribute('aria-checked', p === opt ? 'true' : 'false');
      });
      updateOrderModalTotal();
    });
  }

  if (sweetnessOptionsGroup) {
    sweetnessOptionsGroup.addEventListener('click', (e) => {
      const opt = e.target.closest('.pill-radio-opt');
      if (!opt || opt.classList.contains('is-sold-out')) return;
      customSweetness = opt.getAttribute('data-val') || 'Less Sweet (75%)';
      sweetnessOptionsGroup.querySelectorAll('.pill-radio-opt').forEach(p => {
        p.classList.toggle('active', p === opt);
        p.setAttribute('aria-checked', p === opt ? 'true' : 'false');
      });
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
      const notes = orderModalNotesInput ? orderModalNotesInput.value.trim() : '';
      addToCartCustomized(currentCustomizingItem, customTemp, customAddon, customAddonSurcharge, customSweetness, notes, customQty);
      closeOrderModal();
    });
  }

  function saveCart() {
    localStorage.setItem('becoffee_cart', JSON.stringify(state.cart));
    updateCartUI();
  }

  function addToCartCustomized(item, temperature, addon, surcharge, sweetness, notes, qty = 1) {
    const basePrice = parseFloat(item.price) || 120;
    const unitPrice = basePrice + surcharge;
    const sanitizedKey = `${item.id}-${temperature.toLowerCase()}-${addon.toLowerCase().replace(/[^a-z0-9]/g, '')}-${sweetness.toLowerCase().replace(/[^a-z0-9]/g, '')}-${(notes || '').toLowerCase().slice(0, 10).replace(/[^a-z0-9]/g, '')}`;

    const existing = state.cart.find(i => (i.cartItemId || i.id) === sanitizedKey);
    if (existing) {
      existing.qty += qty;
    } else {
      state.cart.push({
        cartItemId: sanitizedKey,
        id: item.id,
        name: item.name,
        temperature: temperature,
        milk_option: addon,
        sweetness_level: sweetness,
        custom_notes: notes,
        price: unitPrice,
        qty: qty
      });
    }

    saveCart();

    // Automatically slide open the cart drawer so user sees customized item added
    if (cartDrawerOverlay) {
      cartDrawerOverlay.classList.add('active');
    }

    showToast(`Added "${item.name} (${temperature} · ${addon})"${qty > 1 ? ` ×${qty}` : ''} to order (₱${(unitPrice * qty).toFixed(2)})`);
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
    const mobileBarCartBadge = document.getElementById('mobileBarCartBadge');
    if (mobileBarCartBadge) {
      mobileBarCartBadge.textContent = totalCount;
      mobileBarCartBadge.style.display = totalCount > 0 ? 'flex' : 'none';
    }
    const staffBarCartCount = document.getElementById('staffBarCartCount');
    if (staffBarCartCount) staffBarCartCount.textContent = totalCount;

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
          <p style="font-size: 0.85rem; margin-bottom: 0.75rem;">Explore our single-origins, espresso drinks, or bakery offerings.</p>
          <a href="#menu" class="btn btn-sm btn-crema" style="display: inline-flex;" onclick="document.getElementById('cartDrawerOverlay').classList.remove('active');">Explore Handcrafted Menu</a>
        </div>
      `;
      if (cartSubtotalEl) cartSubtotalEl.textContent = '₱0';
      if (cartEcoFeeEl) cartEcoFeeEl.textContent = '₱0';
      if (cartGrandTotalEl) cartGrandTotalEl.textContent = '₱0';
      const cartEtaCard = document.getElementById('cartEtaCard');
      if (cartEtaCard) cartEtaCard.style.display = 'none';
      const customTimeWrap = document.getElementById('orderCustomTimeWrap');
      if (customTimeWrap) customTimeWrap.style.display = 'none';
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

    // Dynamic Estimated Pickup Time & Customer Arrival Time
    const totalDrinks = state.cart.reduce((sum, i) => sum + i.qty, 0);
    let minMins = 8;
    let maxMins = 12;
    if (totalDrinks >= 2 && totalDrinks <= 3) {
      minMins = 10;
      maxMins = 15;
    } else if (totalDrinks >= 4 && totalDrinks <= 5) {
      minMins = 15;
      maxMins = 20;
    } else if (totalDrinks > 5) {
      minMins = 20;
      maxMins = 25;
    }

    const now = new Date();
    const asapTarget = new Date(now.getTime() + minMins * 60000);
    const asapTimeStr = asapTarget.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    const outpostSelect = document.getElementById('orderCustomerNotes');
    const outpostName = (outpostSelect && outpostSelect.value) ? outpostSelect.value : 'Putik';
    const arrivalSelect = document.getElementById('orderArrivalTime');
    const customTimeInput = document.getElementById('orderCustomTimeInput');
    const customTimeWrap = document.getElementById('orderCustomTimeWrap');

    let arrivalMode = arrivalSelect ? arrivalSelect.value : 'asap';
    let displayTimeStr = asapTimeStr;
    let badgeText = `~${minMins}–${maxMins} mins`;
    let arrivalSubtext = `Customer can come by: ~${asapTimeStr} at ${outpostName} (${totalDrinks} ${totalDrinks === 1 ? 'drink' : 'drinks'})`;

    if (arrivalSelect) {
      const optAsap = arrivalSelect.querySelector('option[value="asap"]');
      const opt15 = arrivalSelect.querySelector('option[value="15"]');
      const opt30 = arrivalSelect.querySelector('option[value="30"]');
      const opt45 = arrivalSelect.querySelector('option[value="45"]');
      const opt60 = arrivalSelect.querySelector('option[value="60"]');

      const t15 = new Date(now.getTime() + 15 * 60000).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      const t30 = new Date(now.getTime() + 30 * 60000).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      const t45 = new Date(now.getTime() + 45 * 60000).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      const t60 = new Date(now.getTime() + 60 * 60000).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });

      if (optAsap) optAsap.textContent = `ASAP (~${asapTimeStr}, ready in ${minMins}–${maxMins}m)`;
      if (opt15) opt15.textContent = `In 15 minutes (~${t15})`;
      if (opt30) opt30.textContent = `In 30 minutes (~${t30})`;
      if (opt45) opt45.textContent = `In 45 minutes (~${t45})`;
      if (opt60) opt60.textContent = `In 1 hour (~${t60})`;

      if (arrivalMode === '15') {
        displayTimeStr = t15;
        badgeText = 'In 15 mins';
        arrivalSubtext = `Customer can come by: ~${t15} at ${outpostName}`;
      } else if (arrivalMode === '30') {
        displayTimeStr = t30;
        badgeText = 'In 30 mins';
        arrivalSubtext = `Customer can come by: ~${t30} at ${outpostName}`;
      } else if (arrivalMode === '45') {
        displayTimeStr = t45;
        badgeText = 'In 45 mins';
        arrivalSubtext = `Customer can come by: ~${t45} at ${outpostName}`;
      } else if (arrivalMode === '60') {
        displayTimeStr = t60;
        badgeText = 'In 1 hour';
        arrivalSubtext = `Customer can come by: ~${t60} at ${outpostName}`;
      } else if (arrivalMode === 'custom') {
        if (customTimeWrap) customTimeWrap.style.display = 'block';
        if (customTimeInput && customTimeInput.value) {
          const parts = customTimeInput.value.split(':');
          const hr = parseInt(parts[0], 10);
          const mn = parts[1];
          const ampm = hr >= 12 ? 'PM' : 'AM';
          const hr12 = hr % 12 || 12;
          const formatted = `${hr12}:${mn} ${ampm}`;
          displayTimeStr = formatted;
          badgeText = formatted;
          arrivalSubtext = `Customer can come by: ~${formatted} at ${outpostName}`;
        } else {
          badgeText = 'Scheduled';
          arrivalSubtext = `Customer can come at custom time at ${outpostName}`;
        }
      }
      if (arrivalMode !== 'custom' && customTimeWrap) {
        customTimeWrap.style.display = 'none';
      }
    }

    const cartEtaCard = document.getElementById('cartEtaCard');
    const cartEtaBadge = document.getElementById('cartEtaBadge');
    const cartEtaTarget = document.getElementById('cartEtaTarget');
    if (cartEtaCard) cartEtaCard.style.display = 'flex';
    if (cartEtaBadge) cartEtaBadge.textContent = badgeText;
    if (cartEtaTarget) {
      cartEtaTarget.textContent = arrivalSubtext;
    }

    cartItemsContainer.innerHTML = state.cart.map(item => `
      <div class="cart-item-row" data-cart-id="${item.cartItemId || item.id}">
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <span class="cart-item-variant">${item.temperature || 'Iced'} · ${item.milk_option || 'Regular Milk'} · ${item.sweetness_level || '75% Less Sweet'}</span>
          ${item.custom_notes ? `<div style="font-size: 0.74rem; color: #DF9B64; font-style: italic; margin-top: 0.2rem;">"${item.custom_notes}"</div>` : ''}
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
      cartAuthHint.style.display = 'none';
    }

    if (checkoutBtn) {
      checkoutBtn.disabled = false;
      checkoutBtn.textContent = `Confirm Order Pickup (${formatPHP(grandTotal)})`;
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

  // Hero CTA "Order for Pickup" Wire to Cart Drawer
  const heroOrderPickupBtn = document.getElementById('heroOrderPickupBtn');
  if (heroOrderPickupBtn && cartDrawerOverlay) {
    heroOrderPickupBtn.addEventListener('click', () => {
      cartDrawerOverlay.classList.add('active');
      if (state.cart.length === 0) {
        showToast('Your pickup cart is ready. Select any drink below to customize and order!');
      }
    });
  }

  // Sticky Mobile Action Bar Cart Button
  const mobileBarCartBtn = document.getElementById('mobileBarCartBtn');
  if (mobileBarCartBtn && cartDrawerOverlay) {
    mobileBarCartBtn.addEventListener('click', () => {
      cartDrawerOverlay.classList.add('active');
    });
  }

  // Checkout order action (Frictionless Guest & Member Order Creation)
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', async () => {
      if (state.cart.length === 0) return;

      const custName = (orderCustomerName ? orderCustomerName.value.trim() : '') || (state.currentUser ? state.currentUser.name : '');
      const custPhone = (orderCustomerPhone ? orderCustomerPhone.value.trim() : '') || (state.currentUser && state.currentUser.phone ? state.currentUser.phone : '');
      const custNotes = (orderCustomerNotes ? orderCustomerNotes.value.trim() : '');

      if (!custName) {
        showToast('Please enter your full name for order pickup.');
        if (orderCustomerName) orderCustomerName.focus();
        return;
      }

      if (!custPhone || custPhone.length < 7) {
        showToast('Please enter a valid mobile number (+63).');
        if (orderCustomerPhone) orderCustomerPhone.focus();
        return;
      }
      
      const originalText = checkoutBtn.textContent;
      checkoutBtn.disabled = true;
      checkoutBtn.textContent = 'Confirming Order...';

      try {
        const arrivalSelect = document.getElementById('orderArrivalTime');
        const customTimeInput = document.getElementById('orderCustomTimeInput');
        const customTimeWrap = document.getElementById('orderCustomTimeWrap');
        let arrivalLabel = 'ASAP';
        if (arrivalSelect) {
          if (arrivalSelect.value === 'custom' && customTimeInput && customTimeInput.value) {
            arrivalLabel = customTimeInput.value;
          } else if (arrivalSelect.selectedOptions && arrivalSelect.selectedOptions[0]) {
            arrivalLabel = arrivalSelect.selectedOptions[0].textContent;
          }
        }
        const combinedNotes = `${custNotes || 'Putik'} (Arrival: ${arrivalLabel})`;

        const payload = {
          items: state.cart.map(i => ({
            id: i.id,
            quantity: i.qty,
            temperature: i.temperature || 'Iced',
            milk_option: i.milk_option || 'Regular Milk',
            sweetness_level: i.sweetness_level || 'Normal (100%)',
            custom_notes: i.custom_notes || '',
            size: 'Medium'
          })),
          customer_name: custName,
          customer_phone: custPhone,
          customer_notes: combinedNotes
        };

        const res = await fetch(getApiUrl('api/orders.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
          const ref = data.order_reference || (data.order && (data.order.order_code || data.order.order_reference)) || 'BC-ORDER';
          const totalDrinks = state.cart.reduce((sum, i) => sum + i.qty, 0);
          let estMins = '8–12';
          if (totalDrinks >= 2 && totalDrinks <= 3) estMins = '10–15';
          else if (totalDrinks >= 4 && totalDrinks <= 5) estMins = '15–20';
          else if (totalDrinks > 5) estMins = '20–25';
          showToast(`Order ${ref} confirmed! Customer can come by ~${displayTimeStr} at ${custNotes || 'Putik'}.`);
          state.cart = [];
          saveCart();
          if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
          if (orderCustomerNotes) {
            if (orderCustomerNotes.tagName === 'SELECT') {
              orderCustomerNotes.selectedIndex = 0;
            } else {
              orderCustomerNotes.value = '';
            }
          }
          if (arrivalSelect) arrivalSelect.selectedIndex = 0;
          if (customTimeInput) customTimeInput.value = '';
          if (customTimeWrap) customTimeWrap.style.display = 'none';
        } else {
          showToast(data.error || 'Unable to complete order. Please try again.');
        }
      } catch (err) {
        showToast('Order received! Thank you for ordering with BeCoffee.');
        state.cart = [];
        saveCart();
        if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
      } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = originalText;
      }
    });
  }

  // Outpost & Arrival Time switch listeners: dynamically recalculate ETA banner
  if (orderCustomerNotes) {
    orderCustomerNotes.addEventListener('change', () => {
      if (typeof updateCartUI === 'function') {
        updateCartUI();
      }
    });
  }

  if (orderArrivalTime) {
    orderArrivalTime.addEventListener('change', () => {
      if (typeof updateCartUI === 'function') {
        updateCartUI();
      }
    });
  }

  if (orderCustomTimeInput) {
    orderCustomTimeInput.addEventListener('input', () => {
      if (typeof updateCartUI === 'function') {
        updateCartUI();
      }
    });
  }

  // --- 11. Reservations & Workshops Modal ---
  function updateReservationEstimate() {
    if (!bookingTypeSelect || !bookingGuestsInput || !bookingPriceEstimate) return;
    const type = bookingTypeSelect.value;
    const isWorkshop = (type === 'cupping' || type === 'espresso-class');

    if (isWorkshop) {
      bookingGuestsInput.max = '6';
      if (parseInt(bookingGuestsInput.value, 10) > 6) {
        bookingGuestsInput.value = '6';
      }
    } else {
      bookingGuestsInput.max = '12';
    }

    const guests = Math.max(1, parseInt(bookingGuestsInput.value, 10) || 1);

    let price = 0;
    if (type === 'cupping') {
      price = 1850 * guests; // ₱1,850 per person
      bookingPriceEstimate.textContent = `${formatPHP(price)} (₱1,850 × ${guests} guests · Max 6 pax)`;
    } else if (type === 'espresso-class') {
      price = 2400 * guests; // ₱2,400 per person
      bookingPriceEstimate.textContent = `${formatPHP(price)} (₱2,400 × ${guests} guests · Max 6 pax)`;
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
      const reservePrivacyConsent = document.getElementById('reservePrivacyConsent');
      if (reservePrivacyConsent && !reservePrivacyConsent.checked) {
        showToast('Please accept the privacy consent to complete your reservation.');
        return;
      }

      const bookingType = document.getElementById('bookingType').value;
      const guests = parseInt(document.getElementById('bookingGuests').value, 10);
      if ((bookingType === 'cupping' || bookingType === 'espresso-class') && guests > 6) {
        showToast('Cupping and barista workshops are limited to a maximum of 6 participants.');
        document.getElementById('bookingGuests').value = 6;
        updateReservationEstimate();
        return;
      }

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

  // --- 12. Philippine Outpost Tabs (Zamboanga vs Baliwasan) ---
  const locationsData = {
    putik: {
      name: 'Zamboanga Flagship Sanctuary',
      badge: 'Zamboanga City Flagship & Slow Bar',
      address: 'RCDAO Village, MCLL Highway, Putik, Zamboanga City, 7000 Zamboanga Peninsula',
      landmark: 'Along MCLL Highway, beside RCDAO Village entrance (250m before Putik Barangay Hall, across Caltex Station)',
      hours: 'Mon – Sun: 7:00 AM – 10:00 PM PHT',
      phones: [
        { label: '+63 62 991 2633', tel: '+63629912633' },
        { label: '+63 917 555 2333', tel: '+639175552333' }
      ],
      features: 'High-speed 500Mbps Fiber, Outdoor Garden Seating, Slow Bar & Siphon, Pour-over Flights',
      image: 'images/locations/putik_flagship.webp',
      directionsUrl: 'https://maps.app.goo.gl/RHdya1FR7aJFRbDx5',
      mapEmbedUrl: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15840.40788647008!2d122.0950!3d6.9380!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325041f0b0946b07%3A0x6a2c91834167e4ea!2sPutik%2C%20Zamboanga%2C%20Zamboanga%20del%20Sur!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph'
    },
    baliwasan: {
      name: 'Baliwasan Roastery & Slow Bar',
      badge: 'Zamboanga Peninsula Roastery & Brew Lab',
      address: 'San Jose Gusu & Baliwasan Chico Road, Zamboanga City, 7000',
      landmark: 'Near Western Mindanao State University (WMSU) & Baliwasan Seaside, 5 mins from Zamboanga International Airport',
      hours: 'Wed – Sun: 8:00 AM – 8:00 PM PHT (Closed Mon & Tue)',
      phones: [
        { label: '+63 62 991 8888', tel: '+63629918888' },
        { label: '+63 919 888 2333', tel: '+639198882333' }
      ],
      features: 'Direct Cupping Pavilion, Alfresco Seating, Artisan Roast Flights, High-Speed Fiber',
      image: 'images/locations/baliwasan_roastery.webp',
      directionsUrl: 'https://maps.app.goo.gl/yeavai9XSWvr7YhN8',
      mapEmbedUrl: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15840.852445831962!2d122.0620!3d6.9180!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325041a7d18bc305%3A0x4eb266395b7e9bf9!2sBaliwasan%2C%20Zamboanga%2C%20Zamboanga%20del%20Sur!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph'
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
      const locLandmarkText = document.getElementById('locLandmarkText');
      if (locLandmarkText && data.landmark) {
        locLandmarkText.innerHTML = '<strong>Landmark:</strong> ' + data.landmark;
      }
      if (locHoursText) locHoursText.textContent = data.hours;
      if (locPhoneWrap && data.phones) {
        locPhoneWrap.innerHTML = data.phones.map(p => 
          `<a href="tel:${p.tel}" class="phone-link">${p.label}</a>`
        ).join(' <span class="phone-divider">/</span> ');
      } else if (locPhoneText && data.phone) {
        locPhoneText.textContent = data.phone;
      }
      if (locFeaturesText) locFeaturesText.textContent = data.features;
      if (locDirectionsBtn && data.directionsUrl) {
        locDirectionsBtn.href = data.directionsUrl;
      }
      const locMapIframe = document.getElementById('locMapIframe');
      if (locMapIframe && data.mapEmbedUrl) {
        locMapIframe.src = data.mapEmbedUrl;
      }
      const mapOpenFullBtn = document.getElementById('mapOpenFullBtn');
      if (mapOpenFullBtn && data.directionsUrl) {
        mapOpenFullBtn.href = data.directionsUrl;
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

  // --- 13.5. Staff POS Station Bar & URL Notices ---
  function checkUrlNotices() {
    const params = new URLSearchParams(window.location.search);
    const notice = params.get('notice');
    const error = params.get('error');

    if (notice === 'customer_restricted') {
      showToast('Manager Studio is restricted to administrator accounts.');
    } else if (notice === 'staff_restricted') {
      showToast('Sales & Pricing settings are restricted to Store Management.');
    } else if (notice === 'staff_only') {
      showToast('Kitchen Display (KDS) is restricted to Staff and Management.');
    } else if (error === 'unauthorized') {
      showToast('Authentication required. Please sign in.');
    }
  }

  function renderStaffStationBar() {
    let existingBar = document.getElementById('staffPosStationBar');
    const isStaff = (state.currentUser && state.currentUser.role === 'staff') || window.location.search.includes('mode=staff');
    const isAdmin = state.currentUser && (state.currentUser.role === 'admin' || state.currentUser.role === 'superadmin');

    if (!isStaff && !isAdmin) {
      if (existingBar) existingBar.remove();
      return;
    }

    if (!existingBar) {
      existingBar = document.createElement('div');
      existingBar.id = 'staffPosStationBar';
      existingBar.className = 'staff-pos-station-bar';
      existingBar.style.cssText = `
        background: rgba(21, 16, 13, 0.98);
        border-bottom: 2px solid ${isStaff ? '#10B981' : '#F59E0B'};
        padding: 0.6rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(12px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        flex-wrap: wrap;
        gap: 0.75rem;
      `;
      document.body.prepend(existingBar);
    }

    const userName = state.currentUser ? state.currentUser.name : 'Counter Staff';
    const isSuper = state.currentUser && state.currentUser.role === 'superadmin';
    const roleTitle = isStaff ? 'STAFF POS MODE' : (isSuper ? 'SUPERADMIN' : 'ADMIN POS MODE');
    const roleBg = isStaff ? 'rgba(16, 185, 129, 0.2)' : (isSuper ? 'rgba(124, 58, 237, 0.25)' : 'rgba(245, 158, 11, 0.2)');
    const roleBorder = isStaff ? '#10B981' : (isSuper ? '#8B5CF6' : '#F59E0B');
    const roleColor = isStaff ? '#6EE7B7' : (isSuper ? '#DDD6FE' : '#FCD34D');
    const totalCount = state.cart.reduce((sum, i) => sum + i.qty, 0);

    existingBar.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <span style="background: ${roleBg}; border: 1px solid ${roleBorder}; color: ${roleColor}; padding: 0.25rem 0.65rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">
          ☕ ${roleTitle}
        </span>
        <span style="font-size: 0.85rem; color: #F5EBE1;">
          Counter: <strong>${userName}</strong>
        </span>
      </div>
      <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
        ${isAdmin ? `<a href="${isSuper ? 'admin.php?view=developer' : 'admin.php'}" style="text-decoration: none; font-size: 0.8rem; background: rgba(226, 135, 67, 0.25); border: 1px solid rgba(226, 135, 67, 0.5); color: #FDBA74; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">📊 Admin Studio</a>` : ''}
        <a href="kds.php" target="_blank" style="text-decoration: none; font-size: 0.8rem; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93C5FD; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
          📋 Kitchen KDS
        </a>
        <button type="button" id="staffQuickCartBtn" style="cursor: pointer; font-size: 0.8rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: #FFF; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 600;">
          🛒 Order Cart (<span id="staffBarCartCount">${totalCount}</span>)
        </button>
        <button type="button" id="staffQuickSignOutBtn" style="cursor: pointer; font-size: 0.8rem; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #FCA5A5; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 600;">
          Sign Out
        </button>
      </div>
    `;

    const cartBtn = document.getElementById('staffQuickCartBtn');
    if (cartBtn && cartDrawerOverlay) {
      cartBtn.onclick = () => cartDrawerOverlay.classList.add('active');
    }
    const signOutBtn = document.getElementById('staffQuickSignOutBtn');
    if (signOutBtn) {
      signOutBtn.onclick = async () => {
        try {
          await fetch(getApiUrl('api/auth.php?action=logout'), { method: 'POST', credentials: 'include' });
        } catch(e){}
        localStorage.removeItem('becoffee_demo_session');
        window.location.href = 'api/auth.php?action=logout';
      };
    }
  }

  // --- 14. User Authentication & Account Management ---
  function updateAuthUI() {
    renderStaffStationBar();
    // Permanently purge any legacy guest sign in buttons from DOM
    const legacyGuestBtn = document.getElementById('mobileNavSignInBtn');
    if (legacyGuestBtn && legacyGuestBtn.parentNode) legacyGuestBtn.parentNode.removeChild(legacyGuestBtn);
    const legacyGuestWrap = document.getElementById('mobileAuthGuest');
    if (legacyGuestWrap && legacyGuestWrap.parentNode) legacyGuestWrap.parentNode.removeChild(legacyGuestWrap);

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
      

      if (mobileAccountSettingsItem) {
        mobileAccountSettingsItem.classList.add('is-auth');
      }

      const mobileNavAuth = document.getElementById('mobileNavAuth');
      const mobileAuthName = document.getElementById('mobileAuthName');
      const mobileAuthAvatar = document.getElementById('mobileAuthAvatar');

      if (mobileNavAuth) {
        mobileNavAuth.classList.add('is-active');
        mobileNavAuth.style.display = 'block';
      }
      if (mobileAuthName) mobileAuthName.textContent = state.currentUser.name;
      if (mobileAuthAvatar) {
        const parts = state.currentUser.name.trim().split(/\s+/);
        mobileAuthAvatar.textContent = parts.length > 1
          ? (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
          : parts[0].charAt(0).toUpperCase();
      }

      // Pre-fill reservation and order forms if inputs are empty
      const bookingName = document.getElementById('bookingName');
      const bookingPhone = document.getElementById('bookingPhone');
      if (bookingName && !bookingName.value) bookingName.value = state.currentUser.name;
      if (bookingPhone && !bookingPhone.value && state.currentUser.phone) bookingPhone.value = state.currentUser.phone;

      if (orderCustomerName && !orderCustomerName.value) orderCustomerName.value = state.currentUser.name;
      if (orderCustomerPhone && !orderCustomerPhone.value && state.currentUser.phone) orderCustomerPhone.value = state.currentUser.phone;
      if (checkoutModeLabel) checkoutModeLabel.textContent = `Member: ${firstName}`;
    } else {
      if (authOpenBtn) authOpenBtn.style.display = 'inline-flex';
      if (userProfilePill) userProfilePill.style.display = 'none';

      if (mobileNavAuth) {
        mobileNavAuth.classList.remove('is-active');
        mobileNavAuth.style.display = 'none';
      }

      if (mobileAccountSettingsItem) {
        mobileAccountSettingsItem.classList.remove('is-auth');
      }
      if (checkoutModeLabel) {
        checkoutModeLabel.textContent = 'No account required';
      }
    }

    // Keep cart checkout button & hints updated with auth status
    if (typeof updateCartUI === 'function') {
      updateCartUI();
    }
  }

  async function checkAuthStatus() {
    if (window.location.protocol === 'file:' && !window.BECOFFEE_DEMO_ACTIVE) {
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

  function hideFloatingMobileBars() {
    const mobileBar = document.querySelector('.mobile-action-bar');
    if (mobileBar) {
      mobileBar.style.setProperty('display', 'none', 'important');
      mobileBar.style.setProperty('visibility', 'hidden', 'important');
    }
    const demoBadge = document.getElementById('becoffeeDemoBadge');
    if (demoBadge) {
      demoBadge.style.setProperty('display', 'none', 'important');
      demoBadge.style.setProperty('visibility', 'hidden', 'important');
    }
  }

  function restoreFloatingMobileBars() {
    const anyModal = document.querySelector('.modal-overlay.active, .cart-drawer-overlay.active');
    if (!anyModal) {
      const mobileBar = document.querySelector('.mobile-action-bar');
      if (mobileBar) {
        mobileBar.style.removeProperty('display');
        mobileBar.style.removeProperty('visibility');
      }
      const demoBadge = document.getElementById('becoffeeDemoBadge');
      if (demoBadge) {
        demoBadge.style.removeProperty('display');
        demoBadge.style.removeProperty('visibility');
      }
    }
  }

  function openAuthModal(defaultTab = 'signin') {
    switchAuthTab(defaultTab);
    if (authModalOverlay) authModalOverlay.classList.add('active');
    document.body.classList.add('modal-open');
    hideFloatingMobileBars();
  }

  function closeAuthModal() {
    if (authModalOverlay) authModalOverlay.classList.remove('active');
    document.body.classList.remove('modal-open');
    restoreFloatingMobileBars();
    setAuthAlert('');
    if (signInForm) signInForm.reset();
    if (registerForm) registerForm.reset();
  }

  // Open / Close Auth Modal Events
  if (authOpenBtn) {
    authOpenBtn.addEventListener('click', () => openAuthModal('signin'));
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

      if (window.location.protocol === 'file:' && !window.BECOFFEE_DEMO_ACTIVE) {
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

        const contentType = res.headers.get('content-type') || '';
        let data = null;
        if (contentType.includes('application/json')) {
          data = await res.json();
        } else {
          if (res.status === 404) {
            setAuthAlert('Backend API not found (404). This hosting environment does not support PHP.');
          } else {
            setAuthAlert(`Server responded with status ${res.status}. Check PHP/MySQL server logs.`);
          }
          return;
        }

        if (res.ok && (data.success || data.authenticated)) {
          state.currentUser = data.user;
          setAuthAlert('Success! Welcome back.', 'success');
          showToast(`Welcome back, ${data.user.name}!`);

          // Sync session to localStorage for seamless studio authorization
          if (data.user) {
            localStorage.setItem('becoffee_demo_session', JSON.stringify(data.user));
          }

          try {
            updateAuthUI();
          } catch (uiErr) {
            console.warn('UI update notice after login:', uiErr);
          }

          // Role-specific redirects upon sign-in
          if (data.user && (data.user.role === 'admin' || data.user.role === 'superadmin')) {
            const isSuper = data.user.role === 'superadmin';
            const roleTitle = isSuper ? 'SuperAdmin (Developer)' : 'Administrator';
            const targetUrl = isSuper ? 'admin.php?view=developer' : 'admin.php';
            setAuthAlert(`${roleTitle} verified. Leading to Studio...`, 'success');
            showToast(`${roleTitle} verified! Redirecting...`);
            setTimeout(() => {
              window.location.href = targetUrl;
            }, 600);
            return;
          }

          if (data.user && data.user.role === 'staff') {
            setAuthAlert('Kitchen Staff verified. Redirecting to Kitchen Screen (KDS)...', 'success');
            showToast('Staff verified! Loading Kitchen Display System...');
            setTimeout(() => {
              closeAuthModal();
              window.location.href = 'kds.php';
            }, 600);
            return;
          }

          if (data.user && data.user.role === 'customer') {
            setAuthAlert('Welcome! Redirecting to Take Out Order...', 'success');
            showToast('Welcome! Loading Take Out ordering...');
            setTimeout(() => {
              closeAuthModal();
              window.location.href = 'takeout.php';
            }, 600);
            return;
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
          setAuthAlert(`Cannot reach API at ${getApiUrl('api/auth.php?action=login')}. Verify your host supports PHP and MySQL.`);
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

      const regPrivacyConsent = document.getElementById('regPrivacyConsent');
      if (regPrivacyConsent && !regPrivacyConsent.checked) {
        setAuthAlert('Please agree to the BeCoffee Privacy Notice (RA 10173) to create an account.');
        return;
      }

      const originalText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
      }

      if (window.location.protocol === 'file:' && !window.BECOFFEE_DEMO_ACTIVE) {
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

        const contentType = res.headers.get('content-type') || '';
        let data = null;
        if (contentType.includes('application/json')) {
          data = await res.json();
        } else {
          if (res.status === 404) {
            setAuthAlert('Backend API not found (404). This hosting environment does not support PHP.');
          } else {
            setAuthAlert(`Server responded with status ${res.status}. Check PHP/MySQL server logs.`);
          }
          return;
        }

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
          setAuthAlert(`Cannot reach registration API at ${getApiUrl('api/auth.php?action=register')}. Check server configuration.`);
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
      try { localStorage.removeItem(DEMO_SESSION_KEY); } catch (e) {}
      updateAuthUI();
      showToast(data.message || 'Signed out successfully.');
    } catch (err) {
      state.currentUser = null;
      try { localStorage.removeItem(DEMO_SESSION_KEY); } catch (e) {}
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

  // --- 17. Admin Menu CMS ---
  // Decoupled to standalone authenticated studio: admin.php & js/admin.js
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
    
    const role = state.currentUser.role || 'customer';
    const roleLabels = {
      superadmin: 'SuperAdmin (Developer)',
      admin: 'Administrator',
      staff: 'Store Staff',
      customer: 'Customer Member'
    };
    if (accountRolePill) {
      accountRolePill.textContent = roleLabels[role] || 'Customer Member';
      accountRolePill.className = `account-role-pill ${role}`;
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

  // Mobile Nav Auth & Sign Out Handlers
  const mobileNavLogoutBtn = document.getElementById('mobileNavLogoutBtn');
  const mobileNavSettingsBtn = document.getElementById('mobileNavSettingsBtn');

  if (mobileNavLogoutBtn) {
    mobileNavLogoutBtn.addEventListener('click', () => {
      if (navLinks) navLinks.classList.remove('active');
      if (mobileToggle) mobileToggle.classList.remove('active');
      handleLogout();
    });
  }

  if (mobileNavSettingsBtn) {
    mobileNavSettingsBtn.addEventListener('click', () => {
      if (navLinks) navLinks.classList.remove('active');
      if (mobileToggle) mobileToggle.classList.remove('active');
      openAccountModal();
    });
  }

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

  // --- 18. Philippine Data Privacy Notice Modal Handlers (RA 10173) ---
  const privacyModalOverlay = document.getElementById('privacyModalOverlay');
  const closePrivacyBtn = document.getElementById('closePrivacyBtn');
  const ackPrivacyBtn = document.getElementById('ackPrivacyBtn');
  const openPrivacyLinks = document.querySelectorAll('.open-privacy-link');

  function openPrivacyModal(e) {
    if (e) e.preventDefault();
    if (privacyModalOverlay) {
      privacyModalOverlay.classList.add('active');
    }
  }

  function closePrivacyModal() {
    if (privacyModalOverlay) {
      privacyModalOverlay.classList.remove('active');
    }
  }

  openPrivacyLinks.forEach(link => {
    link.addEventListener('click', openPrivacyModal);
  });

  if (closePrivacyBtn) {
    closePrivacyBtn.addEventListener('click', closePrivacyModal);
  }
  if (ackPrivacyBtn) {
    ackPrivacyBtn.addEventListener('click', closePrivacyModal);
  }
  if (privacyModalOverlay) {
    privacyModalOverlay.addEventListener('click', (e) => {
      if (e.target === privacyModalOverlay) {
        closePrivacyModal();
      }
    });
  }

  // Global Escape key listener to close active overlays
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closePrivacyModal();
      closeAuthModal();
      if (reserveModalOverlay) reserveModalOverlay.classList.remove('active');
      if (cartDrawerOverlay) cartDrawerOverlay.classList.remove('active');
      if (orderModalOverlay) orderModalOverlay.classList.remove('active');
      if (accountSettingsModalOverlay) accountSettingsModalOverlay.classList.remove('active');
    }
  });

  // --- 16. Initial Startup Sequence ---
  renderMenu();
  updateCartUI();
  checkUrlNotices();
  renderStaffStationBar();
  checkAuthStatus();
  loadMenuFromAPI();
});
