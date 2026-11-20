@extends('layouts.app')

@section('title', 'روزانہ ادھار رجسٹر و کھاتہ بک — Hexasolv')
@section('page', 'udhar-register')

@section('content')
@php
  $urduMonths = [
    1 => 'جنوری', 2 => 'فروری', 3 => 'مارچ', 4 => 'اپریل', 5 => 'مئی', 6 => 'جون',
    7 => 'جولائی', 8 => 'اگست', 9 => 'ستمبر', 10 => 'اکتوبر', 11 => 'نومبر', 12 => 'دسمبر'
  ];
  $urduDateStr = $dateObj->day . ' ' . ($urduMonths[$dateObj->month] ?? '') . ' ' . $dateObj->year . 'ء';
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
  .fintech-wrapper {
    direction: rtl;
    text-align: right;
    font-family: 'Noto Sans Arabic', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    color: #0f172a;
    padding-bottom: 90px;
  }
  .fintech-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease;
  }
  .fintech-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  }
  .kpi-tile {
    position: relative;
    overflow: hidden;
    padding: 20px;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  }
  .kpi-tile::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 4px;
  }
  .kpi-tile.kpi-red::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
  .kpi-tile.kpi-blue::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
  .kpi-tile.kpi-purple::before { background: linear-gradient(90deg, #8b5cf6, #5813BC); }

  .kpi-label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .kpi-value {
    font-size: 28px;
    font-weight: 900;
    line-height: 1.2;
    margin-bottom: 4px;
    letter-spacing: -0.5px;
  }
  .kpi-sub {
    font-size: 12px;
    color: #94a3b8;
  }
  .avatar-initial {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 17px;
    color: #5813BC;
    background: #f3e8ff;
    flex-shrink: 0;
  }
  .fintech-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 12.5px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
  }
  .fintech-table td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13.5px;
  }
  .fintech-table tbody tr:hover {
    background: #f8fafc;
  }
  .pill-nav .nav-link {
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    padding: 10px 20px;
    color: #64748b;
    border: 1px solid transparent;
  }
  .pill-nav .nav-link.active {
    background: #5813BC !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(88, 19, 188, 0.25);
  }
  .btn-wa-action {
    background: #25D366;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 700;
  }
  .btn-wa-action:hover {
    background: #1eb956;
    color: #fff;
  }
</style>

