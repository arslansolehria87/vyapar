<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Preview' }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        .invoice-theme-layout { --accent:#1f4e79; --accent-2:#ff981f; grid-template-columns:380px minmax(0,1fr) 380px; }
        body.pdf-export-page { background:#ffffff; }
        body.pdf-export-page .top-strip, body.pdf-export-page .page-head, body.pdf-export-page .sidebar, body.pdf-export-page .sheet-expand, body.pdf-export-page .tip-box, body.pdf-export-page .theme-mode-switch, body.pdf-export-page .theme-color-panel, body.pdf-export-page .share-row { display:none !important; }
        body.pdf-export-page .invoice-theme-layout { display:grid; grid-template-columns:minmax(0,1fr) 280px; gap:18px; align-items:start; justify-content:center; max-width:1320px; margin:0 auto; padding:20px; }
        body.pdf-export-page .preview-wrap { padding:0; }
        body.pdf-export-page .sheet { max-width:none; min-height:auto; margin:0; }
        body.pdf-export-page .invoice-canvas { border:0; min-height:auto; padding:0; overflow:visible; }
        body.pdf-export-page .right-panel { display:block; position:sticky; top:20px; border-left:1px solid #e5e7eb; background:#fff; }
        body.pdf-export-page .right-title { padding-bottom:16px; }
        .invoice-theme-layout .sidebar { position:static; width:auto; padding:0; border-right:1px solid #dddddd; }
        .invoice-theme-layout .right-panel { width:auto; }
        .invoice-theme-layout .theme-mode-switch { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:16px 18px; background:#fff; border-bottom:1px solid #e1e5ec; }
        .invoice-theme-layout .theme-mode-btn { border:1px solid #d6dce6; border-radius:10px; background:#f8fafc; color:#355368; font-size:14px; font-weight:700; padding:11px 12px; cursor:pointer; }
        .invoice-theme-layout .theme-mode-btn.active { background:var(--accent); border-color:var(--accent); color:#fff; }
        .invoice-theme-layout .theme-list li { padding:0; }
        .invoice-theme-layout .theme-list-button { width:100%; border:0; background:#fff; text-align:left; padding:15px 22px; font-size:15px; font-weight:600; color:#355368; cursor:pointer; border-bottom:1px solid #e5e8ec; }
        .invoice-theme-layout .theme-list-button.active { background:rgba(31,78,121,.12); color:var(--accent); }
        .invoice-theme-layout .theme-color-panel { background:#fff; border-top:1px solid #e1e4ea; margin-top:8px; padding:18px 20px 20px; }
        .invoice-theme-layout .theme-color-panel__title { font-size:15px; font-weight:700; color:#2f536a; margin-bottom:14px; }
        .invoice-theme-layout .theme-color-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .invoice-theme-layout .theme-color-dot { width:22px; height:22px; border-radius:999px; border:2px solid transparent; background:var(--dot); cursor:pointer; box-shadow:inset 0 0 0 1px rgba(15,23,42,.12); flex:0 0 auto; }
        .invoice-theme-layout .theme-color-dot.active { border-color:#d9dee8; box-shadow:0 0 0 2px var(--dot); }
        .invoice-theme-layout .picker-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:12px; font-size:13px; font-weight:700; color:#355368; }
        .invoice-theme-layout .picker-row input[type="color"] { width:34px; height:24px; padding:0; border:0; background:transparent; cursor:pointer; }
        .invoice-theme-layout .picker-row.secondary { display:none; }
        .invoice-theme-layout.is-double-divine .picker-row.secondary { display:flex; }
        .invoice-theme-layout .preview-wrap { padding:15px 22px 18px; }
        .invoice-theme-layout .sheet { max-width:1068px; min-height:828px; margin:0 auto; }
        .invoice-theme-layout .sheet-expand { cursor:pointer; z-index:5; }
        .invoice-theme-layout .invoice-canvas { --inv-accent:#1f4e79; display:flex; justify-content:center; align-items:flex-start; background:#fff; border:1px solid #a6adba; min-height:640px; padding:12px; overflow:auto; }
        .invoice-theme-layout .inv-doc { width:100%; max-width:880px; border:1px solid #6d7381; background:#fff; color:#141821; }
        .invoice-theme-layout .inv-title { text-align:center; font-size:31px; font-weight:700; padding:6px 0; border-bottom:1px solid #6d7381; color:var(--inv-accent); }
        .invoice-theme-layout .inv-title--sale { font-size:29px; }
        .invoice-theme-layout .inv-title--large { font-size:42px; }
        .invoice-theme-layout .inv-head { display:grid; grid-template-columns:90px 1fr 1fr; gap:8px; padding:8px; border-bottom:1px solid #6d7381; align-items:center; }
        .invoice-theme-layout .inv-head--sale { grid-template-columns:90px 1fr 220px; }
        .invoice-theme-layout .inv-head--modern { grid-template-columns:1fr 90px; border:0; border-bottom:1px solid #b8b3ea; }
        .invoice-theme-layout .inv-head--saleclassic { grid-template-columns:90px 260px 1fr; }
        .invoice-theme-layout .inv-logo { width:70px; height:45px; border:1px solid #ced3dd; background:#eff2f7; font-size:10px; color:#8b94a6; display:flex; align-items:center; justify-content:center; }
        .invoice-theme-layout .inv-company { font-size:15px; font-weight:700; }
        .invoice-theme-layout .inv-phone, .invoice-theme-layout .inv-meta { font-size:11px; }
        .invoice-theme-layout .inv-meta { line-height:1.5; }
        .invoice-theme-layout .inv-meta--grid { display:grid; grid-template-columns:1fr 1fr; border-left:1px solid #6d7381; }
        .invoice-theme-layout .inv-meta--grid > div { padding:4px 8px; border-bottom:1px solid #6d7381; border-right:1px solid #6d7381; }
        .invoice-theme-layout .inv-grid { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #6d7381; }
        .invoice-theme-layout .inv-grid--purple { grid-template-columns:1fr 1fr 220px; }
        .invoice-theme-layout .inv-grid--modern { grid-template-columns:1fr 1fr 1fr; border-bottom:0; padding-top:8px; }
        .invoice-theme-layout .inv-col { padding:6px; border-right:1px solid #6d7381; }
        .invoice-theme-layout .inv-col:last-child { border-right:0; }
        .invoice-theme-layout .inv-col h5 { font-size:11px; font-weight:700; margin:0 0 4px; color:var(--inv-accent); }
        .invoice-theme-layout .inv-col p { font-size:10px; margin:0 0 2px; }
        .invoice-theme-layout .inv-doc strong { color:var(--inv-accent); }
        .invoice-theme-layout .inv-ship { font-size:10px; padding:4px 6px; border-bottom:1px solid #6d7381; }
        .invoice-theme-layout .inv-table { width:100%; border-collapse:collapse; font-size:10px; }
        .invoice-theme-layout .inv-table th, .invoice-theme-layout .inv-table td { border:1px solid #6d7381; padding:3px; text-align:left; }
        .invoice-theme-layout .inv-table th { text-align:center; font-weight:700; background:var(--inv-accent); color:#fff; }
        .invoice-theme-layout .inv-bottom { display:grid; grid-template-columns:1fr 1fr; border-top:1px solid #6d7381; }
        .invoice-theme-layout .inv-bottom--compact .box { min-height:72px; }
        .invoice-theme-layout .inv-bottom--purple .box strong { display:block; background:var(--inv-accent); color:#fff; padding:2px 6px; margin:0 -6px 6px; font-size:11px; }
        .invoice-theme-layout .inv-bottom .box { border-right:1px solid #6d7381; padding:6px; font-size:10px; min-height:110px; }
        .invoice-theme-layout .inv-bottom .box:last-child { border-right:0; }
        .invoice-theme-layout .inv-bottom .box p { display:flex; justify-content:space-between; gap:12px; }
        .invoice-theme-layout .inv-sign { display:flex; justify-content:flex-end; align-items:flex-end; height:100%; }
        .invoice-theme-layout .inv-purple .inv-table th, .invoice-theme-layout .inv-purple .inv-grid h5, .invoice-theme-layout .inv-double-divine .inv-table th, .invoice-theme-layout .inv-double-divine .inv-table tr:last-child td, .invoice-theme-layout .inv-french-elite .inv-table th, .invoice-theme-layout .inv-french-elite .inv-table tr:last-child td { background:var(--inv-accent); color:#fff; }
        .invoice-theme-layout .inv-modern, .invoice-theme-layout .inv-double-divine, .invoice-theme-layout .inv-french-elite { border:0; }
        .invoice-theme-layout .inv-modern .inv-title { border-bottom:0; }
        .invoice-theme-layout .inv-modern .inv-table th, .invoice-theme-layout .theme-two .inv-table th, .invoice-theme-layout .tax-theme-6 .box strong { background:var(--inv-accent); }
        .invoice-theme-layout .inv-modern .inv-table, .invoice-theme-layout .inv-modern .inv-table td, .invoice-theme-layout .inv-modern .inv-table th { border-color:#8f939f; }
        .invoice-theme-layout .inv-sale-classic .inv-title { font-size:30px; }
        .invoice-theme-layout .theme-three .inv-title { font-size:34px; }
        .invoice-theme-layout .theme-four .inv-head { border-bottom:2px solid #2c3340; }
        .invoice-theme-layout .double-divine-custom { border:0; box-shadow:none; padding:8px 8px 24px; }
        .invoice-theme-layout .dd-top { position:relative; height:102px; margin-bottom:12px; }
        .invoice-theme-layout .dd-top-left { position:absolute; left:0; top:0; width:56%; height:102px; background:var(--inv-accent); border-bottom-right-radius:42px; padding:18px 22px 12px; color:#fff; z-index:1; }
        .invoice-theme-layout .dd-top-right { position:absolute; left:23%; top:0; width:70%; height:74px; background:var(--accent-2); border-bottom-left-radius:62px; color:#fff; display:flex; align-items:center; justify-content:flex-end; padding:0 28px; z-index:2; }
        .invoice-theme-layout .dd-logo { width:56px; height:56px; background:#737373; color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; margin-bottom:18px; }
        .invoice-theme-layout .dd-company { font-size:17px; line-height:1.2; }
        .invoice-theme-layout .dd-phone { font-size:13px; font-weight:600; }
        .invoice-theme-layout .dd-main { display:grid; grid-template-columns:1.55fr .9fr; gap:18px; padding:0 12px 6px; align-items:start; }
        .invoice-theme-layout .dd-title { font-size:22px; font-weight:500; color:#000; margin:0 0 8px; }
        .invoice-theme-layout .dd-meta { font-size:12px; line-height:2; color:#000; }
        .invoice-theme-layout .dd-meta strong { color:#000; }
        .invoice-theme-layout .dd-section-title { color:var(--accent-2); font-size:14px; font-weight:500; margin:0 0 8px; }
        .invoice-theme-layout .dd-party-name { font-size:14px; font-weight:800; color:#000; margin-bottom:4px; }
        .invoice-theme-layout .dd-subtext { color:#000; font-size:11px; margin:0 0 6px; }
        .invoice-theme-layout .dd-table { width:calc(100% - 24px); margin:0 12px; border-collapse:collapse; font-size:11px; }
        .invoice-theme-layout .dd-table th, .invoice-theme-layout .dd-table td { border:1px solid #8f97a7; padding:5px 6px; }
        .invoice-theme-layout .dd-table thead th, .invoice-theme-layout .dd-table .dd-total-row td { background:var(--accent-2); color:#fff; font-weight:700; }
        .invoice-theme-layout .dd-bottom { display:grid; grid-template-columns:1.45fr .85fr; gap:18px; padding:10px 12px 0; align-items:start; }
        .invoice-theme-layout .dd-lines p { margin:0 0 10px; color:#000; font-size:11px; }
        .invoice-theme-layout .dd-lines h4 { margin:0 0 8px; }
        .invoice-theme-layout .dd-amounts { width:100%; max-width:370px; margin-left:auto; border-collapse:collapse; font-size:11px; }
        .invoice-theme-layout .dd-amounts td { border:1px solid #8f97a7; padding:4px 6px; }
        .invoice-theme-layout .dd-amounts .highlight td { background:var(--accent-2); color:#fff; font-weight:700; }
        .invoice-theme-layout .dd-sign { padding:28px 12px 0; color:#000; font-size:14px; }
        .invoice-theme-layout .dd-sign-line { width:150px; padding-top:6px; font-weight:700; }
        .invoice-preview-modal { position:fixed; inset:0; background:rgba(15,23,42,.45); display:none; align-items:center; justify-content:center; padding:24px; z-index:9999; }
        .invoice-preview-modal.open { display:flex; }
        .invoice-preview-dialog { width:min(1280px,96vw); max-height:92vh; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 25px 70px rgba(0,0,0,.25); display:flex; flex-direction:column; }
        .invoice-preview-dialog__head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #e5e7eb; }
        .invoice-preview-dialog__title { font-size:18px; font-weight:700; color:#1f2937; }
        .invoice-preview-dialog__close { width:34px; height:34px; border:0; border-radius:999px; background:#eef2f7; color:#64748b; font-size:22px; cursor:pointer; }
        .invoice-preview-dialog__body { padding:20px; overflow:auto; background:#f3f4f6; }
        .invoice-preview-dialog__sheet { max-width:980px; margin:0 auto; background:#fff; box-shadow:0 8px 30px rgba(0,0,0,.12); padding:18px; }
        .invoice-preview-dialog.invoice-theme-layout { grid-template-columns:none; }
        .invoice-theme-layout .elite-banner { display:flex; justify-content:space-between; align-items:center; background:var(--inv-accent); color:#fff; padding:8px 10px; }
        .invoice-theme-layout .elite-title { font-size:28px; font-weight:800; letter-spacing:.02em; }
        .invoice-theme-layout .elite-store { margin:8px 0 6px; color:var(--inv-accent); font-size:34px; font-weight:700; }
        .invoice-theme-layout .inv-thermal { width:var(--thermal-width,280px); border:1px solid var(--inv-accent); box-shadow:0 1px 6px rgba(0,0,0,.08); padding:14px 12px; font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:#111; background:#fff; }
        .invoice-theme-layout .inv-thermal .t-center { text-align:center; } .invoice-theme-layout .inv-thermal .t-small { font-size:11px; } .invoice-theme-layout .inv-thermal .t-bold { font-weight:800; } .invoice-theme-layout .inv-thermal .t-line { border-top:2px dotted var(--inv-accent); margin:10px 0; } .invoice-theme-layout .inv-thermal .t-row { display:flex; justify-content:space-between; gap:10px; font-size:11px; } .invoice-theme-layout .inv-thermal .t-row--triple { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; } .invoice-theme-layout .inv-thermal table { width:100%; border-collapse:collapse; font-size:11px; } .invoice-theme-layout .inv-thermal th { text-align:left; font-weight:800; padding:6px 0; border-bottom:2px dotted var(--inv-accent); } .invoice-theme-layout .inv-thermal td { padding:6px 0; vertical-align:top; } .invoice-theme-layout .inv-thermal .t-muted { color:#444; } .invoice-theme-layout .inv-thermal .t-right { text-align:right; } .invoice-theme-layout .inv-thermal .t-indent { padding-left:16px; } .invoice-theme-layout .inv-thermal .t-italic { font-style:italic; }
        .hidden { display:none; }
    </style>
</head>
    @php
        $classicThemes = [['id'=>1,'name'=>'Telly Theme','variant'=>'classicA'],['id'=>2,'name'=>'Landscape Theme 1','variant'=>'purpleA'],['id'=>3,'name'=>'Landscape Theme 2','variant'=>'classicB'],['id'=>4,'name'=>'Tax Theme 1','variant'=>'purpleB'],['id'=>5,'name'=>'Tax Theme 2','variant'=>'classicC'],['id'=>6,'name'=>'Tax Theme 3','variant'=>'modernPurple'],['id'=>10,'name'=>'Double Divine','variant'=>'doubleDivine'],['id'=>11,'name'=>'French Elite','variant'=>'frenchElite']];
        $vintageThemes = [['id'=>7,'name'=>'Tax Theme 4','variant'=>'purpleC'],['id'=>8,'name'=>'Tax Theme 5','variant'=>'classicSale'],['id'=>9,'name'=>'Tax Theme 6','variant'=>'taxTheme6'],['id'=>12,'name'=>'Theme 1','variant'=>'theme1'],['id'=>13,'name'=>'Theme 2','variant'=>'theme2'],['id'=>14,'name'=>'Theme 3','variant'=>'theme3'],['id'=>15,'name'=>'Theme 4','variant'=>'theme4']];
        $thermalThemes = [['id'=>1,'name'=>'Thermal Theme 1','variant'=>'thermal1'],['id'=>2,'name'=>'Thermal Theme 2','variant'=>'thermal2'],['id'=>3,'name'=>'Thermal Theme 3','variant'=>'thermal3'],['id'=>4,'name'=>'Thermal Theme 4','variant'=>'thermal4'],['id'=>5,'name'=>'Thermal Theme 5','variant'=>'thermal5']];
        $regularThemes = array_merge($classicThemes, $vintageThemes);
        $invoicePreviewData = $invoicePreviewData ?? ['title'=>'Tax Invoice','businessName'=>'My Company','phone'=>'3714346914','invoiceNo'=>'3','date'=>'02/04/2026','time'=>'11:40 AM','dueDate'=>'05/04/2026','billTo'=>'fddfhhfd','billAddress'=>'23123','billPhone'=>'213123','shipTo'=>'Korangi Industrial Area, Karachi','items'=>[['name'=>'hasnain','hsn'=>'1209','qty'=>'1','unit'=>'Ltr','rate'=>123,'disc'=>'0','gst'=>'5%','amt'=>123]],'description'=>'Thanks for doing business with us!','subtotal'=>123,'discount'=>0,'taxAmount'=>6.15,'total'=>129.15,'received'=>0,'balance'=>129.15];
        $themePalette = ['#1f4e79','#2563eb','#0ea5e9','#16a34a','#d97706','#db2777','#7c3aed','#111827'];
        $browserTabLabel = $browserTabLabel ?? ($invoicePreviewData['billTo'] ?? 'Invoice Preview');
        $saveCloseUrl = $saveCloseUrl ?? '#';
        $pdfMode = $pdfMode ?? false;
        $autoDownload = $autoDownload ?? false;
        $initialMode = $initialMode ?? 'regular';
        $initialRegularThemeId = $initialRegularThemeId ?? 1;
        $initialThermalThemeId = $initialThermalThemeId ?? 1;
        $initialAccent = $initialAccent ?? '#1f4e79';
        $initialAccent2 = $initialAccent2 ?? '#ff981f';
    @endphp

<body class="{{ $pdfMode ? 'pdf-export-page' : '' }}">
    <div class="top-strip"><div class="fake-tab"><span>{{ $browserTabLabel }}</span><div class="tab-actions"><span class="tab-close">&times;</span><span class="tab-plus">+</span></div></div><div class="top-icons"><span>&#8984;</span><span>&#9638;</span><span>&#9881;</span><span>&#10005;</span></div></div>
    <div class="page-head"><h1>{{ $pageTitle ?? 'Preview' }}</h1><div class="page-head-right"><input type="checkbox"><span>Do not show invoice preview again</span><span style="color:#b8b8b8;">|</span><a href="{{ $saveCloseUrl }}" class="save-close">Save &amp; Close</a></div></div>

    <div class="layout invoice-theme-layout">
        <aside class="sidebar">
            <div class="sidebar-section-title">Select Theme</div>
            <div class="theme-mode-switch">
                <button type="button" class="theme-mode-btn active" data-mode="regular">Regular Printer</button>
                <button type="button" class="theme-mode-btn" data-mode="thermal">Thermal Printer</button>
            </div>
            <div id="regularThemeSections">
                <div class="theme-dropdown" data-dropdown><div class="group-header" data-toggle><span>Classic Themes</span><span class="dropdown-arrow" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="M3 10.5L8 5.5l5 5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path></svg></span></div><ul class="theme-list">
                    @foreach ($classicThemes as $theme)
<li><button type="button" class="theme-list-button {{ $theme['id'] === 1 ? 'active' : '' }}" data-kind="regular" data-theme-id="{{ $theme['id'] }}">{{ $theme['name'] }}</button></li>
@endforeach
                </ul></div>
                <div class="theme-dropdown" data-dropdown><div class="group-header" data-toggle><span>Vintage Themes</span><span class="dropdown-arrow" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="M3 10.5L8 5.5l5 5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path></svg></span></div><ul class="theme-list">
                    @foreach ($vintageThemes as $theme)
<li><button type="button" class="theme-list-button" data-kind="regular" data-theme-id="{{ $theme['id'] }}">{{ $theme['name'] }}</button></li>
@endforeach
                </ul></div>
            </div>
            <div id="thermalThemeSection" class="hidden">
                <div class="theme-dropdown" data-dropdown><div class="group-header" data-toggle><span>Thermal Themes</span><span class="dropdown-arrow" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="M3 10.5L8 5.5l5 5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path></svg></span></div><ul class="theme-list">
                    @foreach ($thermalThemes as $theme)
<li><button type="button" class="theme-list-button {{ $theme['id'] === 1 ? 'active' : '' }}" data-kind="thermal" data-theme-id="{{ $theme['id'] }}">{{ $theme['name'] }}</button></li>
@endforeach
                </ul></div>
            </div>
            <div class="theme-color-panel">
                <div class="theme-color-panel__title">Change Colors</div>
                <div class="theme-color-grid">@foreach ($themePalette as $index => $color)
<button type="button" class="theme-color-dot {{ $index === 0 ? 'active' : '' }}" data-accent="{{ $color }}" style="--dot:{{ $color }}"></button>
@endforeach</div>
                <label class="picker-row"><span>Custom Color</span><input type="color" id="customAccentPicker" value="#1f4e79"></label>
                <label class="picker-row secondary"><span>Double Divine 2nd Color</span><input type="color" id="doubleDivineSecondPicker" value="#ff981f"></label>
            </div>
            <div class="tip-box"><div class="tip-card"><span class="tip-bulb">&#128161;</span><span id="themeHint">Print settings ke tamam regular aur thermal designs yahan preview ho rahe hain.</span></div></div>
        </aside>
        <main class="preview-wrap"><div class="sheet" id="invoiceSheet"><div class="sheet-expand" id="openPreviewModal">&#8599;</div><div id="invoiceCanvas" class="invoice-canvas"></div></div></main>
        <aside class="right-panel">
            <div class="right-title">Share Invoice</div>
            <div class="share-row"><button type="button" class="share-item" id="shareWhatsappBtn"><div class="share-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="22" fill="#36c95f"></circle><path d="M24 19c1.5-1.4 3-1.2 4.1.3l2.5 3.8c.8 1.2.8 2.3-.3 3.2l-1.6 1.3c-.6.5-.7 1-.3 1.7 1.7 2.8 3.9 5 6.7 6.7.7.4 1.2.3 1.7-.3l1.3-1.6c.9-1.1 2-1.1 3.2-.3l3.8 2.5c1.5 1 1.7 2.6.3 4.1-1.4 1.5-3.2 2.3-5.6 2.1-4.4-.3-8.6-2.7-12.6-6.8-4.1-4.1-6.4-8.2-6.8-12.6-.2-2.4.6-4.2 2.1-5.6z" fill="#fff"></path></svg></div><div class="share-label">Whatsapp</div></button><button type="button" class="share-item" id="shareGmailBtn"><div class="share-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M12 18h40v28H12z" fill="#fff"></path><path d="M12 18l20 16 20-16" fill="none" stroke="#db493b" stroke-width="6"></path><path d="M12 46V18l12 10" fill="none" stroke="#db493b" stroke-width="6"></path><path d="M52 46V18L40 28" fill="none" stroke="#db493b" stroke-width="6"></path></svg></div><div class="share-label">Gmail</div></button></div>
            @if (empty($pdfMode))
                <div class="action-row"><button type="button" class="action-item" id="downloadPdfBtn"><div class="action-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M32 14v24" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"></path><path d="M22 30l10 10 10-10" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 48h28" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"></path></svg></div><div class="action-label">Download PDF</div></button><button type="button" class="action-item" id="thermalPrintBtn"><div class="action-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><rect x="20" y="12" width="24" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="4"></rect><rect x="16" y="24" width="32" height="24" rx="4" fill="none" stroke="currentColor" stroke-width="4"></rect><path d="M24 34h16M24 40h12" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg></div><div class="action-label">Print Invoice <span class="action-subtle">(Thermal)</span></div></button><button type="button" class="action-item" id="normalPrintBtn"><div class="action-icon primary"><svg viewBox="0 0 64 64" aria-hidden="true"><rect x="20" y="10" width="24" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="4"></rect><rect x="14" y="24" width="36" height="18" rx="4" fill="none" stroke="currentColor" stroke-width="4"></rect><rect x="20" y="38" width="24" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="4"></rect><circle cx="45" cy="30" r="2.5" fill="currentColor"></circle></svg></div><div class="action-label">Print Invoice <span class="action-subtle">(Normal)</span></div></button></div>
            @endif
        </aside>
    </div>
    <div class="invoice-preview-modal" id="invoicePreviewModal">
        <div class="invoice-preview-dialog invoice-theme-layout">
            <div class="invoice-preview-dialog__head">
                <div class="invoice-preview-dialog__title">Print Preview</div>
                <button type="button" class="invoice-preview-dialog__close" id="closePreviewModal">&times;</button>
            </div>
            <div class="invoice-preview-dialog__body">
                <div class="invoice-preview-dialog__sheet">
                    <div id="modalInvoiceCanvas"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        const regularThemes = @json($regularThemes), thermalThemes = @json($thermalThemes), invoiceData = @json($invoicePreviewData);
        const initialMode = @json($initialMode), initialRegularThemeId = Number(@json($initialRegularThemeId)), initialThermalThemeId = Number(@json($initialThermalThemeId)), initialAccent = @json($initialAccent), initialAccent2 = @json($initialAccent2), invoicePdfBaseUrl = @json(isset($sale) ? route('sale.invoice-pdf', $sale) : null), autoDownload = @json($autoDownload), saleId = @json(isset($sale) ? $sale->id : null);
        const storedThemeState = (() => {
            const keys = [];
            if (saleId) keys.push(`saleInvoiceTheme:${saleId}`);
            keys.push('saleInvoiceTheme:draft');

            for (const key of keys) {
                try {
                    const raw = window.localStorage.getItem(key);
                    if (!raw) continue;
                    const parsed = JSON.parse(raw);
                    if (parsed && typeof parsed === 'object') {
                        return parsed;
                    }
                } catch (error) {}
            }

            return null;
        })();

        let currentMode = (storedThemeState?.mode === 'thermal' || storedThemeState?.mode === 'regular')
            ? storedThemeState.mode
            : (initialMode === 'thermal' ? 'thermal' : 'regular');
        let activeRegularId = Number(storedThemeState?.regularThemeId || initialRegularThemeId || 1);
        let activeThermalId = Number(storedThemeState?.thermalThemeId || initialThermalThemeId || 1);
        const root = document.querySelector('.invoice-theme-layout'), invoiceCanvas = document.getElementById('invoiceCanvas'), regularThemeSections = document.getElementById('regularThemeSections'), thermalThemeSection = document.getElementById('thermalThemeSection'), modeButtons = Array.from(document.querySelectorAll('.theme-mode-btn')), themeButtons = Array.from(document.querySelectorAll('.theme-list-button')), colorButtons = Array.from(document.querySelectorAll('.theme-color-dot')), themeHint = document.getElementById('themeHint'), sheet = document.getElementById('invoiceSheet'), downloadPdfBtn = document.getElementById('downloadPdfBtn'), normalPrintBtn = document.getElementById('normalPrintBtn'), thermalPrintBtn = document.getElementById('thermalPrintBtn'), shareWhatsappBtn = document.getElementById('shareWhatsappBtn'), shareGmailBtn = document.getElementById('shareGmailBtn'), customAccentPicker = document.getElementById('customAccentPicker'), doubleDivineSecondPicker = document.getElementById('doubleDivineSecondPicker'), openPreviewModalBtn = document.getElementById('openPreviewModal'), invoicePreviewModal = document.getElementById('invoicePreviewModal'), closePreviewModalBtn = document.getElementById('closePreviewModal'), modalInvoiceCanvas = document.getElementById('modalInvoiceCanvas');
        document.querySelectorAll('[data-dropdown]').forEach(function (d) { const t = d.querySelector('[data-toggle]'); if (t) t.addEventListener('click', function () { d.classList.toggle('closed'); }); });
        function fmt(n){ return `Rs ${(Math.round(Number(n)*100)/100).toFixed(2)}`; }
        function getRegularThemeById(id){ return regularThemes.find(t => Number(t.id)===Number(id)) || regularThemes[0]; }
        function getThermalThemeById(id){ return thermalThemes.find(t => Number(t.id)===Number(id)) || thermalThemes[0]; }
        function setActiveButton(kind,id){ themeButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.kind===kind && Number(btn.dataset.themeId)===Number(id))); }
        function persistThemeSelection(){
            try {
                const payload = JSON.stringify({
                    mode: currentMode,
                    regularThemeId: activeRegularId,
                    thermalThemeId: activeThermalId,
                    accent: customAccentPicker ? customAccentPicker.value : initialAccent,
                    accent2: doubleDivineSecondPicker ? doubleDivineSecondPicker.value : initialAccent2
                });
                window.localStorage.setItem('saleInvoiceTheme:draft', payload);
                if (saleId) {
                    window.localStorage.setItem(`saleInvoiceTheme:${saleId}`, payload);
                }
            } catch (error) {}
        }
        function setAccent(color){ root.style.setProperty('--accent', color); invoiceCanvas.style.setProperty('--inv-accent', color); if(customAccentPicker) customAccentPicker.value = color; }
        function setSecondAccent(color){ root.style.setProperty('--accent-2', color); invoiceCanvas.style.setProperty('--inv-accent-2', color); if(doubleDivineSecondPicker) doubleDivineSecondPicker.value = color; }
        function renderRegularPreview(theme){
            const items = Array.isArray(invoiceData.items) && invoiceData.items.length ? invoiceData.items : [{ name:'Item', hsn:'', qty:'0', unit:'', rate:0, disc:'0.00', gst:'0%', amt:0 }];
            const subtotal = Number(invoiceData.subtotal ?? items.reduce((s,i)=>s+Number(i.amt ?? i.rate ?? 0),0));
            const tax = Number(invoiceData.taxAmount ?? items.reduce((s,i)=>s+(Number(i.amt ?? i.rate ?? 0)*(parseFloat(i.gst || 0)/100)),0));
            const discount = Number(invoiceData.discount ?? 0);
            const total = Number(invoiceData.total ?? Math.max(subtotal + tax - discount, 0));
            const received = Number(invoiceData.received ?? 0);
            const balance = Number(invoiceData.balance ?? Math.max(total - received, 0));
            const totalQty = items.reduce((s,i)=>s+Number(i.qty || 0),0);
            const description = invoiceData.description || 'Thanks for doing business with us!';
            const bankName = invoiceData.bankName || 'Meezan Bank';
            const bankAccountNumber = invoiceData.bankAccountNumber || '12312312312';
            const bankAccountHolder = invoiceData.bankAccountHolder || invoiceData.businessName;
            const base = `<div class="inv-doc"><div class="inv-title">${invoiceData.title}</div><div class="inv-head"><div class="inv-logo">Image</div><div><div class="inv-company">${invoiceData.businessName}</div><div class="inv-phone">Phone: ${invoiceData.phone}</div></div><div class="inv-meta"><div><strong>Invoice No:</strong> ${invoiceData.invoiceNo}</div><div><strong>Date:</strong> ${invoiceData.date}</div><div><strong>Time:</strong> ${invoiceData.time}</div><div><strong>Due Date:</strong> ${invoiceData.dueDate}</div></div></div><div class="inv-grid"><div class="inv-col"><h5>Bill To:</h5><p>${invoiceData.billTo}</p><p>${invoiceData.billAddress}</p><p>Phone: ${invoiceData.billPhone}</p></div><div class="inv-col"><h5>Invoice Details:</h5><p>Tax Type: GST</p><p>State: Sindh</p></div></div><div class="inv-ship"><strong>Ship To:</strong> ${invoiceData.shipTo}</div><table class="inv-table"><thead><tr><th>#</th><th>Item Name</th><th>HSC/SAC</th><th>Quantity</th><th>Price/Unit</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td>${item.hsn}</td><td>${item.qty}</td><td>${fmt(item.rate)}</td><td>${item.disc}%</td><td>${item.gst}</td><td>${fmt(item.amt)}</td></tr>`).join('')}<tr><td colspan="7" style="text-align:right;"><strong>Total</strong></td><td><strong>${fmt(total)}</strong></td></tr></tbody></table><div class="inv-bottom"><div class="box"><strong>Description:</strong><p>${description}</p><br /><strong>Bank Details:</strong><p>Bank Name: ${bankName}</p><p>Account Holder: ${bankAccountHolder}</p><p>Bank Account No: ${bankAccountNumber}</p></div><div class="box"><strong>Terms & Conditions:</strong><p>Thanks for doing business with us!</p><br /><p>Total <span>${fmt(total)}</span></p><p>Received <span>${fmt(received)}</span></p><p>Balance <span>${fmt(balance)}</span></p><div class="inv-sign">Authorized Signatory</div></div></div></div>`;
            const purple = `<div class="inv-doc inv-purple"><div class="inv-title inv-title--sale">Sale</div><div class="inv-head inv-head--sale"><div class="inv-logo">Image</div><div><div class="inv-company">${invoiceData.businessName}</div><div class="inv-phone">Ph. no.: ${invoiceData.phone}</div></div><div class="inv-meta"><div><strong>Invoice No:</strong> ${invoiceData.invoiceNo}</div><div><strong>Date:</strong> ${invoiceData.date}</div><div><strong>Time:</strong> ${invoiceData.time}</div><div><strong>Due Date:</strong> ${invoiceData.dueDate}</div></div></div><div class="inv-grid inv-grid--purple"><div class="inv-col"><h5>Bill To:</h5><p>${invoiceData.billTo}</p><p>${invoiceData.billAddress}</p></div><div class="inv-col"><h5>Shipping To</h5><p>${invoiceData.shipTo}</p></div><div class="inv-col"><h5>Invoice Details</h5><p>Invoice No.: ${invoiceData.invoiceNo}</p></div></div><table class="inv-table"><thead><tr><th>#</th><th>Item name</th><th>HSC/SAC</th><th>Quantity</th><th>Price/unit</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td>${item.hsn}</td><td>${item.qty}</td><td>${fmt(item.rate)}</td><td>Rs ${item.disc} (${item.disc}%)</td><td>Rs ${(Number(item.amt ?? item.rate)*(parseFloat(item.gst)/100)).toFixed(2)} (${item.gst})</td><td>${fmt(item.amt)}</td></tr>`).join('')}<tr><td></td><td><strong>Total</strong></td><td></td><td><strong>${totalQty}</strong></td><td></td><td><strong>${fmt(discount)}</strong></td><td><strong>${fmt(tax)}</strong></td><td><strong>${fmt(total)}</strong></td></tr></tbody></table><div class="inv-bottom inv-bottom--purple"><div class="box"><strong>Tax type</strong><p>SGST</p><p>CGST</p><strong>Invoice Amount In Words</strong><p>One Hundred Twenty Three Rupees only</p><strong>Bank Details</strong><p>Bank Name: ${bankName}</p><p>Account Holder: ${bankAccountHolder}</p><p>Bank Account No: ${bankAccountNumber}</p></div><div class="box"><strong>Amounts</strong><p>Sub Total <span>${fmt(subtotal)}</span></p><p>Discount <span>${fmt(discount)}</span></p><p>Tax <span>${fmt(tax)}</span></p><p><strong>Total <span>${fmt(total)}</span></strong></p><p>Received <span>${fmt(received)}</span></p><p>Balance <span>${fmt(balance)}</span></p><strong>Terms and conditions</strong><p>Thanks for doing business with us!</p><div class="inv-sign">Authorized Signatory</div></div></div></div>`;
            const modern = `<div class="inv-doc inv-modern"><div class="inv-head inv-head--modern"><div><div class="inv-company">${invoiceData.businessName}</div><div class="inv-phone">Ph. no.: ${invoiceData.phone}</div></div><div class="inv-logo">Image</div></div><div class="inv-title inv-title--sale">Sale</div><div class="inv-grid inv-grid--modern"><div class="inv-col"><h5>Bill To:</h5><p>${invoiceData.billTo}</p><p>${invoiceData.billAddress}</p></div><div class="inv-col"><h5>Shipping To</h5><p>${invoiceData.shipTo}</p></div><div class="inv-col"><h5>Invoice Details</h5><p>Invoice No.: ${invoiceData.invoiceNo}</p><p>Date: ${invoiceData.date}</p><p>Time: ${invoiceData.time}</p></div></div><table class="inv-table"><thead><tr><th>#</th><th>Item name</th><th>HSC/SAC</th><th>Quantity</th><th>Price/unit</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td>${item.hsn}</td><td>${item.qty}</td><td>${fmt(item.rate)}</td><td>Rs ${item.disc}</td><td>${item.gst}</td><td>${fmt(item.amt)}</td></tr>`).join('')}<tr><td></td><td><strong>Total</strong></td><td></td><td><strong>${totalQty}</strong></td><td></td><td><strong>${fmt(discount)}</strong></td><td><strong>${fmt(tax)}</strong></td><td><strong>${fmt(total)}</strong></td></tr></tbody></table><div class="inv-bottom"><div class="box"><strong>Description</strong><p>${description}</p><strong>INVOICE AMOUNT IN WORDS</strong><p>One Hundred Twenty Three Rupees only</p><strong>TERMS AND CONDITIONS</strong><p>Thanks for doing business with us!</p></div><div class="box"><p>Sub Total <span>${fmt(subtotal)}</span></p><p>Discount <span>${fmt(discount)}</span></p><p>GST <span>${fmt(tax)}</span></p><p><strong>Total <span>${fmt(total)}</span></strong></p><p>Received <span>${fmt(received)}</span></p><p>Balance <span>${fmt(balance)}</span></p><p><strong>You Saved <span>${fmt(discount)}</span></strong></p></div></div></div>`;
            const saleClassic = `<div class="inv-doc inv-sale-classic"><div class="inv-title inv-title--sale">Sale</div><div class="inv-head inv-head--saleclassic"><div class="inv-logo">Image</div><div><div class="inv-company">${invoiceData.businessName}</div><div class="inv-phone">Ph. no.: ${invoiceData.phone}</div></div><div class="inv-meta inv-meta--grid"><div><strong>Invoice No.</strong><br>${invoiceData.invoiceNo}</div><div><strong>Date</strong><br>${invoiceData.date}, ${invoiceData.time}</div><div><strong>Due Date</strong><br>${invoiceData.dueDate}</div><div><strong>Transport Name</strong><br>City Rider</div></div></div><div class="inv-grid"><div class="inv-col"><h5>Bill To</h5><p>${invoiceData.billTo}</p><p>${invoiceData.billAddress}</p></div><div class="inv-col"><h5>Ship To</h5><p>${invoiceData.shipTo}</p></div></div><table class="inv-table"><thead><tr><th>#</th><th>Item name</th><th>HSC/SAC</th><th>Quantity</th><th>Price/unit</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td>${item.hsn}</td><td>${item.qty}</td><td>${fmt(item.rate)}</td><td>Rs ${item.disc} (0%)</td><td>Rs ${(Number(item.amt ?? item.rate)*(parseFloat(item.gst)/100)).toFixed(2)} (${item.gst})</td><td>${fmt(item.amt)}</td></tr>`).join('')}<tr><td></td><td><strong>Total</strong></td><td></td><td><strong>${totalQty}</strong></td><td></td><td><strong>${fmt(discount)}</strong></td><td><strong>${fmt(tax)}</strong></td><td><strong>${fmt(total)}</strong></td></tr></tbody></table><div class="inv-bottom"><div class="box"><strong>Invoice Amount In Words</strong><p>One Hundred Twenty Three Rupees only</p><strong>Description</strong><p>${description}</p></div><div class="box"><strong>Amounts</strong><p>Sub Total <span>${fmt(subtotal)}</span></p><p>Discount <span>${fmt(discount)}</span></p><p>Tax <span>${fmt(tax)}</span></p><p><strong>Total <span>${fmt(total)}</span></strong></p><p>Received <span>${fmt(received)}</span></p><p>Balance <span>${fmt(balance)}</span></p></div></div></div>`;
            const doubleDivine = `<div class="inv-doc double-divine-custom"><div class="dd-top"><div class="dd-top-right"><div class="dd-phone">${invoiceData.phone}</div></div><div class="dd-top-left"><div class="dd-logo">LOGO</div><div class="dd-company">${invoiceData.businessName}</div></div></div><div class="dd-main"><div><div class="dd-section-title">Bill To:</div><div class="dd-party-name">${invoiceData.billTo}</div><p class="dd-subtext">${invoiceData.billAddress}</p><p class="dd-subtext"><strong>Contact No:</strong> ${invoiceData.billPhone}</p></div><div><h2 class="dd-title">Invoice</h2><div class="dd-meta"><div><strong>Invoice No.:</strong> <span style="display:inline-block; min-width:46px; text-align:right;">${invoiceData.invoiceNo}</span></div><div><strong>Date:</strong> <span style="display:inline-block; min-width:96px; text-align:right;">${invoiceData.date}</span></div></div></div></div><table class="dd-table"><thead><tr><th style="width:6%;">#</th><th>Item name</th><th style="width:18%; text-align:right;">Quantity</th><th style="width:18%; text-align:right;">Price/ Unit</th><th style="width:18%; text-align:right;">Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td style="text-align:right;">${item.qty}</td><td style="text-align:right;">${fmt(item.rate)}</td><td style="text-align:right;">${fmt(item.amt)}</td></tr>`).join('')}<tr class="dd-total-row"><td></td><td>Total</td><td style="text-align:right;">${totalQty}</td><td></td><td style="text-align:right;">${fmt(total)}</td></tr></tbody></table><div class="dd-bottom"><div class="dd-lines"><h4 class="dd-section-title">Invoice Amount In Words</h4><p>One Hundred Twenty Three Rupees only</p><h4 class="dd-section-title">Terms And Conditions</h4><p>${description}</p><p style="margin-top:28px;">For : ${invoiceData.businessName}</p></div><div><table class="dd-amounts"><tr><td>Sub Total</td><td style="text-align:right;">${fmt(subtotal)}</td></tr><tr class="highlight"><td>Total</td><td style="text-align:right;">${fmt(total)}</td></tr><tr><td>Received</td><td style="text-align:right;">${fmt(received)}</td></tr><tr><td>Balance</td><td style="text-align:right;">${fmt(balance)}</td></tr></table></div></div><div class="dd-sign"><div class="dd-sign-line">Authorized Signatory</div></div></div>`;
            const frenchElite = `<div class="inv-doc inv-french-elite"><div class="elite-banner"><div class="elite-title">TAX INVOICE</div><div class="inv-logo">Image</div></div><div class="elite-store">${invoiceData.businessName}</div><div class="inv-grid inv-grid--modern"><div class="inv-col"><h5>Invoice No.: #${invoiceData.invoiceNo}</h5><p>Invoice Date: ${invoiceData.date}</p><p>Invoice Time: ${invoiceData.time}</p></div><div class="inv-col"><h5>Bill To:</h5><p>${invoiceData.billTo}</p><p>${invoiceData.billAddress}</p></div><div class="inv-col"><h5>Transportation Details:</h5><p>Transport Name: City Rider</p><p>Vehicle Number: LEA 2026</p></div></div><table class="inv-table"><thead><tr><th>#</th><th>Item name</th><th>HSN/SAC</th><th>Quantity</th><th>Price / unit</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead><tbody>${items.map((item,index)=>`<tr><td>${index+1}</td><td>${item.name}</td><td>${item.hsn}</td><td>${item.qty}</td><td>${fmt(item.rate)}</td><td>Rs ${item.disc}</td><td>${item.gst}</td><td>${fmt(item.amt)}</td></tr>`).join('')}<tr><td></td><td><strong>Total</strong></td><td></td><td><strong>${totalQty}</strong></td><td></td><td><strong>${fmt(discount)}</strong></td><td><strong>${fmt(tax)}</strong></td><td><strong>${fmt(total)}</strong></td></tr></tbody></table><div class="inv-bottom"><div class="box"><strong>Pay To:</strong><p>Bank Name: ${bankName}</p><p>Account Holder: ${bankAccountHolder}</p><p>Account No: ${bankAccountNumber}</p><strong>Terms And Conditions</strong><p>${description}</p></div><div class="box"><p><strong>Total</strong> <span>${fmt(total)}</span></p><p>Received <span>${fmt(received)}</span></p><p>Balance <span>${fmt(balance)}</span></p><div class="inv-sign">Authorized Signatory</div></div></div></div>`;
            const byVariant = { classicA:base, classicB:base.replace('<div class="inv-title">','<div class="inv-title inv-title--large">'), classicC:base.replace('<div class="inv-bottom">','<div class="inv-bottom inv-bottom--compact">'), purpleA:purple, purpleB:purple.replace('GST</th>','CGST</th><th>SGST</th>'), purpleC:purple.replace('inv-bottom--purple','inv-bottom--purple inv-bottom--compact'), modernPurple:modern, classicSale:saleClassic, taxTheme6:purple.replace('inv-bottom--purple','inv-bottom--purple tax-theme-6'), doubleDivine:doubleDivine, frenchElite:frenchElite, theme1:base.replace('<div class="inv-title">','<div class="inv-title inv-title--sale">'), theme2:purple.replace('inv-doc inv-purple','inv-doc inv-purple theme-two'), theme3:modern.replace('inv-doc inv-modern','inv-doc inv-modern theme-three'), theme4:saleClassic.replace('inv-doc inv-sale-classic','inv-doc inv-sale-classic theme-four') };
            invoiceCanvas.innerHTML = byVariant[theme.variant] || byVariant.classicA;
            root.classList.toggle('is-double-divine', theme.variant === 'doubleDivine');
            themeHint.textContent = `${theme.name} ka regular preview open hai. Color dots se live color change kar sakte ho.`;
        }
        function renderThermalPreview(theme){
            const items = Array.isArray(invoiceData.items) && invoiceData.items.length ? invoiceData.items : [{ name:'Item', hsn:'', qty:'0', unit:'', rate:0, disc:'0.00', gst:'0%', amt:0 }];
            const subtotal = Number(invoiceData.subtotal ?? items.reduce((s,i)=>s+Number(i.amt ?? i.rate ?? 0),0));
            const tax = Number(invoiceData.taxAmount ?? items.reduce((s,i)=>s+(Number(i.amt ?? i.rate ?? 0)*(parseFloat(i.gst || 0)/100)),0));
            const discount = Number(invoiceData.discount ?? 0);
            const total = Number(invoiceData.total ?? Math.max(subtotal + tax - discount, 0));
            const received = Number(invoiceData.received ?? 0);
            const balance = Number(invoiceData.balance ?? Math.max(total - received, 0));
            const totalQty = items.reduce((s,i)=>s+Number(i.qty || 0),0);
            const thermalTopA = `<div class="t-center t-bold">${invoiceData.businessName}</div><div class="t-center t-small">Ph.No.: ${invoiceData.phone}</div><div class="t-line"></div><div class="t-row"><span>${invoiceData.billTo}</span><span class="t-bold">Invoice</span></div><div class="t-row t-small"><span>Ph. No.: ${invoiceData.billPhone}</span><span>Date: ${invoiceData.date}</span></div><div class="t-row t-small"><span>Bill To:</span><span>Invoice No.: ${invoiceData.invoiceNo}</span></div><div class="t-row t-small"><span>${invoiceData.billAddress}</span><span></span></div><div class="t-line"></div>`;
            const thermalTopB = `<div class="t-center t-bold">${invoiceData.businessName}</div><div class="t-center t-small">Ph.No.: ${invoiceData.phone}</div><div class="t-line"></div><div class="t-row t-small"><span>Invoice No.: ${invoiceData.invoiceNo}</span><span>Date: ${invoiceData.date}</span></div><div class="t-center t-bold">Invoice</div><div class="t-line"></div><div class="t-center t-bold">${invoiceData.billTo}</div><div class="t-center t-small">Ph. No.: ${invoiceData.billPhone}</div><div class="t-line"></div><div class="t-row t-small"><span class="t-bold">Bill To:</span><span></span></div><div class="t-row t-small"><span>${invoiceData.billAddress}</span><span></span></div><div class="t-line"></div>`;
            const firstItem = items[0];
            const rowsClassic = `<tr><td>1</td><td>${firstItem.name}<div class="t-muted t-small">${firstItem.qty} ${firstItem.unit}</div><div class="t-muted t-small t-italic">Sample item description</div><div class="t-muted t-small">HSN: ${firstItem.hsn}</div></td><td class="t-right">${Number(firstItem.rate).toFixed(2)}</td><td class="t-right">${Number(firstItem.rate).toFixed(2)}</td><td class="t-right">${Number(firstItem.amt).toFixed(2)}</td></tr>`;
            const tableTheme1 = `<table><thead><tr><th>#</th><th>Name<div class="t-small">Qty</div></th><th class="t-right">Qty</th><th class="t-right">Price</th><th class="t-right">Amount</th></tr></thead><tbody>${rowsClassic}</tbody></table>`;
            const tableTheme2 = `<table><thead><tr><th>#</th><th>Item Name<div class="t-small">Qty</div><div class="t-small">Description</div></th><th class="t-right">MRP</th><th class="t-right">Price</th><th class="t-right">Amount</th></tr></thead><tbody>${rowsClassic}</tbody></table>`;
            const totalsA = `<div class="t-line"></div><div class="t-row"><span class="t-bold">Qty: ${totalQty}</span><span class="t-bold">${total.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Disc.</span><span>${discount.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Tax</span><span>${tax.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span class="t-bold">Total</span><span class="t-bold">${total.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Received</span><span>${received.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Balance</span><span>${balance.toFixed(2)}</span></div>`;
            const totalsB = `<div class="t-line"></div><div class="t-row t-row--triple"><span class="t-bold">Qty: ${totalQty}</span><span class="t-bold t-center">Items: ${items.length}</span><span class="t-bold t-right">${total.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Disc.</span><span>${discount.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Tax</span><span>${tax.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span class="t-bold">Total</span><span class="t-bold">${total.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Received</span><span>${received.toFixed(2)}</span></div><div class="t-row t-small t-indent"><span>Balance</span><span>${balance.toFixed(2)}</span></div>`;
            const footer = `<div class="t-line"></div><div class="t-center t-small">Balance to be paid in 3 days</div><div class="t-line"></div><div class="t-center t-bold t-small">Terms & Conditions</div><div class="t-center t-small">Thanks for doing business with us!</div>`;
            const thermalHtmlByVariant = { thermal1:`<div class="inv-thermal inv-thermal--t1">${thermalTopA}${tableTheme1}${totalsA}${footer}</div>`, thermal2:`<div class="inv-thermal inv-thermal--t2">${thermalTopA}${tableTheme2}${totalsA}${footer}</div>`, thermal3:`<div class="inv-thermal inv-thermal--t3">${thermalTopA}${tableTheme1}${totalsA}${footer}</div>`, thermal4:`<div class="inv-thermal inv-thermal--t4">${thermalTopB}${tableTheme2}${totalsB}${footer}</div>`, thermal5:`<div class="inv-thermal inv-thermal--t5">${thermalTopB}${tableTheme1}${totalsB}${footer}</div>` };
            invoiceCanvas.innerHTML = thermalHtmlByVariant[theme.variant] || thermalHtmlByVariant.thermal1;
            invoiceCanvas.style.setProperty('--thermal-width', '280px');
            root.classList.remove('is-double-divine');
            themeHint.textContent = `${theme.name} ka thermal preview open hai. Color dots border aur lines par apply ho rahe hain.`;
        }
        function syncModalPreview(){ if(modalInvoiceCanvas) modalInvoiceCanvas.innerHTML = invoiceCanvas.innerHTML; }
        function buildPdfExportNode(){
            const previewNode = invoiceCanvas.firstElementChild;
            if(!previewNode) return null;

            const previewWidth = Math.ceil(previewNode.getBoundingClientRect().width || previewNode.scrollWidth || (currentMode === 'thermal' ? 280 : 900));
            const exportHost = document.createElement('div');
            exportHost.className = `invoice-theme-layout${root.classList.contains('is-double-divine') ? ' is-double-divine' : ''}`;
            exportHost.style.position = 'fixed';
            exportHost.style.left = '0';
            exportHost.style.right = '0';
            exportHost.style.top = '0';
            exportHost.style.width = `${Math.min(previewWidth + 48, 1120)}px`;
            exportHost.style.maxWidth = '100%';
            exportHost.style.margin = '0 auto';
            exportHost.style.padding = currentMode === 'thermal' ? '20px' : '24px';
            exportHost.style.background = '#ffffff';
            exportHost.style.opacity = '0.01';
            exportHost.style.pointerEvents = 'none';
            exportHost.style.zIndex = '1';
            exportHost.style.boxSizing = 'border-box';
            exportHost.style.display = 'flex';
            exportHost.style.justifyContent = 'center';
            exportHost.style.alignItems = 'flex-start';
            exportHost.style.setProperty('--accent', getComputedStyle(root).getPropertyValue('--accent') || '#1f4e79');
            exportHost.style.setProperty('--accent-2', getComputedStyle(root).getPropertyValue('--accent-2') || '#ff981f');

            const exportMain = document.createElement('main');
            exportMain.className = 'preview-wrap';
            exportMain.style.padding = '0';
            exportMain.style.display = 'flex';
            exportMain.style.justifyContent = 'center';

            const exportSheet = document.createElement('div');
            exportSheet.className = 'sheet';
            exportSheet.style.maxWidth = 'none';
            exportSheet.style.minHeight = 'auto';
            exportSheet.style.margin = '0';
            exportSheet.style.width = '100%';

            const exportCanvas = document.createElement('div');
            exportCanvas.className = 'invoice-canvas';
            exportCanvas.style.background = '#ffffff';
            exportCanvas.style.border = '0';
            exportCanvas.style.minHeight = 'auto';
            exportCanvas.style.padding = '0';
            exportCanvas.style.overflow = 'visible';
            exportCanvas.style.display = 'flex';
            exportCanvas.style.justifyContent = 'center';
            exportCanvas.style.alignItems = 'flex-start';
            exportCanvas.style.width = '100%';
            exportCanvas.style.setProperty('--inv-accent', getComputedStyle(invoiceCanvas).getPropertyValue('--inv-accent') || '#1f4e79');
            exportCanvas.style.setProperty('--thermal-width', getComputedStyle(invoiceCanvas).getPropertyValue('--thermal-width') || '280px');

            const previewClone = previewNode.cloneNode(true);
            previewClone.style.margin = '0 auto';
            previewClone.style.boxSizing = 'border-box';
            previewClone.style.width = '100%';
            exportCanvas.appendChild(previewClone);
            exportSheet.appendChild(exportCanvas);
            exportMain.appendChild(exportSheet);
            exportHost.appendChild(exportMain);
            document.body.appendChild(exportHost);

            return {
                node: exportHost,
                cleanup() {
                    exportHost.remove();
                }
            };
        }
        function renderPreview(){ if(currentMode==='thermal') renderThermalPreview(getThermalThemeById(activeThermalId)); else renderRegularPreview(getRegularThemeById(activeRegularId)); syncModalPreview(); persistThemeSelection(); }
        function setMode(mode){ currentMode = mode === 'thermal' ? 'thermal' : 'regular'; modeButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.mode === currentMode)); regularThemeSections.classList.toggle('hidden', currentMode !== 'regular'); thermalThemeSection.classList.toggle('hidden', currentMode !== 'thermal'); setActiveButton(currentMode, currentMode === 'thermal' ? activeThermalId : activeRegularId); renderPreview(); }
        modeButtons.forEach(btn => btn.addEventListener('click', function(){ setMode(btn.dataset.mode); }));
        themeButtons.forEach(btn => btn.addEventListener('click', function(){ const kind = btn.dataset.kind, id = Number(btn.dataset.themeId); if(kind === 'thermal'){ activeThermalId = id; currentMode = 'thermal'; } else { activeRegularId = id; currentMode = 'regular'; } setMode(currentMode); }));
        colorButtons.forEach(btn => btn.addEventListener('click', function(){ const accent = btn.dataset.accent; if(!accent) return; colorButtons.forEach(item => item.classList.remove('active')); btn.classList.add('active'); setAccent(accent); renderPreview(); }));
        if(customAccentPicker) customAccentPicker.addEventListener('input', function(){ colorButtons.forEach(item => item.classList.remove('active')); setAccent(customAccentPicker.value); renderPreview(); });
        if(doubleDivineSecondPicker) doubleDivineSecondPicker.addEventListener('input', function(){ setSecondAccent(doubleDivineSecondPicker.value); if(currentMode === 'regular' && getRegularThemeById(activeRegularId).variant === 'doubleDivine') renderPreview(); });
        function doPrint(){ window.print(); }
        async function waitForInvoiceRender(){
            if (document.fonts && document.fonts.ready) {
                try { await document.fonts.ready; } catch (e) {}
            }
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        async function downloadRenderedPdf(){
            if(!window.html2pdf) {
                window.print();
                return;
            }
            await waitForInvoiceRender();
            const exportTarget = buildPdfExportNode();
            if(!exportTarget) return;
            return window.html2pdf().set({
                margin:[6,6,6,6],
                filename:`invoice-${invoiceData.invoiceNo || 'preview'}.pdf`,
                image:{ type:'jpeg', quality:.98 },
                html2canvas:{ scale:2, useCORS:true, backgroundColor:'#ffffff' },
                jsPDF:{ unit:'mm', format:'a4', orientation: 'portrait' }
            }).from(exportTarget.node).save().then(() => exportTarget.cleanup()).catch(() => {
                exportTarget.cleanup();
                window.print();
            });
        }
        function getInvoicePdfUrl(download = false){ const activeThemeId = currentMode === 'thermal' ? activeThermalId : activeRegularId; const params = new URLSearchParams({ mode: currentMode, theme_id: String(activeThemeId), accent: customAccentPicker ? customAccentPicker.value : initialAccent, accent2: doubleDivineSecondPicker ? doubleDivineSecondPicker.value : initialAccent2 }); if(download) params.set('download', '1'); return `${invoicePdfBaseUrl}?${params.toString()}`; }
        function triggerServerPdfDownload(){
            if(!invoicePdfBaseUrl) return false;
            window.location.href = getInvoicePdfUrl(true);
            return true;
        }
        function getShareMessage(){
            const invoiceNumber = invoiceData.invoiceNo || saleId || 'Invoice';
            const previewUrl = window.location.href;
            const pdfUrl = invoicePdfBaseUrl ? getInvoicePdfUrl(true) : previewUrl;
            return `Invoice #${invoiceNumber}\nParty: ${invoiceData.billTo || '-'}\nTotal: ${fmt(invoiceData.total || 0)}\nReceived: ${fmt(invoiceData.received || 0)}\nBalance: ${fmt(invoiceData.balance || 0)}\nPreview: ${previewUrl}\nPDF: ${pdfUrl}`;
        }
        function openShareWindow(baseUrl, paramName){
            const text = getShareMessage();
            const shareUrl = `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}${paramName}=${encodeURIComponent(text)}`;
            window.open(shareUrl, '_blank', 'noopener');
        }
        if(downloadPdfBtn){
            downloadPdfBtn.addEventListener('click', function(){
                if(triggerServerPdfDownload()) return;
                downloadRenderedPdf();
            });
        }
        if(normalPrintBtn) normalPrintBtn.addEventListener('click', doPrint);
        if(thermalPrintBtn) thermalPrintBtn.addEventListener('click', doPrint);
        if(shareWhatsappBtn) shareWhatsappBtn.addEventListener('click', function(){ openShareWindow('https://wa.me/', 'text'); });
        if(shareGmailBtn) shareGmailBtn.addEventListener('click', function(){ openShareWindow('https://mail.google.com/mail/?view=cm&fs=1', 'body'); });
        if(openPreviewModalBtn) openPreviewModalBtn.addEventListener('click', function(){ syncModalPreview(); invoicePreviewModal.classList.add('open'); });
        if(closePreviewModalBtn) closePreviewModalBtn.addEventListener('click', function(){ invoicePreviewModal.classList.remove('open'); });
        if(invoicePreviewModal) invoicePreviewModal.addEventListener('click', function(e){ if(e.target === invoicePreviewModal) invoicePreviewModal.classList.remove('open'); });
        activeRegularId = Number(storedThemeState?.regularThemeId || activeRegularId || 1);
        activeThermalId = Number(storedThemeState?.thermalThemeId || activeThermalId || 1);
        setAccent(storedThemeState?.accent || initialAccent || '#1f4e79');
        setSecondAccent(storedThemeState?.accent2 || initialAccent2 || '#ff981f');
        setMode(currentMode || initialMode || 'regular');
        if(document.body.classList.contains('pdf-export-page') && autoDownload){
            setTimeout(function(){
                if(triggerServerPdfDownload()) return;
                downloadRenderedPdf();
            }, 400);
        }
    </script>
</body>
</html>
