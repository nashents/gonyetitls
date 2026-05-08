<?php

namespace App\Exports;

use App\Models\Horse;
use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ShiftActivitiesExport implements WithMultipleSheets
{
    protected Carbon $date;
    protected ?array $horseIds;

    public function __construct(Carbon $date, ?array $horseIds = null)
    {
        $this->date     = $date;
        $this->horseIds = $horseIds;
    }

    public function sheets(): array
    {
        $query = Horse::with([
            'shifts' => function ($q) {
                $q->with([
                    'trips.loading_point',
                    'trips.offloading_point',
                    'user',
                    'team',
                ])
                    ->whereBetween('shift_start_time', [
                        $this->date->copy()->startOfDay(),
                        $this->date->copy()->endOfDay(),
                    ])
                    ->orderBy('shift_start_time');
            },
        ])->whereHas('shifts', function ($q) {
            $q->whereBetween('shift_start_time', [
                $this->date->copy()->startOfDay(),
                $this->date->copy()->endOfDay(),
            ]);
        });

        if ($this->horseIds) {
            $query->whereIn('id', $this->horseIds);
        }

        $horses = $query->orderBy('fleet_number')->get();

        // Group horses in pairs — two horses side by side per sheet page
        $pairs  = $horses->chunk(2);
        $sheets = [];

        foreach ($pairs as $pair) {
            $sheets[] = new ShiftActivitiesSheet($pair, $this->date);
        }

        if (empty($sheets)) {
            $sheets[] = new ShiftActivitiesSheet(collect(), $this->date);
        }

        return $sheets;
    }
}
