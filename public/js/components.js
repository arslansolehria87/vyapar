/**
 * ═══════════════════════════════════════════
 *  VYAPAR — Shared Components (Navbar + Sidebar)
 *  Injected via JS to avoid HTML duplication
 *  ✅ Laravel version — uses URL paths not file paths
 * ═══════════════════════════════════════════
 */

(function () {
  const appUser = window.App?.user || null;
  const logoutUrl = window.App?.logoutUrl || null;
  const csrfToken = window.App?.csrfToken || null;
  const isAuthenticated = window.App?.isAuthenticated ?? false;
  const userPermissionsRaw = Array.isArray(appUser?.permissions) ? appUser.permissions : [];
  const userPermissions = userPermissionsRaw.map((p) => (typeof p === 'string' ? p.trim().toLowerCase() : p));

  const userRoles = Array.isArray(appUser?.roles) ? appUser.roles.map((r) => (typeof r === 'string' ? r.trim().toLowerCase() : '')) : [];
  const userRoleFallback = typeof appUser?.role === 'string' ? appUser.role.trim().toLowerCase() : '';

  // If user is authenticated but appUser is null, treat as super admin (fail-safe)
  const isSuperAdmin =
    appUser?.id === 1 ||
    userRoles.some((r) => r.includes('admin')) ||
    userRoleFallback.includes('admin') ||
    userPermissions.includes('admin') ||
    userPermissions.includes('super-admin') ||
    (typeof appUser?.name === 'string' && appUser.name.toLowerCase().includes('admin')) ||
    (userRoles.length === 0 && userPermissions.length === 0 && appUser?.id && appUser?.id !== 0 && appUser?.name && appUser.name.toLowerCase().includes('super')) ||
    (isAuthenticated && !appUser); // Fallback: if authenticated but no user object, show full menu

  console.log('Sidebar debug: window.App', window.App, 'appUser', appUser, 'userRoles', userRoles, 'userPermissions', userPermissions, 'isSuperAdmin', isSuperAdmin);

  const hasPermission = (permission) => {
    if (isSuperAdmin) return true;
    if (!permission || typeof permission !== 'string') return false;
    return userPermissions.includes(permission.trim().toLowerCase());
  };

  const permissionAliases = {
    // Purchase sub-items: only specific permissions (no purchase.view/create fallback)
    'purchase.bill': ['purchase.bill'],
    'purchase.payment_out': ['purchase.payment_out'],
    'purchase.return': ['purchase.return'],
    'purchase.expense': ['purchase.expense'],
    'purchase.order': ['purchase.order'],
    // Purchase parent: all purchase sub-permissions
    'purchase.view': ['purchase.view', 'purchase.bill', 'purchase.payment_out', 'purchase.return', 'purchase.expense', 'purchase.order'],

    // Sales sub-items: only specific permissions (no sales.view/create fallback)
    'sales.invoice': ['sales.invoice'],
    'sales.estimate': ['sales.estimate'],
    'sales.payment_in': ['sales.payment_in'],
    'sales.proforma': ['sales.proforma'],
    'sales.order': ['sales.order'],
    'sales.delivery_challan': ['sales.delivery_challan'],
    'sales.sale_return': ['sales.sale_return'],
    'sales.pos': ['sales.pos'],
    // Sales parent: all sales sub-permissions
    'sales.view': ['sales.view', 'sales.invoice', 'sales.estimate', 'sales.payment_in', 'sales.proforma', 'sales.order', 'sales.delivery_challan', 'sales.sale_return', 'sales.pos'],

    // Cash/Bank items
    'cashbank.loan_accounts': ['cashbank.loan_accounts'],
    'cashbank.bank_accounts': ['cashbank.bank_accounts'],
    'cashbank.view': ['cashbank.view', 'cashbank.loan_accounts', 'cashbank.bank_accounts', 'cashbank.cheques'],
  };

  const hasExtendedPermission = (permission) => {
    if (isSuperAdmin) return true;

    if (!permission) {
      return true;
    }

    const normalized = permissionAliases[permission] || [permission];
    return normalized.some((perm) => hasPermission(perm));
  };

  const getInitials = (name) => {
    if (!name) return 'GS';
    return name
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((word) => word[0].toUpperCase())
      .join('');
  };

  const userName = appUser?.name || 'Grocery Store';
  const userInitials = getInitials(appUser?.name);
  const currentUrl = window.location.pathname;
  const sidebarVisibilityStorageKey = 'vyaparGeneralSidebarConfig';
  const sidebarVisibilityEndpoint = '/dashboard/settings/general/sidebar-config';
  const sidebarWidthStorageKey = 'vyaparSidebarWidth';
  const sidebarCompactModeStorageKey = 'vyaparSidebarCompactMode';
  const sidebarUserModeStorageKey = 'vyaparSidebarUserMode';
  const sidebarMinWidth = 68;
  const sidebarCollapsedThreshold = 90;
  const sidebarMaxWidth = 420;

  const defaultSidebarVisibilityConfig = {
    more_transactions: {
      estimate_quotation_enabled: true,
      proforma_invoice_enabled: true,
      sale_purchase_order_enabled: true,
      other_income_enabled: false,
      fixed_assets_enabled: false,
      delivery_challan_enabled: true,
      goods_return_on_delivery_challan: false,
      print_amount_in_delivery_challan: false,
    },
  };

  const toBooleanish = (value) => {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'number') return value !== 0;
    if (typeof value === 'string') {
      const normalized = value.trim().toLowerCase();
      return ['1', 'true', 'yes', 'on'].includes(normalized);
    }
    return false;
  };

  const normalizeSidebarVisibilityConfig = (config) => {
    const merged = {
      ...defaultSidebarVisibilityConfig,
      ...(config && typeof config === 'object' ? config : {}),
    };
    merged.more_transactions = {
      ...defaultSidebarVisibilityConfig.more_transactions,
      ...(config?.more_transactions && typeof config.more_transactions === 'object' ? config.more_transactions : {}),
    };
    Object.keys(merged.more_transactions).forEach((key) => {
      if (key.endsWith('_enabled') || key.includes('_enabled') || key.includes('_return') || key.includes('_amount')) {
        merged.more_transactions[key] = toBooleanish(merged.more_transactions[key]);
      }
    });
    return merged;
  };

  const getStoredSidebarWidth = () => {
    const userModeStored = localStorage.getItem(sidebarUserModeStorageKey);
    if (userModeStored !== '1') {
      return 250;
    }

    const stored = Number.parseInt(localStorage.getItem(sidebarWidthStorageKey) || '', 10);
    if (Number.isFinite(stored)) {
      return Math.min(sidebarMaxWidth, Math.max(sidebarMinWidth, stored));
    }
    return 250;
  };

  const applySidebarWidth = (width) => {
    const resolved = Math.min(sidebarMaxWidth, Math.max(sidebarMinWidth, Number(width) || 250));
    document.documentElement.style.setProperty('--sidebar-width', `${resolved}px`);
    localStorage.setItem(sidebarWidthStorageKey, String(resolved));
    localStorage.setItem(sidebarUserModeStorageKey, '1');

    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
      sidebar.classList.toggle('is-collapsed', resolved <= sidebarCollapsedThreshold);
      sidebar.classList.toggle('is-expanded', resolved > sidebarCollapsedThreshold);
    }
    const minimizeBtnIcon = document.querySelector('#sidebarMinimizeBtn i');
    if (minimizeBtnIcon) {
      minimizeBtnIcon.className = resolved <= sidebarCollapsedThreshold ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left';
    }
    localStorage.setItem(sidebarCompactModeStorageKey, resolved <= sidebarCollapsedThreshold ? '1' : '0');

    const shortcutOverlay = document.getElementById('shortcutsOverlay');
    if (shortcutOverlay) {
      shortcutOverlay.style.left = `${resolved}px`;
      shortcutOverlay.style.width = `calc(100% - ${resolved}px)`;
    }

    const privacyOverlay = document.getElementById('privacyOverlay');
    if (privacyOverlay) {
      privacyOverlay.style.left = `${resolved}px`;
    }

    return resolved;
  };

  const initSidebarResize = () => {
    const sidebar = document.getElementById('sidebar');
    const handle = document.getElementById('sidebarResizeHandle');
    if (!sidebar || !handle) return;

    let isResizing = false;
    let startWidth = getStoredSidebarWidth();
    const minWidth = sidebarMinWidth;
    const maxWidth = sidebarMaxWidth;

    const onMouseMove = (event) => {
      if (!isResizing) return;
      const nextWidth = Math.min(maxWidth, Math.max(minWidth, event.clientX));
      applySidebarWidth(nextWidth);
    };

    const stopResizing = () => {
      if (!isResizing) return;
      isResizing = false;
      document.body.classList.remove('sidebar-is-resizing');
      document.documentElement.classList.remove('sidebar-is-resizing');
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', stopResizing);
      document.removeEventListener('mouseleave', stopResizing);
    };

    handle.addEventListener('mousedown', (event) => {
      event.preventDefault();
      event.stopPropagation();
      isResizing = true;
      startWidth = applySidebarWidth(getStoredSidebarWidth());
      document.body.classList.add('sidebar-is-resizing');
      document.documentElement.classList.add('sidebar-is-resizing');
      document.addEventListener('mousemove', onMouseMove);
      document.addEventListener('mouseup', stopResizing);
      document.addEventListener('mouseleave', stopResizing);
    });

    sidebar.addEventListener('dblclick', () => {
      applySidebarWidth(startWidth > sidebarCollapsedThreshold ? 250 : getStoredSidebarWidth());
    });
  };

  const initSidebarControls = () => {
    const sidebar = document.getElementById('sidebar');
    const minimizeBtn = document.getElementById('sidebarMinimizeBtn');
    if (!sidebar) return;

    const clearFlyoutState = (exceptItem = null) => {
      sidebar.querySelectorAll('.sidebar-nav > .nav-item.flyout-open').forEach((item) => {
        if (exceptItem && item === exceptItem) return;
        item.classList.remove('flyout-open');
        const submenu = item.querySelector('.sidebar-submenu');
        const toggle = item.querySelector('.sidebar-dropdown-toggle');
        if (submenu) {
          submenu.classList.remove('open');
          submenu.removeAttribute('style');
        }
        if (toggle) toggle.classList.remove('expanded');
      });
    };

    const positionFlyout = (item) => {
      const submenu = item.querySelector('.sidebar-submenu');
      const toggle = item.querySelector('.sidebar-dropdown-toggle');
      if (!submenu || !toggle) return;

      const sidebarRect = sidebar.getBoundingClientRect();
      const toggleRect = toggle.getBoundingClientRect();
      submenu.style.position = 'fixed';
      submenu.style.left = `${Math.round(sidebarRect.right)}px`;
      submenu.style.top = `${Math.max(50, Math.round(toggleRect.top))}px`;
      submenu.style.width = '230px';
      submenu.style.maxHeight = '320px';
      submenu.style.overflowY = 'auto';
      submenu.style.zIndex = '1055';
      submenu.style.display = 'block';
    };

    if (minimizeBtn) {
      minimizeBtn.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const collapsed = sidebar.classList.contains('is-collapsed');
        applySidebarWidth(collapsed ? 250 : sidebarMinWidth);
        clearFlyoutState();
      });
    }

    sidebar.addEventListener('click', (event) => {
      const toggle = event.target.closest('.sidebar-dropdown-toggle');
      if (!toggle) return;

      const item = toggle.closest('.nav-item');
      const submenu = item?.querySelector('.sidebar-submenu');
      if (!item || !submenu) return;

      event.preventDefault();
      event.stopPropagation();

      const collapsed = sidebar.classList.contains('is-collapsed');
      const isOpen = item.classList.contains('flyout-open') || submenu.classList.contains('open');
      if (collapsed) {
        if (isOpen) {
          clearFlyoutState();
          return;
        }
        clearFlyoutState(item);
        item.classList.add('flyout-open');
        submenu.classList.add('open');
        positionFlyout(item);
        return;
      }

      clearFlyoutState(item);
      if (isOpen) {
        submenu.classList.remove('open');
        toggle.classList.remove('expanded');
      } else {
        submenu.classList.add('open');
        toggle.classList.add('expanded');
        item.classList.add('flyout-open');
      }
    });

    window.addEventListener('resize', () => {
      const openItem = sidebar.querySelector('.sidebar-nav > .nav-item.flyout-open');
      if (openItem && sidebar.classList.contains('is-collapsed')) {
        positionFlyout(openItem);
      }
    });
  };

  const sidebarFeatureEnabled = (config, feature) => {
    const moreTransactions = config?.more_transactions || {};
    switch (feature) {
      case 'sales.estimate':
        return toBooleanish(moreTransactions.estimate_quotation_enabled);
      case 'sales.proforma':
        return toBooleanish(moreTransactions.proforma_invoice_enabled);
      case 'sales.order':
      case 'purchase.order':
        return toBooleanish(moreTransactions.sale_purchase_order_enabled);
      case 'sales.delivery_challan':
        return toBooleanish(moreTransactions.delivery_challan_enabled);
      default:
        return true;
    }
  };

  const canViewRole = isSuperAdmin || hasPermission('roles.view');
  const canViewUser = isSuperAdmin || hasPermission('user.view');
  const canViewParty = isSuperAdmin || hasPermission('party.view');
  const canViewProduct = isSuperAdmin || hasPermission('product.view');
  const canViewGrow = isSuperAdmin || hasPermission('grow.view');

  const menuItems = [
    {
      label: 'Home',
      href: '/dashboard',
      icon: 'fa-house',
      dataPage: 'dashboard',
    },
    {
      label: 'User Management',
      href: '#',
      icon: 'fa-users',
      children: [
        { label: 'Users', href: '/dashboard/users', dataPage: 'users', permission: 'user.view' },
        { label: 'Roles', href: '/dashboard/roles', dataPage: 'roles', permission: 'roles.view' },
      ],
    },
    {
      label: 'Parties',
      href: '/dashboard/parties',
      icon: 'fa-user-group',
      dataPage: 'parties',
      permission: 'party.view',
    },
    {
      label: 'Brokers',
      href: '/dashboard/brokers',
      icon: 'fa-handshake',
      dataPage: 'brokers',
      permission: 'broker.view',
    },
    {
      label: 'Items',
      href: '/dashboard/items',
      icon: 'fa-box',
      dataPage: 'items',
      permission: 'product.view',
    },
    {
      label: 'Sale',
      href: '#',
      icon: 'fa-cart-shopping',
      permission: 'sales.view',
      children: [
        { label: 'Sale Invoices', href: '/dashboard/sales', dataPage: 'sales.index', permission: 'sales.invoice' },
        { label: 'Estimate/Quotation', href: '/dashboard/sales/estimate', dataPage: 'sales.estimate', permission: 'sales.estimate', feature: 'sales.estimate' },
        { label: 'Proforma Invoice', href: '/dashboard/proforma-invoice', dataPage: 'sales.proforma', permission: 'sales.proforma', feature: 'sales.proforma' },
        { label: 'Payment-In', href: '/dashboard/payment-in', dataPage: 'sales.payment_in', permission: 'sales.payment_in' },
        { label: 'Sale Order', href: '/dashboard/sale-order', dataPage: 'sales.order', permission: 'sales.order', feature: 'sales.order' },
        { label: 'Delivery Challan', href: '/dashboard/delivery-challan', dataPage: 'sales.delivery_challan', permission: 'sales.delivery_challan', feature: 'sales.delivery_challan' },
        { label: 'Sale Return / Credit Note', href: '/dashboard/sale-return', dataPage: 'sales.sale_return', permission: 'sales.sale_return' },
        { label: 'Sale POS', href: '/dashboard/sales/pos', dataPage: 'sales.pos', permission: 'sales.pos' },
      ],
    },
    {
      label: 'Purchase & Expense',
      href: '#',
      icon: 'fa-bag-shopping',
      permission: 'purchase.view',
      children: [
        { label: 'Purchase Bills', href: '/dashboard/purchase-bill', dataPage: 'purchase.bill', permission: 'purchase.bill' },
        { label: 'Payment-Out', href: '/dashboard/payment-out', dataPage: 'purchase.payment_out', permission: 'purchase.payment_out' },
        { label: 'Purchase Return', href: '/dashboard/purchase-return', dataPage: 'purchase.return', permission: 'purchase.return' },
        { label: 'Purchase Order', href: '/dashboard/purchase-order', dataPage: 'purchase.order', permission: 'purchase.order', feature: 'purchase.order' },
        { label: 'Expense', href: '/dashboard/expense', dataPage: 'purchase.expense', permission: 'purchase.expense' },
      ],
    },
    {
      label: 'Grow Your Business',
      href: '/dashboard/grow',
      icon: 'fa-seedling',
      dataPage: 'grow',
      permission: 'grow.view',
    },
    {
      label: 'Cash & Bank',
      href: '#',
      icon: 'fa-wallet',
      permission: 'cashbank.view',
      children: [
        { label: 'Bank Accounts', href: '/dashboard/bank-accounts', dataPage: 'cashbank.bank_accounts', permission: 'cashbank.bank_accounts' },
        { label: 'Loan Accounts', href: '/dashboard/loan-accounts', dataPage: 'cashbank.loan_accounts', permission: 'cashbank.loan_accounts' },
        { label: 'Cheques', href: '/dashboard/cheques', dataPage: 'cashbank.cheques', permission: 'cashbank.cheques' },
      ],
    },
    {
      label: 'Reports',
      href: '/dashboard/reports',
      icon: 'fa-chart-simple',
      dataPage: 'reports',
    },
    {
      label: 'Sync / Share / Backup',
      href: '/dashboard/sync',
      icon: 'fa-cloud-arrow-up',
      dataPage: 'sync',
    },
    {
      label: 'Utilities',
      href: '#',
      icon: 'fa-screwdriver-wrench',
      children: [
        { label: 'Import Items', href: '/dashboard/utilities/import-items', dataPage: 'utilities.import_items' },
        { label: 'Export Items', href: '/dashboard/utilities/export-items', dataPage: 'utilities.export_items' },
        { label: 'Import Parties', href: '/dashboard/utilities/import-parties', dataPage: 'utilities.import_parties' },
        { label: 'Barcode Generator', href: '/dashboard/utilities/barcode-generator', dataPage: 'utilities.barcode_generator' },
        { label: 'Close Financial Year', href: '/dashboard/utilities/close-financial-year', dataPage: 'utilities.close_financial_year' },
      ],
    },
    {
      label: 'Settings',
      href: '/dashboard/settings/general',
      icon: 'fa-gear',
      dataPage: 'settings.general',
    },
  ];

 

  const canViewMenuItem = (item) => {
    // Always show Home for authenticated users
    if (item.dataPage === 'dashboard') return true;
    if (isSuperAdmin) return true;

    const hasChild = item.children ? item.children.some(canViewMenuItem) : false;
    const hasOwn = item.permission ? hasExtendedPermission(item.permission) : false;
    const result = hasOwn || hasChild;

    console.log('Sidebar debug: canViewMenuItem', {
      label: item.label,
      permission: item.permission,
      isSuperAdmin,
      hasOwn,
      hasChild,
      result,
      itemChildren: item.children?.map((c) => c.label) ?? [],
      currentUrl,
    });

    return result;
  };

  const renderMenu = () => {
    return menuItems
      .filter(canViewMenuItem)
      .map((item) => {
        const hasChildren = Array.isArray(item.children) && item.children.length;
        if (!hasChildren) {
          const currentIcon = item.icon ? `<i class="fa-solid ${item.icon}"></i> ` : '';
          const href = item.href || '#';
          const activeClass = currentUrl === href || currentUrl === item.dataPage ? 'active' : '';
          return `
            <li class="nav-item" ${item.feature ? `data-sidebar-feature="${item.feature}"` : ''}>
              <a class="nav-link ${activeClass}" data-page="${item.dataPage || ''}" href="${href}">
                ${currentIcon}<span class="sidebar-item-label">${item.label}</span>
              </a>
            </li>
          `;
        }

        const submenuHtml = item.children
          .filter(canViewMenuItem)
          .map((child) => {
            const activeClass = currentUrl === child.href || currentUrl === child.dataPage ? 'active' : '';
            return `
              <li class="${activeClass}" ${child.feature ? `data-sidebar-feature="${child.feature}"` : ''}><a class="nav-link" data-page="${child.dataPage || ''}" href="${child.href}"><span class="sidebar-item-label">${child.label}</span></a></li>
            `;
          })
          .join('');

        if (!submenuHtml) return '';

        return `
          <li class="nav-item" ${item.feature ? `data-sidebar-feature="${item.feature}"` : ''}>
            <a class="nav-link sidebar-dropdown-toggle" href="#">
              <i class="fa-solid ${item.icon}"></i> <span class="sidebar-item-label">${item.label}</span>
              <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="sidebar-submenu">${submenuHtml}</ul>
          </li>
        `;
      })
      .join('');
  };

