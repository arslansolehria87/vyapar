<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Sale Order Preview' }}</title>
    <style>
        @page {
            size: A4;
            margin: 18px;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f5f7fb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        body {
            min-height: 100vh;
        }

        .preview-shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 18px;
        }

        .doc {
            background: #fff;
            border: 1px solid #d7dfeb;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #111c4e;
            margin: 16px 0 18px;
        }

        .top-banner {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .top-banner td {
            vertical-align: middle;
        }

        .top-left {
            width: 62%;
            background: #243042;
            color: #fff;
            padding: 16px 18px;
        }

        .top-right {
            width: 38%;
            background: #e37d2f;
            color: #fff;
            text-align: right;
            padding: 16px 18px;
        }

        .logo-box {
            width: 58px;
            height: 58px;
            background: rgba(255, 255, 255, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.1;
            margin: 0;
        }

        .company-phone {
            margin-top: 4px;
            font-size: 13px;
        }

        .section-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-bottom: 18px;
        }

        .section-table td {
            vertical-align: top;
            width: 50%;
        }

        .panel {
            border: 1px solid #cfd8e6;
            border-radius: 10px;
            padding: 14px 16px;
            background: #fff;
            box-sizing: border-box;
        }

        .panel-head {
            color: #f07b21;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 8px;
        }

        .bill-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #111827;
        }

        .invoice-title {
            font-size: 26px;
            font-weight: 500;
            margin: 0 0 12px;
            color: #111827;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 4px 0;
            font-size: 14px;
        }

        .meta-row strong {
            font-weight: 700;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #cfd8e6;
            padding: 8px 10px;
        }

        .items-table th {
            background: #e37d2f;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-align: left;
        }

        .items-table td {
            font-size: 14px;
            background: #fff;
        }

        .items-table .right {
            text-align: right;
        }

        .items-table .center {
            text-align: center;
        }

        .summary-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary-wrap td {
            vertical-align: top;
        }

        .words-box,
        .terms-box {
            border: 1px solid #cfd8e6;
            border-radius: 10px;
            padding: 14px 16px;
            background: #fff;
            min-height: 104px;
        }

        .box-title {
            color: #f07b21;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .summary-table {
            width: 100%;
            border: 1px solid #cfd8e6;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .summary-table td {
            border-bottom: 1px solid #cfd8e6;
            padding: 8px 12px;
            font-size: 14px;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
        }

        .summary-table .grand td {
            background: #e37d2f;
            color: #fff;
            font-weight: 700;
        }

        .summary-table .right {
            text-align: right;
        }

        .footer-sign {
            border: 1px solid #cfd8e6;
            border-radius: 10px;
            min-height: 158px;
            padding: 18px 18px 20px;
            background: #fff;
        }

        .footer-for {
            text-align: right;
            font-weight: 700;
            margin-bottom: 24px;
            color: #111827;
        }

        .signature-wrap {
            width: 300px;
            min-height: 92px;
            margin-left: auto;
            border: 1px solid #cfd8e6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 10px 12px;
            box-sizing: border-box;
        }

        .signature-wrap img {
            max-width: 100%;
            max-height: 60px;
            object-fit: contain;
            display: block;
        }

        .signature-label {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .signature-empty {
            width: 100%;
            min-height: 68px;
        }

        .muted {
            color: #6b7280;
        }

        @media (max-width: 900px) {
            .preview-shell {
                padding: 10px;
            }

            .section-table,
            .summary-wrap tr,
            .summary-wrap td {
                display: block;
                width: 100%;
            }

            .summary-wrap td + td {
                margin-top: 14px;
            }

            .signature-wrap {
                width: 100%;
                margin-left: 0;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .preview-shell {
                padding: 0;
            }

            .doc {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
@php
    $preview = $invoicePreviewData ?? [];
    $items = collect($preview['items'] ?? []);
    $businessName = (string) ($preview['businessName'] ?? config('app.name', 'My Company'));
    $phone = (string) ($preview['phone'] ?? '');
    $invoiceNo = (string) ($preview['invoiceNo'] ?? ($sale->bill_number ?? $sale->id ?? ''));
    $orderDate = (string) ($preview['date'] ?? '');
    $dueDate = (string) ($preview['dueDate'] ?? '');
    $billTo = (string) ($preview['billTo'] ?? '');
    $billAddress = (string) ($preview['billAddress'] ?? '');
    $billPhone = (string) ($preview['billPhone'] ?? '');
    $shipTo = (string) ($preview['shipTo'] ?? '');
    $subtotal = (float) ($preview['subtotal'] ?? 0);
    $discount = (float) ($preview['discount'] ?? 0);
    $taxAmount = (float) ($preview['taxAmount'] ?? 0);
    $total = (float) ($preview['total'] ?? 0);
    $received = (float) ($preview['received'] ?? 0);
    $balance = (float) ($preview['balance'] ?? 0);
    $totalInWords = trim((string) ($preview['totalInWords'] ?? 'Zero Rupees only'));
    $termsText = trim((string) ($preview['termsText'] ?? ''));
    if ($termsText === '') {
        $termsText = 'Thanks for shopping with us!';
    }
    $signatureImage = trim((string) ($signatureImage ?? ''));
    $signatureText = 'Authorized Signatory';
@endphp
<body>
    <div class="preview-shell">
        <div class="doc">
            <div class="page-title">Sale Order</div>

            <table class="top-banner">
                <tr>
                    <td class="top-left">
                        <div class="logo-box">LOGO</div>
                        <div class="company-name">{{ $businessName }}</div>
                        <div class="company-phone">Phone: {{ $phone }}</div>
                    </td>
                    <td class="top-right">
                        <div style="font-size:13px;font-weight:700;opacity:.95;">&nbsp;</div>
                    </td>
                </tr>
            </table>

            <table class="section-table">
                <tr>
                    <td>
                        <div class="panel">
                            <div class="invoice-title">Invoice</div>
                            <div class="meta-row">
                                <span><strong>Invoice No.</strong></span>
                                <span>{{ $invoiceNo }}</span>
                            </div>
                            <div class="meta-row">
                                <span><strong>Date</strong></span>
                                <span>{{ $orderDate }}</span>
                            </div>
                            @if($dueDate !== '')
                                <div class="meta-row">
                                    <span><strong>Due Date</strong></span>
                                    <span>{{ $dueDate }}</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="panel">
                            <div class="panel-head">Bill To</div>
                            <div class="bill-name">{{ $billTo }}</div>
                            @if($billAddress !== '')
                                <div>{{ $billAddress }}</div>
                            @endif
                            @if($shipTo !== '')
                                <div>{{ $shipTo }}</div>
                            @endif
                            @if($billPhone !== '')
                                <div>Contact No: {{ $billPhone }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 48px;">#</th>
                        <th>Item name</th>
                        <th style="width: 110px;" class="right">Quantity</th>
                        <th style="width: 180px;">Unit / Price</th>
                        <th style="width: 170px;" class="right">Amount(Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item['name'] ?? '-' }}</strong></td>
                            <td class="right">{{ $item['qty'] ?? '0' }}</td>
                            <td>
                                <div>{{ $item['unit'] ?? '-' }}</div>
                                <div class="muted">Rs {{ number_format((float) ($item['rate'] ?? 0), 2) }}</div>
                            </td>
                            <td class="right">Rs {{ number_format((float) ($item['amt'] ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>1</td>
                            <td><strong>Item</strong></td>
                            <td class="right">0</td>
                            <td>
                                <div>-</div>
                                <div class="muted">Rs 0.00</div>
                            </td>
                            <td class="right">Rs 0.00</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td></td>
                        <td><strong>Total</strong></td>
                        <td class="right"><strong>{{ number_format((float) ($items->sum(fn ($item) => (float) ($item['qty'] ?? 0))), 2) }}</strong></td>
                        <td></td>
                        <td class="right"><strong>Rs {{ number_format($total, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <table class="summary-wrap">
                <tr>
                    <td style="width: 54%; padding-right: 10px;">
                        <div class="words-box">
                            <div class="box-title">Invoice Amount in Words</div>
                            <div>{{ $totalInWords }}</div>
                        </div>
                        <div style="height: 16px;"></div>
                        <div class="terms-box">
                            <div class="box-title">Terms &amp; Conditions</div>
                            <div>{!! nl2br(e($termsText)) !!}</div>
                        </div>
                    </td>
                    <td style="width: 46%; padding-left: 10px;">
                        <table class="summary-table">
                            <tr>
                                <td>Sub Total</td>
                                <td class="right">Rs {{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr class="grand">
                                <td>Total</td>
                                <td class="right">Rs {{ number_format($total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Received</td>
                                <td class="right">Rs {{ number_format($received, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Balance</td>
                                <td class="right">Rs {{ number_format($balance, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="footer-sign">
                <div class="footer-for">For: {{ $businessName }}</div>

                <div class="signature-wrap">
                    @if(!empty($signatureImage))
                        <img src="{{ $signatureImage }}" alt="Signature">
                        <div class="signature-label">{{ $signatureText }}</div>
                    @else
                        <div class="signature-empty"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>
</html>
