<?php

namespace App\Exports;

use App\Models\Horse;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ─────────────────────────────────────────────────────────────────────────────
// MAIN EXPORT — one sheet per day of the current month
// ─────────────────────────────────────────────────────────────────────────────

class MonthlyShiftActivitiesExport implements WithMultipleSheets
{
    protected int $year;
    protected int $month;

    public function __construct(?int $year = null, ?int $month = null)
    {
        $now         = Carbon::now();
        $this->year  = $year  ?? $now->year;
        $this->month = $month ?? $now->month;
    }

    public function sheets(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        // Load ALL shifts for the month with relations in one query
        $allShifts = Shift::with([
            'trips.loadingPoint',
            'trips.offloadingPoint',
            'user',
            'team',
            'horse',
        ])
            ->whereBetween('shift_start_time', [$start, $end])
            ->orderBy('shift_start_time')
            ->get()
            ->groupBy(fn($s) => Carbon::parse($s->shift_start_time)->format('Y-m-d'));

        // All horses that have shifts this month
        $horseIds = Shift::whereBetween('shift_start_time', [$start, $end])
            ->whereNotNull('horse_id')
            ->pluck('horse_id')
            ->unique();

        $horses = Horse::whereIn('id', $horseIds)->orderBy('fleet_number')->get()->keyBy('id');

        $sheets = [];
        $daysInMonth = $start->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date    = Carbon::create($this->year, $this->month, $day);
            $dateKey = $date->format('Y-m-d');

            $dayShifts = $allShifts->get($dateKey, collect());

            // Group shifts by horse_id
            $shiftsByHorse = $dayShifts->groupBy('horse_id');

            // Build horse+shift collection for this day
            $horseShifts = collect();
            foreach ($horses as $horse) {
                $horse->setRelation('shifts', $shiftsByHorse->get($horse->id, collect()));
                $horseShifts->push(clone $horse);
            }

            // Only include days that have at least one shift
            if ($horseShifts->filter(fn($h) => $h->shifts->isNotEmpty())->isEmpty()) {
                continue;
            }

            $sheets[] = new DailyAllHorsesSheet($horseShifts, $date);
        }

        if (empty($sheets)) {
            $sheets[] = new DailyAllHorsesSheet(collect(), Carbon::now());
        }

        return $sheets;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET — all horses for a single day, pairs side by side, stacked vertically
// ─────────────────────────────────────────────────────────────────────────────

class DailyAllHorsesSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected Collection $horses;
    protected Carbon $date;

    const MAX_TRIPS   = 27;
    const LEFT_COLS   = 11;  // A–K
    const RIGHT_START = 13;  // M (L is spacer)
    const DAY         = 'day';
    const NIGHT       = 'night';

    // Tracks row ranges for styling: [[dayStart, nightStart, blockEnd], ...]
    protected array $blockMeta = [];

    public function __construct(Collection $horses, Carbon $date)
    {
        $this->horses = $horses;
        $this->date   = $date;
    }

    public function title(): string
    {
        return $this->date->format('d M'); // e.g. "01 May"
    }

    // ─────────────────────────────────────────────────────────
    // ARRAY
    // ─────────────────────────────────────────────────────────

    public function array(): array
    {
        $rows = [];
        $this->blockMeta = [];

        // Sheet title row
        $rows[] = array_merge(
            ['SHIFT ACTIVITIES MONITORING — ' . strtoupper($this->date->format('l, d F Y'))],
            array_fill(0, self::LEFT_COLS + 1 + self::LEFT_COLS - 1, '')
        );
        $rows[] = array_fill(0, self::LEFT_COLS + 1 + self::LEFT_COLS, '');

        $currentRow = 3; // 1-based: title=1, blank=2, next=3

        // Chunk horses into pairs
        $pairs = $this->horses->chunk(2);

        foreach ($pairs as $pair) {
            $horse1 = $pair->values()->get(0);
            $horse2 = $pair->values()->get(1);

            $dayStart = $currentRow;

            // Day shift block (both horses side by side)
            $dayBlock1 = $this->buildHorseBlock($horse1, self::DAY);
            $dayBlock2 = $this->buildHorseBlock($horse2, self::DAY);
            $dayMerged = $this->mergeBlocks($dayBlock1, $dayBlock2);

            foreach ($dayMerged as $row) {
                $rows[]     = $row;
                $currentRow++;
            }

            $nightStart = $currentRow;

            // Night shift block
            $nightBlock1 = $this->buildHorseBlock($horse1, self::NIGHT);
            $nightBlock2 = $this->buildHorseBlock($horse2, self::NIGHT);
            $nightMerged = $this->mergeBlocks($nightBlock1, $nightBlock2);

            foreach ($nightMerged as $row) {
                $rows[]     = $row;
                $currentRow++;
            }

            $blockEnd = $currentRow - 1;

            $this->blockMeta[] = [
                'dayStart'   => $dayStart,
                'nightStart' => $nightStart,
                'blockEnd'   => $blockEnd,
            ];

            // Spacer rows between horse pairs
            $rows[]     = array_fill(0, self::LEFT_COLS + 1 + self::LEFT_COLS, '');
            $rows[]     = array_fill(0, self::LEFT_COLS + 1 + self::LEFT_COLS, '');
            $currentRow += 2;
        }

        return $rows;
    }

