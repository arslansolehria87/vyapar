<?php

namespace App\Services\Reports;

use App\Repositories\Reports\ItemReportByPartyRepository;
use Illuminate\Support\Collection;

class ItemReportByPartyService
{
    public function __construct(
        private readonly ItemReportByPartyRepository $repository
    ) {
    }

    public function report(array $filters): array
    {
        $saleRows = $this->repository->saleSummary($filters);
        $purchaseRows = $this->repository->purchaseSummary($filters);

        $rows = $this->mergeRows($saleRows, $purchaseRows);

        return [
            'rows' => $rows->values(),
            'totals' => [
                'sale_quantity' => $this->formatNumber($rows->sum('sale_quantity')),
                'sale_amount' => $this->formatNumber($rows->sum('sale_amount')),
                'purchase_quantity' => $this->formatNumber($rows->sum('purchase_quantity')),
                'purchase_amount' => $this->formatNumber($rows->sum('purchase_amount')),
            ],
        ];
    }

    private function mergeRows(Collection $saleRows, Collection $purchaseRows): Collection
    {
        $rows = collect();

        foreach ($saleRows as $row) {
            $key = $this->rowKey($row->item_id, $row->item_name);
            $rows[$key] = [
                'item_name' => $row->item_name,
                'sale_quantity' => $this->formatNumber($row->sale_quantity),
                'sale_amount' => $this->formatNumber($row->sale_amount),
                'purchase_quantity' => 0.0,
                'purchase_amount' => 0.0,
            ];
        }

        foreach ($purchaseRows as $row) {
            $key = $this->rowKey($row->item_id, $row->item_name);
            $existing = $rows->get($key, [
                'item_name' => $row->item_name,
                'sale_quantity' => 0.0,
                'sale_amount' => 0.0,
                'purchase_quantity' => 0.0,
                'purchase_amount' => 0.0,
            ]);

            $existing['purchase_quantity'] = $this->formatNumber($row->purchase_quantity);
            $existing['purchase_amount'] = $this->formatNumber($row->purchase_amount);
            $rows[$key] = $existing;
        }

        return $rows->sortBy('item_name', SORT_NATURAL | SORT_FLAG_CASE);
    }

    private function rowKey(int|string|null $itemId, string $itemName): string
    {
        if ((int) $itemId > 0) {
            return 'item:' . (int) $itemId;
        }

        return 'name:' . mb_strtolower(trim($itemName));
    }

    private function formatNumber(mixed $value): float
    {
        return round((float) ($value ?? 0), 2);
    }
}
