/**
 * BeCoffee — Dedicated Admin CMS & Roastery Operations Controller
 * Authenticated Studio with Session Guard & REST API Integration
 */

document.addEventListener('DOMContentLoaded', () => {
  const isDemo = window.location.hostname.includes('netlify.app') ||
                 window.location.hostname.includes('github.io') ||
                 window.location.hostname.includes('vercel.app') ||
                 window.location.protocol === 'file:' ||
                 window.location.search.includes('demo=1') ||
                 localStorage.getItem('becoffee_demo_active') === 'true';

  const adminAuthSection = document.getElementById('adminAuthSection');
  const adminStudioSection = document.getElementById('adminStudioSection');
  const adminLoginForm = document.getElementById('adminLoginForm');
  const adminLoginAlert = document.getElementById('adminLoginAlert');
  const adminLogoutBtn = document.getElementById('adminLogoutBtn');

  const adminTotalCount = document.getElementById('adminTotalCount');
  const adminActiveCount = document.getElementById('adminActiveCount');
  const adminUnavailableCount = document.getElementById('adminUnavailableCount');
  const adminSearchInput = document.getElementById('adminSearchInput');
  const adminCategoryFilters = document.getElementById('adminCategoryFilters');
  const adminItemsContainer = document.getElementById('adminItemsContainer');
  const adminAddNewBtn = document.getElementById('adminAddNewBtn');

  const adminProductEditModal = document.getElementById('adminProductEditModal');
  const closeAdminEditModalBtn = document.getElementById('closeAdminEditModalBtn');
  const cancelAdminEditBtn = document.getElementById('cancelAdminEditBtn');
  const adminProductEditForm = document.getElementById('adminProductEditForm');
  const adminProdModalTitle = document.getElementById('adminProdModalTitle');

  let adminMenuCatalog = [];
  let currentCategory = 'all';
  let searchQuery = '';

  function getApiUrl(endpoint) {
    if (isDemo) return endpoint;
    if (window.location.protocol === 'file:') return `http://localhost/BeCoffee/${endpoint}`;
    if (window.location.port && window.location.port !== '80' && window.location.port !== '443') {
      return `http://localhost/BeCoffee/${endpoint}`;
    }
    return endpoint;
  }

  function formatPHP(amount) {
    return `₱${Number(amount || 0).toLocaleString('en-PH')}`;
  }

  function showAlert(msg, type = 'error') {
    if (!adminLoginAlert) return;
    adminLoginAlert.style.display = 'block';
    adminLoginAlert.style.background = type === 'error' ? 'rgba(239, 68, 68, 0.15)' : 'rgba(34, 197, 94, 0.15)';
    adminLoginAlert.style.border = `1px solid ${type === 'error' ? '#EF4444' : '#22C55E'}`;
    adminLoginAlert.style.color = type === 'error' ? '#FCA5A5' : '#86EFAC';
    adminLoginAlert.textContent = msg;
  }

  // --- 1. Session Verification ---
  async function checkAdminSession() {
    try {
      // 1. Check local session storage (shared from index.html sign-in)
      const localSession = JSON.parse(localStorage.getItem('becoffee_demo_session') || 'null');
      if (localSession && localSession.role === 'admin') {
        showStudio();
        return;
      }

      // 2. Query active server session
      const res = await fetch(getApiUrl('api/auth.php?action=me'), { credentials: 'include' });
      const data = await res.json();
      if (res.ok && (data.success || data.authenticated) && data.user && data.user.role === 'admin') {
        localStorage.setItem('becoffee_demo_session', JSON.stringify(data.user));
        showStudio();
      } else {
        showAuth();
      }
    } catch (e) {
      showAuth();
    }
  }

  function showAuth() {
    if (adminAuthSection) adminAuthSection.style.display = 'block';
    if (adminStudioSection) adminStudioSection.style.display = 'none';
    if (adminLogoutBtn) adminLogoutBtn.style.display = 'none';
  }

  function showStudio() {
    if (adminAuthSection) adminAuthSection.style.display = 'none';
    if (adminStudioSection) adminStudioSection.style.display = 'block';
    if (adminLogoutBtn) adminLogoutBtn.style.display = 'inline-block';
    loadAdminCatalog();
  }

  // --- 2. Admin Login Form Submission ---
  if (adminLoginForm) {
    adminLoginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('adminEmail').value.trim();
      const password = document.getElementById('adminPassword').value;

      if (isDemo) {
        if (email.toLowerCase() === 'admin@becoffee.ph' && (password === 'AdminBeCoffee2026!' || password === 'admin123')) {
          const adminUser = { id: 1, name: 'BeCoffee Administrator', email: 'admin@becoffee.ph', role: 'admin' };
          localStorage.setItem('becoffee_demo_session', JSON.stringify(adminUser));
          showStudio();
          return;
        }
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
          if (data.user.role !== 'admin') {
            showAlert('Access denied. This account does not possess administrator privileges.');
            return;
          }
          showStudio();
        } else {
          showAlert(data.error || 'Authentication failed. Please verify credentials.');
        }
      } catch (err) {
        showAlert('Network error connecting to authentication server.');
      }
    });
  }

  // --- 3. Logout ---
  if (adminLogoutBtn) {
    adminLogoutBtn.addEventListener('click', async () => {
      if (isDemo) {
        localStorage.removeItem('becoffee_demo_session');
      } else {
        await fetch(getApiUrl('api/auth.php?action=logout'), { method: 'POST', credentials: 'include' });
      }
      showAuth();
    });
  }

  // --- 4. Catalog Fetch & Render ---
  async function loadAdminCatalog() {
    if (adminItemsContainer) {
      adminItemsContainer.innerHTML = '<div class="cms-loading-state">Loading active catalog items...</div>';
    }

    try {
      if (isDemo) {
        const stored = localStorage.getItem('becoffee_demo_menu');
        adminMenuCatalog = stored ? JSON.parse(stored) : (window.DEFAULT_BECOFFEE_MENU || []);
      } else {
        const res = await fetch(getApiUrl('api/admin_menu.php?action=list'), { credentials: 'include' });
        const data = await res.json();
        if (res.ok && data.success && Array.isArray(data.items)) {
          adminMenuCatalog = data.items;
        } else {
          adminMenuCatalog = window.DEFAULT_BECOFFEE_MENU || [];
        }
      }
    } catch (e) {
      adminMenuCatalog = window.DEFAULT_BECOFFEE_MENU || [];
    }

    renderCatalog();
  }

  function renderCatalog() {
    const filtered = adminMenuCatalog.filter(item => {
      const catMatch = currentCategory === 'all' || item.category === currentCategory || String(item.category_id) === currentCategory;
      const searchMatch = !searchQuery || (item.name || '').toLowerCase().includes(searchQuery) ||
                          (item.id || '').toLowerCase().includes(searchQuery) ||
                          (item.origin || '').toLowerCase().includes(searchQuery);
      return catMatch && searchMatch;
    });

    // Update KPI counters
    const total = adminMenuCatalog.length;
    const active = adminMenuCatalog.filter(i => i.is_available !== false && i.isAvailable !== false).length;
    const unavailable = total - active;

    if (adminTotalCount) adminTotalCount.textContent = total;
    if (adminActiveCount) adminActiveCount.textContent = active;
    if (adminUnavailableCount) adminUnavailableCount.textContent = unavailable;

    if (!adminItemsContainer) return;

    if (filtered.length === 0) {
      adminItemsContainer.innerHTML = '<div class="cms-loading-state">No matching menu products found.</div>';
      return;
    }

    adminItemsContainer.innerHTML = `
      <table class="cms-table" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left; color: #DF9B64; font-size: 0.82rem;">
            <th style="padding: 0.75rem 1rem;">Product</th>
            <th style="padding: 0.75rem 1rem;">Category</th>
            <th style="padding: 0.75rem 1rem;">Base Price</th>
            <th style="padding: 0.75rem 1rem;">Availability</th>
            <th style="padding: 0.75rem 1rem; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          ${filtered.map(item => {
            const isAvail = item.is_available !== false && item.isAvailable !== false;
            return `
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.85rem;">
                  <img src="${item.image_url || item.image}" alt="${item.name}" style="width: 42px; height: 42px; border-radius: 8px; object-fit: cover; background: #222;" onerror="this.src='images/menu/hc-classic.webp'">
                  <div>
                    <div style="font-weight: 600; color: #FFF;">${item.name}</div>
                    <div style="font-size: 0.76rem; color: var(--color-text-secondary);">${item.origin || item.elevation || ''}</div>
                  </div>
                </td>
                <td style="padding: 0.75rem 1rem; font-size: 0.84rem; text-transform: capitalize;">${item.category || item.category_name || 'House Coffee'}</td>
                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #DF9B64;">${formatPHP(item.price || item.priceIcedM)}</td>
                <td style="padding: 0.75rem 1rem;">
                  <button type="button" class="btn btn-sm ${isAvail ? 'btn-secondary' : 'btn-outline'}" data-toggle-id="${item.id}" data-current="${isAvail}" style="font-size: 0.78rem; padding: 0.3rem 0.65rem;">
                    ${isAvail ? '🟢 In Stock' : '🔴 Sold Out'}
                  </button>
                </td>
                <td style="padding: 0.75rem 1rem; text-align: right;">
                  <button type="button" class="btn btn-sm btn-crema" data-edit-id="${item.id}" style="font-size: 0.78rem; padding: 0.3rem 0.65rem;">
                    Edit
                  </button>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    `;

    // Bind Toggle Availability
    adminItemsContainer.querySelectorAll('[data-toggle-id]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-toggle-id');
        const nextAvail = btn.getAttribute('data-current') !== 'true';
        btn.disabled = true;

        if (isDemo) {
          adminMenuCatalog = adminMenuCatalog.map(it => it.id === id ? { ...it, isAvailable: nextAvail, is_available: nextAvail } : it);
          localStorage.setItem('becoffee_demo_menu', JSON.stringify(adminMenuCatalog));
          renderCatalog();
        } else {
          await fetch(getApiUrl('api/admin_menu.php?action=toggle_availability'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ item_id: id, is_available: nextAvail ? 1 : 0 })
          });
          loadAdminCatalog();
        }
      });
    });

    // Bind Edit Modal Opener
    adminItemsContainer.querySelectorAll('[data-edit-id]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-edit-id');
        const item = adminMenuCatalog.find(i => i.id === id);
        if (item) openEditModal(item);
      });
    });
  }

  // --- 5. Search & Filters ---
  if (adminSearchInput) {
    adminSearchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.toLowerCase().trim();
      renderCatalog();
    });
  }

  if (adminCategoryFilters) {
    adminCategoryFilters.querySelectorAll('.cms-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        adminCategoryFilters.querySelectorAll('.cms-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentCategory = tab.getAttribute('data-cat');
        renderCatalog();
      });
    });
  }

  // --- 6. Edit / Add Product Modal ---
  function openEditModal(item = null) {
    if (!adminProductEditModal) return;
    adminProductEditModal.classList.add('active');

    if (item) {
      if (adminProdModalTitle) adminProdModalTitle.textContent = 'Edit Product: ' + item.name;
      document.getElementById('adminEditOriginalId').value = item.id;
      document.getElementById('adminEditName').value = item.name || '';
      document.getElementById('adminEditSlug').value = item.id || '';
      document.getElementById('adminEditPrice').value = item.price || 120;
      document.getElementById('adminEditPriceIcedM').value = item.priceIcedM || item.price || 120;
      document.getElementById('adminEditPriceIcedL').value = item.priceIcedL || 140;
      document.getElementById('adminEditPriceHot').value = item.priceHot || 120;
      document.getElementById('adminEditOrigin').value = item.origin || '';
      document.getElementById('adminEditElevation').value = item.elevation || 'Hot / Iced';
      document.getElementById('adminEditImage').value = item.image_url || item.image || 'images/menu/hc-classic.webp';
      document.getElementById('adminEditDesc').value = item.description || '';
      document.getElementById('adminEditAvailable').checked = item.is_available !== false && item.isAvailable !== false;
      document.getElementById('adminEditBestseller').checked = Boolean(item.is_bestseller || item.isBestseller);
    } else {
      if (adminProdModalTitle) adminProdModalTitle.textContent = 'Add New Product';
      adminProductEditForm.reset();
      document.getElementById('adminEditOriginalId').value = '';
      document.getElementById('adminEditAvailable').checked = true;
      document.getElementById('adminEditImage').value = 'images/menu/hc-classic.webp';
    }
    const adminImageFileInput = document.getElementById('adminImageFileInput');
    const adminCompressStatus = document.getElementById('adminCompressStatus');
    if (adminImageFileInput) adminImageFileInput.value = '';
    if (adminCompressStatus) adminCompressStatus.textContent = '';
  }

  // Automatic Client-Side Photo Compression to WebP (<80KB)
  const adminImageFileInput = document.getElementById('adminImageFileInput');
  const adminCompressStatus = document.getElementById('adminCompressStatus');

  if (adminImageFileInput) {
    adminImageFileInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;

      if (adminCompressStatus) {
        adminCompressStatus.textContent = `Optimizing ${file.name} (${(file.size / (1024 * 1024)).toFixed(1)} MB)...`;
        adminCompressStatus.style.color = 'var(--color-crema-gold)';
      }

      const reader = new FileReader();
      reader.onload = (evt) => {
        const img = new Image();
        img.onload = () => {
          const maxDim = 800;
          let w = img.width;
          let h = img.height;
          if (w > maxDim || h > maxDim) {
            if (w > h) {
              h = Math.round(h * (maxDim / w));
              w = maxDim;
            } else {
              w = Math.round(w * (maxDim / h));
              h = maxDim;
            }
          }

          const canvas = document.createElement('canvas');
          canvas.width = w;
          canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, w, h);

          const compressedData = canvas.toDataURL('image/webp', 0.82);
          const sizeKb = Math.round((compressedData.length * 3 / 4) / 1024);

          const imageInput = document.getElementById('adminEditImage');
          if (imageInput) imageInput.value = compressedData;

          if (adminCompressStatus) {
            adminCompressStatus.textContent = `✓ Auto-compressed to WebP (${w}×${h}px · ~${sizeKb} KB)`;
            adminCompressStatus.style.color = '#78A868';
          }
        };
        img.src = evt.target.result;
      };
      reader.readAsDataURL(file);
    });
  }

  if (adminAddNewBtn) {
    adminAddNewBtn.addEventListener('click', () => openEditModal(null));
  }

  if (closeAdminEditModalBtn) {
    closeAdminEditModalBtn.addEventListener('click', () => adminProductEditModal.classList.remove('active'));
  }
  if (cancelAdminEditBtn) {
    cancelAdminEditBtn.addEventListener('click', () => adminProductEditModal.classList.remove('active'));
  }

  if (adminProductEditForm) {
    adminProductEditForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const origId = document.getElementById('adminEditOriginalId').value;
      const slug = document.getElementById('adminEditSlug').value.trim() || ('item-' + Date.now());
      const payload = {
        id: origId || slug,
        category_id: parseInt(document.getElementById('adminEditCategory').value, 10),
        name: document.getElementById('adminEditName').value.trim(),
        price: parseFloat(document.getElementById('adminEditPrice').value),
        priceIcedM: parseFloat(document.getElementById('adminEditPriceIcedM').value),
        priceIcedL: parseFloat(document.getElementById('adminEditPriceIcedL').value) || null,
        priceHot: parseFloat(document.getElementById('adminEditPriceHot').value) || null,
        origin: document.getElementById('adminEditOrigin').value.trim(),
        elevation: document.getElementById('adminEditElevation').value.trim(),
        image: document.getElementById('adminEditImage').value.trim(),
        description: document.getElementById('adminEditDesc').value.trim(),
        is_available: document.getElementById('adminEditAvailable').checked ? 1 : 0,
        is_bestseller: document.getElementById('adminEditBestseller').checked ? 1 : 0
      };

      if (isDemo) {
        const idx = adminMenuCatalog.findIndex(i => i.id === payload.id);
        if (idx >= 0) adminMenuCatalog[idx] = { ...adminMenuCatalog[idx], ...payload };
        else adminMenuCatalog.unshift(payload);
        localStorage.setItem('becoffee_demo_menu', JSON.stringify(adminMenuCatalog));
        adminProductEditModal.classList.remove('active');
        renderCatalog();
      } else {
        await fetch(getApiUrl('api/admin_menu.php?action=save_item'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify(payload)
        });
        adminProductEditModal.classList.remove('active');
        loadAdminCatalog();
      }
    });
  }

  // Check session on initial load
  checkAdminSession();
});