    protected function buildHorseBlock(?object $horse, string $shiftType): array
    {
        $rows          = [];
        $shiftTypeName = $shiftType === self::DAY ? 'DAY SHIFT' : 'NIGHT SHIFT';

        $shift = null;
        if ($horse) {
            $shift = $horse->shifts->first(function ($s) use ($shiftType) {
                $hour = Carbon::parse($s->shift_start_time)->hour;
                return $shiftType === self::DAY
                    ? ($hour >= 0 && $hour < 14)
                    : ($hour >= 14);
            });
        }

        $teamLeader     = $shift ? trim(($shift->user?->name ?? '') . ' ' . ($shift->user?->surname ?? '')) : '';
        $teamName       = $shift?->team?->name ?? '';
        $departWorkshop = $shift ? Carbon::parse($shift->shift_start_time)->format('H:i') : '';
        $fleetNum       = $horse?->fleet_number ?? '';

        $firstTrip  = $shift?->trips->sortBy('depart_lp')->first();
        $arriveDome = $firstTrip && $firstTrip->arrive_lp
            ? Carbon::parse($firstTrip->arrive_lp)->format('H:i')
            : '';

        $rows[] = ['SHIFT ACTIVITIES MONITORING SHORTHAUL', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = array_fill(0, self::LEFT_COLS, '');
        $rows[] = ['Team: ' . $teamName, '', 'Team Leader: ' . $teamLeader, '', '', $shiftTypeName, '', '', '', '', ''];
        $rows[] = ['Fleet #', 'Depart Workshop', $departWorkshop, '', '', '', '', '', '', '', ''];
        $rows[] = [$fleetNum, 'Arrive dome', $arriveDome, '', '', '', '', '', '', '', ''];
        $rows[] = [
            'Load No', 'Loading Point', 'Depart Loading Point', 'Loading Time',
            'Arrive Offloading Point', 'Driving Time (Loaded)', 'Depart Offloading Point',
            'Offloading Time', 'Arrive Loading Point', 'Driving Time (Empty)', 'Total Trip Cycle Time',
        ];

        $trips             = $shift ? $shift->trips->sortBy('depart_lp')->values() : collect();
        $totalCycleMinutes = 0;

        for ($i = 0; $i < self::MAX_TRIPS; $i++) {
            $trip = $trips->get($i);
            $num  = $i + 1;

            if ($trip) {
                $departLP = $trip->depart_lp ? Carbon::parse($trip->depart_lp) : null;
                $arriveOP = $trip->arrive_op ? Carbon::parse($trip->arrive_op) : null;
                $departOP = $trip->depart_op ? Carbon::parse($trip->depart_op) : null;
                $arriveLP = $trip->arrive_lp ? Carbon::parse($trip->arrive_lp) : null;

                $loadingTime   = ($departLP && $arriveOP) ? $this->diffMinutes($departLP, $arriveOP) : null;
                $drivingLoaded = ($departLP && $arriveOP) ? $this->diffMinutes($departLP, $arriveOP) : null;
                $offloadTime   = ($arriveOP && $departOP) ? $this->diffMinutes($arriveOP, $departOP) : null;
                $drivingEmpty  = ($departOP && $arriveLP) ? $this->diffMinutes($departOP, $arriveLP) : null;
                $cycleTime     = ($departLP && $arriveLP) ? $this->diffMinutes($departLP, $arriveLP) : null;

                if ($cycleTime !== null) {
                    $totalCycleMinutes += $cycleTime;
                }

                $rows[] = [
                    $num,
                    $trip->loadingPoint?->name ?? '',
                    $departLP ? $departLP->format('H:i') : '',
                    $loadingTime   !== null ? $this->fmtMinutes($loadingTime)   : '0:00',
                    $arriveOP ? $arriveOP->format('H:i') : '',
                    $drivingLoaded !== null ? $this->fmtMinutes($drivingLoaded) : '0:00',
                    $departOP ? $departOP->format('H:i') : '',
                    $offloadTime   !== null ? $this->fmtMinutes($offloadTime)   : '0:00',
                    $arriveLP ? $arriveLP->format('H:i') : '',
                    $drivingEmpty  !== null ? $this->fmtMinutes($drivingEmpty)  : '0:00',
                    $cycleTime     !== null ? $this->fmtMinutes($cycleTime)     : '0:00',
                ];
            } else {
                $rows[] = [$num, '', '', '0:00', '', '0:00', '', '0:00', '', '0:00', '0:00'];
            }
        }

        $rows[] = [
            'Total Shift Time', '', '', '', '', '', '', '', '', '',
            $this->fmtMinutes($totalCycleMinutes),
        ];

        return $rows;
    }

    protected function mergeBlocks(array $block1, array $block2): array
    {
        $merged = [];
        $count  = max(count($block1), count($block2));
        $spacer = array_fill(0, self::LEFT_COLS, '');

        for ($i = 0; $i < $count; $i++) {
            $left  = $block1[$i] ?? $spacer;
            $right = $block2[$i] ?? array_fill(0, self::LEFT_COLS, '');
            while (count($left) < self::LEFT_COLS) {
                $left[] = '';
            }
            $merged[] = array_merge($left, [''], $right);
        }

        return $merged;
    }

    // ─────────────────────────────────────────────────────────
    // STYLES
    // ─────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  'B' => 18, 'C' => 12, 'D' => 10,
            'E' => 14, 'F' => 14, 'G' => 14, 'H' => 12,
            'I' => 14, 'J' => 14, 'K' => 14,
            'L' => 3,  // spacer
            'M' => 8,  'N' => 18, 'O' => 12, 'P' => 10,
            'Q' => 14, 'R' => 14, 'S' => 14, 'T' => 12,
            'U' => 14, 'V' => 14, 'W' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Style sheet title row
                $lastCol = $this->colLetter(self::LEFT_COLS + 1 + self::LEFT_COLS);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // Style each horse-pair block
                foreach ($this->blockMeta as $meta) {
                    $this->styleBlock($sheet, $meta['dayStart'],   false);
                    $this->styleBlock($sheet, $meta['dayStart'],   true);
                    $this->styleBlock($sheet, $meta['nightStart'], false);
                    $this->styleBlock($sheet, $meta['nightStart'], true);
                }

                // Print settings
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getHeaderFooter()
                    ->setOddHeader('&C&B Shift Activities — ' . $this->date->format('l, d F Y'));
                $sheet->getHeaderFooter()
                    ->setOddFooter('&LGenerated: &D &T&R Page &P of &N');
            },
        ];
    }

    protected function styleBlock(Worksheet $sheet, int $startRow, bool $isRight = false): void
    {
        $offset = $isRight ? self::RIGHT_START - 1 : 0;
        $col    = fn(int $n) => $this->colLetter($n + $offset);

        $r1        = $startRow;
        $r3        = $startRow + 2;
        $r4        = $startRow + 3;
        $r5        = $startRow + 4;
        $r6        = $startRow + 5;
        $firstTrip = $startRow + 6;
        $lastTrip  = $startRow + 5 + self::MAX_TRIPS;
        $totalsRow = $lastTrip + 1;

        $leftCol  = $col(1);
        $rightCol = $col(self::LEFT_COLS);

        // Title row
        $sheet->mergeCells("{$leftCol}{$r1}:{$rightCol}{$r1}");
        $sheet->getStyle("{$leftCol}{$r1}:{$rightCol}{$r1}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($r1)->setRowHeight(20);

        // Team / Team Leader / Shift type row
        $sheet->mergeCells("{$leftCol}{$r3}:{$col(2)}{$r3}");
        $sheet->mergeCells("{$col(3)}{$r3}:{$col(5)}{$r3}");
        $sheet->mergeCells("{$col(6)}{$r3}:{$rightCol}{$r3}");
        $sheet->getStyle("{$leftCol}{$r3}:{$rightCol}{$r3}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("{$col(6)}{$r3}:{$rightCol}{$r3}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Fleet info rows
        foreach ([$r4, $r5] as $fr) {
            $sheet->getStyle("{$leftCol}{$fr}:{$rightCol}{$fr}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEF3FF']],
            ]);
        }

        // Column header row
        $sheet->getStyle("{$leftCol}{$r6}:{$rightCol}{$r6}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E4057']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF7F8C8D']],
            ],
        ]);
        $sheet->getRowDimension($r6)->setRowHeight(36);

        // Trip rows
        for ($r = $firstTrip; $r <= $lastTrip; $r++) {
            $fillColor = ($r % 2 === 0) ? 'FFF5F8FF' : 'FFFFFFFF';
            $sheet->getStyle("{$leftCol}{$r}:{$rightCol}{$r}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillColor]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFCCCCCC']]],
                'font'      => ['size' => 8],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("{$col(2)}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $lpCell = $sheet->getCell($col(2) . $r)->getValue();
            if (empty($lpCell)) {
                $sheet->getStyle("{$leftCol}{$r}:{$rightCol}{$r}")
                    ->getFont()->getColor()->setARGB('FFB0B0B0');
            }
        }

        // Totals row
        $sheet->mergeCells("{$leftCol}{$totalsRow}:{$col(10)}{$totalsRow}");
        $sheet->getStyle("{$leftCol}{$totalsRow}:{$rightCol}{$totalsRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
            'borders'   => [
                'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF4472C4']],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Outer border for whole block
        $sheet->getStyle("{$leftCol}{$r1}:{$rightCol}{$totalsRow}")->applyFromArray([
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1F497D']],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    protected function diffMinutes(Carbon $from, Carbon $to): int
    {
        $diff = $from->diffInMinutes($to, false);
        return $diff < 0 ? $diff + 1440 : $diff;
    }

    protected function fmtMinutes(int $minutes): string
    {
        $h = intdiv(abs($minutes), 60);
        $m = abs($minutes) % 60;
        return ($minutes < 0 ? '-' : '') . $h . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
    }

    protected function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod    = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int)(($colIndex - $mod) / 26);
        }
        return $letter;
    }
}
