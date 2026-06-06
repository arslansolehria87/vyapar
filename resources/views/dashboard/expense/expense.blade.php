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

  <script>
    function removePaymentRow(i) {
  paymentRows.splice(i, 1);
  renderPaymentCard();
}

function openAddBankModal() {
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
            <input id="bankAsOfDate" type="text" value="${new Date().toLocaleDateString('en-GB')}"
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
  const name = document.getElementById('bankAccName')?.value.trim();
  if (!name) { showToast('Account Display Name is required.', 'red'); return; }
  showToast('Bank account added successfully.', 'green');
  closeModal('addBankModal');
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
      expenseSave:     "{{ route('expense.save') }}",
      expenseDestroy:  "{{ url('dashboard/expense') }}",
    };
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
    .slc-left { display: flex; align-items: center; gap: 4px; }
    .category-list { flex: 1; overflow-y: auto; }
    .category-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background .1s; }
    .category-item:hover { background: #f8f9ff; }
    .category-item.active { background: #eef2ff; }
    .cat-name { font-size: 13px; color: #333; }
    .cat-right { display: flex; align-items: center; gap: 8px; }
    .cat-amount { font-size: 13px; color: #333; min-width: 24px; text-align: right; }
    .cat-dots-wrap { position: relative; }
    .cat-dots-btn { background: none; border: none; cursor: pointer; color: #aaa; font-size: 13px; padding: 2px 4px; }
    .cat-dots-btn:hover { color: #555; }
    .cat-dots-menu { position: absolute; right: 0; top: 22px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.12); z-index: 200; min-width: 120px; display: none; }
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
    .detail-table-wrap { flex: 1; overflow-y: auto; }
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
    .td-row-menu { position: absolute; right: 0; top: 24px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,.14); z-index: 300; min-width: 150px; display: none; }
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
    .form-items-wrap { border: 1px solid #e0e0e0; border-radius: 6px 6px 0 0; overflow: hidden; }
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
    .item-dd-list { position: absolute; top: 34px; left: 0; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.1); z-index: 200; min-width: 220px; display: none; }
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

    /* MODALS */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 1100; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 10px; padding: 28px 28px 24px; width: 400px; max-width: 95vw; position: relative; box-shadow: 0 8px 32px rgba(0,0,0,.18); }
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
              <i class="bi bi-arrow-up" style="font-size:10px;"></i>
            </div>
            <span>AMOUNT</span>
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
                <th><span class="th-wrap">DATE<span class="th-sort" onclick="sortItemsTable('date')" id="isort_date"><span class="sa-up" id="isort_date_up">&#9650;</span><span class="sa-dn" id="isort_date_dn">&#9660;</span></span></span></th>
                <th><span class="th-wrap">EXP NO.<span class="th-sort" onclick="sortItemsTable('expNo')" id="isort_expNo"><span class="sa-up" id="isort_expNo_up">&#9650;</span><span class="sa-dn" id="isort_expNo_dn">&#9660;</span></span></span></th>
                <th><span class="th-wrap">PARTY<span class="th-sort" onclick="sortItemsTable('party')" id="isort_party"><span class="sa-up" id="isort_party_up">&#9650;</span><span class="sa-dn" id="isort_party_dn">&#9660;</span></span></span></th>
                <th><span class="th-wrap">PAYMENT TYPE<span class="th-sort" onclick="sortItemsTable('paymentType')" id="isort_paymentType"><span class="sa-up" id="isort_paymentType_up">&#9650;</span><span class="sa-dn" id="isort_paymentType_dn">&#9660;</span></span></span></th>
                <th><span class="th-wrap">AMOUNT<span class="th-sort" onclick="sortItemsTable('amount')" id="isort_amount"><span class="sa-up" id="isort_amount_up">&#9650;</span><span class="sa-dn" id="isort_amount_dn">&#9660;</span></span></span></th>
                <th><span class="th-wrap">BALANCE<span class="th-sort" onclick="sortItemsTable('balance')" id="isort_balance"><span class="sa-up" id="isort_balance_up">&#9650;</span><span class="sa-dn" id="isort_balance_dn">&#9660;</span></span></span></th>
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

    {{-- EXPENSE FORM — now full-screen fixed overlay --}}
    <div id="expenseFormPage">
      <div style="display:flex; align-items:center; background:#fff; border-bottom:1px solid #e0e0e0; flex-shrink:0;">
        <div class="form-tabs-bar" id="formTabsBar" style="flex:1; border-bottom:none;"></div>
        <button onclick="tryCloseEntireForm()" style="background:none; border:none; cursor:pointer; color:#555; font-size:20px; padding:0 16px; line-height:1; flex-shrink:0; margin-left:auto;" title="Close">&#x2715;</button>
      </div>
      <div class="form-body">
        <div class="form-title">Expense</div>
        <div class="form-top-row">
          <div class="form-cat-wrap" id="formCatWrap">
            <div class="form-cat-select" id="formCatSelectBtn" onclick="toggleCatDropdown(event)">
              <span class="form-cat-label">Expense Category*</span>
              <span id="formCatLabel"></span>
              <i class="bi bi-chevron-down" style="font-size:11px;color:#555;"></i>
            </div>
            <div class="form-cat-dropdown" id="formCatDropdown">
              <div class="cat-dd-add-row" onclick="openAddCatModal()">
                <i class="bi bi-plus-circle-fill text-primary"></i> Add Expense Category
              </div>
              <div id="formCatOptions"></div>
            </div>
          </div>
          <div class="form-date-wrap" id="formDateWrap">
            <div class="form-exp-no-row">
              <span class="form-exp-no-label">Expense No</span>
              <input type="text" class="form-exp-no-input" id="formExpNoInput" placeholder="">
            </div>
            <div class="form-date-row">
              <span>Date</span>
              <span class="form-date-val" id="formDateVal"></span>
              <span class="form-date-icon" onclick="toggleCalendar(event)"><i class="bi bi-calendar3"></i></span>
            </div>
            <div class="calendar-popup" id="calendarPopup">
              <div class="cal-header">
                <button class="cal-nav" onclick="calNav(-1)">&#9664;</button>
                <span id="calMonthLabel"></span>
                <button class="cal-nav" onclick="calNav(1)">&#9654;</button>
              </div>
              <div class="cal-grid" id="calGrid"></div>
            </div>
          </div>
        </div>
        <div class="form-items-wrap">
          <table class="form-items-table">
            <thead><tr>
              <th class="col-hash">#</th>
              <th class="col-item">ITEM</th>
              <th class="col-qty">QTY</th>
              <th class="col-price">PRICE/UNIT</th>
              <th class="col-amount">AMOUNT</th>
            </tr></thead>
            <tbody id="formItemsBody"></tbody>
          </table>
        </div>
        <div class="items-footer-bar">
          <button class="btn-add-row" onclick="addItemRow()">ADD ROW</button>
          <div class="items-total-label">
            TOTAL &nbsp;&nbsp; <span id="formQtyTotal">0</span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span id="formAmtTotal">0</span>
          </div>
        </div>
        <div class="payment-section">
          <div id="paymentCard"></div>
          <div class="total-block">
            <div class="round-off-wrap">
              <input type="checkbox" id="roundOffChk" onchange="calcTotals()">
              <label for="roundOffChk" style="font-size:13px;cursor:pointer;">Round Off</label>
              <input type="text" class="round-val" id="roundOffVal" value="0" readonly>
            </div>
            <div class="total-field-wrap">
              <span class="total-field-label">Total</span>
              <div class="total-box" id="formTotalBox"></div>
            </div>
          </div>
        </div>
        <div class="form-extra-btns">
          <button class="form-extra-btn"><i class="bi bi-file-earmark-text"></i> ADD DESCRIPTION</button>
        </div>
      </div>
      <div class="form-footer">
        <div class="share-btn-group">
          <button class="btn-share-main" onclick="toggleShareDropdown()">Share</button>
          <button class="btn-share-caret" onclick="toggleShareDropdown()"><i class="bi bi-chevron-down"></i></button>
        </div>
        <button class="btn-save" id="btnSaveExpense" onclick="saveExpense()">Save</button>
        <div class="share-dropdown" id="shareDropdown">
          <div class="share-dd-item"><i class="bi bi-share"></i> Share</div>
          <div class="share-dd-item"><i class="bi bi-printer"></i> Print</div>
          <div class="share-dd-item"><i class="bi bi-plus-square"></i> Save &amp; New</div>
        </div>
      </div>
    </div>

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
    <div class="modal-box">
      <button class="modal-close" onclick="closeModal('addItemModal')">&#x2715;</button>
      <div class="modal-title">Add Expense Item</div>
      <div class="modal-field">
        <label>Item Name *</label>
        <input type="text" id="newItemName" onkeydown="if(event.key==='Enter') saveNewItem()">
      </div>
      <span class="item-pricing-label">Pricing</span>
      <input type="text" class="item-price-field" id="newItemPrice" placeholder="Price">
      <div class="modal-actions" style="margin-top:16px;">
        <button class="btn-save-modal" onclick="saveNewItem()">Save</button>
      </div>
    </div>
  </div>

  {{-- MODAL: Edit Item --}}
  <div class="modal-overlay" id="editItemModal">
    <div class="modal-box">
      <button class="modal-close" onclick="closeModal('editItemModal')">&#x2715;</button>
      <div class="modal-title">Edit Expense Item</div>
      <div class="modal-field">
        <label>Item Name *</label>
        <input type="text" id="editItemName">
      </div>
      <span class="item-pricing-label">Pricing</span>
      <input type="text" class="item-price-field" id="editItemPrice" placeholder="Price">
      <div class="modal-actions-split">
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
  let selectedCatIdx  = 0;
  let selectedItemIdx = 0;
  let editingItemIdx  = -1;
  let currentTab      = 'category';
  let rowKey          = 0;
  let tabCounter      = 0;
  let paymentRows     = [];
  let pendingConfirmCb = null;
  let calViewDate = new Date();
  let calSelDate  = new Date();
  let closingTabN = null;

  // ── Per-tab state storage ──
  const tabStates = {};
  let activeTabN  = null;

  function defaultTabState() {
    return {
      catName  : '',
      expNo    : '',
      date     : new Date(),
      items    : [],
      payments : [{ type: 'Cheque', ref: '' }],
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

  // ── Sort state ──
  let detailSortCol = 'amount';
  let detailSortDir = 'desc';

  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // ─── Ajax helper ───
  function ajax(method, url, data) {
    return fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: data ? JSON.stringify(data) : undefined,
    }).then(r => r.json());
  }

  function tryCloseEntireForm() {
    closingTabN = 'all';
    document.getElementById('closeExpenseOverlay').classList.add('open');
  }

  // ═══════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════
  document.addEventListener('DOMContentLoaded', function () {
    setDateDisplay(calSelDate);
    buildCalendar();
    if (categories.length > 0) showPage('splitPane');
    else                        showPage('emptyState');
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
      document.getElementById('categoryDetailPanel').style.display = 'flex';
      document.getElementById('itemsDetailPanel').style.display    = 'none';
      renderCategoryList(); renderDetailPanel();
    } else {
      document.getElementById('slcLabel').textContent = 'ITEM';
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
    ['date','expNo','party','paymentType','amount','balance'].forEach(c => {
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
      if (col === 'date') {
        av = av ? new Date(av).getTime() : 0;
        bv = bv ? new Date(bv).getTime() : 0;
        return (av - bv) * dir;
      }
      return String(av).localeCompare(String(bv)) * dir;
    });
  }

  function sortItemsTable(col) {
    ['date','expNo','party','paymentType','amount','balance'].forEach(c => {
      const upEl = document.getElementById('isort_'+c+'_up');
      const dnEl = document.getElementById('isort_'+c+'_dn');
      if (upEl) upEl.classList.remove('active');
      if (dnEl) dnEl.classList.remove('active');
    });
    const upEl = document.getElementById('isort_'+col+'_up');
    const dnEl = document.getElementById('isort_'+col+'_dn');
    if (upEl) upEl.classList.add('active');
    if (dnEl) dnEl.classList.add('active');
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
    renderDetailPanel();
  }
  function applyFilterPop(id, col) { closeFilterPop(id); renderDetailPanel(); }

  // ═══════════════════════════════════════════════════════
  //  CATEGORY LIST
  // ═══════════════════════════════════════════════════════
  function renderCategoryList() {
    const ul = document.getElementById('categoryList');
    ul.innerHTML = '';
    if (currentTab === 'category') {
      if (!categories.length) {
        ul.innerHTML = '<div style="padding:24px;text-align:center;color:#aaa;font-size:13px;">No categories yet.</div>';
        return;
      }
      categories.forEach((c, i) => {
        const div = document.createElement('div');
        div.className = 'category-item' + (i === selectedCatIdx ? ' active' : '');
        div.innerHTML = `
          <span class="cat-name">${escHtml(c.name)}</span>
          <div class="cat-right">
            <span class="cat-amount">Rs ${parseFloat(c.amount||0).toFixed(2)}</span>
            <div class="cat-dots-wrap">
              <button class="cat-dots-btn" onclick="toggleCatMenu(event,${i})"><i class="fa-solid fa-ellipsis-vertical"></i></button>
              <div class="cat-dots-menu" id="catMenu_${i}">
                <div class="cat-dots-item" onclick="openEditCatModal(${i})">Edit</div>
                <div class="cat-dots-item danger" onclick="deleteCategoryPrompt(${i})">Delete</div>
              </div>
            </div>
          </div>`;
        div.addEventListener('click', e => {
          if (e.target.closest('.cat-dots-wrap')) return;
          selectedCatIdx = i; renderCategoryList(); renderDetailPanel();
        });
        ul.appendChild(div);
      });
    } else {
      if (!expenseItems.length) {
        ul.innerHTML = '<div style="padding:24px;text-align:center;color:#aaa;font-size:13px;">No items yet.</div>';
        return;
      }
      expenseItems.forEach((it, i) => {
        const div = document.createElement('div');
        div.className = 'category-item' + (i === selectedItemIdx ? ' active' : '');
        div.innerHTML = `
          <span class="cat-name">${escHtml(it.name)}</span>
          <div class="cat-right">
            <span class="cat-amount">Rs ${parseFloat(it.price||0).toFixed(2)}</span>
            <div class="cat-dots-wrap">
              <button class="cat-dots-btn" onclick="toggleItemMenu(event,${i})"><i class="fa-solid fa-ellipsis-vertical"></i></button>
              <div class="cat-dots-menu" id="itemMenu_${i}">
                <div class="cat-dots-item" onclick="openEditItemModal(${i})">Edit</div>
                <div class="cat-dots-item danger" onclick="deleteItemPrompt(${i})">Delete</div>
              </div>
            </div>
          </div>`;
        div.addEventListener('click', e => {
          if (e.target.closest('.cat-dots-wrap')) return;
          selectedItemIdx = i; renderCategoryList(); renderItemDetailPanel(i);
        });
        ul.appendChild(div);
      });
    }
  }

  function toggleCatMenu(e, i) { e.stopPropagation(); closeAllCatMenus(i); document.getElementById('catMenu_'+i)?.classList.toggle('open'); }
  function closeAllCatMenus(except) { document.querySelectorAll('[id^="catMenu_"]').forEach((m,idx) => { if(idx!==except) m.classList.remove('open'); }); }
  function toggleItemMenu(e, i) { e.stopPropagation(); closeAllItemMenus(i); document.getElementById('itemMenu_'+i)?.classList.toggle('open'); }
  function closeAllItemMenus(except) { document.querySelectorAll('[id^="itemMenu_"]').forEach((m,idx) => { if(idx!==except) m.classList.remove('open'); }); }

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
      document.getElementById('detailTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">No expenses yet.</td></tr>';
      return;
    }
    document.getElementById('detailTitle').textContent   = c.name.toUpperCase();
    document.getElementById('detailType').textContent    = c.type || '';
    document.getElementById('detailTotal').textContent   = 'Rs ' + parseFloat(c.amount||0).toFixed(2);
    document.getElementById('detailBalance').textContent = 'Rs 0.00';
    const tbody = document.getElementById('detailTableBody');
    tbody.innerHTML = '';
    if (!c.entries || !c.entries.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">No expenses yet.</td></tr>';
      return;
    }
    const sorted = getSortedEntries(c.entries);
    sorted.forEach((e, ei) => {
      const tr = document.createElement('tr');
      if (ei === 0) tr.classList.add('row-highlight');
      tr.innerHTML = `
          <td>${escHtml(e.date||'')}</td>
          <td>${escHtml(e.expNo||'')}</td>
          <td>${escHtml(e.party||'')}</td>
          <td>${escHtml(e.paymentType||'')}</td>
          <td style="font-weight:500;">${parseFloat(e.amount||0).toFixed(2)}</td>
          <td>${parseFloat(e.balance||0).toFixed(2)}</td>
          <td style="position:relative; width:40px; text-align:center;">
              <button class="td-action-btn" onclick="toggleRowMenu(event,${e.id})">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <div class="td-row-menu" id="rowMenu_${e.id}">
                  <div class="td-row-menu-item" onclick="openViewEdit(${e.id},${selectedCatIdx})">View/Edit</div>
                  <div class="td-row-menu-item danger" onclick="deleteExpenseRow(${e.id},${selectedCatIdx})">Delete</div>
                  <div class="td-row-menu-item" onclick="duplicateExpenseRow(${e.id},${selectedCatIdx})">Duplicate</div>
                  <div class="td-row-menu-item" onclick="openPrintView(${e.id},${selectedCatIdx})">Print</div>
                  <div class="td-row-menu-item" onclick="openPreview(${e.id},${selectedCatIdx})">Preview</div>
                  <div class="td-row-menu-item" onclick="openDirectPDF(${e.id},${selectedCatIdx})">Open PDF</div>
                  <div class="td-row-menu-item" onclick="openViewHistory(${e.id},${selectedCatIdx})">View History</div>
              </div>
          </td>`;
      tbody.appendChild(tr);
    });
  }

  function toggleRowMenu(e, id) {
    e.stopPropagation();
    document.querySelectorAll('.td-row-menu').forEach(m => { if(m.id !== 'rowMenu_'+id) m.classList.remove('open'); });
    document.getElementById('rowMenu_'+id)?.classList.toggle('open');
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

    // Open the expense form pre-filled with the entry data
    // but WITHOUT _editingExpenseId so Save creates a new record
    resetForm();
    showPage('expenseFormPage');

    // Pre-fill category
    document.getElementById('formCatLabel').textContent = cat.name;
    document.getElementById('formCatSelectBtn').classList.add('filled');

    // Pre-fill expense no (blank — user can set their own)
    document.getElementById('formExpNoInput').value = '';

    // Pre-fill date from original entry
    if (entry.date) {
      const parts = entry.date.split('-');
      if (parts.length === 3) {
        calSelDate  = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        calViewDate = new Date(calSelDate);
        setDateDisplay(calSelDate);
        buildCalendar();
      }
    }

    // Pre-fill payment
    paymentRows = [{ type: entry.paymentType || 'Cheque', amount: '', ref: entry.reference_no || '' }];}
  function renderPaymentCard() {
    const card = document.getElementById('paymentCard'); card.innerHTML = '';
    paymentRows.forEach((row, i) => {
      const wrap = document.createElement('div'); wrap.className = 'payment-row-wrap';
      const pr = document.createElement('div'); pr.className = 'payment-row';
      pr.innerHTML = `
        <div class="payment-field" style="position:relative;">
          <span class="payment-field-label" style="position:absolute;top:6px;left:12px;font-size:10px;color:#555;z-index:1;">Payment Type</span>
          <select class="payment-type-select" onchange="payRowChange(${i},'type',this.value)" style="border:1px solid #aaa;border-radius:6px;padding:20px 30px 8px 12px;font-size:13px;min-width:200px;min-height:54px;cursor:pointer;color:#1a1f36;background:#fff;outline:none;appearance:none;width:100%;">
            <option value="add_bank" style="color:#2563eb;">+ Add Bank A/C</option>
            <option value="" ${!row.type?'selected':''}>Select Type</option>
            <option value="Cash" ${row.type==='Cash'?'selected':''}>Cash</option>
            <option value="Cheque" ${row.type==='Cheque'?'selected':''}>Cheque</option>
            <option value="UPI" ${row.type==='UPI'?'selected':''}>UPI</option>
            <option value="Card" ${row.type==='Card'?'selected':''}>Card</option>
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
      ref.type='text'; ref.placeholder='Reference No.'; ref.value=row.ref||'';
      ref.style.cssText='border:1px solid #bbb;border-radius:6px;padding:10px 12px;font-size:13px;width:200px;outline:none;margin-top:8px;display:block;background:#fff;';
      ref.oninput = ev => payRowChange(i, 'ref', ev.target.value);
      wrap.appendChild(ref);
      card.appendChild(wrap);

      // detect "Add Bank A/C" selection
      const sel = pr.querySelector('select');
      sel.addEventListener('change', function() {
        if (this.value === 'add_bank') {
          this.value = row.type || '';
          openAddBankModal();
        }
      });
    });

    const footer = document.createElement('div');
    footer.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-top:10px;';
    const total = paymentRows.reduce((s,r) => s + (parseFloat(r.amount)||0), 0);
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
              Date: &nbsp;<strong>${escHtml(formatDisplayDate(entry.date))}</strong>
              ${entry.expNo ? '<br>Exp No: ' + escHtml(entry.expNo) : ''}
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
            <td style="padding:9px 16px;border:1px solid #ccc;text-align:right;font-size:12px;">Rs ${parseFloat(entry.amount||0).toFixed(2)}</td>
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
    <td><strong style="display:block;color:#555;font-size:11px;">EXPENSE DETAILS:</strong>Date: ${escHtml(formatDisplayDate(entry.date))}${entry.expNo ? '<br>Exp No: ' + escHtml(entry.expNo) : ''}</td>
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
    <tr><td>Paid</td><td>:</td><td>Rs ${parseFloat(entry.amount||0).toFixed(2)}</td></tr>
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
  function renderItemDetailPanel(i) {
    const it = expenseItems[i];
    if (!it) return;
    document.getElementById('itemsDetailTitle').textContent   = it.name.toUpperCase();
    document.getElementById('itemsDetailTotal').textContent   = 'Rs ' + parseFloat(it.price||0).toFixed(2);
    document.getElementById('itemsDetailBalance').textContent = 'Rs 0.00';
    document.getElementById('itemsDetailTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">No transactions to show</td></tr>';
  }

  // ─── EDIT ITEM ───
  function openEditItemModal(i) {
    closeAllItemMenus(-1); editingItemIdx = i;
    const it = expenseItems[i];
    document.getElementById('editItemName').value  = it.name;
    document.getElementById('editItemPrice').value = it.price || '';
    openModal('editItemModal');
    setTimeout(() => document.getElementById('editItemName').focus(), 80);
  }
  function saveEditItem() {
    const name  = document.getElementById('editItemName').value.trim();
    const price = parseFloat(document.getElementById('editItemPrice').value) || 0;
    if (!name) { showToast('Item name cannot be empty.', 'red'); return; }
    const it = expenseItems[editingItemIdx];
    ajax('PUT', window.expenseRoutes.itemUpdate + '/' + it.id, { name, price }).then(res => {
      if (res.success) {
        expenseItems[editingItemIdx].name  = res.item.name;
        expenseItems[editingItemIdx].price = res.item.price;
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
  function openExpenseForm() { resetForm(); showPage('expenseFormPage'); }

  function resetForm() {
    document.getElementById('formCatLabel').textContent = '';
    document.getElementById('formCatSelectBtn').classList.remove('filled');
    calSelDate = new Date(); setDateDisplay(calSelDate); calViewDate = new Date(); buildCalendar();
    document.getElementById('formExpNoInput').value = '';
    rowKey = 0;
    document.getElementById('formItemsBody').innerHTML = '';
    addItemRow(); appendStaticRow(); calcTotals();
    paymentRows = [{ type: 'Cheque', amount: '', ref: '' }];
    renderPaymentCard();
    tabCounter = 1; renderFormTabs(1); renderFormCatOptions();
    window._editingExpenseId = null;
    window._editingCatIdx    = null;
  }

  function saveTabState() {
    if (!activeTabN) return;
    const s = tabStates[activeTabN] || defaultTabState();
    s.catName  = document.getElementById('formCatLabel')?.textContent?.trim() || '';
    s.expNo    = document.getElementById('formExpNoInput')?.value || '';
    s.date     = calSelDate ? new Date(calSelDate) : new Date();
    s.roundOff = document.getElementById('roundOffChk')?.checked || false;
    s.editingExpenseId = window._editingExpenseId || null;
    s.editingCatIdx    = window._editingCatIdx    || null;
    s.items = [];
    document.querySelectorAll('[id^="itemRow_"]').forEach(tr => {
      const rk = tr.id.replace('itemRow_', '');
      s.items.push({
        rk,
        name  : document.getElementById('itemName_'  + rk)?.value || '',
        qty   : document.getElementById('itemQty_'   + rk)?.value || '',
        price : document.getElementById('itemPrice_' + rk)?.value || '',
      });
    });
    s.payments = paymentRows.map(p => ({ type: p.type || '', ref: p.ref || '' }));
    tabStates[activeTabN] = s;
  }

  function restoreTabState(tabN) {
    const s = tabStates[tabN] || defaultTabState();
    const catLbl = document.getElementById('formCatLabel');
    const catBtn = document.getElementById('formCatSelectBtn');
    if (catLbl) catLbl.textContent = s.catName || '';
    if (catBtn) catBtn.classList.toggle('filled', !!(s.catName));
    const expNoEl = document.getElementById('formExpNoInput');
    if (expNoEl) expNoEl.value = s.expNo || '';
    calSelDate  = s.date ? new Date(s.date) : new Date();
    calViewDate = new Date(calSelDate);
    setDateDisplay(calSelDate);
    buildCalendar();
    const chkEl = document.getElementById('roundOffChk');
    if (chkEl) chkEl.checked = !!s.roundOff;
    rowKey = 0;
    document.getElementById('formItemsBody').innerHTML = '';
    if (s.items && s.items.length > 0) {
      s.items.forEach(it => {
        addItemRow();
        const rk = rowKey;
        const nameEl  = document.getElementById('itemName_'  + rk);
        const qtyEl   = document.getElementById('itemQty_'   + rk);
        const priceEl = document.getElementById('itemPrice_' + rk);
        if (nameEl)  nameEl.value  = it.name  || '';
        if (qtyEl)   qtyEl.value   = it.qty   || '';
        if (priceEl) priceEl.value = it.price || '';
        calcRow(rk);
      });
    } else {
      addItemRow();
    }
    appendStaticRow();
    paymentRows = (s.payments && s.payments.length)
      ? s.payments.map(p => ({ type: p.type || 'Cheque', ref: p.ref || '', amount: '' }))
      : [{ type: 'Cheque', ref: '', amount: '' }];
    renderPaymentCard();
    window._editingExpenseId = s.editingExpenseId || null;
    window._editingCatIdx    = s.editingCatIdx    || null;
    calcTotals();
    activeTabN = tabN;
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
    const body = document.getElementById('formItemsBody');
    document.getElementById('staticRow2')?.remove();
    const tr2 = document.createElement('tr'); tr2.id = 'staticRow2';
    tr2.innerHTML = '<td style="text-align:center;color:#555;font-size:13px;">2</td><td></td><td></td><td></td><td></td>';
    body.appendChild(tr2);
  }
  function addItemRow() {
    rowKey++;
    const body = document.getElementById('formItemsBody');
    document.getElementById('staticRow2')?.remove();
    const tr = document.createElement('tr'); tr.id = 'itemRow_' + rowKey;
    const rk = rowKey;
    tr.innerHTML = `
      <td style="text-align:center;color:#555;font-size:13px;padding:6px 8px;">${rowKey}</td>
      <td class="item-dd-wrap">
        <input type="text" id="itemName_${rk}" onfocus="showItemDropdown(${rk})" oninput="filterItemDropdown(${rk})" autocomplete="off" style="width:100%;">
        <div class="item-dd-list" id="itemDd_${rk}">
          <div class="item-dd-add-row" onclick="openAddItemModal()"><i class="bi bi-plus-circle-fill text-primary"></i> Add Expense Item</div>
          <div id="itemDdOpts_${rk}"></div>
        </div>
      </td>
      <td><input type="number" id="itemQty_${rk}" min="0" oninput="calcRow(${rk})"></td>
      <td><input type="number" id="itemPrice_${rk}" min="0" oninput="calcRow(${rk})"></td>
      <td><input type="number" id="itemAmt_${rk}" readonly></td>`;
    body.appendChild(tr);
    renderItemDdOptions(rk);
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
    const qty   = parseFloat(document.getElementById('itemQty_'+rk)?.value)||0;
    const price = parseFloat(document.getElementById('itemPrice_'+rk)?.value)||0;
    const amtEl = document.getElementById('itemAmt_'+rk);
    if (amtEl) amtEl.value = (qty && price) ? (qty*price) : '';
    calcTotals();
  }
  function calcTotals() {
    let tQ=0, tA=0;
    document.querySelectorAll('[id^="itemQty_"]').forEach(el => { tQ += parseFloat(el.value)||0; });
    document.querySelectorAll('[id^="itemAmt_"]').forEach(el => { tA += parseFloat(el.value)||0; });
    document.getElementById('formQtyTotal').textContent = tQ||0;
    document.getElementById('formAmtTotal').textContent = tA||0;
    const rounded = Math.round(tA);
    const chk = document.getElementById('roundOffChk');
    document.getElementById('roundOffVal').value = tA ? (rounded-tA).toFixed(2) : '0';
    document.getElementById('formTotalBox').textContent = tA ? (chk&&chk.checked ? rounded : tA.toFixed(2)) : '';
    const ptEl = document.getElementById('payTotalText');  // ← NEW block starts here
    if (ptEl) {
        const payTotal = paymentRows.reduce((s,r) => s + (parseFloat(r.amount)||0), 0);
        ptEl.textContent = 'Total payment: ' + payTotal + '/' + (tA||0);
    }
}  // ← closing brace stays here
  // ─── ADD ITEM MODAL ───
  function openAddItemModal() {
    document.querySelectorAll('.item-dd-list').forEach(d => d.classList.remove('open'));
    document.getElementById('newItemName').value  = '';
    document.getElementById('newItemPrice').value = '';
    openModal('addItemModal');
    setTimeout(() => document.getElementById('newItemName').focus(), 80);
  }
  function saveNewItem() {
    const name  = document.getElementById('newItemName').value.trim();
    const price = parseFloat(document.getElementById('newItemPrice').value) || 0;
    if (!name) { showToast('Item name cannot be empty.', 'red'); return; }
    const saveBtn = document.querySelector('#addItemModal .btn-save-modal');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving...'; }
    const resetBtn = () => { if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Save'; } };
    const addItemLocally = (item) => {
  expenseItems.push(item);
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
    if (priceEl) priceEl.value = item.price || '';
    if (qtyEl)   qtyEl.value   = 1;
    document.getElementById('itemDd_' + rk)?.classList.remove('open');
    calcRow(rk);
  }
  if (currentTab === 'items') renderCategoryList();
  showToast('Item saved successfully.', 'green');
};
    ajax('POST', window.expenseRoutes.itemStore, { name, price })
      .then(res => {
        resetBtn();
        if (res.success && res.item) addItemLocally(res.item);
        else addItemLocally({ id: 'local_' + Date.now(), name, price });
      })
      .catch(() => { resetBtn(); addItemLocally({ id: 'local_' + Date.now(), name, price }); });
  }

 
  function payRowChange(i,field,val) { paymentRows[i][field]=val; }
  function addPaymentRow() { 
  paymentRows.push({type:'', amount:0, ref:''}); 
  renderPaymentCard(); 
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
    const total   = parseFloat(document.getElementById('formAmtTotal').textContent) || 0;
    const payType = paymentRows[0]?.type || 'Cash';
    const ref     = paymentRows[0]?.ref  || '';
    const dateVal = document.getElementById('formDateVal').textContent;
    const expNo   = document.getElementById('formExpNoInput').value.trim();
    const parts   = dateVal.split('/');
    const dbDate  = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateVal;
    const btn     = document.getElementById('btnSaveExpense');
    btn.disabled = true; btn.textContent = 'Saving...';
    ajax('POST', window.expenseRoutes.expenseSave, {
      expense_category_id: cat.id,
      expense_no:   expNo,
      expense_date: dbDate,
      total_amount: total,
      payment_type: payType,
      reference_no: ref,
    }).then(res => {
      btn.disabled = false; btn.textContent = 'Save';
      if (res.success) {
        cat.amount = (parseFloat(cat.amount)||0) + total;
        cat.entries = cat.entries || [];
        cat.entries.unshift(res.expense);
        selectedCatIdx = categories.indexOf(cat);
        showPage('splitPane');
        showToast('Expense saved successfully.', 'green');
      } else {
        showToast('Save failed.', 'red');
      }
    }).catch(() => { btn.disabled=false; btn.textContent='Save'; showToast('Save failed.', 'red'); });
  }

  // ═══════════════════════════════════════════════════════
  //  CALENDAR
  // ═══════════════════════════════════════════════════════
  function setDateDisplay(d) {
    const dd=String(d.getDate()).padStart(2,'0'), mm=String(d.getMonth()+1).padStart(2,'0');
    document.getElementById('formDateVal').textContent = dd+'/'+mm+'/'+d.getFullYear();
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
    if(!e.target.closest('#formCatWrap')) document.getElementById('formCatDropdown')?.classList.remove('open');
    if(!e.target.closest('.item-dd-wrap')) document.querySelectorAll('.item-dd-list').forEach(d=>d.classList.remove('open'));
    if(!e.target.closest('.form-footer')) document.getElementById('shareDropdown')?.classList.remove('open');
    if(!e.target.closest('#formDateWrap')) document.getElementById('calendarPopup')?.classList.remove('open');
    if(!e.target.closest('.cat-dots-wrap')) { closeAllCatMenus(-1); closeAllItemMenus(-1); }
    if(!e.target.closest('.td-action-btn')) document.querySelectorAll('.td-row-menu').forEach(m=>m.classList.remove('open'));
    if(!e.target.closest('.th-filter') && !e.target.closest('.filter-popover')) document.querySelectorAll('.filter-popover').forEach(p=>p.classList.remove('open'));
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
    paymentRows = [{ type: entry.paymentType || 'Cheque', amount: entry.amount || '', ref: entry.reference_no || '' }];
    renderPaymentCard();
    window._editingExpenseId = expId;
    window._editingCatIdx    = catIdx;
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
