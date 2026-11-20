@extends('layouts.app')

@section('title', "کھاتہ لیجر برائے {$party->name} — Hexasolv")
@section('page', 'udhar-register')

@section('content')
<div class="container-fluid py-3 px-2 px-md-4" style="direction: rtl; text-align: right; font-family: 'Noto Nastaliq Urdu', 'Plus Jakarta Sans', Tahoma, sans-serif;">

  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <a href="{{ route('udhar.register') }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="واپس روزانہ ادھار رجسٹر">
            <i class="fa-solid fa-arrow-right"></i> واپس رجسٹر
          </a>
          <span class="badge" style="background: linear-gradient(135deg, #5813BC, #AC22CB); font-size: 11px;">کھاتہ لیجر</span>
          <h3 class="m-0 fw-bold" style="color: #18181b; font-size: 22px;">{{ $party->name }}</h3>
        </div>
        <p class="text-muted small m-0">
          @if($party->phone)
            <span class="me-2"><i class="fa-solid fa-phone me-1"></i> <span style="direction:ltr;display:inline-block;">{{ $party->phone }}</span></span>
          @endif
          @if($party->city || $party->address)
            <span><i class="fa-solid fa-location-dot me-1"></i> {{ $party->city ?: $party->address }}</span>
          @endif
        </p>
      </div>

      <!-- Quick Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('udhar.receipt', ['party' => $party->id, 'days' => $days]) }}" target="_blank" class="btn btn-sm text-white px-3 py-2" style="background: #18181b; border-radius: 8px; font-weight: 700;">
          <i class="fa-solid fa-print me-1"></i> 🖨️ پرچی / رسید پرنٹ کریں
        </a>

        @if($waUrl)
          <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm text-white px-3 py-2" style="background: #25D366; border-radius: 8px; font-weight: 700;">
            <i class="fa-brands fa-whatsapp me-1"></i> واٹس ایپ خلاصہ بھیجیں
          </a>
        @endif

        <button type="button" class="btn btn-sm text-white px-3 py-2" style="background: linear-gradient(135deg, #5813BC, #AC22CB); border-radius: 8px; font-weight: 700;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal()">
          <i class="fa-solid fa-microphone me-1"></i> نیا وائس اندراج
        </button>
      </div>
    </div>

    <!-- Date Range Quick Filter Pills -->
    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
      <span class="small fw-bold text-muted">حساب کا دورانیہ:</span>
      <a href="{{ route('udhar.khata', ['party' => $party->id, 'days' => '30']) }}" class="btn btn-sm {{ $days === '30' ? 'btn-primary' : 'btn-outline-secondary' }}" style="{{ $days === '30' ? 'background:#5813BC;border-color:#5813BC;' : '' }}; border-radius: 20px;">
        📅 پچھلے 30 دن کا کھاتہ
      </a>
      <a href="{{ route('udhar.khata', ['party' => $party->id, 'days' => '60']) }}" class="btn btn-sm {{ $days === '60' ? 'btn-primary' : 'btn-outline-secondary' }}" style="{{ $days === '60' ? 'background:#5813BC;border-color:#5813BC;' : '' }}; border-radius: 20px;">
        📅 پچھلے 60 دن کا کھاتہ
      </a>
      <a href="{{ route('udhar.khata', ['party' => $party->id, 'days' => 'all']) }}" class="btn btn-sm {{ $days === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}" style="{{ $days === 'all' ? 'background:#5813BC;border-color:#5813BC;' : '' }}; border-radius: 20px;">
        📋 مکمل کھاتہ (تمام ادوار)
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <!-- Total Purchases -->
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-right: 4px solid #5813BC !important;">
        <span class="text-muted small fw-bold">کل خریداری (منتخب دورانیہ):</span>
        <h2 class="fw-bold my-1" style="color: #5813BC; font-size: 26px;">
          Rs {{ number_format($periodPurchases) }}
        </h2>
        <span class="small text-secondary">تمام اشیاء اور سامان کا بل</span>
      </div>
    </div>

    <!-- Total Payments Paid in Between -->
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-right: 4px solid #16a34a !important;">
        <span class="text-muted small fw-bold">بیچ میں ادا شدہ رقم (وصولی):</span>
        <h2 class="fw-bold my-1" style="color: #16a34a; font-size: 26px;">
          Rs {{ number_format($periodPaid) }}
        </h2>
        <span class="small text-secondary">گاہک کی طرف سے نقد دی گئی رقم</span>
      </div>
    </div>

    <!-- Outstanding Net Balance -->
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-right: 4px solid #e11d48 !important;">
        <span class="text-muted small fw-bold">موجودہ کل بقایا واجب الادا (Net Udhar):</span>
        <h2 class="fw-bold my-1" style="color: #e11d48; font-size: 28px;">
          Rs {{ number_format($netBalance) }}
        </h2>
        <span class="small text-secondary">جو رقم دکاندار نے گاہک سے لینی ہے</span>
      </div>
    </div>
  </div>

  <!-- Timeline Table (Desktop) & Cards (Mobile) -->
  <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold m-0" style="color: #18181b; font-size: 16px;">
        تاریخ وار کھاتہ لین دین شیڈول (Schedule)
      </h5>
      <span class="badge bg-light text-dark border">
        کل اندراجات: {{ count($filteredSchedule) }}
      </span>
    </div>

    @if(empty($filteredSchedule))
      <div class="text-center py-5">
        <div style="font-size: 40px; color: #a1a1aa; margin-bottom: 12px;">📭</div>
        <h5 class="fw-bold text-secondary">اس دورانیے میں کوئی کھاتہ اندراج موجود نہیں ہے</h5>
        <p class="text-muted small">اوپر سے "مکمل کھاتہ" منتخب کریں یا مائیک کے ذریعے نیا بل بنائیں۔</p>
      </div>
    @else
      <!-- Desktop Table (md and up) -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
          <thead class="table-light text-muted">
            <tr>
              <th style="width: 50px;">#</th>
              <th>تاریخ و وقت</th>
              <th>بل / رسید نمبر</th>
              <th>تفصیل و سامان (اشیاء)</th>
              <th class="text-end">ادھار بل (+)</th>
              <th class="text-end">ادا شدہ رقم (-)</th>
              <th class="text-end fw-bold" style="color:#5813BC;">بقایا بیلنس</th>
            </tr>
          </thead>
          <tbody>
            @foreach($filteredSchedule as $idx => $row)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                  <strong>{{ $row['date_formatted'] }}</strong>
                  @if($row['time'])
                    <div class="text-muted small">{{ $row['time'] }}</div>
                  @endif
                </td>
                <td>
                  <span class="badge bg-light text-dark border">{{ $row['ref_number'] }}</span>
                </td>
                <td style="max-width: 320px;">
                  @if(!empty($row['items']))
                    <div class="fw-bold text-dark">{{ $row['description'] }}</div>
                    <div class="small text-secondary" style="font-size: 12px;">
                      @foreach($row['items'] as $it)
                        <span class="badge bg-light text-secondary border me-1 my-1">
                          {{ $it['qty'] }} {{ $it['unit'] }} {{ $it['name'] }} @ Rs {{ number_format($it['price']) }} = Rs {{ number_format($it['amount']) }}
                        </span>
                      @endforeach
                    </div>
                  @else
                    <span>{{ $row['description'] }}</span>
                  @endif
                </td>
                <td class="text-end fw-bold" style="color: {{ $row['debit'] > 0 ? '#18181b' : '#a1a1aa' }};">
                  {{ $row['debit'] > 0 ? 'Rs ' . number_format($row['debit']) : '-' }}
                </td>
                <td class="text-end fw-bold" style="color: {{ $row['credit'] > 0 ? '#16a34a' : '#a1a1aa' }};">
                  {{ $row['credit'] > 0 ? 'Rs ' . number_format($row['credit']) : '-' }}
                </td>
                <td class="text-end fw-bold" style="color: #e11d48; font-size: 15px;">
                  Rs {{ number_format($row['running_balance']) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards (d-md-none) for 100% Mobile Phone Experience -->
      <div class="d-md-none p-2">
        @foreach($filteredSchedule as $idx => $row)
          <div class="card mb-2 border rounded-3 p-3 shadow-none" style="background: {{ $row['credit'] > 0 ? '#f0fdf4' : '#ffffff' }}; border-right: 4px solid {{ $row['credit'] > 0 ? '#16a34a' : '#5813BC' }} !important;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-bold" style="font-size: 13px; color: #18181b;">
                {{ $row['date_formatted'] }} {{ $row['time'] ? '• ' . $row['time'] : '' }}
              </span>
              <span class="badge bg-light text-dark border" style="font-size: 11px;">#{{ $row['ref_number'] }}</span>
            </div>

            <!-- Items summary on mobile -->
            <div class="small text-muted mb-2">
              {{ $row['items_summary'] ?: $row['description'] }}
            </div>

            <!-- Amounts Row -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 12.5px;">
              <div>
                @if($row['debit'] > 0)
                  <span class="text-muted">بل رقم:</span>
                  <strong style="color: #18181b;">Rs {{ number_format($row['debit']) }}</strong>
                @endif
                @if($row['credit'] > 0)
                  <span class="text-muted">ادا رقم:</span>
                  <strong style="color: #16a34a;">Rs {{ number_format($row['credit']) }}</strong>
                @endif
              </div>
              <div class="text-end">
                <span class="text-muted" style="font-size: 11px;">بقایا:</span>
                <strong style="color: #e11d48; font-size: 14.5px;">Rs {{ number_format($row['running_balance']) }}</strong>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>
@endsection
