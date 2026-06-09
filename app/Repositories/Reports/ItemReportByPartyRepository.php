<?php

namespace App\Repositories\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ItemReportByPartyRepository
{
    public function saleSummary(array $filters): Collection
    {
        if (! Schema::hasTable('sales') || ! Schema::hasTable('sale_items')) {
            return collect();
        }

        return DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('items as i', 'i.id', '=', 'si.item_id')
            ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
            ->whereBetween('s.invoice_date', [$filters['from'], $filters['to']])
            ->whereIn('s.type', ['invoice', 'pos'])
            ->when($filters['party_id'] !== null, function ($query) use ($filters) {
                $query->where('s.party_id', $filters['party_id']);
            })
            ->select(
                DB::raw("COALESCE(si.item_id, 0) as item_id"),
                DB::raw("COALESCE(i.name, si.item_name, 'Item') as item_name"),
                DB::raw('SUM(COALESCE(si.quantity, 0)) as sale_quantity'),
                DB::raw('SUM(COALESCE(si.amount, COALESCE(si.quantity, 0) * COALESCE(si.unit_price, 0))) as sale_amount')
            )
            ->groupBy(DB::raw('COALESCE(si.item_id, 0)'), DB::raw("COALESCE(i.name, si.item_name, 'Item')"))
            ->orderBy('item_name')
            ->get();
    }

    public function purchaseSummary(array $filters): Collection
    {
        if (! Schema::hasTable('purchases') || ! Schema::hasTable('purchase_items')) {
            return collect();
        }

        return DB::table('purchase_items as pi')
            ->join('purchases as pu', 'pu.id', '=', 'pi.purchase_id')
            ->leftJoin('items as i', 'i.id', '=', 'pi.item_id')
            ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
            ->whereBetween('pu.bill_date', [$filters['from'], $filters['to']])
            ->where('pu.type', 'purchase_bill')
            ->when($filters['party_id'] !== null, function ($query) use ($filters) {
                $query->where('pu.party_id', $filters['party_id']);
            })
            ->select(
                DB::raw("COALESCE(pi.item_id, 0) as item_id"),
                DB::raw("COALESCE(i.name, pi.item_name, 'Item') as item_name"),
                DB::raw('SUM(COALESCE(pi.quantity, 0)) as purchase_quantity'),
                DB::raw('SUM(COALESCE(pi.amount, COALESCE(pi.quantity, 0) * COALESCE(pi.unit_price, 0))) as purchase_amount')
            )
            ->groupBy(DB::raw('COALESCE(pi.item_id, 0)'), DB::raw("COALESCE(i.name, pi.item_name, 'Item')"))
            ->orderBy('item_name')
            ->get();
    }
}
