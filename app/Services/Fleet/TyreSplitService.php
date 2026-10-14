<?php

namespace App\Services\Fleet;

use App\Models\DispatchItem;
use App\Models\Tyre;
use Illuminate\Support\Facades\DB;

/**
 * One tyre record = one physical tyre. Splits a record holding several tyres (qty or balance > 1)
 * into qty-1 records, sharing its cost equally so the total value is unchanged (no journal).
 *
 * Each unit keeps its place: units on an approved dispatch become out-of-store records on that
 * dispatch (the dispatch line is split one per tyre), units on a pending dispatch and units still
 * in stores stay in stock, and anything unaccounted for is left out of stock.
 */
class TyreSplitService
{
    // Money fields that price the whole record; split per unit.
    const PER_RECORD = ['subtotal', 'subtotal_incl', 'total', 'exchange_amount', 'tax_amount'];

    // Dispatch line money fields; split per unit.
    const PER_LINE = ['amount', 'exchange_amount', 'subtotal', 'subtotal_incl', 'tax_amount', 'weight'];

    public static function needsSplit(Tyre $tyre): bool
    {
        return (float) $tyre->qty > 1 || (float) $tyre->balance > 1;
    }

    /**
     * @return int number of records created
     */
    public function split(Tyre $tyre): int
    {
        if (!static::needsSplit($tyre)) {
            return 0;
        }

        return DB::transaction(function () use ($tyre) {
            $originalQty = (int) round(max((float) $tyre->qty, (float) $tyre->balance, 1));
            $balance = (int) round(max(0, (float) $tyre->balance));

            $items = DispatchItem::with('dispatch')
                ->where('tyre_id', $tyre->id)
                ->get()
                ->filter(fn ($item) => $item->dispatch && in_array($item->dispatch->authorization, ['approved', 'pending']));

            // Slots: one per physical tyre, in this order so the original record keeps a dispatched
            // (possibly fitted) unit first.
            $slots = [];
            foreach ($items->where('dispatch.authorization', 'approved') as $item) {
                for ($i = 0; $i < max(1, (int) round((float) $item->qty)); $i++) {
                    $slots[] = ['balance' => 0, 'item' => $item];
                }
            }
            $pendingQty = 0;
            foreach ($items->where('dispatch.authorization', 'pending') as $item) {
                $units = max(1, (int) round((float) $item->qty));
                $pendingQty += $units;
                for ($i = 0; $i < $units; $i++) {
                    $slots[] = ['balance' => 1, 'item' => $item];
                }
            }
            for ($i = 0; $i < max(0, $balance - $pendingQty); $i++) {
                $slots[] = ['balance' => 1, 'item' => null];
            }
            while (count($slots) < $originalQty) {
                $slots[] = ['balance' => 0, 'item' => null];
            }

            $totals = $tyre->only(self::PER_RECORD);
            $prefix = preg_replace('/\d+$/', '', (string) $tyre->tyre_number) ?: 'TN';
            $created = 0;
            $rows = [];

            foreach ($slots as $index => $slot) {
                $row = $index === 0 ? $tyre : $tyre->replicate(['tyre_number']);
                foreach (self::PER_RECORD as $field) {
                    $row->{$field} = $this->share($totals[$field], count($slots), $index);
                }
                $row->qty = 1;
                $row->weight = 1;
                $row->balance = $slot['balance'];
                $row->status = $slot['balance'] > 0 ? 1 : 0;
                $row->save();

                if ($index > 0) {
                    $row->tyre_number = $prefix.str_pad($row->id, 5, '0', STR_PAD_LEFT);
                    $row->save();
                    $created++;
                }
                $rows[] = $row;
            }

            $this->splitDispatchLines($slots, $rows);

            return $created;
        });
    }

    // Each dispatch line covering q tyres becomes q lines of one tyre each.
    private function splitDispatchLines(array $slots, array $rows): void
    {
        $byItem = [];
        foreach ($slots as $index => $slot) {
            if ($slot['item']) {
                $byItem[$slot['item']->id][] = $rows[$index];
            }
        }

        foreach ($byItem as $itemId => $tyres) {
            $item = DispatchItem::find($itemId);
            $count = count($tyres);
            $totals = $item->only(self::PER_LINE);

            foreach ($tyres as $index => $tyre) {
                $line = $index === 0 ? $item : $item->replicate();
                foreach (self::PER_LINE as $field) {
                    $line->{$field} = $this->share($totals[$field], $count, $index);
                }
                $line->qty = 1;
                $line->tyre_id = $tyre->id;
                $line->save();
            }
        }
    }

    // Equal share of a value over $count units; the first unit takes the rounding remainder.
    private function share($value, int $count, int $index)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $each = round((float) $value / $count, 2);

        return $index === 0 ? round((float) $value - $each * ($count - 1), 2) : $each;
    }
}
