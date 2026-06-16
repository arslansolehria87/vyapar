<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\AppSetting;
use App\Models\Broker;
use App\Models\PaymentIn;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class InvoiceController extends Controller
{
    public function reactAsset(string $source, string $file)
    {
        abort_unless(array_key_exists($source, $this->reactInvoiceAssetCandidates()), 404);
        abort_unless(preg_match('/^index-[A-Za-z0-9_-]+\.(css|js)$/', $file), 404);

        $path = $this->reactInvoiceAssetCandidates()[$source]['path'] . DIRECTORY_SEPARATOR . $file;
        abort_unless(File::exists($path), 404);

        $contentType = str_ends_with($file, '.css')
            ? 'text/css; charset=UTF-8'
            : 'application/javascript; charset=UTF-8';

        return response(File::get($path), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);

        abort(404);
    }

    public function index(Request $request)
    {
        // return $request->all();
        return view('invoice.index', $this->buildInvoiceViewData($request));
    }

    public function modalPreview(Request $request)
    {
        $viewData = $this->buildInvoiceViewData($request);

        abort_unless(!empty($viewData['invoicePreviewData']), 404);

        return view('invoice.party-preview', [
            'invoicePreviewData' => $viewData['invoicePreviewData'],
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $viewData = $this->buildInvoiceViewData($request);
        $saleId = (int) ($viewData['sale']->id ?? $request->integer('sale_id') ?? $request->integer('payment_in'));
        $purchase = $viewData['purchase'] ?? null;
        $themeDefaults = $purchase instanceof Purchase
            ? $this->resolveStoredPurchaseThemeConfig($purchase, $request)
            : $this->resolveStoredInvoiceThemeConfig($viewData['sale'] ?? null, $request);
        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        abort_unless($saleId > 0 || $purchase instanceof Purchase || !empty($viewData['invoicePreviewData']), 404);

        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $viewData['invoicePreviewData'],
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
        ])->setPaper('a4', 'portrait');

        if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        }

        $downloadPrefix = $purchase instanceof Purchase
            ? ($purchase->type === 'purchase_return' ? 'purchase-return' : 'purchase')
            : 'invoice';
        $downloadName = $downloadPrefix . '-' . ($viewData['invoicePreviewData']['invoiceNo'] ?? $saleId ?: 'preview') . '.pdf';

        return $pdf->download($downloadName);
    }

    public function print()
    {
        return view('invoice.print');
    }

    public function proforma(Request $request, Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);

        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount']);
        $invoicePreviewData = $this->mapSaleToThemePreviewData($sale);
        $invoicePreviewData['title'] = 'Proforma Invoice';
        $reactAssets = $this->resolveReactInvoiceAssets();
        $themeDefaults = $this->resolveStoredInvoiceThemeConfig($sale, $request);
        $invoiceAppData = [
            'invoiceData' => $invoicePreviewData,
            'saleId' => $sale->id,
            'initialTheme' => $themeDefaults['mode'],
            'initialColor' => $themeDefaults['accent'],
            'initialColor2' => $themeDefaults['accent2'],
            'browserTabLabel' => 'Proforma #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('proforma-invoice'),
            'themeSaveUrl' => route('sale.invoice-theme.store', $sale),
            'initialRegularThemeId' => $themeDefaults['regularThemeId'],
            'initialThermalThemeId' => $themeDefaults['thermalThemeId'],
        ];

        return view('invoice.proforma', [
            'invoiceAppData' => $invoiceAppData,
            'invoicePreviewData' => $invoicePreviewData,
            'pageTitle' => 'Proforma Preview',
            'browserTabLabel' => 'Proforma #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('proforma-invoice'),
            'themeSaveUrl' => route('sale.invoice-theme.store', $sale),
            'initialMode' => $themeDefaults['mode'],
            'initialRegularThemeId' => $themeDefaults['regularThemeId'],
            'initialThermalThemeId' => $themeDefaults['thermalThemeId'],
            'initialAccent' => $themeDefaults['accent'],
            'initialAccent2' => $themeDefaults['accent2'],
            'autoPrintPreview' => $request->boolean('print'),
            'reactCss' => $reactAssets['css_url'],
            'reactJs' => $reactAssets['js_url'],
            'reactIsModule' => true,
        ]);
    }

    public function emailDocument(Request $request, Sale $sale, ?string $expectedType = null)
    {
        if ($expectedType !== null) {
            abort_unless($sale->type === $expectedType, 404);
        }

        $data = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $sale->loadMissing(['items.item', 'party', 'broker', 'challanDetail', 'details', 'payments.bankAccount']);
        $invoicePreviewData = $this->mapSaleToThemePreviewData($sale);
        $invoicePreviewData['title'] = $this->documentLabelForSale($sale);

        $themeDefaults = $this->resolveStoredInvoiceThemeConfig($sale, $request);
        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $invoicePreviewData,
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
        ])->setPaper('a4', 'portrait');

        if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        }

        $subject = trim((string) ($data['subject'] ?? ''));
        if ($subject === '') {
            $subject = $this->documentLabelForSale($sale);
        }

        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '') {
            $message = $this->documentEmailBodyForSale($sale);
        }

        $fileName = $this->documentFileNameForSale($sale);

        try {
            Mail::raw($message, function ($mail) use ($data, $subject, $pdf, $fileName) {
                $mail->to($data['email'])
                    ->subject($subject)
                    ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);

                $fromAddress = config('mail.from.address');
                if (!empty($fromAddress)) {
                    $mail->from($fromAddress, config('mail.from.name'));
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send email right now.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully.',
        ]);
    }

    public function emailProforma(Request $request, Sale $sale)
    {
        return $this->emailDocument($request, $sale, 'proforma');
    }

    public function paymentIn(Request $request)
    {
        return $this->index($request);
    }

    private function documentLabelForSale(Sale $sale): string
    {
        return match ($sale->type) {
            'proforma' => 'Proforma Invoice',
            'sale_order' => 'Sale Order',
            'sale_return' => 'Sale Return',
            'delivery_challan' => 'Delivery Challan',
            default => 'Invoice',
        };
    }

    private function documentFileNameForSale(Sale $sale): string
    {
        $prefix = match ($sale->type) {
            'proforma' => 'proforma-invoice',
            'sale_order' => 'sale-order',
            'sale_return' => 'sale-return',
            'delivery_challan' => 'delivery-challan',
            default => 'invoice',
        };

        return $prefix . '-' . ($sale->bill_number ?: $sale->id) . '.pdf';
    }

    private function documentEmailBodyForSale(Sale $sale): string
    {
        $label = $this->documentLabelForSale($sale);

        return "Dear Sir,\n\nPlease find the attached {$label}.\n\nThanks and regards.";
    }

    private function buildInvoiceViewData(Request $request): array
    {
        $type = strtolower(trim((string) $request->query('type', '')));
     
        $selectedTheme = (string) $request->query('theme', 'tally');
        $selectedColor = (string) $request->query('color', '#707070');
        $selectedColor2 = (string) $request->query('color2', '#ff981f');
        $reactAssets = $this->resolveReactInvoiceAssets();

        $saveCloseUrl = route('sale.index');

        if (in_array($type, ['sale_order', 'sale-order'], true)) {
            $saveCloseUrl = route('sale-order');
        } elseif (in_array($type, ['return-order', 'sale_return', 'sale-return'], true)) {
            $saveCloseUrl = route('sale-return');
        } elseif (in_array($type, ['proforma', 'proforma-invoice'], true)) {
            $saveCloseUrl = route('proforma-invoice');
        }


        $viewData = [
            'invoicePreviewData' => [],
            'pageTitle' => 'Preview',
            'browserTabLabel' => 'Invoice Preview',
            'saveCloseUrl' => $saveCloseUrl,
            'documentType' => $type,
            'initialMode' => $selectedTheme,
            'initialRegularThemeId' => (int) $request->query('theme_id', 1),
            'initialThermalThemeId' => (int) $request->query('theme_id', 1),
            'initialAccent' => $selectedColor,
            'initialAccent2' => $selectedColor2,
            'reactCss' => $reactAssets['css_url'],
            'reactJs' => $reactAssets['js_url'],
        ];

        if ($request->filled('sale_id')) {
            $sale = Sale::with(['items.item', 'party', 'broker', 'challanDetail', 'details', 'payments.bankAccount'])
                ->findOrFail($request->integer('sale_id'));

            $docType = $request->query('doc');
            $invoiceSource = $sale;
            $savedTheme = $this->resolveSavedSaleThemeState($sale);

            if ($docType === 'delivery_challan') {
                if ($sale->type === 'delivery_challan') {
                    $invoiceSource = $sale;
                } elseif ($sale->reference_id) {
                    $sourceChallan = Sale::with(['items.item', 'party', 'broker', 'challanDetail', 'details'])
                        ->whereKey($sale->reference_id)
                        ->where('type', 'delivery_challan')
                        ->first();
                    if ($sourceChallan) {
                        $invoiceSource = $sourceChallan;
                    }
                }
            }

            $viewData['sale'] = $sale;
            $viewData['invoicePreviewData'] = $this->mapSaleToThemePreviewData($invoiceSource);
            $viewData['browserTabLabel'] = ($invoiceSource->type === 'delivery_challan' ? 'Delivery Challan' : 'Invoice') . ' #' . ($invoiceSource->bill_number ?: $invoiceSource->id);
            $themeDefaults = $this->resolveStoredInvoiceThemeConfig($invoiceSource, $request);
            $viewData['initialMode'] = $themeDefaults['mode'];
            $viewData['initialRegularThemeId'] = $themeDefaults['regularThemeId'];
            $viewData['initialThermalThemeId'] = $themeDefaults['thermalThemeId'];
            $viewData['initialAccent'] = $themeDefaults['accent'];
            $viewData['initialAccent2'] = $themeDefaults['accent2'];
            $viewData['themeSaveUrl'] = route('sale.invoice-theme.store', $invoiceSource);

            if ($invoiceSource->type === 'proforma' || in_array($type, ['proforma', 'proforma-invoice'], true)) {
                $viewData['saveCloseUrl'] = route('proforma-invoice');
                $viewData['documentType'] = 'proforma-invoice';
                $viewData['browserTabLabel'] = 'Proforma #' . ($invoiceSource->bill_number ?: $invoiceSource->id);
            }
        } elseif ($request->filled('purchase_id')) {
            $purchase = Purchase::with(['items', 'party', 'payments.bankAccount'])
                ->findOrFail($request->integer('purchase_id'));

            $viewData['purchase'] = $purchase;
            $viewData['invoicePreviewData'] = $this->mapPurchaseToThemePreviewData($purchase);
            $viewData['browserTabLabel'] = ($purchase->type === 'purchase_return' ? 'Purchase Return' : 'Purchase') . ' #' . ($purchase->bill_number ?: $purchase->id);
            $viewData['saveCloseUrl'] = $purchase->type === 'purchase_return' ? route('purchase-return') : route('purchase-expenses');
            $viewData['documentType'] = $purchase->type === 'purchase_return' ? 'purchase-return' : 'purchase';

            if ($purchase->type === 'purchase_return') {
                $themeDefaults = $this->resolveStoredPurchaseThemeConfig($purchase, $request);
                $viewData['initialMode'] = $themeDefaults['mode'];
                $viewData['initialRegularThemeId'] = $themeDefaults['regularThemeId'];
                $viewData['initialThermalThemeId'] = $themeDefaults['thermalThemeId'];
                $viewData['initialAccent'] = $themeDefaults['accent'];
                $viewData['initialAccent2'] = $themeDefaults['accent2'];
                $viewData['themeSaveUrl'] = route('purchase-return.invoice-theme.store', $purchase);
            }
        } elseif ($request->filled('payment_in')) {
            $paymentInRecord = PaymentIn::with(['party', 'bankAccount'])
                ->findOrFail($request->integer('payment_in'));

            $viewData['invoicePreviewData'] = $this->mapPaymentInToThemePreviewData($paymentInRecord);
            $viewData['browserTabLabel'] = 'Receipt #' . ($paymentInRecord->receipt_no ?: $paymentInRecord->id);
            $viewData['saveCloseUrl'] = route('payment-in');
        }

        return $viewData;
    }

    private function mapPurchaseToThemePreviewData(Purchase $purchase): array
    {
        $items = $purchase->items->map(function ($item) use ($purchase) {
            $quantity = (float) ($item->quantity ?? 0);
            $rate = (float) ($item->unit_price ?? 0);
            $amount = (float) ($item->amount ?? ($quantity * $rate));

            return [
                'name' => (string) ($item->item_name ?: 'Item'),
                'hsn' => (string) ($item->item_code ?? ''),
                'qty' => $quantity,
                'unit' => (string) ($item->unit ?? ''),
                'rate' => $rate,
                'disc' => (string) ($item->discount ?? '0'),
                'gst' => (float) ($purchase->tax_pct ?? 0) . '%',
                'amt' => $amount,
                'customFieldSummary' => trim((string) ($item->item_description ?? '')),
            ];
        })->values()->all();

        $billDate = $purchase->bill_date ?: $purchase->created_at;
        $dueDate = $purchase->due_date ?: $billDate;
        $firstBankPayment = $purchase->payments->first(fn ($payment) => $payment->bankAccount);

        return [
            'title' => $purchase->type === 'purchase_return' ? 'Purchase Return / Debit Note' : 'Purchase',
            'businessName' => config('app.name', 'Vyapar'),
            'phone' => '',
            'invoiceNo' => (string) ($purchase->bill_number ?: $purchase->id),
            'date' => optional($billDate)->format('d/m/Y') ?: '',
            'time' => optional($purchase->created_at)->format('h:i A') ?: '',
            'dueDate' => optional($dueDate)->format('d/m/Y') ?: '',
            'billTo' => (string) ($purchase->party_name ?: ($purchase->party?->name ?? '')),
            'billAddress' => (string) ($purchase->billing_address ?? ''),
            'billPhone' => (string) ($purchase->phone ?? ''),
            'shipTo' => (string) ($purchase->billing_address ?? ''),
            'items' => $items,
            'subtotal' => (float) ($purchase->total_amount ?? 0),
            'discount' => (float) ($purchase->discount_rs ?? 0),
            'taxAmount' => (float) ($purchase->tax_amount ?? 0),
            'total' => (float) ($purchase->grand_total ?? 0),
            'received' => (float) ($purchase->paid_amount ?? 0),
            'balance' => (float) ($purchase->balance ?? 0),
            'description' => (string) ($purchase->description ?: 'Thanks for doing business with us!'),
            'bankName' => (string) ($firstBankPayment?->bankAccount?->display_name ?? ''),
            'bankAccountNumber' => (string) ($firstBankPayment?->bankAccount?->account_number ?? ''),
            'bankAccountHolder' => (string) ($firstBankPayment?->bankAccount?->account_holder_name ?? ''),
        ];
    }

    private function resolveStoredPurchaseThemeConfig(Purchase $purchase, Request $request): array
    {
        $stored = $purchase->invoice_theme;
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }
        $stored = is_array($stored) ? $stored : [];

        $mode = (string) $request->query('mode', $stored['mode'] ?? 'regular');
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';
        $regularThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $thermalThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $accent = (string) $request->query('accent', (string) ($stored['accent'] ?? '#1f4e79'));
        $accent2 = (string) $request->query('accent2', (string) ($stored['accent2'] ?? '#ff981f'));

        return [
            'mode' => $mode,
            'regularThemeId' => $regularThemeId > 0 ? $regularThemeId : 1,
            'thermalThemeId' => $thermalThemeId > 0 ? $thermalThemeId : 1,
            'accent' => $accent !== '' ? $accent : '#1f4e79',
            'accent2' => $accent2 !== '' ? $accent2 : '#ff981f',
        ];
    }

    private function resolveInvoiceThemeConfig(string $mode, int $themeId): array
    {
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';

        $themes = $mode === 'thermal'
            ? [
                1 => ['name' => 'Thermal Theme 1', 'variant' => 'thermal1'],
                2 => ['name' => 'Thermal Theme 2', 'variant' => 'thermal2'],
                3 => ['name' => 'Thermal Theme 3', 'variant' => 'thermal3'],
                4 => ['name' => 'Thermal Theme 4', 'variant' => 'thermal4'],
                5 => ['name' => 'Thermal Theme 5', 'variant' => 'thermal5'],
            ]
            : [
                1 => ['name' => 'Telly Theme', 'variant' => 'classicA'],
                2 => ['name' => 'Landscape Theme 1', 'variant' => 'purpleA'],
                3 => ['name' => 'Landscape Theme 2', 'variant' => 'classicB'],
                4 => ['name' => 'Tax Theme 1', 'variant' => 'purpleB'],
                5 => ['name' => 'Tax Theme 2', 'variant' => 'classicC'],
                6 => ['name' => 'Tax Theme 3', 'variant' => 'modernPurple'],
                7 => ['name' => 'Tax Theme 4', 'variant' => 'purpleC'],
                8 => ['name' => 'Tax Theme 5', 'variant' => 'classicSale'],
                9 => ['name' => 'Tax Theme 6', 'variant' => 'taxTheme6'],
                10 => ['name' => 'Double Divine', 'variant' => 'doubleDivine'],
                11 => ['name' => 'French Elite', 'variant' => 'frenchElite'],
                12 => ['name' => 'Theme 1', 'variant' => 'theme1'],
                13 => ['name' => 'Theme 2', 'variant' => 'theme2'],
                14 => ['name' => 'Theme 3', 'variant' => 'theme3'],
                15 => ['name' => 'Theme 4', 'variant' => 'theme4'],
            ];

        $theme = $themes[$themeId] ?? reset($themes);

        return [
            'id' => $themeId,
            'mode' => $mode,
            'name' => $theme['name'],
            'variant' => $theme['variant'],
        ];
    }

    private function resolveStoredInvoiceThemeConfig(?Sale $sale, Request $request): array
    {
        $stored = $sale?->invoice_theme;

        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        $mode = (string) ($request->query('mode', $stored['mode'] ?? 'regular'));
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';

        $regularThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $thermalThemeId = (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1));
        $accent = (string) $request->query('accent', (string) ($stored['accent'] ?? '#1f4e79'));
        $accent2 = (string) $request->query('accent2', (string) ($stored['accent2'] ?? '#ff981f'));

        return [
            'mode' => $mode,
            'regularThemeId' => $regularThemeId > 0 ? $regularThemeId : 1,
            'thermalThemeId' => $thermalThemeId > 0 ? $thermalThemeId : 1,
            'accent' => $accent !== '' ? $accent : '#1f4e79',
            'accent2' => $accent2 !== '' ? $accent2 : '#ff981f',
        ];
    }

    private function resolveSavedSaleThemeState(?Sale $sale): array
    {
        $stored = $sale?->invoice_theme;

        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        return [
            'mode' => (string) ($stored['mode'] ?? 'regular'),
            'regularThemeId' => (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1)),
            'thermalThemeId' => (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1)),
            'accent' => (string) ($stored['accent'] ?? '#1f4e79'),
            'accent2' => (string) ($stored['accent2'] ?? '#ff981f'),
        ];
    }

    private function resolveChromeExecutable(): ?string
    {
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveReactInvoiceAssets(): array
    {
        $candidates = $this->reactInvoiceAssetCandidates();

        $resolved = null;

        foreach ($candidates as $source => $candidate) {
            $assetDirectory = $candidate['path'];
            if (! File::isDirectory($assetDirectory)) {
                continue;
            }

            $cssFile = collect(File::glob($assetDirectory . DIRECTORY_SEPARATOR . 'index-*.css'))
                ->sortByDesc(fn ($path) => filemtime($path))
                ->first();
            $jsFile = collect(File::glob($assetDirectory . DIRECTORY_SEPARATOR . 'index-*.js'))
                ->sortByDesc(fn ($path) => filemtime($path))
                ->first();

            if (! $cssFile || ! $jsFile) {
                continue;
            }

            $latestTimestamp = max(
                @filemtime($cssFile) ?: 0,
                @filemtime($jsFile) ?: 0
            );

            $payload = [
                'timestamp' => $latestTimestamp,
                'source' => $source,
                'css_path' => $cssFile,
                'js_path' => $jsFile,
                'css_url' => route('invoice.react-asset', ['source' => $source, 'file' => basename($cssFile)]) . '?v=' . (@filemtime($cssFile) ?: time()),
                'js_url' => route('invoice.react-asset', ['source' => $source, 'file' => basename($jsFile)]) . '?v=' . (@filemtime($jsFile) ?: time()),
            ];

            if ($resolved === null || $latestTimestamp > $resolved['timestamp']) {
                $resolved = $payload;
            }
        }

        if ($resolved) {
            return [
                'css_path' => $resolved['css_path'],
                'js_path' => $resolved['js_path'],
                'css_url' => $resolved['css_url'],
                'js_url' => $resolved['js_url'],
            ];
        }

        return [
            'css_path' => null,
            'js_path' => null,
            'css_url' => null,
            'js_url' => null,
        ];
    }

    private function reactInvoiceAssetCandidates(): array
    {
        return [
            'root' => [
                'path' => base_path('react-invoice/assets'),
            ],
            'public_parent' => [
                'path' => dirname(public_path()) . DIRECTORY_SEPARATOR . 'react-invoice' . DIRECTORY_SEPARATOR . 'assets',
            ],
            'base_parent' => [
                'path' => dirname(base_path()) . DIRECTORY_SEPARATOR . 'react-invoice' . DIRECTORY_SEPARATOR . 'assets',
            ],
            'dist' => [
                'path' => base_path('dist/assets'),
            ],
            'public' => [
                'path' => public_path('react-invoice/assets'),
            ],
            'nested_public' => [
                'path' => public_path('react-invoice/react-invoice/assets'),
            ],
        ];
    }

    private function mapSaleToThemePreviewData(Sale $sale): array
    {
        $sale->loadMissing(['items.item', 'challanDetail', 'details', 'broker', 'party']);
        $bankAccount = $sale->payments
            ->pluck('bankAccount')
            ->filter()
            ->first();

        if (!$bankAccount) {
            $bankAccount = BankAccount::where('print_on_invoice', true)
                ->orderBy('id')
                ->first();
        }

        $taxPct = $this->formatPercentValue($sale->tax_pct);

        $items = $sale->items->map(function ($item) use ($taxPct) {
            $quantity = (float) ($item->quantity ?? 0);
            $rate = (float) ($item->unit_price ?? 0);
            $amount = (float) ($item->amount ?? 0);
            $itemDefinitions = collect($item->item?->custom_fields ?? []);
            $customFieldsSource = $item->custom_fields ?? [];
            if (empty($customFieldsSource)) {
                $extraFields = is_array($item->extra_fields ?? null) ? $item->extra_fields : [];
                $customFieldsSource = collect(range(1, 6))
                    ->map(function ($index) use ($extraFields) {
                        $value = trim((string) ($extraFields['custom_field_' . $index] ?? ''));
                        if ($value === '') {
                            return null;
                        }

                        return [
                            'key' => 'custom_field_' . $index,
                            'enabled' => true,
                            'label' => 'Custom Field ' . $index,
                            'show_in_print' => true,
                            'value' => $value,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            }

            $customFields = collect($customFieldsSource ?: $itemDefinitions->all())
                ->values()
                ->map(function ($field, $index) use ($itemDefinitions) {
                    $definition = $itemDefinitions->get($index, []);
                    if (is_array($field)) {
                        $label = trim((string) ($field['label'] ?? $field['name'] ?? ''));
                        $definitionLabel = is_array($definition) ? trim((string) ($definition['label'] ?? $definition['name'] ?? '')) : '';
                        if ($label === '' || preg_match('/^Custom Field\s*\d+$/i', $label)) {
                            $label = $definitionLabel;
                        }
                        return [
                            'key' => (string) ($field['key'] ?? ''),
                            'enabled' => (bool) ($field['enabled'] ?? true),
                            'label' => $label,
                            'show_in_print' => (bool) ($field['show_in_print'] ?? true),
                            'value' => trim((string) ($field['value'] ?? $field['text'] ?? '')),
                        ];
                    }

                    $definitionLabel = is_array($definition) ? trim((string) ($definition['label'] ?? $definition['name'] ?? '')) : '';
                    return [
                        'key' => '',
                        'enabled' => true,
                        'label' => $definitionLabel,
                        'show_in_print' => true,
                        'value' => trim((string) $field),
                    ];
                })
                ->filter(function (array $field) {
                    return $field['enabled'] && $field['show_in_print'] && ($field['label'] !== '' || $field['value'] !== '');
                })
                ->values()
                ->all();

            if ($amount <= 0 && $quantity > 0 && $rate > 0) {
                $amount = round($quantity * $rate, 2);
            }

            return [
                'name' => (string) ($item->item_name ?: ($item->item?->name ?: 'Item')),
                'hsn' => (string) ($item->item_code ?: ($item->item?->item_code ?: '')),
                'qty' => (string) ($item->quantity ?? 0),
                'tadaat' => (string) ($item->quantity ?? 0),
                'gross_w' => (float) ($item->gross_w ?? 0),
                'net_w' => (float) ($item->net_w ?? 0),
                'unit' => (string) ($item->unit ?: ($item->item?->unit ?: '')),
                'rate' => $rate,
                'disc' => number_format((float) ($item->discount ?? 0), 2, '.', ''),
                'gst' => $taxPct,
                'amt' => $amount,
                'customFields' => $customFields,
                'customFieldSummary' => collect($customFields)
                    ->map(function ($field) {
                        if (!is_array($field)) {
                            return trim((string) $field);
                        }

                        $label = trim((string) ($field['label'] ?? ''));
                        $value = trim((string) ($field['value'] ?? ''));
                        if ($label === '' && $value === '') {
                            return '';
                        }

                        return $value !== '' ? ($label !== '' ? $label . ': ' . $value : $value) : $label;
                    })
                    ->filter()
                    ->implode(' | '),
                'amount' => $amount,
            ];
        })->values()->all();

        if (empty($items)) {
            $items[] = [
                'name' => 'Item',
                'hsn' => '',
                'qty' => '0',
                'tadaat' => '0',
                'gross_w' => 0,
                'net_w' => 0,
                'unit' => '',
                'rate' => 0,
                'disc' => '0.00',
                'gst' => $taxPct,
                'amt' => 0,
                'amount' => 0,
            ];
        }

        $createdAt = $sale->created_at instanceof Carbon ? $sale->created_at : Carbon::parse($sale->created_at);
        $invoiceDate = $sale->invoice_date ? Carbon::parse($sale->invoice_date) : $createdAt;
        $challanDetail = $sale->challanDetail;
        $saleDetail = $sale->details;
        $transportBroker = $this->resolveTransportationBroker($sale, $saleDetail);
        $partyCity = (string) ($sale->party?->city ?: '');
        if ($sale->type === 'delivery_challan' && $sale->challanDetail?->invoice_date) {
            $invoiceDate = Carbon::parse($sale->challanDetail->invoice_date);
        }

        $paymentsReceived = (float) $sale->payments
            ->sum('amount');

        $totalAmount = (float) ($sale->grand_total ?? 0);
        $storedBalance = (float) ($sale->balance ?? 0);

        $receivedAmount = (float) ($sale->received_amount ?? 0);
        $receivedFromBalance = $totalAmount > 0 ? max($totalAmount - $storedBalance, 0) : 0;
        $receivedAmount = max($receivedAmount, $paymentsReceived, $receivedFromBalance);

        $partyExtraFields = [];
        foreach ([
            [AppSetting::getValue('party_additional_field_1_name', ''), AppSetting::getValue('party_additional_field_1_print', '0') === '1'],
            [AppSetting::getValue('party_additional_field_2_name', ''), AppSetting::getValue('party_additional_field_2_print', '0') === '1'],
        ] as [$label, $showInPrint]) {
            $label = trim((string) $label);
            if ($label !== '' && $showInPrint) {
                $partyExtraFields[] = $label;
            }
        }

        $partyCustomFields = collect($sale->party?->custom_fields ?? [])
            ->map(function ($field) {
                if (is_array($field)) {
                    $field = $field['label'] ?? $field['value'] ?? $field['name'] ?? '';
                }

                return trim((string) $field);
            })
            ->filter()
            ->values()
            ->all();

        $invoiceNumber = $sale->bill_number ?: $sale->id;
        if ($sale->type === 'delivery_challan' && $sale->challanDetail?->challan_number) {
            $invoiceNumber = $sale->challanDetail->challan_number;
        }

        $adjustmentRows = [];
        foreach ($this->normalizeCustomExpenseRows($saleDetail?->custom_expenses) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $mode = strtoupper((string) ($row['mode'] ?? $row['operator'] ?? ''));
            $amount = (float) ($row['amount'] ?? $row['value'] ?? 0);
            if (!in_array($mode, ['S', '-'], true) || $amount <= 0) {
                continue;
            }

            $label = trim((string) ($row['details'] ?? $row['tafseel'] ?? $row['title'] ?? $row['heading'] ?? ''));
            if ($label === '') {
                $label = $mode === 'S' ? 'S Mode Adjustment' : 'Minus Mode Adjustment';
            }

            $adjustmentRows[] = [
                'label' => $label,
                'amount' => round($amount, 2),
                'mode' => $mode,
            ];
        }

        return [
            'title' => $sale->type === 'invoice' ? 'Invoice' : ucwords(str_replace('_', ' ', (string) $sale->type)),
            'businessName' => (string) config('app.name', 'My Company'),
            'businessEmail' => (string) config('mail.from.address', ''),
            'phone' => (string) ($sale->phone ?: ($sale->party?->phone ?: '')),
            'invoiceNo' => (string) $invoiceNumber,
            'date' => $invoiceDate->format('d/m/Y'),
            'time' => $createdAt->format('h:i A'),
            'dueDate' => ($sale->due_date ? Carbon::parse($sale->due_date) : $invoiceDate)->format('d/m/Y'),
            'rate' => (float) ($sale->rate ?? 0),
            'billTo' => (string) ($sale->display_party_name !== '-' ? $sale->display_party_name : 'Walk-in Customer'),
            'billAddress' => (string) ($sale->billing_address ?: ''),
            'billPhone' => (string) ($sale->phone ?: ($sale->party?->phone ?: '')),
            'shipTo' => (string) ($sale->shipping_address ?: $sale->billing_address ?: ''),
            'description' => (string) ($sale->description ?: 'Thanks for doing business with us!'),
            'subtotal' => (float) ($sale->total_amount ?? 0),
            'discount' => (float) ($sale->discount_rs ?? 0),
            'taxAmount' => (float) ($sale->tax_amount ?? 0),
            'total' => $totalAmount,
            'received' => $receivedAmount,
            'balance' => (float) ($sale->balance ?? max($totalAmount - $receivedAmount, 0)),
            'items' => $items,
            'bankName' => (string) ($bankAccount?->bank_name ?: $bankAccount?->display_name ?: ''),
            'bankAccountNumber' => (string) ($bankAccount?->account_number ?: ''),
            'bankAccountHolder' => (string) ($bankAccount?->account_holder_name ?: ''),
            'partyExtraFields' => $partyExtraFields,
            'partyCustomFields' => $partyCustomFields,
            'brokerName' => (string) ($challanDetail?->broker_name ?: $transportBroker['name'] ?: $sale->broker?->name ?: ''),
            'brokerPhone' => (string) ($challanDetail?->broker_phone ?: $transportBroker['phone'] ?: $sale->broker?->phone ?: ''),
            'city' => $partyCity,
            'warehouseName' => (string) ($challanDetail?->warehouse_name ?: ''),
            'holderName' => (string) ($challanDetail?->warehouse_handler_name ?: ''),
            'documentType' => (string) $sale->type,
            'totalInWords' => $this->formatAmountInWords($totalAmount),
            'deliveryChallanFor' => (string) ($sale->display_party_name !== '-' ? $sale->display_party_name : 'Walk-in Customer'),
            'deliveryChallanPhone' => (string) ($sale->phone ?: ($sale->party?->phone ?: '')),
            'deliveryPerson' => (string) ($saleDetail?->delivery_person ?: ''),
            'transportBrokerName' => (string) ($transportBroker['name'] ?: $sale->broker?->name ?: ''),
            'transportBrokerCity' => (string) ($transportBroker['city'] ?: ''),
            'transportBrokerPhone' => (string) ($transportBroker['phone'] ?: $sale->broker?->phone ?: ''),
            'transportName' => (string) ($saleDetail?->goods_name ?: ''),
            'biltiNo' => (string) ($saleDetail?->bilti_no ?: ''),
            'biltiGariNo' => (string) ($saleDetail?->bilti_gari_no ?: ($challanDetail?->vehicle_number ?: '')),
            'transportCity' => (string) ($saleDetail?->city ?: $partyCity),
            'transportDetail' => (string) ($saleDetail?->details_extra ?: ''),
            'adjustmentRows' => $adjustmentRows,
        ];
    }

    private function normalizeCustomExpenseRows($rawRows): array
    {
        if (is_array($rawRows)) {
            return $rawRows;
        }

        if (is_string($rawRows) && trim($rawRows) !== '') {
            $decoded = json_decode($rawRows, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function resolveTransportationBroker(Sale $sale, ?SaleDetail $saleDetail): array
    {
        foreach ($this->normalizeCustomExpenseRows($saleDetail?->custom_expenses) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $mode = strtoupper((string) ($row['mode'] ?? $row['operator'] ?? ''));
            if ($mode !== 'S') {
                continue;
            }

            $accountType = strtolower((string) ($row['account_type'] ?? ''));
            $accountId = !empty($row['account_id']) ? (int) $row['account_id'] : (!empty($row['brokerId']) ? (int) $row['brokerId'] : null);

            if ($accountType !== 'broker' && empty($accountId)) {
                continue;
            }

            $broker = $accountId ? Broker::find($accountId) : null;
            $name = trim((string) ($row['account_name'] ?? $row['brokerName'] ?? $broker?->name ?? ''));

            if ($name === '' && !$broker) {
                continue;
            }

            return [
                'name' => $name,
                'city' => (string) ($broker?->city ?? ''),
                'phone' => (string) ($broker?->phone ?? ''),
            ];
        }

        return [
            'name' => (string) ($sale->broker?->name ?? ''),
            'city' => (string) ($sale->broker?->city ?? ''),
            'phone' => (string) ($sale->broker?->phone ?? ''),
        ];
    }

    private function mapPaymentInToThemePreviewData(PaymentIn $paymentIn): array
    {
        $createdAt = $paymentIn->created_at instanceof Carbon
            ? $paymentIn->created_at
            : Carbon::parse($paymentIn->created_at ?? now());

        $date = $paymentIn->date ? Carbon::parse($paymentIn->date) : $createdAt;
        $amount = (float) ($paymentIn->amount ?? 0);

        return [
            'title' => 'Payment In Invoice',
            'businessName' => (string) config('app.name', 'My Company'),
            'businessEmail' => (string) config('mail.from.address', ''),
            'phone' => (string) ($paymentIn->bankAccount?->phone ?: ''),
            'invoiceNo' => (string) ($paymentIn->receipt_no ?: $paymentIn->id),
            'date' => $date->format('d/m/Y'),
            'time' => $createdAt->format('h:i A'),
            'dueDate' => $date->format('d/m/Y'),
            'billTo' => (string) ($paymentIn->party?->name ?: 'Customer'),
            'billAddress' => (string) ($paymentIn->party?->billing_address ?: ''),
            'billPhone' => (string) ($paymentIn->party?->phone ?: ''),
            'shipTo' => (string) ($paymentIn->party?->billing_address ?: ''),
            'description' => (string) ($paymentIn->description ?: 'Payment received.'),
            'subtotal' => $amount,
            'discount' => 0,
            'taxAmount' => 0,
            'total' => $amount,
            'received' => $amount,
            'balance' => 0,
            'items' => [[
                'name' => (string) (($paymentIn->payment_type ?: 'Payment') . ' Payment'),
                'hsn' => (string) ($paymentIn->reference_no ?: ''),
                'qty' => '1',
                'unit' => '',
                'rate' => $amount,
                'disc' => '0.00',
                'gst' => '0%',
                'amt' => $amount,
            ]],
            'bankName' => (string) ($paymentIn->bankAccount?->bank_name ?: $paymentIn->bankAccount?->display_name ?: ''),
            'bankAccountNumber' => (string) ($paymentIn->bankAccount?->account_number ?: ''),
            'bankAccountHolder' => (string) ($paymentIn->bankAccount?->account_holder_name ?: ''),
            'totalInWords' => $this->formatAmountInWords($amount),
        ];
    }

    private function formatAmountInWords(float $amount): string
    {
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            $whole = (int) floor(abs($amount));
            $fraction = (int) round((abs($amount) - $whole) * 100);

            $words = ucfirst((string) $formatter->format($whole)) . ' Rupees';

            if ($fraction > 0) {
                $words .= ' and ' . (string) $formatter->format($fraction) . ' Paisa';
            }

            return trim($words) . ' only';
        }

        return 'Rupees ' . number_format($amount, 2);
    }

    private function formatPercentValue($value): string
    {
        $number = (float) ($value ?? 0);

        if (fmod($number, 1.0) === 0.0) {
            return (string) ((int) $number) . '%';
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.') . '%';
    }
}
