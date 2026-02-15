<?php

namespace App\Exports;

use App\Models\Tyre;
use App\Models\ChecklistResult;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class TyresExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell
{
    use Exportable;

    public ?string $search;

    public function __construct($search = null)
    {
        $this->search = $search ? trim((string)$search) : null;
    }

    public function query()
    {
        $search = trim((string) $this->search);

        return Tyre::query()
            ->with([
                'product.brand',
                'currency',
                'store',
                'activeAssignment.horse',
                'activeAssignment.vehicle',
                'activeAssignment.trailer',
                'latestChecklistResult',
            ])
            ->where('disposed', 0)
            ->where('retread', 0)
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";

                $q->where(function ($qq) use ($like) {

                    // Tyre fields
                    $qq->where('tyre_number', 'like', $like)
                        ->orWhere('serial_number', 'like', $like)
                        ->orWhere('purchase_date', 'like', $like)
                        ->orWhere('type', 'like', $like);

                    // Related models
                    $qq->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like))
                        ->orWhereHas('product.brand', fn ($b) => $b->where('name', 'like', $like))
                        ->orWhereHas('currency', fn ($c) => $c->where('name', 'like', $like))
                        ->orWhereHas('store', fn ($s) => $s->where('name', 'like', $like))
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', $like));

                    // Search in active assignments by registration_number
                    $qq->orWhereHas('tyre_assignments', function ($ta) use ($like) {
                        $ta->where('status', 1)
                            ->where(function ($x) use ($like) {
                                $x->whereHas('horse', fn ($h) => $h->where('registration_number', 'like', $like))
                                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', $like))
                                    ->orWhereHas('trailer', fn ($t) => $t->where('registration_number', 'like', $like));
                            });
                    });
                });
            })
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        // Matches Blade order (excluding Action)
        return [
            'Tyre',
            'Dimensions',
            'Location',
            'Usage',
            'Health Status',
            'Qty',
            'Ccy',
            'Rate',
            'Tax',
            'Cost',
            'Total',
        ];
    }

    public function map($tyre): array
    {
        $brand   = $tyre->product?->brand?->name ?? '';
        $product = $tyre->product?->name ?? '';
        $serial  = $tyre->serial_number ? "S#: {$tyre->serial_number}" : null;

        $tyreCell = trim("{$brand} {$product}");
        $tyreType = $tyre->type ? "Type: {$tyre->type}" : null;

        $tyreTextLines = array_values(array_filter([$tyreCell, $tyreType, $serial]));
        $tyreText = implode("\n", $tyreTextLines);

        $dimensionsText = implode("\n", array_filter([
            trim(($tyre->width ?? '') . ' / ' . ($tyre->aspect_ratio ?? '') . ' R ' . ($tyre->diameter ?? '')),
            'Tread Depth(mm): ' . (is_null($tyre->thread_depth) ? '-' : number_format((float)$tyre->thread_depth, 2)),
            'Pressure(psi): ' . (is_null($tyre->pressure_psi) ? '-' : number_format((float)$tyre->pressure_psi, 2)),
            'Life Span(kms): ' . (is_null($tyre->life_span) ? '-' : number_format((float)$tyre->life_span, 2)),
        ]));

        // Location (assignment or instore/retread)
        $assignment = $tyre->activeAssignment;

        if ($assignment) {
            $assetLine = null;

            if ($assignment->horse) {
                $assetLine = 'Horse: ' . $assignment->horse->registration_number .
                    ($assignment->horse->fleet_number ? " ({$assignment->horse->fleet_number})" : '');
            } elseif ($assignment->trailer) {
                $assetLine = 'Trailer: ' . $assignment->trailer->registration_number .
                    ($assignment->trailer->fleet_number ? " ({$assignment->trailer->fleet_number})" : '');
            } elseif ($assignment->vehicle) {
                // NOTE: your blade had a small bug: it used $assignment->horse->registration_number for vehicle.
                $assetLine = 'Vehicle: ' . $assignment->vehicle->registration_number .
                    ($assignment->vehicle->fleet_number ? " ({$assignment->vehicle->fleet_number})" : '');
            }

            $locationText = implode("\n", array_filter([
                $assetLine,
                trim(($assignment->axle ?? '') . ' ' . ($assignment->position ?? '')),
            ]));
        } else {
            if ((int)($tyre->retread ?? 0) === 0) {
                $locationText = implode("\n", array_filter([
                    'Instore',
                    $tyre->store?->name ?? '',
                ]));
            } else {
                $locationText = 'Retread';
            }
        }

        // Usage (same logic as blade, only show fitted/current etc if assignment exists)
        $purchase = $tyre->purchase_date
            ? Carbon::parse($tyre->purchase_date)->format('d M Y')
            : '-';

        $usageLines = [
            "Acquisition: {$purchase}",
            "Age: " . ($tyre->age ?? '-'),
        ];

        if ($assignment) {
            $fitted  = number_format((float)($assignment->starting_odometer ?? 0));
            $current = $assignment->ending_odometer
                ? number_format((float)$assignment->ending_odometer)
                : number_format((float)($assignment->horse?->mileage ?? 0));

            $usageLines[] = "Fitted: {$fitted}";
            $usageLines[] = "Current: {$current}";
            $usageLines[] = "Travelled: " . number_format((float)($assignment->travelled_km ?? 0)) . " km";
            $usageLines[] = "Life(Standard): " . number_format((float)($tyre->life_span ?? 0)) . " km";

            $rem = $assignment->remaining_km;
            $pct = $assignment->remaining_pct;

            $remaining = is_null($rem) ? '-' : number_format((float)$rem) . ' km';
            if (!is_null($pct)) {
                $remaining .= " ({$pct}%)";
            }
            $usageLines[] = "Remaining: {$remaining}";
        }

        $usageText = implode("\n", $usageLines);

        // Health Status (use latestChecklistResult eager-loaded)
        $check = $tyre->latestChecklistResult;

        $healthText = '-';
        if ($check) {
            $depthBadge    = $this->badgeFromModels($tyre, $check, 'depth');
            $pressureBadge = $this->badgeFromModels($tyre, $check, 'pressure');
            $stars         = $this->stars((int)($check->rating ?? 0));
            $notes         = Str::limit((string)($check->notes ?? ''), 30, '...');

            $healthText = implode("\n", [
                "Tread Depth(mm): {$check->tread_depth_mm} ({$depthBadge})",
                "Tyre Pressure(psi): {$check->pressure_psi} ({$pressureBadge})",
                "Valve: " . ((int)$check->valve_ok === 1 ? "Air Tight" : "Leaking"),
                "Sidewall Damage: " . ($check->sidewall_damage ?? '-'),
                "Rim Condition: " . ($check->rim_condition ?? '-'),
                "Wheelnuts Torqued: " . ((int)$check->wheel_nuts_torqued === 1 ? "Yes" : "No"),
                "Axle Match: " . ((int)$check->axle_match === 1 ? "Match" : "Not Matching"),
                "Overall Rating: {$stars}",
                "Notes: {$notes}",
                "Action: " . ($check->action_required ?? '-'),
            ]);
        }

        $ccyName   = $tyre->currency?->name ?? '';
        $ccySymbol = $tyre->currency?->symbol ?? '';

        $rate = $ccySymbol . number_format((float)($tyre->amount ?? 0), 2);
        $tax  = $ccySymbol . number_format((float)($tyre->tax_amount ?? 0), 2);
        $cost = $ccySymbol . number_format((float)($tyre->cost ?? 0), 2);

        $totalLines = [
            $ccySymbol . number_format((float)($tyre->total ?? 0), 2),
        ];

        // Exchange lines (same as blade)
        $companyCurrencyId = Auth::user()?->employee?->company?->currency_id;
        if ($companyCurrencyId && $companyCurrencyId != $tyre->currency_id) {
            $companyCcyName   = Auth::user()->employee->company->currency?->name ?? '';
            $companyCcySymbol = Auth::user()->employee->company->currency?->symbol ?? '';

            $totalLines[] = "Exc Rate: " . number_format((float)($tyre->exchange_rate ?? 0), 2);
            $totalLines[] = "Exc Total: {$companyCcyName} {$companyCcySymbol}" . number_format((float)($tyre->exchange_amount ?? 0), 2);
        }

        $totalText = implode("\n", $totalLines);

        return [
            $tyreText,
            $dimensionsText,
            $locationText,
            $usageText,
            $healthText,
            $tyre->qty ?? 0,
            $ccyName,
            $rate,
            $tax,
            $cost,
            $totalText,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // 11 columns => A to K
                $event->sheet->getStyle('A7:K7')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                $highestRow = $event->sheet->getHighestRow();

                // Wrap the "rich text" columns like your blade
                $event->sheet->getStyle("A8:E{$highestRow}")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);

                $event->sheet->getStyle("K8:K{$highestRow}")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);

                // Optional widths for readability
                $event->sheet->getColumnDimension('A')->setWidth(28);
                $event->sheet->getColumnDimension('B')->setWidth(22);
                $event->sheet->getColumnDimension('C')->setWidth(24);
                $event->sheet->getColumnDimension('D')->setWidth(28);
                $event->sheet->getColumnDimension('E')->setWidth(30);
                $event->sheet->getColumnDimension('K')->setWidth(26);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();

        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . 'Logo');

            if (file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        }

        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    private function badgeFromModels(Tyre $tyre, ChecklistResult $check, string $category): string
    {
        if ($category === 'pressure') {
            $standard = (float)($tyre->pressure_psi ?? 0);
            $current  = (float)($check->pressure_psi ?? 0);
        } else {
            $standard = (float)($tyre->thread_depth ?? 0);
            $current  = (float)($check->tread_depth_mm ?? 0);
        }

        $pct = $standard > 0 ? ($current / $standard) * 100 : 0;

        if ($pct >= 90) return 'success';
        if ($pct >= 50) return 'warning';
        return 'danger';
    }

    private function stars(int $rating): string
    {
        $rating = max(0, min(5, $rating));
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }
}