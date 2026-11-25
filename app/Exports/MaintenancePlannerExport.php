<?php

namespace App\Exports;

use App\Services\Maintenance\MaintenancePlannerData;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Workshop Maintenance Planner: "Planner" sheet = one row per unit, one
 * column per tracked item (THERMOSTATS (15000 HRS)...) showing hours/km/days
 * left, N/T where the unit doesn't track the item; "Schedule Detail" sheet =
 * one row per schedule with last done / next due / current reading.
 */
class MaintenancePlannerExport implements WithMultipleSheets
{
    protected MaintenancePlannerData $data;

    public function __construct(array $filters = [])
    {
        $this->data = new MaintenancePlannerData($filters);
    }

    public function sheets(): array
    {
        return [
            new MaintenancePlannerMatrixSheet($this->data),
            new MaintenancePlannerDetailSheet($this->data),
        ];
    }
}
