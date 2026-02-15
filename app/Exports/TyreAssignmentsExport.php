<?php

namespace App\Exports;

use App\Models\ChecklistResult;
use App\Models\Tyre;
use App\Models\TyreAssignment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class TyreAssignmentsExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithEvents,
    WithDrawings,
    WithCustomStartCell
{
    use Exportable;

   
    public $equipmentId;
    public $type;

    public function __construct($id = null, $type = null)
    {
        
        $this->type = $type;
        $this->equipmentId = $id;
       
    }

    public function query()
    {
        $baseQuery = TyreAssignment::query()
            ->where('status', 1)
            ->with([
                'horse',
                'tyre.product.brand',
                'tyre.latestChecklistResult',
            ]);

            if ($this->type == "Horse") {
                $baseQuery->where('horse_id', $this->equipmentId);
            } elseif ($this->type == "Trailer") {
                $baseQuery->where('trailer_id', $this->equipmentId);
            } elseif ($this->type == "Vehicle") {
                $baseQuery->where('vehicle_id', $this->equipmentId);
            }

            $baseQuery->orderBy('created_at', 'desc');
        

        return $baseQuery;
            
    }

    public function map($tyre_assignment): array
    {
        $tyre = $tyre_assignment->tyre;

        $product_name = $tyre?->product?->name ?? '';
        $brand_name   = $tyre?->product?->brand?->name ?? '';

        $width    = $tyre?->width ?? '';
        $ratio    = $tyre?->aspect_ratio ?? '';
        $diameter = $tyre?->diameter ?? '';
        $specs    = trim($width . '/' . $ratio . 'R' . $diameter);

        // ---------------- Usage column ----------------
        $purchaseDate = $tyre?->purchase_date
            ? \Carbon\Carbon::parse($tyre->purchase_date)->format('d M Y')
            : '-';

        $age = $tyre?->age ?? '-';

        $fitted  = number_format((float)($tyre_assignment->starting_odometer ?? 0));
        $current = $tyre_assignment->ending_odometer
            ? number_format((float)$tyre_assignment->ending_odometer)
            : number_format((float)($tyre_assignment->horse?->mileage ?? 0));

        $travelled = number_format((float)($tyre_assignment->travelled_km ?? 0)) . ' km';
        $lifeStd   = number_format((float)($tyre?->life_span ?? 0)) . ' km';

        $rem = $tyre_assignment->remaining_km;
        $pct = $tyre_assignment->remaining_pct;

        $remaining = is_null($rem) ? '-' : number_format((float)$rem) . ' km';
        if (!is_null($pct)) {
            $remaining .= " ({$pct}%)";
        }

        $usageText = implode("\n", [
            "Acquisition: {$purchaseDate}",
            "Age: {$age}",
            "Fitted: {$fitted}",
            "Current: {$current}",
            "Travelled: {$travelled}",
            "Life(Standard): {$lifeStd}",
            "Remaining: {$remaining}",
        ]);

        // ---------------- Health column ----------------
        $healthText = '-';

        $checklist = $tyre?->latestChecklistResult; // ✅ already eager-loaded

        if ($tyre && $checklist) {
            $depthBadge    = $this->badgeFromModels($tyre, $checklist, 'depth');
            $pressureBadge = $this->badgeFromModels($tyre, $checklist, 'pressure');

            $stars = $this->stars((int)($checklist->rating ?? 0));
            $notes = Str::limit((string)($checklist->notes ?? ''), 30, '...');

            $healthText = implode("\n", [
                "Tread Depth(mm): {$checklist->tread_depth_mm} ({$depthBadge})",
                "Tyre Pressure(psi): {$checklist->pressure_psi} ({$pressureBadge})",
                "Valve: " . ((int)$checklist->valve_ok === 1 ? 'Air Tight' : 'Leaking'),
                "Sidewall Damage: " . ($checklist->sidewall_damage ?? '-'),
                "Rim Condition: " . ($checklist->rim_condition ?? '-'),
                "Wheelnuts Torqued: " . ((int)$checklist->wheel_nuts_torqued === 1 ? 'Yes' : 'No'),
                "Axle Match: " . ((int)$checklist->axle_match === 1 ? 'Match' : 'Not Matching'),
                "Overall Rating: {$stars}",
                "Notes: {$notes}",
                "Action: " . ($checklist->action_required ?? '-'),
            ]);
        }

        // NOTE: Headings are 8 columns, so map MUST return 8 columns.
        return [
            $tyre?->tyre_number ?? '',
            trim($product_name . ' ' . $brand_name),
            $tyre?->serial_number ?? '',
            $specs,
            $tyre_assignment->axle,      // Axle
            $tyre_assignment->position,  // Position
            $usageText,                  // Usage
            $healthText,                 // Health
        ];
    }

    public function headings(): array
    {
        return [
            'Tyre#',
            'Product',
            'Serial#',
            'Specifications',
            'Axle',
            'Position',
            'Usage',
            'Health',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // ✅ Your header row is A7:H7 (8 columns) not J
                $event->sheet->getStyle('A7:H7')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // ✅ Wrap text for Usage + Health columns so multi-line displays properly
                $highestRow = $event->sheet->getHighestRow();

                $event->sheet->getStyle("G8:H{$highestRow}")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);

                // Optional but highly recommended for readability
                $event->sheet->getColumnDimension('G')->setWidth(40);
                $event->sheet->getColumnDimension('H')->setWidth(45);
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

    /**
     * Badge logic but without extra DB queries (fast).
     */
    private function badgeFromModels(Tyre $tyre, ChecklistResult $checklist, string $category): string
    {
        $badge = 'active';

        if ($category === 'pressure') {
            $standard = (float)($tyre->pressure_psi ?? 0);
            $current  = (float)($checklist->pressure_psi ?? 0);
        } else { // depth
            $standard = (float)($tyre->thread_depth ?? 0);
            $current  = (float)($checklist->tread_depth_mm ?? 0);
        }

        $pct = $standard > 0 ? ($current / $standard) * 100 : 0;

        if ($pct >= 90) {
            return 'success';
        }
        if ($pct >= 50) {
            return 'warning';
        }
        return 'danger';
    }

    private function stars(int $rating): string
    {
        $rating = max(0, min(5, $rating));
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }
}