const sidebarHTML = `
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-resize-handle" id="sidebarResizeHandle" title="Resize sidebar" aria-label="Resize sidebar"></div>
    <button type="button" class="sidebar-minimize-btn" id="sidebarMinimizeBtn" title="Minimize sidebar" aria-label="Minimize sidebar">
      <i class="fa-solid fa-angles-left"></i>
    </button>

    <div class="sidebar-search position-relative">
      <i class="fa-solid fa-magnifying-glass search-icon"></i>
      <input type="text" placeholder="Open Anything (Ctrl+F)" id="sidebarSearch">
    </div>
    <ul class="sidebar-nav">
      ${renderMenu()}
    </ul>

    <div class="sidebar-promo">
      <span class="promo-badge">Vyapar</span>
      <h6>EARLY BIRD OFFER</h6>
      <p>Upto <strong>50% OFF</strong> on all plans. Limited time only!</p>
      <button class="btn-promo">Buy Now</button>
    </div>

    <div class="sidebar-company" id="sidebarCompany">
      <div class="company-avatar">${userInitials}</div>
      <div class="company-info">
        <div class="company-name">${userName}</div>
        <div class="company-role">My Company</div>
      </div>
      ${logoutUrl && window.App?.isAuthenticated ? `
      <div class="company-dropdown" id="companyDropdown">
        <button class="company-dropdown-item" type="button" id="sidebarLogoutBtn">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </div>
      ` : ''}
    </div>
  </aside>`;

  // subsequent code (topbar creation and injection etc.) remains unchanged


  // ── Top Navbar ──
 const navbarHTML = `
<nav class="top-navbar" id="topNavbar">
  <div class="navbar-left">
    <span class="brand-logo"><i class="fa-solid fa-bolt"></i> Vyapar</span>

    <!-- ── Company dropdown ── -->
    <div class="navbar-company-wrapper" style="position:relative;display:inline-block;">
      <a href="#" class="nav-link-item" id="navCompanyBtn"
         onclick="toggleNavCompanyMenu(event)">
        <i class="fa-regular fa-building"></i> Company
        <i class="fa-solid fa-caret-down" style="font-size:.7rem;margin-left:2px;"></i>
      </a>
      <div class="navbar-company-dropdown" id="navCompanyDropdown">
      <a class="navbar-company-item" href="/dashboard/company">
          <i class="fa-solid fa-right-left"></i> Change Company
        </a>
        <a class="navbar-company-item" href="#" onclick="openNavRenameModal(event)">
          <i class="fa-solid fa-pencil"></i> Rename Company Name
        </a>
      </div>
    </div>
    <!-- ── End Company dropdown ── -->

    <div class="nav-help-wrapper" style="position:relative; display:inline-block;">
  <a href="#" class="nav-link-item" onclick="toggleHelpMenu(event)">
    <i class="fa-regular fa-circle-question"></i> Help
    <i class="fa-solid fa-caret-down" style="font-size:.7rem;margin-left:2px;"></i>
  </a>
  <div id="helpDropdown" style="display:none; position:absolute; top:100%; left:0; background:#d8dbe9; border:1px solid #c5c8d4; border-radius:2px; box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:180px; z-index:9999; margin-top:5px; overflow:hidden;">
    <a href="#" onclick="handleHelpNav('/dashboard/help/contact')" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:13px; border-bottom:1px solid #c5c8d4;">Contact Us</a>
    <a href="#" onclick="handleHelpNav('/dashboard/help/tutorials')" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:13px; border-bottom:1px solid #c5c8d4;">Video Tutorials</a>
    <a href="#" onclick="handleHelpNav('/dashboard/help/releases')" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:13px; border-bottom:1px solid #c5c8d4;">View Release Notes</a>
    <a href="#" onclick="handleHelpNav('/dashboard/help/privacy')" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:13px;">Privacy policy</a>
  </div>
</div>
    <div class="nav-version-wrapper" style="position:relative; display:inline-block;">
  <a href="#" class="nav-link-item" onclick="toggleVersionsMenu(event)">
    <i class="fa-solid fa-code-branch"></i> Versions
  </a>

  <div id="versionsDropdown" style="
    display:none;
    position:absolute;
    top:100%;
    left:0;
    background:#d8dbe9; /* Matches the grayish-purple tint in your 2nd pic */
    border:1px solid #c5c8d4;
    border-radius:2px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
    min-width:180px;
    z-index:9999;
    margin-top:2px;
    padding: 12px;
    text-align: left;
  ">
    <div style="font-size: 13px; color: #444; margin-bottom: 10px;">
      Application : <span style="font-weight: 500;">1.1.1</span>
    </div>
    <div style="font-size: 13px; color: #444; margin-bottom: 10px;">
      E.Version : <span style="font-weight: 500;">36.9.1</span>
    </div>
    <div style="margin-top: 12px;">
      <a href="#" style="text-decoration: none; color: #444; font-size: 13px; border-bottom: 1px solid #888;">
        Check For Update
      </a>
    </div>
  </div>
</div>
    <div class="nav-shortcuts-wrapper" style="position:relative; display:inline-block;">
  <a href="#" class="nav-link-item" onclick="toggleShortcutsMenu(event)">
    <i class="fa-regular fa-keyboard"></i> Shortcuts
    <i class="fa-solid fa-caret-down" style="font-size:.7rem;margin-left:2px;"></i>
  </a>
  <div id="shortcutsDropdown" style="display:none; position:absolute; top:100%; left:0; background:#d8dbe9; border:1px solid #c5c8d4; border-radius:2px; box-shadow:0 4px 12px rgba(0,0,0,0.1); min-width:160px; z-index:9999; margin-top:5px; overflow:hidden;">
    <a href="#" onclick="openShortcutsModal('standard', event)" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:14px; border-bottom:1px solid #c5c8d4;">Shortcuts</a>
    <a href="#" onclick="openShortcutsModal('pos', event)" style="display:block; padding:10px 15px; text-decoration:none; color:#333; font-size:14px;">POS Shortcuts</a>
  </div>
</div>
    <button class="btn-icon" title="Refresh"><i class="fa-solid fa-arrows-rotate"></i></button>
  </div>
  <div class="navbar-center">
    Customer Support : <i class="fa-solid fa-phone"></i>
    <span class="phone-number">(+91) 9333 911 911</span> |
    <a href="#">Get Instant Online Support</a>
  </div>
 <div class="navbar-right">
  </div>
</nav>

<!-- ── Rename Company Modal (injected by navbar) ── -->
<div id="navRenameOverlay" style="
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.35); z-index:99999;
  align-items:center; justify-content:center;">
  <div style="
    background:#e8ecf4; border-radius:8px;
    padding:24px 28px 20px; width:420px; max-width:95vw;
    box-shadow:0 8px 32px rgba(0,0,0,.18); position:relative;">
    <h6 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:#1a1f2e;">
      Update company display name
    </h6>
    <button onclick="closeNavRenameModal()" style="
      position:absolute;top:12px;right:14px;
      background:#ccc;border:none;border-radius:50%;
      width:22px;height:22px;font-size:.8rem;
      cursor:pointer;display:flex;align-items:center;justify-content:center;color:#333;">
      ✕
    </button>
    <input type="text" id="navRenameInput" placeholder="Company Name" style="
      width:100%;padding:10px 12px;
      border:1px solid #d0d4dd;border-radius:5px;
      font-size:.92rem;background:#f7f8fc;outline:none;margin-bottom:12px;
      box-sizing:border-box;">
    <div style="height:1px;background:#d0d4dd;margin:10px 0 14px;"></div>
    <button onclick="saveNavRename()" style="
      float:right;padding:8px 28px;
      background:#1a73e8;color:#fff;border:none;
      border-radius:5px;font-size:.88rem;font-weight:600;
      cursor:pointer;letter-spacing:.5px;">SAVE</button>
    <div style="clear:both"></div>
  </div>
</div>`;

// ═══════════════════════════════════════════════════════════════
//  ADD these CSS rules to your styles.css  (or inside components.js
//  as an injected <style> block — see below)
// ═══════════════════════════════════════════════════════════════

const navCompanyStyles = `
  .navbar-company-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #fff;
    border: 1px solid #e0e4ea;
    border-radius: 6px;
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
    min-width: 190px;
    z-index: 9999;
    overflow: hidden;
    margin-top: 4px;
  }
  .navbar-company-dropdown.open { display: block; }
  .navbar-company-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 16px;
    font-size: .86rem;
    color: #333;
    text-decoration: none;
    transition: background .12s;
  }
  .navbar-company-item:hover { background: #f5f7fa; color: #1a73e8; }
`;

// Inject styles
(function() {
  const style = document.createElement('style');
  style.textContent = navCompanyStyles;
  document.head.appendChild(style);
})();
 const hoverBtnStyles = `
  .add-more-container {
    cursor: pointer;
    display: inline-block;
    vertical-align: middle;
  }
  .add-more-btn {
    background-color: #CCE6FF;
    color: #007bff;
    height: 38px;
    width: 38px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    overflow: hidden;
    transition: width 0.3s ease, padding 0.3s ease;
    white-space: nowrap;
  }
  .add-more-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s ease, opacity 0.2s ease, margin 0.3s ease;
  }
  .add-more-container:hover .add-more-btn {
    width: 130px;
    padding: 0 15px;
    justify-content: flex-start;
  }
  .add-more-container:hover .add-more-text {
    max-width: 100px;
    opacity: 1;
    margin-left: 8px;
  }
`;
const hoverStyleTag = document.createElement('style');
hoverStyleTag.innerText = hoverBtnStyles;
document.head.appendChild(hoverStyleTag);
const layoutFixStyles = `
  #topNavbar {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1050 !important;
    width: 100% !important;
  }
  #sidebar {
    z-index: 1040 !important;
  }
  .navbar-left {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    overflow: visible !important;
  }
  .brand-logo {
    flex-shrink: 0 !important;
    white-space: nowrap !important;
  }
  #sidebar {
    position: fixed !important;
    transition: width .18s ease, min-width .18s ease;
    overflow-x: hidden !important;
  }
  #sidebar .sidebar-resize-handle {
    position: absolute;
    top: 0;
    right: -1px;
    width: 14px;
    height: 100%;
    cursor: ew-resize;
    z-index: 1200;
    background: linear-gradient(to right, transparent 0%, rgba(255,255,255,.06) 100%);
    border-left: 1px solid rgba(255,255,255,.18);
    box-shadow: inset -1px 0 0 rgba(0,0,0,.25);
    pointer-events: auto;
  }
  #sidebar .sidebar-resize-handle::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 2px;
    height: 34px;
    transform: translate(-50%, -50%);
    border-radius: 2px;
    background: rgba(255,255,255,.55);
    box-shadow: -4px 0 0 rgba(255,255,255,.32), 4px 0 0 rgba(255,255,255,.32);
  }
  #sidebar .sidebar-resize-handle:hover,
  #sidebar .sidebar-resize-handle:active {
    background: linear-gradient(to right, transparent 0%, rgba(255,255,255,.18) 100%);
  }
  #sidebar .sidebar-minimize-btn {
    position: absolute;
    top: 8px;
    right: 20px;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 8px;
    background: rgba(255,255,255,.08);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1201;
    cursor: pointer;
    transition: background .18s ease, transform .18s ease;
  }
  #sidebar .sidebar-minimize-btn:hover {
    background: rgba(255,255,255,.16);
    transform: translateY(-1px);
  }
  body.sidebar-is-resizing,
  body.sidebar-is-resizing * {
    cursor: ew-resize !important;
    user-select: none !important;
  }
  #sidebar.is-collapsed .sidebar-search,
  #sidebar.is-collapsed .sidebar-promo,
  #sidebar.is-collapsed .sidebar-company .company-info,
  #sidebar.is-collapsed .sidebar-company .company-dropdown,
  #sidebar.is-collapsed .sidebar-nav .sidebar-item-label,
  #sidebar.is-collapsed .sidebar-nav .badge-plus,
  #sidebar.is-collapsed .sidebar-nav .dropdown-arrow,
  #sidebar.is-collapsed .sidebar-submenu {
    display: none !important;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item {
    position: relative;
  }
  #sidebar.is-collapsed .sidebar-nav .nav-link {
    justify-content: center !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  #sidebar.is-collapsed .sidebar-nav .nav-link i {
    margin: 0 !important;
  }
  #sidebar.is-collapsed .sidebar-company {
    justify-content: center !important;
  }
  #sidebar.is-collapsed .sidebar-company .company-avatar {
    margin-right: 0 !important;
  }
  #sidebar.is-collapsed .sidebar-minimize-btn {
    right: 14px;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu {
    display: block !important;
    position: fixed !important;
    left: var(--sidebar-width, 250px);
    top: auto;
    width: 220px;
    max-height: none !important;
    overflow: auto !important;
    background: #212734 !important;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 10px 26px rgba(0,0,0,.3);
    border-radius: 10px;
    padding: 6px 0 !important;
    z-index: 1055;
    pointer-events: auto;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu > li {
    display: block !important;
    width: 100% !important;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu .nav-link {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    padding-left: 16px !important;
    padding-right: 16px !important;
    gap: 10px !important;
    color: #f3f4f6 !important;
    min-height: 42px !important;
    width: 100% !important;
    white-space: nowrap !important;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu .nav-link:hover {
    background: rgba(255,255,255,.08) !important;
    color: #fff !important;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu .sidebar-item-label {
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
    color: inherit !important;
  }
  #sidebar.is-collapsed .sidebar-nav > .nav-item.flyout-open > .sidebar-submenu .nav-link i {
    color: inherit !important;
  }
`;
const layoutFixTag = document.createElement('style');
layoutFixTag.innerText = layoutFixStyles;
document.head.appendChild(layoutFixTag);
const finalButtonStyles = `
  .btn-vy-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 20px;
    border-radius: 50px;
    border: none;
    font-weight: 700;
    font-size: 14px;
    color: #ffffff !important;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s ease;
  }
  .btn-vy-red { background-color: #e62e3d !important; }
  .btn-vy-blue { background-color: #0073e6 !important; }
  .btn-vy-action:hover { filter: brightness(1.1); color: #ffffff !important; }
  .btn-vy-action i { font-size: 11px; }
`;
(function() {
    const style = document.createElement('style');
    style.textContent = finalButtonStyles;
    document.head.appendChild(style);
})();
// ═══════════════════════════════════════════════════════════════
//  ADD these JS functions anywhere after the navbarHTML injection
// ═══════════════════════════════════════════════════════════════

