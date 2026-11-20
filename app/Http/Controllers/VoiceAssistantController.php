<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use App\Models\Party;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VoiceAssistantController extends Controller
{
    /**
     * Recursively ensure all array values are valid UTF-8
     */
    protected function cleanUtf8(mixed $data): mixed
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        if (is_array($data)) {
            return array_map([$this, 'cleanUtf8'], $data);
        }
        return $data;
    }

    /**
     * Multibyte UTF-8 safe JSON response helper
     */
    protected function jsonResponse(array $data, int $status = 200): JsonResponse
    {
        $clean = $this->cleanUtf8($data);
        return response()->json($clean, $status, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Process Voice Input (Audio or Urdu/Roman Urdu Transcript)
     */
    public function process(Request $request): JsonResponse
    {
        try {
            $company = Auth::user()?->currentCompany();
            if (!$company) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'No active company found for this session.'
                ], 400);
            }

            $transcript = $request->input('transcript', '');

            // Handle audio file upload if provided
            if ($request->hasFile('audio') && empty($transcript)) {
                $transcript = $this->transcribeAudio($request->file('audio'));
            }

            // Convert any Devanagari (Hindi) input to pure Urdu Nastaliq
            $transcript = $this->convertDevanagariToUrdu($transcript);

            if (empty(trim($transcript))) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'کوئی آواز یا متن موصول نہیں ہوا۔ براہ کرم دوبارہ بولیں۔ (No speech detected)'
                ], 422);
            }

            // Detect Intent
            $intent = $this->detectIntent($transcript);

            if ($intent === 'CONVERSATION') {
                return $this->handleConversation($transcript);
            } elseif ($intent === 'TODAY_UDHAR_QUERY') {
                return $this->handleTodayUdharQuery($company, $transcript);
            } elseif ($intent === 'STOCK_INWARD') {
                return $this->handleStockInward($company, $transcript);
            } elseif ($intent === 'KHATA_QUERY') {
                return $this->handleKhataQuery($company, $transcript);
            } elseif ($intent === 'STOCK_QUERY') {
                return $this->handleStockQuery($company, $transcript);
            } elseif ($intent === 'PAYMENT_RECEIVED') {
                return $this->handlePaymentReceived($company, $transcript);
            } else {
                // Default: SALE_ENTRY
                return $this->handleSaleEntry($company, $transcript);
            }

        } catch (\Exception $e) {
            Log::error('VoiceAssistant process error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'وائس پراسیسنگ میں خرابی پیش آگئی: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Chit-chat, Greetings or Questions about Urdu Support
     */
    protected function handleConversation(string $transcript): JsonResponse
    {
        $lower = mb_strtolower($transcript);

        if (str_contains($lower, 'اردو میں کیوں نہیں') || str_contains($lower, 'شو ہو رہا') || str_contains($lower, 'ہندی')) {
            $voiceReply = "ارسلان بھائی! اب سب کچھ سو فیصد خالص اردو رسم الخط (نستعلیق) میں ہی ظاہر ہوگا۔ الیکس اے آئی اب مکمل طور پر خالص اردو میں بولنے اور لکھنے کے لیے تیار ہے۔ آپ گاہک کا نام اور سامان بولیں یا کھاتہ معلوم کریں۔";
        } elseif (str_contains($lower, 'ارسلان') || str_contains($lower, 'arslan')) {
            $voiceReply = "وعلیکم السلام ارسلان بھائی! میں بالکل ٹھیک ہوں۔ میں آپ کا الیکس اے آئی وائس اسسٹنٹ ہوں۔ بتائیں آج کس گاہک کا بل بنانا ہے یا کس کا کھاتہ دیکھنا ہے؟";
        } elseif (str_contains($lower, 'کیسے ہو') || str_contains($lower, 'ہاؤ آر یو') || str_contains($lower, 'how are you')) {
            $voiceReply = "اللہ کا شکر ہے میں بالکل ٹھیک ہوں۔ میں آپ کا الیکس اے آئی وائس اسسٹنٹ ہوں۔ آپ گاہک کی خریداری بولیں، میں فوراً بل بنا دوں گا۔";
        } else {
            $voiceReply = "السلام علیکم! میں الیکس اے آئی وائس اسسٹنٹ ہوں۔ آپ دکان کا کوئی بھی بل بنانے یا کھاتہ دیکھنے کے لیے بول سکتے ہیں۔";
        }

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'CONVERSATION',
            'transcript' => $transcript,
            'message' => $voiceReply,
            'voice_reply' => $voiceReply,
        ]);
    }

    /**
     * Handle Sale Entry via Voice
     */
    protected function handleSaleEntry(Company $company, string $transcript): JsonResponse
    {
        $parsed = $this->parseUrduSaleSpeech($company, $transcript);

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'SALE_ENTRY',
            'transcript' => $transcript,
            'data' => $parsed,
            'voice_reply' => $parsed['voice_summary'],
        ]);
    }

    /**
     * Confirm & Save Voice Generated Sale to Database
     */
    public function confirmSale(Request $request): JsonResponse
    {
        $company = Auth::user()?->currentCompany();
        if (!$company) {
            return $this->jsonResponse(['success' => false, 'message' => 'Company not found'], 400);
        }

        $validated = $request->validate([
            'party_name' => 'required|string|max:191',
            'party_phone' => 'nullable|string|max:30',
            'payment_type' => 'nullable|string', // credit or cash
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($company, $validated) {
            // 1. Find or create party (Customer)
            $partyName = trim($validated['party_name']);
            $party = Party::where('company_id', $company->id)
                ->where(function ($q) use ($partyName) {
                    $q->where('name', 'LIKE', $partyName)
                      ->orWhere('name', 'LIKE', "%{$partyName}%");
                })->first();

            if (!$party) {
                $party = Party::create([
                    'company_id' => $company->id,
                    'name' => $partyName,
                    'phone' => $validated['party_phone'] ?? null,
                    'party_type' => 'customer',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'balance' => 0,
                    'is_active' => true,
                ]);
            }

            // 2. Generate unique Bill Number
            $lastSale = Sale::where('company_id', $company->id)->latest('id')->first();
            $nextNo = $lastSale ? ((int) preg_replace('/\D/', '', $lastSale->bill_number ?: '0') + 1) : 1001;
            $billNumber = 'BILL-' . str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);

            $grandTotal = 0;
            $totalQty = 0;

            foreach ($validated['items'] as $it) {
                $grandTotal += (float) $it['amount'];
                $totalQty += (float) $it['quantity'];
            }

            $paymentType = $validated['payment_type'] ?? 'credit';
            $receivedAmount = $paymentType === 'cash' ? $grandTotal : (float) ($validated['received_amount'] ?? 0);
            $remainingBalance = max(0, $grandTotal - $receivedAmount);

            // 3. Create Sale Record
            $sale = Sale::create([
                'company_id' => $company->id,
                'party_id' => $party->id,
                'phone' => $party->phone,
                'bill_number' => $billNumber,
                'type' => 'pos',
                'invoice_date' => now()->toDateString(),
                'payment_type' => $paymentType,
                'total_qty' => (int) ceil($totalQty),
                'total_amount' => $grandTotal,
                'grand_total' => $grandTotal,
                'received_amount' => $receivedAmount,
                'balance' => $remainingBalance,
                'status' => $remainingBalance > 0 ? 'unpaid' : 'paid',
                'description' => 'Alex AI Voice Generated Sale',
            ]);

            // 4. Create Sale Items & Deduct Stock if item exists
            foreach ($validated['items'] as $it) {
                $dbItem = Item::where('company_id', $company->id)
                    ->where(function ($q) use ($it) {
                        $q->where('name', 'LIKE', $it['name'])
                          ->orWhere('name', 'LIKE', "%{$it['name']}%");
                    })->first();

                SaleItem::create([
                    'company_id' => $company->id,
                    'sale_id' => $sale->id,
                    'item_id' => $dbItem?->id,
                    'item_name' => $it['name'],
                    'quantity' => (int) ceil($it['quantity']),
                    'unit' => $it['unit'] ?? ($dbItem?->unit ?: 'pcs'),
                    'unit_price' => (float) $it['unit_price'],
                    'amount' => (float) $it['amount'],
                ]);
            }

            // 5. Update Party Balance
            $newPartyBalance = (float) $party->current_balance + $remainingBalance;
            $party->update([
                'current_balance' => $newPartyBalance,
                'balance' => $newPartyBalance,
            ]);

            // 6. Generate WhatsApp Receipt Text
            $waMessage = $this->buildWhatsAppMessage($company, $party, $sale, $validated['items']);
            $cleanPhone = preg_replace('/\D/', '', (string)$party->phone);
            if (str_starts_with($cleanPhone, '03') && strlen($cleanPhone) === 11) {
                $cleanPhone = '92' . substr($cleanPhone, 1);
            }
            $waUrl = !empty($cleanPhone)
                ? "https://wa.me/{$cleanPhone}?text=" . urlencode($waMessage)
                : null;

            return $this->jsonResponse([
                'success' => true,
                'message' => "بل {$billNumber} کامیابی سے درج ہو گیا اور کھاتہ اپ ڈیٹ ہو گیا!",
                'sale_id' => $sale->id,
                'bill_number' => $billNumber,
                'party_name' => $party->name,
                'grand_total' => $grandTotal,
                'remaining_balance' => $remainingBalance,
                'customer_total_balance' => $newPartyBalance,
                'whatsapp_url' => $waUrl,
                'whatsapp_text' => $waMessage,
                'voice_reply' => "بل نمبر {$billNumber}، کل رقم {$grandTotal} روپے کامیابی سے درج ہو گئی۔"
            ]);
        });
    }

    /**
     * Handle Query for Today's Udhar Register ("آج کا ادھار دکھاؤ")
     */
    protected function handleTodayUdharQuery(Company $company, string $transcript): JsonResponse
    {
        $today = now()->toDateString();

        $creditSales = Sale::where('company_id', $company->id)
            ->whereDate('invoice_date', $today)
            ->where(function ($q) {
                $q->where('payment_type', 'credit')
                  ->orWhere('balance', '>', 0);
            })
            ->with(['party', 'items'])
            ->latest('id')
            ->get();

        $totalUdhar = (float) $creditSales->sum('balance');
        $count = $creditSales->pluck('party_id')->filter()->unique()->count();

        $salesList = $creditSales->take(10)->map(function ($s) {
            $itemsText = $s->items->map(fn($it) => "{$it->quantity} {$it->unit} {$it->item_name}")->implode('، ');
            return [
                'party_name' => $s->party?->name ?: 'Walk-in Customer',
                'bill_number' => $s->bill_number,
                'amount' => (float)$s->grand_total,
                'balance' => (float)$s->balance,
                'total_balance' => (float)($s->party?->current_balance ?: $s->balance),
                'items_summary' => $itemsText ?: 'کریانہ سامان',
                'time' => $s->created_at ? $s->created_at->format('h:i A') : '',
            ];
        });

        if ($count > 0) {
            $names = $creditSales->take(3)->map(fn($s) => $s->party?->name)->filter()->implode('، ');
            $voiceReply = "آج کل {$count} گاہکوں نے " . number_format($totalUdhar) . " روپے کا ادھار لیا ہے، جن میں {$names} شامل ہیں۔";
        } else {
            $voiceReply = "آج ابھی تک کسی گاہک کا نیا ادھار درج نہیں ہوا ہے۔";
        }

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'TODAY_UDHAR_QUERY',
            'transcript' => $transcript,
            'date' => $today,
            'total_udhar' => $totalUdhar,
            'customers_count' => $count,
            'sales' => $salesList,
            'register_url' => route('udhar.register'),
            'voice_reply' => $voiceReply,
        ]);
    }

    /**
     * Handle Khata Query ("حمزہ کا کھاتہ دکھاؤ")
     */
    public function handleKhataQuery(Company $company, string $transcript): JsonResponse
    {
        $customerName = $this->extractCustomerNameFromQuery($transcript);

        $partyQuery = Party::where('company_id', $company->id);
        if (!empty($customerName)) {
            $partyQuery->where(function ($q) use ($customerName) {
                $q->where('name', 'LIKE', "%{$customerName}%")
                  ->orWhere('name', 'LIKE', $customerName);
            });
        }

        $party = $partyQuery->first();

        if (!$party) {
            return $this->jsonResponse([
                'success' => false,
                'intent' => 'KHATA_QUERY',
                'transcript' => $transcript,
                'message' => "معذرت، '{$customerName}' نام کا کوئی گاہک کھاتہ میں موجود نہیں ہے۔ کیا آپ نیا کھاتہ بنانا چاہتے ہیں؟",
                'voice_reply' => "معذرت، {$customerName} نام کا کوئی گاہک نہیں ملا۔"
            ]);
        }

        // Fetch recent sales for this customer
        $recentSales = Sale::where('company_id', $company->id)
            ->where('party_id', $party->id)
            ->latest('invoice_date')
            ->take(15)
            ->get();

        $totalPurchased = (float) $recentSales->sum('grand_total');
        $totalPaid = (float) $recentSales->sum('received_amount');
        $currentBalance = (float) ($party->current_balance ?: $party->balance ?: 0);

        $voiceReply = "{$party->name} کا کل بقایا ادھار " . number_format($currentBalance) . " روپے ہے۔ پچھلے دنوں میں انہوں نے " . count($recentSales) . " خریداریاں کی ہیں۔";

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'KHATA_QUERY',
            'transcript' => $transcript,
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
                'phone' => $party->phone,
                'current_balance' => $currentBalance,
                'opening_balance' => (float)$party->opening_balance,
            ],
            'summary' => [
                'total_sales_count' => count($recentSales),
                'total_purchased' => $totalPurchased,
                'total_paid' => $totalPaid,
                'remaining_balance' => $currentBalance,
            ],
            'recent_transactions' => $recentSales->map(function ($s) {
                return [
                    'id' => $s->id,
                    'bill_number' => $s->bill_number,
                    'date' => $s->invoice_date,
                    'amount' => (float) $s->grand_total,
                    'received' => (float) $s->received_amount,
                    'balance' => (float) $s->balance,
                    'status' => $s->status,
                ];
            }),
            'voice_reply' => $voiceReply,
        ]);
    }

    /**
     * Handle Stock Query ("چینی کا سٹاک کتنا ہے؟")
     */
    protected function handleStockQuery(Company $company, string $transcript): JsonResponse
    {
        $itemName = $this->extractItemNameFromQuery($transcript);

        $items = Item::where('company_id', $company->id)
            ->when(!empty($itemName), function ($q) use ($itemName) {
                $q->where('name', 'LIKE', "%{$itemName}%");
            })
            ->take(5)
            ->get();

        if ($items->isEmpty()) {
            return $this->jsonResponse([
                'success' => false,
                'intent' => 'STOCK_QUERY',
                'message' => "معذرت، '{$itemName}' نام کا کوئی آئٹم سٹاک لسٹ میں نہیں ملا۔",
                'voice_reply' => "معذرت، {$itemName} کا اسٹاک نہیں ملا۔"
            ]);
        }

        $first = $items->first();
        $stock = $first->opening_qty ?? 0;
        $unit = $first->unit ?: 'کلو/عدد';
        $price = $first->sale_price ?: $first->price ?: 0;

        $reply = "{$first->name} کا اسٹاک {$stock} {$unit} موجود ہے، اور فروخت ریٹ {$price} روپے ہے۔";

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'STOCK_QUERY',
            'transcript' => $transcript,
            'item' => [
                'name' => $first->name,
                'stock' => $stock,
                'unit' => $unit,
                'sale_price' => $price,
            ],
            'voice_reply' => $reply
        ]);
    }

    /**
     * Handle Stock Inward / Inventory Arrival ("آج 20 بوری چینی اور 10 پیٹی گھی کا اسٹاک آیا ہے")
     */
    protected function handleStockInward(Company $company, string $transcript): JsonResponse
    {
        $rawItems = $this->extractItemsAndQuantities($transcript);

        if (empty($rawItems)) {
            $clean = preg_replace('/^(.*?)(?:اسٹاک\s+آیا|مال\s+آیا|اسٹاک\s+میں\s+جمع\s+کرو|نیا\s+مال|stock\s+inward)\s+/iu', '', $transcript);
            $rawItems = $this->extractItemsAndQuantities($clean);
        }

        $items = [];
        $urduEnglishMap = [
            'چینی' => 'Sugar',
            'گھی' => 'Ghee / Cooking Oil',
            'کشمیر گھی' => 'Kashmir Ghee',
            'آٹا' => 'Wheat Flour',
            'دال' => 'Lentils / Daal',
            'دال چنا' => 'Daal Chana',
            'دال مونگ' => 'Daal Moong',
            'دال ماش' => 'Daal Mash',
            'چاول' => 'Rice',
            'باسمتی چاول' => 'Basmati Rice',
            'پتی' => 'Tea',
            'چائے' => 'Tea',
            'صابن' => 'Soap',
            'لائف بوائے صابن' => 'Lifebuoy Soap',
            'سرف' => 'Washing Powder / Surf',
            'دودھ' => 'Milk',
            'تیل' => 'Oil',
            'نمک' => 'Salt',
            'مرچ' => 'Chilli Powder',
            'ہلدی' => 'Turmeric',
            'میدہ' => 'Fine Flour',
            'سوجی' => 'Semolina',
            'بسکٹ' => 'Biscuits',
            'انڈے' => 'Eggs',
        ];

        foreach ($rawItems as $raw) {
            $rawName = trim($raw['name']);
            $qty = (float) $raw['quantity'];
            $unit = $raw['unit'];
            $spokenRate = (float) ($raw['rate'] ?? 0);

            // Match against database items
            $dbItem = Item::where('company_id', $company->id)
                ->where(function ($q) use ($rawName) {
                    $q->where('name', 'LIKE', "%{$rawName}%")
                      ->orWhere('name', 'LIKE', $rawName);
                })->first();

            $currentStock = $dbItem ? (float) ($dbItem->stock_qty ?? $dbItem->opening_qty ?? 0) : 0;
            $newStock = $currentStock + $qty;
            $purchaseRate = $spokenRate > 0 ? $spokenRate : ($dbItem ? (float) ($dbItem->purchase_price ?: 0) : 0);

            $enName = $dbItem ? $dbItem->name : ($urduEnglishMap[$rawName] ?? ucfirst($rawName));

            $items[] = [
                'item_id' => $dbItem?->id,
                'name_ur' => $rawName,
                'name_en' => $enName,
                'current_stock' => $currentStock,
                'added_qty' => $qty,
                'new_stock' => $newStock,
                'unit' => $unit,
                'purchase_rate' => $purchaseRate,
                'in_database' => (bool) $dbItem,
            ];
        }

        if (empty($items)) {
            return $this->jsonResponse([
                'success' => false,
                'intent' => 'STOCK_INWARD',
                'message' => 'مال کی آمد میں کوئی آئٹم نہیں پہچانا جا سکا۔ براہ کرم واضح مقدار اور آئٹم کا نام بولیں۔ (مثال: 20 بوری چینی، 10 پیٹی گھی)',
                'voice_reply' => 'معذرت، سامان اور مقدار کی سمجھ نہیں آئی۔ براہ کرم دوبارہ بولیں۔'
            ]);
        }

        $itemsSummary = implode('، ', array_map(function ($it) {
            return "{$it['added_qty']} {$it['unit']} {$it['name_ur']}";
        }, $items));

        $voiceReply = "مال کی آمد کا اندراج: {$itemsSummary}۔ کیا آپ اسے اسٹاک میں شامل کرنا چاہتے ہیں؟";

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'STOCK_INWARD',
            'transcript' => $transcript,
            'items' => $items,
            'voice_reply' => $voiceReply,
        ]);
    }

    /**
     * Confirm & Save Stock Inward into Database Inventory
     */
    public function confirmStockInward(Request $request): JsonResponse
    {
        $company = Auth::user()?->currentCompany();
        if (!$company) {
            return $this->jsonResponse(['success' => false, 'message' => 'کمپنی نہیں ملی'], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|integer',
            'items.*.name' => 'required|string',
            'items.*.added_qty' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.purchase_rate' => 'nullable|numeric|min:0',
        ]);

        $updatedNames = [];

        DB::transaction(function () use ($company, $validated, &$updatedNames) {
            foreach ($validated['items'] as $it) {
                $addedQty = (float) $it['added_qty'];
                $rate = isset($it['purchase_rate']) ? (float) $it['purchase_rate'] : 0;
                $itemId = $it['item_id'] ?? null;

                $item = null;
                if ($itemId) {
                    $item = Item::where('company_id', $company->id)->find($itemId);
                }

                if (!$item) {
                    $item = Item::where('company_id', $company->id)
                        ->where('name', 'LIKE', $it['name'])
                        ->first();
                }

                if ($item) {
                    $item->opening_qty = (float) ($item->opening_qty ?? 0) + $addedQty;
                    if ($rate > 0) {
                        $item->purchase_price = $rate;
                    }
                    $item->save();

                    if (Schema::hasTable('item_stocks')) {
                        $stockRow = DB::table('item_stocks')->where('item_id', $item->id)->first();
                        if ($stockRow) {
                            $updateData = ['current_stock' => (float) ($stockRow->current_stock ?? 0) + $addedQty];
                            if ($rate > 0 && Schema::hasColumn('item_stocks', 'at_price')) {
                                $updateData['at_price'] = $rate;
                            }
                            DB::table('item_stocks')->where('id', $stockRow->id)->update($updateData);
                        }
                    }

                    $updatedNames[] = "{$addedQty} {$item->unit} {$item->name}";
                } else {
                    $newItem = Item::create([
                        'company_id' => $company->id,
                        'name' => $it['name'],
                        'unit' => $it['unit'] ?: 'kg',
                        'opening_qty' => $addedQty,
                        'purchase_price' => $rate,
                        'sale_price' => $rate > 0 ? ($rate * 1.15) : 0,
                        'is_active' => true,
                    ]);

                    if (Schema::hasTable('item_stocks')) {
                        DB::table('item_stocks')->insert([
                            'item_id' => $newItem->id,
                            'opening_stock' => $addedQty,
                            'current_stock' => $addedQty,
                            'at_price' => $rate,
                            'as_of_date' => now()->toDateString(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $updatedNames[] = "{$addedQty} {$newItem->unit} {$newItem->name}";
                }
            }
        });

        $namesSummary = implode('، ', $updatedNames);
        $voiceReply = "{$namesSummary} کامیابی کے ساتھ اسٹاک انوینٹری میں شامل کر دی گئی ہے۔";

        return $this->jsonResponse([
            'success' => true,
            'message' => 'اسٹاک انٹری کامیابی کے ساتھ محفوظ ہو گئی ہے۔',
            'voice_reply' => $voiceReply,
        ]);
    }

    /**
     * Handle Customer Payment Received ("حمزہ نے 500 روپے جمع کروائے")
     */
    protected function handlePaymentReceived(Company $company, string $transcript): JsonResponse
    {
        $norm = $this->normalizeUrduNumbers($transcript);
        $amount = 0;
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:روپے|rupay|rs|rupees)?/iu', $norm, $m)) {
            $amount = (float)$m[1];
        }

        $customerName = 'Customer';
        if (preg_match('/^(.*?)(?:\s+نے|\s+ne|\s+ko|\s+کو|\s*[:\-\—]\s*)/iu', $transcript, $m)) {
            $customerName = trim($m[1]);
            $customerName = preg_replace('/^(?:یار\s+بات\s+سنو|یار\s+سنو|بات\s+سنو|سنو|بھائی|یار|آج|aj|aaj|bhai|yaar|check\s+karo|\s+)+/iu', '', $customerName);
            $customerName = trim($customerName);
        }

        $party = Party::where('company_id', $company->id)
            ->where(function ($q) use ($customerName) {
                $q->where('name', 'LIKE', "%{$customerName}%")
                  ->orWhere('name', 'LIKE', $customerName);
            })->first();

        $reply = "{$customerName} کی طرف سے " . number_format($amount) . " روپے وصولی درج کر لی گئی ہے۔";

        return $this->jsonResponse([
            'success' => true,
            'intent' => 'PAYMENT_RECEIVED',
            'transcript' => $transcript,
            'customer_name' => $customerName,
            'amount' => $amount,
            'party' => $party ? ['name' => $party->name, 'current_balance' => (float)$party->current_balance] : null,
            'voice_reply' => $reply,
        ]);
    }

    /**
     * Parse Spoken Urdu / Roman Urdu into Structured Sale Data
     */
    protected function parseUrduSaleSpeech(Company $company, string $text): array
    {
        $clean = trim($text);

        // 1. Extract Customer Name & Strip Customer Prefix
        $customerName = 'Walk-in Customer';
        $itemsText = $clean;

        if (preg_match('/^(.*?)(?:\s+مجھ\s+سے|\s*[:\-\—]\s*|\s+نے|\s+ne|\s+ko|\s+کو|\s+nay|\s+کے\s+کھاتے\s+میں)\s+/iu', $clean, $m)) {
            $customerName = trim($m[1]);
            // Remove common conversational fillers and greetings
            $customerName = preg_replace('/^(?:یا\s+میرا\s+دوست|میرا\s+دوست|میرے\s+دوست|میرے\s+بھائی|بھائی\s+جان|دوست|بھائی|یا|سنو|یار\s+بات\s+سنو|یار\s+سنو|بات\s+سنو|سنو|یار|آج|aj|aaj|bhai|yaar|check\s+karo|\s+)+/iu', '', $customerName);
            $customerName = trim($customerName);

            // Strip customer prefix from sentence so items can be cleanly parsed
            $itemsText = preg_replace('/^(.*?)(?:\s+مجھ\s+سے|\s*[:\-\—]\s*|\s+نے|\s+ne|\s+ko|\s+کو|\s+nay|\s+کے\s+کھاتے\s+میں)\s+/iu', '', $clean);
        } elseif (preg_match('/(?:گاہک|customer|party|party_name)\s+([a-zA-Z\p{Arabic}\s]+?)(?:\s+ne|\s+نے|$)/iu', $clean, $m)) {
            $customerName = trim($m[1]);
            $itemsText = preg_replace('/^(?:گاہک|customer|party|party_name)\s+([a-zA-Z\p{Arabic}\s]+?)(?:\s+ne|\s+نے)\s+/iu', '', $clean);
        }

        // 2. Identify and parse item segments with custom spoken rates
        $rawItems = $this->extractItemsAndQuantities($itemsText);

        $parsedItems = [];
        $grandTotal = 0;

        foreach ($rawItems as $raw) {
            $qty = $raw['quantity'];
            $unit = $raw['unit'];
            $itemName = $raw['name'];
            $spokenRate = (float) ($raw['rate'] ?? 0);

            // Match against database items
            $dbItem = Item::where('company_id', $company->id)
                ->where(function ($q) use ($itemName) {
                    $q->where('name', 'LIKE', "%{$itemName}%")
                      ->orWhere('name', 'LIKE', $itemName);
                })->first();

            // If shopkeeper explicitly spoke a rate, THAT SPOKEN RATE HAS HIGHEST PRIORITY!
            $price = 0;
            if ($spokenRate > 0) {
                $price = $spokenRate;
                if ($dbItem) {
                    $itemName = $dbItem->name;
                    $unit = $dbItem->unit ?: $unit;
                }
            } elseif ($dbItem) {
                $price = (float) ($dbItem->sale_price ?: $dbItem->price ?: 0);
                $itemName = $dbItem->name;
                $unit = $dbItem->unit ?: $unit;
            } else {
                // Heuristic standard Pakistani grocery defaults if DB has 0
                $price = $this->getDefaultGroceryPrice($itemName);
            }

            $amount = $qty * $price;
            $grandTotal += $amount;

            $parsedItems[] = [
                'name' => $itemName,
                'quantity' => $qty,
                'unit' => $unit,
                'unit_price' => $price,
                'amount' => $amount,
                'in_database' => (bool)$dbItem,
            ];
        }

        // If no items extracted, create a generic placeholder item
        if (empty($parsedItems)) {
            $parsedItems[] = [
                'name' => 'General Grocery Items',
                'quantity' => 1,
                'unit' => 'item',
                'unit_price' => 0,
                'amount' => 0,
                'in_database' => false,
            ];
        }

        // Voice summary text in Urdu
        $itemsSpeech = implode('، ', array_map(function ($it) {
            return "{$it['quantity']} {$it['unit']} {$it['name']}";
        }, $parsedItems));

        $voiceSummary = "{$customerName} کا بل: {$itemsSpeech}۔ کل رقم " . number_format($grandTotal) . " روپے۔ کیا بل کنفرم کروں؟";

        return [
            'customer_name' => $customerName,
            'items' => $parsedItems,
            'total_amount' => $grandTotal,
            'payment_type' => 'credit', // Default in rural karyana is khata/credit
            'voice_summary' => $voiceSummary,
        ];
    }

    /**
     * Regex and Keyword Parser for Quantities, Units, and Items (with Spoken Rate Support)
     */
    protected function extractItemsAndQuantities(string $text): array
    {
        $items = [];
        $norm = $this->normalizeUrduNumbers($text);

        // 1. Remove trailing total/summary statements e.g. "اور کل 500 روپے", "کل ملا کر 500"
        $norm = preg_replace('/\b(اور\s+)?(کل|ٹوٹل|سارا|سب\s+ملا\s+کر|ملا\s+کر)\s+(\d+(?:\.\d+)?)\s*(روپے|روپئے|rs)?.*$/iu', '', $norm);

        // 2. Split into item segments by conjunctions/punctuation
        $segments = preg_split('/\s+(?:اور|پھر|ساتھ|aur)\s+|[،,]/u', $norm);

        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (empty($seg)) continue;

            // Skip summary or filler segments
            if (preg_match('/^(کل|ٹوٹل|سب|یہ\s+چیزیں|یہ\s+سامان|سارا|بل)\b/iu', $seg)) {
                continue;
            }

            $qty = 1.0;
            $unit = 'kg';
            $rate = 0.0;
            $name = '';

            // Pattern 1: "[Item] [Rate] [روپے کلو/والی/کے حساب سے] [Qty] [Unit]"
            // E.g. "چینی 300 روپے کلو 5 کلو", "گھی 580 والی 2 کلو"
            if (preg_match('/^([a-zA-Z\p{Arabic}\s]+?)\s+(\d+(?:\.\d+)?)\s*(?:روپے\s*کلو|روپے\s*والا|روپے\s*والی|روپے|والا|والی|کے\s*حساب\s*سے|کا|کی|کے)?\s+(\d+(?:\.\d+)?)\s*(کلو|گرام|لیٹر|پاؤ|بوری|ڈبہ|پیکٹ|عدد|kg|kilo|ltr|liter|packet|box|bag|pcs)?$/iu', $seg, $m)) {
                $name = trim($m[1]);
                $rate = (float)$m[2];
                $qty = (float)$m[3];
                $unit = !empty($m[4]) ? trim($m[4]) : 'kg';
            }
            // Pattern 2: "[Qty] [Unit] [Item] [Rate] [روپے کلو/کے حساب سے/والی/کا/روپے]"
            // E.g. "5 کلو چینی 300 روپے کلو", "5 کلو چینی 300 کے حساب سے", "5 کلو چینی 300 والی", "2 کلو گھی 580 کا", "3 صابن 110 والے"
            elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(کلو|گرام|لیٹر|پاؤ|بوری|ڈبہ|پیکٹ|عدد|kg|kilo|ltr|liter|packet|box|bag|pcs)?\s+([a-zA-Z\p{Arabic}\s]+?)\s+(\d+(?:\.\d+)?)\s*(?:روپے\s*کلو|روپے\s*والا|روپے\s*والی|روپے\s*والے|والے|والی|والا|روپے\s*کے\s*حساب\s*سے|کے\s*حساب\s*سے|روپے|کا|کی|کے)?$/iu', $seg, $m)) {
                $qty = (float)$m[1];
                $unit = !empty($m[2]) ? trim($m[2]) : 'kg';
                $name = trim($m[3]);
                $rate = (float)$m[4];
            }
            // Pattern 3: Standard "[Qty] [Unit] [Item]" (no rate specified)
            // E.g. "5 کلو چینی", "2 کلو گھی", "5 کلو دال چنا یہ چیزیں"
            elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(کلو|گرام|لیٹر|پاؤ|بوری|ڈبہ|پیکٹ|عدد|kg|kilo|ltr|liter|packet|box|bag|pcs)?\s+([a-zA-Z\p{Arabic}\s]+)$/iu', $seg, $m)) {
                $qty = (float)$m[1];
                $unit = !empty($m[2]) ? trim($m[2]) : 'kg';
                $name = trim($m[3]);
                $rate = 0.0;
            }

            // Clean up item name
            $name = preg_replace('/\b(یہ\s+چیزیں|یہ\s+سامان|چیزیں|سامان|وغیرہ|لیں|لی|لیا|لئے|لیے|لیا\s+ہے|خریدا|آج|aj|liya|bhi|hai|hay|ہے|ہیں|تھا|لے\s*کر|لے\s*گئے|گئے\s*ہیں|کشمیر)\b/iu', '', $name);
            $name = preg_replace('/^[\s,،.؟?\-]+|[\s,،.؟?\-]+$/u', '', $name);
            $name = trim($name);

            if (!empty($name) && mb_strlen($name) > 1 && !in_array($name, ['کل', 'ٹوٹل', 'بل', 'روپے', 'پیسے', 'سامان'])) {
                $items[] = [
                    'name' => $name,
                    'quantity' => $qty,
                    'unit' => $this->standardizeUnit($unit),
                    'rate' => $rate,
                ];
            }
        }

        return $items;
    }

    /**
     * Normalize Urdu & Roman Urdu spoken numbers to Arabic numerals
     */
    protected function normalizeUrduNumbers(string $text): string
    {
        $replacements = [
            // Urdu words
            'ایک' => '1', 'دو' => '2', 'تین' => '3', 'چار' => '4', 'پانچ' => '5',
            'چھ' => '6', 'سات' => '7', 'آٹھ' => '8', 'نو' => '9', 'دس' => '10',
            'گیارہ' => '11', 'بارہ' => '12', 'پندرہ' => '15', 'بیس' => '20',
            'پچیس' => '25', 'تیس' => '30', 'چالیس' => '40', 'پچاس' => '50',
            'سو' => '100', 'ہزار' => '1000', 'ڈھائی' => '2.5', 'ڈیڑھ' => '1.5', 'سوا' => '1.25', 'آدھا' => '0.5',

            // Roman Urdu words
            'aik' => '1', 'ek' => '1', 'teen' => '3', 'chaar' => '4', 'char' => '4',
            'paanch' => '5', 'panch' => '5', 'chhe' => '6', 'che' => '6', 'saat' => '7',
            'aath' => '8', 'ath' => '8', 'nau' => '9', 'das' => '10', 'gyara' => '11',
            'bara' => '12', 'pandrah' => '15', 'bees' => '20', 'pachees' => '25',
            'tees' => '30', 'chalees' => '40', 'pachaas' => '50', 'dhai' => '2.5',
            'dedh' => '1.5', 'sawa' => '1.25', 'aadha' => '0.5', 'adha' => '0.5',
        ];

        foreach ($replacements as $word => $digit) {
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', $digit, $text);
        }

        return $text;
    }

    /**
     * Standardize unit string
     */
    protected function standardizeUnit(string $u): string
    {
        $u = strtolower(trim($u));
        if (in_array($u, ['کلو', 'kg', 'kilo', 'kilogram'])) return 'kg';
        if (in_array($u, ['گرام', 'gram', 'gm'])) return 'g';
        if (in_array($u, ['لیٹر', 'liter', 'litre', 'ltr'])) return 'ltr';
        if (in_array($u, ['پاؤ', 'pao'])) return 'pao';
        if (in_array($u, ['درجن', 'darjan', 'dozen'])) return 'dozen';
        if (in_array($u, ['ڈبہ', 'dabba', 'box'])) return 'box';
        if (in_array($u, ['بوری', 'bori', 'bag'])) return 'bag';
        if (in_array($u, ['پیکٹ', 'packet', 'pkt'])) return 'packet';
        return 'pcs';
    }

    /**
     * Standard retail prices for common Pakistani items when not in inventory
     */
    protected function getDefaultGroceryPrice(string $item): float
    {
        $item = strtolower($item);
        if (str_contains($item, 'چینی') || str_contains($item, 'cheeni') || str_contains($item, 'sugar')) return 145.00;
        if (str_contains($item, 'گھی') || str_contains($item, 'ghee')) return 480.00;
        if (str_contains($item, 'تیل') || str_contains($item, 'oil')) return 520.00;
        if (str_contains($item, 'آٹا') || str_contains($item, 'atta') || str_contains($item, 'flour')) return 120.00;
        if (str_contains($item, 'پتی') || str_contains($item, 'patti') || str_contains($item, 'chai') || str_contains($item, 'tea')) return 350.00;
        if (str_contains($item, 'سرف') || str_contains($item, 'surf') || str_contains($item, 'detergent')) return 220.00;
        if (str_contains($item, 'صابن') || str_contains($item, 'sabun') || str_contains($item, 'soap')) return 110.00;
        if (str_contains($item, 'دودھ') || str_contains($item, 'doodh') || str_contains($item, 'milk')) return 190.00;
        if (str_contains($item, 'چاول') || str_contains($item, 'chawal') || str_contains($item, 'rice')) return 260.00;
        if (str_contains($item, 'دال') || str_contains($item, 'daal')) return 280.00;

        return 100.00; // General safe fallback
    }

    /**
     * Extract Customer Name from Query ("حمزہ کا کھاتہ دکھاؤ")
     */
    protected function extractCustomerNameFromQuery(string $text): string
    {
        // Matches: "[Name] کا کھاتہ", "[Name] ka khata", "[Name] ki detail"
        if (preg_match('/(?:مجھے\s+)?([a-zA-Z\p{Arabic}\s]+?)(?:\s+کا\s+کھاتہ|\s+ka\s+khata|\s+ka\s+hisab|\s+کا\s+حساب|\s+کا\s+بیلنس)/iu', $text, $m)) {
            return trim($m[1]);
        }

        // Remove trigger words
        $clean = preg_replace('/(کھاتہ|حساب|دکھاؤ|بتائو|check|khata|hisab|dikhao|batao|bataiye|mujhe|mujhey)\s*/iu', '', $text);
        return trim($clean);
    }

    /**
     * Extract Item Name from Query ("چینی کا سٹاک کتنا ہے")
     */
    protected function extractItemNameFromQuery(string $text): string
    {
        if (preg_match('/([a-zA-Z\p{Arabic}\s]+?)(?:\s+کا\s+سٹاک|\s+ka\s+stock|\s+kitna\s+hai|\s+کتنا\s+ہے)/iu', $text, $m)) {
            return trim($m[1]);
        }
        return trim(preg_replace('/(سٹاک|اسٹاک|stock|kitna|hai|check)\s*/iu', '', $text));
    }

    /**
     * Detect User Intent from Speech
     */
    protected function detectIntent(string $text): string
    {
        $lower = mb_strtolower($text);

        // Conversational / Greeting intent
        if (str_contains($lower, 'ہیلو') || str_contains($lower, 'hello') ||
            str_contains($lower, 'ہاؤ آر یو') || str_contains($lower, 'how are you') ||
            str_contains($lower, 'کیسے ہو') || str_contains($lower, 'kaise ho') ||
            str_contains($lower, 'کون ہو') || str_contains($lower, 'اردو میں کیوں نہیں') ||
            str_contains($lower, 'شو ہو رہا') || str_contains($lower, 'مدد') ||
            str_contains($lower, 'السلام علیکم') || str_contains($lower, 'سلام') ||
            str_contains($lower, 'کیا حال ہے') || str_contains($lower, 'کیا حال')) {
            return 'CONVERSATION';
        }

        // Today's Udhar Register query intent
        if (str_contains($lower, 'آج کا ادھار') || str_contains($lower, 'آج کا کھاتہ') ||
            str_contains($lower, 'کس کس نے ادھار لیا') || str_contains($lower, 'ادھار رجسٹر') ||
            str_contains($lower, 'آج کے ادھار') || str_contains($lower, 'کتنا ادھار ہوا') ||
            str_contains($lower, 'آج کی ادھار') || str_contains($lower, 'ادھار لسٹ')) {
            return 'TODAY_UDHAR_QUERY';
        }

        // Stock inward intent (مال کی آمد / اسٹاک اندراج)
        if (str_contains($lower, 'اسٹاک آیا') || str_contains($lower, 'مال آیا') ||
            str_contains($lower, 'اسٹاک میں جمع') || str_contains($lower, 'نیا مال') ||
            str_contains($lower, 'اسٹاک انٹری') || str_contains($lower, 'سٹاک آیا') ||
            str_contains($lower, 'آمد ہوئی') || str_contains($lower, 'بوری آئی') ||
            str_contains($lower, 'پیٹی آئی') || str_contains($lower, 'کارٹن آیا') ||
            str_contains($lower, 'آمد') || str_contains($lower, 'اسٹاک داخل') ||
            str_contains($lower, 'stock aaya') || str_contains($lower, 'maal aaya') ||
            str_contains($lower, 'stock in') || str_contains($lower, 'stock inward')) {
            return 'STOCK_INWARD';
        }

        // Khata statement intent
        if (str_contains($lower, 'کھاتہ') || str_contains($lower, 'khata') ||
            str_contains($lower, 'حساب') || str_contains($lower, 'hisab') ||
            str_contains($lower, 'بقیہ') || str_contains($lower, 'baqaya') ||
            str_contains($lower, 'کتنے پیسے') || str_contains($lower, 'kitne paise')) {
            return 'KHATA_QUERY';
        }

        // Stock inquiry intent
        if (str_contains($lower, 'سٹاک') || str_contains($lower, 'اسٹاک') ||
            str_contains($lower, 'stock') || str_contains($lower, 'kitna para') ||
            str_contains($lower, 'کتنا پڑا')) {
            return 'STOCK_QUERY';
        }

        // Payment received intent
        if (str_contains($lower, 'جمع کروائے') || str_contains($lower, 'jama') ||
            str_contains($lower, 'دیے ہیں') || str_contains($lower, 'diye')) {
            return 'PAYMENT_RECEIVED';
        }

        return 'SALE_ENTRY';
    }

    /**
     * Transcribe Audio File using Gemini or Whisper API if configured
     */
    protected function transcribeAudio($audioFile): string
    {
        $geminiKey = env('GEMINI_API_KEY');
        if (!empty($geminiKey)) {
            try {
                $base64 = base64_encode(file_get_contents($audioFile->getRealPath()));
                $mimeType = $audioFile->getMimeType() ?: 'audio/webm';

                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Listen carefully to this audio in Pakistani Urdu / Roman Urdu / Punjabi and transcribe exact words without any commentary. Transcribe customer name and grocery items accurately.'],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $base64
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                }
            } catch (\Exception $e) {
                Log::warning('Gemini audio transcribe failed: ' . $e->getMessage());
            }
        }

        return '';
    }

    /**
     * Build Branded WhatsApp Receipt Text in Urdu & English
     */
    protected function buildWhatsAppMessage(Company $company, Party $party, Sale $sale, array $items): string
    {
        $divider = "━━━━━━━━━━━━━━━━━━━━";
        $cName = $company->name ?: 'Hexasolv Store';
        $msg = "✨ *{$cName} — ڈیجیٹل بل رسید*\n";
        $msg .= "{$divider}\n";
        $msg .= "🧾 *بل نمبر:* #{$sale->bill_number}\n";
        $msg .= "📅 *تاریخ:* " . now()->format('d M, Y h:i A') . "\n";
        $msg .= "👤 *گاہک:* {$party->name}\n";
        $msg .= "{$divider}\n";
        $msg .= "🛒 *سامان کی تفصیل:*\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $amt = number_format((float)$it['amount'], 2);
            $msg .= "{$num}. *{$it['name']}* ({$it['quantity']} {$it['unit']} x Rs {$it['unit_price']}) = Rs {$amt}\n";
        }

        $msg .= "{$divider}\n";
        $msg .= "💰 *کل بل رقم:* Rs " . number_format($sale->grand_total, 2) . "\n";
        if ($sale->received_amount > 0) {
            $msg .= "💵 *وصول رقم:* Rs " . number_format($sale->received_amount, 2) . "\n";
        }
        $msg .= "🔴 *بقیہ ادھار:* Rs " . number_format($sale->balance, 2) . "\n";
        $msg .= "📊 *گاہک کا کل کھاتہ بیلنس:* Rs " . number_format($party->current_balance, 2) . "\n";
        $msg .= "{$divider}\n";
        $msg .= "🙏 *خریداری کا شکریہ!*\n";
        $msg .= "Powered by Hexasolv ERP\n";

        return $msg;
    }

    /**
     * Convert Hindi Devanagari script to pure Urdu Nastaliq
     */
    protected function convertDevanagariToUrdu(string $text): string
    {
        if (empty($text) || !preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return $text;
        }

        $words = [
            'हैलो' => 'ہیلو', 'हेलो' => 'ہیلو', 'हाउ' => 'ہاؤ', 'आर' => 'آر', 
            'यूआइ' => 'یو آئی', 'यूआई' => 'یو آئی', 'यू' => 'یو', 'आई' => 'آئی', 'एम' => 'ایم',
            'अरसलन' => 'ارسلان', 'अरसलान' => 'ارسلان', 'अर्सलान' => 'ارسلان',
            'यार' => 'یار', 'मुझे' => 'مجھے', 'उर्दू' => 'اردو', 'में' => 'میں',
            'क्यों' => 'کیوں', 'नहीं' => 'نہیں', 'शो' => 'شو', 'हो' => 'ہو',
            'रहा' => 'رہا', 'सब' => 'سب', 'कुछ' => 'کچھ', 'और' => 'اور',
            'حمزہ' => 'حمزہ', 'हमज़ा' => 'حمزہ', 'हमजा' => 'حمزہ', 'علی' => 'علی', 'अली' => 'علی',
            'उस्मान' => 'عثمان', 'अहमद' => 'احمد', 'बिलाल' => 'بلال', 'भाई' => 'بھائی',
            'ने' => 'نے', 'आज' => 'آج', 'किलो' => 'کلو', 'चीनी' => 'چینی',
            'घी' => 'گھی', 'तेल' => 'تیل', 'आटा' => 'آٹا', 'चावल' => 'چاول', 'दाल' => 'دال',
            'दूध' => 'دودھ', 'पत्ती' => 'پتی', 'चाय' => 'چائے', 'सामान' => 'سامان', 'दुकान' => 'دکان',
            'लिया' => 'لیا', 'खाता' => 'کھاتہ', 'दिखाओ' => 'دکھاؤ', 'बताओ' => 'بتاؤ',
            'साबुन' => 'صابن', 'पैकेट' => 'پیکٹ', 'सर्फ' => 'سرف', 'बोरी' => 'بوری', 'ڈبہ' => 'ڈبہ',
            'रुपये' => 'روپے', 'रुपए' => 'روپے', 'पैसे' => 'پیسے',
            'दिए' => 'دیے', 'दिया' => 'دیا', 'जमा' => 'جمع', 'बकाया' => 'بقایا', 'बाकी' => 'باقی',
            'उधार' => 'ادھار', 'نقد' => 'نقد', 'नकद' => 'نقد', 'स्टॉक' => 'سٹاک',
            'कितना' => 'کتنا', 'कितने' => 'کتنے', 'कितनी' => 'کتنی', 'करो' => 'کرو', 'करें' => 'کریں',
            'एक' => 'ایک', 'दो' => 'دو', 'तीन' => 'تین', 'चार' => 'چار', 'पांच' => 'پانچ', 'पाँच' => 'پانچ',
            'छह' => 'چھ', 'सात' => 'سات', 'आठ' => 'آٹھ', 'नौ' => 'نو', 'दस' => 'دس'
        ];

        foreach ($words as $hi => $ur) {
            $text = str_replace($hi, $ur, $text);
        }

        $charMap = [
            'अ' => 'ا', 'आ' => 'آ', 'इ' => 'اِ', 'ई' => 'ای', 'उ' => 'اُ', 'ऊ' => 'او',
            'ए' => 'اے', 'ऐ' => 'اے', 'ओ' => 'او', 'औ' => 'او',
            'क' => 'ک', 'ख' => 'کھ', 'ग' => 'گ', 'घ' => 'گھ',
            'च' => 'چ', 'छ' => 'چھ', 'ज' => 'ج', 'झ' => 'جھ',
            'ट' => 'ٹ', 'ठ' => 'ٹھ', 'ڈ' => 'ڈ', 'ढ' => 'ڈھ',
            'त' => 'ت', 'थ' => 'تھ', 'द' => 'د', 'ध' => 'دھ', 'न' => 'ن',
            'प' => 'پ', 'फ' => 'ف', 'ब' => 'ب', 'भ' => 'بھ', 'म' => 'م',
            'य' => 'ی', 'र' => 'ر', 'ल' => 'ل', 'व' => 'و', 'श' => 'ش', 'ष' => 'ش', 'स' => 'س', 'ह' => 'ہ',
            'ा' => 'ا', 'ि' => 'ِ', 'ी' => 'ی', 'ु' => 'ُ', 'ू' => 'و', 'ے' => 'ے', 'ै' => 'ے', 'ो' => 'و', 'ौ' => 'و',
            '्' => '', 'ं' => 'ں', 'ँ' => 'ں', 'ः' => 'ہ', '़' => '', 'ड़' => 'ڑ', 'ढ़' => 'ڑھ'
        ];

        return strtr($text, $charMap);
    }
}
