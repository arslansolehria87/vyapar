<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Party Payment Reminders</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
  <script>
    const authUser = @json(Auth::user());
    window.App = window.App || {
      isAuthenticated: @json(Auth::check()),
      user: authUser ? {
        id: authUser.id,
        name: authUser.name,
        roles: @json(collect(Auth::user()?->roles ?? [])->pluck('name')->toArray()),
        permissions: @json(collect(Auth::user()?->getAllPermissions() ?? [])->pluck('name')->toArray()),
      } : { id: null, name: null, roles: [], permissions: [] },
      logoutUrl: "{{ route('logout') }}",
      csrfToken: "{{ csrf_token() }}",
    };
  </script>

  <style>
    body {
      background: #f6f8fc;
    }
    .reminder-shell {
      padding: 20px 24px 28px;
    }
    .reminder-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
      overflow: hidden;
    }
    .reminder-toolbar {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
      padding: 16px 18px 0;
    }
    .reminder-title {
      font-size: 22px;
      font-weight: 700;
      margin: 0;
    }
    .reminder-subtitle {
      color: #64748b;
      font-size: 13px;
      margin-top: 4px;
    }
    .reminder-table-wrap {
      padding: 16px 18px 20px;
    }
    .reminder-table td,
    .reminder-table th {
      vertical-align: middle;
    }
    .reminder-table th {
      font-size: 13px;
      color: #475569;
      font-weight: 600;
      white-space: nowrap;
    }
    .reminder-table td {
      font-size: 14px;
    }
    .reminder-bulk-btn {
      display: none;
      align-items: center;
      gap: 8px;
    }
    .whatsapp-btn {
      min-width: 42px;
    }
    .reminder-message-preview {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      background: #f8fafc;
      padding: 12px 14px;
      font-size: 13px;
      color: #334155;
      white-space: pre-wrap;
    }
    .table-check {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
  </style>
</head>
<body data-page="party-reminders">

  <!-- Navbar & Sidebar injected by components.js -->

  <main class="main-content">
    <div class="reminder-shell">
      <div class="reminder-card">
        <div class="p-4 pb-0">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
              <h1 class="reminder-title">Payment Reminder</h1>
              <div class="reminder-subtitle">Send WhatsApp reminders to parties with pending balances.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <a href="{{ route('settings.parties') }}" class="btn btn-outline-secondary">Back to Party Settings</a>
              <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentReminderMessageModal">
                Edit Reminder Message
              </button>
              <button type="button" class="btn reminder-bulk-btn" id="bulkReminderBtn" style="background:#25D366; border-color:#25D366; color:#fff;">
                <i class="fab fa-whatsapp me-1"></i>
                <span id="bulkReminderBtnLabel">Bulk Party Reminder</span>
              </button>
            </div>
          </div>

          <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
            <div class="input-group" style="max-width: 420px;">
              <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" id="paymentReminderPartySearch" placeholder="Search party">
            </div>
          </div>
        </div>

        <div class="reminder-table-wrap">
          <div class="table-responsive">
            <table class="table table-hover align-middle reminder-table mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 48px;">
                    <input type="checkbox" id="selectAllReminders" class="table-check">
                  </th>
                  <th style="width: 60px;">#</th>
                  <th>Party</th>
                  <th class="text-end">Amount</th>
                  <th class="text-end" style="width: 150px;">Reminder</th>
                </tr>
              </thead>
              <tbody id="paymentReminderTableBody">
                @forelse(($reminderParties ?? collect()) as $party)
                  @php
                    $balance = (float) ($party->current_balance ?? 0);
                    $phone = trim((string) ($party->phone ?? ''));
                  @endphp
                  @if($balance > 0)
                    <tr data-party-name="{{ strtolower($party->name) }}" data-party-phone="{{ $phone }}" data-party-amount="{{ number_format($balance, 2, '.', '') }}">
                      <td>
                        @if($phone)
                          <input type="checkbox" class="table-check reminder-select" data-party-name="{{ e($party->name) }}" data-party-phone="{{ e($phone) }}" data-party-amount="{{ number_format($balance, 2, '.', '') }}" data-party-due-days="{{ (int) ($party->due_days ?? $partySettings['payment_reminder_days'] ?? 2) }}">
                        @endif
                      </td>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        <div class="fw-semibold">{{ $party->name }}</div>
                        <div class="text-muted small">{{ $phone ?: 'No phone number' }}</div>
                      </td>
                      <td class="text-end text-success">Rs {{ number_format($balance, 2) }}</td>
                      <td class="text-end">
                        @if($phone)
                          <button type="button"
                                  class="btn btn-success btn-sm whatsapp-btn reminder-send-single"
                                  data-party-name="{{ e($party->name) }}"
                                  data-party-phone="{{ e($phone) }}"
                                  data-party-amount="{{ number_format($balance, 2, '.', '') }}"
                                  data-party-due-days="{{ (int) ($party->due_days ?? $partySettings['payment_reminder_days'] ?? 2) }}">
                            <i class="fab fa-whatsapp"></i>
                          </button>
                        @else
                          <a class="btn btn-sm btn-outline-primary" href="{{ route('parties') }}">+ Add Phone No</a>
                        @endif
                      </td>
                    </tr>
                  @endif
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">No parties available for reminders.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="paymentReminderMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add / Edit Reminder Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="small text-muted mb-2">
            Use placeholders: <code>[Party Name]</code>, <code>[Amount]</code>, <code>[Business Name]</code>, <code>[Due Days]</code>
          </div>
          <div class="reminder-message-preview mb-3">{{ $partySettings['payment_reminder_message'] ?? "Dear [Party Name],\n\nYour payment of [Amount] is pending with [Business Name].\n\n[Additional Message]\n\nIf you already have made the payment, kindly ignore this message." }}</div>
          <textarea id="paymentReminderMessageInput" class="form-control" rows="10" style="min-height: 220px; white-space: pre-wrap;">{{ $partySettings['payment_reminder_message'] ?? "Dear [Party Name],\n\nYour payment of [Amount] is pending with [Business Name].\n\n[Additional Message]\n\nIf you already have made the payment, kindly ignore this message." }}</textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-outline-primary" id="paymentReminderResetBtn">Reset Default</button>
          <button type="button" class="btn btn-primary" id="paymentReminderSaveBtn">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script>
    (function () {
      const defaultPaymentReminderMessage = @json($partySettings['payment_reminder_message'] ?? "Dear [Party Name],\n\nYour payment of [Amount] is pending with [Business Name].\n\n[Additional Message]\n\nIf you already have made the payment, kindly ignore this message.");
      const paymentReminderMessageInput = document.getElementById('paymentReminderMessageInput');
      const paymentReminderSaveBtn = document.getElementById('paymentReminderSaveBtn');
      const paymentReminderResetBtn = document.getElementById('paymentReminderResetBtn');
      const paymentReminderPartySearch = document.getElementById('paymentReminderPartySearch');
      const paymentReminderTableBody = document.getElementById('paymentReminderTableBody');
      const bulkReminderBtn = document.getElementById('bulkReminderBtn');
      const bulkReminderBtnLabel = document.getElementById('bulkReminderBtnLabel');
      const selectAllReminders = document.getElementById('selectAllReminders');

      function saveReminderMessage() {
        fetch('{{ route('parties.settings.update') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            party_grouping: @json($partySettings['party_grouping'] ?? true),
            shipping_address: @json($partySettings['shipping_address'] ?? true),
            print_shipping_address: @json($partySettings['print_shipping_address'] ?? true),
            party_status: @json($partySettings['party_status'] ?? true),
            payment_reminder: @json($partySettings['payment_reminder'] ?? true),
            payment_reminder_days: Number(@json($partySettings['payment_reminder_days'] ?? 2)),
            payment_reminder_message: paymentReminderMessageInput?.value || defaultPaymentReminderMessage,
            additional_field_1: @json($partySettings['additional_field_1'] ?? false),
            additional_field_1_name: @json($partySettings['additional_field_1_name'] ?? ''),
            additional_field_1_print: @json($partySettings['additional_field_1_print'] ?? false),
            additional_field_2: @json($partySettings['additional_field_2'] ?? false),
            additional_field_2_name: @json($partySettings['additional_field_2_name'] ?? ''),
            additional_field_2_print: @json($partySettings['additional_field_2_print'] ?? false),
          })
        }).catch(() => {});
      }

      function buildReminderMessage(partyName, amount, dueDays) {
        const businessName = @json(config('app.name', 'My Company'));
        return String(paymentReminderMessageInput?.value || defaultPaymentReminderMessage || '')
          .replace(/\[Party Name\]/g, partyName || 'Party')
          .replace(/\[Amount\]/g, `Rs ${Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`)
          .replace(/\[Business Name\]/g, businessName)
          .replace(/\[Due Days\]/g, String(dueDays || ''));
      }

      function normalizeWhatsAppPhone(phone) {
        let cleanPhone = String(phone || '').replace(/\D/g, '');
        if (!cleanPhone) return '';
        if (cleanPhone.startsWith('0') && cleanPhone.length >= 10 && cleanPhone.length <= 11) {
          cleanPhone = `92${cleanPhone.slice(1)}`;
        }
        if (cleanPhone.length === 10 && !cleanPhone.startsWith('92')) {
          cleanPhone = `92${cleanPhone}`;
        }
        return cleanPhone;
      }

      function openWhatsAppReminder(phone, message) {
        const cleanPhone = normalizeWhatsAppPhone(phone);
        if (!cleanPhone) return false;
        const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank', 'noopener');
        return true;
      }

      function updateBulkButtonState() {
        const selected = Array.from(document.querySelectorAll('.reminder-select:checked'));
        if (!bulkReminderBtn || !bulkReminderBtnLabel) return;
        if (!selected.length) {
          bulkReminderBtn.style.display = 'none';
          return;
        }
        bulkReminderBtn.style.display = 'inline-flex';
        bulkReminderBtnLabel.textContent = selected.length === 1 ? 'Send Party Reminder' : 'Bulk Party Reminder';
      }

      function filterRows() {
        const query = String(paymentReminderPartySearch?.value || '').trim().toLowerCase();
        paymentReminderTableBody?.querySelectorAll('tr[data-party-name]').forEach((row) => {
          const haystack = [row.dataset.partyName || '', row.dataset.partyPhone || '', row.dataset.partyAmount || ''].join(' ').toLowerCase();
          row.style.display = haystack.includes(query) ? '' : 'none';
        });
      }

      paymentReminderPartySearch?.addEventListener('input', filterRows);
      paymentReminderMessageInput?.addEventListener('input', saveReminderMessage);
      paymentReminderSaveBtn?.addEventListener('click', function () {
        saveReminderMessage();
        bootstrap.Modal.getInstance(document.getElementById('paymentReminderMessageModal'))?.hide();
      });
      paymentReminderResetBtn?.addEventListener('click', function () {
        if (!paymentReminderMessageInput) return;
        paymentReminderMessageInput.value = defaultPaymentReminderMessage;
        saveReminderMessage();
      });

      paymentReminderTableBody?.addEventListener('click', function (e) {
        const btn = e.target.closest('.reminder-send-single');
        if (!btn) return;
        const phone = String(btn.dataset.partyPhone || '').replace(/\D/g, '');
        if (!phone) return alert('Phone number is missing.');
        const msg = buildReminderMessage(btn.dataset.partyName || '', btn.dataset.partyAmount || 0, btn.dataset.partyDueDays || 0);
        openWhatsAppReminder(phone, msg);
      });

      document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('reminder-select')) {
          updateBulkButtonState();
          if (selectAllReminders) {
            const checkboxes = Array.from(document.querySelectorAll('.reminder-select'));
            selectAllReminders.checked = checkboxes.length > 0 && checkboxes.every((cb) => cb.checked);
            selectAllReminders.indeterminate = checkboxes.some((cb) => cb.checked) && !selectAllReminders.checked;
          }
        }
      });

      selectAllReminders?.addEventListener('change', function () {
        const checkboxes = Array.from(document.querySelectorAll('.reminder-select'));
        checkboxes.forEach((cb) => { cb.checked = selectAllReminders.checked; });
        updateBulkButtonState();
      });

      bulkReminderBtn?.addEventListener('click', function () {
        const selected = Array.from(document.querySelectorAll('.reminder-select:checked'));
        if (!selected.length) return;
        selected.forEach((checkbox, index) => {
          const phone = normalizeWhatsAppPhone(checkbox.dataset.partyPhone || '');
          if (!phone) return;
          const msg = buildReminderMessage(
            checkbox.dataset.partyName || '',
            checkbox.dataset.partyAmount || 0,
            checkbox.dataset.partyDueDays || 0
          );
          window.setTimeout(() => {
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
          }, index * 350);
        });
      });

      updateBulkButtonState();
      filterRows();
    })();
  </script>
</body>
</html>

