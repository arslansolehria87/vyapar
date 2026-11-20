<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>کھاتہ رسید — {{ $party->name }} — {{ $company->name }}</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Noto Nastaliq Urdu', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      direction: rtl;
      text-align: right;
      background: #f4f4f5;
      color: #18181b;
      font-size: 13px;
      line-height: 1.5;
      padding: 20px;
    }
    .receipt-container {
      max-width: 80mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 16px 12px;
      border: 1px dashed #d4d4d8;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
      border-radius: 6px;
    }
    .no-print-bar {
      max-width: 80mm;
      margin: 0 auto 12px auto;
      display: flex;
      gap: 8px;
    }
    .btn {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      font-size: 13px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      text-decoration: none;
    }
    .btn-print {
      background: #18181b;
      color: #ffffff;
    }
    .btn-wa {
      background: #25D366;
      color: #ffffff;
    }
    .shop-header {
      text-align: center;
      border-bottom: 2px dashed #18181b;
      padding-bottom: 10px;
      margin-bottom: 10px;
    }
    .shop-name {
      font-size: 18px;
      font-weight: 900;
      color: #000;
    }
    .shop-meta {
      font-size: 11px;
      color: #52525b;
      margin-top: 2px;
    }
    .customer-box {
      background: #faf5ff;
      border: 1px solid #e9d5ff;
      border-radius: 6px;
      padding: 8px 10px;
      margin-bottom: 10px;
    }
    .customer-box strong {
      font-size: 14px;
      color: #5813BC;
    }
    .receipt-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      font-size: 11.5px;
    }
    .receipt-table th {
      border-bottom: 1px solid #18181b;
      padding: 4px 2px;
      text-align: right;
      color: #71717a;
      font-weight: 700;
    }
    .receipt-table td {
      padding: 6px 2px;
      border-bottom: 1px dashed #e4e4e7;
      vertical-align: top;
    }
    .totals-box {
      border-top: 2px solid #18181b;
      padding-top: 8px;
      margin-bottom: 12px;
    }
    .totals-row {
      display: flex;
      justify-content: space-between;
      padding: 3px 0;
      font-size: 12.5px;
    }
    .totals-row.grand {
      font-size: 16px;
      font-weight: 900;
      border-top: 1px dashed #18181b;
      padding-top: 6px;
      margin-top: 4px;
      color: #b91c1c;
    }
    .receipt-footer {
      text-align: center;
      font-size: 11px;
      color: #71717a;
      border-top: 1px dashed #d4d4d8;
      padding-top: 10px;
      margin-top: 10px;
    }
    .signature-line {
      margin-top: 20px;
      border-top: 1px solid #a1a1aa;
      display: inline-block;
      width: 120px;
      padding-top: 2px;
      font-size: 10.5px;
    }

    @media print {
      body {
        background: #ffffff !important;
        padding: 0 !important;
      }
      .no-print-bar {
        display: none !important;
      }
      .receipt-container {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        width: 80mm !important;
        padding: 0 !important;
      }
    }
  </style>
</head>
<body>

  <!-- Screen Buttons (Hidden when printing) -->
  <div class="no-print-bar">
    <button type="button" class="btn btn-print" onclick="window.print()">
      <i class="fa-solid fa-print"></i> پرنٹ کریں (Print)
    </button>
    <button type="button" class="btn" style="background:#5813BC;color:#fff;" onclick="window.close()">
      <i class="fa-solid fa-xmark"></i> بند کریں
    </button>
  </div>

  <div class="receipt-container">
    <!-- Shop Header -->
    <div class="shop-header">
      <div class="shop-name">{{ $company->name }}</div>
      @if($company->address)
        <div class="shop-meta">{{ $company->address }}</div>
      @endif
      @if($company->phone)
        <div class="shop-meta">فون: {{ $company->phone }}</div>
      @endif
      <div class="shop-meta" style="margin-top: 4px;">
        تاریخ رسید: {{ now()->format('d/m/Y h:i A') }}
      </div>
    </div>

    <!-- Customer Box -->
    <div class="customer-box">
      <div>گاہک کا نام: <strong>{{ $party->name }}</strong></div>
      @if($party->phone)
        <div style="font-size:11px;color:#52525b;direction:ltr;text-align:right;">{{ $party->phone }}</div>
      @endif
      <div style="font-size:11px;color:#71717a;margin-top:2px;">
        کھاتہ حساب: {{ $days === 'all' ? 'مکمل کھاتہ' : "پچھلے {$days} دن کا خلاصہ" }}
      </div>
    </div>

    <!-- Items Table -->
    <table class="receipt-table">
      <thead>
        <tr>
          <th>تاریخ</th>
          <th>تفصیل / سامان</th>
          <th style="text-align:left;">بل / ادا</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td style="white-space:nowrap;">{{ $r['date'] }}</td>
            <td>
              <div>{{ $r['items'] }}</div>
              @if($r['bill'])
                <span style="font-size:9.5px;color:#71717a;">#{{ $r['bill'] }}</span>
              @endif
            </td>
            <td style="text-align:left;white-space:nowrap;">
              <strong>Rs {{ number_format($r['amount']) }}</strong>
              @if($r['paid'] > 0)
                <div style="color:#16a34a;font-size:10px;">ادا: Rs {{ number_format($r['paid']) }}</div>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" style="text-align:center;padding:12px;color:#a1a1aa;">کوئی اندراج موجود نہیں</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Totals Summary -->
    <div class="totals-box">
      <div class="totals-row">
        <span>کل خریداری بل رقم:</span>
        <strong>Rs {{ number_format($totalPurchased) }}</strong>
      </div>
      <div class="totals-row" style="color:#16a34a;">
        <span>کل ادا شدہ رقم (وصولی):</span>
        <strong>Rs {{ number_format($totalPaid) }}</strong>
      </div>
      <div class="totals-row grand">
        <span>موجودہ کل بقایا ادھار:</span>
        <span>Rs {{ number_format($currentBalance) }}</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
      <div>دکان پر تشریف آوری کا شکریہ!</div>
      <div style="font-size:10px;margin-top:3px;">Hexasolv Alex AI وائس کھاتہ سسٹم</div>
      <div class="signature-line">دکاندار کے دستخط</div>
    </div>
  </div>

  <script>
    // Auto-trigger print after render if print query parameter is passed
    if (window.location.search.includes('autoprint=1')) {
      window.onload = function() {
        setTimeout(function() { window.print(); }, 400);
      };
    }
  </script>
</body>
</html>
