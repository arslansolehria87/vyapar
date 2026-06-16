<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vyapar - Invoice Builder</title>
  <meta name="description" content="Create and preview invoice themes in the React invoice builder.">

  @unless (!empty($pdfDirectDownload))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  @endunless

  @php
    $withAssetVersion = function ($url) {
      if (empty($url)) {
        return null;
      }
      $path = parse_url($url, PHP_URL_PATH) ?: '';
      $absolute = public_path(ltrim($path, '/'));
      if (is_file($absolute)) {
        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($absolute);
      }
      return $url;
    };
    $reactCss = $withAssetVersion($reactCss ?? null);
    $reactJs = $withAssetVersion($reactJs ?? null);
  @endphp

  @if (!empty($pdfDirectDownload) && !empty($reactCssInline))
    <style>{!! $reactCssInline !!}</style>
  @elseif (!empty($reactCss))
    <link rel="stylesheet" href="{{ $reactCss }}">
  @endif

  @unless (!empty($pdfDirectDownload))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer></script>
  @endunless

  <style>
    html,
    body {
      margin: 0;
      min-height: 100%;
      background: #f3f4f6;
    }

    #root {
      min-height: 100vh;
    }

    #root .app-container {
      min-height: 100vh;
      height: auto;
    }

    .bundle-missing {
      max-width: 720px;
      margin: 40px auto;
      padding: 16px 20px;
      border: 1px solid #f1c40f;
      border-radius: 10px;
      background: #fff8db;
      color: #6b5a00;
      font-family: Roboto, sans-serif;
    }

    @media print {
      html,
      body {
        background: #ffffff !important;
        min-height: auto !important;
      }

      body * {
        visibility: hidden !important;
      }

      .right-panel,
      .right-panel * {
        visibility: visible !important;
      }

      #root,
      #root .app-container {
        min-height: auto !important;
        height: auto !important;
      }

      .preview-topbar,
      .preview-head,
      .left-panel,
      .share-panel,
      .modal-overlay {
        display: none !important;
      }

      .app-container {
        display: block !important;
        min-height: auto !important;
        height: auto !important;
      }

      .right-panel {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
      }

      .right-panel * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      @page {
        size: A4 portrait;
        margin: 8mm;
      }
    }
  </style>
</head>