<div class="container-fluid py-3 px-2 px-md-4 fintech-wrapper">

  <!-- Top Hero Header -->
  <div class="fintech-card p-3 p-md-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge" style="background: linear-gradient(135deg, #5813BC, #AC22CB); font-size: 11px; padding: 5px 10px; border-radius: 6px; letter-spacing: 0.5px;">ALEX AI KHATA</span>
          <h2 class="m-0 fw-bold" style="font-size: 22px; color: #0f172a;">📖 روزانہ ادھار رجسٹر و کھاتہ بک</h2>
        </div>
        <p class="text-muted small m-0" style="font-size: 13px;">
          تاریخ: <strong style="color: #5813BC;">{{ $urduDateStr }}</strong> ({{ $dateObj->isToday() ? 'آج' : ($dateObj->isYesterday() ? 'گزشتہ کل' : $dateObj->format('l')) }}) — تمام ادھار بلز اور گاہکوں کا بقایا کھاتہ
        </p>
      </div>

      <!-- Date Filter & Quick Actions -->
      <form method="GET" action="{{ route('udhar.register') }}" class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group shadow-sm" role="group" style="border-radius: 10px; overflow: hidden;">
          <a href="{{ route('udhar.register', ['date' => now()->toDateString()]) }}" class="btn btn-sm {{ $dateObj->isToday() ? 'btn-primary' : 'btn-light' }}" style="{{ $dateObj->isToday() ? 'background:#5813BC;border-color:#5813BC;color:#fff;' : 'background:#fff;border-color:#cbd5e1;' }} font-weight:700; padding: 6px 14px;">
            آج
          </a>
          <a href="{{ route('udhar.register', ['date' => now()->subDay()->toDateString()]) }}" class="btn btn-sm {{ $dateObj->isYesterday() ? 'btn-primary' : 'btn-light' }}" style="{{ $dateObj->isYesterday() ? 'background:#5813BC;border-color:#5813BC;color:#fff;' : 'background:#fff;border-color:#cbd5e1;' }} font-weight:700; padding: 6px 14px;">
            گزشتہ کل
          </a>
        </div>

        <input type="date" name="date" value="{{ $selectedDate }}" class="form-control form-control-sm shadow-sm" style="width: 145px; font-weight: 700; border-radius: 10px; border-color: #cbd5e1; padding: 6px 10px;" onchange="this.form.submit()">

        <button type="button" class="btn btn-sm text-white shadow-sm" style="background: linear-gradient(135deg, #5813BC, #AC22CB); font-weight: 800; border-radius: 10px; padding: 7px 16px;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal()">
          <i class="fa-solid fa-microphone me-1"></i> نیا وائس بل
        </button>
      </form>
    </div>
  </div>

  <!-- 3 Modern Financial KPI Tiles -->
  <div class="row g-3 mb-4">
    <!-- Tile 1: Today's Credit Sales -->
    <div class="col-12 col-md-4">
      <div class="kpi-tile kpi-red">
        <div class="kpi-label">
          <span>اس تاریخ کا کل نیا ادھار</span>
          <span style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-arrow-trend-up"></i>
          </span>
        </div>
        <div class="kpi-value" style="color: #dc2626;">
          Rs {{ number_format($dayTotalCredit) }}
        </div>
        <div class="kpi-sub">
          اس تاریخ کی کل سیل: <strong>Rs {{ number_format($dayGrandTotal) }}</strong>
        </div>
      </div>
    </div>

    <!-- Tile 2: Credit Customers -->
    <div class="col-12 col-md-4">
      <div class="kpi-tile kpi-blue">
        <div class="kpi-label">
          <span>ادھار لینے والے گاہک</span>
          <span style="width: 32px; height: 32px; border-radius: 8px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-users"></i>
          </span>
        </div>
        <div class="kpi-value" style="color: #2563eb;">
          {{ $dayCustomerCount }} <span style="font-size: 16px; font-weight: 600;">گاہک</span>
        </div>
        <div class="kpi-sub">
          کل ادھار بلز کی تعداد: <strong>{{ $creditSales->count() }}</strong>
        </div>
      </div>
    </div>

    <!-- Tile 3: Total Market Balance -->
    <div class="col-12 col-md-4">
      <div class="kpi-tile kpi-purple">
        <div class="kpi-label">
          <span>مارکیٹ کا کل بقایا ادھار (تمام کسٹمرز)</span>
          <span style="width: 32px; height: 32px; border-radius: 8px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-scale-balanced"></i>
          </span>
        </div>
        <div class="kpi-value" style="color: #7c3aed;">
          Rs {{ number_format($totalMarketOutstanding) }}
        </div>
        <div class="kpi-sub">
          تمام کھاتے داروں کی طرف واجب الادا رقم
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <ul class="nav pill-nav mb-3 gap-2" id="udharTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="daily-tab" data-bs-toggle="pill" data-bs-target="#dailyRegisterPane" type="button" role="tab">
        📋 روزنامچہ ادھار ({{ $dateObj->isToday() ? 'آج کے بلز' : $urduDateStr }})
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="all-khatas-tab" data-bs-toggle="pill" data-bs-target="#allKhatasPane" type="button" role="tab" style="background:#fff; border-color:#e2e8f0;">
        👥 تمام کھاتے دار اور کل بیلنس (Customers Ledger)
      </button>
    </li>
  </ul>

  <!-- Tab Contents -->
  <div class="tab-content" id="udharTabsContent">
    <!-- TAB 1: Daily Register -->
    <div class="tab-pane fade show active" id="dailyRegisterPane" role="tabpanel">
      <div class="fintech-card overflow-hidden">
        <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
          <h5 class="fw-bold m-0" style="font-size: 15px; color: #0f172a;">
            ادھار کھاتہ اندراجات برائے {{ $urduDateStr }}
          </h5>
          <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 12px;">
            کل اندراجات: {{ $creditSales->count() }}
          </span>
        </div>

        @if($creditSales->isEmpty())
          <div class="text-center py-5">
            <div style="font-size: 48px; margin-bottom: 10px;">📭</div>
            <h5 class="fw-bold text-secondary">اس تاریخ میں کوئی ادھار بل موجود نہیں ہے</h5>
            <p class="text-muted small">مائیک کا بٹن دبا کر نیا بل بولیں، وہ فوراً یہاں اور متعلقہ گاہک کے کھاتے میں درج ہو جائے گا۔</p>
            <button type="button" class="btn text-white px-4 py-2 mt-2 shadow-sm" style="background:#5813BC; border-radius:10px; font-weight:700;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal()">
              <i class="fa-solid fa-microphone me-1"></i> نیا وائس بل بنائیں
            </button>
          </div>
        @else
          <!-- Desktop Table (md and up) -->
          <div class="table-responsive d-none d-md-block">
            <table class="table fintech-table mb-0 align-middle">
              <thead>
                <tr>
                  <th style="width: 50px; text-align: center;">#</th>
                  <th>گاہک کا نام</th>
                  <th>بل نمبر</th>
                  <th>وقت</th>
                  <th>سامان کی تفصیل (اشیاء)</th>
                  <th style="text-align: left;">آج کا بل</th>
                  <th style="text-align: left;">گاہک کا کل نیا کھاتہ بیلنس</th>
                  <th style="text-align: center; width: 170px;">ایکشن</th>
                </tr>
              </thead>
              <tbody>
                @foreach($creditSales as $idx => $s)
                  @php
                    $party = $s->party;
                    $partyName = $party?->name ?: ($s->phone ?: 'Walk-in Customer');
                    $initial = mb_substr($partyName, 0, 1, 'UTF-8');
                    $itemsText = $s->items->map(function($it) {
                        return "{$it->quantity} {$it->unit} {$it->item_name}";
                    })->implode(' ، ');

                    $phone = preg_replace('/\D/', '', (string)($party?->phone ?: $s->phone));
                    if (str_starts_with($phone, '03') && strlen($phone) === 11) $phone = '92' . substr($phone, 1);
                    $waMsg = "محترم {$partyName} صاحب، {$company->name} سے آج آپ کا بل #{$s->bill_number} رقم Rs " . number_format($s->balance) . " درج ہوا ہے۔ آپ کا کل بقایا کھاتہ بیلنس Rs " . number_format($party?->current_balance ?: $s->balance) . " ہے۔ شکریہ!";
                    $waUrl = !empty($phone) ? "https://wa.me/{$phone}?text=" . urlencode($waMsg) : null;
                  @endphp
                  <tr>
                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $idx + 1 }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar-initial">{{ $initial }}</div>
                        <div>
                          <a href="{{ $party ? route('udhar.khata', $party->id) : '#' }}" class="fw-bold text-decoration-none" style="color: #0f172a; font-size: 15px;">
                            {{ $partyName }}
                          </a>
                          @if($party?->phone)
                            <div class="text-muted small" style="direction: ltr; text-align: right; font-size: 11.5px;">{{ $party->phone }}</div>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; font-size: 12px; padding: 5px 8px;">
                        #{{ $s->bill_number }}
                      </span>
                    </td>
                    <td class="text-muted small" style="white-space: nowrap;">
                      {{ $s->created_at ? $s->created_at->format('h:i A') : '-' }}
                    </td>
                    <td style="max-width: 280px;">
                      <span class="badge bg-light text-dark border" style="font-weight: 500; font-size: 12px; padding: 5px 8px;" title="{{ $itemsText }}">
                        {{ $itemsText ?: 'جنرل کریانہ سامان' }}
                      </span>
                    </td>
                    <td style="text-align: left; font-weight: 800; color: #0f172a; font-size: 15px;">
                      Rs {{ number_format($s->grand_total) }}
                    </td>
                    <td style="text-align: left;">
                      <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-size: 14px; font-weight: 800; padding: 6px 10px;">
                        Rs {{ number_format($party?->current_balance ?: $s->balance) }}
                      </span>
                    </td>
                    <td style="text-align: center;">
                      <div class="d-inline-flex align-items-center gap-1">
                        @if($waUrl)
                          <a href="{{ $waUrl }}" target="_blank" class="btn-wa-action" title="واٹس ایپ رسید بھیجیں">
                            <i class="fa-brands fa-whatsapp"></i>
                          </a>
                        @endif
                        @if($party)
                          <a href="{{ route('udhar.receipt', ['party' => $party->id, 'days' => 30]) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="پرچی / رسید پرنٹ کریں" style="border-radius: 8px; padding: 5px 9px;">
                            <i class="fa-solid fa-print"></i>
                          </a>
                          <a href="{{ route('udhar.khata', $party->id) }}" class="btn btn-sm text-white" title="مکمل 30/60 دن کا کھاتہ دیکھیں" style="padding: 5px 12px; border-radius: 8px; background: #5813BC; font-size: 12px; font-weight: 700;">
                            کھاتہ ↗
                          </a>
                        @endif
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards Layout (d-md-none) -->
          <div class="d-md-none p-3">
            @foreach($creditSales as $idx => $s)
              @php
                $party = $s->party;
                $partyName = $party?->name ?: ($s->phone ?: 'Walk-in');
                $initial = mb_substr($partyName, 0, 1, 'UTF-8');
                $itemsText = $s->items->map(fn($it) => "{$it->quantity} {$it->unit} {$it->item_name}")->implode(' ، ');
                $phone = preg_replace('/\D/', '', (string)($party?->phone ?: $s->phone));
                if (str_starts_with($phone, '03') && strlen($phone) === 11) $phone = '92' . substr($phone, 1);
                $waMsg = "محترم {$partyName} صاحب، {$company->name} سے آج آپ کا بل #{$s->bill_number} رقم Rs " . number_format($s->balance) . " درج ہوا ہے۔ آپ کا کل بقایا کھاتہ بیلنس Rs " . number_format($party?->current_balance ?: $s->balance) . " ہے۔ شکریہ!";
                $waUrl = !empty($phone) ? "https://wa.me/{$phone}?text=" . urlencode($waMsg) : null;
              @endphp
              <div class="fintech-card p-3 mb-3" style="border-right: 4px solid #5813BC;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 15px;">{{ $initial }}</div>
                    <div>
                      <a href="{{ $party ? route('udhar.khata', $party->id) : '#' }}" class="fw-bold text-decoration-none" style="color: #0f172a; font-size: 16px;">
                        {{ $partyName }}
                      </a>
                      @if($party?->phone)
                        <div class="text-muted small" style="direction: ltr; text-align: right; font-size: 11px;">{{ $party->phone }}</div>
                      @endif
                    </div>
                  </div>
                  <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:700;">#{{ $s->bill_number }}</span>
                </div>

                <div class="small text-muted mb-2 py-1 px-2 rounded bg-light" style="font-size: 12px;">
                  <i class="fa-solid fa-basket-shopping me-1 text-secondary"></i> {{ $itemsText ?: 'کریانہ سامان' }}
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom mb-3" style="font-size: 13px;">
                  <div>
                    <span class="text-muted">آج کا بل:</span>
                    <strong style="color: #0f172a; font-size: 15px;">Rs {{ number_format($s->grand_total) }}</strong>
                  </div>
                  <div class="text-end">
                    <span class="text-muted">کل بقایا کھاتہ:</span>
                    <strong style="color: #dc2626; font-size: 16px;">Rs {{ number_format($party?->current_balance ?: $s->balance) }}</strong>
                  </div>
                </div>

                <div class="d-flex gap-2">
                  @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" class="btn-wa-action flex-fill justify-content-center">
                      <i class="fa-brands fa-whatsapp"></i> واٹس ایپ
                    </a>
                  @endif
                  @if($party)
                    <a href="{{ route('udhar.receipt', ['party' => $party->id, 'days' => 30]) }}" target="_blank" class="btn btn-sm btn-outline-dark" style="border-radius: 8px;">
                      <i class="fa-solid fa-print"></i> رسید
                    </a>
                    <a href="{{ route('udhar.khata', $party->id) }}" class="btn btn-sm text-white flex-fill text-center" style="background: #5813BC; border-radius: 8px; font-weight: 700; padding: 7px 0;">
                      کھاتہ لیجر ↗
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <!-- TAB 2: All Customers Khata Directory -->
    <div class="tab-pane fade" id="allKhatasPane" role="tabpanel">
      <div class="fintech-card p-3 mb-3">
        <form method="GET" action="{{ route('udhar.register') }}" class="d-flex gap-2">
          <input type="hidden" name="date" value="{{ $selectedDate }}">
          <input type="text" name="search" value="{{ $search }}" placeholder="گاہک کا نام یا فون نمبر تلاش کریں..." class="form-control" style="font-size: 14px; border-radius: 10px;">
          <button type="submit" class="btn text-white px-4" style="background: #5813BC; font-weight: 700; border-radius: 10px;">تلاش کریں</button>
          @if($search)
            <a href="{{ route('udhar.register', ['date' => $selectedDate]) }}" class="btn btn-outline-secondary" style="border-radius: 10px;">کلیئر</a>
          @endif
        </form>
      </div>

      <div class="fintech-card overflow-hidden">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
          <table class="table fintech-table mb-0 align-middle">
            <thead>
              <tr>
                <th style="width: 50px; text-align: center;">#</th>
                <th>کھاتے دار کا نام</th>
                <th>فون نمبر</th>
                <th>شہر / پتہ</th>
                <th style="text-align: left;">کل بقایا ادھار بیلنس</th>
                <th style="text-align: center; width: 190px;">ایکشن</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allKhatas as $idx => $p)
                @php
                  $initial = mb_substr($p->name, 0, 1, 'UTF-8');
                @endphp
                <tr>
                  <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $allKhatas->firstItem() + $idx }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-initial">{{ $initial }}</div>
                      <div>
                        <a href="{{ route('udhar.khata', $p->id) }}" class="fw-bold text-decoration-none" style="color: #0f172a; font-size: 15px;">
                          {{ $p->name }}
                        </a>
                      </div>
                    </div>
                  </td>
                  <td style="direction: ltr; text-align: right; color: #64748b; font-size: 12.5px;">{{ $p->phone ?: '-' }}</td>
                  <td style="color: #64748b;">{{ $p->city ?: ($p->address ?: '-') }}</td>
                  <td style="text-align: left;">
                    <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-size: 14.5px; font-weight: 800; padding: 6px 10px;">
                      Rs {{ number_format($p->current_balance) }}
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <div class="d-inline-flex gap-1">
                      <a href="{{ route('udhar.receipt', ['party' => $p->id, 'days' => 30]) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="30 دن کی پرچی پرنٹ کریں" style="padding: 5px 9px; font-size: 12px; border-radius: 8px;">
                        <i class="fa-solid fa-print"></i> رسید
                      </a>
                      <a href="{{ route('udhar.khata', $p->id) }}" class="btn btn-sm text-white px-3" style="background: #5813BC; border-radius: 8px; font-size: 12px; font-weight: 700; padding: 5px 12px;">
                        مکمل کھاتہ ↗
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">کوئی کھاتے دار نہیں ملا</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none p-3">
          @forelse($allKhatas as $idx => $p)
            @php
              $initial = mb_substr($p->name, 0, 1, 'UTF-8');
            @endphp
            <div class="fintech-card p-3 mb-2" style="border-right: 4px solid #5813BC;">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 15px;">{{ $initial }}</div>
                  <div>
                    <a href="{{ route('udhar.khata', $p->id) }}" class="fw-bold text-decoration-none" style="color: #0f172a; font-size: 16px;">
                      {{ $p->name }}
                    </a>
                  </div>
                </div>
                <strong style="color: #dc2626; font-size: 16px;">Rs {{ number_format($p->current_balance) }}</strong>
              </div>
              <div class="text-muted small mb-2" style="font-size: 11.5px;">
                @if($p->phone)<span style="direction:ltr;display:inline-block;">{{ $p->phone }}</span> • @endif {{ $p->city ?: ($p->address ?: 'کھاتے دار') }}
              </div>
              <div class="d-flex gap-2 pt-2 border-top">
                <a href="{{ route('udhar.receipt', ['party' => $p->id, 'days' => 30]) }}" target="_blank" class="btn btn-sm btn-outline-dark" style="border-radius: 8px;">
                  <i class="fa-solid fa-print"></i> پرچی
                </a>
                <a href="{{ route('udhar.khata', $p->id) }}" class="btn btn-sm text-white flex-fill text-center" style="background:#5813BC; border-radius: 8px; font-weight:700; padding: 6px 0;">
                  مکمل کھاتہ لیجر ↗
                </a>
              </div>
            </div>
          @empty
            <div class="text-center py-4 text-muted">کوئی کھاتے دار نہیں ملا</div>
          @endforelse
        </div>

        @if($allKhatas->hasPages())
          <div class="p-3 border-top">
            {{ $allKhatas->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection
