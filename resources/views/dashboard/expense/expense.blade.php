<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vyapar — Expenses</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

  @if(file_exists(public_path('css/styles.css')))
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  @endif

  @if(!empty($startInCreate))
  <style>
    #emptyState,
    #splitPane { display: none !important; }
  </style>
  @endif

  <script>
    function removePaymentRow(i) {
  paymentRows.splice(i, 1);
  renderPaymentCard();
}

function openAddBankModal(targetSelect = null) {
  window.__expenseBankTargetSelect = targetSelect || null;
  let m = document.getElementById('addBankModal');
  if (!m) {
    m = document.createElement('div');
    m.id = 'addBankModal';
    m.className = 'modal-overlay';
    m.style.zIndex = '1200';
    m.innerHTML = `
      <div class="modal-box" style="width:780px;max-width:96vw;padding:32px 36px 28px;border-radius:12px;">
        <button onclick="closeModal('addBankModal')" style="position:absolute;top:16px;right:18px;background:none;border:none;font-size:22px;cursor:pointer;color:#888;line-height:1;">&#x2715;</button>
        <div style="font-size:17px;font-weight:700;color:#1a1f36;margin-bottom:28px;">Add Bank Account</div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
          <div style="position:relative;">
            <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#e53935;padding:0 4px;z-index:1;font-weight:600;">Account Display Name *</label>
            <input id="bankAccName" type="text" placeholder="Enter Account Display Name"
              style="width:100%;border:1.5px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
          </div>
          <div style="position:relative;">
            <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">Opening Balance</label>
            <input id="bankOpenBal" type="number" placeholder="Enter Opening Balance"
              style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
          </div>
          <div style="position:relative;">
            <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">As of Date</label>
            <input id="bankAsOfDate" type="date" value="${new Date().toISOString().slice(0,10)}"
              style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <button onclick="toggleBankMoreFields()" id="bankMoreFieldsBtn"
            style="background:none;border:none;color:#2563eb;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;padding:0;">
            <span style="font-size:18px;font-weight:300;line-height:1;">+</span> Add more fields
          </button>
          <div id="bankMoreFields" style="display:none;margin-top:20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
              <div style="position:relative;">
                <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#e53935;padding:0 4px;z-index:1;font-weight:600;">Account Number *</label>
                <input id="bankAccNo" type="text" placeholder="Enter Account Number"
                  style="width:100%;border:1.5px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
              </div>
              <div style="position:relative;">
                <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">SWIFT Code</label>
                <input id="bankSwift" type="text" placeholder="Enter SWIFT"
                  style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
              </div>
              <div style="position:relative;">
                <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">IBAN</label>
                <input id="bankIban" type="text" placeholder="Enter IBAN"
                  style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
              <div style="position:relative;">
                <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">Bank Name</label>
                <input id="bankName" type="text" placeholder="Enter Bank Name"
                  style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
              </div>
              <div style="position:relative;">
                <label style="position:absolute;top:-9px;left:12px;background:#fff;font-size:10px;color:#888;padding:0 4px;z-index:1;">Account Holder Name</label>
                <input id="bankHolder" type="text" placeholder="Enter Account Holder Name"
                  style="width:100%;border:1px solid #ccc;border-radius:8px;padding:14px 12px 10px;font-size:13px;outline:none;">
              </div>
            </div>
          </div>
        </div>

        <label style="display:flex;align-items:center;gap:10px;font-size:13px;cursor:pointer;margin-bottom:28px;color:#333;">
          <input type="checkbox" id="bankPrintDetails" style="accent-color:#2563eb;width:16px;height:16px;">
          Print Bank Details on Invoices
          <span style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;border:1px solid #aaa;color:#888;font-size:10px;cursor:help;" title="Bank details will be printed on invoice">&#9432;</span>
        </label>

        <div style="border-top:1px solid #f0f0f0;padding-top:20px;display:flex;justify-content:flex-end;gap:12px;">
          <button onclick="closeModal('addBankModal')"
            style="border:1px solid #ccc;background:#fff;border-radius:25px;padding:10px 28px;font-size:13px;cursor:pointer;color:#555;font-weight:500;">
            Cancel
          </button>
          <button onclick="saveAddBank()"
            style="border:none;background:#D4112E;color:#fff;border-radius:25px;padding:10px 28px;font-size:13px;font-weight:600;cursor:pointer;">
            Save Details
          </button>
        </div>
      </div>`;
    document.body.appendChild(m);
  }
  const nameInput = document.getElementById('bankAccName');
  const openBalInput = document.getElementById('bankOpenBal');
  const asOfDateInput = document.getElementById('bankAsOfDate');
  const accNoInput = document.getElementById('bankAccNo');
  const swiftInput = document.getElementById('bankSwift');
  const ibanInput = document.getElementById('bankIban');
  const bankNameInput = document.getElementById('bankName');
  const holderInput = document.getElementById('bankHolder');
  const printInput = document.getElementById('bankPrintDetails');
  if (nameInput) nameInput.value = '';
  if (openBalInput) openBalInput.value = '0';
  if (asOfDateInput) asOfDateInput.value = new Date().toISOString().slice(0, 10);
  if (accNoInput) accNoInput.value = '';
  if (swiftInput) swiftInput.value = '';
  if (ibanInput) ibanInput.value = '';
  if (bankNameInput) bankNameInput.value = '';
  if (holderInput) holderInput.value = '';
  if (printInput) printInput.checked = false;
  openModal('addBankModal');
}

function toggleBankMoreFields() {
  const el = document.getElementById('bankMoreFields');
  const btn = document.getElementById('bankMoreFieldsBtn');
  if (el.style.display === 'none') {
    el.style.display = 'block';
    btn.innerHTML = '<span style="font-size:18px;font-weight:300;line-height:1;">−</span> Hide extra fields';
  } else {
    el.style.display = 'none';
    btn.innerHTML = '<span style="font-size:18px;font-weight:300;line-height:1;">+</span> Add more fields';
  }
}

