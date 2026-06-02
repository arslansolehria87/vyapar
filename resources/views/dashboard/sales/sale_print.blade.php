<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Sale - {{ $sale->bill_number ?? $sale->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .print-container {
            background-color: white;
            padding: 40px;
            margin: 0 auto;
            max-width: 900px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .transaction-header {
            border-bottom: 3px solid #ff4d4d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .transaction-header h2 {
            color: #333;
            margin: 0;
            font-weight: 700;
        }

        .invoice-number {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .transaction-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .detail-section {
            padding: 20px;
            background-color: #fafafa;
            border-radius: 6px;
        }

        .detail-section h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 8px;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-cancelled {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .amount-summary {
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .amount-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 700;
            font-size: 18px;
        }

        .print-actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .print-actions button {
            margin: 0 10px;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .print-container {
                box-shadow: none;
                padding: 0;
            }

            .print-actions {
                display: none;
            }
        }

        .party-info {
            font-size: 14px;
            line-height: 1.6;
        }

        .party-info strong {
            color: #333;
        }

        .date-info {
            background-color: #f0f8ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .date-info .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .date-info .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="transaction-header">
            <h2>Sale Transaction</h2>
            <div class="invoice-number">Invoice #{{ $sale->bill_number ?? $sale->id }}</div>
        </div>

        <!-- Date and Party Info -->
        <div class="date-info">
            <div class="info-label">Invoice Date</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($sale->invoice_date ?? $sale->created_at)->format('d/m/Y') }}</div>
        </div>

        <!-- Main Details -->
        <div class="transaction-details">
            <!-- Party Information -->
            <div class="detail-section">
                <h5>Party Information</h5>
                <div class="party-info">
                    <strong>{{ $sale->party?->name ?? 'No Party Selected' }}</strong>
                </div>
            </div>

            <!-- Status -->
            <div class="detail-section">
                <h5>Transaction Status</h5>
                @php
                    $status = strtolower($sale->status ?? 'unpaid');
                    $statusClass = 'status-' . $status;
                @endphp
                <div>
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                </div>
            </div>
        </div>

        <!-- Amount Details -->
        <div class="transaction-details">
            <div class="detail-section">
                <h5>Amount Details</h5>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">Rs {{ number_format(($sale->grand_total ?? $sale->total_amount ?? 0), 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Received Amount:</span>
                    <span class="detail-value">Rs {{ number_format($sale->received_amount ?? 0, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Balance:</span>
                    <span class="detail-value">Rs {{ number_format($sale->balance ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="detail-section">
                <h5>Due Date & Payment</h5>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">
                        @if($sale->due_date)
                            {{ \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">
                        {{ $sale->payments->pluck('payment_type')->filter()->unique()->join(', ') ?: '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Summary Box -->
        <div class="amount-summary">
            <div class="amount-row">
                <span>Total Amount</span>
                <span>Rs {{ number_format(($sale->grand_total ?? $sale->total_amount ?? 0), 2) }}</span>
            </div>
            <div class="amount-row">
                <span>Received Amount</span>
                <span>Rs {{ number_format($sale->received_amount ?? 0, 2) }}</span>
            </div>
            <div class="amount-row">
                <span>Outstanding Balance</span>
                <span>Rs {{ number_format($sale->balance ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Print Actions -->
        <div class="print-actions">
            <button class="btn btn-danger rounded-pill px-4" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print This Transaction
            </button>
            <button class="btn btn-outline-secondary rounded-pill px-4" onclick="window.history.back()">
                <i class="fa-solid fa-arrow-left"></i> Go Back
            </button>
        </div>
    </div>

    <script>
        // Auto-print on load (optional - uncomment if you want auto-print)
        // window.addEventListener('load', function() {
        //     window.print();
        // });
    </script>
</body>
</html>
