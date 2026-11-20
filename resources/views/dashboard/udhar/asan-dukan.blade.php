@extends('layouts.app')

@section('title', 'آسان کریانہ و دکان پورٹل — Hexasolv')
@section('page', 'asan-dukan')

@section('content')
<div class="container-fluid py-3 px-2 px-md-4" style="direction: rtl; text-align: right; font-family: 'Noto Nastaliq Urdu', 'Plus Jakarta Sans', Tahoma, sans-serif;">

  <!-- Top Hero Banner with Mode Switch -->
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 p-3 bg-white rounded-3 shadow-sm border">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge" style="background: linear-gradient(135deg, #16a34a, #059669); font-size: 11px; padding: 4px 10px;">🏪 آسان دکان پورٹل</span>
        <h3 class="m-0 fw-bold" style="color: #18181b; font-size: 22px;">{{ $company->name }} — آسان کریانہ و کھاتہ</h3>
      </div>
      <p class="text-muted small m-0">
        صرف چار آسان بٹن: بل بنائیں، مال جمع کریں، ادھار دیکھیں، اور پرچی پرنٹ کریں!
      </p>
    </div>

    <!-- Mode Switcher -->
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm text-white px-3 py-2" style="background: linear-gradient(135deg, #5813BC, #AC22CB); font-weight: 700; border-radius: 8px;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal()">
        <i class="fa-solid fa-microphone me-1"></i> وائس مائیک آن کریں
      </button>

      <a href="/dashboard" class="btn btn-sm btn-outline-secondary px-3 py-2" style="border-radius: 8px;" title="تمام ایڈوانس فیچرز کھولیں">
        ⚙️ مکمل ERP موڈ
      </a>
    </div>
  </div>

  <!-- Big 4 Touch Action Cards (Village Friendly) -->
  <div class="row g-3 mb-4">
    <!-- Card 1: Voice Billing & Khata -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center" style="background: linear-gradient(135deg, #5813BC 0%, #7c3aed 100%); color: #fff; cursor: pointer; transition: transform 0.2s;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal()" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 28px;">
          🎙️
        </div>
        <h4 class="fw-bold mb-1" style="font-size: 20px;">وائس بل و کھاتہ</h4>
        <p class="small mb-3 text-white-50">مائیک میں بولیں: "ارسلان نے 5 کلو چینی اور 2 گھی لیا"</p>
        <button type="button" class="btn btn-sm btn-light fw-bold text-dark w-100 rounded-pill py-2">
          <i class="fa-solid fa-bolt me-1"></i> ابھی بول کر بل بنائیں
        </button>
      </div>
    </div>

    <!-- Card 2: Voice Stock Inward -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; cursor: pointer; transition: transform 0.2s;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal('آج 20 بوری چینی اور 10 پیٹی گھی کا اسٹاک آیا ہے')" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 28px;">
          📦
        </div>
        <h4 class="fw-bold mb-1" style="font-size: 20px;">مال کی آمد (اسٹاک انٹری)</h4>
        <p class="small mb-3 text-white-50">نیا مال بولیں: "20 بوری چینی اور 10 پیٹی گھی کا اسٹاک آیا ہے"</p>
        <button type="button" class="btn btn-sm btn-light fw-bold text-dark w-100 rounded-pill py-2">
          <i class="fa-solid fa-boxes-stacked me-1"></i> اسٹاک وائس انٹری
        </button>
      </div>
    </div>

    <!-- Card 3: Daily Udhar Register -->
    <div class="col-12 col-sm-6 col-lg-3">
      <a href="{{ route('udhar.register') }}" class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center text-decoration-none" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); color: #fff; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 28px;">
          📖
        </div>
        <h4 class="fw-bold mb-1" style="font-size: 20px;">روزانہ ادھار رجسٹر</h4>
        <p class="small mb-3 text-white-50">آج کا کل ادھار: Rs {{ number_format($todayCreditTotal) }} ({{ $todayCreditCount }} گاہک)</p>
        <div class="btn btn-sm btn-light fw-bold text-dark w-100 rounded-pill py-2">
          <i class="fa-solid fa-book-open me-1"></i> رجسٹر اور کھاتے دیکھیں
        </div>
      </a>
    </div>

    <!-- Card 4: Payment In / Wusooli -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; cursor: pointer; transition: transform 0.2s;" onclick="window.AlexVoiceAssistant && window.AlexVoiceAssistant.openVoiceModal('ارسلان نے 1000 روپے جمع کروائے')" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 28px;">
          💵
        </div>
        <h4 class="fw-bold mb-1" style="font-size: 20px;">ادھار وصولی / قسط</h4>
        <p class="small mb-3 text-white-50">گاہک نے پیسے دیے: بولیں "ارسلان نے 1000 روپے جمع کروائے"</p>
        <button type="button" class="btn btn-sm btn-light fw-bold text-dark w-100 rounded-pill py-2">
          <i class="fa-solid fa-hand-holding-dollar me-1"></i> وصولی درج کریں
        </button>
      </div>
    </div>
  </div>

  <!-- Today's Quick Udhar Strip & Top Customers -->
  <div class="row g-3">
    <!-- Today's Summary -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold m-0" style="color: #18181b; font-size: 16px;">
            📋 آج کے ادھار گاہک (Today's Credit Sales)
          </h5>
          <a href="{{ route('udhar.register') }}" class="btn btn-sm btn-outline-primary" style="color:#5813BC;border-color:#d8b4fe;font-size:12px;">
            مکمل دیکھیں ↗
          </a>
        </div>

        @if($todayCreditSales->isEmpty())
          <div class="text-center py-4 text-muted">
            <div style="font-size:32px;margin-bottom:8px;">✅</div>
            آج ابھی تک کوئی ادھار بل درج نہیں ہوا۔
          </div>
        @else
          <div class="list-group list-group-flush">
            @foreach($todayCreditSales->take(5) as $s)
              @php
                $party = $s->party;
                $itemsText = $s->items->map(fn($it) => "{$it->quantity} {$it->unit} {$it->item_name}")->implode('، ');
              @endphp
              <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                <div>
                  <strong style="color: #18181b; font-size: 14px;">{{ $party?->name ?: 'Walk-in' }}</strong>
                  <div class="text-muted small" style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $itemsText ?: 'کریانہ سامان' }}
                  </div>
                </div>
                <div class="text-end">
                  <strong style="color: #e11d48; font-size: 15px; display: block;">Rs {{ number_format($s->balance) }}</strong>
                  @if($party)
                    <a href="{{ route('udhar.khata', $party->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;">
                      کھاتہ و رسید
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <!-- Khata Directory Quick Lookup -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold m-0" style="color: #18181b; font-size: 16px;">
            👥 بڑے کھاتے دار (Top Khata Balances)
          </h5>
          <span class="badge bg-light text-danger border" style="font-size: 13px;">
            کل مارکیٹ ادھار: Rs {{ number_format($marketOutstanding) }}
          </span>
        </div>

        <div class="list-group list-group-flush">
          @forelse($recentCustomers as $p)
            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
              <div>
                <a href="{{ route('udhar.khata', $p->id) }}" class="fw-bold text-decoration-none" style="color: #5813BC; font-size: 15px;">
                  {{ $p->name }}
                </a>
                @if($p->phone)
                  <div class="text-muted small" style="direction:ltr;text-align:right;">{{ $p->phone }}</div>
                @endif
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="fw-bold" style="color: #e11d48; font-size: 16px;">
                  Rs {{ number_format($p->current_balance) }}
                </span>
                <a href="{{ route('udhar.receipt', ['party' => $p->id, 'days' => 30]) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="30 دن کی رسید پرنٹ کریں" style="padding: 2px 8px; font-size: 11px;">
                  <i class="fa-solid fa-print"></i> رسید
                </a>
                <a href="{{ route('udhar.khata', $p->id) }}" class="btn btn-sm text-white" style="background:#5813BC; padding: 2px 10px; font-size: 11px; border-radius: 6px;">
                  کھاتہ کھولیں
                </a>
              </div>
            </div>
          @empty
            <div class="text-center py-4 text-muted">
              کوئی کھاتے دار موجود نہیں
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