function saveAddBank() {
  const nameInput = document.getElementById('bankAccName');
  const openBalInput = document.getElementById('bankOpenBal');
  const asOfDateInput = document.getElementById('bankAsOfDate');
  const accNoInput = document.getElementById('bankAccNo');
  const swiftInput = document.getElementById('bankSwift');
  const ibanInput = document.getElementById('bankIban');
  const bankNameInput = document.getElementById('bankName');
  const holderInput = document.getElementById('bankHolder');
  const printInput = document.getElementById('bankPrintDetails');
  const name = nameInput?.value.trim();
  if (!name) { showToast('Account Display Name is required.', 'red'); return; }

  const formData = new FormData();
  formData.append('display_name', name);
  formData.append('opening_balance', openBalInput?.value || '0');
  formData.append('as_of_date', asOfDateInput?.value || new Date().toISOString().slice(0, 10));
  formData.append('account_number', accNoInput?.value || '');
  formData.append('swift_code', swiftInput?.value || '');
  formData.append('iban', ibanInput?.value || '');
  formData.append('bank_name', bankNameInput?.value || '');
  formData.append('account_holder_name', holderInput?.value || '');
  if (printInput?.checked) formData.append('print_on_invoice', '1');

  const submitBtn = document.querySelector('#addBankModal button[onclick="saveAddBank()"]');
  if (submitBtn) submitBtn.disabled = true;

  fetch("{{ route('bank-accounts.store') }}", {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': CSRF,
      'Accept': 'application/json',
    },
    body: formData,
  }).then(async (res) => {
    const raw = await res.text();
    let payload = {};
    try { payload = raw ? JSON.parse(raw) : {}; } catch (_) { payload = { message: raw || '' }; }
    if (!res.ok || payload.success === false) {
      const message = payload.message || Object.values(payload.errors || {}).flat().filter(Boolean)[0] || 'Bank account save failed.';
      throw new Error(message);
    }
    return payload;
  }).then((payload) => {
    const bank = payload.bank || payload;
    const label = bank.display_with_account || bank.display_name || bank.bank_name || `Bank ${bank.id}`;
    expenseBankAccounts.push({
      id: bank.id,
      display_name: bank.display_name || '',
      display_with_account: label,
      bank_name: bank.bank_name || '',
      account_number: bank.account_number || '',
    });

    const select = window.__expenseBankTargetSelect;
    if (select) {
      const optionValue = `bank:${bank.id}`;
      const existing = Array.from(select.options).find(opt => opt.value === optionValue);
      if (!existing) {
        const opt = document.createElement('option');
        opt.value = optionValue;
        opt.textContent = label;
        const optGroup = Array.from(select.querySelectorAll('optgroup')).find(g => g.label === 'Bank Accounts');
        if (optGroup) optGroup.appendChild(opt);
        else select.appendChild(opt);
      }
      select.value = optionValue;
      const rowIndex = select.dataset.paymentRowIndex ? parseInt(select.dataset.paymentRowIndex, 10) : 0;
      if (!Number.isNaN(rowIndex)) {
        payRowChange(rowIndex, 'type', optionValue);
      }
    }

    window.__expenseBankTargetSelect = null;
    showToast(payload.message || 'Bank account added successfully.', 'green');
    closeModal('addBankModal');
  }).catch((err) => {
    showToast(err?.message || 'Bank account save failed.', 'red');
  }).finally(() => {
    if (submitBtn) submitBtn.disabled = false;
  });
}
    const authUser = @json(Auth::user());
    window.App = window.App || {
      isAuthenticated: @json(Auth::check()),
      user: authUser ? {
        id: authUser.id,
        name: authUser.name,
        roles: @json(Auth::user()?->roles()->pluck('name')->toArray() ?? []),
        permissions: @json(Auth::user()?->getAllPermissions() ?? []),
      } : { id: null, name: null, roles: [], permissions: [] },
      logoutUrl: "{{ route('logout') }}",
      csrfToken: "{{ csrf_token() }}",
    };

    // API URLs passed to JS
    window.expenseRoutes = {
      categoryStore:   "{{ route('expense.categories.store') }}",
      categoryDestroy: "{{ url('dashboard/expense/categories') }}",
      itemStore:       "{{ route('expense.items.store') }}",
      itemUpdate:      "{{ url('dashboard/expense/items') }}",
      itemDestroy:     "{{ url('dashboard/expense/items') }}",
      partyStore:      "{{ route('parties.store') }}",
      partyGroupStore: "{{ route('party-groups.store') }}",
      expenseSave:     "{{ route('expense.save') }}",
      expense:         "{{ route('expense') }}",
      expenseCreate:   "{{ route('expense.create') }}",
      expenseDestroy:  "{{ url('dashboard/expense') }}",
    };
    window.expenseBootstrap = {!! json_encode([
      'parties' => $parties ?? [],
      'partyGroups' => $partyGroups ?? [],
      'bankAccounts' => $bankAccounts ?? [],
      'taxRates' => $taxRates ?? [],
      'transactionSettings' => $transactionSettings ?? [],
      'hasTaxRates' => !empty($taxRates ?? []),
    ]) !!};
    window.expenseStartInCreate = @json(!empty($startInCreate));
  </script>

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 14px; }
    body { display: flex; flex-direction: column; overflow: hidden; background: #f5f5f5; }
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; height: 100%; }

    /* EMPTY STATE */
    #emptyState { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; min-height: calc(100vh - 56px); background: #fff; text-align: center; padding: 40px; }
    #emptyState .empty-svg { width: 120px; height: 120px; margin-bottom: 28px; }
    #emptyState h4 { font-size: 15px; font-weight: 600; color: #333; margin-bottom: 8px; }
    #emptyState p  { font-size: 13px; color: #888; margin-bottom: 30px; }
    .btn-empty-add { background: #D4112E; color: #fff; border: none; border-radius: 25px; padding: 13px 50px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
    .btn-empty-add:hover { background: #b30e26; color: #fff; }
    .btn-empty-add .plus-icon { font-size: 20px; font-weight: 300; line-height: 1; }

    /* SPLIT PANE */
    #splitPane { display: none; flex: 1; flex-direction: column; overflow: hidden; }
    .exp-tabs-bar { display: flex; background: #fff; border-bottom: 2px solid #e0e0e0; flex-shrink: 0; }
    .exp-tab-btn { flex: 1; text-align: center; padding: 11px 0; font-size: 13px; font-weight: 600; color: #999; cursor: pointer; border: none; border-bottom: 3px solid transparent; letter-spacing: .5px; background: none; margin-bottom: -2px; transition: color .15s; }
    .exp-tab-btn.active { color: #1a1f36; border-bottom-color: #1a1f36; }
    .split-layout { display: flex; flex: 1; overflow: hidden; }
    .split-left { width: 310px; min-width: 310px; background: #fff; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; height: 100%; }
    .split-left-header { padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8e8e8; flex-shrink: 0; }
    .sl-search-icon { color: #555; font-size: 16px; cursor: pointer; background: none; border: none; }
    .btn-add-expense-red { background: #D4112E; color: #fff; border: none; border-radius: 20px; padding: 7px 14px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .btn-add-expense-red:hover { background: #b30e26; }
    .btn-add-expense-red .plus-icon { font-size: 16px; font-weight: 300; }
    .split-left-cols { display: flex; justify-content: space-between; align-items: center; padding: 7px 14px 6px; color: #888; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e8e8e8; flex-shrink: 0; }
    .slc-left, .slc-right { display: flex; align-items: center; gap: 4px; position: relative; }
    .slc-right { margin-left: auto; }
    .slc-filter-wrap { position: relative; display: inline-flex; align-items: center; gap: 2px; }
    .slc-sort-icon { font-size: 10px; color: #9aa0aa; line-height: 1; }
    .category-list { flex: 1; overflow-y: auto; overflow-x: visible; }
    .category-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background .1s; position: relative; overflow: visible; }
    .category-item:hover { background: #f8f9ff; }
    .category-item.active { background: #eef2ff; }
    .cat-name { font-size: 13px; color: #333; }
    .cat-right { display: flex; align-items: center; gap: 8px; }
    .cat-amount { font-size: 13px; color: #333; min-width: 24px; text-align: right; }
    .cat-dots-wrap { position: relative; z-index: 5; flex-shrink: 0; }
    .cat-dots-btn { background: none; border: none; cursor: pointer; color: #aaa; font-size: 13px; padding: 2px 4px; }
    .cat-dots-btn:hover { color: #555; }
    .cat-dots-menu { position: absolute; right: 0; top: 22px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.12); z-index: 400; min-width: 120px; display: none; }
    .cat-dots-menu.open { display: block; }
    .cat-dots-item { padding: 9px 14px; font-size: 13px; cursor: pointer; color: #333; border-bottom: 1px solid #f5f5f5; }
    .cat-dots-item:last-child { border-bottom: none; }
    .cat-dots-item:hover { background: #f5f5f5; }
    .cat-dots-item.danger { color: #D4112E; }
    .split-right { flex: 1; background: #fff; display: flex; flex-direction: column; overflow: hidden; }
    .detail-header { padding: 12px 20px 10px; border-bottom: 1px solid #e8e8e8; display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0; }
    .detail-cat-title { font-size: 15px; font-weight: 700; color: #1a1f36; text-transform: uppercase; }
    .detail-cat-type  { font-size: 12px; color: #888; margin-top: 2px; }
    .detail-totals    { text-align: right; font-size: 13px; color: #555; }
    .detail-totals .red-amt { color: #D4112E; font-weight: 600; }
    .detail-search-bar { padding: 10px 16px; border-bottom: 1px solid #e8e8e8; flex-shrink: 0; }
    .detail-search-input-wrap { border: 1px solid #e0e0e0; border-radius: 5px; padding: 6px 10px; display: flex; align-items: center; gap: 6px; width: 220px; }
    .detail-search-input-wrap input { border: none; outline: none; font-size: 13px; flex: 1; background: none; color: #333; }
    .detail-table-wrap { flex: 1; overflow-y: auto; overflow-x: visible; }
    .detail-table { width: 100%; border-collapse: collapse; }
    .detail-table th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #555; border-bottom: 1px solid #e8e8e8; white-space: nowrap; background: #fff; position: sticky; top: 0; text-transform: uppercase; letter-spacing: .3px; }
    .th-filter { color: #aaa; font-size: 10px; margin-left: 3px; cursor: pointer; }
    .detail-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f0f0f0; color: #333; }
    .detail-table tr:hover td { background: #f8f9ff; }
    .detail-table tr.row-highlight td { background: #eef2ff; }
    .td-action-btn { background: none; border: none; cursor: pointer; color: #aaa; font-size: 13px; padding: 2px 4px; position: relative; }
    .td-action-btn:hover { color: #555; }

    /* ── SORT ARROWS ── */
    .th-sort { display: inline-flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; margin-left: 3px; vertical-align: middle; gap: 0; line-height: 1; }
    .th-sort .sa-up, .th-sort .sa-dn { font-size: 8px; color: #bbb; line-height: 1; display: block; }
    .th-sort .sa-up.active { color: #1a1f36; }
    .th-sort .sa-dn.active { color: #1a1f36; }

    /* ── FILTER POPOVER ── */
    .th-wrap { position: relative; display: inline-flex; align-items: center; gap: 2px; }
    .filter-popover { display: none; position: absolute; top: 28px; left: 0; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,.13); z-index: 400; min-width: 200px; padding: 0; }
    .filter-popover.open { display: block; }
    .filter-pop-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px 8px; border-bottom: 1px solid #f0f0f0; }
    .filter-pop-title { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .3px; }
    .filter-pop-close { background: none; border: none; cursor: pointer; color: #888; font-size: 15px; line-height: 1; padding: 0 2px; }
    .filter-pop-close:hover { color: #333; }
    .filter-pop-body { padding: 10px 14px; }
    .filter-pop-select { width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 7px 10px; font-size: 13px; color: #333; background: #fff; outline: none; margin-bottom: 8px; cursor: pointer; }
    .filter-pop-input { width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 7px 10px; font-size: 13px; color: #333; outline: none; margin-bottom: 8px; }
    .filter-pop-input:focus { border-color: #2563eb; }
    .filter-pop-checkbox-row { display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 13px; color: #333; cursor: pointer; }
    .filter-pop-checkbox-row input[type=checkbox] { accent-color: #2563eb; width: 15px; height: 15px; cursor: pointer; }
    .filter-pop-footer { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 8px 14px 10px; border-top: 1px solid #f0f0f0; }
    .filter-pop-clear { background: #fff; border: 1px solid #ccc; border-radius: 20px; padding: 5px 16px; font-size: 12px; cursor: pointer; color: #555; }
    .filter-pop-clear:hover { background: #f5f5f5; }
    .filter-pop-apply { background: #D4112E; border: none; border-radius: 20px; padding: 5px 16px; font-size: 12px; font-weight: 600; cursor: pointer; color: #fff; }
    .filter-pop-apply:hover { background: #b30e26; }

    /* Transaction row dropdown */
    .td-row-menu { position: fixed; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,.14); z-index: 1000; min-width: 150px; display: none; }
    .td-row-menu.open { display: block; }
    .td-row-menu-item { padding: 10px 16px; font-size: 13px; cursor: pointer; color: #333; border-bottom: 1px solid #f5f5f5; white-space: nowrap; }
    .td-row-menu-item:last-child { border-bottom: none; }
    .td-row-menu-item:hover { background: #f5f5f5; }
    .td-row-menu-item.danger { color: #D4112E; }

    /* ═══════════════════════════════════════════
       EXPENSE FORM — FULL SCREEN OVERLAY
       ═══════════════════════════════════════════ */
  #expenseFormPage {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1050;
  flex-direction: column;
  background: #f5f5f5;
  overflow: hidden;
}

    .form-tabs-bar { background: #fff; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; padding: 0 16px; flex-shrink: 0; min-height: 40px; }
    .form-tab { display: flex; align-items: center; gap: 6px; padding: 10px 12px; font-size: 13px; color: #555; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; margin-bottom: -1px; }
    .form-tab.active { color: #1a1f36; border-bottom-color: #1a1f36; font-weight: 600; }
    .form-tab-close { color: #888; font-size: 13px; cursor: pointer; line-height: 1; margin-left: 2px; }
    .form-tab-close:hover { color: #D4112E; }
    .form-tab-add { width: 26px; height: 26px; background: #2563eb; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; margin-left: 6px; user-select: none; }
    .form-tab-add:hover { background: #1d4ed8; }
    .form-body { flex: 1; overflow-y: auto; padding: 24px 32px; }
    .form-title { font-size: 20px; font-weight: 600; color: #222; margin-bottom: 24px; }

    /* ── FORM TOP ROW ── */
    .form-top-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; gap: 16px; }

    /* ── CATEGORY SELECT ── */
    .form-cat-wrap { position: relative; display: inline-block; }
    .form-cat-select {
      border: 1px solid #aaa;
      border-radius: 6px;
      padding: 20px 36px 8px 12px;
      font-size: 13px;
      background: #fff;
      min-width: 220px;
      min-height: 54px;
      cursor: pointer;
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      color: #333;
      user-select: none;
      position: relative;
    }
    .form-cat-select.filled { color: #1a1f36; border-color: #aaa; }
    .form-cat-label {
  position: absolute;
  top: 6px;
  left: 12px;
  background: #fff;
  font-size: 10px;
  color: #e53935;
  padding: 0 2px;
  pointer-events: none;
  font-weight: 500;
}
    .form-cat-dropdown { position: absolute; top: 58px; left: 0; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,.12); z-index: 200; min-width: 220px; display: none; }
    .form-cat-dropdown.open { display: block; }
    .cat-dd-add-row { display: flex; align-items: center; gap: 6px; padding: 10px 14px; color: #2563eb; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
    .cat-dd-add-row:hover { background: #f5f5f5; }
    .cat-option { padding: 9px 14px; font-size: 13px; cursor: pointer; color: #333; }
    .cat-option:hover { background: #f5f5f5; }

    /* ── DATE WRAP ── */
    .form-date-wrap { position: relative; text-align: right; }
    .form-exp-no-row {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
      font-size: 13px;
      color: #555;
      margin-bottom: 10px;
    }
    .form-exp-no-label { font-size: 13px; color: #555; white-space: nowrap; }
    .form-exp-no-input {
      border: 1.5px solid #1a1f36;
      border-radius: 4px;
      padding: 6px 10px;
      font-size: 13px;
      width: 200px;
      outline: none;
      color: #333;
      background: #fff;
    }
    .form-exp-no-input:focus { border-color: #2563eb; }
    .form-date-row { display: flex; align-items: center; justify-content: flex-end; gap: 10px; font-size: 13px; color: #555; }
    .form-date-val { font-weight: 400; color: #1a1f36; font-size: 14px; }
    .form-date-icon { color: #2563eb; cursor: pointer; font-size: 18px; }
    .calendar-popup { display: none; position: absolute; right: 0; top: 52px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,.14); z-index: 300; padding: 14px; width: 240px; }
    .calendar-popup.open { display: block; }
    .cal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; font-size: 13px; font-weight: 600; }
    .cal-nav { background: none; border: none; cursor: pointer; color: #555; font-size: 16px; padding: 2px 6px; }
    .cal-nav:hover { color: #1a1f36; }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .cal-day-name { font-size: 11px; color: #888; text-align: center; padding: 2px 0; }
    .cal-day { font-size: 12px; text-align: center; padding: 5px 2px; cursor: pointer; border-radius: 50%; }
    .cal-day:hover { background: #f0f4ff; }
    .cal-day.selected { background: #2563eb; color: #fff; }
    .cal-day.other-month { color: #ccc; }

    /* ── ITEMS TABLE ── */
    .form-items-wrap { border: 1px solid #e0e0e0; border-radius: 6px 6px 0 0; overflow: visible; position: relative; }
    .form-items-table { width: 100%; border-collapse: collapse; background: #fff; }
    .form-items-table th { background: #f5f5f5; padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #555; border-bottom: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; text-transform: uppercase; letter-spacing: .3px; }
    .form-items-table th:last-child { border-right: none; }
    .form-items-table td { padding: 6px 8px; border-bottom: 1px solid #e8e8e8; border-right: 1px solid #e8e8e8; }
    .form-items-table td:last-child { border-right: none; }
    .form-items-table td input { border: none; border-radius: 0; padding: 6px 8px; font-size: 13px; width: 100%; outline: none; background: transparent; }
    .form-items-table td input:focus { background: #f0f4ff; }
    .form-items-table td input[readonly] { background: transparent; color: #555; }
    .col-hash { width: 50px; } .col-item { } .col-qty { width: 120px; } .col-price { width: 160px; } .col-amount { width: 140px; }

    /* ── ITEMS FOOTER ── */
    .items-footer-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      border: 1px solid #e0e0e0;
      border-top: none;
      padding: 10px 14px;
      border-radius: 0 0 6px 6px;
    }
    .btn-add-row { border: 1.5px solid #2563eb; color: #2563eb; background: none; border-radius: 4px; padding: 5px 14px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .btn-add-row:hover { background: #eff6ff; }
    .items-total-label { font-size: 13px; font-weight: 600; color: #555; display: flex; align-items: center; gap: 0; }
    .btn-row-sort { background: none; border: none; cursor: pointer; color: #bbb; font-size: 14px; margin-right: 4px; }
    .btn-row-del { background: none; border: none; cursor: pointer; color: #bbb; font-size: 14px; }
    .btn-row-del:hover { color: #D4112E; }
    .item-dd-wrap { position: relative; }
    .item-dd-list { position: absolute; top: 34px; left: 0; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.1); z-index: 500; min-width: 220px; display: none; max-height: 280px; overflow: auto; }
    .item-dd-list.open { display: block; }
    .item-dd-add-row { display: flex; align-items: center; gap: 6px; padding: 9px 12px; color: #2563eb; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
    .item-dd-add-row:hover { background: #f5f5f5; }
    .item-option { padding: 8px 12px; font-size: 13px; cursor: pointer; color: #333; display: flex; justify-content: space-between; }
    .item-option:hover { background: #f5f5f5; }
    .item-option .item-price { color: #888; font-size: 12px; }

    /* ── PAYMENT SECTION ── */
    .payment-section { display: flex; align-items: flex-start; justify-content: space-between; margin-top: 24px; gap: 16px; flex-wrap: wrap; }
    .payment-card { border: none; border-radius: 0; padding: 0; background: transparent; min-width: 240px; }
    .payment-row-wrap { margin-bottom: 10px; }
    .payment-row { display: flex; align-items: center; gap: 10px; margin-bottom: 0; }
    .payment-field { position: relative; flex: 1; }
    .payment-field-label {
      position: absolute;
      top: 6px;
      left: 12px;
      background: #f5f5f5;
      font-size: 10px;
      color: #555;
      padding: 0 2px;
      z-index: 1;
      pointer-events: none;
    }
    .payment-type-select {
      border: 1px solid #aaa;
      border-radius: 6px;
      padding: 20px 30px 8px 12px;
      font-size: 13px;
      min-width: 200px;
      min-height: 54px;
      cursor: pointer;
      color: #1a1f36;
      background: #fff;
      outline: none;
      appearance: none;
      width: 100%;
    }
    .payment-amount-input { display: none; }
    .btn-payment-del { display: none; }
    .ref-no-input {
      border: 1px solid #bbb;
      border-radius: 6px;
      padding: 10px 12px;
      font-size: 13px;
      width: 200px;
      outline: none;
      margin-top: 8px;
      margin-bottom: 0;
      display: block;
      background: #fff;
    }
    .ref-no-input::placeholder { color: #bbb; }
    .btn-add-payment-type { background: none; border: none; color: #2563eb; font-size: 13px; cursor: pointer; padding: 0; display: flex; align-items: center; gap: 4px; margin-top: 10px; }
    .payment-card-footer { display: flex; align-items: center; justify-content: flex-start; margin-top: 8px; padding-top: 0; border-top: none; }
    .payment-total-text { display: none; }

    /* ── TOTAL BLOCK ── */
    .total-block { display: flex; align-items: center; gap: 14px; }
    .round-off-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; }
    .round-val {
      border: 1px solid #bbb;
      border-radius: 4px;
      padding: 6px 10px;
      width: 70px;
      text-align: right;
      font-size: 13px;
      outline: none;
      background: #fff;
    }
    .total-label-text { display: none; }
    .total-field-wrap { position: relative; display: inline-block; min-width: 200px; }
    .total-field-label {
      position: absolute;
      top: -8px; left: 10px;
      background: #f5f5f5;
      font-size: 10px; color: #555;
      padding: 0 4px; z-index: 1;
      pointer-events: none;
    }
    .total-box {
      border: 1px solid #aaa;
      border-radius: 6px;
      padding: 14px 14px 10px 12px;
      min-width: 200px; min-height: 48px;
      text-align: right;
      font-size: 14px; font-weight: 600;
      background: #fff;
      display: flex; align-items: center; justify-content: flex-end;
      color: #1a1f36;
    }

    /* ── FORM EXTRA BTNS ── */
    .form-extra-btns { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }
    .form-extra-btn { background: none; border: none; color: #777; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 0; text-align: left; }
    .form-extra-btn:hover { color: #333; }
    .form-extra-btn.hide-btn { display: none; }

    /* ── FORM FOOTER ── */
    .form-footer { background: #fff; border-top: 1px solid #e0e0e0; padding: 10px 24px; display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-shrink: 0; position: relative; }
    .share-btn-group { display: flex; }
    .btn-share-main { border: 1px solid #ccc; background: #fff; border-radius: 5px 0 0 5px; padding: 7px 16px; font-size: 13px; cursor: pointer; color: #333; border-right: none; }
    .btn-share-main:hover { background: #f5f5f5; }
    .btn-share-caret { border: 1px solid #ccc; background: #fff; border-radius: 0 5px 5px 0; padding: 7px 9px; font-size: 13px; cursor: pointer; color: #333; }
    .btn-share-caret:hover { background: #f5f5f5; }
    .btn-save { background: #2563eb; color: #fff; border: none; border-radius: 5px; padding: 8px 30px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-save:hover { background: #1d4ed8; }
    .btn-save:disabled { background: #93b4f0; cursor: not-allowed; }
    .share-dropdown { position: absolute; bottom: 52px; right: 82px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,.12); z-index: 300; min-width: 160px; display: none; padding: 4px 0; }
    .share-dropdown.open { display: block; }
    .share-dd-item { padding: 11px 16px; cursor: pointer; font-size: 13px; color: #333; display: flex; align-items: center; gap: 10px; }
    .share-dd-item:hover { background: #f5f5f5; }

    /* ── CREATE EXPENSE SCREENSHOT OVERRIDES ── */
    #expenseFormPage {
      background: #f7f7f7 !important;
    }
    #expenseFormPage > div:first-child {
      min-height: 42px !important;
      border-bottom: 1px solid #e6e6e6 !important;
    }
    #expenseFormPage .form-body {
      padding: 14px 28px 0 !important;
      background: #f7f7f7 !important;
      display: grid !important;
      grid-template-columns: minmax(260px, 1.05fr) minmax(280px, .95fr) minmax(300px, 1fr) !important;
      column-gap: 24px !important;
      row-gap: 16px !important;
      align-content: start !important;
    }
    #expenseFormPage .form-title {
      font-size: 18px !important;
      font-weight: 600 !important;
      color: #1f2937 !important;
      margin-bottom: 0 !important;
      line-height: 1.1 !important;
    }
    #expenseFormPage #expenseTaxSwitchWrap {
      display: inline-flex !important;
      align-items: center !important;
      gap: 10px !important;
      margin-left: 6px !important;
      transform: translateY(-1px);
    }
    #expenseFormPage #expenseTaxSwitchWrap span {
      font-size: 12px !important;
      letter-spacing: .2px;
    }
    #expenseFormPage #expenseTaxSwitchWrap .expense-tax-switch {
      position: relative !important;
      display: inline-block !important;
      width: 42px !important;
      height: 22px !important;
      flex-shrink: 0 !important;
    }
    #expenseFormPage #expenseTaxSwitchWrap .expense-tax-switch input {
      opacity: 0 !important;
      width: 0 !important;
      height: 0 !important;
      position: absolute !important;
    }
    #expenseFormPage #expenseTaxSwitchWrap .expense-tax-slider {
      position: absolute !important;
      inset: 0 !important;
      background: #cbd5e1 !important;
      border-radius: 999px !important;
      transition: .2s ease !important;
      box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08) !important;
    }
    #expenseFormPage #expenseTaxSwitchWrap .expense-tax-slider::before {
      content: '' !important;
      position: absolute !important;
      width: 18px !important;
      height: 18px !important;
      left: 2px !important;
      top: 2px !important;
      border-radius: 50% !important;
      background: #fff !important;
      box-shadow: 0 1px 3px rgba(15, 23, 42, .25) !important;
      transition: .2s ease !important;
    }
    #expenseFormPage #expenseTaxSwitch:checked + .expense-tax-slider {
      background: #2563eb !important;
    }
    #expenseFormPage #expenseTaxSwitch:checked + .expense-tax-slider::before {
      transform: translateX(20px) !important;
    }
    #expenseFormPage .form-top-row {
      display: grid !important;
      grid-template-columns: 280px minmax(220px, 1fr) 340px !important;
      gap: 26px !important;
      align-items: start !important;
      margin-bottom: 12px !important;
      grid-column: 1 / -1 !important;
    }
    #expenseFormPage .form-body > div:first-child {
      grid-column: 1 / -1 !important;
    }
    #expenseFormPage #expensePartyWrap {
      margin-top: 2px !important;
      max-width: 260px !important;
    }
    #expenseFormPage #expensePartyWrap label {
      margin-bottom: 4px !important;
      font-size: 11px !important;
      color: #8b95a7 !important;
      font-weight: 600 !important;
    }
    #expenseFormPage #expensePartySearch {
      min-height: 42px !important;
      height: 42px !important;
      border-radius: 4px !important;
      border-color: #cbd5e1 !important;
      box-shadow: none !important;
    }
    #expenseFormPage #expensePartyMenu {
      border-radius: 4px !important;
      border-color: #d7e0ea !important;
      box-shadow: 0 12px 28px rgba(15, 23, 42, .12) !important;
      max-height: 260px !important;
      overflow: auto !important;
    }
    #expenseFormPage .expense-party-option > div {
      padding: 10px 12px !important;
    }
    #expenseFormPage .expense-party-option:hover > div {
      background: #f8fafc !important;
    }
    #expenseFormPage #expensePartyMenu > div:first-child {
      font-size: 11px !important;
      color: #64748b !important;
      font-weight: 700 !important;
      padding: 10px 12px 8px !important;
    }
    #expenseFormPage #expensePartyMenu button {
      line-height: 1.1 !important;
    }
    #expenseFormPage #expensePartyBalance {
      margin-top: 2px !important;
      font-size: 11px !important;
    }
    #expenseFormPage .form-cat-wrap {
      width: 20% !important;
      max-width: 100% !important;
    }
    #expenseFormPage .form-cat-select {
      min-height: 48px !important;
      border-radius: 4px !important;
      border-color: #aeb6c4 !important;
      padding: 18px 14px 8px !important;
      width: 100% !important;
      background: #fff !important;
    }
    #expenseFormPage .form-cat-select .form-cat-label {
      font-size: 11px !important;
      color: #e53935 !important;
      margin-bottom: 0 !important;
      top: 6px !important;
      left: 12px !important;
    }
    #expenseFormPage .form-cat-select #formCatLabel {
      font-size: 13px !important;
      font-weight: 400 !important;
      color: #1f2937 !important;
    }
    #expenseFormPage .form-date-wrap {
      width: 100% !important;
      padding-top: 0 !important;
      text-align: right !important;
    }
    #expenseFormPage .expense-po-center-column {
      display: flex !important;
      justify-content: center !important;
      align-items: flex-start !important;
      min-height: 0 !important;
    }
    #expenseFormPage .expense-header-right-stack {
      display: flex !important;
      flex-direction: column !important;
      align-items: flex-end !important;
      gap: 8px !important;
      margin-bottom: 10px !important;
    }
    #expenseFormPage .expense-header-right-stack .expense-header-mini-fields-grid {
      margin: 0 !important;
    }
    #expenseFormPage .expense-header-right-stack .date-wrapper {
      display: grid !important;
      grid-template-columns: 112px 190px !important;
      align-items: center !important;
      gap: 8px !important;
      width: 310px !important;
      max-width: 310px !important;
      text-align: left !important;
    }
    #expenseFormPage .expense-header-right-stack .date-wrapper > span {
      font-size: 12px !important;
      color: #1f2937 !important;
      line-height: 1.2 !important;
      white-space: nowrap !important;
    }
    #expenseFormPage .expense-header-right-stack .input-control {
      width: 190px !important;
      height: 34px !important;
      min-height: 34px !important;
      border: 1px solid #cbd5e1 !important;
      border-radius: 4px !important;
      background: #fff !important;
      padding: 6px 10px !important;
      font-size: 13px !important;
      color: #111827 !important;
      box-shadow: none !important;
      outline: none !important;
    }
    #expenseFormPage .expense-header-right-stack .input-control:focus {
      border-color: #2563eb !important;
      box-shadow: 0 0 0 2px rgba(37, 99, 235, .12) !important;
    }
    #expenseFormPage .expense-po-fields-group.d-none,
    #expenseFormPage .expense-transaction-time-group.d-none,
    #expenseFormPage .expense-payment-terms-group.d-none,
    #expenseFormPage .expense-deal-days-group.d-none,
    #expenseFormPage .expense-final-due-date-group.d-none {
      display: none !important;
    }
    #expenseFormPage .form-exp-no-row {
      justify-content: flex-end !important;
      gap: 12px !important;
      margin-bottom: 12px !important;
    }
    #expenseFormPage .form-exp-no-label {
      font-size: 12px !important;
      color: #8b95a7 !important;
    }
    #expenseFormPage .form-exp-no-input {

      border-radius: 4px !important;
      border-color: #aeb6c4 !important;
      font-size: 13px !important;
    }
    #expenseFormPage .form-date-row {
      justify-content: flex-end !important;
      gap: 12px !important;
      margin-top: 6px !important;
      font-size: 13px !important;
    }
    #expenseFormPage .form-date-row > span:first-child,
    #expenseFormPage .form-date-row > span:nth-child(2) {
      color: #8b95a7 !important;
    }
    #expenseFormPage .form-date-val {
      font-weight: 500 !important;
      color: #1f2937 !important;
      min-width: 92px !important;
      text-align: left !important;
    }
    #expenseFormPage .form-date-icon {
      color: #1e88e5 !important;
      font-size: 18px !important;
      transform: translateY(1px);
    }
    #expenseFormPage .form-items-wrap {
      margin-top: 14px !important;
      border: 1px solid #d8dde6 !important;
      border-radius: 4px 4px 0 0 !important;
      background: #fff !important;
      grid-column: 1 / -1 !important;
      margin-top: 4px !important;
    }
    #expenseFormPage .form-items-table {
      width: 100% !important;
      table-layout: fixed !important;
    }
    #expenseFormPage .form-items-table thead th {
      height: 32px !important;
      padding: 6px 10px !important;
      background: #fff !important;
      font-size: 12px !important;
      color: #4b5563 !important;
      border-bottom: 1px solid #d8dde6 !important;
    }
    #expenseFormPage .form-items-table td {
      height: 44px !important;
      padding: 0 8px !important;
      background: #fff !important;
    }
    #expenseFormPage .form-items-table td input,
    #expenseFormPage .form-items-table td select {
      height: 34px !important;
      min-height: 34px !important;
      padding: 6px 8px !important;
      font-size: 13px !important;
    }
    #expenseFormPage .col-hash { width: 54px !important; }
    #expenseFormPage .col-item { width: auto !important; }
    #expenseFormPage .col-qty { width: 130px !important; }
    #expenseFormPage .col-price { width: 170px !important; }
    #expenseFormPage .col-tax { width: 210px !important; }
    #expenseFormPage .col-amount { width: 150px !important; }
    #expenseFormPage .items-footer-bar {
      min-height: 40px !important;
      padding: 8px 20px !important;
      border-radius: 0 0 4px 4px !important;
      grid-column: 1 / -1 !important;
    }
    #expenseFormPage .items-total-label {
      font-size: 13px !important;
      color: #475569 !important;
      letter-spacing: .1px;
    }
    #expenseFormPage .payment-section {
      display: contents !important;
    }
    #expenseFormPage #paymentCard {
      grid-column: 2 !important;
      grid-row: 5 !important;
      margin-top: 0 !important;
      min-width: 0 !important;
      width: 100% !important;
      align-self: start !important;
    }
    #expenseFormPage .payment-card {
      min-width: 0 !important;
      max-width: 100% !important;
      width: 100% !important;
    }
    #expenseFormPage .payment-row-wrap {
      margin-bottom: 10px !important;
    }
    #expenseFormPage .payment-row {
      gap: 10px !important;
      align-items: flex-start !important;
    }
    #expenseFormPage .payment-field {
      min-width: 0 !important;
    }
    #expenseFormPage .payment-type-select {
      min-width: 190px !important;
      width: 190px !important;
      min-height: 50px !important;
      border-radius: 4px !important;
      border-color: #aeb6c4 !important;
      padding-top: 18px !important;
      padding-bottom: 8px !important;
      box-shadow: none !important;
    }
    #expenseFormPage .payment-field input[type="number"] {
      width: 120px !important;
      min-height: 50px !important;
      border-radius: 4px !important;
      border-color: #aeb6c4 !important;
      padding-top: 18px !important;
      padding-bottom: 8px !important;
    }
    #expenseFormPage .payment-field-label {
      top: 5px !important;
      left: 11px !important;
      font-size: 10px !important;
      color: #8b95a7 !important;
    }
    #expenseFormPage .total-block {
      grid-column: 3 !important;
      grid-row: 6 !important;
      gap: 16px !important;
      justify-content: flex-end !important;
      min-width: 0 !important;
      width: 100% !important;
      align-self: start !important;
    }
    #expenseFormPage .round-off-wrap {
      gap: 6px !important;
      color: #475569 !important;
    }
    #expenseFormPage .round-off-wrap label {
      font-size: 12px !important;
      color: #6b7280 !important;
      margin: 0 !important;
    }
    #expenseFormPage .round-val {
      width: 74px !important;
      height: 34px !important;
      border-radius: 4px !important;
    }
    #expenseFormPage .total-field-label {
      font-size: 14px !important;
      color: #475569 !important;
    }
    #expenseFormPage .form-extra-btns {
      margin-top: 0 !important;
      gap: 10px !important;
      grid-column: 2 !important;
      grid-row: 6 !important;
      align-self: start !important;
    }
    #expenseFormPage .expense-notes-panel {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 14px 16px 16px;
      margin-bottom: 50px;
    }
    #expenseFormPage .expense-meta-right-stack {
      align-items: flex-start;
      width: 100%;
      min-width: 0;
    }
    #expenseFormPage .action-buttons-column {
      flex: 0 0 300px;
      min-width: 300px;
    }
    #expenseFormPage .description-action-group {
      align-items: stretch;
      flex-direction: column;
      gap: 10px;
    }
    #expenseFormPage .description-content-row {
      flex: 1 1 auto;
      min-width: 0;
    }
    #expenseFormPage .description-pane {
      width: 100%;
    }
    #expenseFormPage .description-side-fields {
      flex: 0 0 220px;
      min-width: 200px;
    }
    #expenseFormPage .party-meta-field {
      min-width: 0;
    }
    #expenseFormPage .image-upload-section {
      display: block;
    }
    #expenseFormPage .form-extra-btn {
      font-size: 13px !important;
      color: #7c8695 !important;
      letter-spacing: .2px;
    }
    #expenseFormPage .form-extra-btn i {
      font-size: 14px !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection,
    #expenseFormPage #expenseTransportationSection {
      margin-top: 0 !important;
      align-self: start !important;
    }
    #expenseFormPage #expenseTransportationSection {
      grid-column: 1 !important;
      grid-row: 5 !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection {
      grid-column: 3 !important;
      grid-row: 5 !important;
      justify-self: end !important;
      width: 360px !important;
      max-width: 360px !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection > div,
    #expenseFormPage #expenseTransportationSection > div {
      border: none !important;
      background: transparent !important;
      padding: 0 !important;
      border-radius: 0 !important;
    }
    #expenseFormPage .expense-section-card {
      border: 1px solid #e0e0e0 !important;
      border-radius: 10px !important;
      background: #fff !important;
      padding: 12px 14px !important;
    }
    #expenseFormPage .expense-section-title {
      font-size: 13px !important;
      font-weight: 700 !important;
      color: #1a1f36 !important;
      margin-bottom: 10px !important;
    }
    #expenseFormPage .expense-field-grid {
      display: grid !important;
      gap: 12px !important;
      grid-template-columns: repeat(auto-fit, minmax(180px, 180px)) !important;
      justify-content: start !important;
    }
    #expenseFormPage .expense-field-grid .expense-compact-wrapper {
      width: 180px !important;
      max-width: 180px !important;
      margin-bottom: 0 !important;
      justify-self: start !important;
    }
    #expenseFormPage .expense-floating-wrapper {
      overflow: visible !important;
    }
    #expenseFormPage .expense-floating-wrapper {
      position: relative !important;
      width: 100% !important;
      margin-bottom: 14px !important;
    }
    #expenseFormPage .expense-floating-wrapper .meta-control {
      border: 1px solid #cfd6e2 !important;
      border-radius: 4px !important;
      min-height: 38px !important;
      background: #fff !important;
      padding-top: 18px !important;
      padding-bottom: 8px !important;
      font-size: 13px !important;
      box-shadow: none !important;
    }
    #expenseFormPage .expense-floating-wrapper textarea.meta-control {
      min-height: 86px !important;
      resize: vertical !important;
    }
    #expenseFormPage .expense-compact-wrapper .meta-control {
      min-height: 38px !important;
      padding-top: 16px !important;
      padding-bottom: 7px !important;
      border-radius: 4px !important;
    }
    #expenseFormPage .expense-compact-wrapper textarea.meta-control {
      min-height: 70px !important;
    }
    #expenseFormPage .expense-floating-wrapper label {
      position: absolute !important;
      top: 5px !important;
      left: 12px !important;
      background: #fff !important;
      font-size: 10px !important;
      color: #8b95a7 !important;
      padding: 0 4px !important;
      pointer-events: none !important;
      transition: all .18s ease !important;
      transform-origin: left top !important;
      z-index: 2 !important;
    }
    #expenseFormPage .expense-floating-wrapper .meta-control:focus + label,
    #expenseFormPage .expense-floating-wrapper .meta-control:not(:placeholder-shown) + label {
      top: -8px !important;
      font-size: 9px !important;
      color: #2563eb !important;
    }
    #expenseFormPage .expense-floating-wrapper .meta-control[type="date"] {
      color-scheme: light !important;
    }
    #expenseFormPage .expense-header-mini-fields-grid {
      display: grid !important;
      grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) !important;
      gap: 10px 12px !important;
      margin-top: 2px !important;
    }
    #expenseFormPage .expense-po-fields-group {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      gap: 10px !important;
      width: 220px !important;
      max-width: 220px !important;
      margin: 0 auto !important;
    }
    #expenseFormPage .expense-po-fields-stack {
      display: flex !important;
      flex-direction: column !important;
      gap: 10px !important;
      width: 100% !important;
      align-items: stretch !important;
    }
    #expenseFormPage .expense-po-fields-group .expense-header-mini-field {
      width: 100% !important;
      max-width: 220px !important;
    }
    #expenseFormPage .expense-po-fields-group .expense-floating-wrapper {
      margin-bottom: 0 !important;
    }
    #expenseFormPage .expense-transaction-time-group,
    #expenseFormPage .expense-payment-terms-group,
    #expenseFormPage .expense-deal-days-group,
    #expenseFormPage .expense-final-due-date-group {
      display: flex !important;
      justify-content: flex-end !important;
      width: 100% !important;
      margin-bottom: 10px !important;
    }
    #expenseFormPage .expense-transaction-time-group .expense-header-mini-field,
    #expenseFormPage .expense-payment-terms-group .expense-header-mini-field,
    #expenseFormPage .expense-deal-days-group .expense-header-mini-field,
    #expenseFormPage .expense-final-due-date-group .expense-header-mini-field {
      width: 310px !important;
      max-width: 310px !important;
    }
    #expenseFormPage .expense-header-mini-field .meta-control {
      min-height: 38px !important;
      padding-top: 16px !important;
      padding-bottom: 7px !important;
    }
    #expenseFormPage .expense-header-mini-field input[type="date"].meta-control {
      padding-top: 14px !important;
    }
    #expenseFormPage .expense-attachment-actions {
      display: flex !important;
      flex-direction: column !important;
      flex-wrap: nowrap !important;
      gap: 10px !important;
      margin-top: 0 !important;
    }
    #expenseFormPage .description-action-group .action-btn,
    #expenseFormPage .expense-attachment-actions .action-btn {
      width: 80% !important;
      min-height: 58px !important;
      justify-content: flex-start !important;
      gap: 12px !important;
      padding: 0 16px !important;
      border-radius: 4px !important;
      font-size: 20px !important;
      color: #5f6f82 !important;
      border: 1px solid #d0d7e2 !important;
      background: #f8fafc !important;
    }
    #expenseFormPage .description-action-group .action-btn i,
    #expenseFormPage .expense-attachment-actions .action-btn i {
      width: 18px !important;
      text-align: center !important;
      color: #64748b !important;
    }
    #expenseFormPage .expense-attachment-preview-wrap {
      margin-top: 8px !important;
    }
    #expenseFormPage .expense-notes-panel {
      display: grid !important;
      grid-template-columns: 220px 1fr !important;
      gap: 16px !important;
      align-items: start !important;
    }
    #expenseFormPage .expense-meta-right-stack {
      min-width: 0 !important;
    }
    #expenseFormPage .description-pane {
      width: 220px !important;
      max-width: 220px !important;
    }
    #expenseFormPage .expense-description-input {
      min-height: 78px !important;
      width: 220px !important;
      max-width: 220px !important;
    }
    #expenseFormPage #expenseTransportationSection .expense-floating-wrapper {
      margin-bottom: 0 !important;
    }
    #expenseFormPage #expenseTransportationSection .expense-floating-wrapper .meta-control {
      width: 100% !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection .expense-section-card,
    #expenseFormPage #expenseTransportationSection .expense-section-card {
      max-width: 100% !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection .expense-field-grid {
      grid-template-columns: minmax(0, 220px) !important;
      justify-content: end !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-field-grid {
      grid-template-columns: 1fr !important;
      justify-content: end !important;
      gap: 10px !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-field-grid .expense-compact-wrapper {
      width: 100% !important;
      max-width: none !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-discount-row,
    #expenseFormPage #expenseDiscountTaxSection .expense-tax-row,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-discount-row,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-tax-row {
      display: flex !important;
      align-items: center !important;
      justify-content: flex-end !important;
      gap: 8px !important;
      width: 100% !important;
      flex-wrap: wrap !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-row-label,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-row-label {
      min-width: 78px !important;
      text-align: right !important;
      font-size: 14px !important;
      color: #666 !important;
      margin-right: 6px !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-inline-input,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-inline-input {
      width: 108px !important;
      max-width: 108px !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-inline-input.expense-tax-select,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-inline-input.expense-tax-select {
      width: 148px !important;
      max-width: 148px !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-inline-suffix,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-inline-suffix {
      font-size: 14px !important;
      color: #666 !important;
      min-width: 18px !important;
      text-align: center !important;
    }
    #expenseFormPage #expenseDiscountTaxSection .expense-tax-amount-inline,
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block .expense-tax-amount-inline {
      width: 82px !important;
      max-width: 82px !important;
      flex: 0 0 82px !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection .expense-discount-tax-block {
      margin-bottom: 16px !important;
      padding-bottom: 14px !important;
      border-bottom: 1px solid #e5e7eb !important;
    }
    #expenseFormPage #expenseTransportationSection .expense-field-grid {
      grid-template-columns: repeat(2, minmax(0, 220px)) !important;
      justify-content: start !important;
    }
    #expenseFormPage #expenseAdditionalChargesSection .expense-field-grid .expense-compact-wrapper,
    #expenseFormPage #expenseTransportationSection .expense-field-grid .expense-compact-wrapper {
      width: 220px !important;
      max-width: 220px !important;
    }
    #expenseFormPage .expense-tax-cell > div {
      min-width: 0 !important;
      width: 100% !important;
      gap: 6px !important;
    }
    #expenseFormPage .expense-tax-cell select,
    #expenseFormPage .expense-tax-cell input {
      min-width: 0 !important;
      width: 100% !important;
      height: 30px !important;
      border-radius: 4px !important;
      font-size: 12px !important;
      padding: 5px 8px !important;
    }
    #expenseFormPage .form-footer {
      grid-column: 1 / -1 !important;
      grid-row: 7 !important;
    }
    @media (max-width: 1199px) {
      #expenseFormPage .form-body {
        display: block !important;
      }
      #expenseFormPage .form-top-row,
      #expenseFormPage .form-items-wrap,
      #expenseFormPage .items-footer-bar,
      #expenseFormPage .payment-section,
      #expenseFormPage #expenseAdditionalChargesSection,
      #expenseFormPage #expenseTransportationSection,
      #expenseFormPage .form-extra-btns,
      #expenseFormPage .form-footer {
        grid-column: auto !important;
        grid-row: auto !important;
      }
      #expenseFormPage .payment-section {
        display: grid !important;
      }
      #expenseFormPage .expense-meta-right-stack {
        flex-direction: column !important;
      }
      #expenseFormPage .expense-notes-panel {
        display: block !important;
      }
      #expenseFormPage .description-pane,
      #expenseFormPage .expense-description-input {
        width: 100% !important;
        max-width: none !important;
      }
      #expenseFormPage .description-side-fields,
      #expenseFormPage .action-buttons-column {
        width: 100% !important;
        flex-basis: auto !important;
      }
      #expenseFormPage .expense-field-grid {
        grid-template-columns: minmax(0, 1fr) !important;
      }
      #expenseFormPage .expense-field-grid .expense-compact-wrapper {
        max-width: none !important;
        width: 100% !important;
      }
    }
    #expenseFormPage .form-footer {
      padding: 10px 22px !important;
    }
    #expenseFormPage .btn-save {
      min-width: 96px !important;
      height: 40px !important;
      border-radius: 4px !important;
    }
    #expenseFormPage .share-btn {
      height: 40px !important;
      border-radius: 4px !important;
    }
    #expenseFormPage .share-btn-group {
      align-items: center !important;
    }
    #expenseFormPage .link-payment-btn {
      height: 40px !important;
      border-radius: 4px !important;
      min-width: 150px !important;
    }

    /* MODALS */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 1100; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 10px; padding: 28px 28px 24px; width: 400px; max-width: 95vw; position: relative; box-shadow: 0 8px 32px rgba(0,0,0,.18); }
    .modal-box.expense-item-modal { width: 760px; max-width: 96vw; }
    #addPartyModal .modal-dialog.expense-party-modal {
      width: min(1020px, calc(100vw - 36px));
      max-width: min(1020px, calc(100vw - 36px));
      margin: 0;
    }
    #addPartyModal .modal-content {
      border: none;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 24px 80px rgba(15, 23, 42, .22);
      max-height: calc(100vh - 36px);
      display: flex;
      flex-direction: column;
    }
    #addPartyModal .modal-header {
      padding: 18px 24px;
      border-bottom: 1px solid #e5e7eb;
      background: linear-gradient(180deg, #fff 0%, #fbfcff 100%);
    }
    #addPartyModal .modal-body {
      padding: 20px 24px 18px;
      background: #f8fafc;
      overflow-y: auto;
    }
    #addPartyModal .modal-footer {
      padding: 16px 24px 22px;
      border-top: 1px solid #e5e7eb;
      background: #fff;
      justify-content: flex-end;
      gap: 10px;
    }
    #addPartyModal .floating-input-wrapper .meta-control {
      border-radius: 10px;
      border-color: #cbd5e1;
      box-shadow: none;
    }
    #addPartyModal .party-modal-control {
      min-height: 44px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      box-shadow: none;
      background: #fff;
      color: #1f2937;
      padding-left: 14px;
      padding-right: 14px;
      font-size: 14px;
    }
    #addPartyModal .party-modal-control:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }
    #addPartyModal .party-modal-textarea {
      min-height: 92px;
      resize: vertical;
      padding-top: 12px;
    }
    #addPartyModal .party-modal-input-group {
      min-height: 44px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: none;
    }
    #addPartyModal .party-modal-input-group .input-group-text,
    #addPartyModal .party-modal-input-group .form-control {
      border-color: #d1d5db;
    }
    #addPartyModal .party-modal-input-group .input-group-text {
      background: #f8fafc;
      color: #64748b;
      font-weight: 600;
      padding-left: 12px;
      padding-right: 12px;
    }
    #addPartyModal .party-modal-input-group .form-control {
      min-height: 44px;
      box-shadow: none;
    }
    #addPartyModal .party-group-wrap {
      position: relative;
      width: 100%;
    }
    #addPartyModal .party-group-trigger {
      width: 100%;
      min-height: 44px;
      border: 1px solid #d1d5db;
      border-radius: 12px;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 14px;
      font-size: 14px;
      color: #374151;
      box-shadow: none;
      text-align: left;
      font-weight: 400;
    }
    #addPartyModal .party-group-trigger:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }
    #addPartyModal .party-group-trigger .text {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    #addPartyModal .party-group-menu {
      position: absolute;
      left: 0;
      right: 0;
      top: calc(100% + 6px);
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .16);
      z-index: 30;
      overflow: hidden;
      max-height: 280px;
      overflow-y: auto;
    }
    #addPartyModal .party-group-menu .dropdown-item {
      width: 100%;
      text-align: left;
      border: none;
      background: #fff;
      padding: 10px 14px;
      font-size: 13px;
      color: #334155;
      cursor: pointer;
    }
    #addPartyModal .party-group-menu .dropdown-item:hover {
      background: #f8fafc;
    }
    #addPartyModal .party-group-menu .dropdown-item.text-primary {
      color: #2563eb;
      font-weight: 600;
      border-bottom: 1px solid #eef2f7;
    }
    #addPartyModal .party-group-empty {
      padding: 12px 14px;
      color: #94a3b8;
      font-size: 12px;
    }
    #partyGroupModal .modal-box {
      width: 420px;
    }
    .modal-title { font-size: 16px; font-weight: 700; color: #1a1f36; margin-bottom: 20px; }
    .modal-close { position: absolute; top: 14px; right: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: #888; line-height: 1; }
    .modal-close:hover { color: #333; }
    .modal-field { margin-bottom: 16px; position: relative; }
    .modal-field label { position: absolute; top: -8px; left: 10px; background: #fff; font-size: 10px; color: #2563eb; padding: 0 4px; z-index: 1; }
    .modal-field input { width: 100%; border: 1.5px solid #2563eb; border-radius: 6px; padding: 11px 12px; font-size: 13px; outline: none; color: #1a1f36; }
    .modal-field select { width: 100%; border: 1px solid #ccc; border-radius: 6px; padding: 11px 12px; font-size: 13px; outline: none; color: #555; background: #fff; appearance: none; }
    .modal-type-label { position: absolute; top: -8px; left: 10px; background: #fff; font-size: 10px; color: #555; padding: 0 4px; z-index: 1; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .btn-cancel-modal { background: #fff; border: 1px solid #ccc; border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer; color: #555; }
    .btn-cancel-modal:hover { background: #f5f5f5; }
    .btn-save-modal { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 8px 24px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-save-modal:hover { background: #1d4ed8; }
    .item-modal-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 16px 18px; }
    .item-modal-section { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; background: #fafafa; }
    .item-modal-section-title { font-size: 12px; font-weight: 700; color: #2563eb; margin-bottom: 10px; letter-spacing: .2px; text-transform: uppercase; }
    .item-modal-field { margin-bottom: 14px; position: relative; }
    .item-modal-field label { display: block; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
    .item-modal-field input,
    .item-modal-field select { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 13px; outline: none; background: #fff; color: #1a1f36; }
    .item-modal-field input:focus,
    .item-modal-field select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }
    .item-modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
    .item-modal-hint { font-size: 11px; color: #94a3b8; margin-top: 6px; }
    @media (max-width: 767px) {
      .item-modal-grid { grid-template-columns: 1fr; }
      .item-modal-footer { flex-wrap: wrap; }
    }
    .item-pricing-label { display: block; color: #2563eb; font-size: 13px; font-weight: 600; margin: 4px 0 10px; }
    .item-price-field { border: 1px solid #ccc; border-radius: 6px; padding: 10px 12px; font-size: 13px; width: 100%; outline: none; }
    .item-price-field:focus { border-color: #2563eb; }
    .modal-actions-split { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
    .btn-delete-modal { background: #fff; border: 1px solid #D4112E; color: #D4112E; border-radius: 6px; padding: 8px 20px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-delete-modal:hover { background: #fff5f5; }

    /* CONFIRM */
    .confirm-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.25); z-index: 1150; align-items: center; justify-content: center; }
    .confirm-overlay.open { display: flex; }
    .confirm-box { background: #fff; border-radius: 8px; padding: 20px 24px; width: 360px; max-width: 90vw; box-shadow: 0 4px 24px rgba(0,0,0,.15); }
    .confirm-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 14px; font-weight: 700; color: #333; }
    .confirm-close-btn { background: none; border: none; cursor: pointer; color: #888; font-size: 18px; line-height: 1; }
    .confirm-msg { font-size: 13px; color: #555; margin-bottom: 20px; }
    .confirm-actions { display: flex; justify-content: flex-end; gap: 10px; }
    .btn-confirm-yes { background: #fff; border: 1.5px solid #2563eb; color: #2563eb; border-radius: 5px; padding: 7px 22px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-confirm-yes:hover { background: #eff6ff; }
    .btn-confirm-no { background: #2563eb; border: none; border-radius: 5px; padding: 7px 22px; font-size: 13px; font-weight: 600; cursor: pointer; color: #fff; }
    .btn-confirm-no:hover { background: #1d4ed8; }

    /* TOAST */
    .toast-custom { position: fixed; top: 14px; right: 14px; border-radius: 8px; padding: 12px 16px; font-size: 13px; display: flex; align-items: center; gap: 10px; z-index: 9999; box-shadow: 0 4px 16px rgba(0,0,0,.18); opacity: 0; transition: opacity .3s; pointer-events: none; max-width: 360px; min-width: 240px; }
    .toast-custom.show { opacity: 1; pointer-events: all; }
    .toast-custom.toast-red   { background: #D4112E; color: #fff; }
    .toast-custom.toast-green { background: #16a34a; color: #fff; }
    .toast-icon-el { font-size: 17px; flex-shrink: 0; }
    .toast-msg-el  { flex: 1; line-height: 1.4; }
    .toast-close-btn { cursor: pointer; font-size: 16px; line-height: 1; margin-left: 4px; flex-shrink: 0; }

    /* Close Expense confirm */
  .close-expense-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 1160; align-items: center; justify-content: center; }
    .close-expense-overlay.open { display: flex; }
    .close-expense-box { background: #fff; border-radius: 10px; padding: 28px 28px 24px; width: 420px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,.18); }
    .close-expense-title { font-size: 16px; font-weight: 700; color: #1a1f36; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
    .close-expense-msg { font-size: 13px; color: #555; margin-bottom: 24px; }
    .close-expense-actions { display: flex; justify-content: flex-end; gap: 10px; }
    .btn-cancel-close { background: #fff; border: 1px solid #ccc; border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer; color: #555; }
    .btn-confirm-close { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 8px 24px; font-size: 13px; font-weight: 600; cursor: pointer; }

    /* ═══════════════════════════════════════════
       PRINT STYLES — full-screen print view
       ═══════════════════════════════════════════ */
    #printViewOverlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 850;
      background: #525659;
      flex-direction: column;
    }
    #printViewOverlay.open { display: flex; }
    .print-view-toolbar {
      background: #3c3f41;
      padding: 8px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }
    .print-view-toolbar-left { display: flex; align-items: center; gap: 12px; color: #ccc; font-size: 13px; }
    .print-view-toolbar-right { display: flex; align-items: center; gap: 8px; }
    .print-toolbar-btn {
      background: none;
      border: 1px solid #666;
      color: #ccc;
      border-radius: 4px;
      padding: 5px 12px;
      font-size: 12px;
      cursor: pointer;
    }
    .print-toolbar-btn:hover { background: #555; }
    .print-toolbar-btn.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
    .print-toolbar-btn.primary:hover { background: #1d4ed8; }
    .print-view-body {
      flex: 1;
      overflow-y: auto;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 30px 20px;
    }
    .print-page {
      background: #fff;
      width: 794px;
      min-height: 1123px;
      box-shadow: 0 4px 24px rgba(0,0,0,.4);
      padding: 48px 56px;
      font-family: 'Segoe UI', sans-serif;
      font-size: 13px;
      color: #1a1f36;
    }
    .print-page h2 { text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 28px; }
    .print-company-box { border: 1px solid #ccc; padding: 16px 20px; margin-bottom: 0; }
    .print-company-name { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .print-company-meta { font-size: 12px; color: #555; display: flex; gap: 32px; }
    .print-info-table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; border-top: none; }
    .print-info-table td { padding: 10px 16px; border: 1px solid #ccc; vertical-align: top; font-size: 12px; }
    .print-info-table td strong { display: block; margin-bottom: 4px; font-size: 11px; color: #555; text-transform: uppercase; }
    .print-items-table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; margin-top: 0; }
    .print-items-table th { background: #f5f5f5; padding: 9px 12px; font-size: 11px; font-weight: 700; text-align: left; border: 1px solid #ccc; }
    .print-items-table th:last-child, .print-items-table td:last-child { text-align: right; }
    .print-items-table td { padding: 9px 12px; border: 1px solid #ccc; font-size: 12px; }
    .print-items-table tr.total-row td { font-weight: 700; background: #f9f9f9; }
    .print-summary-table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; border-top: none; }
    .print-summary-table td { padding: 9px 16px; border: 1px solid #ccc; font-size: 12px; }
    .print-summary-table td:last-child { text-align: right; font-weight: 500; }
    .print-summary-table tr.total-row td { font-weight: 700; background: #f9f9f9; }
    .print-words-row td { font-style: italic; color: #555; }
    .print-signatory { margin-top: 40px; display: flex; justify-content: flex-end; }
    .print-signatory-box { border: 1px solid #ccc; padding: 14px 24px; min-width: 220px; min-height: 80px; text-align: center; }
    .print-signatory-box .sig-for { font-size: 12px; color: #555; margin-bottom: 36px; }
    .print-signatory-box .sig-line { border-top: 1px solid #ccc; padding-top: 6px; font-size: 11px; color: #888; }

    @media print {
      body > * { display: none !important; }
      #printViewOverlay { display: flex !important; position: fixed; inset: 0; }
      .print-view-toolbar { display: none; }
      .print-view-body { padding: 0; background: #fff; }
      .print-page { box-shadow: none; width: 100%; min-height: auto; }
    }
  </style>
</head>
<body data-page="expenses">

  {{-- Navbar & Sidebar injected by components.js --}}

  <main class="main-content" id="mainContent">

    {{-- EMPTY STATE --}}
    <div id="emptyState">
      <svg class="empty-svg" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="12" y="42" width="86" height="54" rx="8" stroke="#c5cad8" stroke-width="3" fill="#f0f2f7"/>
        <path d="M12 58 Q12 42 24 42 H88 Q100 42 100 58" stroke="#c5cad8" stroke-width="2.5" fill="#dde0ea"/>
        <rect x="70" y="60" width="22" height="18" rx="4" stroke="#c5cad8" stroke-width="2" fill="#fff"/>
        <circle cx="81" cy="69" r="3" fill="#c5cad8"/>
        <circle cx="92" cy="30" r="17" stroke="#c5cad8" stroke-width="3" fill="#f0f2f7"/>
        <text x="92" y="37" text-anchor="middle" font-size="18" font-family="serif" fill="#c5cad8">₹</text>
      </svg>
      <h4>Add your 1st Expense</h4>
      <p>Record your business expenses &amp; know your real profits.</p>
      <button class="btn-empty-add" id="emptyAddBtn">
        <span class="plus-icon">+</span> Add Expenses
      </button>
    </div>

    {{-- SPLIT-PANE --}}
    <div id="splitPane">
      <div class="exp-tabs-bar">
        <button class="exp-tab-btn active" id="tabCategory" onclick="switchListTab('category')">CATEGORY</button>
        <button class="exp-tab-btn"        id="tabItems"    onclick="switchListTab('items')">ITEMS</button>
      </div>
      <div class="split-layout">
        <div class="split-left">
          <div class="split-left-header">
            <button class="sl-search-icon"><i class="bi bi-search"></i></button>
            <button class="btn-add-expense-red" id="splitAddBtn">
              <span class="plus-icon">+</span> Add Expense
            </button>
          </div>
          <div class="split-left-cols">
            <div class="slc-left">
              <span id="slcLabel">CATEGORY</span>
              <span class="slc-filter-wrap">
                <i class="bi bi-arrow-up slc-sort-icon"></i>
                <span class="th-filter" onclick="toggleFilterPop(event,'fpop_left_name')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                <div class="filter-popover" id="fpop_left_name" style="min-width:220px;">
                  <div class="filter-pop-header">
                    <span class="filter-pop-title" id="fpop_left_name_title">CATEGORY FILTER</span>
                    <button class="filter-pop-close" onclick="closeFilterPop('fpop_left_name')">&#x2715;</button>
                  </div>
                  <div class="filter-pop-body">
                    <select class="filter-pop-select" id="fpop_left_name_cat">
                      <option>Contains</option>
                      <option>Exact match</option>
                    </select>
                    <input type="text" class="filter-pop-input" id="fpop_left_name_val" placeholder="Search category">
                  </div>
                  <div class="filter-pop-footer">
                    <button class="filter-pop-clear" onclick="clearFilterPop('fpop_left_name','left_name')">Clear</button>
                    <button class="filter-pop-apply" onclick="applyFilterPop('fpop_left_name','left_name')">Apply</button>
                  </div>
                </div>
              </span>
            </div>
            <div class="slc-right">
              <span id="slcAmountLabel">AMOUNT</span>
              <span class="slc-filter-wrap">
                <span class="th-filter" onclick="toggleFilterPop(event,'fpop_left_amount')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                <div class="filter-popover" id="fpop_left_amount" style="min-width:220px; left:auto; right:0;">
                  <div class="filter-pop-header">
                    <span class="filter-pop-title" id="fpop_left_amount_title">AMOUNT FILTER</span>
                    <button class="filter-pop-close" onclick="closeFilterPop('fpop_left_amount')">&#x2715;</button>
                  </div>
                  <div class="filter-pop-body">
                    <select class="filter-pop-select" id="fpop_left_amount_cat">
                      <option>Equal to</option>
                      <option>Less Than</option>
                      <option>Greater Than</option>
                    </select>
                    <input type="number" class="filter-pop-input" id="fpop_left_amount_val" placeholder="0">
                  </div>
                  <div class="filter-pop-footer">
                    <button class="filter-pop-clear" onclick="clearFilterPop('fpop_left_amount','left_amount')">Clear</button>
                    <button class="filter-pop-apply" onclick="applyFilterPop('fpop_left_amount','left_amount')">Apply</button>
                  </div>
                </div>
              </span>
            </div>
          </div>
          <div class="category-list" id="categoryList"></div>
        </div>

        {{-- RIGHT: category --}}
        <div class="split-right" id="categoryDetailPanel">
          <div class="detail-header">
            <div>
              <div class="detail-cat-title" id="detailTitle">—</div>
              <div class="detail-cat-type"  id="detailType"></div>
            </div>
            <div class="detail-totals">
              <div>Total : <span class="red-amt" id="detailTotal">Rs 0.00</span></div>
              <div style="margin-top:4px;">Balance : <span class="red-amt" id="detailBalance">Rs 0.00</span></div>
            </div>
          </div>
          <div class="detail-search-bar">
            <div class="detail-search-input-wrap">
              <i class="bi bi-search" style="color:#aaa;font-size:13px;"></i>
              <input type="text" id="detailSearchInput" placeholder="">
            </div>
          </div>
          <div class="detail-table-wrap">
            <table class="detail-table">
              <thead><tr>
                {{-- DATE --}}
                <th>
                  <span class="th-wrap">
                    DATE
                    <span class="th-sort" onclick="sortDetailTable('date')" id="sort_date">
                      <span class="sa-up" id="sort_date_up">&#9650;</span>
                      <span class="sa-dn" id="sort_date_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_date')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_date">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_date')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_date_cat">
                          <option>Equal To</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">Select Date</div>
                        <input type="text" class="filter-pop-input" id="fpop_date_val" placeholder="DD/MM/YYYY">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_date','date')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_date','date')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                {{-- EXP NO. --}}
                <th>
                  <span class="th-wrap">
                    EXP NO.
                    <span class="th-sort" onclick="sortDetailTable('expNo')" id="sort_expNo">
                      <span class="sa-up" id="sort_expNo_up">&#9650;</span>
                      <span class="sa-dn" id="sort_expNo_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_expNo')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_expNo">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_expNo')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_expNo_cat">
                          <option>Equal To</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">Select Date</div>
                        <input type="text" class="filter-pop-input" id="fpop_expNo_val" placeholder="DD/MM/YYYY">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_expNo','expNo')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_expNo','expNo')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                {{-- PARTY --}}
                <th>
                  <span class="th-wrap">
                    PARTY
                    <span class="th-sort" onclick="sortDetailTable('party')" id="sort_party">
                      <span class="sa-up" id="sort_party_up">&#9650;</span>
                      <span class="sa-dn" id="sort_party_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_party')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_party">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_party')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_party_cat">
                          <option>Contains</option><option>Exact match</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">PARTY</div>
                        <input type="text" class="filter-pop-input" id="fpop_party_val" placeholder="">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_party','party')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_party','party')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                {{-- PAYMENT TYPE --}}
                <th>
                  <span class="th-wrap">
                    PAYMENT TYPE
                    <span class="th-sort" onclick="sortDetailTable('paymentType')" id="sort_paymentType">
                      <span class="sa-up" id="sort_paymentType_up">&#9650;</span>
                      <span class="sa-dn" id="sort_paymentType_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_payType')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_payType">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_payType')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_payType_cash"> Cash</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_payType_cheque"> Cheque</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_payType_upi"> UPI</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_payType_card"> Card</label>
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_payType','paymentType')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_payType','paymentType')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                {{-- AMOUNT --}}
                <th>
                  <span class="th-wrap">
                    AMOUNT
                    <span class="th-sort" onclick="sortDetailTable('amount')" id="sort_amount">
                      <span class="sa-up" id="sort_amount_up">&#9650;</span>
                      <span class="sa-dn active" id="sort_amount_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_amount')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_amount">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_amount')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_amount_cat">
                          <option>Equal to</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <input type="number" class="filter-pop-input" id="fpop_amount_val" placeholder="0">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_amount','amount')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_amount','amount')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                {{-- STATUS --}}
                <th>
                  <span class="th-wrap">
                    STATUS
                    <span class="th-sort" onclick="sortDetailTable('status')" id="sort_status">
                      <span class="sa-up" id="sort_status_up">&#9650;</span>
                      <span class="sa-dn" id="sort_status_dn">&#9660;</span>
                    </span>
                  </span>
                </th>
                {{-- DUE DATE --}}
                <th>
                  <span class="th-wrap">
                    DUE DATE
                    <span class="th-sort" onclick="sortDetailTable('dueDate')" id="sort_dueDate">
                      <span class="sa-up" id="sort_dueDate_up">&#9650;</span>
                      <span class="sa-dn" id="sort_dueDate_dn">&#9660;</span>
                    </span>
                  </span>
                </th>
                {{-- BALANCE --}}
                <th>
                  <span class="th-wrap">
                    BALANCE
                    <span class="th-sort" onclick="sortDetailTable('balance')" id="sort_balance">
                      <span class="sa-up" id="sort_balance_up">&#9650;</span>
                      <span class="sa-dn" id="sort_balance_dn">&#9660;</span>
                    </span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_balance')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_balance">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_balance')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_balance_cat">
                          <option>Equal to</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <input type="number" class="filter-pop-input" id="fpop_balance_val" placeholder="0">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_balance','balance')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_balance','balance')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th></th>
              </tr></thead>
              <tbody id="detailTableBody"></tbody>
            </table>
          </div>
        </div>

        {{-- RIGHT: items --}}
        <div class="split-right" id="itemsDetailPanel" style="display:none;">
          <div class="detail-header">
            <div><div class="detail-cat-title" id="itemsDetailTitle">—</div></div>
            <div class="detail-totals">
              <div>Total : <span class="red-amt" id="itemsDetailTotal">Rs 0.00</span></div>
              <div style="margin-top:4px;">Balance : <span class="red-amt" id="itemsDetailBalance">Rs 0.00</span></div>
            </div>
          </div>
          <div class="detail-search-bar">
            <div class="detail-search-input-wrap">
              <i class="bi bi-search" style="color:#aaa;font-size:13px;"></i>
              <input type="text" id="itemsDetailSearchInput" placeholder="">
            </div>
          </div>
          <div class="detail-table-wrap">
            <table class="detail-table">
              <thead><tr>
                <th>
                  <span class="th-wrap">
                    DATE
                    <span class="th-sort" onclick="sortItemsTable('date')" id="isort_date"><span class="sa-up" id="isort_date_up">&#9650;</span><span class="sa-dn" id="isort_date_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_date')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_date">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_date')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_items_date_cat">
                          <option>Equal To</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">Select Date</div>
                        <input type="text" class="filter-pop-input" id="fpop_items_date_val" placeholder="DD/MM/YYYY">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_date','date')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_date','date')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    EXP NO.
                    <span class="th-sort" onclick="sortItemsTable('expNo')" id="isort_expNo"><span class="sa-up" id="isort_expNo_up">&#9650;</span><span class="sa-dn" id="isort_expNo_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_expNo')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_expNo">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_expNo')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_items_expNo_cat">
                          <option>Equal To</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">Select Date</div>
                        <input type="text" class="filter-pop-input" id="fpop_items_expNo_val" placeholder="DD/MM/YYYY">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_expNo','expNo')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_expNo','expNo')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    PARTY
                    <span class="th-sort" onclick="sortItemsTable('party')" id="isort_party"><span class="sa-up" id="isort_party_up">&#9650;</span><span class="sa-dn" id="isort_party_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_party')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_party">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_party')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_items_party_cat">
                          <option>Contains</option><option>Exact match</option>
                        </select>
                        <div style="font-size:12px;color:#555;margin-bottom:4px;">PARTY</div>
                        <input type="text" class="filter-pop-input" id="fpop_items_party_val" placeholder="">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_party','party')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_party','party')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    PAYMENT TYPE
                    <span class="th-sort" onclick="sortItemsTable('paymentType')" id="isort_paymentType"><span class="sa-up" id="isort_paymentType_up">&#9650;</span><span class="sa-dn" id="isort_paymentType_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_payType')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_payType">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_payType')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_items_payType_cash"> Cash</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_items_payType_cheque"> Cheque</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_items_payType_upi"> UPI</label>
                        <label class="filter-pop-checkbox-row"><input type="checkbox" id="fpop_items_payType_card"> Card</label>
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_payType','paymentType')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_payType','paymentType')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    AMOUNT
                    <span class="th-sort" onclick="sortItemsTable('amount')" id="isort_amount"><span class="sa-up" id="isort_amount_up">&#9650;</span><span class="sa-dn" id="isort_amount_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_amount')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_amount">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_amount')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_items_amount_cat">
                          <option>Equal to</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <input type="number" class="filter-pop-input" id="fpop_items_amount_val" placeholder="0">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_amount','amount')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_amount','amount')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    STATUS
                    <span class="th-sort" onclick="sortItemsTable('status')" id="isort_status"><span class="sa-up" id="isort_status_up">&#9650;</span><span class="sa-dn" id="isort_status_dn">&#9660;</span></span>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    DUE DATE
                    <span class="th-sort" onclick="sortItemsTable('dueDate')" id="isort_dueDate"><span class="sa-up" id="isort_dueDate_up">&#9650;</span><span class="sa-dn" id="isort_dueDate_dn">&#9660;</span></span>
                  </span>
                </th>
                <th>
                  <span class="th-wrap">
                    BALANCE
                    <span class="th-sort" onclick="sortItemsTable('balance')" id="isort_balance"><span class="sa-up" id="isort_balance_up">&#9650;</span><span class="sa-dn" id="isort_balance_dn">&#9660;</span></span>
                    <span class="th-filter" onclick="toggleFilterPop(event,'fpop_items_balance')"><i class="fa-solid fa-filter" style="font-size:9px;color:#bbb;"></i></span>
                    <div class="filter-popover" id="fpop_items_balance">
                      <div class="filter-pop-header">
                        <span class="filter-pop-title">Select Category</span>
                        <button class="filter-pop-close" onclick="closeFilterPop('fpop_items_balance')">&#x2715;</button>
                      </div>
                      <div class="filter-pop-body">
                        <select class="filter-pop-select" id="fpop_items_balance_cat">
                          <option>Equal to</option><option>Less Than</option><option>Greater Than</option>
                        </select>
                        <input type="number" class="filter-pop-input" id="fpop_items_balance_val" placeholder="0">
                      </div>
                      <div class="filter-pop-footer">
                        <button class="filter-pop-clear" onclick="clearFilterPop('fpop_items_balance','balance')">Clear</button>
                        <button class="filter-pop-apply" onclick="applyFilterPop('fpop_items_balance','balance')">Apply</button>
                      </div>
                    </div>
                  </span>
                </th>
                <th></th>
              </tr></thead>
              <tbody id="itemsDetailTableBody">
                <tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">No transactions to show</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- EXPENSE FORM — now full-screen fixed overlay (moved to partial) --}}
    @include('dashboard.expense.create-expense')

  </main>

  {{-- ═══════════════════════════════════════════
       PRINT VIEW OVERLAY (full screen, like Image 4)
       ═══════════════════════════════════════════ --}}
  <div id="printViewOverlay">
    <div class="print-view-toolbar">
      <div class="print-view-toolbar-left">
        <span id="printViewFilename">print.html</span>
        <span style="color:#888;">|</span>
        <span>1 / 1</span>
        <button class="print-toolbar-btn" onclick="changePrintZoom(-10)">−</button>
        <span id="printZoomLabel" style="color:#ccc;font-size:12px;">100%</span>
        <button class="print-toolbar-btn" onclick="changePrintZoom(10)">+</button>
      </div>
      <div class="print-view-toolbar-right">
        <button class="print-toolbar-btn" onclick="closePrintView()">Close</button>
        <button class="print-toolbar-btn primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
      </div>
    </div>
    <div class="print-view-body" id="printViewBody">
      <div class="print-page" id="printPageContent"></div>
    </div>
  </div>

  {{-- MODAL: Edit Category --}}
  <div class="modal-overlay" id="editCatModal">
    <div class="modal-box">
      <button class="modal-close" onclick="closeModal('editCatModal')">&#x2715;</button>
      <div class="modal-title">Edit Expense Category</div>
      <div class="modal-field">
        <label>Expense Category</label>
        <input type="text" id="editCatName">
      </div>
      <div class="modal-field" style="margin-top:4px;">
        <span class="modal-type-label">Expense Type</span>
        <select id="editCatType">
          <option value="Indirect Expense">Indirect Expense</option>
          <option value="Direct Expense">Direct Expense</option>
        </select>
      </div>
      <div class="modal-actions">
        <button class="btn-cancel-modal" onclick="closeModal('editCatModal')">Cancel</button>
        <button class="btn-save-modal" onclick="saveEditCategory()">Save</button>
      </div>
    </div>
  </div>

  {{-- MODAL: Add Category --}}
  <div class="modal-overlay" id="addCatModal">
    <div class="modal-box">
      <button class="modal-close" onclick="closeModal('addCatModal')">&#x2715;</button>
      <div class="modal-title">Add Expense Category</div>
      <div class="modal-field">
        <label>Expense Category</label>
        <input type="text" id="newCatName" onkeydown="if(event.key==='Enter') saveNewCategory()">
      </div>
      <div class="modal-field" style="margin-top:4px;">
        <span class="modal-type-label">Expense Type</span>
        <select id="newCatType">
          <option value="Indirect Expense">Indirect Expense</option>
          <option value="Direct Expense">Direct Expense</option>
        </select>
      </div>
      <div class="modal-actions">
        <button class="btn-cancel-modal" onclick="closeModal('addCatModal')">Cancel</button>
        <button class="btn-save-modal" onclick="saveNewCategory()">Save</button>
      </div>
    </div>
  </div>

  {{-- MODAL: Add Item --}}
  <div class="modal-overlay" id="addItemModal">
    <div class="modal-box expense-item-modal">
      <button class="modal-close" onclick="closeModal('addItemModal')">&#x2715;</button>
      <div class="modal-title">Add Expense Item</div>
      <div class="item-modal-grid">
        <div class="item-modal-section">
          <div class="item-modal-section-title">Item Details</div>
          <div class="item-modal-field">
            <label for="newItemName">Item Name *</label>
            <input type="text" id="newItemName" onkeydown="if(event.key==='Enter') saveNewItem()" placeholder="Enter item name">
          </div>
          <div class="item-modal-field">
            <label for="newItemPrice">Pricing</label>
            <input type="number" id="newItemPrice" placeholder="Enter price" min="0" step="0.01">
          </div>
        </div>
        <div class="item-modal-section">
          <div class="item-modal-section-title">Tax Settings</div>
          <div class="item-modal-field">
            <label for="newItemTaxIncluded">Tax Included</label>
            <select id="newItemTaxIncluded">
              <option value="0">Tax Non Included</option>
              <option value="1">Tax Included</option>
            </select>
          </div>
          <div class="item-modal-field">
            <label for="newItemTaxRate">Tax Rate</label>
            <select id="newItemTaxRate">
              <option value="">Select tax rate</option>
              @foreach(($taxRates ?? []) as $taxRate)
                <option value="{{ $taxRate['id'] ?? $taxRate->id }}">{{ $taxRate['name'] ?? $taxRate->name }} ({{ number_format((float) ($taxRate['rate'] ?? $taxRate->rate ?? 0), 2) }}%)</option>
              @endforeach
            </select>
            <div class="item-modal-hint">Tax rates are loaded from your saved tax rate list.</div>
          </div>
        </div>
      </div>
      <div class="item-modal-footer">
        <button class="btn-save-modal" onclick="saveNewItem()">Save</button>
      </div>
    </div>
  </div>

  {{-- MODAL: Add Party --}}
  <div class="modal-overlay" id="addPartyModal">
   <div class="modal-dialog modal-xl modal-dialog-centered expense-party-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPartyModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Add Party</h5>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="partyModalSettingsTrigger" title="Settings"><i class="fa-solid fa-gear"></i></button>
          <button type="button" class="btn-close" onclick="closeModal('addPartyModal')" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body">
        <form id="addPartyForm">
          @csrf
          <div class="row g-3 mb-4">
            <div class="col-md-4" data-party-setting="name">
              <label class="form-label fw-600">Party Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control party-modal-control" placeholder="Enter party name" id="partyNameInput" required>
            </div>
            <div class="col-md-4" data-party-setting="phone">
              <label class="form-label fw-600">Phone Number</label>
              <input type="tel" name="phone" class="form-control party-modal-control" placeholder="Enter phone number" id="partyPhoneInput">
            </div>
            <div class="col-md-4" data-party-setting="phone_2">
              <label class="form-label fw-600">Phone Number 2</label>
              <input type="tel" name="phone_number_2" class="form-control party-modal-control" placeholder="Enter second phone number" id="partyPhone2Input">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">PTCL Number</label>
              <input type="text" name="ptcl_number" class="form-control party-modal-control" placeholder="Enter PTCL number" id="partyPtclInput">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">City</label>
              <input type="text" name="city" class="form-control party-modal-control" placeholder="Enter city" id="partyCityInput">
            </div>


            <div class="col-md-4">
              <label class="form-label fw-600">Party Group</label>
              <div class="party-group-wrap">
                <button type="button" class="party-group-trigger" id="partyGroupTrigger" onclick="toggleExpensePartyGroupMenu(event)">
                  <span class="text" id="partyGroupText">Select party group</span>
                  <i class="fa fa-chevron-down"></i>
                </button>
                <input type="hidden" name="party_group" id="partyGroupInput">
                <div id="partyGroupMenu" class="party-group-menu d-none">
                  <button type="button" class="dropdown-item text-primary" id="addNewGroupBtn" onclick="openExpensePartyGroupModal()">
                    + New Group
                  </button>
                  <div id="partyGroupList">
                    @foreach(($partyGroups ?? []) as $partyGroup)
                      <button type="button" class="dropdown-item" data-group="{{ $partyGroup->name }}">
                        {{ $partyGroup->name }}
                      </button>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="partyModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="party-address-tab" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab" aria-controls="partyAddressPane" aria-selected="true">
                <i class="fa-solid fa-location-dot me-1"></i> Address
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-credit-tab" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab" aria-controls="partyCreditPane" aria-selected="false">
                <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-additional-tab" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab" aria-controls="partyAdditionalPane" aria-selected="false">
                <i class="fa-solid fa-sliders me-1"></i> Additional Fields
              </button>
            </li>
          </ul>

          <div class="tab-content pt-3" id="partyModalTabContent">
            <!-- Address Tab -->
            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel" aria-labelledby="party-address-tab">
              <div class="row g-3">
                <div class="col-md-6" data-party-setting="email">
                  <label class="form-label fw-600">Email ID</label>
                  <input type="email" name="email" class="form-control party-modal-control" placeholder="example@email.com" value="">
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                  <label class="form-label fw-600">Address</label>
                  <textarea id="partyAddressInput" class="form-control party-modal-control party-modal-textarea" name="address" rows="3" placeholder="Enter address"></textarea>
                </div>
                <div class="col-md-6" data-party-setting="billing_address">
                  <label class="form-label fw-600">Billing Address</label>
                  <textarea id="billingAddress" class="form-control party-modal-control party-modal-textarea" name="billing_address" rows="3" placeholder="Enter billing address"></textarea>
                </div>
                <div class="col-md-6" data-party-setting="shipping_address">
                  <label class="form-label fw-600">Shipping Address</label>
                  <textarea id="shippingAddress" class="form-control party-modal-control party-modal-textarea" name="shipping_address" rows="3" placeholder="Enter shipping address"></textarea>
                </div>
              </div>
            </div>

            <!-- Credit & Balance Tab -->
            <div class="tab-pane fade" id="partyCreditPane" role="tabpanel" aria-labelledby="party-credit-tab">
              <div class="row g-4">
                <div class="col-md-4" data-party-setting="opening_balance">
                  <label class="form-label fw-600">Opening Balance</label>
                  <div class="input-group party-modal-input-group">
                    <span class="input-group-text">Rs</span>
                    <input type="number" name="opening_balance" class="form-control party-modal-control" placeholder="0.00" min="0" step="0.01">
                  </div>
                </div>
                <div class="col-md-4" data-party-setting="as_of_date">
                  <label class="form-label fw-600">As Of Date</label>
                  <input type="date" name="as_of_date" class="form-control party-modal-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4" data-party-setting="credit_limit">
                  <label class="form-label fw-600 d-block">Credit Limit</label>
                  <div class="form-check form-switch mb-2">
                    <input class="form-check-input" name="credit_limit_enabled" type="checkbox" id="creditLimitSwitch">
                    <label class="form-check-label" for="creditLimitSwitch">Enable</label>
                  </div>
                  <div class="input-group party-modal-input-group is-hidden" id="creditLimitAmountWrap">
                    <span class="input-group-text">Rs</span>
                    <input type="number" name="credit_limit_amount" class="form-control party-modal-control" placeholder="Enter credit limit" id="creditLimitAmountInput" min="0" step="0.01">
                  </div>
                </div>
                <div class="col-md-4" data-party-setting="due_days">
                  <label class="form-label fw-600">Due Days</label>
                  <input type="number" name="due_days" class="form-control party-modal-control" placeholder="e.g. 5, 10, 30" min="1" max="100" id="partyDueDaysInput">
                </div>
              </div>

              <div class="mt-4" data-party-setting="transaction_type">
                <label class="form-label fw-600 d-block">Transaction Type</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="toReceive" value="receive">
                  <label class="form-check-label" for="toReceive">To Receive</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="toPay" value="pay">
                  <label class="form-check-label" for="toPay">To Pay</label>
                </div>
              </div>

              <div class="row g-3 mt-4" data-party-setting="party_type">
                <div class="col-12">
                  <label class="form-label fw-600 d-block">Party Type</label>
                  <div class="form-check">
                    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="customerParty" value="customer">
                    <label class="form-check-label" for="customerParty">Customer</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="supplierParty" value="supplier">
                    <label class="form-check-label" for="supplierParty">Supplier</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="brokerParty" value="broker">
                    <label class="form-check-label" for="brokerParty">Broker</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Fields Tab -->
            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel" aria-labelledby="party-additional-tab" data-party-setting="additional_fields">
              <p class="text-muted mb-3" style="font-size:13px;">Add custom fields to track additional information.</p>
              <div class="row g-3">
                @for($i=1; $i<=4; $i++)
                <div class="col-md-6">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="customField{{$i}}Check">
                    <label class="form-check-label" for="customField{{$i}}Check">Custom Field {{$i}}</label>
                  </div>
                  <input type="text" name="custom_fields[]" class="form-control form-control-sm" placeholder="Field name">
                </div>
                @endfor

              </div>
            </div>
          </div>


          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" id="btnSaveNewParty">
              <i class="fa-solid fa-plus me-1"></i> Save & New
            </button>
            <button type="button" class="btn btn-primary" id="btnSaveParty">
              <i class="fa-solid fa-check me-1"></i> Save
            </button>
 <button type="button" class="btn btn-primary" id="btnUpdateParty" style="display:none;">Update</button>
    <button type="button" class="btn btn-danger" id="btnDeleteParty" style="display:none;">Delete</button>
          </div>
        </form>

      </div>
    </div>
</div>
  </div>

  <div class="modal-overlay" id="partyGroupModal" style="z-index: 1150;">
    <div class="modal-box" style="width: 430px; max-width: 92vw;">
      <button class="modal-close" type="button" onclick="closeExpensePartyGroupModal()">&#x2715;</button>
      <div class="modal-title">New Party Group</div>
      <div class="modal-field" style="margin-bottom: 18px;">
        <label for="partyGroupNameInput">Group Name</label>
        <input type="text" id="partyGroupNameInput" placeholder="e.g. Wholesale">
      </div>
      <div class="modal-actions" style="margin-top: 0;">
        <button type="button" class="btn-cancel-modal" onclick="closeExpensePartyGroupModal()">Cancel</button>
        <button type="button" class="btn-save-modal" onclick="saveExpensePartyGroup()">Save</button>
      </div>
    </div>
  </div>

  {{-- MODAL: Edit Item --}}
  <div class="modal-overlay" id="editItemModal">
    <div class="modal-box expense-item-modal">
      <button class="modal-close" onclick="closeModal('editItemModal')">&#x2715;</button>
      <div class="modal-title">Edit Expense Item</div>
      <div class="item-modal-grid">
        <div class="item-modal-section">
          <div class="item-modal-section-title">Item Details</div>
          <div class="item-modal-field">
            <label for="editItemName">Item Name *</label>
            <input type="text" id="editItemName">
          </div>
          <div class="item-modal-field">
            <label for="editItemPrice">Pricing</label>
            <input type="number" id="editItemPrice" placeholder="Enter price" min="0" step="0.01">
          </div>
        </div>
        <div class="item-modal-section">
          <div class="item-modal-section-title">Tax Settings</div>
          <div class="item-modal-field">
            <label for="editItemTaxIncluded">Tax Included</label>
            <select id="editItemTaxIncluded">
              <option value="0">Tax Non Included</option>
              <option value="1">Tax Included</option>
            </select>
          </div>
          <div class="item-modal-field">
            <label for="editItemTaxRate">Tax Rate</label>
            <select id="editItemTaxRate">
              <option value="">Select tax rate</option>
              @foreach(($taxRates ?? []) as $taxRate)
                <option value="{{ $taxRate['id'] ?? $taxRate->id }}">{{ $taxRate['name'] ?? $taxRate->name }} ({{ number_format((float) ($taxRate['rate'] ?? $taxRate->rate ?? 0), 2) }}%)</option>
              @endforeach
            </select>
            <div class="item-modal-hint">Tax rates are loaded from your saved tax rate list.</div>
          </div>
        </div>
      </div>
      <div class="item-modal-footer">
        <button class="btn-delete-modal" onclick="deleteItemFromEditModal()">Delete</button>
        <button class="btn-save-modal" onclick="saveEditItem()">Save</button>
      </div>
    </div>
  </div>

  {{-- CONFIRM DIALOG --}}
  <div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
      <div class="confirm-header">
        <span id="confirmTitle">Vyapar</span>
        <button class="confirm-close-btn" onclick="closeConfirm()">&#x2715;</button>
      </div>
      <div class="confirm-msg" id="confirmMsg"></div>
      <div class="confirm-actions">
        <button class="btn-confirm-yes" id="confirmYesBtn">YES</button>
        <button class="btn-confirm-no" onclick="closeConfirm()">NO</button>
      </div>
    </div>
  </div>

  {{-- CLOSE EXPENSE CONFIRM --}}
  <div class="close-expense-overlay" id="closeExpenseOverlay">
    <div class="close-expense-box">
      <div class="close-expense-title">
        Close Expense
        <button style="background:none;border:none;font-size:18px;cursor:pointer;color:#888;" onclick="closeExpenseCancel()">&#x2715;</button>
      </div>
      <div class="close-expense-msg">Current changes will be discarded. Do you wish to continue?</div>
      <div class="close-expense-actions">
        <button class="btn-cancel-close" onclick="closeExpenseCancel()">Cancel</button>
        <button class="btn-confirm-close" onclick="closeExpenseConfirm()">OK</button>
      </div>
    </div>
  </div>

  {{-- TOAST --}}
  <div class="toast-custom" id="toastEl">
    <span class="toast-icon-el"><i class="bi bi-shield-exclamation"></i></span>
    <span class="toast-msg-el" id="toastMsg"></span>
    <span class="toast-close-btn" onclick="hideToast()">&#x2715;</span>
  </div>

  {{-- PREVIEW MODAL (like Image 3) --}}
  <div class="modal-overlay" id="previewModal" style="z-index:970;">
    <div class="modal-box" style="width:780px;max-width:96vw;padding:0;border-radius:10px;overflow:hidden;">
      <div style="background:#fff;padding:16px 24px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:15px;font-weight:700;color:#1a1f36;">Preview</span>
        <button class="modal-close" style="position:static;" onclick="closeModal('previewModal')">&#x2715;</button>
      </div>
      <div style="padding:24px 32px;overflow-y:auto;max-height:72vh;" id="previewContent"></div>
      <div style="background:#fff;border-top:1px solid #e0e0e0;padding:14px 24px;display:flex;justify-content:flex-end;gap:10px;">
        <button onclick="previewOpenPDF()" style="border:1.5px solid #D4112E;color:#D4112E;background:#fff;border-radius:20px;padding:8px 18px;font-size:13px;cursor:pointer;">Open PDF</button>
        <button onclick="previewDoPrint()" style="border:1.5px solid #D4112E;color:#D4112E;background:#fff;border-radius:20px;padding:8px 18px;font-size:13px;cursor:pointer;">Print</button>
        <button onclick="previewSavePDF()" style="border:1.5px solid #D4112E;color:#D4112E;background:#fff;border-radius:20px;padding:8px 18px;font-size:13px;cursor:pointer;">Save PDF</button>
        <button onclick="previewEmailPDF()" style="border:1.5px solid #D4112E;color:#D4112E;background:#fff;border-radius:20px;padding:8px 18px;font-size:13px;cursor:pointer;">Email PDF</button>
        <button onclick="closeModal('previewModal')" style="border:none;background:#D4112E;color:#fff;border-radius:20px;padding:8px 22px;font-size:13px;font-weight:600;cursor:pointer;">Close</button>
      </div>
    </div>
  </div>

  {{-- VIEW HISTORY MODAL (like Image 1) --}}
  <div class="modal-overlay" id="viewHistoryModal" style="z-index:970;">
    <div class="modal-box" style="width:760px;max-width:96vw;padding:0;border-radius:10px;overflow:hidden;">
      <div style="background:#fff;padding:16px 24px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:15px;font-weight:700;color:#1a1f36;">Edit History for Expense</span>
        <button class="modal-close" style="position:static;" onclick="closeModal('viewHistoryModal')">&#x2715;</button>
      </div>
      <div id="historyContent" style="padding:40px 24px;text-align:center;min-height:320px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
        {{-- Content injected by JS --}}
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/expenses.js') }}"></script>

  <script>
  // ═══════════════════════════════════════════════════════
  //  STATE — loaded from DB via controller
  // ═══════════════════════════════════════════════════════
  let categories   = @json($categories   ?? []);
  let expenseItems = @json($expenseItems ?? []);
  const expenseBoot = window.expenseBootstrap || {};
  const expenseParties = Array.isArray(expenseBoot.parties) ? expenseBoot.parties : [];
  const expensePartyGroups = Array.isArray(expenseBoot.partyGroups) ? expenseBoot.partyGroups : [];
  const expenseBankAccounts = Array.isArray(expenseBoot.bankAccounts) ? expenseBoot.bankAccounts : [];
  const expenseTaxRates = Array.isArray(expenseBoot.taxRates) ? expenseBoot.taxRates : [];
  const expenseTransactionSettings = expenseBoot.transactionSettings || {};
  const expenseHasTaxRates = !!expenseBoot.hasTaxRates;
  let selectedCatIdx  = 0;
  let selectedItemIdx = 0;
  let editingItemIdx  = -1;
  let currentTab      = 'category';
  let rowKey          = 0;
  let tabCounter      = 0;
  let paymentRows     = [];
  let expenseAttachmentFiles = { images: [], documents: [] };
  let pendingConfirmCb = null;
  let calViewDate = new Date();
  let calSelDate  = new Date();
  let closingTabN = null;
  const expenseEditParam = new URLSearchParams(window.location.search).get('expense_id');

  // ── Per-tab state storage ──
  const tabStates = {};
  let activeTabN  = null;

  function defaultTabState() {
    return {
      catName  : '',
      partyId  : '',
      partyName: '',
      poNo: '',
      poDate: '',
      transactionTime: '',
      dealDays: 0,
      dueDate: '',
      paymentTermsName: '',
      status: 'unpaid',
      taxEnabled: false,
      discountPercent: 0,
      discountAmount: 0,
      summaryTaxRateId: '',
      summaryTaxAmount: 0,
      expNo    : '',
      date     : new Date(),
      items    : [],
      payments : [{ type: 'Cheque', ref: '' }],
      description: '',
      attachments: { images: [], documents: [] },
      additionalCharges: {},
      transportationDetails: {},
      roundOff : false,
      editingExpenseId : null,
      editingCatIdx    : null,
    };
  }
  function openDirectPDF(expId, catIdx) {
  document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
  window._previewExpId  = expId;
  window._previewCatIdx = catIdx;
  previewOpenPDF();
}

  function openExpenseById(expId) {
    const id = Number(expId);
    if (!id) return false;
    for (let ci = 0; ci < categories.length; ci++) {
      const entry = (categories[ci].entries || []).find(e => Number(e.id) === id);
      if (entry) {
        openViewEdit(entry.id, ci);
        return true;
      }
    }
    return false;
  }

  function loadExpenseEntryIntoState(entry, catIdx) {
    const state = tabStates[activeTabN] || defaultTabState();
    state.catName = categories[catIdx]?.name || '';
    state.partyId = entry.party_id || '';
    state.partyName = entry.party || '';
    state.poNo = entry.poNo || '';
    state.poDate = entry.poDate || '';
    state.transactionTime = entry.transactionTime || '';
    state.dealDays = entry.dealDays || 0;
    state.dueDate = entry.dueDate || '';
    state.paymentTermsName = entry.paymentTermsName || '';
    state.status = entry.status || (parseFloat(entry.balance || 0) <= 0 ? 'paid' : (parseFloat(entry.paidAmount || 0) > 0 ? 'partial' : 'unpaid'));
    state.taxEnabled = !!entry.taxEnabled;
    state.discountPercent = parseFloat(entry.discountPercent || entry.discount_percent || 0) || 0;
    state.discountAmount = parseFloat(entry.discountAmount || entry.discount_amount || 0) || 0;
    state.summaryTaxRateId = entry.summaryTaxRateId || entry.summary_tax_rate_id || entry.taxRateId || entry.tax_rate_id || '';
    state.summaryTaxAmount = parseFloat(entry.summaryTaxAmount || entry.summary_tax_amount || entry.taxAmount || entry.tax_amount || 0) || 0;
    state.expNo = entry.expNo || '';
    state.date = entry.date ? new Date(entry.date) : new Date();
    state.description = entry.description || '';
    state.roundOff = false;
    state.items = Array.isArray(entry.items)
      ? entry.items.map((it, idx) => ({
          rk: idx + 1,
          name: it.name || '',
          qty: it.qty || it.quantity || 1,
          price: it.price || it.unit_price || 0,
          taxRateId: it.taxRateId || it.tax_rate_id || '',
          taxRateName: it.taxRateName || it.tax_rate_name || '',
          taxRateValue: it.taxRateValue || it.tax_rate_value || 0,
          taxAmount: it.taxAmount || it.tax_amount || 0,
          baseAmount: (parseFloat(it.qty || it.quantity || 1) || 0) * (parseFloat(it.price || it.unit_price || 0) || 0),
          amount: it.amount || 0,
        }))
      : [];
    state.payments = [{
      type: entry.bankAccountId ? `bank:${entry.bankAccountId}` : (entry.paymentType || 'Cheque'),
      ref: entry.reference_no || '',
      amount: entry.paidAmount ?? Math.max((parseFloat(entry.amount || 0) - parseFloat(entry.balance || 0)), 0),
    }];
    state.additionalCharges = entry.additionalCharges || {};
    state.transportationDetails = entry.transportationDetails || {};
    state.editingExpenseId = entry.id || null;
    state.editingCatIdx = catIdx;
    tabStates[activeTabN] = state;
    window._editingExpenseId = entry.id || null;
    window._editingCatIdx = catIdx;
  }

  // ── Sort state ──
  let detailSortCol = 'amount';
  let detailSortDir = 'desc';
  let itemsSortCol = 'date';
  let itemsSortDir = 'desc';

  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // ─── Ajax helper ───
  function ajax(method, url, data) {
    return fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: data ? JSON.stringify(data) : undefined,
    }).then(r => r.json());
  }

  function getExpenseTaxRateById(rateId) {
    return expenseTaxRates.find(rate => String(rate.id) === String(rateId)) || null;
  }

  function isExpenseTaxOn() {
    return !!document.getElementById('expenseTaxSwitch')?.checked;
  }

  function getExpensePartyById(partyId) {
    return expenseParties.find(p => String(p.id) === String(partyId)) || null;
  }

  function formatExpenseStatusLabel(value) {
    const raw = String(value || '').trim().toLowerCase();
    if (!raw) return 'Unpaid';
    return raw.replace(/\b\w/g, ch => ch.toUpperCase());
  }

  function getExpenseDerivedStatus(totalAmount, paidAmount) {
    const total = parseFloat(totalAmount || 0);
    const paid = parseFloat(paidAmount || 0);
    if (total <= 0) return 'unpaid';
    if (paid >= total) return 'paid';
    if (paid > 0) return 'partial';
    return 'unpaid';
  }

  function formatExpenseDisplayDate(value) {
    const parsed = parseExpenseDateDisplay(value);
    return parsed ? formatExpenseDateDisplay(parsed) : '';
  }

  function getExpensePreviewMetaHtml(entry) {
    const rows = [
      ['Date', formatDisplayDate(entry.date)],
      ['Exp No', entry.expNo],
      ['PO No', entry.poNo],
      ['PO Date', formatExpenseDateDisplay(entry.poDate || '')],
      ['Transaction Time', entry.transactionTime],
      ['Payment Terms', entry.paymentTermsName],
      ['Deal Days', entry.dealDays ? `${entry.dealDays} Days` : ''],
      ['Due Date', formatExpenseDateDisplay(entry.dueDate || '')],
      ['Status', formatExpenseStatusLabel(entry.status)],
    ].filter(([, value]) => String(value || '').trim() !== '');

    return rows.map(([label, value]) => `
      <div style="display:flex;justify-content:space-between;gap:8px;padding:3px 0;">
        <span style="color:#555;font-weight:600;">${escHtml(label)}</span>
        <span style="font-weight:700;">${escHtml(value)}</span>
      </div>
    `).join('');
  }

  function getExpenseDisplayStatus(value) {
    return formatExpenseStatusLabel(value || '');
  }

  function getExpenseDisplayDueDate(value) {
    return formatExpenseDisplayDate(value || '');
  }

  function formatExpenseMoney(value) {
    const num = parseFloat(value) || 0;
    return num.toFixed(2);
  }

  function humanizeExpenseLabel(key) {
    return String(key || '')
      .replace(/_/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .replace(/\b\w/g, ch => ch.toUpperCase()) || 'Field';
  }

  function getExpenseTransportFieldLabel(field, index = 0) {
    const key = String(field?.key || '').trim();
    const label = String(field?.label || '').trim();
    if (label) return label;

    const defaults = {
      field_1: 'Transport Name',
      field_2: 'Vehicle Number',
      field_3: 'Delivery Date',
      field_4: 'Delivery Location',
      field_5: 'Field 5',
    };

    if (key && defaults[key]) return defaults[key];
    if (key) return humanizeExpenseLabel(key);
    return `Field ${index + 1}`;
  }

  function normalizeExpenseSettingFields(rawFields) {
    const list = Array.isArray(rawFields)
      ? rawFields
      : (rawFields && typeof rawFields === 'object' ? Object.values(rawFields) : []);

    return list
      .map(field => {
        if (!field) return null;
        if (typeof field === 'string') {
          return {
            key: field,
            label: humanizeExpenseLabel(field),
            enabled: true,
          };
        }
        const key = String(field.key || field.name || field.id || '').trim();
        if (!key) return null;
        return {
          ...field,
          key,
          label: String(field.label || field.name || field.title || humanizeExpenseLabel(key)).trim(),
          enabled: field.enabled !== false,
        };
      })
      .filter(Boolean);
  }

  function getExpensePartyGroups() {
    return Array.isArray(expensePartyGroups) ? expensePartyGroups : [];
  }

  function renderExpensePartyGroupOptions(selectedValue = '') {
    const menu = document.getElementById('partyGroupMenu');
    const list = document.getElementById('partyGroupList');
    const input = document.getElementById('partyGroupInput');
    const text = document.getElementById('partyGroupText');
    if (!list || !input || !text) return;

    const value = String(selectedValue || '').trim();
    input.value = value;
    text.textContent = value || 'Select party group';

    const groups = getExpensePartyGroups();
    list.innerHTML = groups.length
      ? groups.map(group => {
          const groupName = String(group?.name || '').trim();
          if (!groupName) return '';
          const active = value === groupName ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '';
          return `<button type="button" class="dropdown-item" data-group="${escHtml(groupName)}" style="${active}">${escHtml(groupName)}</button>`;
        }).join('')
      : '<div class="party-group-empty">No groups found.</div>';

    list.querySelectorAll('[data-group]').forEach(btn => {
      btn.addEventListener('click', () => {
        selectExpensePartyGroup(btn.dataset.group || '');
      });
    });

    menu?.querySelectorAll('.dropdown-item.text-primary')?.forEach(btn => {
      btn.style.position = 'sticky';
      btn.style.top = '0';
      btn.style.zIndex = '1';
    });
  }

  function toggleExpensePartyGroupMenu(event) {
    event?.stopPropagation?.();
    const menu = document.getElementById('partyGroupMenu');
    if (!menu) return;
    const isOpen = !menu.classList.contains('d-none');
    if (isOpen) {
      menu.classList.add('d-none');
    } else {
      renderExpensePartyGroupOptions(document.getElementById('partyGroupInput')?.value || '');
      menu.classList.remove('d-none');
    }
  }

  function selectExpensePartyGroup(groupName) {
    const menu = document.getElementById('partyGroupMenu');
    const input = document.getElementById('partyGroupInput');
    const text = document.getElementById('partyGroupText');
    if (input) input.value = groupName || '';
    if (text) text.textContent = groupName || 'Select party group';
    menu?.classList.add('d-none');
  }

  function openExpensePartyGroupModal() {
    document.getElementById('partyGroupNameInput').value = '';
    document.getElementById('partyGroupMenu')?.classList.add('d-none');
    openModal('partyGroupModal');
    setTimeout(() => document.getElementById('partyGroupNameInput')?.focus(), 60);
  }

  function closeExpensePartyGroupModal() {
    closeModal('partyGroupModal');
  }

  function saveExpensePartyGroup() {
    const input = document.getElementById('partyGroupNameInput');
    const name = input?.value.trim();
    if (!name) {
      showToast('Party group name is required.', 'red');
      return;
    }
    const btn = document.querySelector('#partyGroupModal .btn-save-modal');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Saving...';
    }
    fetch(window.expenseRoutes.partyGroupStore, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ name }),
    })
      .then(r => r.json())
      .then(res => {
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Save';
        }
        if (res.success && res.partyGroup) {
          const created = {
            id: res.partyGroup.id,
            name: res.partyGroup.name,
          };
          if (!expensePartyGroups.some(group => String(group.name) === String(created.name))) {
            expensePartyGroups.push(created);
          }
          renderExpensePartyGroupOptions(created.name);
          selectExpensePartyGroup(created.name);
          closeExpensePartyGroupModal();
          showToast('Party group added successfully.', 'green');
        } else {
          showToast('Could not add party group.', 'red');
        }
      })
      .catch(() => {
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Save';
        }
        showToast('Could not add party group.', 'red');
      });
  }

  function normalizeExpenseAttachments(raw = {}) {
    const base = { images: [], documents: [] };
    if (!raw || typeof raw !== 'object') return base;
    return {
      images: Array.isArray(raw.images) ? raw.images.filter(Boolean) : [],
      documents: Array.isArray(raw.documents) ? raw.documents.filter(Boolean) : [],
    };
  }

  function getExpenseStoredAttachments() {
    const state = currentExpenseState();
    state.attachments = normalizeExpenseAttachments(state.attachments);
    return state.attachments;
  }

  function getExpenseAttachmentFileName(path) {
    if (!path) return 'Attachment';
    const clean = String(path).split('?')[0];
    return clean.split('/').pop() || 'Attachment';
  }

  function renderExpenseTaxRateOptions(selectedId = '') {
    return expenseTaxRates.map(rate => {
      const id = String(rate.id);
      const selected = String(selectedId) === id ? 'selected' : '';
      const label = `${rate.name || 'Tax'} (${parseFloat(rate.rate || 0)}%)`;
      return `<option value="${id}" ${selected}>${escHtml(label)}</option>`;
    }).join('');
  }

  function getExpenseItemTaxPayloadFromModal(prefix) {
    const taxRateId = document.getElementById(prefix + 'TaxRate')?.value || '';
    const taxRate = getExpenseTaxRateById(taxRateId);
    const taxIncluded = document.getElementById(prefix + 'TaxIncluded')?.value === '1';
    const price = parseFloat(document.getElementById(prefix + 'Price')?.value) || 0;
    const rate = parseFloat(taxRate?.rate || 0);
    let taxAmount = 0;
    let amount = price;

    if (taxRate && rate > 0) {
      if (taxIncluded) {
        const baseAmount = price / (1 + rate / 100);
        taxAmount = Math.max(price - baseAmount, 0);
        amount = price;
      } else {
        taxAmount = price * (rate / 100);
        amount = price + taxAmount;
      }
    }

    return {
      tax_included: taxIncluded ? 1 : 0,
      tax_rate_id: taxRate?.id || '',
      tax_rate_name: taxRate?.name || '',
      tax_rate_value: taxRate?.rate || 0,
      tax_amount: Number.isFinite(taxAmount) ? parseFloat(taxAmount.toFixed(2)) : 0,
      amount: Number.isFinite(amount) ? parseFloat(amount.toFixed(2)) : price,
    };
  }

  function renderExpensePartyOptions(filterText = '') {
    const box = document.getElementById('expensePartyOptions');
    if (!box) return;
    const query = String(filterText || '').trim().toLowerCase();
    const rows = expenseParties.filter(party => {
      const haystack = [party.name, party.phone, party.phone_number_2, party.ptcl_number, party.email]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();
      return !query || haystack.includes(query);
    });

    box.innerHTML = rows.length ? rows.map(party => {
      const balance = parseFloat(party.current_balance || party.opening_balance || 0);
      const sign = party.transaction_type === 'pay' ? '-' : '';
      return `
        <div class="expense-party-option" data-id="${party.id}" data-name="${String(party.name || '').replace(/"/g, '&quot;')}"
             data-balance="${balance}" data-phone="${String(party.phone || '').replace(/"/g, '&quot;')}">
          <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:10px 12px; border-bottom:1px solid #eef2f7; cursor:pointer;">
            <div style="min-width:0; flex:1;">
              <div style="font-size:13px; font-weight:700; color:#1a1f36;">${party.name || ''}</div>
              <div style="font-size:11px; color:#64748b;">${party.phone || '-'}</div>
            </div>
            <div style="font-size:12px; font-weight:700; color:${party.transaction_type === 'pay' ? '#dc2626' : '#059669'};">
              ${sign}Rs ${balance.toFixed(2)}
            </div>
          </div>
        </div>
      `;
    }).join('') : '<div style="padding:14px 12px; font-size:12px; color:#94a3b8;">No parties found.</div>';

    box.innerHTML += `
      <div style="border-top:1px solid #eef2f7; padding:10px 12px;">
        <button type="button" onclick="openExpensePartyCreate()" style="display:flex; align-items:center; gap:8px; border:none; background:none; color:#2563eb; font-size:13px; font-weight:600; cursor:pointer; padding:0;">
          <i class="fa-solid fa-user-plus"></i>
          Add Party
        </button>
      </div>
    `;

    box.querySelectorAll('.expense-party-option').forEach(option => {
      option.addEventListener('click', () => selectExpenseParty({
        id: option.dataset.id,
        name: option.dataset.name,
        balance: option.dataset.balance,
      }));
    });
  }

  function openExpensePartyDropdown() {
    const menu = document.getElementById('expensePartyMenu');
    if (!menu) return;
    menu.style.display = 'block';
    renderExpensePartyOptions(document.getElementById('expensePartySearch')?.value || '');
  }

  function closeExpensePartyDropdown() {
    const menu = document.getElementById('expensePartyMenu');
    if (menu) menu.style.display = 'none';
  }

  function filterExpensePartyDropdown() {
    openExpensePartyDropdown();
  }

  function selectExpenseParty(party) {
    const idEl = document.getElementById('expensePartyId');
    const searchEl = document.getElementById('expensePartySearch');
    const balanceEl = document.getElementById('expensePartyBalance');
    if (idEl) idEl.value = party.id || '';
    if (searchEl) searchEl.value = party.name || '';
    if (balanceEl) balanceEl.textContent = party.balance ? `Balance: Rs ${formatExpenseMoney(party.balance)}` : '';
    closeExpensePartyDropdown();
  }

  function clearExpenseParty() {
    const idEl = document.getElementById('expensePartyId');
    const searchEl = document.getElementById('expensePartySearch');
    const balanceEl = document.getElementById('expensePartyBalance');
    if (idEl) idEl.value = '';
    if (searchEl) searchEl.value = '';
    if (balanceEl) balanceEl.textContent = '';
    closeExpensePartyDropdown();
  }

  function openExpensePartyCreate() {
    resetExpensePartyModal();
    renderExpensePartyGroupOptions(document.getElementById('partyGroupInput')?.value || '');
    openModal('addPartyModal');
    setTimeout(() => document.getElementById('partyNameInput')?.focus(), 80);
  }

  function resetExpensePartyModal() {
    const form = document.getElementById('addPartyForm');
    if (form) form.reset();
    const toPayEl = document.getElementById('toPay');
    const supplierEl = document.getElementById('supplierParty');
    if (toPayEl) toPayEl.checked = true;
    if (supplierEl) supplierEl.checked = true;
    const partyGroupText = document.getElementById('partyGroupText');
    const partyGroupInput = document.getElementById('partyGroupInput');
    if (partyGroupText) partyGroupText.textContent = 'Select party group';
    if (partyGroupInput) partyGroupInput.value = '';
    document.getElementById('partyGroupMenu')?.classList.add('d-none');
    closeExpensePartyDropdown();
    document.getElementById('creditLimitAmountWrap')?.classList.add('is-hidden');
    const updateBtn = document.getElementById('btnUpdateParty');
    const deleteBtn = document.getElementById('btnDeleteParty');
    if (updateBtn) updateBtn.style.display = 'none';
    if (deleteBtn) deleteBtn.style.display = 'none';
  }

  function saveExpenseParty(closeAfterSave, sourceBtnId) {
    const form = document.getElementById('addPartyForm');
    if (!form || !window.expenseRoutes?.partyStore) return;

    const name = document.getElementById('partyNameInput')?.value.trim();
    if (!name) {
      showToast('Party name is required.', 'red');
      return;
    }

    const sourceBtn = sourceBtnId ? document.getElementById(sourceBtnId) : null;
    if (sourceBtn) {
      sourceBtn.disabled = true;
      sourceBtn.dataset.originalText = sourceBtn.innerHTML;
      sourceBtn.innerHTML = 'Saving...';
    }

    const formData = new FormData(form);
    const transactionType = document.getElementById('toReceive')?.checked ? 'receive' : (document.getElementById('toPay')?.checked ? 'pay' : 'pay');
    formData.set('transaction_type', transactionType);
    formData.set('credit_limit_enabled', document.getElementById('creditLimitSwitch')?.checked ? '1' : '0');
    formData.set('party_group', document.getElementById('partyGroupInput')?.value || '');

    fetch(window.expenseRoutes.partyStore, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    })
      .then(async r => {
        const contentType = r.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await r.json() : { message: await r.text() };
        if (!r.ok) {
          const firstError = payload?.errors ? Object.values(payload.errors).flat()[0] : null;
          throw new Error(firstError || payload?.message || 'Could not add party.');
        }
        return payload;
      })
      .then(res => {
        if (sourceBtn) {
          sourceBtn.disabled = false;
          sourceBtn.innerHTML = sourceBtn.dataset.originalText || 'Save';
          delete sourceBtn.dataset.originalText;
        }

        if (res.success && res.party) {
          const normalized = {
            id: res.party.id,
            name: res.party.name,
            phone: res.party.phone || '',
            phone_number_2: res.party.phone_number_2 || '',
            ptcl_number: res.party.ptcl_number || '',
            email: res.party.email || '',
            city: res.party.city || '',
            party_group: res.party.party_group || '',
            address: res.party.address || '',
            billing_address: res.party.billing_address || '',
            shipping_address: res.party.shipping_address || '',
            opening_balance: res.party.opening_balance || 0,
            current_balance: res.party.current_balance || res.party.opening_balance || 0,
            transaction_type: res.party.transaction_type || transactionType || 'pay',
          };
          expenseParties.push(normalized);
          renderExpensePartyOptions('');
          selectExpenseParty(normalized);
          renderExpensePartyGroupOptions(normalized.party_group || '');
          if (closeAfterSave) {
            closeModal('addPartyModal');
          } else {
            resetExpensePartyModal();
            document.getElementById('partyNameInput')?.focus();
          }
          showToast('Party added successfully.', 'green');
        } else {
          showToast(res.message || 'Could not add party.', 'red');
        }
      })
      .catch((error) => {
        if (sourceBtn) {
          sourceBtn.disabled = false;
          sourceBtn.innerHTML = sourceBtn.dataset.originalText || 'Save';
          delete sourceBtn.dataset.originalText;
        }
        showToast(error.message || 'Could not add party.', 'red');
      });
  }

  document.getElementById('btnSaveParty')?.addEventListener('click', () => saveExpenseParty(true, 'btnSaveParty'));
  document.getElementById('btnSaveNewParty')?.addEventListener('click', () => saveExpenseParty(false, 'btnSaveNewParty'));

  function openAddItemModal() {
    const nameEl = document.getElementById('newItemName');
    const priceEl = document.getElementById('newItemPrice');
    const taxIncludedEl = document.getElementById('newItemTaxIncluded');
    const taxRateEl = document.getElementById('newItemTaxRate');
    if (nameEl) nameEl.value = '';
    if (priceEl) priceEl.value = '';
    if (taxIncludedEl) taxIncludedEl.value = '0';
    if (taxRateEl) taxRateEl.value = '';
    openModal('addItemModal');
    setTimeout(() => nameEl?.focus(), 80);
  }

  function openExpenseAttachmentPicker(type) {
    const input = type === 'document'
      ? document.querySelector('.expense-document-input')
      : document.querySelector('.expense-image-input');
    input?.click();
  }

  function handleExpenseAttachmentSelection(type, files) {
    const bucket = type === 'document' ? expenseAttachmentFiles.documents : expenseAttachmentFiles.images;
    Array.from(files || []).forEach(file => {
      if (file && file.size > 0) bucket.push(file);
    });
    renderExpenseAttachmentPreview();
  }

  function removeExpenseAttachment(type, index) {
    const bucket = type === 'document' ? expenseAttachmentFiles.documents : expenseAttachmentFiles.images;
    bucket.splice(index, 1);
    renderExpenseAttachmentPreview();
  }

  function renderExpenseAttachmentPreview() {
    const imageWrap = document.querySelector('.image-files-list');
    const documentWrap = document.querySelector('.document-files-list');
    const uploadSection = document.querySelector('.expense-image-upload-section');
    if (uploadSection) {
      uploadSection.classList.toggle('has-files', expenseAttachmentFiles.images.length > 0 || expenseAttachmentFiles.documents.length > 0);
    }
    if (imageWrap) {
      imageWrap.innerHTML = expenseAttachmentFiles.images.map((file, index) => `
        <div class="d-flex align-items-center gap-2 px-2 py-1 rounded border bg-white">
          <i class="fa-solid fa-image text-primary"></i>
          <span class="small text-muted">${escHtml(file.name || 'Attachment')}</span>
          <button type="button" class="btn btn-link p-0 text-danger" onclick="removeExpenseAttachment('image', ${index})">×</button>
        </div>
      `).join('');
    }
    if (documentWrap) {
      documentWrap.innerHTML = expenseAttachmentFiles.documents.map((file, index) => `
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border rounded bg-white mt-2">
          <span class="small text-muted">${escHtml(file.name || 'Attachment')}</span>
          <button type="button" class="btn btn-link p-0 text-danger" onclick="removeExpenseAttachment('document', ${index})">Remove</button>
        </div>
      `).join('');
    }
  }

  function toggleExpenseTax(enabled) {
    const taxHead = document.getElementById('expenseTaxHead');
    document.querySelectorAll('.expense-tax-cell').forEach(el => el.classList.toggle('d-none', !enabled));
    if (taxHead) taxHead.classList.toggle('d-none', !enabled);
    const partyWrap = document.getElementById('expensePartyWrap');
    if (partyWrap) partyWrap.style.display = enabled ? '' : 'none';
    const switchWrap = document.getElementById('expenseTaxSwitchWrap');
    if (switchWrap) switchWrap.style.display = expenseHasTaxRates ? 'flex' : 'none';
    applyExpenseHeaderVisibility();
    renderExpenseAdditionalCharges();
    renderExpenseTransportationSection();
    document.querySelectorAll('[id^="itemTaxRate_"]').forEach(sel => {
      if (sel.closest('td')) sel.closest('td').classList.toggle('d-none', !enabled);
    });
    calcTotals();
  }

  function setExpenseDescriptionVisible(visible) {
    const wrap = document.getElementById('expenseDescriptionWrap');
    if (!wrap) return;
    const button = document.querySelector('#expenseFormPage .add-description');
    wrap.classList.toggle('d-none', !visible);
    wrap.style.display = visible ? 'block' : '';
    button?.classList.toggle('d-none', !!visible);
    if (visible) document.getElementById('expenseDescriptionInput')?.focus();
  }

  function toggleExpenseDescription() {
    setExpenseDescriptionVisible(true);
  }

  function getExpenseTransactionSettings() {
    return expenseTransactionSettings || {};
  }

  function formatExpenseDateDisplay(date) {
    if (!date) return '';
    const d = date instanceof Date ? date : new Date(date);
    if (Number.isNaN(d.getTime())) return '';
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    return `${dd}/${mm}/${d.getFullYear()}`;
  }

  function formatExpenseDateDb(date) {
    if (!date) return '';
    const d = date instanceof Date ? date : new Date(date);
    if (Number.isNaN(d.getTime())) return '';
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    return `${d.getFullYear()}-${mm}-${dd}`;
  }

  function formatExpenseTimeDisplay(date = new Date()) {
    const d = date instanceof Date ? date : new Date(date);
    if (Number.isNaN(d.getTime())) return '';
    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const suffix = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    if (hours === 0) hours = 12;
    return `${String(hours).padStart(2, '0')}:${minutes} ${suffix}`;
  }

  function parseExpenseDateDisplay(value) {
    if (!value) return null;
    const raw = String(value).trim();
    if (raw.includes('/')) {
      const parts = raw.split('/');
      if (parts.length !== 3) return null;
      const d = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
      return Number.isNaN(d.getTime()) ? null : d;
    }
    if (raw.includes('-')) {
      const parts = raw.split('-');
      if (parts.length !== 3) return null;
      const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
      return Number.isNaN(d.getTime()) ? null : d;
    }
    const fallback = new Date(raw);
    return Number.isNaN(fallback.getTime()) ? null : fallback;
  }

  function getExpenseDealDaysValue() {
    const select = document.getElementById('expenseDealDaysSelect');
    if (!select) return 0;
    if (select.value === 'custom') {
      return parseInt(document.getElementById('expenseDealDaysCustom')?.value) || 0;
    }
    return parseInt(select.value) || 0;
  }

  function updateExpenseDueDateDisplay() {
    const dueInput = document.getElementById('expenseDueDateDisplay');
    const dueGroup = document.querySelector('.expense-final-due-date-group');
    if (!dueInput || !dueGroup || dueGroup.classList.contains('d-none')) return;
    const baseDate = parseExpenseDateDisplay(document.getElementById('formDateVal')?.textContent || '');
    if (!baseDate) {
      dueInput.value = '';
      return;
    }
    const dueDate = new Date(baseDate);
    const dealDays = getExpenseDealDaysValue();
    if (dealDays > 0) {
      dueDate.setDate(dueDate.getDate() + dealDays);
    }
    dueInput.value = formatExpenseDateDisplay(dueDate);
  }

  function applyExpenseHeaderVisibility() {
    const settings = getExpenseTransactionSettings();
    const header = settings.transaction_header || {};
    const more = settings.more_transaction_features || {};
    const taxEnabled = isExpenseTaxOn() && expenseHasTaxRates;
    const showPoFields = taxEnabled && !!header.customer_po_enabled;
    const showTimeField = taxEnabled && !!header.transaction_time_enabled;
    const showTermsFields = taxEnabled && !!more.due_dates_payment_terms_enabled;
    document.querySelector('.expense-po-fields-group')?.classList.toggle('d-none', !showPoFields);
    document.querySelector('.expense-transaction-time-group')?.classList.toggle('d-none', !showTimeField);
    document.querySelector('.expense-payment-terms-group')?.classList.toggle('d-none', !showTermsFields);
    document.querySelector('.expense-deal-days-group')?.classList.toggle('d-none', !showTermsFields);
    document.querySelector('.expense-final-due-date-group')?.classList.toggle('d-none', !showTermsFields);
    if (!taxEnabled) {
      const dueInput = document.getElementById('expenseDueDateDisplay');
      if (dueInput) dueInput.value = '';
    }

    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    if (dealDaysSelect && dealDaysCustom && !dealDaysSelect.value) {
      const defaultDays = parseInt(settings.payment_terms?.days || 0) || 0;
      const presetValues = ['0', '5', '10', '15', '30', '45', 'custom'];
      if (presetValues.includes(String(defaultDays))) {
        dealDaysSelect.value = String(defaultDays);
        dealDaysCustom.classList.add('d-none');
      } else if (defaultDays > 0) {
        dealDaysSelect.value = 'custom';
        dealDaysCustom.value = String(defaultDays);
        dealDaysCustom.classList.remove('d-none');
      }
    }
    const paymentTermsDisplay = document.getElementById('expensePaymentTermsDisplay');
    if (paymentTermsDisplay && taxEnabled) paymentTermsDisplay.value = settings.payment_terms?.name || 'Net 15';
  }

  function bindExpenseHeaderControls() {
    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    if (dealDaysSelect && !dealDaysSelect.dataset.bound) {
      dealDaysSelect.dataset.bound = '1';
      dealDaysSelect.addEventListener('change', () => {
        if (dealDaysCustom) {
          dealDaysCustom.classList.toggle('d-none', dealDaysSelect.value !== 'custom');
        }
        updateExpenseDueDateDisplay();
      });
    }
    if (dealDaysCustom && !dealDaysCustom.dataset.bound) {
      dealDaysCustom.dataset.bound = '1';
      dealDaysCustom.addEventListener('input', updateExpenseDueDateDisplay);
    }
  }

  function renderExpenseAdditionalCharges() {
    const section = document.getElementById('expenseAdditionalChargesSection');
    if (!section) return;
    const settings = getExpenseTransactionSettings();
    const items = normalizeExpenseSettingFields(settings.additional_charges_items);
    const shouldShow = isExpenseTaxOn() && !!settings.additional_charges_enabled;
    section.style.display = shouldShow ? 'block' : 'none';
    if (!shouldShow) {
      section.innerHTML = '';
      return;
    }
    const standaloneDiscountTaxSection = document.getElementById('expenseDiscountTaxSection');
    if (standaloneDiscountTaxSection) {
      standaloneDiscountTaxSection.style.display = 'none';
      standaloneDiscountTaxSection.innerHTML = '';
    }

    const saved = currentExpenseState().additionalCharges || {};
    section.innerHTML = `
      <div class="expense-section-card">
        <div class="expense-discount-tax-block">
          ${expenseDiscountTaxControlsMarkup()}
        </div>
        <div class="expense-section-title">Additional Charges</div>
        <div class="expense-field-grid">
          ${items.filter(item => item && item.enabled).map(item => `
            <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper">
              <input type="number" class="meta-control" min="0" step="0.01" value="${parseFloat(saved[item.key] || 0) || 0}"
                data-add-charge="${item.key}" oninput="updateExpenseAdditionalCharges()" placeholder=" ">
              <label>${item.label || humanizeExpenseLabel(item.key)}</label>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  function expenseDiscountTaxControlsMarkup() {
    const state = currentExpenseState();
    const discountPercent = parseFloat(state.discountPercent || 0) || 0;
    const discountAmount = parseFloat(state.discountAmount || 0) || 0;
    const selectedTax = getExpenseTaxRateById(state.summaryTaxRateId || '');
    const taxAmount = parseFloat(state.summaryTaxAmount || 0) || 0;
    const selectedTaxId = selectedTax?.id || state.summaryTaxRateId || '';

    return `
      <div class="expense-discount-row">
        <span class="expense-row-label">Discount</span>
        <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper expense-inline-input">
          <input type="number" id="expenseDiscountPercentInput" class="meta-control" min="0" step="0.01" placeholder=" " value="${discountPercent || ''}" oninput="updateExpenseDiscountFromPercent(this.value)">
          <label>(%)</label>
        </div>
        <span class="expense-inline-suffix">-</span>
        <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper expense-inline-input">
          <input type="number" id="expenseDiscountAmountInput" class="meta-control" min="0" step="0.01" placeholder=" " value="${discountAmount || ''}" oninput="updateExpenseDiscountFromAmount(this.value)">
          <label>(Rs)</label>
        </div>
      </div>
      <div class="expense-tax-row" style="margin-top:10px;">
        <span class="expense-row-label">Tax</span>
        <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper expense-inline-input expense-tax-select">
          <select id="expenseSummaryTaxRateSelect" class="meta-control" onchange="updateExpenseSummaryTaxFromRate(this.value)">
            <option value="">NONE</option>
            ${renderExpenseTaxRateOptions(selectedTaxId)}
          </select>
          <label>Tax</label>
        </div>
        <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper expense-inline-input expense-tax-amount-inline">
          <input type="number" id="expenseSummaryTaxAmountInput" class="meta-control" min="0" step="0.01" placeholder=" " value="${taxAmount || ''}" readonly>
          <label>(Rs)</label>
        </div>
      </div>
    `;
  }

  function renderExpenseDiscountTaxSection() {
    const section = document.getElementById('expenseDiscountTaxSection');
    if (!section) return;
    const settings = getExpenseTransactionSettings();
    const additionalChargesVisible = isExpenseTaxOn() && !!settings.additional_charges_enabled;
    if (additionalChargesVisible) {
      section.style.display = 'none';
      section.innerHTML = '';
      return;
    }
    const taxEnabled = isExpenseTaxOn();
    const state = currentExpenseState();
    const discountPercent = parseFloat(state.discountPercent || 0) || 0;
    const discountAmount = parseFloat(state.discountAmount || 0) || 0;
    const selectedTax = getExpenseTaxRateById(state.summaryTaxRateId || '');
    const taxRateValue = parseFloat(selectedTax?.rate || 0) || 0;
    const taxAmount = parseFloat(state.summaryTaxAmount || 0) || 0;
    const forceVisible = section.dataset.forceVisible === '1';
    const shouldShow = forceVisible || taxEnabled || discountPercent > 0 || discountAmount > 0 || taxRateValue > 0 || taxAmount > 0;
    section.style.display = shouldShow ? 'block' : 'none';
    if (!shouldShow) {
      section.innerHTML = '';
      return;
    }

    section.innerHTML = `
      <div class="expense-section-card">
        ${expenseDiscountTaxControlsMarkup()}
      </div>
    `;
  }

  function renderExpenseTransportationSection() {
    const section = document.getElementById('expenseTransportationSection');
    if (!section) return;
    const settings = getExpenseTransactionSettings();
    const configuredFields = normalizeExpenseSettingFields(settings.transportation_details_fields)
      .filter(field => field && field.key);
    const fallbackFields = [
      { key: 'field_1', label: 'Transport Name', enabled: true },
      { key: 'field_2', label: 'Vehicle Number', enabled: true },
      { key: 'field_3', label: 'Delivery Date', enabled: true },
      { key: 'field_4', label: 'Delivery Location', enabled: true },
      { key: 'field_5', label: 'Field 5', enabled: true },
    ];

    const enabledFields = configuredFields.length ? configuredFields : (settings.transportation_details_enabled ? fallbackFields : []);
    const shouldShow = enabledFields.length > 0;
    section.style.display = shouldShow ? 'block' : 'none';
    if (!shouldShow) {
      section.innerHTML = '';
      return;
    }

    const saved = currentExpenseState().transportationDetails || {};
    section.innerHTML = `
      <div class="expense-section-card">
        <div class="expense-section-title">Transportation Details</div>
        <div class="expense-field-grid">
          ${enabledFields.map((field, index) => `
            <div class="floating-input-wrapper expense-floating-wrapper expense-compact-wrapper">
              <input type="text" class="meta-control" data-transport-field="${field.key}" value="${String(saved[field.key] || '').replace(/"/g, '&quot;')}"
                oninput="updateExpenseTransportationDetails()" placeholder=" ">
              <label>${getExpenseTransportFieldLabel(field, index)}</label>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  function currentExpenseState() {
    const state = tabStates[activeTabN] || defaultTabState();
    state.poNo = state.poNo || '';
    state.poDate = state.poDate || '';
    state.transactionTime = state.transactionTime || '';
    state.dealDays = state.dealDays || 0;
    state.dueDate = state.dueDate || '';
    state.paymentTermsName = state.paymentTermsName || '';
    state.status = state.status || 'unpaid';
    state.discountPercent = state.discountPercent || 0;
    state.discountAmount = state.discountAmount || 0;
    state.summaryTaxRateId = state.summaryTaxRateId || '';
    state.summaryTaxAmount = state.summaryTaxAmount || 0;
    state.additionalCharges = state.additionalCharges || {};
    state.transportationDetails = state.transportationDetails || {};
    return state;
  }

  function updateExpenseAdditionalCharges() {
    const state = currentExpenseState();
    state.additionalCharges = {};
    document.querySelectorAll('[data-add-charge]').forEach(input => {
      state.additionalCharges[input.dataset.addCharge] = parseFloat(input.value) || 0;
    });
    calcTotals();
  }

  function updateExpenseTransportationDetails() {
    const state = currentExpenseState();
    state.transportationDetails = {};
    document.querySelectorAll('[data-transport-field]').forEach(input => {
      state.transportationDetails[input.dataset.transportField] = input.value || '';
    });
  }

  function updateExpenseDiscountFromPercent(val) {
    const state = currentExpenseState();
    const subtotal = parseFloat(document.getElementById('formAmtTotal')?.dataset?.rawTotal || document.getElementById('formAmtTotal')?.textContent || 0) || 0;
    const percent = Math.max(parseFloat(val) || 0, 0);
    const amount = subtotal > 0 ? (subtotal * percent / 100) : 0;
    state.discountPercent = percent;
    state.discountAmount = amount;
    const amtEl = document.getElementById('expenseDiscountAmountInput');
    if (amtEl) amtEl.value = amount ? amount.toFixed(2) : '';
    updateExpenseSummaryTaxFromRate(document.getElementById('expenseSummaryTaxRateSelect')?.value || state.summaryTaxRateId || '');
    calcTotals();
  }

  function updateExpenseDiscountFromAmount(val) {
    const state = currentExpenseState();
    const subtotal = parseFloat(document.getElementById('formAmtTotal')?.dataset?.rawTotal || document.getElementById('formAmtTotal')?.textContent || 0) || 0;
    const amount = Math.max(parseFloat(val) || 0, 0);
    const percent = subtotal > 0 ? (amount / subtotal) * 100 : 0;
    state.discountAmount = amount;
    state.discountPercent = percent;
    const pctEl = document.getElementById('expenseDiscountPercentInput');
    if (pctEl) pctEl.value = percent ? percent.toFixed(2) : '';
    updateExpenseSummaryTaxFromRate(document.getElementById('expenseSummaryTaxRateSelect')?.value || state.summaryTaxRateId || '');
    calcTotals();
  }

  function updateExpenseSummaryTaxFromRate(rateId) {
    const state = currentExpenseState();
    state.summaryTaxRateId = rateId || '';
    const taxRate = getExpenseTaxRateById(rateId);
    const subtotal = parseFloat(document.getElementById('formAmtTotal')?.dataset?.rawTotal || document.getElementById('formAmtTotal')?.textContent || 0) || 0;
    const discountAmount = parseFloat(state.discountAmount || 0) || 0;
    const taxableBase = Math.max(subtotal - discountAmount, 0);
    const taxAmount = taxRate ? (taxableBase * (parseFloat(taxRate.rate) || 0) / 100) : 0;
    state.summaryTaxAmount = taxAmount;
    const taxAmtEl = document.getElementById('expenseSummaryTaxAmountInput');
    if (taxAmtEl) taxAmtEl.value = taxAmount ? taxAmount.toFixed(2) : '';
    calcTotals();
  }

  function applyExpenseFeatureVisibility() {
    const taxWrap = document.getElementById('expenseTaxSwitchWrap');
    if (taxWrap) taxWrap.style.display = expenseHasTaxRates ? 'flex' : 'none';
    const taxEnabled = isExpenseTaxOn() && expenseHasTaxRates;
    const partyWrap = document.getElementById('expensePartyWrap');
    if (partyWrap) partyWrap.style.display = taxEnabled ? '' : 'none';
    document.getElementById('expenseTaxHead')?.classList.toggle('d-none', !taxEnabled);
    document.querySelectorAll('.expense-tax-cell').forEach(cell => cell.classList.toggle('d-none', !taxEnabled));
    applyExpenseHeaderVisibility();
    bindExpenseHeaderControls();
    renderExpenseDiscountTaxSection();
    renderExpenseAdditionalCharges();
    renderExpenseTransportationSection();
    updateExpenseDueDateDisplay();
  }

  function tryCloseEntireForm() {
    if (window.expenseStartInCreate) {
      window.location.href = window.expenseRoutes?.expense || "{{ route('expense') }}";
      return;
    }
    closingTabN = 'all';
    document.getElementById('closeExpenseOverlay').classList.add('open');
  }

  // ═══════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════
  document.addEventListener('DOMContentLoaded', function () {
    setDateDisplay(calSelDate);
    buildCalendar();
    if (window.expenseStartInCreate) {
      resetForm();
      showPage('expenseFormPage');
      applyExpenseFeatureVisibility();
      if (expenseEditParam) {
        setTimeout(() => openExpenseById(expenseEditParam), 0);
      }
      return;
    }
    if (categories.length > 0) showPage('splitPane');
    else                        showPage('emptyState');
    if (expenseEditParam) {
      setTimeout(() => openExpenseById(expenseEditParam), 0);
    }
  });

  // ═══════════════════════════════════════════════════════
  //  PAGE SWITCH
  // ═══════════════════════════════════════════════════════
  function showPage(id) {
    ['emptyState','splitPane'].forEach(p => {
      const el = document.getElementById(p);
      if (el) el.style.display = 'none';
    });

    // The expense form is a fixed overlay — show/hide independently
    const formEl = document.getElementById('expenseFormPage');

    if (id === 'expenseFormPage') {
      formEl.style.display = 'flex';
      return;
    }

    // Hide form overlay when going back to list
    formEl.style.display = 'none';

    const target = document.getElementById(id);
    if (!target) return;
    if (id === 'emptyState') {
      target.style.display = 'flex';
    } else if (id === 'splitPane') {
      target.style.display = 'flex';
      renderCategoryList();
      if (currentTab === 'category') renderDetailPanel();
      else renderItemsList();
    }
  }

  document.getElementById('emptyAddBtn').addEventListener('click', openExpenseForm);

  // ═══════════════════════════════════════════════════════
  //  LIST TABS
  // ═══════════════════════════════════════════════════════
  function switchListTab(tab) {
    currentTab = tab;
    document.getElementById('tabCategory').classList.toggle('active', tab === 'category');
    document.getElementById('tabItems').classList.toggle('active',    tab === 'items');
    if (tab === 'category') {
      document.getElementById('slcLabel').textContent = 'CATEGORY';
      document.getElementById('slcAmountLabel').textContent = 'AMOUNT';
      document.getElementById('fpop_left_name_title').textContent = 'CATEGORY FILTER';
      document.getElementById('fpop_left_name_val').placeholder = 'Search category';
      document.getElementById('fpop_left_amount_title').textContent = 'AMOUNT FILTER';
      document.getElementById('categoryDetailPanel').style.display = 'flex';
      document.getElementById('itemsDetailPanel').style.display    = 'none';
      renderCategoryList(); renderDetailPanel();
    } else {
      document.getElementById('slcLabel').textContent = 'ITEM';
      document.getElementById('slcAmountLabel').textContent = 'PRICE';
      document.getElementById('fpop_left_name_title').textContent = 'ITEM FILTER';
      document.getElementById('fpop_left_name_val').placeholder = 'Search item';
      document.getElementById('fpop_left_amount_title').textContent = 'PRICE FILTER';
      document.getElementById('categoryDetailPanel').style.display = 'none';
      document.getElementById('itemsDetailPanel').style.display    = 'flex';
      renderItemsList();
    }
  }

  // ═══════════════════════════════════════════════════════
  //  SORT — detail table
  // ═══════════════════════════════════════════════════════
  function sortDetailTable(col) {
    if (detailSortCol === col) {
      detailSortDir = detailSortDir === 'asc' ? 'desc' : 'asc';
    } else {
      detailSortCol = col;
      detailSortDir = 'asc';
    }
    ['date','expNo','party','paymentType','amount','status','dueDate','balance'].forEach(c => {
      const upEl = document.getElementById('sort_'+c+'_up');
      const dnEl = document.getElementById('sort_'+c+'_dn');
      if (upEl) upEl.classList.remove('active');
      if (dnEl) dnEl.classList.remove('active');
    });
    const activeUpEl = document.getElementById('sort_'+col+'_up');
    const activeDnEl = document.getElementById('sort_'+col+'_dn');
    if (detailSortDir === 'asc' && activeUpEl) activeUpEl.classList.add('active');
    if (detailSortDir === 'desc' && activeDnEl) activeDnEl.classList.add('active');
    renderDetailPanel();
  }

  function getSortedEntries(entries) {
    if (!entries || !entries.length) return entries;
    const col = detailSortCol;
    const dir = detailSortDir === 'asc' ? 1 : -1;
    return [...entries].sort((a, b) => {
      let av = a[col] ?? '';
      let bv = b[col] ?? '';
      if (col === 'amount' || col === 'balance') {
        av = parseFloat(av) || 0;
        bv = parseFloat(bv) || 0;
        return (av - bv) * dir;
      }
      if (col === 'date' || col === 'dueDate') {
        const aDate = parseExpenseFilterDate(av);
        const bDate = parseExpenseFilterDate(bv);
        av = aDate ? aDate.getTime() : 0;
        bv = bDate ? bDate.getTime() : 0;
        return (av - bv) * dir;
      }
      return String(av).localeCompare(String(bv)) * dir;
    });
  }

  function sortItemsTable(col) {
    if (itemsSortCol === col) {
      itemsSortDir = itemsSortDir === 'asc' ? 'desc' : 'asc';
    } else {
      itemsSortCol = col;
      itemsSortDir = 'asc';
    }
    ['date','expNo','party','paymentType','amount','status','dueDate','balance'].forEach(c => {
      const upEl = document.getElementById('isort_'+c+'_up');
      const dnEl = document.getElementById('isort_'+c+'_dn');
      if (upEl) upEl.classList.remove('active');
      if (dnEl) dnEl.classList.remove('active');
    });
    const upEl = document.getElementById('isort_'+col+'_up');
    const dnEl = document.getElementById('isort_'+col+'_dn');
    if (itemsSortDir === 'asc' && upEl) upEl.classList.add('active');
    if (itemsSortDir === 'desc' && dnEl) dnEl.classList.add('active');
    renderItemDetailPanel(selectedItemIdx);
  }

  // ═══════════════════════════════════════════════════════
  //  FILTER POPOVERS
  // ═══════════════════════════════════════════════════════
  function toggleFilterPop(e, id) {
    e.stopPropagation();
    const pop = document.getElementById(id);
    if (!pop) return;
    const wasOpen = pop.classList.contains('open');
    document.querySelectorAll('.filter-popover').forEach(p => p.classList.remove('open'));
    if (!wasOpen) pop.classList.add('open');
  }
  function closeFilterPop(id) { document.getElementById(id)?.classList.remove('open'); }
  function clearFilterPop(id, col) {
    const pop = document.getElementById(id);
    if (pop) {
      pop.querySelectorAll('input[type=text], input[type=number]').forEach(el => el.value = '');
      pop.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false);
      pop.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    }
    closeFilterPop(id);
    renderCategoryList();
    if (currentTab === 'category') {
      renderDetailPanel();
    } else if (expenseItems.length) {
      renderItemDetailPanel(selectedItemIdx);
    }
  }
  function applyFilterPop(id, col) {
    closeFilterPop(id);
    renderCategoryList();
    if (currentTab === 'category') {
      renderDetailPanel();
    } else if (expenseItems.length) {
      renderItemDetailPanel(selectedItemIdx);
    }
  }

  function normalizeExpenseFilterText(value) {
    return String(value ?? '').trim().toLowerCase();
  }
  function parseExpenseFilterDate(value) {
    if (!value) return null;
    const raw = String(value).trim();
    const displayDate = parseExpenseDateDisplay(raw);
    if (displayDate) return displayDate;
    const fallback = new Date(raw);
    return Number.isNaN(fallback.getTime()) ? null : fallback;
  }
  function getExpenseFilterPopoverId(col) {
    if (String(col).startsWith('fpop_')) return col;
    const prefix = currentTab === 'items' ? 'fpop_items_' : 'fpop_';
    return `${prefix}${col}`;
  }
  function getFilterPopoverValue(col, suffix) {
    const popId = getExpenseFilterPopoverId(col);
    return document.getElementById(`${popId}_${suffix}`)?.value ?? '';
  }
  function matchesExpenseTextFilter(value, col) {
    const mode = getFilterPopoverValue(col, 'cat') || 'Contains';
    const term = normalizeExpenseFilterText(getFilterPopoverValue(col, 'val'));
    if (!term) return true;
    const actual = normalizeExpenseFilterText(value);
    return mode === 'Exact match' ? actual === term : actual.includes(term);
  }
  function matchesExpenseNumberFilter(value, col) {
    const mode = getFilterPopoverValue(col, 'cat') || 'Equal to';
    const term = parseFloat(getFilterPopoverValue(col, 'val'));
    if (Number.isNaN(term)) return true;
    const actual = parseFloat(value) || 0;
    if (mode === 'Less Than') return actual < term;
    if (mode === 'Greater Than') return actual > term;
    return actual === term;
  }
  function matchesExpenseDateFilter(value, col) {
    const mode = getFilterPopoverValue(col, 'cat') || 'Equal To';
    const term = parseExpenseFilterDate(getFilterPopoverValue(col, 'val'));
    if (!term) return true;
    const actual = parseExpenseFilterDate(value);
    if (!actual) return false;
    const a = new Date(actual.getFullYear(), actual.getMonth(), actual.getDate()).getTime();
    const b = new Date(term.getFullYear(), term.getMonth(), term.getDate()).getTime();
    if (mode === 'Less Than') return a < b;
    if (mode === 'Greater Than') return a > b;
    return a === b;
  }
  function matchesExpensePaymentTypeFilter(value, col) {
    const popId = getExpenseFilterPopoverId(col);
    const pop = document.getElementById(popId);
    if (!pop) return true;
    const selected = [];
    if (pop.querySelector(`#${popId}_cash`)?.checked) selected.push('cash');
    if (pop.querySelector(`#${popId}_cheque`)?.checked) selected.push('cheque');
    if (pop.querySelector(`#${popId}_upi`)?.checked) selected.push('upi');
    if (pop.querySelector(`#${popId}_card`)?.checked) selected.push('card');
    if (!selected.length) return true;
    const actual = normalizeExpenseFilterText(value);
    return selected.includes(actual);
  }

  // ═══════════════════════════════════════════════════════
  //  CATEGORY LIST
  // ═══════════════════════════════════════════════════════
  function renderCategoryList() {
    const ul = document.getElementById('categoryList');
    ul.innerHTML = '';
    const list = currentTab === 'category' ? categories : expenseItems;
    const filteredList = list.filter(row => {
      const nameValue = currentTab === 'category' ? row.name : row.name;
      const amountValue = currentTab === 'category' ? row.amount : row.price;
      return matchesExpenseTextFilter(nameValue, 'fpop_left_name') &&
             matchesExpenseNumberFilter(amountValue, 'fpop_left_amount');
    });

    if (currentTab === 'category') {
      if (!filteredList.length) {
        ul.innerHTML = '<div style="padding:24px;text-align:center;color:#aaa;font-size:13px;">No categories yet.</div>';
        return;
      }
      filteredList.forEach((c, i) => {
        const originalIndex = categories.indexOf(c);
        const div = document.createElement('div');
        div.className = 'category-item' + (originalIndex === selectedCatIdx ? ' active' : '');
        div.innerHTML = `
          <span class="cat-name">${escHtml(c.name)}</span>
          <div class="cat-right">
            <span class="cat-amount">Rs ${parseFloat(c.amount||0).toFixed(2)}</span>
            <div class="cat-dots-wrap">
              <button type="button" class="cat-dots-btn" onclick="toggleCatMenu(event,${originalIndex})"><i class="fa-solid fa-ellipsis-vertical"></i></button>
              <div class="cat-dots-menu" id="catMenu_${originalIndex}">
                <div class="cat-dots-item" onclick="openEditCatModal(${originalIndex})">Edit</div>
                <div class="cat-dots-item danger" onclick="deleteCategoryPrompt(${originalIndex})">Delete</div>
              </div>
            </div>
          </div>`;
        div.addEventListener('click', e => {
          if (e.target.closest('.cat-dots-wrap')) return;
          selectedCatIdx = originalIndex; renderCategoryList(); renderDetailPanel();
        });
        ul.appendChild(div);
      });
    } else {
      if (!filteredList.length) {
        ul.innerHTML = '<div style="padding:24px;text-align:center;color:#aaa;font-size:13px;">No items yet.</div>';
        return;
      }
      filteredList.forEach((it, i) => {
        const originalIndex = expenseItems.indexOf(it);
        const div = document.createElement('div');
        div.className = 'category-item' + (originalIndex === selectedItemIdx ? ' active' : '');
        div.innerHTML = `
          <span class="cat-name">${escHtml(it.name)}</span>
          <div class="cat-right">
            <span class="cat-amount">Rs ${parseFloat(it.price||0).toFixed(2)}</span>
            <div class="cat-dots-wrap">
              <button type="button" class="cat-dots-btn" onclick="toggleItemMenu(event,${originalIndex})"><i class="fa-solid fa-ellipsis-vertical"></i></button>
              <div class="cat-dots-menu" id="itemMenu_${originalIndex}">
                <div class="cat-dots-item" onclick="openEditItemModal(${originalIndex})">Edit</div>
                <div class="cat-dots-item danger" onclick="deleteItemPrompt(${originalIndex})">Delete</div>
              </div>
            </div>
          </div>`;
        div.addEventListener('click', e => {
          if (e.target.closest('.cat-dots-wrap')) return;
          selectedItemIdx = originalIndex; renderCategoryList(); renderItemDetailPanel(originalIndex);
        });
        ul.appendChild(div);
      });
    }
  }

  function toggleCatMenu(e, i) {
    e.stopPropagation();
    const menu = document.getElementById('catMenu_' + i);
    if (!menu) return;
    const exceptId = 'catMenu_' + i;
    document.querySelectorAll('[id^="catMenu_"]').forEach(m => { if (m.id !== exceptId) m.classList.remove('open'); });
    menu.classList.toggle('open');
  }
  function closeAllCatMenus(except) {
    const exceptId = except === undefined || except === null ? null : 'catMenu_' + except;
    document.querySelectorAll('[id^="catMenu_"]').forEach(m => { if (exceptId === null || m.id !== exceptId) m.classList.remove('open'); });
  }
  function toggleItemMenu(e, i) {
    e.stopPropagation();
    const menu = document.getElementById('itemMenu_' + i);
    if (!menu) return;
    const exceptId = 'itemMenu_' + i;
    document.querySelectorAll('[id^="itemMenu_"]').forEach(m => { if (m.id !== exceptId) m.classList.remove('open'); });
    menu.classList.toggle('open');
  }
  function closeAllItemMenus(except) {
    const exceptId = except === undefined || except === null ? null : 'itemMenu_' + except;
    document.querySelectorAll('[id^="itemMenu_"]').forEach(m => { if (exceptId === null || m.id !== exceptId) m.classList.remove('open'); });
  }

  // ═══════════════════════════════════════════════════════
  //  DETAIL PANEL — CATEGORY
  // ═══════════════════════════════════════════════════════
 
  function renderDetailPanel() {
    const c = categories[selectedCatIdx];
    if (!c) {
      document.getElementById('detailTitle').textContent   = '—';
      document.getElementById('detailType').textContent    = '';
      document.getElementById('detailTotal').textContent   = 'Rs 0.00';
      document.getElementById('detailBalance').textContent = 'Rs 0.00';
      document.getElementById('detailTableBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:24px;">No expenses yet.</td></tr>';
      return;
    }
    document.getElementById('detailTitle').textContent   = c.name.toUpperCase();
    document.getElementById('detailType').textContent    = c.type || '';
    const tbody = document.getElementById('detailTableBody');
    const allRows = getSortedEntries(c.entries || []);
    const search = normalizeExpenseItemName(document.getElementById('detailSearchInput')?.value || '');
    const filteredRows = allRows.filter(row => {
      if (search && !(
        normalizeExpenseFilterText(row.date).includes(search) ||
        normalizeExpenseFilterText(row.expNo).includes(search) ||
        normalizeExpenseFilterText(row.party).includes(search) ||
        normalizeExpenseFilterText(row.paymentType).includes(search)
      )) return false;
      return matchesExpenseDateFilter(row.date, 'date') &&
        matchesExpenseTextFilter(row.expNo, 'expNo') &&
        matchesExpenseTextFilter(row.party, 'party') &&
        matchesExpensePaymentTypeFilter(row.paymentType, 'payType') &&
        matchesExpenseNumberFilter(row.amount, 'amount') &&
        matchesExpenseNumberFilter(row.balance, 'balance');
    });
    const totalAmount = filteredRows.reduce((sum, row) => sum + (parseFloat(row.amount) || 0), 0);
    const totalBalance = filteredRows.reduce((sum, row) => sum + (parseFloat(row.balance) || 0), 0);
    document.getElementById('detailTotal').textContent   = 'Rs ' + totalAmount.toFixed(2);
    document.getElementById('detailBalance').textContent = 'Rs ' + totalBalance.toFixed(2);
    tbody.innerHTML = '';
    if (!filteredRows.length) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:24px;">No expenses yet.</td></tr>';
      return;
    }
    filteredRows.forEach((e, ei) => {
      const rowKey = e.id ?? e.entryId ?? e.expenseId ?? e.transactionId ?? `tx-${ei}`;
      const tr = document.createElement('tr');
      if (ei === 0) tr.classList.add('row-highlight');
      tr.addEventListener('dblclick', (ev) => {
        if (ev.target.closest('.td-row-menu') || ev.target.closest('.td-action-btn')) return;
        openViewEdit(e.id ?? e.entryId, selectedCatIdx);
      });
      tr.innerHTML = `
          <td>${escHtml(e.date||'')}</td>
          <td>${escHtml(e.expNo||'')}</td>
          <td>${escHtml(e.party||'')}</td>
          <td>${escHtml(e.paymentType||'')}</td>
          <td style="font-weight:500;">${parseFloat(e.amount||0).toFixed(2)}</td>
          <td>${escHtml(formatExpenseStatusLabel(e.status))}</td>
          <td>${escHtml(formatExpenseDateDisplay(e.dueDate || e.due_date || ''))}</td>
          <td>${parseFloat(e.balance||0).toFixed(2)}</td>
          <td style="position:relative; width:40px; text-align:center;">
              <button type="button" class="td-action-btn" data-row-id="${rowKey}" data-entry-id="${e.entryId ?? e.id ?? ''}" onclick="toggleRowMenu(event, '${String(rowKey).replace(/'/g, "\\'")}', this)">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <div class="td-row-menu" id="rowMenu_${rowKey}">
                  <div class="td-row-menu-item" onclick="openViewEdit(${e.id ?? e.entryId},${selectedCatIdx})">View/Edit</div>
                  <div class="td-row-menu-item danger" onclick="deleteExpenseRow(${e.id ?? e.entryId},${selectedCatIdx})">Delete</div>
                  <div class="td-row-menu-item" onclick="duplicateExpenseRow(${e.id ?? e.entryId},${selectedCatIdx})">Duplicate</div>
                  <div class="td-row-menu-item" onclick="openPrintView(${e.id ?? e.entryId},${selectedCatIdx})">Print</div>
                  <div class="td-row-menu-item" onclick="openPreview(${e.id ?? e.entryId},${selectedCatIdx})">Preview</div>
                  <div class="td-row-menu-item" onclick="openDirectPDF(${e.id ?? e.entryId},${selectedCatIdx})">Open PDF</div>
                  <div class="td-row-menu-item" onclick="openViewHistory(${e.id ?? e.entryId},${selectedCatIdx})">View History</div>
              </div>
          </td>`;
      tbody.appendChild(tr);
    });
  }

  function toggleRowMenu(e, id, buttonEl) {
    const button = buttonEl || (e?.target?.closest?.('.td-action-btn') || e?.currentTarget?.closest?.('.td-action-btn'));
    if (!button || typeof button.getBoundingClientRect !== 'function') return;

    e?.preventDefault();
    e?.stopPropagation();

    const rowId = button.getAttribute('data-row-id') || button.getAttribute('data-entry-id') || id;
    const menu = rowId
      ? document.getElementById('rowMenu_' + rowId)
      : button.closest('td')?.querySelector('.td-row-menu');
    if (!menu) return;

    const wasOpen = menu.classList.contains('open');
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));

    if (!wasOpen) {
      const rect = button.getBoundingClientRect();
      const left = Math.min(rect.right - 140, window.innerWidth - 170);
      menu.style.top = `${rect.bottom + 4}px`;
      menu.style.left = `${Math.max(8, left)}px`;
      menu.classList.add('open');
    }
  }

  function deleteExpenseRow(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
    showConfirm('Delete Expense', 'This expense will be deleted permanently.', () => {
        ajax('DELETE', window.expenseRoutes.expenseDestroy + '/' + expId)
            .then(res => {
                if (res.success) {
                    const cat = categories[catIdx];
                    const idx = cat.entries.findIndex(e => e.id === expId);
                    if (idx > -1) {
                        cat.amount = parseFloat(cat.amount) - parseFloat(cat.entries[idx].amount);
                        cat.entries.splice(idx, 1);
                    }
                    renderCategoryList();
                    renderDetailPanel();
                    showToast('Expense deleted.', 'green');
                } else {
                    showToast(res.message || 'Delete failed.', 'red');
                }
            })
            .catch(() => showToast('Delete failed.', 'red'));
    });
  }

  // ═══════════════════════════════════════════════════════
  //  DUPLICATE — opens expense form pre-filled
  //  User edits and saves → creates a NEW expense (duplicate)
  // ═══════════════════════════════════════════════════════
  function duplicateExpenseRow(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
    const cat   = categories[catIdx];
    const entry = cat ? cat.entries.find(e => e.id === expId) : null;
    if (!entry) return;

    resetForm();
    showPage('expenseFormPage');

    document.getElementById('formCatLabel').textContent = cat.name;
    document.getElementById('formCatSelectBtn').classList.add('filled');
    document.getElementById('formExpNoInput').value = '';
    const poNoInput = document.getElementById('expensePoNoInput');
    if (poNoInput) poNoInput.value = entry.poNo || '';
    const poDateInput = document.getElementById('expensePoDateInput');
    if (poDateInput) poDateInput.value = entry.poDate || '';
    const transactionTimeDisplay = document.getElementById('expenseTransactionTimeDisplay');
    if (transactionTimeDisplay) transactionTimeDisplay.value = entry.transactionTime || formatExpenseTimeDisplay();
    const paymentTermsDisplay = document.getElementById('expensePaymentTermsDisplay');
    if (paymentTermsDisplay) paymentTermsDisplay.value = entry.paymentTermsName || getExpenseTransactionSettings()?.payment_terms?.name || 'Net 15';
    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    if (dealDaysSelect) {
      const dealDaysValue = entry.dealDays !== undefined && entry.dealDays !== null ? String(entry.dealDays) : String(getExpenseTransactionSettings()?.payment_terms?.days ?? 0);
      const presetValues = ['0', '5', '10', '15', '30', '45'];
      if (presetValues.includes(dealDaysValue)) {
        dealDaysSelect.value = dealDaysValue;
        dealDaysCustom?.classList.add('d-none');
      } else {
        dealDaysSelect.value = 'custom';
        if (dealDaysCustom) {
          dealDaysCustom.value = dealDaysValue;
          dealDaysCustom.classList.remove('d-none');
        }
      }
    }
    const dueDateDisplay = document.getElementById('expenseDueDateDisplay');
    if (dueDateDisplay) dueDateDisplay.value = entry.dueDate ? formatExpenseDateDisplay(entry.dueDate) : '';
    const statusSelect = document.getElementById('expenseStatusSelect');
    if (statusSelect) statusSelect.value = getExpenseDerivedStatus(entry.amount, entry.paidAmount);
    updateExpenseDueDateDisplay();

    if (entry.date) {
      const parts = entry.date.split('-');
      if (parts.length === 3) {
        calSelDate  = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        calViewDate = new Date(calSelDate);
        setDateDisplay(calSelDate);
        buildCalendar();
      }
    }

    const partyIdEl = document.getElementById('expensePartyId');
    const partySearchEl = document.getElementById('expensePartySearch');
    const partyBalanceEl = document.getElementById('expensePartyBalance');
    if (partyIdEl) partyIdEl.value = entry.party_id || '';
    if (partySearchEl) partySearchEl.value = entry.party || '';
    if (partyBalanceEl && entry.party_id) {
      const party = getExpensePartyById(entry.party_id);
      const balance = parseFloat(party?.current_balance || party?.opening_balance || 0);
      partyBalanceEl.textContent = `Balance: Rs ${formatExpenseMoney(balance)}`;
    }

    const taxSwitch = document.getElementById('expenseTaxSwitch');
    if (taxSwitch) taxSwitch.checked = !!entry.taxEnabled;
    const summaryTaxRateEl = document.getElementById('expenseSummaryTaxRateSelect');
    const summaryTaxAmountEl = document.getElementById('expenseSummaryTaxAmountInput');
    const discountPercentEl = document.getElementById('expenseDiscountPercentInput');
    const discountAmountEl = document.getElementById('expenseDiscountAmountInput');
    if (summaryTaxRateEl) summaryTaxRateEl.value = entry.summaryTaxRateId || entry.taxRateId || '';
    if (summaryTaxAmountEl) summaryTaxAmountEl.value = entry.summaryTaxAmount || entry.taxAmount || '';
    if (discountPercentEl) discountPercentEl.value = entry.discountPercent || '';
    if (discountAmountEl) discountAmountEl.value = entry.discountAmount || '';

    const descEl = document.getElementById('expenseDescriptionInput');
    if (descEl) descEl.value = entry.description || '';
    setExpenseDescriptionVisible(!!entry.description);

    document.getElementById('formItemsBody').innerHTML = '';
    rowKey = 0;
    if (entry.items && entry.items.length) {
      entry.items.forEach(it => {
        addItemRow();
        const rk = rowKey;
        document.getElementById('itemName_' + rk).value = it.name || '';
        document.getElementById('itemQty_' + rk).value = it.qty || '';
        document.getElementById('itemPrice_' + rk).value = it.price || '';
        const taxSel = document.getElementById('itemTaxRate_' + rk);
        if (taxSel) taxSel.value = it.taxRateId || '';
        calcRow(rk);
      });
    } else {
      addItemRow();
    }
    appendStaticRow();

    paymentRows = [{
      type: entry.bankAccountId ? `bank:${entry.bankAccountId}` : (entry.paymentType || 'Cheque'),
      amount: entry.paidAmount ?? Math.max((parseFloat(entry.amount || 0) - parseFloat(entry.balance || 0)), 0),
      ref: entry.reference_no || ''
    }];
    renderPaymentCard();
    applyExpenseFeatureVisibility();
    calcTotals();

    window._editingExpenseId = null;
    window._editingCatIdx = null;
  }
  function renderPaymentCard() {
    const card = document.getElementById('paymentCard');
    if (!card) return;
    card.innerHTML = '';

    paymentRows.forEach((row, i) => {
      const wrap = document.createElement('div');
      wrap.className = 'payment-row-wrap';

      const selectedType = row.type || '';
      const isBank = String(selectedType).startsWith('bank:');
      const bankSelectValue = isBank ? selectedType : '';
      const bankOptions = expenseBankAccounts.map(bank => {
        const label = bank.display_with_account || bank.display_name || bank.bank_name || `Bank ${bank.id}`;
        const value = `bank:${bank.id}`;
        return `<option value="${value}" ${bankSelectValue === value ? 'selected' : ''}>${String(label).replace(/"/g, '&quot;')}</option>`;
      }).join('');

      const pr = document.createElement('div');
      pr.className = 'payment-row';
      pr.innerHTML = `
        <div class="payment-field" style="position:relative;">
          <span class="payment-field-label" style="position:absolute;top:6px;left:12px;font-size:10px;color:#555;z-index:1;">Payment Type</span>
          <select class="payment-type-select" onchange="payRowChange(${i},'type',this.value)" style="border:1px solid #aaa;border-radius:6px;padding:20px 30px 8px 12px;font-size:13px;min-width:210px;min-height:54px;cursor:pointer;color:#1a1f36;background:#fff;outline:none;appearance:none;width:100%;">
            <option value="" ${!selectedType ? 'selected' : ''}>Select Type</option>
            <option value="Cash" ${selectedType === 'Cash' ? 'selected' : ''}>Cash</option>
            <option value="Cheque" ${selectedType === 'Cheque' ? 'selected' : ''}>Cheque</option>
            <optgroup label="Bank Accounts">
              ${bankOptions}
            </optgroup>
            <option value="add_bank" style="color:#2563eb;">+ Add Bank A/C</option>
          </select>
        </div>
        <div style="position:relative;">
          <span style="position:absolute;top:6px;left:12px;font-size:10px;color:#555;z-index:1;background:#fff;padding:0 2px;">Amount</span>
          <input type="number" value="${row.amount||0}" oninput="payRowChange(${i},'amount',this.value)" style="border:1px solid #aaa;border-radius:6px;padding:20px 12px 8px 12px;font-size:13px;width:120px;min-height:54px;outline:none;color:#1a1f36;background:#fff;">
        </div>
        ${i > 0 ? `<button onclick="removePaymentRow(${i})" style="background:none;border:none;cursor:pointer;color:#aaa;font-size:18px;padding:4px 8px;" title="Delete">🗑</button>` : '<div style="width:34px;"></div>'}`;

      wrap.appendChild(pr);

      const ref = document.createElement('input');
      ref.type = 'text';
      ref.placeholder = 'Reference No.';
      ref.value = row.ref || '';
      ref.className = 'ref-no-input';
      ref.oninput = ev => payRowChange(i, 'ref', ev.target.value);
      wrap.appendChild(ref);

      card.appendChild(wrap);

      const sel = pr.querySelector('select');
      if (sel) sel.dataset.paymentRowIndex = String(i);
      if (sel) {
        sel.addEventListener('change', function() {
          if (this.value === 'add_bank') {
            this.value = row.type || '';
            openAddBankModal(this);
            return;
          }
          payRowChange(i, 'type', this.value);
          calcTotals();
        });
      }
    });

    const footer = document.createElement('div');
    footer.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-top:10px;';
    const total = paymentRows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0);
    const grandTotal = parseFloat(document.getElementById('formAmtTotal')?.textContent) || 0;
    footer.innerHTML = `
      <button onclick="addPaymentRow()" style="background:none;border:none;color:#2563eb;font-size:13px;cursor:pointer;padding:0;display:flex;align-items:center;gap:4px;">+ Add Payment type</button>
      <span style="font-size:13px;font-weight:600;color:#555;" id="payTotalText">Total payment: ${total}/${grandTotal}</span>`;
    card.appendChild(footer);
  }
  // ═══════════════════════════════════════════════════════
  //  PRINT VIEW (full-screen, like Image 4)
  // ═══════════════════════════════════════════════════════
  let printZoom = 100;

  function openPrintView(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
    const cat   = categories[catIdx];
    const entry = cat ? cat.entries.find(e => e.id === expId) : null;
    if (!entry) return;

    const userName  = window.App?.user?.name || 'Company';
    const userPhone = window.App?.user?.phone || '';
    const userEmail = window.App?.user?.email || '';

    // Build items rows — if entry has items use them, else show generic row
    const itemRows = (entry.items && entry.items.length)
      ? entry.items.map((it, idx) => `
          <tr>
            <td>${idx+1}</td>
            <td>${escHtml(it.name||'')}</td>
            <td style="text-align:right;">${parseFloat(it.qty||1)}</td>
            <td style="text-align:right;">Rs ${parseFloat(it.price||0).toFixed(2)}</td>
            <td style="text-align:right;">Rs ${parseFloat(it.amount||0).toFixed(2)}</td>
          </tr>`).join('')
      : `<tr>
          <td>1</td>
          <td>${escHtml(cat.name)}</td>
          <td style="text-align:right;">1</td>
          <td style="text-align:right;">Rs 0.00</td>
          <td style="text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
        </tr>`;

    const totalQty = (entry.items && entry.items.length)
      ? entry.items.reduce((s, it) => s + parseFloat(it.qty||1), 0)
      : 1;

    document.getElementById('printPageContent').innerHTML = `
      <h2>Expense</h2>
      <div class="print-company-box">
        <div class="print-company-name">${escHtml(userName)}</div>
        <div class="print-company-meta">
          ${userPhone ? '<span>Phone: <strong>' + escHtml(userPhone) + '</strong></span>' : ''}
          ${userEmail ? '<span>Email: <strong>' + escHtml(userEmail) + '</strong></span>' : ''}
        </div>
      </div>
      <table class="print-info-table">
        <tr>
          <td style="width:50%;"><strong>Expense For:</strong>${escHtml(cat.name)}</td>
          <td><strong>Expense Details:</strong>Date: ${escHtml(formatDisplayDate(entry.date))}${entry.expNo ? '<br>Exp No: ' + escHtml(entry.expNo) : ''}</td>
        </tr>
      </table>
      <table class="print-items-table" style="margin-top:0;border-top:none;">
        <thead>
          <tr>
            <th>#</th>
            <th>Item name</th>
            <th style="text-align:right;">Quantity</th>
            <th style="text-align:right;">Price/ Unit(Rs)</th>
            <th style="text-align:right;">Amount(Rs)</th>
          </tr>
        </thead>
        <tbody>
          ${itemRows}
          <tr class="total-row">
            <td></td>
            <td><strong>Total</strong></td>
            <td style="text-align:right;"><strong>${totalQty}</strong></td>
            <td></td>
            <td style="text-align:right;"><strong>Rs ${parseFloat(entry.amount||0).toFixed(2)}</strong></td>
          </tr>
        </tbody>
      </table>
      <table class="print-summary-table" style="border-top:none;">
        <tr class="total-row">
          <td><strong>Total</strong></td>
          <td>:</td>
          <td><strong>Rs ${parseFloat(entry.amount||0).toFixed(2)}</strong></td>
        </tr>
        <tr>
          <td><strong>Amount in Words:</strong></td>
          <td colspan="2"></td>
        </tr>
        <tr class="print-words-row">
          <td colspan="3" style="padding-left:16px;">${numberToWords(parseFloat(entry.amount||0))}</td>
        </tr>
        <tr>
          <td>Paid</td>
          <td>:</td>
          <td>Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
        </tr>
        <tr>
          <td>Balance</td>
          <td>:</td>
          <td>Rs ${parseFloat(entry.balance||0).toFixed(2)}</td>
        </tr>
      </table>
      <div class="print-signatory">
        <div class="print-signatory-box">
          <div class="sig-for">For ${escHtml(userName)}:</div>
          <div class="sig-line">Authorized Signatory</div>
        </div>
      </div>`;

    printZoom = 100;
    document.getElementById('printZoomLabel').textContent = '100%';
    document.getElementById('printPageContent').style.transform = 'scale(1)';
    document.getElementById('printPageContent').style.transformOrigin = 'top center';
    document.getElementById('printViewOverlay').classList.add('open');
  }

  function closePrintView() {
    document.getElementById('printViewOverlay').classList.remove('open');
  }

  function changePrintZoom(delta) {
    printZoom = Math.max(50, Math.min(200, printZoom + delta));
    document.getElementById('printZoomLabel').textContent = printZoom + '%';
    document.getElementById('printPageContent').style.transform = 'scale(' + (printZoom/100) + ')';
    document.getElementById('printPageContent').style.transformOrigin = 'top center';
  }

  function formatDisplayDate(dateStr) {
    if (!dateStr) return '';
    // Convert YYYY-MM-DD to MM/DD/YYYY or DD/MM/YYYY
    const parts = dateStr.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return dateStr;
  }

  // ═══════════════════════════════════════════════════════
  //  PREVIEW MODAL (like Image 3)
  // ═══════════════════════════════════════════════════════
  function openPreview(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
    const cat   = categories[catIdx];
    const entry = cat ? cat.entries.find(e => e.id === expId) : null;
    if (!entry) return;

    const userName  = window.App?.user?.name || 'Company';
    const userPhone = window.App?.user?.phone || '';
    const userEmail = window.App?.user?.email || '';

    const itemRows = (entry.items && entry.items.length)
      ? entry.items.map((it, idx) => `
          <tr style="border-bottom:1px solid #e0e0e0;">
            <td style="padding:8px 12px;">${idx+1}</td>
            <td style="padding:8px 12px;">${escHtml(it.name||'')}</td>
            <td style="padding:8px 12px;text-align:right;">${parseFloat(it.qty||1)}</td>
            <td style="padding:8px 12px;text-align:right;">Rs ${parseFloat(it.price||0).toFixed(2)}</td>
            <td style="padding:8px 12px;text-align:right;">Rs ${parseFloat(it.amount||0).toFixed(2)}</td>
          </tr>`).join('')
      : `<tr style="border-bottom:1px solid #e0e0e0;">
          <td style="padding:8px 12px;">1</td>
          <td style="padding:8px 12px;">${escHtml(cat.name)}</td>
          <td style="padding:8px 12px;text-align:right;">1</td>
          <td style="padding:8px 12px;text-align:right;">Rs 0.00</td>
          <td style="padding:8px 12px;text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
        </tr>`;

    const totalQty = (entry.items && entry.items.length)
      ? entry.items.reduce((s, it) => s + parseFloat(it.qty||1), 0)
      : 1;

    document.getElementById('previewContent').innerHTML = `
      <div style="font-family:'Segoe UI',sans-serif;">
        <h2 style="text-align:center;font-size:18px;font-weight:700;margin-bottom:20px;color:#1a1f36;">Expense</h2>
        <div style="border:1px solid #ccc;padding:14px 18px;margin-bottom:0;">
          <div style="font-size:20px;font-weight:700;color:#1a1f36;margin-bottom:4px;">${escHtml(userName)}</div>
          <div style="font-size:12px;color:#555;display:flex;gap:28px;">
            ${userPhone ? '<span>Phone: <strong>' + escHtml(userPhone) + '</strong></span>' : ''}
            ${userEmail ? '<span>Email: <strong>' + escHtml(userEmail) + '</strong></span>' : ''}
          </div>
        </div>
        <table style="width:100%;border-collapse:collapse;border:1px solid #ccc;border-top:none;">
          <tr>
            <td style="padding:10px 16px;border-right:1px solid #ccc;width:50%;font-size:12px;vertical-align:top;">
              <strong style="display:block;margin-bottom:4px;color:#555;">Expense For:</strong>
              ${escHtml(cat.name)}
            </td>
            <td style="padding:10px 16px;font-size:12px;vertical-align:top;">
              <strong style="display:block;margin-bottom:4px;color:#555;">Expense Details:</strong>
              ${getExpensePreviewMetaHtml(entry)}
            </td>
          </tr>
        </table>
        <table style="width:100%;border-collapse:collapse;border:1px solid #ccc;border-top:none;">
          <thead>
            <tr style="background:#f5f5f5;">
              <th style="padding:9px 12px;border:1px solid #ccc;font-size:11px;text-align:left;">#</th>
              <th style="padding:9px 12px;border:1px solid #ccc;font-size:11px;text-align:left;">Item name</th>
              <th style="padding:9px 12px;border:1px solid #ccc;font-size:11px;text-align:right;">Quantity</th>
              <th style="padding:9px 12px;border:1px solid #ccc;font-size:11px;text-align:right;">Price/ Unit(Rs)</th>
              <th style="padding:9px 12px;border:1px solid #ccc;font-size:11px;text-align:right;">Amount(Rs)</th>
            </tr>
          </thead>
          <tbody>
            ${itemRows}
            <tr style="background:#f9f9f9;font-weight:700;border-top:1px solid #ccc;">
              <td style="padding:9px 12px;border:1px solid #ccc;"></td>
              <td style="padding:9px 12px;border:1px solid #ccc;">Total</td>
              <td style="padding:9px 12px;border:1px solid #ccc;text-align:right;">${totalQty}</td>
              <td style="padding:9px 12px;border:1px solid #ccc;"></td>
              <td style="padding:9px 12px;border:1px solid #ccc;text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
            </tr>
          </tbody>
        </table>
        <table style="width:100%;border-collapse:collapse;border:1px solid #ccc;border-top:none;">
          <tr style="background:#f9f9f9;font-weight:700;">
            <td style="padding:10px 16px;border:1px solid #ccc;">Total</td>
            <td style="padding:10px 16px;border:1px solid #ccc;">:</td>
            <td style="padding:10px 16px;border:1px solid #ccc;text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
          </tr>
          <tr>
            <td colspan="3" style="padding:10px 16px;border:1px solid #ccc;border-top:none;font-weight:600;font-size:12px;">Amount in Words:</td>
          </tr>
          <tr>
            <td colspan="3" style="padding:6px 16px 10px;border:1px solid #ccc;border-top:none;font-style:italic;font-size:12px;color:#555;">
              ${numberToWords(parseFloat(entry.amount||0))}
            </td>
          </tr>
          <tr>
            <td style="padding:9px 16px;border:1px solid #ccc;font-size:12px;">Paid</td>
            <td style="padding:9px 16px;border:1px solid #ccc;font-size:12px;">:</td>
            <td style="padding:9px 16px;border:1px solid #ccc;text-align:right;font-size:12px;">Rs ${parseFloat(entry.paidAmount ?? (parseFloat(entry.amount||0) - parseFloat(entry.balance||0))).toFixed(2)}</td>
          </tr>
          <tr>
            <td style="padding:9px 16px;border:1px solid #ccc;font-size:12px;">Balance</td>
            <td style="padding:9px 16px;border:1px solid #ccc;font-size:12px;">:</td>
            <td style="padding:9px 16px;border:1px solid #ccc;text-align:right;font-size:12px;">Rs ${parseFloat(entry.balance||0).toFixed(2)}</td>
          </tr>
        </table>
        <div style="display:flex;justify-content:flex-end;margin-top:24px;">
          <div style="border:1px solid #ccc;padding:14px 24px;min-width:220px;min-height:80px;text-align:center;">
            <div style="font-size:12px;color:#555;margin-bottom:36px;">For ${escHtml(userName)}:</div>
            <div style="border-top:1px solid #ccc;padding-top:6px;font-size:11px;color:#888;">Authorized Signatory</div>
          </div>
        </div>
      </div>`;

    // Store ref for Print button inside Preview
    window._previewExpId  = expId;
    window._previewCatIdx = catIdx;
    openModal('previewModal');
  }

  function previewDoPrint() {
    closeModal('previewModal');
    if (window._previewExpId != null) {
      openPrintView(window._previewExpId, window._previewCatIdx);
      setTimeout(() => window.print(), 300);
    }
  }
  function previewOpenPDF() {
  closeModal('previewModal');
  if (window._previewExpId == null) return;
  const cat   = categories[window._previewCatIdx];
  const entry = cat ? cat.entries.find(e => e.id === window._previewExpId) : null;
  if (!entry) return;

  const userName  = window.App?.user?.name || 'Company';
  const userPhone = window.App?.user?.phone || '';
  const userEmail = window.App?.user?.email || '';

  const itemRows = (entry.items && entry.items.length)
    ? entry.items.map((it, idx) => `<tr><td>${idx+1}</td><td>${escHtml(it.name||'')}</td><td style="text-align:right;">${parseFloat(it.qty||1)}</td><td style="text-align:right;">Rs ${parseFloat(it.price||0).toFixed(2)}</td><td style="text-align:right;">Rs ${parseFloat(it.amount||0).toFixed(2)}</td></tr>`).join('')
    : `<tr><td>1</td><td>${escHtml(cat.name)}</td><td style="text-align:right;">1</td><td style="text-align:right;">Rs 0.00</td><td style="text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td></tr>`;

  const totalQty = (entry.items && entry.items.length)
    ? entry.items.reduce((s, it) => s + parseFloat(it.qty||1), 0) : 1;

  const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Expense</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; font-size: 13px; color: #1a1f36; padding: 48px 56px; }
    h2 { text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 28px; }
    .company-box { border: 1px solid #ccc; padding: 16px 20px; margin-bottom: 0; }
    .company-name { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .company-meta { font-size: 12px; color: #555; display: flex; gap: 32px; }
    table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; }
    th { background: #f5f5f5; padding: 9px 12px; font-size: 11px; font-weight: 700; text-align: left; border: 1px solid #ccc; }
    td { padding: 9px 12px; border: 1px solid #ccc; font-size: 12px; }
    td:last-child, th:last-child { text-align: right; }
    .total-row td { font-weight: 700; background: #f9f9f9; }
    .signatory { margin-top: 40px; display: flex; justify-content: flex-end; }
    .signatory-box { border: 1px solid #ccc; padding: 14px 24px; min-width: 220px; min-height: 80px; text-align: center; }
  </style></head><body>
  <h2>Expense</h2>
  <div class="company-box">
    <div class="company-name">${escHtml(userName)}</div>
    <div class="company-meta">
      ${userPhone ? '<span>Phone: <strong>' + escHtml(userPhone) + '</strong></span>' : ''}
      ${userEmail ? '<span>Email: <strong>' + escHtml(userEmail) + '</strong></span>' : ''}
    </div>
  </div>
  <table style="border-top:none;"><tr>
    <td style="width:50%;"><strong style="display:block;color:#555;font-size:11px;">EXPENSE FOR:</strong>${escHtml(cat.name)}</td>
    <td><strong style="display:block;color:#555;font-size:11px;">EXPENSE DETAILS:</strong>${getExpensePreviewMetaHtml(entry)}</td>
  </tr></table>
  <table style="border-top:none;"><thead><tr>
    <th>#</th><th>Item name</th><th style="text-align:right;">Quantity</th><th style="text-align:right;">Price/Unit(Rs)</th><th style="text-align:right;">Amount(Rs)</th>
  </tr></thead><tbody>
    ${itemRows}
    <tr class="total-row"><td></td><td>Total</td><td style="text-align:right;">${totalQty}</td><td></td><td style="text-align:right;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td></tr>
  </tbody></table>
  <table style="border-top:none;">
    <tr class="total-row"><td>Total</td><td>:</td><td>Rs ${parseFloat(entry.amount||0).toFixed(2)}</td></tr>
    <tr><td colspan="3"><em>${numberToWords(parseFloat(entry.amount||0))}</em></td></tr>
    <tr><td>Paid</td><td>:</td><td>Rs ${parseFloat(entry.paidAmount ?? (parseFloat(entry.amount||0) - parseFloat(entry.balance||0))).toFixed(2)}</td></tr>
    <tr><td>Balance</td><td>:</td><td>Rs ${parseFloat(entry.balance||0).toFixed(2)}</td></tr>
  </table>
  <div class="signatory"><div class="signatory-box">
    <div style="font-size:12px;color:#555;margin-bottom:36px;">For ${escHtml(userName)}:</div>
    <div style="border-top:1px solid #ccc;padding-top:6px;font-size:11px;color:#888;">Authorized Signatory</div>
  </div></div>
  <script>window.onload = function() { window.print(); }<\/script>
  </body></html>`;

  const blob = new Blob([html], { type: 'text/html' });
  const url  = URL.createObjectURL(blob);
  window.open(url, '_blank');
}
  function previewSavePDF()  { showToast('Save PDF feature coming soon.', 'red'); }
  function previewEmailPDF() { showToast('Email PDF feature coming soon.', 'red'); }

  // ═══════════════════════════════════════════════════════
  //  VIEW HISTORY MODAL (like Image 1)
  // ═══════════════════════════════════════════════════════
  function openViewHistory(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));

    // In a real app you'd fetch history from the server.
    // For now show the same empty state as Image 1.
    document.getElementById('historyContent').innerHTML = `
      <svg width="100" height="100" viewBox="0 0 120 120" fill="none" style="margin-bottom:20px;opacity:0.3;">
        <rect x="18" y="10" width="60" height="80" rx="6" fill="#9ca3af"/>
        <rect x="28" y="5"  width="60" height="80" rx="6" fill="#d1d5db"/>
        <rect x="38" y="42" width="32" height="4" rx="2" fill="#2563eb"/>
        <rect x="38" y="52" width="24" height="4" rx="2" fill="#2563eb" opacity=".5"/>
      </svg>
      <p style="color:#9ca3af;font-size:13px;margin:0;">No edits have been made to this transaction.</p>`;

    openModal('viewHistoryModal');
  }

  // ═══════════════════════════════════════════════════════
  //  DETAIL PANEL — ITEMS
  // ═══════════════════════════════════════════════════════
  function renderItemsList() { renderCategoryList(); if (expenseItems.length) renderItemDetailPanel(selectedItemIdx); }
  function normalizeExpenseItemName(name) {
    return String(name || '').trim().toLowerCase();
  }
  function getItemTransactions(item) {
    const target = normalizeExpenseItemName(item?.name);
    if (!target) return [];

    const rowsByEntry = new Map();
    categories.forEach(cat => {
      (cat.entries || []).forEach(entry => {
        const matchedItems = (entry.items || []).filter(it => normalizeExpenseItemName(it.name) === target);
        if (!matchedItems.length) return;

        const amount = matchedItems.reduce((sum, matchedItem) => {
          return sum + (parseFloat(matchedItem.amount ?? (parseFloat(matchedItem.qty || 1) * parseFloat(matchedItem.price || 0))) || 0);
        }, 0);
        rowsByEntry.set(String(entry.id), {
          id: String(entry.id),
          catIdx: categories.indexOf(cat),
          date: entry.date || '',
          expNo: entry.expNo || '',
          party: entry.party || '',
          paymentType: entry.paymentType || '',
          status: entry.status || '',
          dueDate: entry.dueDate || entry.due_date || '',
          amount,
          balance: parseFloat(entry.balance || 0) || 0,
          entryId: entry.id,
        });
      });
    });

    const rows = Array.from(rowsByEntry.values());
    const dir = itemsSortDir === 'asc' ? 1 : -1;
    return rows.sort((a, b) => {
      let av = a[itemsSortCol] ?? '';
      let bv = b[itemsSortCol] ?? '';
      if (itemsSortCol === 'amount' || itemsSortCol === 'balance') {
        av = parseFloat(av) || 0;
        bv = parseFloat(bv) || 0;
        return (av - bv) * dir;
      }
      if (itemsSortCol === 'date' || itemsSortCol === 'dueDate') {
        av = av ? new Date(av).getTime() : 0;
        bv = bv ? new Date(bv).getTime() : 0;
        return (av - bv) * dir;
      }
      return String(av).localeCompare(String(bv)) * dir;
    });
  }
  function renderItemDetailPanel(i) {
    const it = expenseItems[i];
    if (!it) return;
    document.getElementById('itemsDetailTitle').textContent   = it.name.toUpperCase();
    const search = normalizeExpenseItemName(document.getElementById('itemsDetailSearchInput')?.value || '');
    const allRows = getItemTransactions(it);
    const rows = allRows.filter(row => {
      if (search && !(
        normalizeExpenseFilterText(row.date).includes(search) ||
        normalizeExpenseFilterText(row.expNo).includes(search) ||
        normalizeExpenseFilterText(row.party).includes(search) ||
        normalizeExpenseFilterText(row.paymentType).includes(search)
      )) return false;
      return matchesExpenseDateFilter(row.date, 'date') &&
        matchesExpenseTextFilter(row.expNo, 'expNo') &&
        matchesExpenseTextFilter(row.party, 'party') &&
        matchesExpensePaymentTypeFilter(row.paymentType, 'payType') &&
        matchesExpenseNumberFilter(row.amount, 'amount') &&
        matchesExpenseNumberFilter(row.balance, 'balance');
    });
    const totalAmount = rows.reduce((sum, row) => sum + (parseFloat(row.amount) || 0), 0);
    const totalBalance = rows.reduce((sum, row) => sum + (parseFloat(row.balance) || 0), 0);
    document.getElementById('itemsDetailTotal').textContent   = 'Rs ' + totalAmount.toFixed(2);
    document.getElementById('itemsDetailBalance').textContent = 'Rs ' + totalBalance.toFixed(2);
    const tbody = document.getElementById('itemsDetailTableBody');
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:24px;">No transactions to show</td></tr>';
      return;
    }
    tbody.innerHTML = '';
    rows.forEach((e, idx) => {
      const rowKey = e.id ?? e.entryId ?? e.expenseId ?? e.transactionId ?? `tx-${idx}`;
      const txId = e.entryId ?? e.id ?? '';
      const tr = document.createElement('tr');
      if (idx === 0) tr.classList.add('row-highlight');
      tr.addEventListener('dblclick', (ev) => {
        if (ev.target.closest('.td-row-menu') || ev.target.closest('.td-action-btn')) return;
        openViewEdit(txId, e.catIdx);
      });
      tr.innerHTML = `
          <td>${escHtml(e.date||'')}</td>
          <td>${escHtml(e.expNo||'')}</td>
          <td>${escHtml(e.party||'')}</td>
          <td>${escHtml(e.paymentType||'')}</td>
          <td style="font-weight:500;">${parseFloat(e.amount||0).toFixed(2)}</td>
          <td>${escHtml(formatExpenseStatusLabel(e.status))}</td>
          <td>${escHtml(formatExpenseDateDisplay(e.dueDate || e.due_date || ''))}</td>
          <td>${parseFloat(e.balance||0).toFixed(2)}</td>
          <td style="position:relative; width:40px; text-align:center;">
              <button type="button" class="td-action-btn" data-row-id="${rowKey}" data-entry-id="${e.entryId ?? e.id ?? ''}" onclick="toggleRowMenu(event, '${String(rowKey).replace(/'/g, "\\'")}', this)">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <div class="td-row-menu" id="rowMenu_${rowKey}">
                  <div class="td-row-menu-item" onclick="openViewEdit(${txId},${e.catIdx})">View/Edit</div>
                  <div class="td-row-menu-item danger" onclick="deleteExpenseRow(${txId},${e.catIdx})">Delete</div>
                  <div class="td-row-menu-item" onclick="duplicateExpenseRow(${txId},${e.catIdx})">Duplicate</div>
                  <div class="td-row-menu-item" onclick="openPrintView(${txId},${e.catIdx})">Print</div>
                  <div class="td-row-menu-item" onclick="openPreview(${txId},${e.catIdx})">Preview</div>
                  <div class="td-row-menu-item" onclick="openDirectPDF(${txId},${e.catIdx})">Open PDF</div>
                  <div class="td-row-menu-item" onclick="openViewHistory(${txId},${e.catIdx})">View History</div>
              </div>
          </td>`;
      tbody.appendChild(tr);
    });
  }

  // ─── EDIT ITEM ───
  document.getElementById('itemsDetailSearchInput')?.addEventListener('input', () => {
    if (currentTab === 'items') renderItemDetailPanel(selectedItemIdx);
  });

  function openEditItemModal(i) {
    closeAllItemMenus(-1); editingItemIdx = i;
    const it = expenseItems[i];
    document.getElementById('editItemName').value  = it.name;
    document.getElementById('editItemPrice').value = it.price || '';
    document.getElementById('editItemTaxIncluded').value = String(it.tax_included ?? 0);
    document.getElementById('editItemTaxRate').value = it.tax_rate_id || '';
    openModal('editItemModal');
    setTimeout(() => document.getElementById('editItemName').focus(), 80);
  }
  function saveEditItem() {
    const name  = document.getElementById('editItemName').value.trim();
    const price = parseFloat(document.getElementById('editItemPrice').value) || 0;
    if (!name) { showToast('Item name cannot be empty.', 'red'); return; }
    const it = expenseItems[editingItemIdx];
    const taxData = getExpenseItemTaxPayloadFromModal('editItem');
    ajax('PUT', window.expenseRoutes.itemUpdate + '/' + it.id, {
      name,
      price,
      tax_included: taxData.tax_included,
      tax_rate_id: taxData.tax_rate_id,
      tax_rate_name: taxData.tax_rate_name,
      tax_rate_value: taxData.tax_rate_value,
      tax_amount: taxData.tax_amount,
      amount: taxData.amount,
    }).then(res => {
      if (res.success) {
        expenseItems[editingItemIdx].name  = res.item.name;
        expenseItems[editingItemIdx].price = res.item.price;
        expenseItems[editingItemIdx].tax_included = res.item.tax_included ?? taxData.tax_included;
        expenseItems[editingItemIdx].tax_rate_id = res.item.tax_rate_id ?? taxData.tax_rate_id;
        expenseItems[editingItemIdx].tax_rate_name = res.item.tax_rate_name ?? taxData.tax_rate_name;
        expenseItems[editingItemIdx].tax_rate_value = res.item.tax_rate_value ?? taxData.tax_rate_value;
        expenseItems[editingItemIdx].tax_amount = res.item.tax_amount ?? taxData.tax_amount;
        expenseItems[editingItemIdx].amount = res.item.amount ?? taxData.amount;
        closeModal('editItemModal');
        renderCategoryList();
        if (selectedItemIdx === editingItemIdx) renderItemDetailPanel(editingItemIdx);
        showToast('Item updated.', 'green');
      }
    }).catch(() => showToast('Update failed.', 'red'));
  }
  function deleteItemFromEditModal() { closeModal('editItemModal'); deleteItemPrompt(editingItemIdx); }
  function deleteItemPrompt(i) {
    closeAllItemMenus(-1);
    showConfirm('Are you sure you want to delete this Item?', 'This Item will be Deleted.', () => {
      const it = expenseItems[i];
      ajax('DELETE', window.expenseRoutes.itemDestroy + '/' + it.id).then(res => {
        if (res.success) {
          expenseItems.splice(i, 1);
          selectedItemIdx = Math.max(0, selectedItemIdx - 1);
          renderCategoryList(); renderItemsList();
          showToast('Item deleted.', 'green');
        }
      }).catch(() => showToast('Delete failed.', 'red'));
    });
  }

  let editingCatIdx = -1;
  function openEditCatModal(i) {
    closeAllCatMenus(-1);
    editingCatIdx = i;
    const c = categories[i];
    document.getElementById('editCatName').value = c.name;
    document.getElementById('editCatType').value = c.type || 'Indirect Expense';
    openModal('editCatModal');
    setTimeout(() => document.getElementById('editCatName').focus(), 80);
  }
  function saveEditCategory() {
    const name = document.getElementById('editCatName').value.trim();
    const type = document.getElementById('editCatType').value;
    if (!name) { showToast('Category name cannot be empty.', 'red'); return; }
    const c = categories[editingCatIdx];
    ajax('PUT', window.expenseRoutes.categoryDestroy + '/' + c.id, { name, type }).then(res => {
        if (res.success) {
            categories[editingCatIdx].name = name;
            categories[editingCatIdx].type = type;
            closeModal('editCatModal');
            renderCategoryList();
            renderDetailPanel();
            showToast('Category updated.', 'green');
        } else {
            showToast(res.message || 'Update failed.', 'red');
        }
    }).catch(() => showToast('Update failed.', 'red'));
  }

  function deleteCategoryPrompt(i) {
    closeAllCatMenus(-1);
    const c = categories[i];
    if (c.entries && c.entries.length > 0) {
      showToast('Cannot delete: category has transactions.', 'red'); return;
    }
    showConfirm('Delete Category', 'This category will be deleted permanently.', () => {
      ajax('DELETE', window.expenseRoutes.categoryDestroy + '/' + c.id).then(res => {
        if (res.success) {
          categories.splice(i, 1);
          selectedCatIdx = Math.max(0, selectedCatIdx - 1);
          if (!categories.length) { showPage('emptyState'); return; }
          renderCategoryList(); renderDetailPanel();
          showToast('Category deleted.', 'green');
        } else {
          showToast(res.message || 'Delete failed.', 'red');
        }
      }).catch(() => showToast('Delete failed.', 'red'));
    });
  }

  // ═══════════════════════════════════════════════════════
  //  EXPENSE FORM
  // ═══════════════════════════════════════════════════════
  document.getElementById('splitAddBtn').addEventListener('click', openExpenseForm);
  function openExpenseForm() {
    if (!window.expenseStartInCreate && window.expenseRoutes?.expenseCreate) {
      window.location.href = window.expenseRoutes.expenseCreate;
      return;
    }
    resetForm();
    showPage('expenseFormPage');
  }

  function resetForm() {
    document.getElementById('formCatLabel').textContent = '';
    document.getElementById('formCatSelectBtn').classList.remove('filled');
    calSelDate = new Date(); setDateDisplay(calSelDate); calViewDate = new Date(); buildCalendar();
    expenseAttachmentFiles = { images: [], documents: [] };
    renderExpenseAttachmentPreview();
    document.getElementById('formExpNoInput').value = '';
    const resetPoNoInput = document.getElementById('expensePoNoInput');
    if (resetPoNoInput) resetPoNoInput.value = '';
    const resetPoDateInput = document.getElementById('expensePoDateInput');
    if (resetPoDateInput) resetPoDateInput.value = '';
    const resetTransactionTimeDisplay = document.getElementById('expenseTransactionTimeDisplay');
    if (resetTransactionTimeDisplay) resetTransactionTimeDisplay.value = formatExpenseTimeDisplay();
    const resetPaymentTermsDisplay = document.getElementById('expensePaymentTermsDisplay');
    if (resetPaymentTermsDisplay) resetPaymentTermsDisplay.value = getExpenseTransactionSettings()?.payment_terms?.name || 'Net 15';
    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    if (dealDaysSelect) dealDaysSelect.value = String(getExpenseTransactionSettings()?.payment_terms?.days ?? 0);
    if (dealDaysCustom) dealDaysCustom.classList.add('d-none');
    const resetDueDateDisplay = document.getElementById('expenseDueDateDisplay');
    if (resetDueDateDisplay) resetDueDateDisplay.value = '';
    const resetStatusSelect = document.getElementById('expenseStatusSelect');
    if (resetStatusSelect) resetStatusSelect.value = 'unpaid';
    const resetDiscountPercent = document.getElementById('expenseDiscountPercentInput');
    if (resetDiscountPercent) resetDiscountPercent.value = '';
    const resetDiscountAmount = document.getElementById('expenseDiscountAmountInput');
    if (resetDiscountAmount) resetDiscountAmount.value = '';
    const resetSummaryTaxRate = document.getElementById('expenseSummaryTaxRateSelect');
    if (resetSummaryTaxRate) resetSummaryTaxRate.value = '';
    const resetSummaryTaxAmount = document.getElementById('expenseSummaryTaxAmountInput');
    if (resetSummaryTaxAmount) resetSummaryTaxAmount.value = '';
    document.getElementById('expenseDescriptionInput').value = '';
    setExpenseDescriptionVisible(false);
    document.getElementById('expenseTaxSwitch').checked = false;
    clearExpenseParty();
    rowKey = 0;
    document.getElementById('formItemsBody').innerHTML = '';
    addItemRow(); appendStaticRow(); calcTotals();
    paymentRows = [{ type: 'Cheque', amount: '', ref: '' }];
    renderPaymentCard();
    tabCounter = 1; renderFormTabs(1); renderFormCatOptions();
    window._editingExpenseId = null;
    window._editingCatIdx    = null;
    updateExpenseDueDateDisplay();
    applyExpenseFeatureVisibility();
    renderExpensePartyOptions('');
  }

  function saveTabState() {
    if (!activeTabN) return;
    const s = tabStates[activeTabN] || defaultTabState();
    s.catName  = document.getElementById('formCatLabel')?.textContent?.trim() || '';
    s.partyId  = document.getElementById('expensePartyId')?.value || '';
    s.partyName = document.getElementById('expensePartySearch')?.value || '';
    s.poNo = document.getElementById('expensePoNoInput')?.value || '';
    s.poDate = document.getElementById('expensePoDateInput')?.value || '';
    s.transactionTime = document.getElementById('expenseTransactionTimeDisplay')?.value || '';
    s.dealDays = getExpenseDealDaysValue();
    s.dueDate = document.getElementById('expenseDueDateDisplay')?.value || '';
    s.paymentTermsName = document.getElementById('expensePaymentTermsDisplay')?.value || '';
    s.status = document.getElementById('expenseStatusSelect')?.value || 'unpaid';
    s.taxEnabled = !!document.getElementById('expenseTaxSwitch')?.checked;
    s.discountPercent = parseFloat(document.getElementById('expenseDiscountPercentInput')?.value || 0) || 0;
    s.discountAmount = parseFloat(document.getElementById('expenseDiscountAmountInput')?.value || 0) || 0;
    s.summaryTaxRateId = document.getElementById('expenseSummaryTaxRateSelect')?.value || '';
    s.summaryTaxAmount = parseFloat(document.getElementById('expenseSummaryTaxAmountInput')?.value || 0) || 0;
    s.expNo    = document.getElementById('formExpNoInput')?.value || '';
    s.date     = calSelDate ? new Date(calSelDate) : new Date();
    s.roundOff = document.getElementById('roundOffChk')?.checked || false;
    s.description = document.getElementById('expenseDescriptionInput')?.value || '';
    s.editingExpenseId = window._editingExpenseId || null;
    s.editingCatIdx    = window._editingCatIdx    || null;
    s.items = [];
    document.querySelectorAll('[id^="itemRow_"]').forEach(tr => {
      const rk = tr.id.replace('itemRow_', '');
      const taxSel = document.getElementById('itemTaxRate_' + rk);
      const taxRate = taxSel ? getExpenseTaxRateById(taxSel.value) : null;
      const baseAmount = (parseFloat(document.getElementById('itemQty_' + rk)?.value) || 0) * (parseFloat(document.getElementById('itemPrice_' + rk)?.value) || 0);
      const taxAmount = parseFloat(document.getElementById('itemTaxAmt_' + rk)?.value) || 0;
      s.items.push({
        rk,
        name  : document.getElementById('itemName_'  + rk)?.value || '',
        qty   : document.getElementById('itemQty_'   + rk)?.value || '',
        price : document.getElementById('itemPrice_' + rk)?.value || '',
        taxRateId: taxSel?.value || '',
        taxRateName: taxRate?.name || '',
        taxRateValue: taxRate?.rate || 0,
        taxAmount: taxAmount,
        baseAmount: baseAmount,
        amount: parseFloat(document.getElementById('itemAmt_' + rk)?.value) || 0,
      });
    });
    s.payments = paymentRows.map(p => ({ type: p.type || '', ref: p.ref || '', amount: p.amount || 0 }));
    s.additionalCharges = {};
    document.querySelectorAll('[data-add-charge]').forEach(input => {
      s.additionalCharges[input.dataset.addCharge] = parseFloat(input.value) || 0;
    });
    s.transportationDetails = {};
    document.querySelectorAll('[data-transport-field]').forEach(input => {
      s.transportationDetails[input.dataset.transportField] = input.value || '';
    });
    tabStates[activeTabN] = s;
  }

  function restoreTabState(tabN) {
    const s = tabStates[tabN] || defaultTabState();
    const catLbl = document.getElementById('formCatLabel');
    const catBtn = document.getElementById('formCatSelectBtn');
    if (catLbl) catLbl.textContent = s.catName || '';
    if (catBtn) catBtn.classList.toggle('filled', !!(s.catName));
    const partyIdEl = document.getElementById('expensePartyId');
    const partySearchEl = document.getElementById('expensePartySearch');
    if (partyIdEl) partyIdEl.value = s.partyId || '';
    if (partySearchEl) partySearchEl.value = s.partyName || '';
    const poNoEl = document.getElementById('expensePoNoInput');
    const poDateEl = document.getElementById('expensePoDateInput');
    const timeEl = document.getElementById('expenseTransactionTimeDisplay');
    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    const dueDateEl = document.getElementById('expenseDueDateDisplay');
    const paymentTermsEl = document.getElementById('expensePaymentTermsDisplay');
    const statusEl = document.getElementById('expenseStatusSelect');
    if (poNoEl) poNoEl.value = s.poNo || '';
    if (poDateEl) poDateEl.value = s.poDate || '';
    if (timeEl) timeEl.value = s.transactionTime || '';
    if (paymentTermsEl) paymentTermsEl.value = s.paymentTermsName || getExpenseTransactionSettings()?.payment_terms?.name || 'Net 15';
    if (statusEl) statusEl.value = s.status || 'unpaid';
    const discountPercentEl = document.getElementById('expenseDiscountPercentInput');
    const discountAmountEl = document.getElementById('expenseDiscountAmountInput');
    const summaryTaxRateEl = document.getElementById('expenseSummaryTaxRateSelect');
    const summaryTaxAmountEl = document.getElementById('expenseSummaryTaxAmountInput');
    if (discountPercentEl) discountPercentEl.value = s.discountPercent || '';
    if (discountAmountEl) discountAmountEl.value = s.discountAmount || '';
    if (summaryTaxRateEl) summaryTaxRateEl.value = s.summaryTaxRateId || '';
    if (summaryTaxAmountEl) summaryTaxAmountEl.value = s.summaryTaxAmount || '';
    if (dealDaysSelect) {
      const dealDaysValue = s.dealDays !== undefined && s.dealDays !== null ? String(s.dealDays) : '';
      const presetValues = ['0', '5', '10', '15', '30', '45'];
      if (dealDaysValue && presetValues.includes(dealDaysValue)) {
        dealDaysSelect.value = dealDaysValue;
        dealDaysCustom?.classList.add('d-none');
      } else if (dealDaysValue && dealDaysValue !== '0') {
        dealDaysSelect.value = 'custom';
        if (dealDaysCustom) {
          dealDaysCustom.value = dealDaysValue;
          dealDaysCustom.classList.remove('d-none');
        }
      } else {
        dealDaysSelect.value = '0';
        dealDaysCustom?.classList.add('d-none');
      }
    }
    if (dueDateEl) dueDateEl.value = s.dueDate ? formatExpenseDateDisplay(s.dueDate) : '';
    updateExpenseDueDateDisplay();
    const selectedParty = getExpensePartyById(s.partyId);
    if (selectedParty) {
      const balanceEl = document.getElementById('expensePartyBalance');
      const balance = parseFloat(selectedParty.current_balance || selectedParty.opening_balance || 0);
      if (balanceEl) balanceEl.textContent = `Balance: Rs ${formatExpenseMoney(balance)}`;
    }
    const taxSwitch = document.getElementById('expenseTaxSwitch');
    if (taxSwitch) taxSwitch.checked = !!s.taxEnabled;
    const expNoEl = document.getElementById('formExpNoInput');
    if (expNoEl) expNoEl.value = s.expNo || '';
    calSelDate  = s.date ? new Date(s.date) : new Date();
    calViewDate = new Date(calSelDate);
    setDateDisplay(calSelDate);
    buildCalendar();
    const chkEl = document.getElementById('roundOffChk');
    if (chkEl) chkEl.checked = !!s.roundOff;
    const descEl = document.getElementById('expenseDescriptionInput');
    if (descEl) descEl.value = s.description || '';
    setExpenseDescriptionVisible(!!s.description);
    rowKey = 0;
    document.getElementById('formItemsBody').innerHTML = '';
    if (s.items && s.items.length > 0) {
      s.items.forEach(it => {
        addItemRow();
        const rk = rowKey;
        const nameEl  = document.getElementById('itemName_'  + rk);
        const qtyEl   = document.getElementById('itemQty_'   + rk);
        const priceEl = document.getElementById('itemPrice_' + rk);
        const taxSel  = document.getElementById('itemTaxRate_' + rk);
        const taxAmt  = document.getElementById('itemTaxAmt_' + rk);
        if (nameEl)  nameEl.value  = it.name  || '';
        if (qtyEl)   qtyEl.value   = it.qty   || '';
        if (priceEl) priceEl.value = it.price || '';
        if (taxSel)  taxSel.value  = it.taxRateId || '';
        calcRow(rk);
        if (taxAmt) taxAmt.value = it.taxAmount || 0;
      });
    } else {
      addItemRow();
    }
    appendStaticRow();
    activeTabN = tabN;
    paymentRows = (s.payments && s.payments.length)
      ? s.payments.map(p => ({ type: p.type || 'Cheque', ref: p.ref || '', amount: p.amount || '' }))
      : [{ type: 'Cheque', ref: '', amount: '' }];
    renderPaymentCard();
    window._editingExpenseId = s.editingExpenseId || null;
    window._editingCatIdx    = s.editingCatIdx    || null;
    applyExpenseFeatureVisibility();
    calcTotals();
  }

  // ─── FORM TABS ───
  function renderFormTabs(activeN) {
    const bar = document.getElementById('formTabsBar');
    bar.innerHTML = '';
    for (let i = 1; i <= tabCounter; i++) {
      const div = document.createElement('div');
      div.className = 'form-tab' + (i === activeN ? ' active' : '');
      div.id = 'formTab_' + i;
      const n = i;
      const label = document.createElement('span');
      label.textContent = 'Expense #' + i;
      label.style.pointerEvents = 'none';
      const x = document.createElement('span');
      x.className = 'form-tab-close';
      x.innerHTML = '&#x2715;';
      x.addEventListener('click', function(ev) { ev.stopPropagation(); tryCloseFormTab(n); });
      div.appendChild(label);
      div.appendChild(x);
      div.addEventListener('click', () => activateFormTab(n));
      bar.appendChild(div);
    }
    const addBtn = document.createElement('div');
    addBtn.className = 'form-tab-add';
    addBtn.textContent = '+';
    addBtn.onclick = addFormTab;
    bar.appendChild(addBtn);
    if (!tabStates[activeN]) tabStates[activeN] = defaultTabState();
    activeTabN = activeN;
  }

  function addFormTab() {
    saveTabState();
    tabCounter++;
    tabStates[tabCounter] = defaultTabState();
    const bar = document.getElementById('formTabsBar');
    const addBtn = bar.querySelector('.form-tab-add');
    const div = document.createElement('div');
    div.className = 'form-tab';
    div.id = 'formTab_' + tabCounter;
    const n = tabCounter;
    const label = document.createElement('span');
    label.textContent = 'Expense #' + tabCounter;
    label.style.pointerEvents = 'none';
    const x = document.createElement('span');
    x.className = 'form-tab-close';
    x.innerHTML = '&#x2715;';
    x.addEventListener('click', function(ev) { ev.stopPropagation(); tryCloseFormTab(n); });
    div.appendChild(label);
    div.appendChild(x);
    div.addEventListener('click', () => activateFormTab(n));
    bar.insertBefore(div, addBtn);
    activateFormTab(n);
  }

  function activateFormTab(n) {
    if (activeTabN === n) return;
    saveTabState();
    document.querySelectorAll('.form-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('formTab_' + n)?.classList.add('active');
    restoreTabState(n);
  }

  function tryCloseFormTab(n, e) {
    if (e) e.stopPropagation();
    closingTabN = n;
    document.getElementById('closeExpenseOverlay').classList.add('open');
  }
  function closeExpenseCancel() { document.getElementById('closeExpenseOverlay').classList.remove('open'); closingTabN = null; }
  function closeExpenseConfirm() {
    document.getElementById('closeExpenseOverlay').classList.remove('open');
    if (closingTabN === 'all') {
      document.getElementById('formTabsBar').innerHTML = '';
      tabCounter = 0;
      activeTabN = null;
      showPage(categories.length ? 'splitPane' : 'emptyState');
    } else if (closingTabN !== null) {
      document.getElementById('formTab_' + closingTabN)?.remove();
      if (!document.querySelector('.form-tab')) {
        tabCounter = 0;
        activeTabN = null;
        showPage(categories.length ? 'splitPane' : 'emptyState');
      }
    }
    closingTabN = null;
  }

  // ─── CATEGORY DROPDOWN ───
  function toggleCatDropdown(e) { e.stopPropagation(); document.getElementById('formCatDropdown').classList.toggle('open'); }
  function renderFormCatOptions() {
    const el = document.getElementById('formCatOptions'); el.innerHTML = '';
    categories.forEach((c, i) => {
      const div = document.createElement('div'); div.className = 'cat-option'; div.textContent = c.name;
      div.onclick = () => pickCategory(i); el.appendChild(div);
    });
  }
  function pickCategory(i) {
    document.getElementById('formCatLabel').textContent = categories[i].name;
    document.getElementById('formCatSelectBtn').classList.add('filled');
    document.getElementById('formCatDropdown').classList.remove('open');
  }

  // ─── ADD CATEGORY ───
  function openAddCatModal() {
    document.getElementById('formCatDropdown').classList.remove('open');
    document.getElementById('newCatName').value = '';
    document.getElementById('newCatType').value = 'Indirect Expense';
    openModal('addCatModal');
    setTimeout(() => document.getElementById('newCatName').focus(), 80);
  }
  function saveNewCategory() {
    const name = document.getElementById('newCatName').value.trim();
    if (!name) return;
    const type = document.getElementById('newCatType').value;
    ajax('POST', window.expenseRoutes.categoryStore, { name, type }).then(res => {
      if (res.success) {
        categories.push(res.category);
        renderFormCatOptions(); renderCategoryList();
        pickCategory(categories.length - 1);
        closeModal('addCatModal');
      }
    }).catch(() => showToast('Failed to save category.', 'red'));
  }

  // ─── ITEM ROWS ───
  function appendStaticRow() {
    document.getElementById('staticRow2')?.remove();
  }
  function addItemRow() {
    rowKey++;
    const body = document.getElementById('formItemsBody');
    document.getElementById('staticRow2')?.remove();
    const tr = document.createElement('tr');
    tr.id = 'itemRow_' + rowKey;
    const rk = rowKey;
    const taxOptions = renderExpenseTaxRateOptions();
    tr.innerHTML = `
      <td style="text-align:center;color:#555;font-size:13px;padding:6px 8px;">${rowKey}</td>
      <td class="item-dd-wrap">
        <input type="text" id="itemName_${rk}" onfocus="showItemDropdown(${rk})" oninput="filterItemDropdown(${rk})" autocomplete="off" style="width:100%;">
        <div class="item-dd-list" id="itemDd_${rk}">
          <div class="item-dd-add-row" onclick="openAddItemModal()"><i class="bi bi-plus-circle-fill text-primary"></i> Add Expense Item</div>
          <div id="itemDdOpts_${rk}"></div>
        </div>
      </td>
      <td><input type="number" id="itemQty_${rk}" min="0" value="1" oninput="calcRow(${rk})"></td>
      <td><input type="number" id="itemPrice_${rk}" min="0" oninput="calcRow(${rk})"></td>
      <td class="expense-tax-cell d-none">
        <div style="display:flex; flex-direction:column; gap:6px; min-width:150px;">
          <select id="itemTaxRate_${rk}" oninput="calcRow(${rk})" style="border:1px solid #cbd5e1; border-radius:6px; padding:7px 8px; font-size:12px; outline:none; background:#fff; min-width:120px;">
            <option value="">Select</option>
            ${taxOptions}
          </select>
          <input type="number" id="itemTaxAmt_${rk}" readonly style="border:1px solid #cbd5e1; border-radius:6px; padding:7px 8px; font-size:12px; background:#f8fafc; outline:none; min-width:120px;" placeholder="0.00">
        </div>
      </td>
      <td><input type="number" id="itemAmt_${rk}" readonly></td>`;
    body.appendChild(tr);
    renderItemDdOptions(rk);
    applyExpenseFeatureVisibility();
  }
  function showItemDropdown(rk) { renderItemDdOptions(rk); document.querySelectorAll('.item-dd-list').forEach(d => d.classList.remove('open')); document.getElementById('itemDd_'+rk)?.classList.add('open'); }
  function filterItemDropdown(rk) { showItemDropdown(rk); }
  function renderItemDdOptions(rk) {
    const el = document.getElementById('itemDdOpts_'+rk); if (!el) return;
    const q = (document.getElementById('itemName_'+rk)?.value || '').toLowerCase();
    el.innerHTML = '';
    expenseItems.filter(it => it.name.toLowerCase().includes(q)).forEach(it => {
      const d = document.createElement('div'); d.className = 'item-option';
      d.innerHTML = `<span>${escHtml(it.name)}</span><span class="item-price">PRICE: ${parseFloat(it.price||0)}</span>`;
      d.onclick = () => { document.getElementById('itemName_'+rk).value = it.name; document.getElementById('itemPrice_'+rk).value = it.price||''; calcRow(rk); document.getElementById('itemDd_'+rk)?.classList.remove('open'); };
      el.appendChild(d);
    });
  }
  function calcRow(rk) {
    const qtyEl = document.getElementById('itemQty_'+rk);
    const qty   = qtyEl && qtyEl.value !== '' ? (parseFloat(qtyEl.value) || 0) : 1;
    const price = parseFloat(document.getElementById('itemPrice_'+rk)?.value) || 0;
    const taxSel = document.getElementById('itemTaxRate_'+rk);
    const taxAmtEl = document.getElementById('itemTaxAmt_'+rk);
    const amtEl = document.getElementById('itemAmt_'+rk);
    const baseAmount = qty * price;
    let taxAmount = 0;
    if (isExpenseTaxOn() && taxSel && taxSel.value) {
      const taxRate = getExpenseTaxRateById(taxSel.value);
      taxAmount = baseAmount * ((parseFloat(taxRate?.rate) || 0) / 100);
    }
    if (taxAmtEl) taxAmtEl.value = taxAmount ? taxAmount.toFixed(2) : '0.00';
    if (amtEl) amtEl.value = (baseAmount + taxAmount) ? (baseAmount + taxAmount).toFixed(2) : '';
    calcTotals();
  }
  function calcTotals() {
    let tQ = 0;
    let tA = 0;
    document.querySelectorAll('[id^="itemQty_"]').forEach(el => { tQ += el.value !== '' ? (parseFloat(el.value) || 0) : 1; });
    document.querySelectorAll('[id^="itemAmt_"]').forEach(el => { tA += parseFloat(el.value) || 0; });
    let addChargesTotal = 0;
    document.querySelectorAll('[data-add-charge]').forEach(el => { addChargesTotal += parseFloat(el.value) || 0; });
    const totalAmount = tA + addChargesTotal;
    document.getElementById('formQtyTotal').textContent = tQ || 0;
    document.getElementById('formAmtTotal').dataset.rawTotal = String(totalAmount || 0);
    document.getElementById('formAmtTotal').textContent = totalAmount ? totalAmount.toFixed(2) : '0';
    const discountAmount = parseFloat(document.getElementById('expenseDiscountAmountInput')?.value || currentExpenseState().discountAmount || 0) || 0;
    const summaryTaxAmount = parseFloat(document.getElementById('expenseSummaryTaxAmountInput')?.value || currentExpenseState().summaryTaxAmount || 0) || 0;
    const netTotal = Math.max(totalAmount - discountAmount + summaryTaxAmount, 0);
    const rounded = Math.round(netTotal);
    const chk = document.getElementById('roundOffChk');
    document.getElementById('roundOffVal').value = netTotal ? (rounded - netTotal).toFixed(2) : '0';
    document.getElementById('formTotalBox').textContent = netTotal ? (chk && chk.checked ? rounded.toFixed(2) : netTotal.toFixed(2)) : '0';
    const ptEl = document.getElementById('payTotalText');
    if (ptEl) {
      const payTotal = paymentRows.reduce((s,r) => s + (parseFloat(r.amount)||0), 0);
      ptEl.textContent = 'Total payment: ' + payTotal + '/' + netTotal;
    }
    const statusSelect = document.getElementById('expenseStatusSelect');
    if (statusSelect) statusSelect.value = getExpenseDerivedStatus(netTotal, paymentRows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0));
  }
  function saveNewItem() {
    const name  = document.getElementById('newItemName').value.trim();
    const price = parseFloat(document.getElementById('newItemPrice').value) || 0;
    if (!name) { showToast('Item name cannot be empty.', 'red'); return; }
    const taxData = getExpenseItemTaxPayloadFromModal('newItem');
    const saveBtn = document.querySelector('#addItemModal .btn-save-modal');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving...'; }
    const resetBtn = () => { if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Save'; } };
    const addItemLocally = (item) => {
  const idx = expenseItems.findIndex(it => String(it.id) === String(item.id) || String(it.name || '').trim().toLowerCase() === String(item.name || '').trim().toLowerCase());
  if (idx > -1) expenseItems[idx] = { ...expenseItems[idx], ...item };
  else expenseItems.push(item);
  closeModal('addItemModal');
  document.querySelectorAll('[id^="itemDdOpts_"]').forEach(el => {
    const rk = el.id.replace('itemDdOpts_', '');
    renderItemDdOptions(rk);
  });
  const activeInput = document.querySelector('[id^="itemName_"]:focus') ||
                      document.querySelector('[id^="itemName_"]');
  if (activeInput) {
    const rk = activeInput.id.replace('itemName_', '');
    activeInput.value = item.name;
    const priceEl = document.getElementById('itemPrice_' + rk);
    const qtyEl   = document.getElementById('itemQty_'   + rk);
    const taxSelEl = document.getElementById('itemTaxRate_' + rk);
    if (priceEl) priceEl.value = item.price || '';
    if (qtyEl)   qtyEl.value   = 1;
    if (taxSelEl && item.tax_rate_id) taxSelEl.value = item.tax_rate_id;
    document.getElementById('itemDd_' + rk)?.classList.remove('open');
    calcRow(rk);
  }
  if (currentTab === 'items') renderCategoryList();
  showToast('Item saved successfully.', 'green');
};
    ajax('POST', window.expenseRoutes.itemStore, {
      name,
      price,
      tax_included: taxData.tax_included,
      tax_rate_id: taxData.tax_rate_id,
      tax_rate_name: taxData.tax_rate_name,
      tax_rate_value: taxData.tax_rate_value,
      tax_amount: taxData.tax_amount,
      amount: taxData.amount,
    })
      .then(res => {
        resetBtn();
        if (res.success && res.item) addItemLocally({ ...res.item, ...taxData });
        else addItemLocally({ id: 'local_' + Date.now(), name, price, ...taxData });
      })
      .catch(() => { resetBtn(); addItemLocally({ id: 'local_' + Date.now(), name, price, ...taxData }); });
  }


  function payRowChange(i,field,val) {
    paymentRows[i][field]=val;
    calcTotals();
  }
  function addPaymentRow() {
  paymentRows.push({type:'', amount:0, ref:''});
  renderPaymentCard();
  calcTotals();
}

  // ─── SAVE EXPENSE ───
  function saveExpense() {
    const lbl = document.getElementById('formCatLabel').textContent.trim();
    if (!lbl) { showToast('Expense Category can not be left empty.', 'red'); return; }
    const cat = categories.find(c => c.name === lbl);
    if (!cat) return;
    if (window._editingExpenseId) {
      const editId     = window._editingExpenseId;
      const editCatIdx = window._editingCatIdx;
      window._editingExpenseId = null;
      window._editingCatIdx    = null;
      ajax('DELETE', window.expenseRoutes.expenseDestroy + '/' + editId).then(() => {
        const oldCat = categories[editCatIdx];
        if (oldCat) {
          const idx = oldCat.entries.findIndex(e => e.id === editId);
          if (idx > -1) {
            oldCat.amount = parseFloat(oldCat.amount) - parseFloat(oldCat.entries[idx].amount);
            oldCat.entries.splice(idx, 1);
          }
        }
        _doSaveExpense(cat);
      }).catch(() => _doSaveExpense(cat));
      return;
    }
    _doSaveExpense(cat);
  }

  function _doSaveExpense(cat) {
    if (!cat) return;
    const totalAmount = parseFloat(document.getElementById('formTotalBox').textContent) || 0;
    const payTypeRaw  = paymentRows[0]?.type || 'Cash';
    const ref         = paymentRows[0]?.ref  || '';
    const dateVal     = document.getElementById('formDateVal').textContent;
    const expNo       = document.getElementById('formExpNoInput').value.trim();
    const partyId     = document.getElementById('expensePartyId')?.value || '';
    const partyName   = document.getElementById('expensePartySearch')?.value || '';
    const taxEnabled  = !!document.getElementById('expenseTaxSwitch')?.checked;
    const firstTaxSel = document.getElementById('itemTaxRate_1');
    const firstTaxRate = firstTaxSel ? getExpenseTaxRateById(firstTaxSel.value) : null;
    const itemsJson = [];
    document.querySelectorAll('[id^="itemRow_"]').forEach(tr => {
      const rk = tr.id.replace('itemRow_', '');
      const taxSel = document.getElementById('itemTaxRate_' + rk);
      const taxRate = taxSel ? getExpenseTaxRateById(taxSel.value) : null;
      itemsJson.push({
        rk,
        name: document.getElementById('itemName_' + rk)?.value || '',
        qty: document.getElementById('itemQty_' + rk)?.value || '',
        price: document.getElementById('itemPrice_' + rk)?.value || '',
        taxRateId: taxSel?.value || '',
        taxRateName: taxRate?.name || '',
        taxRateValue: taxRate?.rate || 0,
        taxAmount: parseFloat(document.getElementById('itemTaxAmt_' + rk)?.value) || 0,
        amount: parseFloat(document.getElementById('itemAmt_' + rk)?.value) || 0,
      });
    });
    const additionalCharges = {};
    document.querySelectorAll('[data-add-charge]').forEach(input => {
      additionalCharges[input.dataset.addCharge] = parseFloat(input.value) || 0;
    });
    const transportationDetails = {};
    document.querySelectorAll('[data-transport-field]').forEach(input => {
      transportationDetails[input.dataset.transportField] = input.value || '';
    });

    const parts   = dateVal.split('/');
    const dbDate  = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateVal;
    const btn     = document.getElementById('btnSaveExpense');
    btn.disabled = true; btn.textContent = 'Saving...';
    let bankAccountId = '';
    let paymentType = payTypeRaw;
    if (String(payTypeRaw).startsWith('bank:')) {
      bankAccountId = String(payTypeRaw).split(':')[1] || '';
      paymentType = 'Bank';
    }
    const saveHeaderExtras = taxEnabled && expenseHasTaxRates;
    const dealDaysValue = saveHeaderExtras ? getExpenseDealDaysValue() : 0;
    const dueDateDisplayValue = saveHeaderExtras ? (document.getElementById('expenseDueDateDisplay')?.value || '') : '';
    const dueDateObj = dueDateDisplayValue ? parseExpenseDateDisplay(dueDateDisplayValue) : null;
    const dueDateValue = saveHeaderExtras && dueDateObj ? formatExpenseDateDb(dueDateObj) : '';
    const paymentTermsName = saveHeaderExtras ? (document.getElementById('expensePaymentTermsDisplay')?.value || '') : '';
    const paidTotal = parseFloat((paymentRows || []).reduce((sum, row) => sum + (parseFloat(row.amount) || 0), 0)) || 0;
    const statusValue = getExpenseDerivedStatus(totalAmount, paidTotal);
    const discountPercent = parseFloat(document.getElementById('expenseDiscountPercentInput')?.value || currentExpenseState().discountPercent || 0) || 0;
    const discountAmount = parseFloat(document.getElementById('expenseDiscountAmountInput')?.value || currentExpenseState().discountAmount || 0) || 0;
    const summaryTaxRateId = document.getElementById('expenseSummaryTaxRateSelect')?.value || currentExpenseState().summaryTaxRateId || '';
    const summaryTaxRate = getExpenseTaxRateById(summaryTaxRateId);
    const summaryTaxAmount = parseFloat(document.getElementById('expenseSummaryTaxAmountInput')?.value || currentExpenseState().summaryTaxAmount || 0) || 0;
    const formData = new FormData();
    formData.append('expense_category_id', cat.id);
    formData.append('expense_no', expNo);
    formData.append('expense_date', dbDate);
    formData.append('party_id', partyId);
    formData.append('party', partyName);
    formData.append('po_no', saveHeaderExtras ? (document.getElementById('expensePoNoInput')?.value || '') : '');
    formData.append('po_date', saveHeaderExtras ? (document.getElementById('expensePoDateInput')?.value || '') : '');
    formData.append('transaction_time', saveHeaderExtras ? (document.getElementById('expenseTransactionTimeDisplay')?.value || '') : '');
    formData.append('transaction_time_enabled', saveHeaderExtras && expenseTransactionSettings?.transaction_header?.transaction_time_enabled ? '1' : '0');
    formData.append('deal_days', String(dealDaysValue));
    formData.append('due_date', dueDateValue);
    formData.append('payment_terms_name', paymentTermsName);
    formData.append('status', statusValue);
    formData.append('discount_percent', discountPercent);
    formData.append('discount_amount', discountAmount);
    formData.append('tax_enabled', taxEnabled ? '1' : '0');
    formData.append('tax_rate_id', summaryTaxRate?.id || firstTaxRate?.id || '');
    formData.append('tax_rate_name', summaryTaxRate?.name || firstTaxRate?.name || '');
    formData.append('tax_rate_value', summaryTaxRate?.rate || firstTaxRate?.rate || 0);
    formData.append('tax_amount', summaryTaxAmount || itemsJson.reduce((s, r) => s + (parseFloat(r.taxAmount) || 0), 0));
    formData.append('items_json', JSON.stringify(itemsJson));
    formData.append('additional_charges', JSON.stringify(additionalCharges));
    formData.append('transportation_details', JSON.stringify(transportationDetails));
    formData.append('description', document.getElementById('expenseDescriptionInput')?.value || '');
    formData.append('bank_account_id', bankAccountId);
    formData.append('total_amount', totalAmount);
    formData.append('payment_type', paymentType);
    formData.append('reference_no', ref);
    formData.append('payments_json', JSON.stringify(paymentRows.map(row => ({
      type: row.type || '',
      amount: parseFloat(row.amount) || 0,
      ref: row.ref || '',
    }))));
    expenseAttachmentFiles.images.forEach(file => formData.append('images[]', file));
    expenseAttachmentFiles.documents.forEach(file => formData.append('documents[]', file));

    fetch(window.expenseRoutes.expenseSave, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: formData,
    }).then(async r => {
      const raw = await r.text();
      let payload = {};
      try { payload = raw ? JSON.parse(raw) : {}; } catch (_) { payload = { message: raw || '' }; }
      if (!r.ok || payload.success === false) {
        const message = payload.message || Object.values(payload.errors || {}).flat().filter(Boolean)[0] || 'Save failed.';
        throw new Error(message);
      }
      return payload;
    }).then(res => {
      btn.disabled = false; btn.textContent = 'Save';
      window.location.href = window.expenseRoutes.expense;
    }).catch((err) => {
      btn.disabled = false; btn.textContent = 'Save';
      showToast(err?.message || 'Save failed.', 'red');
    });
  }

  function setDateDisplay(d) {
    const dd=String(d.getDate()).padStart(2,'0'), mm=String(d.getMonth()+1).padStart(2,'0');
    document.getElementById('formDateVal').textContent = dd+'/'+mm+'/'+d.getFullYear();
    updateExpenseDueDateDisplay();
  }
  function toggleCalendar(e) { e.stopPropagation(); const p=document.getElementById('calendarPopup'); p.classList.toggle('open'); if(p.classList.contains('open')) buildCalendar(); }
  function calNav(dir) { calViewDate=new Date(calViewDate.getFullYear(),calViewDate.getMonth()+dir,1); buildCalendar(); }
  function buildCalendar() {
    const months=['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('calMonthLabel').textContent=months[calViewDate.getMonth()]+' '+calViewDate.getFullYear();
    const grid=document.getElementById('calGrid'); grid.innerHTML='';
    ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d => { const el=document.createElement('div'); el.className='cal-day-name'; el.textContent=d; grid.appendChild(el); });
    const first=new Date(calViewDate.getFullYear(),calViewDate.getMonth(),1);
    const last=new Date(calViewDate.getFullYear(),calViewDate.getMonth()+1,0);
    const prevLast=new Date(calViewDate.getFullYear(),calViewDate.getMonth(),0).getDate();
    for(let i=0;i<first.getDay();i++){const el=document.createElement('div');el.className='cal-day other-month';el.textContent=prevLast-first.getDay()+1+i;grid.appendChild(el);}
    for(let d=1;d<=last.getDate();d++){
      const el=document.createElement('div'); el.className='cal-day';
      if(calSelDate&&d===calSelDate.getDate()&&calViewDate.getMonth()===calSelDate.getMonth()&&calViewDate.getFullYear()===calSelDate.getFullYear()) el.classList.add('selected');
      el.textContent=d; const day=d; el.onclick=()=>pickCalendarDate(day); grid.appendChild(el);
    }
    const trailing=(first.getDay()+last.getDate())%7;
    if(trailing){for(let d=1;d<=7-trailing;d++){const el=document.createElement('div');el.className='cal-day other-month';el.textContent=d;grid.appendChild(el);}}
  }
  function pickCalendarDate(d) { calSelDate=new Date(calViewDate.getFullYear(),calViewDate.getMonth(),d); setDateDisplay(calSelDate); document.getElementById('calendarPopup').classList.remove('open'); buildCalendar(); }

  function toggleShareDropdown() { document.getElementById('shareDropdown').classList.toggle('open'); }

  // ═══════════════════════════════════════════════════════
  //  MODALS / CONFIRM / TOAST
  // ═══════════════════════════════════════════════════════
  function openModal(id)  { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  document.querySelectorAll('.modal-overlay').forEach(o => { o.addEventListener('click', e => { if(e.target===o) o.classList.remove('open'); }); });

  function showConfirm(title, msg, cb) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMsg').textContent   = msg;
    pendingConfirmCb = cb;
    document.getElementById('confirmOverlay').classList.add('open');
    document.getElementById('confirmYesBtn').onclick = () => {
      const cb = pendingConfirmCb;
      closeConfirm();
      if(cb) cb();
    };
  }
  function closeConfirm() { document.getElementById('confirmOverlay').classList.remove('open'); pendingConfirmCb=null; }
  document.getElementById('confirmOverlay').addEventListener('click', e => { if(e.target===document.getElementById('confirmOverlay')) closeConfirm(); });

  let _toastTimer;
  function showToast(msg, type) {
    const el=document.getElementById('toastEl'); const icon=el.querySelector('.toast-icon-el i');
    document.getElementById('toastMsg').textContent=msg;
    el.className='toast-custom show toast-'+(type||'red');
    icon.className=type==='green'?'bi bi-check-circle-fill':'bi bi-shield-exclamation';
    clearTimeout(_toastTimer); _toastTimer=setTimeout(hideToast,4000);
  }
  function hideToast() { document.getElementById('toastEl').classList.remove('show'); }

  document.addEventListener('click', e => {
    const actionButton = e.target.closest('.td-action-btn');
    if (actionButton) {
      e.preventDefault();
      e.stopPropagation();
      toggleRowMenu(e, actionButton.getAttribute('data-row-id'), actionButton);
      return;
    }

    if(!e.target.closest('#formCatWrap')) document.getElementById('formCatDropdown')?.classList.remove('open');
    if(!e.target.closest('.item-dd-wrap')) document.querySelectorAll('.item-dd-list').forEach(d=>d.classList.remove('open'));
    if(!e.target.closest('.form-footer')) document.getElementById('shareDropdown')?.classList.remove('open');
    if(!e.target.closest('#formDateWrap')) document.getElementById('calendarPopup')?.classList.remove('open');
    if(!e.target.closest('.cat-dots-wrap')) { closeAllCatMenus(-1); closeAllItemMenus(-1); }
    if(!e.target.closest('.td-action-btn')) document.querySelectorAll('.td-row-menu').forEach(m=>m.classList.remove('open'));
    if(!e.target.closest('.th-filter') && !e.target.closest('.filter-popover')) document.querySelectorAll('.filter-popover').forEach(p=>p.classList.remove('open'));
    if(!e.target.closest('.expense-party-picker')) closeExpensePartyDropdown();
    if(!e.target.closest('.party-group-wrap') && !e.target.closest('#partyGroupModal')) document.getElementById('partyGroupMenu')?.classList.add('d-none');
  });

  function escHtml(str) { const d=document.createElement('div'); d.appendChild(document.createTextNode(String(str))); return d.innerHTML; }

  // ─── VIEW/EDIT ───
  function openViewEdit(expId, catIdx) {
    document.querySelectorAll('.td-row-menu').forEach(m => m.classList.remove('open'));
    const cat   = categories[catIdx];
    const entry = cat ? cat.entries.find(e => e.id === expId) : null;
    if (!entry) return;
    resetForm();
    showPage('expenseFormPage');
    document.getElementById('formCatLabel').textContent = cat.name;
    document.getElementById('formCatSelectBtn').classList.add('filled');
    if (entry.expNo) document.getElementById('formExpNoInput').value = entry.expNo;
    if (entry.date) {
      const parts = entry.date.split('-');
      if (parts.length === 3) {
        calSelDate  = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        calViewDate = new Date(calSelDate);
        setDateDisplay(calSelDate);
        buildCalendar();
      }
    }
    const partyIdEl = document.getElementById('expensePartyId');
    const partySearchEl = document.getElementById('expensePartySearch');
    const partyBalanceEl = document.getElementById('expensePartyBalance');
    if (partyIdEl) partyIdEl.value = entry.party_id || '';
    if (partySearchEl) partySearchEl.value = entry.party || '';
    if (partyBalanceEl && entry.party_id) {
      const party = getExpensePartyById(entry.party_id);
      const balance = parseFloat(party?.current_balance || party?.opening_balance || 0);
      partyBalanceEl.textContent = `Balance: Rs ${formatExpenseMoney(balance)}`;
    }
    const poNoEl = document.getElementById('expensePoNoInput');
    const poDateEl = document.getElementById('expensePoDateInput');
    const timeEl = document.getElementById('expenseTransactionTimeDisplay');
    const paymentTermsEl = document.getElementById('expensePaymentTermsDisplay');
    const dealDaysSelect = document.getElementById('expenseDealDaysSelect');
    const dealDaysCustom = document.getElementById('expenseDealDaysCustom');
    const dueDateEl = document.getElementById('expenseDueDateDisplay');
    if (poNoEl) poNoEl.value = entry.poNo || '';
    if (poDateEl) poDateEl.value = entry.poDate || '';
    if (timeEl) timeEl.value = entry.transactionTime || '';
    if (paymentTermsEl) paymentTermsEl.value = entry.paymentTermsName || getExpenseTransactionSettings()?.payment_terms?.name || 'Net 15';
    const statusSelect = document.getElementById('expenseStatusSelect');
    if (statusSelect) statusSelect.value = getExpenseDerivedStatus(entry.amount, entry.paidAmount);
    if (dealDaysSelect) {
      const dealDaysValue = entry.dealDays !== undefined && entry.dealDays !== null ? String(entry.dealDays) : '';
      const presetValues = ['0', '5', '10', '15', '30', '45'];
      if (dealDaysValue && presetValues.includes(dealDaysValue)) {
        dealDaysSelect.value = dealDaysValue;
        dealDaysCustom?.classList.add('d-none');
      } else if (dealDaysValue && dealDaysValue !== '0') {
        dealDaysSelect.value = 'custom';
        if (dealDaysCustom) {
          dealDaysCustom.value = dealDaysValue;
          dealDaysCustom.classList.remove('d-none');
        }
      } else {
        dealDaysSelect.value = String(getExpenseTransactionSettings()?.payment_terms?.days ?? 0);
        dealDaysCustom?.classList.add('d-none');
      }
    }
    if (dueDateEl) dueDateEl.value = entry.dueDate ? formatExpenseDateDisplay(entry.dueDate) : '';
    updateExpenseDueDateDisplay();
    const taxSwitch = document.getElementById('expenseTaxSwitch');
    if (taxSwitch) taxSwitch.checked = !!entry.taxEnabled;
    const discountPercentEl = document.getElementById('expenseDiscountPercentInput');
    const discountAmountEl = document.getElementById('expenseDiscountAmountInput');
    const summaryTaxRateEl = document.getElementById('expenseSummaryTaxRateSelect');
    const summaryTaxAmountEl = document.getElementById('expenseSummaryTaxAmountInput');
    if (discountPercentEl) discountPercentEl.value = entry.discountPercent || entry.discount_percent || '';
    if (discountAmountEl) discountAmountEl.value = entry.discountAmount || entry.discount_amount || '';
    if (summaryTaxRateEl) summaryTaxRateEl.value = entry.summaryTaxRateId || entry.taxRateId || entry.tax_rate_id || '';
    if (summaryTaxAmountEl) summaryTaxAmountEl.value = entry.summaryTaxAmount || entry.taxAmount || entry.tax_amount || '';
    const descEl = document.getElementById('expenseDescriptionInput');
    if (descEl) descEl.value = entry.description || '';
    setExpenseDescriptionVisible(!!entry.description);
    document.getElementById('formItemsBody').innerHTML = '';
    rowKey = 0;
    if (entry.items && entry.items.length) {
      entry.items.forEach(it => {
        addItemRow();
        const rk = rowKey;
        document.getElementById('itemName_' + rk).value = it.name || '';
        document.getElementById('itemQty_' + rk).value = it.qty || '';
        document.getElementById('itemPrice_' + rk).value = it.price || '';
        const taxSel = document.getElementById('itemTaxRate_' + rk);
        if (taxSel) taxSel.value = it.taxRateId || '';
        calcRow(rk);
      });
    } else {
      addItemRow();
    }
    appendStaticRow();
    loadExpenseEntryIntoState(entry, catIdx);
    paymentRows = tabStates[activeTabN]?.payments
      ? tabStates[activeTabN].payments.map(p => ({ type: p.type || '', amount: p.amount || '', ref: p.ref || '' }))
      : [{
          type: entry.bankAccountId ? `bank:${entry.bankAccountId}` : (entry.paymentType || 'Cheque'),
          amount: entry.paidAmount ?? Math.max((parseFloat(entry.amount || 0) - parseFloat(entry.balance || 0)), 0),
          ref: entry.reference_no || ''
        }];
    renderPaymentCard();
    applyExpenseFeatureVisibility();
    calcTotals();
  }

  // ─── NUMBER TO WORDS ───
  function numberToWords(num) {
    if (num === 0) return 'Zero';
    const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    function convert(n) {
      if (n < 20) return ones[n];
      if (n < 100) return tens[Math.floor(n/10)] + (n%10 ? ' '+ones[n%10] : '');
      if (n < 1000) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' '+convert(n%100) : '');
      if (n < 100000) return convert(Math.floor(n/1000)) + ' Thousand' + (n%1000 ? ' '+convert(n%1000) : '');
      return convert(Math.floor(n/100000)) + ' Lakh' + (n%100000 ? ' '+convert(n%100000) : '');
    }
    return convert(Math.floor(num)) + ' Rupees Only';
  }
  
  </script>

</body>
</html>