<body>
  @if (!empty($paymentIn))
    <script>
      window.paymentInInvoice = @json($paymentIn);
    </script>
  @endif

  @if (!empty($allPaymentIns))
    <script>
      window.allPaymentInInvoices = @json($allPaymentIns);
    </script>
  @endif

  @if (!empty($reactJs))
    <div id="root"></div>
    <script>
      window.docType = @json($documentType ?? null);
      window.invoiceAppData = {
        invoiceData: @json($invoicePreviewData ?? null),
        saleId: @json($sale->id ?? ($purchase->id ?? null)),
        initialTheme: @json($initialMode ?? 'tally'),
        initialColor: @json($initialAccent ?? '#707070'),
        initialColor2: @json($initialAccent2 ?? '#ff981f'),
        browserTabLabel: @json($browserTabLabel ?? 'Invoice Preview'),
        saveCloseUrl: @json($saveCloseUrl ?? '/dashboard/sales'),
        themeSaveUrl: @json($themeSaveUrl ?? null),
        initialRegularThemeId: @json($initialRegularThemeId ?? 1),
        initialThermalThemeId: @json($initialThermalThemeId ?? 1),
        debugInfo: @json($debugInfo ?? null),
      };
    </script>
    @if (!empty($sale))
      <script>
        window.serverInvoicePdfConfig = {
          saleId: @json($sale->id),
          baseUrl: @json(route('sale.invoice-pdf', $sale)),
          downloadUrl: @json(route('invoice.download-pdf', ['sale_id' => $sale->id])),
          defaultMode: @json($initialMode ?? 'regular'),
          defaultThemeId: @json($initialMode === 'thermal' ? ($initialThermalThemeId ?? 1) : ($initialRegularThemeId ?? 1)),
          defaultAccent: @json($initialAccent ?? '#1f4e79'),
          defaultAccent2: @json($initialAccent2 ?? '#ff981f')
        };
      </script>
    @elseif (!empty($purchase))
      <script>
        window.serverInvoicePdfConfig = {
          purchaseId: @json($purchase->id),
          downloadUrl: @json(($purchase->type ?? null) === 'purchase_return' ? route('purchase-return.pdf', $purchase) : route('purchase-bills.download-pdf', $purchase)),
          defaultMode: @json($initialMode ?? 'regular'),
          defaultThemeId: @json($initialMode === 'thermal' ? ($initialThermalThemeId ?? 1) : ($initialRegularThemeId ?? 1)),
          defaultAccent: @json($initialAccent ?? '#1f4e79'),
          defaultAccent2: @json($initialAccent2 ?? '#ff981f')
        };
      </script>
    @endif
  @else
    <div class="bundle-missing">
      React invoice bundle not found. Run the React build and copy the generated dist/assets files into
      public/react-invoice/assets.
    </div>
  @endif

  <script>
    (function () {
      const themeLabelMap = {
        'tally': 'tally',
        'tax theme 1': 'tax1',
        'tax theme 2': 'tax2',
        'tax theme 3': 'tax3',
        'tax theme 4': 'tax4',
        'tax theme 5': 'tax5',
        'tax theme 6': 'tax6',
        'landscape theme 1': 'LandScapeTheme1',
        'landscape theme 2': 'LandScapeTheme2',
        'double divine': 'divine',
        'french elite': 'french',
        'theme 1': 'theme1',
        'theme 2': 'theme2',
        'theme 3': 'theme3',
        'theme 4': 'theme4',
        'thermal theme 1': 'thermal1',
        'thermal theme 2': 'thermal2',
        'thermal theme 3': 'thermal3',
        'thermal theme 4': 'thermal4',
        'thermal theme 5': 'thermal5'
      };

      function rgbToHex(value) {
        if (!value) {
          return '#707070';
        }

        if (value.startsWith('#')) {
          return value;
        }

        const match = value.match(/\d+/g);
        if (!match || match.length < 3) {
          return '#707070';
        }

        return '#' + match.slice(0, 3).map(function (channel) {
          const hex = Number(channel).toString(16);
          return hex.length === 1 ? '0' + hex : hex;
        }).join('');
      }

      function getSelectedTheme() {
        const activeTheme = document.querySelector('.theme-item.active');
        const label = activeTheme ? activeTheme.textContent.replace(/\s+/g, ' ').trim().toLowerCase() : '';
        return themeLabelMap[label] || 'tally';
      }

      function getSelectedColor() {
        const selectedColorBox = document.querySelector('.selected-color .color-box');
        if (selectedColorBox) {
          return rgbToHex(getComputedStyle(selectedColorBox).backgroundColor);
        }

        const activeColorDot = document.querySelector('.color-dot.active');
        if (activeColorDot) {
          return rgbToHex(getComputedStyle(activeColorDot).backgroundColor);
        }

        const activeDivineDot = document.querySelector('.divine-dot.active .divine-dot-accent');
        if (activeDivineDot) {
          return rgbToHex(getComputedStyle(activeDivineDot).backgroundColor);
        }

        return '#707070';
      }

      function getThemePayload() {
        const theme = getSelectedTheme();
        const regularThemeIds = {
          tally: 1,
          LandScapeTheme1: 2,
          LandScapeTheme2: 3,
          tax1: 4,
          tax2: 5,
          tax3: 6,
          tax4: 7,
          tax5: 8,
          tax6: 9,
          divine: 10,
          french: 11,
          theme1: 12,
          theme2: 13,
          theme3: 14,
          theme4: 15
        };
        const thermalThemeIds = {
          thermal1: 1,
          thermal2: 2,
          thermal3: 3,
          thermal4: 4,
          thermal5: 5
        };
        const isThermal = Object.prototype.hasOwnProperty.call(thermalThemeIds, theme);

        return {
          mode: isThermal ? 'thermal' : 'regular',
          regularThemeId: isThermal ? null : (regularThemeIds[theme] || 1),
          thermalThemeId: isThermal ? (thermalThemeIds[theme] || 1) : null,
          accent: getSelectedColor() || '#1f4e79',
          accent2: '#ff981f'
        };
      }

      let lastThemeSaveSignature = '';
      let themeSaveTimer = null;
      function persistSelectedInvoiceTheme() {
        const themeSaveUrl = window.invoiceAppData?.themeSaveUrl;
        if (!themeSaveUrl) {
          return Promise.resolve();
        }

        const payload = getThemePayload();
        const signature = JSON.stringify(payload);
        if (signature === lastThemeSaveSignature) {
          return Promise.resolve();
        }
        lastThemeSaveSignature = signature;

        return fetch(themeSaveUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: signature
        }).catch(function () {});
      }

      function scheduleThemeSave() {
        clearTimeout(themeSaveTimer);
        themeSaveTimer = setTimeout(persistSelectedInvoiceTheme, 350);
      }

      function triggerInvoiceDownload() {
        const config = window.serverInvoicePdfConfig || {};
        if (!config.downloadUrl) {
          window.print();
          return;
        }

        const url = new URL(config.downloadUrl, window.location.origin);
        if (config.saleId) {
          url.searchParams.set('sale_id', String(config.saleId));
        }
        if (config.purchaseId) {
          url.searchParams.set('purchase_id', String(config.purchaseId));
        }
        const themePayload = getThemePayload();
        const activeThemeId = themePayload.mode === 'thermal' ? themePayload.thermalThemeId : themePayload.regularThemeId;
        url.searchParams.set('theme', getSelectedTheme());
        url.searchParams.set('color', getSelectedColor());
        url.searchParams.set('mode', themePayload.mode);
        if (activeThemeId) {
          url.searchParams.set('theme_id', String(activeThemeId));
        }
        url.searchParams.set('accent', themePayload.accent);
        url.searchParams.set('accent2', themePayload.accent2);
        window.location.href = url.toString();
      }

      function isDownloadPdfAction(target) {
        const shareItem = target.closest('.share-item');
        if (!shareItem) {
          return false;
        }

        const text = (shareItem.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
        return text.includes('download') && text.includes('pdf');
      }

      document.addEventListener('click', function (event) {
        const saveCloseLink = event.target.closest('.preview-save');
        if (saveCloseLink && window.invoiceAppData?.themeSaveUrl) {
          event.preventDefault();
          event.stopPropagation();
          event.stopImmediatePropagation();
          const href = saveCloseLink.href;
          persistSelectedInvoiceTheme().finally(function () {
            window.location.href = href;
          });
          return;
        }

        if (!isDownloadPdfAction(event.target)) {
          scheduleThemeSave();
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        triggerInvoiceDownload();
      }, true);

      document.addEventListener('change', scheduleThemeSave, true);
      document.addEventListener('input', scheduleThemeSave, true);
      window.addEventListener('load', function () {
        setTimeout(persistSelectedInvoiceTheme, 900);
      });

      window.triggerInvoiceDownload = triggerInvoiceDownload;

      @if (!empty($autoPrintPreview))
        window.addEventListener('load', function () {
          setTimeout(function () {
            window.print();
          }, 700);
        });
      @endif
    })();
  </script>

  @if (!empty($pdfDirectDownload) && !empty($reactJsInline))
    <script type="module">{!! $reactJsInline !!}</script>
  @elseif (!empty($reactJs))
    <script type="module" src="{{ $reactJs }}"></script>
  @endif




</body>
</html>
