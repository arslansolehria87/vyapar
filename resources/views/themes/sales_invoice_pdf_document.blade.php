@php
$invoice=$invoicePreviewData??[];$theme=$themeConfig??['mode'=>'regular','variant'=>'classicA','name'=>'Telly Theme'];$variant=$theme['variant']??'classicA';$accent=$accent??'#1f4e79';$accent2=$accent2??'#ff981f';$items=collect($invoice['items']??[])->values()->all();$subtotal=(float)($invoice['subtotal']??collect($items)->sum('amt'));$discount=(float)($invoice['discount']??0);$tax=(float)($invoice['taxAmount']??0);$total=(float)($invoice['total']??max($subtotal+$tax-$discount,0));$received=(float)($invoice['received']??0);$balance=(float)($invoice['balance']??max($total-$received,0));$totalQty=collect($items)->sum(fn($item)=>(float)($item['qty']??0));$totalGrossW=collect($items)->sum(fn($item)=>(float)($item['gross_w']??0));$totalNetW=collect($items)->sum(fn($item)=>(float)($item['net_w']??0));$title=$invoice['title']??'Invoice';$footerTermsText=trim((string)($invoice['footerTermsText']??$invoice['termsText']??$invoice['terms_condition_text']??$invoice['description']??'Thanks for doing business with us!'));$signatureText=trim((string)($invoice['signatureText']??$invoice['authorizedSignatory']??$invoice['businessName']??'Authorized Signatory'));$signatureImage=(string)($invoice['signatureImage']??$invoice['signature_image']??'');$isThermal=($theme['mode']??'regular')==='thermal';$isDoubleDivine=$variant==='doubleDivine';$isFrenchElite=$variant==='frenchElite';$isPurple=in_array($variant,['purpleA','purpleB','purpleC','taxTheme6','theme2'],true);$isModern=in_array($variant,['modernPurple','theme3'],true);$isSaleClassic=in_array($variant,['classicSale','theme4'],true);$showWeightColumns=!$isThermal;$invoiceRate=(float)($invoice['rate']??0);
$renderItemCell = function (array $item): string {
    $name = e($item['name'] ?? '');
    $summary = trim((string) ($item['customFieldSummary'] ?? ''));
    $fields = is_array($item['customFields'] ?? null) ? ($item['customFields'] ?? []) : (is_array($item['custom_fields'] ?? null) ? ($item['custom_fields'] ?? []) : []);
    $lines = [];

    if ($summary !== '') {
        foreach (explode('|', $summary) as $line) {
            $clean = trim((string) $line);
            if ($clean !== '') {
                $lines[] = e($clean);
            }
        }
    } else {
        foreach ($fields as $field) {
            if (!is_array($field)) {
                $value = trim((string) $field);
                if ($value !== '') {
                    $lines[] = e($value);
                }
                continue;
            }

            $enabled = array_key_exists('enabled', $field) ? (bool) $field['enabled'] : true;
            $showInPrint = array_key_exists('show_in_print', $field) ? (bool) $field['show_in_print'] : true;
            if (! $enabled || ! $showInPrint) {
                continue;
            }

            $label = trim((string) ($field['label'] ?? $field['name'] ?? ''));
            $value = trim((string) ($field['value'] ?? $field['text'] ?? ''));
            if ($label !== '' || $value !== '') {
                $lines[] = e(trim($label . ($label !== '' && $value !== '' ? ': ' : '') . $value));
            }
        }
    }

    $output = '<strong>' . $name . '</strong>';
    if (!empty($lines)) {
        $output .= '<div style="margin-top:2px;font-size:9px;line-height:1.2;color:#4b5563;">';
        foreach ($lines as $line) {
            $output .= '<div>' . $line . '</div>';
        }
        $output .= '</div>';
    }

    return $output;
};
@endphp
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>{{ $invoice['invoiceNo'] ?? 'Invoice' }}</title>
<style>
@page{size:A4 portrait;margin:12px}
html,body{width:100%;margin:0;padding:0;font-family:DejaVu Sans,sans-serif;font-size:12px;color:#1f2937;line-height:1.35;background:#fff}
body{display:block}
.doc-wrap{width:100%;max-width:100%;display:block;box-sizing:border-box;padding:0}
.doc{display:block;width:100%;max-width:760px;margin:0 auto;text-align:left;box-sizing:border-box}
.doc, .doc *{box-sizing:border-box;word-break:break-word;overflow-wrap:anywhere}
.grid-2,.table,.totals,.classic-header,.purple-head,.modern-meta,.sale-classic-head,.double-head,.elite-banner,.double-meta-table,.thermal-table{table-layout:fixed}
.text-right{text-align:right}.text-center{text-align:center}.mb-12{margin-bottom:12px}.mb-16{margin-bottom:16px}.section-head{margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:{{ $accent }};letter-spacing:.4px}.box{border:1px solid #d7dfeb;background:#fff;border-radius:10px;padding:12px 14px}.grid-2,.table,.totals,.classic-header,.purple-head,.modern-meta,.sale-classic-head,.double-head,.elite-banner{width:100%;border-collapse:collapse}.grid-2 td{width:50%;vertical-align:top}.grid-2 td+td .box{margin-left:10px}.table th,.table td{border:1px solid #d7dfeb;padding:8px 7px;vertical-align:top}.table th{color:#fff;font-size:11px;font-weight:700;text-transform:uppercase}.table tbody tr:nth-child(even) td{background:#f8fbff}.summary-row td{font-weight:700;background:#eef5ff!important}.totals td{padding:8px 0;border-bottom:1px solid #e5ebf3}.totals tr:last-child td{border-bottom:0}.totals .grand td{padding:10px 12px;color:#fff;font-weight:700}.classic-brand{width:60%;background:{{ $accent }};color:#fff;border-radius:10px;padding:18px 20px}.classic-brand .title{margin:0 0 8px;font-size:28px;font-weight:700}.classic-brand .name{font-size:20px;font-weight:700;margin-bottom:6px}.classic-meta{padding-left:12px;width:40%}.classic-meta-card{border:1px solid #d7dfeb;border-radius:10px;padding:14px 16px;background:#f8fbff}.purple-title{text-align:center;font-size:30px;font-weight:700;color:{{ $accent }};margin:0 0 14px}.purple-head td,.sale-classic-head td{border:1px solid #d7dfeb;padding:10px;vertical-align:top}.modern-top{border-bottom:2px solid {{ $accent }};padding-bottom:12px;margin-bottom:14px}.modern-title{font-size:34px;font-weight:700;color:{{ $accent }};margin:0 0 10px;text-align:center}.modern-meta td{width:33.33%;padding:8px 10px;border:1px solid #d7dfeb;vertical-align:top}.sale-classic-title{text-align:center;font-size:30px;font-weight:700;color:#1f2937;margin:0 0 12px}.double-left{width:58%;background:{{ $accent }};color:#fff;border-top-left-radius:10px;border-bottom-right-radius:38px;padding:18px 22px}.double-right{width:42%;background:{{ $accent2 }};color:#fff;border-top-right-radius:10px;border-bottom-left-radius:44px;padding:18px 22px;text-align:right}.double-logo{width:56px;height:56px;background:rgba(255,255,255,.18);color:#fff;text-align:center;line-height:56px;font-weight:700;margin-bottom:12px}.double-company{font-size:20px;font-weight:700}.double-title{font-size:26px;margin:0 0 8px;color:#1f2937}.double-meta-table{width:100%;border-collapse:collapse}.double-meta-table td{padding:4px 0}.elite-banner td{background:{{ $accent }};color:#fff;padding:12px 16px;vertical-align:middle}.elite-banner .elite-tax{font-size:24px;font-weight:800;letter-spacing:1px}.elite-store{color:{{ $accent }};font-size:30px;font-weight:700;margin:6px 0 12px}.thermal{width:270px;margin:0 auto;border:1px solid #d7dfeb;padding:12px}.thermal-title{text-align:center;font-size:18px;font-weight:700;color:{{ $accent }};margin-bottom:8px}.thermal-line{border-top:1px dashed #94a3b8;margin:8px 0}.thermal-table{width:100%;border-collapse:collapse;font-size:11px}.thermal-table th,.thermal-table td{padding:5px 0}.thermal-table th{border-bottom:1px dashed #94a3b8;text-align:left}.footer-note{margin-top:14px;text-align:center;font-size:10px;color:#7a8598}
</style></head><body><div class="doc-wrap">
@if($isThermal)
<div class="thermal"><div class="thermal-title">{{ $invoice['businessName'] ?? 'My Company' }}</div><div class="text-center">{{ $invoice['phone'] ?? '' }}</div><div class="thermal-line"></div><div><strong>{{ $title }}</strong></div><div>Invoice No: {{ $invoice['invoiceNo'] ?? '' }}</div><div>Date: {{ $invoice['date'] ?? '' }}</div><div>Party: {{ $invoice['billTo'] ?? '' }}</div><div class="thermal-line"></div><table class="thermal-table"><thead><tr><th>#</th><th>Item</th><th class="text-right">Qty</th><th class="text-right">Amt</th></tr></thead><tbody>@foreach($items as $index=>$item)
<tr><td>{{ $index+1 }}</td><td>{!! $renderItemCell($item) !!}</td><td class="text-right">{{ $item['qty'] ?? '' }}</td><td class="text-right">{{ number_format((float)($item['amt'] ?? 0),2) }}</td></tr>
@endforeach</tbody></table><div class="thermal-line"></div><div class="text-right"><strong>Total: {{ number_format($total,2) }}</strong></div><div class="text-right">Received: {{ number_format($received,2) }}</div><div class="text-right">Balance: {{ number_format($balance,2) }}</div></div>
@elseif($isDoubleDivine)
<div class="doc"><table class="double-head mb-16"><tr><td class="double-left"><div class="double-logo">LOGO</div><div class="double-company">{{ $invoice['businessName'] ?? 'My Company' }}</div></td><td class="double-right">{{ $invoice['phone'] ?? '' }}</td></tr></table><table class="grid-2 mb-16"><tr><td><div class="box"><div class="section-head" style="color:{{ $accent2 }}">Bill To</div><div style="font-size:18px;font-weight:700">{{ $invoice['billTo'] ?? '' }}</div><div>{{ $invoice['billAddress'] ?? '' }}</div><div>Contact No: {{ $invoice['billPhone'] ?? '' }}</div></div></td><td><div class="box"><div class="double-title">Invoice</div><table class="double-meta-table"><tr><td><strong>Invoice No.</strong></td><td class="text-right">{{ $invoice['invoiceNo'] ?? '' }}</td></tr><tr><td><strong>Date</strong></td><td class="text-right">{{ $invoice['date'] ?? '' }}</td></tr></table></div></td></tr></table>
@elseif($isFrenchElite)
<div class="doc"><table class="elite-banner"><tr><td class="elite-tax">TAX INVOICE</td><td class="text-right">LOGO</td></tr></table><div class="elite-store">{{ $invoice['businessName'] ?? 'My Company' }}</div><table class="grid-2 mb-16"><tr><td><div class="box"><div class="section-head">Invoice Details</div><div>Invoice No: {{ $invoice['invoiceNo'] ?? '' }}</div><div>Invoice Date: {{ $invoice['date'] ?? '' }}</div><div>Invoice Time: {{ $invoice['time'] ?? '' }}</div></div></td><td><div class="box"><div class="section-head">Bill To</div><div><strong>{{ $invoice['billTo'] ?? '' }}</strong></div><div>{{ $invoice['billAddress'] ?? '' }}</div><div>{{ $invoice['shipTo'] ?? '' }}</div></div></td></tr></table>
@elseif($isPurple)
<div class="doc"><div class="purple-title">{{ in_array($variant,['theme2'],true)?'Invoice':'Sale' }}</div><table class="purple-head mb-12"><tr><td style="width:90px;border:1px solid #d7dfeb;padding:10px;">Image</td><td style="border:1px solid #d7dfeb;padding:10px;"><div style="font-size:18px;font-weight:700;color:{{ $accent }}">{{ $invoice['businessName'] ?? 'My Company' }}</div><div>Ph. no.: {{ $invoice['phone'] ?? '' }}</div></td><td style="width:220px;border:1px solid #d7dfeb;padding:10px;"><div><strong>Invoice No:</strong> {{ $invoice['invoiceNo'] ?? '' }}</div><div><strong>Date:</strong> {{ $invoice['date'] ?? '' }}</div><div><strong>Time:</strong> {{ $invoice['time'] ?? '' }}</div><div><strong>Due Date:</strong> {{ $invoice['dueDate'] ?? '' }}</div></td></tr></table><table class="grid-2 mb-12"><tr><td><div class="box"><div class="section-head">Bill To</div><div><strong>{{ $invoice['billTo'] ?? '' }}</strong></div><div>{{ $invoice['billAddress'] ?? '' }}</div></div></td><td><div class="box"><div class="section-head">Shipping To</div><div>{{ $invoice['shipTo'] ?? '' }}</div><div class="section-head" style="margin-top:12px;">Invoice Details</div><div>Invoice No.: {{ $invoice['invoiceNo'] ?? '' }}</div></div></td></tr></table>
@elseif($isModern)
<div class="doc"><div class="modern-top"><table style="width:100%;border-collapse:collapse;"><tr><td><div style="font-size:18px;font-weight:700;">{{ $invoice['businessName'] ?? 'My Company' }}</div><div>Ph. no.: {{ $invoice['phone'] ?? '' }}</div></td><td class="text-right" style="width:90px;">Image</td></tr></table></div><div class="modern-title">Sale</div><table class="modern-meta mb-16"><tr><td><div class="section-head">Bill To</div><div><strong>{{ $invoice['billTo'] ?? '' }}</strong></div><div>{{ $invoice['billAddress'] ?? '' }}</div></td><td><div class="section-head">Shipping To</div><div>{{ $invoice['shipTo'] ?? '' }}</div></td><td><div class="section-head">Invoice Details</div><div>Invoice No.: {{ $invoice['invoiceNo'] ?? '' }}</div><div>Date: {{ $invoice['date'] ?? '' }}</div><div>Time: {{ $invoice['time'] ?? '' }}</div></td></tr></table>
@elseif($isSaleClassic)
<div class="doc"><div class="sale-classic-title">Sale</div><table class="sale-classic-head mb-12"><tr><td style="width:90px;">Image</td><td style="width:260px;"><div style="font-size:18px;font-weight:700;">{{ $invoice['businessName'] ?? 'My Company' }}</div><div>Ph. no.: {{ $invoice['phone'] ?? '' }}</div></td><td><div><strong>Invoice No.</strong> {{ $invoice['invoiceNo'] ?? '' }}</div><div><strong>Date</strong> {{ $invoice['date'] ?? '' }}, {{ $invoice['time'] ?? '' }}</div><div><strong>Due Date</strong> {{ $invoice['dueDate'] ?? '' }}</div></td></tr></table><table class="grid-2 mb-12"><tr><td><div class="box"><div class="section-head">Bill To</div><div><strong>{{ $invoice['billTo'] ?? '' }}</strong></div><div>{{ $invoice['billAddress'] ?? '' }}</div></div></td><td><div class="box"><div class="section-head">Ship To</div><div>{{ $invoice['shipTo'] ?? '' }}</div></div></td></tr></table>
@else
<div class="doc" style="padding:6px 10px 0;">
<div style="text-align:center;font-size:28px;font-weight:700;color:#111c4e;margin:0 0 18px;">{{ $title }}</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
<tr>
<td style="border:1px solid #d7dfeb;padding:16px 18px;vertical-align:middle;">
<table style="width:100%;border-collapse:collapse;">
<tr>
<td style="width:140px;vertical-align:middle;">
<div style="width:120px;height:78px;border:1px solid #d7dfeb;background:#f8fbff;text-align:center;line-height:78px;color:#94a3b8;font-size:12px;">Image</div>
</td>
<td style="vertical-align:middle;">
<div style="font-size:24px;font-weight:700;color:#111c4e;line-height:1.2;">{{ $invoice['businessName'] ?? 'My Company' }}</div>
<div style="margin-top:6px;font-size:14px;">Phone: {{ $invoice['phone'] ?? '' }}</div>
</td>
</tr>
</table>
</td>
</tr>
</table>
<table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
<tr>
<td style="width:50%;border:1px solid #d7dfeb;padding:16px 18px;vertical-align:top;">
<div style="font-size:14px;font-weight:700;color:#000;margin-bottom:10px;">Bill To:</div>
<div style="font-size:16px;font-weight:700;margin-bottom:6px;">{{ $invoice['billTo'] ?? '' }}</div>
@if(!empty($invoice['billAddress']))<div>{{ $invoice['billAddress'] }}</div>@endif
@if(!empty($invoice['billPhone']))<div style="margin-top:4px;">Phone: {{ $invoice['billPhone'] }}</div>@endif
</td>
<td style="width:50%;border:1px solid #d7dfeb;padding:16px 18px;vertical-align:top;">
<div style="font-size:14px;font-weight:700;color:#000;margin-bottom:10px;">Invoice Details:</div>
<div style="font-size:16px;margin-bottom:4px;">No: <strong>{{ $invoice['invoiceNo'] ?? '' }}</strong></div>
<div style="font-size:16px;">Date: <strong>{{ $invoice['date'] ?? '' }}</strong></div>
@if(!empty($invoice['time']))<div style="font-size:16px;margin-top:4px;">Time: <strong>{{ $invoice['time'] }}</strong></div>@endif
</td>
</tr>
</table>
@endif

@php $headBg=$isDoubleDivine?$accent2:$accent; $showUnitColumn=false; $showClassicSimpleTable=!$isDoubleDivine && !$isFrenchElite && !$isPurple && !$isModern && !$isSaleClassic && !$isThermal; @endphp
<table class="table mb-16 @if($isFrenchElite) elite-table @endif @if($isDoubleDivine) double-table @endif"><thead><tr>
<th style="background:{{ $headBg }};">#</th><th style="background:{{ $headBg }};">Item Name</th>
@if($showWeightColumns)<th style="background:{{ $headBg }};">Gross W</th><th style="background:{{ $headBg }};">Net W</th><th style="background:{{ $headBg }};">Rate</th>@endif
@if(!$isDoubleDivine && !$showClassicSimpleTable)<th style="background:{{ $headBg }};">HSN/SAC</th>@endif
<th style="background:{{ $headBg }};">Tadaat</th>
@if($showUnitColumn)<th style="background:{{ $headBg }};">Unit</th>@endif
<th style="background:{{ $headBg }};">Price / Unit(Rs)</th>
@if(!$isDoubleDivine && !$showClassicSimpleTable)<th style="background:{{ $headBg }};">Discount</th><th style="background:{{ $headBg }};">GST</th>@endif
<th style="background:{{ $headBg }};">Amount(Rs)</th>
</tr></thead><tbody>
@foreach($items as $index=>$item)

<tr><td>{{ $index+1 }}</td><td>{!! $renderItemCell($item) !!}</td>
@if($showWeightColumns)<td class="text-right">{{ number_format((float)($item['gross_w'] ?? 0),2) }}</td><td class="text-right">{{ number_format((float)($item['net_w'] ?? 0),2) }}</td><td class="text-right">{{ number_format((float)($item['rate'] ?? 0),2) }}</td>@endif
@if(!$isDoubleDivine && !$showClassicSimpleTable)<td>{{ $item['hsn'] ?? '' }}</td>@endif
<td class="text-right">{{ $item['qty'] ?? '' }}</td>
@if($showUnitColumn)<td>{{ $item['unit'] ?? '' }}</td>@endif
<td class="text-right">Rs {{ number_format((float)($item['rate'] ?? 0),2) }}</td>
@if(!$isDoubleDivine && !$showClassicSimpleTable)<td class="text-right">{{ number_format((float)($item['disc'] ?? 0),2) }}</td><td class="text-right">{{ $item['gst'] ?? '' }}</td>@endif
<td class="text-right">@if($showClassicSimpleTable)Rs @endif {{ number_format((float)($item['amt'] ?? 0),2) }}</td></tr>

@endforeach
<tr class="summary-row">
    <td></td>
    <td>Total</td>
    @if($showWeightColumns)
        <td class="text-right">{{ number_format($totalGrossW,2) }}</td>
        <td class="text-right">{{ number_format($totalNetW,2) }}</td>
        <td class="text-right">{{ $invoiceRate > 0 ? number_format($invoiceRate,2) : '' }}</td>
    @endif
    @if(!$isDoubleDivine && !$showClassicSimpleTable)
        <td></td>
    @endif
    <td class="text-right">{{ number_format($totalQty,2) }}</td>
    @if($showUnitColumn)
        <td></td>
    @endif
    <td></td>
    @if(!$isDoubleDivine && !$showClassicSimpleTable)
        <td class="text-right">{{ number_format($discount,2) }}</td>
        <td class="text-right">{{ number_format($tax,2) }}</td>
    @endif
    <td class="text-right">@if($showClassicSimpleTable)Rs @endif {{ number_format($total,2) }}</td>
</tr>
</tbody></table>

@if($isDoubleDivine)
<table class="grid-2"><tr><td style="width:50%;vertical-align:top;padding-right:10px;"><div class="box" style="min-height:140px;"><div class="section-head" style="color:{{ $accent2 }}">Order Amount In Words</div><div>{{ $invoice['totalInWords'] ?? 'Rupees Zero only' }}</div><div class="section-head" style="margin-top:14px;color:{{ $accent2 }}">Terms And Conditions</div><div>{{ $footerTermsText }}</div></div></td><td style="width:50%;vertical-align:top;padding-left:10px;"><div class="box"><table class="double-meta-table"><tr><td>Sub Total</td><td class="text-right">{{ number_format($subtotal,2) }}</td></tr><tr><td>Total</td><td class="text-right">{{ number_format($total,2) }}</td></tr><tr><td>Received</td><td class="text-right">{{ number_format($received,2) }}</td></tr><tr><td>Balance</td><td class="text-right">{{ number_format($balance,2) }}</td></tr></table></div></td></tr></table><div style="display:flex;justify-content:flex-end;margin-top:34px;"><div style="width:280px;text-align:center;"><div style="margin-bottom:12px;">For : {{ $invoice['businessName'] ?? 'My Company' }}</div>@if(!empty($signatureImage))<div style="margin:0 auto 10px;max-width:220px;"><img src="{{ $signatureImage }}" alt="Signature" style="max-width:220px;max-height:84px;object-fit:contain;"></div>@else<div style="height:50px;"></div>@endif<div style="border-top:1px solid #b8c2d1;padding-top:8px;font-weight:700;">{{ $signatureText }}</div></div></div>
@elseif($showClassicSimpleTable)
<table style="width:100%;border-collapse:collapse;">
<tr>
<td style="width:50%;vertical-align:top;padding-right:10px;">
<div class="box" style="min-height:140px;">
<div class="section-head">Order Amount In Words</div>
<div style="margin-bottom:14px;">{{ $invoice['totalInWords'] ?? 'Rupees Zero only' }}</div>
<div class="section-head">Terms And Conditions</div>
<div>{{ $footerTermsText }}</div>
</div>
</td>
<td style="width:50%;vertical-align:top;padding-left:10px;">
<div class="box">
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:10px 0;border-bottom:1px solid #e5ebf3;">Sub Total</td><td class="text-right" style="padding:10px 0;border-bottom:1px solid #e5ebf3;">{{ number_format($subtotal,2) }}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #e5ebf3;">Discount</td><td class="text-right" style="padding:10px 0;border-bottom:1px solid #e5ebf3;">{{ number_format($discount,2) }}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #e5ebf3;">Tax</td><td class="text-right" style="padding:10px 0;border-bottom:1px solid #e5ebf3;">{{ number_format($tax,2) }}</td></tr>
<tr><td style="padding:12px;background:{{ $headBg }};color:#fff;font-weight:700;">Total</td><td class="text-right" style="padding:12px;background:{{ $headBg }};color:#fff;font-weight:700;">{{ number_format($total,2) }}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #e5ebf3;">Received</td><td class="text-right" style="padding:10px 0;border-bottom:1px solid #e5ebf3;">{{ number_format($received,2) }}</td></tr>
<tr><td style="padding:10px 0;">Balance</td><td class="text-right" style="padding:10px 0;">{{ number_format($balance,2) }}</td></tr>
</table>
</div>
</td>
</tr>
</table>
@else
<table class="grid-2"><tr><td><div class="box">
<div class="section-head">Order Amount In Words</div>
<div>{{ $invoice['totalInWords'] ?? 'Rupees Zero only' }}</div>
<div class="section-head" style="margin-top:14px;">Terms And Conditions</div>
<div>{{ $footerTermsText }}</div>
</div></td><td><div class="box"><table class="totals">
<tr><td>Sub Total</td><td class="text-right">{{ number_format($subtotal,2) }}</td></tr>
@if(!$isDoubleDivine)<tr><td>Discount</td><td class="text-right">{{ number_format($discount,2) }}</td></tr><tr><td>Tax</td><td class="text-right">{{ number_format($tax,2) }}</td></tr>@endif
<tr class="grand"><td style="background:{{ $headBg }};">Total</td><td style="background:{{ $headBg }};" class="text-right">{{ number_format($total,2) }}</td></tr>
<tr><td>Received</td><td class="text-right">{{ number_format($received,2) }}</td></tr><tr><td>Balance</td><td class="text-right">{{ number_format($balance,2) }}</td></tr>
</table></div></td></tr></table>
@endif
<div style="display:flex;justify-content:flex-end;margin-top:34px;">
<div style="width:280px;text-align:center;">
<div style="margin-bottom:12px;">For : {{ $invoice['businessName'] ?? 'My Company' }}</div>
@if(!empty($signatureImage))
<div style="margin:0 auto 10px;max-width:220px;"><img src="{{ $signatureImage }}" alt="Signature" style="max-width:220px;max-height:84px;object-fit:contain;"></div>
@else
<div style="height:50px;"></div>
@endif
<div style="border-top:1px solid #b8c2d1;padding-top:8px;font-weight:700;">{{ $signatureText }}</div>
</div>
</div>
@endif
@if(!empty($autoPrint))
<script>
window.addEventListener('load', function () {
    window.print();
});
</script>
@endif
</body></html>
