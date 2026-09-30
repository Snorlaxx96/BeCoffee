/**
 * Escobar Cafe / BeCoffee - Unified System Modal Dialog Engine
 * Replaces browser-native alert(), confirm(), and prompt() with branded,
 * dark-roast styled accessible modal dialogs.
 */
(function(window) {
  'use strict';

  var overlay = null;
  var box = null;
  var currentResolver = null;
  var previousActiveElement = null;

  var ICONS = {
    info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E28743" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
    success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
    warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
    danger: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
    prompt: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>'
  };

  function ensureElements() {
    if (overlay && document.body.contains(overlay)) return;

    overlay = document.createElement('div');
    overlay.className = 'sys-modal-overlay';
    overlay.id = 'sysModalOverlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-hidden', 'true');

    box = document.createElement('div');
    box.className = 'sys-modal-box';
    overlay.appendChild(box);

    document.body.appendChild(overlay);

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) {
        var cancelBtn = overlay.querySelector('.sys-btn-cancel');
        if (cancelBtn) {
          cancelBtn.click();
        } else {
          var confirmBtn = overlay.querySelector('.sys-btn-confirm');
          if (confirmBtn) confirmBtn.click();
        }
      }
    });

    document.addEventListener('keydown', function(e) {
      if (!overlay || !overlay.classList.contains('active')) return;
      if (e.key === 'Escape') {
        e.preventDefault();
        var cancelBtn = overlay.querySelector('.sys-btn-cancel');
        if (cancelBtn) {
          cancelBtn.click();
        } else {
          var confirmBtn = overlay.querySelector('.sys-btn-confirm');
          if (confirmBtn) confirmBtn.click();
        }
      } else if (e.key === 'Enter') {
        var activeInput = overlay.querySelector('.sys-modal-input');
        if (activeInput && document.activeElement === activeInput) {
          e.preventDefault();
          var confirmBtn = overlay.querySelector('.sys-btn-confirm');
          if (confirmBtn) confirmBtn.click();
        }
      }
    });
  }

  function close(value) {
    if (!overlay) return;
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('sys-modal-open');
    if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
      try { previousActiveElement.focus(); } catch(e) {}
    }
    if (currentResolver) {
      var r = currentResolver;
      currentResolver = null;
      r(value);
    }
  }

  var SystemDialog = {
    /**
     * Display an informative alert modal
     * @param {string} message
     * @param {Object} opts { title, type, confirmText }
     * @returns {Promise<void>}
     */
    alert: function(message, opts) {
      opts = opts || {};
      var title = opts.title || 'Notice';
      var type = opts.type || (title.toLowerCase().includes('error') ? 'danger' : 'info');
      var confirmText = opts.confirmText || 'Understood';
      var icon = ICONS[type] || ICONS.info;

      ensureElements();
      previousActiveElement = document.activeElement;

      return new Promise(function(resolve) {
        currentResolver = resolve;
        box.innerHTML = [
          '<div class="sys-modal-icon-badge sys-badge-' + type + '">' + icon + '</div>',
          '<h3 class="sys-modal-title">' + escapeHtml(title) + '</h3>',
          '<div class="sys-modal-body">' + escapeHtml(message) + '</div>',
          '<div class="sys-modal-actions">',
          '  <button type="button" class="sys-btn-confirm sys-btn-primary">' + escapeHtml(confirmText) + '</button>',
          '</div>'
        ].join('');

        var confirmBtn = box.querySelector('.sys-btn-confirm');
        confirmBtn.onclick = function() { close(undefined); };

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sys-modal-open');
        confirmBtn.focus();
      });
    },

    /**
     * Display a confirmation modal
     * @param {string} message
     * @param {Object} opts { title, type, confirmText, cancelText, isDestructive }
     * @returns {Promise<boolean>}
     */
    confirm: function(message, opts) {
      opts = opts || {};
      var title = opts.title || 'Confirm Action';
      var isDestructive = opts.isDestructive || /delete|remove|clear|reset|sign out/i.test(message + ' ' + title);
      var type = opts.type || (isDestructive ? 'danger' : 'warning');
      var confirmText = opts.confirmText || (isDestructive ? 'Confirm' : 'Continue');
      var cancelText = opts.cancelText || 'Cancel';
      var icon = ICONS[type] || (isDestructive ? ICONS.danger : ICONS.warning);

      ensureElements();
      previousActiveElement = document.activeElement;

      return new Promise(function(resolve) {
        currentResolver = resolve;
        box.innerHTML = [
          '<div class="sys-modal-icon-badge sys-badge-' + type + '">' + icon + '</div>',
          '<h3 class="sys-modal-title">' + escapeHtml(title) + '</h3>',
          '<div class="sys-modal-body">' + escapeHtml(message) + '</div>',
          '<div class="sys-modal-actions">',
          '  <button type="button" class="sys-btn-cancel sys-btn-ghost">' + escapeHtml(cancelText) + '</button>',
          '  <button type="button" class="sys-btn-confirm ' + (isDestructive ? 'sys-btn-danger' : 'sys-btn-primary') + '">' + escapeHtml(confirmText) + '</button>',
          '</div>'
        ].join('');

        var cancelBtn = box.querySelector('.sys-btn-cancel');
        var confirmBtn = box.querySelector('.sys-btn-confirm');

        cancelBtn.onclick = function() { close(false); };
        confirmBtn.onclick = function() { close(true); };

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sys-modal-open');
        if (isDestructive) {
          cancelBtn.focus();
        } else {
          confirmBtn.focus();
        }
      });
    },

    /**
     * Display a single-value prompt input modal
     * @param {string} message
     * @param {string} defaultValue
     * @param {Object} opts { title, type, confirmText, cancelText, inputType, placeholder }
     * @returns {Promise<string|null>}
     */
    prompt: function(message, defaultValue, opts) {
      opts = opts || {};
      defaultValue = defaultValue || '';
      var title = opts.title || 'Input Required';
      var type = opts.type || 'prompt';
      var confirmText = opts.confirmText || 'Submit';
      var cancelText = opts.cancelText || 'Cancel';
      var inputType = opts.inputType || 'text';
      var placeholder = opts.placeholder || '';
      var icon = ICONS[type] || ICONS.prompt;

      ensureElements();
      previousActiveElement = document.activeElement;

      return new Promise(function(resolve) {
        currentResolver = resolve;
        box.innerHTML = [
          '<div class="sys-modal-icon-badge sys-badge-prompt">' + icon + '</div>',
          '<h3 class="sys-modal-title">' + escapeHtml(title) + '</h3>',
          '<div class="sys-modal-body">' + escapeHtml(message) + '</div>',
          '<div class="sys-modal-field">',
          '  <input type="' + inputType + '" class="sys-modal-input" value="' + escapeAttr(defaultValue) + '" placeholder="' + escapeAttr(placeholder) + '" autocomplete="off" />',
          '</div>',
          '<div class="sys-modal-actions">',
          '  <button type="button" class="sys-btn-cancel sys-btn-ghost">' + escapeHtml(cancelText) + '</button>',
          '  <button type="button" class="sys-btn-confirm sys-btn-primary">' + escapeHtml(confirmText) + '</button>',
          '</div>'
        ].join('');

        var inputEl = box.querySelector('.sys-modal-input');
        var cancelBtn = box.querySelector('.sys-btn-cancel');
        var confirmBtn = box.querySelector('.sys-btn-confirm');

        cancelBtn.onclick = function() { close(null); };
        confirmBtn.onclick = function() { close(inputEl.value); };

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sys-modal-open');
        inputEl.focus();
        inputEl.select();
      });
    }
  };

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeAttr(str) {
    if (!str) return '';
    return String(str).replace(/"/g, '&quot;');
  }

  window.SystemDialog = SystemDialog;
})(window);
