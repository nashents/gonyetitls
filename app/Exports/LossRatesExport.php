<?php

namespace App\Exports;

use App\Models\Cargo;
use App\Models\Company;
use App\Models\Transporter;
use App\Services\LossRateService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Loss Rates workbook:
 *  - Allowable Loss: customer x cargo matrix of the % a trip would use
 *    (fraction form, e.g. 0.003), "NOT SET" where nothing applies.
 *  - Allowance Sources: same matrix, where each value comes from.
 *  - Rates: per cargo Own Fleet vs Subcontractor rate + source.
 *  - Defaults: Business Settings values.
 */
class LossRatesExport implements WithMultipleSheets
{
    public function __construct(private ?Company $company)
    {
    }

    public function sheets(): array
    {
        $service = app(LossRateService::class);
        $cargos = Cargo::orderBy('name')->orderBy('id')->get();
        $customers = $service->customersQuery($this->company)->get();
        $service->agreements();

        $headings = array_merge(['Customer', 'All cargos (customer %)'], $cargos->pluck('name')->all());
        $values = [];
        $sources = [];

        $cargoDefaults = ['Cargo default', null];
        foreach ($cargos as $cargo) {
            $cargoDefaults[] = $this->fraction($cargo->allowable_loss_percentage);
        }
        $values[] = $cargoDefaults;

        foreach ($customers as $customer) {
            $v = [$customer->name, $this->fraction($customer->allowable_loss_percentage)];
            $s = [$customer->name, is_numeric($customer->allowable_loss_percentage) ? 'Customer (all cargos)' : ''];
            foreach ($cargos as $cargo) {
                $a = $service->allowance($customer, $cargo, $this->company);
                $v[] = is_null($a['value']) ? 'NOT SET' : $this->fraction($a['value']);
                $s[] = $a['source'] ?? 'NOT SET';
            }
            $values[] = $v;
            $sources[] = $s;
        }

        $own = new Transporter(['default' => true]);
        $sub = new Transporter(['default' => false]);
        $rates = $cargos->map(function ($cargo) use ($service, $own, $sub) {
            $o = $service->rate($cargo, $own, $this->company);
            $s = $service->rate($cargo, $sub, $this->company);

            return [$cargo->name, $cargo->type, $o['value'] ?? 'NOT SET', $o['source'], $s['value'] ?? 'NOT SET', $s['source']];
        })->all();

        $ownName = $service->ownFleetName();
        $c = $this->company;

        return [
            $this->sheet('Allowable Loss (fraction)', $headings, $values),
            $this->sheet('Allowance Sources', $headings, $sources),
            $this->sheet('Rates', ['Cargo', 'Type', "Own Fleet ({$ownName}) Rate", 'Own Fleet Source', 'Subcontractor Rate', 'Subcontractor Source'], $rates),
            $this->sheet('Defaults', ['Setting', 'Value'], [
                ['Default Allowable Loss (fraction)', $this->fraction($c?->allowable_loss_percentage)],
                ['Default Allowable Loss (%)', $c?->allowable_loss_percentage],
                ["Default Rate - Own Fleet ({$ownName})", $c?->own_fleet_chargeable_loss_rate],
                ['Default Rate - Subcontractors', $c?->subcontractor_chargeable_loss_rate],
            ]),
        ];
    }

    /** Stored percent -> fraction (0.3 -> 0.003). */
    private function fraction($pct)
    {
        return is_numeric($pct) ? round($pct / 100, 8) : null;
    }

    private function sheet(string $title, array $headings, array $rows)
    {
        return new class($title, $headings, $rows) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize {
            public function __construct(private string $title, private array $headings, private array $rows)
            {
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function title(): string
            {
                return $this->title;
            }
        };
    }
}