window.toggleNavCompanyMenu = function(e) {
  e.preventDefault();
  e.stopPropagation();
  document.getElementById('navCompanyDropdown').classList.toggle('open');
};

document.addEventListener('click', function() {
  const dd = document.getElementById('navCompanyDropdown');
  if (dd) dd.classList.remove('open');
});

window.openNavRenameModal = function(e) {
  e.preventDefault();
  const dd = document.getElementById('navCompanyDropdown');
  if (dd) dd.classList.remove('open');

  const currentName = window.App?.user?.company_name || window.currentCompanyName || '';
  document.getElementById('navRenameInput').value = currentName;

  const overlay = document.getElementById('navRenameOverlay');
  overlay.style.display = 'flex';
  setTimeout(() => document.getElementById('navRenameInput').focus(), 100);
};

window.closeNavRenameModal = function() {
  document.getElementById('navRenameOverlay').style.display = 'none';
};

window.saveNavRename = async function() {
  const name = document.getElementById('navRenameInput').value.trim();
  if (!name) return;

  const companyId = window.App?.current_company_id;
  if (!companyId) {
    alert('No active company selected. Please open a company first.');
    return;
  }

  try {
    const res = await fetch('/dashboard/company/' + companyId + '/rename', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': window.App?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-HTTP-Method-Override': 'PUT'
      },
      body: JSON.stringify({ name })
    });
    const data = await res.json();
    if (data.success) {
      closeNavRenameModal();
      const el = document.querySelector('.sidebar-company .company-name');
      if (el) el.textContent = name;
      window.currentCompanyName = name;
    } else {
      alert(data.message || 'Failed to rename.');
    }
  } catch(err) {
    alert('Error renaming company.');
  }
}


 const topbarHTML = `
  <div id="topbar" class="bg-white border-bottom d-flex align-items-center mb-4" style="margin: -20px -24px 20px -24px; padding: 12px 24px; margin-top:5px; height: 65px;">
    <div class="topbar-inner w-100 d-flex align-items-center justify-content-between">

      <div class="topbar-search d-flex align-items-center" style="background: #f1f3f7; border-radius: 8px; padding: 5px 15px; width: 300px;">
        <i class="fa-solid fa-magnifying-glass text-secondary me-2"></i>
        <input type="text" placeholder="Search..." style="border:none; background:transparent; outline:none; font-size:14px; width:100%;">
      </div>

      <div class="topbar-actions d-flex align-items-center gap-2">
       <a href="/dashboard/sale/create" class="btn-vy-action btn-vy-red">
          <i class="fa-solid fa-plus"></i>
          <span>Add Sale</span>
        </a>

        <a href="/dashboard/purchase-bill/create" class="btn-vy-action btn-vy-blue">
          <i class="fa-solid fa-plus"></i>
          <span>Add Purchase</span>
        </a>
        <div class="add-more-container" onclick="toggleTransactionMenu(event)">
           <div class="add-more-btn">
             <i class="fa-solid fa-plus"></i>
             <span class="add-more-text">Add More</span>
           </div>
        </div>

        <div class="ms-3 ps-3 d-flex align-items-center gap-3" style="border-left:1px solid #ddd; position:relative; overflow:visible !important;">
   <i class="fa-solid fa-print text-secondary" style="cursor:pointer;"></i>
   <i class="fa-solid fa-ellipsis-vertical text-secondary" id="threeDotsBtn" style="cursor:pointer; font-size:16px; padding:5px;" onclick="toggleThreeDotsMenu(event)"></i>
</div>
      </div>
    </div>
  </div>`;




  const applySidebarVisibility = (config) => {
    const resolved = normalizeSidebarVisibilityConfig(config);
    const featureNodes = document.querySelectorAll('.sidebar-nav [data-sidebar-feature]');
    featureNodes.forEach((node) => {
      const feature = node.getAttribute('data-sidebar-feature');
      const shouldShow = sidebarFeatureEnabled(resolved, feature);
      node.style.display = shouldShow ? '' : 'none';
      node.classList.toggle('is-hidden-by-setting', !shouldShow);
    });

    const parentItems = document.querySelectorAll('.sidebar-nav > .nav-item');
    parentItems.forEach((parent) => {
      const submenu = parent.querySelector('.sidebar-submenu');
      if (!submenu) return;
      const visibleChildren = Array.from(submenu.children).filter((child) => child.style.display !== 'none');
      parent.style.display = visibleChildren.length ? '' : 'none';
    });
  };

  const loadSidebarVisibilityConfig = async () => {
    try {
      const appConfig = window.App?.generalSettings;
      if (appConfig && typeof appConfig === 'object') {
        const resolved = normalizeSidebarVisibilityConfig(appConfig);
        localStorage.setItem(sidebarVisibilityStorageKey, JSON.stringify(resolved));
        applySidebarVisibility(resolved);
        return;
      }
    } catch (error) {
      // ignore app config issues
    }

    try {
      const cached = localStorage.getItem(sidebarVisibilityStorageKey);
      if (cached) {
        applySidebarVisibility(JSON.parse(cached));
      }
    } catch (error) {
      // ignore cache parse issues
    }

    try {
      const response = await fetch(sidebarVisibilityEndpoint, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      if (!response.ok) return;
      const data = await response.json();
      const resolved = normalizeSidebarVisibilityConfig(data);
      localStorage.setItem(sidebarVisibilityStorageKey, JSON.stringify(resolved));
      applySidebarVisibility(resolved);
    } catch (error) {
      // keep cached/default visibility
    }
  };

  // ── Inject into page ──
  document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
  document.body.insertAdjacentHTML('afterbegin', navbarHTML);
  applySidebarWidth(getStoredSidebarWidth());
  initSidebarResize();
  initSidebarControls();
  loadSidebarVisibilityConfig();

const shortcutModalsHTML = `
<div id="shortcutsOverlay" style="
    display: none;
    position: fixed;
    top: 50px;
    left: var(--sidebar-width, 250px);
    right: 0;
    bottom: 0;
    background: #ffffff;
    z-index: 99995;
    font-family: 'Segoe UI', Arial, sans-serif;
    overflow-y: auto;
    box-sizing: border-box;
    padding: 20px 40px;">
    <div style="display:flex; justify-content:center; align-items:center; position:relative; margin-bottom:25px; padding-bottom:15px; border-bottom:1px solid #f2f4f7;">
        <h4 style="margin:0; font-size:14px; letter-spacing:1.5px; font-weight:700; color:#5a6578; text-transform:uppercase;">SHORTCUTS</h4>
       <button onclick="document.getElementById('shortcutsOverlay').style.display='none'" style="position:absolute; right:0; background:none; border:none; font-size:26px; cursor:pointer; color:#b0b8c6; line-height:1;">&times;</button>
    </div>
    <div id="shortcutsContent" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:50px; width:100%;"></div>
</div>`;

document.body.insertAdjacentHTML('beforeend', shortcutModalsHTML);
function trRow(label, key, isBlue = false, sub = '') {
    const color = isBlue ? '#1a73e8' : '#333';
    return `
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; cursor:pointer;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-caret-right" style="color:#1a73e8; font-size:10px;"></i>
            <div>
                <div style="font-size:13px; color:${color}; ${isBlue ? 'border-bottom:1px solid #1a73e8;' : ''}">${label}</div>
                ${sub ? `<div style="font-size:10px; color:#aaa; margin-top:2px;">${sub}</div>` : ''}
            </div>
        </div>
        <div style="font-size:10px; color:#888; padding-top:2px;">${key}</div>
    </div>`;
}

const transactionMenuHTML = `
<div id="transMenuOverlay" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(0,0,0,0.1);">
  <div id="transMenuBox" style="position:absolute; top:70px; right:20px; width:650px; background:#fff; border-radius:4px; box-shadow:0 10px 30px rgba(0,0,0,0.2); font-family:sans-serif; overflow:hidden;">

    <div style="position:absolute; top:-10px; right:45px; width:0; height:0; border-left:10px solid transparent; border-right:10px solid transparent; border-bottom:10px solid #fff;"></div>

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; padding:25px 30px; gap:30px;">
      <div>
        <div style="font-weight:700; font-size:13px; color:#333; margin-bottom:20px; text-transform:uppercase;">SALE</div>
        ${trRow('Sale Invoice', 'ALT + S', true)}
        ${trRow('Payment-In', 'ALT + I')}
        ${trRow('Sale Return', 'ALT + R', false, 'Cr Note')}
        ${trRow('Sale Order', 'ALT + F')}
        ${trRow('Estimate/Quotation', 'ALT + M')}
        ${trRow('Proforma Invoice', 'ALT + K')}
        ${trRow('Delivery Challan', 'ALT + D')}
      </div>
      <div>
        <div style="font-weight:700; font-size:13px; color:#333; margin-bottom:20px; text-transform:uppercase;">PURCHASE</div>
        ${trRow('Purchase Bill', 'ALT + P')}
        ${trRow('Payment-Out', 'ALT + O')}
        ${trRow('Purchase Return', 'ALT + L', false, 'Dr Note')}
        ${trRow('Purchase Order', 'ALT + G')}
      </div>
      <div>
        <div style="font-weight:700; font-size:13px; color:#333; margin-bottom:20px; text-transform:uppercase;">OTHERS</div>
        ${trRow('Expenses', 'ALT + E')}
        ${trRow('Party To Party Transfer', 'ALT + J')}
      </div>
    </div>

    <div style="background:#ffebac; padding:12px; display:flex; align-items:center; justify-content:center; font-size:13px; color:#6d531a;">
      Shortcut to open this menu :
      <span style="background:#fff; padding:2px 8px; border-radius:4px; margin:0 5px; font-weight:700; border:1px solid #d4c18e;">Ctrl</span> +
      <span style="background:#fff; padding:2px 8px; border-radius:4px; margin-left:5px; font-weight:700; border:1px solid #d4c18e;">Enter</span>
    </div>
  </div>
</div>`;

document.body.insertAdjacentHTML('beforeend', transactionMenuHTML);

window.toggleTransactionMenu = function(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const menu = document.getElementById('transMenuOverlay');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
};

document.getElementById('transMenuOverlay').addEventListener('click', function(e) {
    if (e.target.id === 'transMenuOverlay') this.style.display = 'none';
});
  // ── Keyboard Shortcut Map ──
  const SHORTCUT_MAP = {
    'alt+s': '/dashboard/sales/create',
    'alt+p': '/dashboard/purchase-bill/create',
    'alt+i': '/dashboard/payment-in',
    'alt+o': '/dashboard/payment-out',
    'alt+e': '/dashboard/expense',
    'alt+n': '/dashboard/parties',
    'alt+a': '/dashboard/items/create',
    'shift+h': '/dashboard',
    'shift+p': '/dashboard/parties',
    'shift+i': '/dashboard/items',
  };
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeShortcutsModal(); return; }
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

    const isAlt   = e.altKey;
    const isShift = e.shiftKey;
    const isCtrl  = e.ctrlKey;
    const isMeta  = e.metaKey;
    const key     = e.key.toLowerCase();

    if ((isCtrl || isMeta) && (key === 'r' || key === 'f5')) return;

    if (isAlt && !isCtrl && !isMeta && key === 's') { e.preventDefault(); window.location.href = '/dashboard/sale/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'p') { e.preventDefault(); window.location.href = '/dashboard/purchase-bill/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'i') { e.preventDefault(); window.location.href = '/dashboard/payment-in'; }
    if (isAlt && !isCtrl && !isMeta && key === 'o') { e.preventDefault(); window.location.href = '/dashboard/payment-out'; }
    if (isAlt && !isCtrl && !isMeta && key === 'e') { e.preventDefault(); window.location.href = '/dashboard/expense'; }
    if (isAlt && !isCtrl && !isMeta && key === 'n') { e.preventDefault(); window.location.href = '/dashboard/parties'; }
    if (isAlt && !isCtrl && !isMeta && key === 'a') { e.preventDefault(); window.location.href = '/dashboard/items/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'f') { e.preventDefault(); window.location.href = '/dashboard/sale-order/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'g') { e.preventDefault(); window.location.href = '/dashboard/purchase-order/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'd') { e.preventDefault(); window.location.href = '/dashboard/create-challan'; }
    if (isAlt && !isCtrl && !isMeta && key === 'm') { e.preventDefault(); window.location.href = '/dashboard/estimates/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'k') { e.preventDefault(); window.location.href = '/dashboard/proforma-invoice/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'r') { e.preventDefault(); window.location.href = '/dashboard/sale-return/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'l') { e.preventDefault(); window.location.href = '/dashboard/purchase-return/create'; }
    if (isAlt && !isCtrl && !isMeta && key === 'b') { e.preventDefault(); window.location.href = '/dashboard/bank-accounts'; }
    if (isAlt && !isCtrl && !isMeta && key === 'z') { e.preventDefault(); window.location.href = '/dashboard/sales/pos'; }

    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'h') { e.preventDefault(); window.location.href = '/dashboard'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'p') { e.preventDefault(); window.location.href = '/dashboard/parties'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'i') { e.preventDefault(); window.location.href = '/dashboard/items'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'r') { e.preventDefault(); window.location.href = '/dashboard/reports'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'b') { e.preventDefault(); window.location.href = '/dashboard/bank-accounts'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'c') { e.preventDefault(); window.location.href = '/dashboard/cash-in-hand'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'e') { e.preventDefault(); window.location.href = '/dashboard/expense'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'o') { e.preventDefault(); window.location.href = '/dashboard/sale-order'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 's') { e.preventDefault(); window.location.href = '/dashboard/sales/estimate'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === 'u') { e.preventDefault(); window.location.href = '/dashboard/cheques'; }
    if (isShift && !isAlt && !isCtrl && !isMeta && key === '1') { e.preventDefault(); window.location.href = '/dashboard/settings/general'; }
    if (isCtrl && !isAlt && !isMeta && key === 'f') { e.preventDefault(); openGlobalSearch(); }
    if (isCtrl && !isAlt && !isMeta && key === 'p') { e.preventDefault(); window.print(); }
    if (isCtrl && !isAlt && !isMeta && key === 'y') { e.preventDefault(); const t = document.getElementById('privacyToggle'); if(t){ t.checked = !t.checked; togglePrivacyMode(t.checked); } }
});
  // ── Handle plus button clicks in menu ──
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('menu-plus-btn')) {
      e.preventDefault();
      e.stopPropagation();
      const url = e.target.getAttribute('data-url');
      const modal = e.target.getAttribute('data-modal');
      if (url) window.location.href = url;
      if (modal) {
        const modalElement = document.getElementById(modal);
        if (modalElement) {
          const bsModal = new window.bootstrap.Modal(modalElement);
          bsModal.show();
        }
      }
    }
  });

  // ── Logout button (if available) ──
  if (logoutUrl && window.App?.isAuthenticated) {
    const logoutBtn = document.getElementById('sidebarLogoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = logoutUrl;
        form.style.display = 'none';
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrfToken || '';
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
      });
    }
  }


  const mainContent = document.getElementById('mainContent');
  if (mainContent) {
    mainContent.insertAdjacentHTML('afterbegin', topbarHTML);
  }


  // ── Company dropdown toggle (logout menu) ──
  const sidebarCompany = document.getElementById('sidebarCompany');
  const companyDropdown = document.getElementById('companyDropdown');
  if (sidebarCompany && companyDropdown) {
    sidebarCompany.addEventListener('click', (event) => {
      event.stopPropagation();
      companyDropdown.classList.toggle('open');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      companyDropdown.classList.remove('open');
    });

    companyDropdown.addEventListener('click', (event) => {
      event.stopPropagation();
    });
  }

  // ── Highlight active page ──

  const normalize = (value) => {
    if (!value) return '';
    return value.trim().toLowerCase().replace(/^\/+|\/+$/g, '');
  };

  const normalizePageKey = (page) => {
    if (!page) return '';
    const normalized = page.trim().toLowerCase();
    if (normalized.endsWith('s')) return normalized.slice(0, -1);
    return normalized + 's';
  };

  const currentPath = normalize(currentUrl);
  const currentBodyPage = normalize(document.body.getAttribute('data-page'));

  let matchedLink = null;
  let matchedLength = 0;

  const links = Array.from(document.querySelectorAll('.sidebar-nav .nav-link'));

  links.forEach((link) => link.classList.remove('active'));
  document.querySelectorAll('.sidebar-nav .sidebar-submenu').forEach((submenu) => submenu.classList.remove('open'));
  document.querySelectorAll('.sidebar-nav .sidebar-dropdown-toggle').forEach((toggle) => toggle.classList.remove('expanded'));

  links.forEach((link) => {
    const href = normalize(link.getAttribute('href'));
    const linkPage = normalize(link.getAttribute('data-page'));

    // exact URL match has highest priority
    if (href && href !== '#' && (currentPath === href || currentPath === href.replace(/^dashboard\//, ''))) {
      if (href.length > matchedLength) {
        matchedLink = link;
        matchedLength = href.length;
      }
      return;
    }

    // child route in same section, e.g. /dashboard/sales/pos should mark /dashboard/sales
    if (href && href !== '#' && currentPath.startsWith(href + '/')) {
      if (href.length > matchedLength) {
        matchedLink = link;
        matchedLength = href.length;
      }
      return;
    }

    // fallback by data-page value (supports singular/plural)
    if (!matchedLink && currentBodyPage) {
      const normalizedLinkPage = normalizePageKey(linkPage);
      const normalizedBody = normalizePageKey(currentBodyPage);
      if (linkPage === currentBodyPage || normalizedLinkPage === normalizedBody || linkPage === normalizedBody || normalizedLinkPage === currentBodyPage) {
        matchedLink = link;
      }
    }
  });

  // Explicit home selection
  if (currentPath === 'dashboard' || currentBodyPage === 'dashboard') {
    const homeLink = document.querySelector('.sidebar-nav .nav-link[href="/dashboard"]');
    if (homeLink) {
      matchedLink = homeLink;
    }
  }

  if (matchedLink) {
    const parentSubmenu = matchedLink.closest('.sidebar-submenu');
    console.log('Sidebar debug: matched link', {
      currentUrl,
      currentPath,
      currentBodyPage,
      matchedHref: normalize(matchedLink.getAttribute('href')),
      matchedDataPage: normalize(matchedLink.getAttribute('data-page')),
      isDropdownChild: Boolean(parentSubmenu),
    });

    if (parentSubmenu) {
      // for dropdown items, add active to the <li>
      const li = matchedLink.closest('li');
      if (li) li.classList.add('active');
    } else {
      // for top-level items, add to the <a>
      matchedLink.classList.add('active');
    }

    if (parentSubmenu) {
      parentSubmenu.classList.add('open');
      const toggle = parentSubmenu.previousElementSibling;
      if (toggle) toggle.classList.add('expanded');
    }
  } else {
    console.log('Sidebar debug: no matched link found', {
      currentUrl,
      currentPath,
      currentBodyPage,
      userPermissions,
      isSuperAdmin,
    });
  }
  // ===== SIDEBAR + BUTTON HANDLERS =====
document.addEventListener('click', function(e) {
    const plusBtn = e.target.closest('.menu-plus-btn[data-modal]');
    if (!plusBtn) return;

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const modalId = plusBtn.getAttribute('data-modal');
    const modalEl = document.getElementById(modalId);

    if (modalEl) {
        // Reset form if exists
        const form = modalEl.querySelector('form');
        if (form) form.reset();

        // Reset specific fields for party modal
        if (modalId === 'addPartyModal') {
            const nameInput = modalEl.querySelector('#partyNameInput');
            if (nameInput) {
                nameInput.value = '';
                setTimeout(() => nameInput.focus(), 300);
            }
            // Reset tab to first tab (Address)
            const firstTab = modalEl.querySelector('#party-address-tab');
            if (firstTab && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(firstTab).show();
            }
        }

        // Show modal
        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    } else {
        // Fallback: redirect to page
        const fallbackUrls = {
            'addPartyModal': '/dashboard/parties?action=add',
            'addItemModal':  '/dashboard/items?action=add',
        };
        if (fallbackUrls[modalId]) {
            window.location.href = fallbackUrls[modalId];
        }
    }
});
// ── Shortcuts Modal Functions ──

  // Helper: pill-style badge row for standard shortcuts
  function stdRow(label, mod, key, extraKey, url) {
    const bg = mod === 'ALT' ? '#fba12c' : (mod === 'SHIFT' ? '#2bce6b' : '#3da5ed');
    const radius1 = key ? '4px 0 0 4px' : '4px';
    const radius2 = '0 4px 4px 0';
    let keysHtml = `<span style="background:${bg};color:white;padding:3px 12px;font-size:11px;font-weight:700;border-radius:${radius1};min-width:48px;text-align:center;display:inline-block;">${mod}</span>`;
    if (key) keysHtml += `<span style="padding:3px 10px;font-size:11px;font-weight:700;border:1px solid ${bg};color:${bg};border-radius:${radius2};min-width:22px;text-align:center;display:inline-block;margin-left:-1px;background:#fff;">${key}</span>`;
    if (extraKey) keysHtml += `<span style="padding:3px 10px;font-size:11px;font-weight:700;border:1px solid ${bg};color:${bg};border-radius:${radius2};min-width:22px;text-align:center;display:inline-block;margin-left:2px;background:#fff;">${extraKey}</span>`;
    const clickHandler = url && url !== '#' ? `onclick="handleShortcutNav('${url}')"` : '';
    return `<div class="sc-clickable-row" ${clickHandler} style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;font-size:14px;color:#3c4858;cursor:pointer;padding:4px 6px;border-radius:4px;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
        <span style="font-weight:500;">${label}</span>
        <div style="display:flex;">${keysHtml}</div>
    </div>`;
}


  // Helper: keyboard-key style row for POS shortcuts
  function posRow(label, mod, key) {
    const keyHtml = key
      ? `<span style="border:1px solid #ccc;padding:2px 6px;border-radius:4px;font-size:12px;background:#fff;">${mod}</span> <span style="border:1px solid #ccc;padding:2px 6px;border-radius:4px;font-size:12px;background:#fff;">${key}</span>`
      : `<span style="border:1px solid #ccc;padding:2px 8px;border-radius:4px;font-size:12px;background:#fff;">${mod}</span>`;
    return `<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;color:#666;font-size:13px;">
      <span>${label}</span>
      <div style="display:flex;gap:5px;">${keyHtml}</div>
    </div>`;
  }

  window.toggleShortcutsMenu = function(e) {
    e.preventDefault();
    e.stopPropagation();
    const dd = document.getElementById('shortcutsDropdown');
    if (dd) dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
  };

 window.openShortcutsModal = function(type, e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    document.getElementById('shortcutsDropdown').style.display = 'none';

    const overlay = document.getElementById('shortcutsOverlay');
    const content = document.getElementById('shortcutsContent');

    if (type === 'pos') {
        overlay.style.display = 'block';
        content.style.gridTemplateColumns = '1fr';
        content.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:30px;border-bottom:1px dashed #ddd;padding-bottom:20px;margin-bottom:20px;">
          <div><b style="display:block;margin-bottom:15px;color:#333;">Item Actions</b>
            ${posRow('Change Quantity','F2')}
            ${posRow('Change Discount','F3')}
            ${posRow('Remove Item','F4')}
            ${posRow('Change Price','F5')}
            ${posRow('Change Unit','F6')}
          </div>
          <div><b style="display:block;margin-bottom:15px;color:#333;">Navigation</b>
            ${posRow('Go to item search','F1')}
            ${posRow('Go to customer search','F11')}
            ${posRow('Open New Tab','CTRL','T')}
            ${posRow('Cancel & Close','CTRL','W')}
          </div>
          <div><b style="display:block;margin-bottom:15px;color:#333;">Transaction Actions</b>
            ${posRow('Apply Bill Tax','F7')}
            ${posRow('Apply Bill Discount','F9')}
            ${posRow('Remarks','F12')}
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:30px;">
          <div><b style="display:block;margin-bottom:15px;color:#333;">Payment Actions</b>
            ${posRow('Other/Credit Payments','CTRL','M')}
          </div>
          <div><b style="display:block;margin-bottom:15px;color:#333;">Save Actions</b>
            ${posRow('Save & Print Bill','CTRL','P')}
            ${posRow('Save & New Bill','CTRL','N')}
            ${posRow('Save Bill','CTRL','S')}
          </div>
          <div><b style="display:block;margin-bottom:15px;color:#333;">Other Actions</b>
            ${posRow('Add Customer','CTRL','D')}
          </div>
        </div>`;
        return;
    }

    content.style.gridTemplateColumns = '1fr 1fr 1fr';
    content.style.gap = '50px';
    content.innerHTML = `
        <div style="border-left:4px solid #fba12c; padding-left:20px;">
          <h5 style="color:#5a6578;margin-top:0;margin-bottom:25px;font-weight:700;font-size:14px;text-transform:uppercase;">Actions</h5>
          ${stdRow('Party to Party Transfer','ALT','J','','/dashboard/parties')}
          ${stdRow('Sale','ALT','S','','/dashboard/sale/create')}
          ${stdRow('Purchase','ALT','P','','/dashboard/purchase-bill/create')}
          ${stdRow('Payment-In','ALT','I','','/dashboard/payment-in')}
          ${stdRow('Payment-Out','ALT','O','','/dashboard/payment-out')}
          ${stdRow('Expense','ALT','E','','/dashboard/expense')}
          ${stdRow('Add Party','ALT','N','','/dashboard/parties')}
          ${stdRow('Add Item','ALT','A','','/dashboard/items/create')}
          ${stdRow('Sale Order','ALT','F','','/dashboard/sale-order/create')}
          ${stdRow('Purchase Order','ALT','G','','/dashboard/purchase-order/create')}
          ${stdRow('Delivery Challan','ALT','D','','/dashboard/create-challan')}
          ${stdRow('Estimate','ALT','M','','/dashboard/estimates/create')}
          ${stdRow('Proforma Invoice','ALT','K','','/dashboard/proforma-invoice/create')}
          ${stdRow('Cr. Note/Sale Return','ALT','R','','/dashboard/sale-return/create')}
          ${stdRow('Dr. Note/Purchase Return','ALT','L','','/dashboard/purchase-return/create')}
          ${stdRow('Add Bank Account','ALT','B','','/dashboard/bank-accounts')}
          ${stdRow('POS Billing','ALT','Z','','/dashboard/sales/pos')}
        </div>
        <div style="border-left:4px solid #2bce6b; padding-left:20px;">
          <h5 style="color:#5a6578;margin-top:0;margin-bottom:25px;font-weight:700;font-size:14px;text-transform:uppercase;">Navigation</h5>
          ${stdRow('Home','SHIFT','H','','/dashboard')}
          ${stdRow('Parties','SHIFT','P','','/dashboard/parties')}
          ${stdRow('Items','SHIFT','I','','/dashboard/items')}
          ${stdRow('Reports','SHIFT','R','','/dashboard/reports')}
          ${stdRow('Bank Accounts','SHIFT','B','','/dashboard/bank-accounts')}
          ${stdRow('Cash In Hand','SHIFT','C','','/dashboard/cash-in-hand')}
          ${stdRow('Expenses','SHIFT','E','','/dashboard/expense')}
          ${stdRow('Orders','SHIFT','O','','/dashboard/sale-order')}
          ${stdRow('Estimate/Quotations','SHIFT','S','','/dashboard/sales/estimate')}
          ${stdRow('Cheques','SHIFT','U','','/dashboard/cheques')}
          ${stdRow('Settings','SHIFT','1','','/dashboard/settings/general')}
        </div>
        <div style="border-left:4px solid #3da5ed; padding-left:20px;">
          <h5 style="color:#5a6578;margin-top:0;margin-bottom:25px;font-weight:700;font-size:14px;text-transform:uppercase;">Activities</h5>
          ${stdRow('Privacy','CTRL','Y','','#')}
          ${stdRow('Save','CTRL','S','','#')}
          ${stdRow('Save & New','CTRL','N','','#')}
          ${stdRow('Save & Print','CTRL','P','','#')}
          ${stdRow('Save & Preview','CTRL','R','','#')}
          ${stdRow('Generate Eway Bill','CTRL','E','','#')}
          ${stdRow('Generate e-Invoice','CTRL','I','','#')}
          ${stdRow('Open New Tab','CTRL','T','','#')}
          ${stdRow('Close Current Tab','CTRL','W','','#')}
          ${stdRow('Open Next Tab','CTRL','Tab','','#')}
          ${stdRow('Open Previous Tab','CTRL','SHIFT','Tab','#')}
        </div>`;

    overlay.style.display = 'block';
};
window.closeShortcutsModal = function() {
  const overlay = document.getElementById('shortcutsOverlay');
  if (overlay) overlay.style.display = 'none';
};
window.handleShortcutNav = function(url) {
  if (!url || url === '#') return;
  const overlay = document.getElementById('shortcutsOverlay');
  if (overlay) overlay.style.display = 'none';
  window.location.href = url;
};

  // Close shortcuts dropdown when clicking outside
  document.addEventListener('click', () => {
    const dd = document.getElementById('shortcutsDropdown');
    if (dd) dd.style.display = 'none';
  });
  const styleEl = document.createElement('style');
styleEl.textContent = `
  .sc-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; font-size: 14px; color: #4d5467; }
  .sc-badges { display: flex; gap: 6px; }
  .sc-main-key { padding: 3px 12px; border-radius: 6px; color: white; font-weight: bold; font-size: 11px; min-width: 55px; text-align: center; }
  .sc-char-key { padding: 3px 10px; border-radius: 6px; border: 1px solid #ddd; font-weight: bold; font-size: 12px; background: #fff; min-width: 20px; text-align: center; }
  .bg-orange { background-color: #fbbd5c; } .text-orange { color: #f39c12; border-color: #fbbd5c; }
  .bg-green { background-color: #58d68d; } .text-green { color: #27ae60; border-color: #58d68d; }
  .bg-blue { background-color: #5dade2; } .text-blue { color: #2980b9; border-color: #5dade2; }
`;
document.head.appendChild(styleEl);
window.toggleVersionsMenu = function(e) {
  e.preventDefault();
  e.stopPropagation();

  const dd = document.getElementById('versionsDropdown');
  if (dd) {
    // Toggle between none and block
    const isHidden = dd.style.display === 'none' || dd.style.display === '';
    dd.style.display = isHidden ? 'block' : 'none';
  }
};

// Close it if the user clicks anywhere else
document.addEventListener('click', function() {
  const dd = document.getElementById('versionsDropdown');
  if (dd) dd.style.display = 'none';

  // Also close 3-dots menu
  const threeDotsMenu = document.getElementById('threeDotsMenu');
  if (threeDotsMenu) threeDotsMenu.style.display = 'none';
});

// Prevent 3-dots menu and notifications panel from closing when clicked inside
document.addEventListener('DOMContentLoaded', function() {
  const threeDotsMenu = document.getElementById('threeDotsMenu');
  const notifPanel = document.getElementById('notificationsPanel');
  if (threeDotsMenu) threeDotsMenu.addEventListener('click', e => e.stopPropagation());
  if (notifPanel) notifPanel.addEventListener('click', e => e.stopPropagation());
});
// ── Global Search ──
const globalSearchHTML = `
<div id="globalSearchOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:200000; align-items:center; justify-content:center; font-family:'Segoe UI', sans-serif;">
  <div style="background:#fff; width:650px; border-radius:8px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
    <div style="padding:20px; display:flex; align-items:center;">
      <div style="position:relative; flex:1;">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#1a73e8;"></i>
        <input type="text" id="globalSearchInput" placeholder="Open anything like invoices, reports..."
          style="width:100%; padding:12px 15px 12px 45px; border:2px solid #a0c4ff; border-radius:25px; outline:none; font-size:16px; color:#333; box-shadow:0 0 8px rgba(26,115,232,0.2); box-sizing:border-box;">
      </div>
      <button onclick="closeGlobalSearch()" style="background:none; border:none; color:#9fa6b2; font-size:28px; cursor:pointer; margin-left:15px;">&times;</button>
    </div>
    <div id="searchResultList" style="max-height:450px; overflow-y:auto; padding-bottom:10px;"></div>
  </div>
</div>`;
document.body.insertAdjacentHTML('beforeend', globalSearchHTML);

const searchDatabase = [
  { category: 'Recent Pages', items: [
    { name: 'Sale Invoices', url: '/dashboard/sales' },
    { name: 'Purchase Bill', url: '/dashboard/purchase-bill' },
  ]},
  { category: 'Suggested Pages', items: [
    { name: 'Home', url: '/dashboard' },
    { name: 'Party Details', url: '/dashboard/parties' },
    { name: 'Item Details', url: '/dashboard/items' },
    { name: 'Reports', url: '/dashboard/reports' },
    { name: 'Settings', url: '/dashboard/settings/general' },
  ]},
  { category: 'Sales', items: [
    { name: 'Create Sale Invoice', url: '/dashboard/sale/create' },
    { name: 'Estimate / Quotation', url: '/dashboard/sales/estimate' },
    { name: 'Create Estimate', url: '/dashboard/estimates/create' },
    { name: 'Payment In', url: '/dashboard/payment-in' },
    { name: 'Proforma Invoice', url: '/dashboard/proforma-invoice' },
    { name: 'Sale Order', url: '/dashboard/sale-order' },
    { name: 'Delivery Challan', url: '/dashboard/delivery-challan' },
    { name: 'Sale Return', url: '/dashboard/sale-return' },
    { name: 'POS Billing', url: '/dashboard/sales/pos' },
  ]},
  { category: 'Purchase', items: [
    { name: 'Purchase Bill', url: '/dashboard/purchase-bill' },
    { name: 'Create Purchase Bill', url: '/dashboard/purchase-bill/create' },
    { name: 'Payment Out', url: '/dashboard/payment-out' },
    { name: 'Purchase Return', url: '/dashboard/purchase-return' },
    { name: 'Expense', url: '/dashboard/expense' },
    { name: 'Purchase Order', url: '/dashboard/purchase-order' },
  ]},
  { category: 'Cash & Bank', items: [
    { name: 'Bank Accounts', url: '/dashboard/bank-accounts' },
    { name: 'Cash In Hand', url: '/dashboard/cash-in-hand' },
    { name: 'Loan Accounts', url: '/dashboard/loan-accounts' },
    { name: 'Cheques', url: '/dashboard/cheques' },
  ]},
  { category: 'Utilities', items: [
    { name: 'Import Items', url: '/dashboard/utilities/import-items' },
    { name: 'Barcode Generator', url: '/dashboard/utilities/barcode-generator' },
    { name: 'Import Parties', url: '/dashboard/utilities/import-parties' },
    { name: 'Export Items', url: '/dashboard/utilities/export-items' },
    { name: 'Close Financial Year', url: '/dashboard/utilities/close-financial-year' },
  ]},
];

function updateSearchResults(query = '') {
  const list = document.getElementById('searchResultList');
  let html = '';
  searchDatabase.forEach(section => {
    const filtered = section.items.filter(i => i.name.toLowerCase().includes(query.toLowerCase()));
    if (filtered.length > 0) {
      html += `<div style="padding:15px 20px 5px; font-size:12px; font-weight:700; color:#8a94ad; text-transform:uppercase;">${section.category}</div>`;
      filtered.forEach(item => {
        html += `<a href="${item.url}" class="search-result-item">
          <span>${item.name}</span>
          <i class="fa-solid fa-turn-down" style="font-size:12px; transform:rotate(90deg); color:#8a94ad;"></i>
        </a>`;
      });
    }
  });
  list.innerHTML = html || `<div style="padding:40px; text-align:center; color:#999;">No results found for "${query}"</div>`;
}

const finalSearchStyles = `
  .search-result-item { display:flex; justify-content:space-between; align-items:center; padding:12px 25px; text-decoration:none; color:#444; font-size:15px; }
  .search-result-item i { display:none; }
  .search-result-item:hover { background:#f0f7ff; color:#1a73e8; }
  .search-result-item:hover i { display:block; }
`;
const searchStyleTag = document.createElement('style');
searchStyleTag.innerText = finalSearchStyles;
document.head.appendChild(searchStyleTag);
// ── 3-Dots Menu CSS ──
const extraStyles = `
  .menu-item { padding:15px 20px; cursor:pointer; color:#444; font-size:14px; transition: background 0.2s; }
  .menu-item:hover { background:#f5f7fa; }
  .menu-item i { margin-right:12px; width:20px; color:#666; }
  .vy-switch { position:relative; display:inline-block; width:34px; height:20px; }
  .vy-switch input { opacity:0; width:0; height:0; }
  .vy-slider { position:absolute; cursor:pointer; inset:0; background-color:#ccc; transition:.4s; border-radius:20px; }
  .vy-slider:before { position:absolute; content:""; height:14px; width:14px; left:3px; bottom:3px; background-color:white; transition:.4s; border-radius:50%; }
  input:checked + .vy-slider { background-color:#1a73e8; }
  input:checked + .vy-slider:before { transform:translateX(14px); }
`;
const extraStyleTag = document.createElement('style');
extraStyleTag.innerText = extraStyles;
document.head.appendChild(extraStyleTag);

window.openGlobalSearch = function() {
  const overlay = document.getElementById('globalSearchOverlay');
  overlay.style.display = 'flex';
  updateSearchResults('');
  setTimeout(() => document.getElementById('globalSearchInput').focus(), 50);
};
window.closeGlobalSearch = function() {
  document.getElementById('globalSearchOverlay').style.display = 'none';
};
document.addEventListener('input', (e) => {
  if (e.target.id === 'globalSearchInput') updateSearchResults(e.target.value);
});

const sidebarSearch = document.getElementById('sidebarSearch');
if (sidebarSearch) {
  sidebarSearch.addEventListener('click', (e) => { e.preventDefault(); openGlobalSearch(); });
}
document.getElementById('globalSearchOverlay').addEventListener('click', (e) => {
  if (e.target.id === 'globalSearchOverlay') closeGlobalSearch();
});
// ── 3-Dots Menu, Privacy Overlay, Notifications Panel ──
const extraComponentsHTML = `
<div id="threeDotsMenu" style="display:none; position:fixed; top:60px; right:20px; width:220px; background:#fff; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.15); z-index:1000001; font-family:sans-serif; overflow:hidden; border:1px solid #eee;">
    <div class="menu-item" onclick="openNotifications()">
        <i class="fa-regular fa-bell"></i> Notifications Ok
    </div>

    <div class="menu-item" onclick="window.location.href='/dashboard/settings/party-reminders'">
    <i class="fa-regular fa-credit-card"></i> Payment Reminder
</div>
    <div class="menu-item" style="display:flex; justify-content:space-between; align-items:center;">
        <span><i class="fa-regular fa-eye"></i> Privacy</span>
        <label class="vy-switch">
            <input type="checkbox" id="privacyToggle" onchange="togglePrivacyMode(this.checked)">
            <span class="vy-slider"></span>
        </label>
    </div>
    <div class="menu-item" onclick="window.location.href='/dashboard/settings/general'">
    <i class="fa-solid fa-gear"></i> Settings
</div>
</div>


<div id="notificationsPanel" style="display:none; position:fixed; top:60px; right:20px; width:400px; background:#fff; border-radius:8px; box-shadow:0 15px 50px rgba(0,0,0,0.2); z-index:1000002; font-family:sans-serif; border:1px solid #ddd;">
    <div style="padding:15px 20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
        <b style="font-size:16px; color:#333;">Notifications</b>
        <button onclick="closeNotifications()" style="background:none; border:none; font-size:20px; color:#999; cursor:pointer;">&times;</button>
    </div>
    <div id="notificationsPanelBody" style="padding:20px; max-height: 70vh; overflow-y: auto;">
        <div style="padding:40px 20px; text-align:center;">
            <div style="background:#f8f9fa; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i class="fa-regular fa-bell" style="font-size:35px; color:#ccc;"></i>
            </div>
            <b style="display:block; margin-bottom:10px; color:#333;">No Notifications yet!</b>
            <p style="color:#777; font-size:13px;">Stay tuned! Notifications about your activity will show up here.</p>
        </div>
    </div>

</div>


`;
document.body.insertAdjacentHTML('beforeend', extraComponentsHTML);
// ── Privacy Overlay (offset-aware, non-destructive) ──
document.body.insertAdjacentHTML('beforeend', `
<div id="privacyOverlay" style="
    display: none;
    position: fixed;
    top: 50px;
    left: var(--sidebar-width, 250px);
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 99998;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;">
    <div style="background:#fff; padding:30px; border-radius:50%; box-shadow:0 10px 30px rgba(0,0,0,0.08); margin-bottom:25px; display:flex; align-items:center; justify-content:center;">
        <i class="fa-solid fa-eye-slash" style="font-size:50px; color:#1a73e8;"></i>
    </div>
    <div style="background:#54595e; color:#fff; padding:15px 30px; border-radius:4px; text-align:center; font-size:14px; line-height:1.5; max-width:400px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
        Privacy Mode enabled.<br>
        Please toggle the Privacy button to view your Dashboard.
    </div>
</div>`);

// ── 3-Dots Menu Logic ──
window.toggleThreeDotsMenu = function(e) {
    e.stopPropagation();
    const menu = document.getElementById('threeDotsMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
};

window.togglePrivacyMode = function(isEnabled) {
    const overlay = document.getElementById('privacyOverlay');
    if (overlay) {
        overlay.style.display = isEnabled ? 'flex' : 'none';
    }
    const menu = document.getElementById('threeDotsMenu');
    if (menu) menu.style.display = 'none';
};

window.openNotifications = function() {
    document.getElementById('threeDotsMenu').style.display = 'none';
    const panel = document.getElementById('notificationsPanel');
    const body = document.getElementById('notificationsPanelBody');
    const escapeText = (value = '') => String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
    if (panel) panel.style.display = 'block';
    if (body) {
      body.innerHTML = '<div style="padding:40px 20px; text-align:center; color:#777;">Loading reminders...</div>';
    }

    fetch('/dashboard/parties/reminders/notifications', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error('Unable to load notifications.')))
      .then((data) => {
        const items = Array.isArray(data?.items) ? data.items : [];
        if (!body) return;
        if (!items.length) {
          body.innerHTML = `
            <div style="padding:40px 20px; text-align:center;">
              <div style="background:#f8f9fa; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i class="fa-regular fa-bell" style="font-size:35px; color:#ccc;"></i>
              </div>
              <b style="display:block; margin-bottom:10px; color:#333;">No Notifications yet!</b>
              <p style="color:#777; font-size:13px;">Stay tuned! Notifications about your activity will show up here.</p>
            </div>`;
          return;
        }

        body.innerHTML = items.map((item) => {
          const amount = Number(item.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          const dueDate = item.reminder_date ? new Date(item.reminder_date).toLocaleDateString() : '-';
          const waLink = item.whatsapp_url || '';
          return `
            <div style="border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:10px; background:#fff;">
              <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                <div>
                  <div style="font-weight:700; color:#111827;">${escapeText(item.name || 'Party')}</div>
                  <div style="font-size:12px; color:#6b7280;">Due: ${escapeText(dueDate)}${item.sent_at ? ' • Sent' : ''}</div>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:700; color:#dc2626;">Rs ${amount}</div>
                  ${waLink ? `<a href="${waLink}" target="_blank" rel="noopener" style="font-size:12px; color:#25D366; text-decoration:none;"><i class="fa-brands fa-whatsapp"></i> Send</a>` : ''}
                </div>
              </div>
            </div>`;
        }).join('');
      })
      .catch(() => {
        if (body) {
          body.innerHTML = '<div style="padding:40px 20px; text-align:center; color:#dc2626;">Unable to load notifications.</div>';
        }
      });
};

window.closeNotifications = function() {
    document.getElementById('notificationsPanel').style.display = 'none';
};
// Emergency click-to-dismiss Privacy Mode overlay
document.addEventListener('DOMContentLoaded', () => {
  const privacyOverlay = document.getElementById('privacyOverlay');
  if (privacyOverlay) {
    privacyOverlay.addEventListener('click', () => {
      privacyOverlay.style.display = 'none';
      const toggleCheckbox = document.getElementById('privacyToggle');
      if (toggleCheckbox) toggleCheckbox.checked = false;
    });
  }
});
window.toggleHelpMenu = function(e) {
  e.preventDefault();
  e.stopPropagation();
  const dd = document.getElementById('helpDropdown');
  if (dd) dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
};

window.handleHelpNav = function(url) {
  const dd = document.getElementById('helpDropdown');
  if (dd) dd.style.display = 'none';
  if (url && url !== '#') window.location.href = url;
};

document.addEventListener('click', function() {
  const helpDd = document.getElementById('helpDropdown');
  if (helpDd) helpDd.style.display = 'none';
});
})();
