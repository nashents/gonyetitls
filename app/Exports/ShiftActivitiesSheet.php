<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShiftActivitiesSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected Collection $horses;
    protected Carbon $date;

    // Layout constants
    const MAX_TRIPS     = 27;
    const LEFT_COLS     = 11; // A–K (horse 1 block)
    const SPACER_COL    = 12; // L  (gap between the two blocks)
    const RIGHT_START   = 13; // M  (horse 2 block starts)
    const RIGHT_COLS    = 11; // M–W

    // Shift type identifiers
    const DAY   = 'day';
    const NIGHT = 'night';

    public function __construct(Collection $horses, Carbon $date)
    {
        $this->horses = $horses;
        $this->date   = $date;
    }

    public function title(): string
    {
        $fleets = $this->horses->pluck('fleet_number')->join('_');
        return $fleets ?: 'Horses';
    }

    // ─────────────────────────────────────────────────────────
    // ARRAY
    // ─────────────────────────────────────────────────────────

    public function array(): array
    {
        $horse1 = $this->horses->get(0);
        $horse2 = $this->horses->get(1);

        $rows = [];

        // ── Day shift block
        $dayBlock1  = $this->buildHorseBlock($horse1, self::DAY);
        $dayBlock2  = $this->buildHorseBlock($horse2, self::DAY);
        $dayMerged  = $this->mergeBlocks($dayBlock1, $dayBlock2);

        // ── Night shift block (appended below with a spacer)
        $nightBlock1 = $this->buildHorseBlock($horse1, self::NIGHT);
        $nightBlock2 = $this->buildHorseBlock($horse2, self::NIGHT);
        $nightMerged = $this->mergeBlocks($nightBlock1, $nightBlock2);

        foreach ($dayMerged as $row) {
            $rows[] = $row;
        }

        // Two blank spacer rows between day and night
        $rows[] = array_fill(0, self::LEFT_COLS + 1 + self::RIGHT_COLS, '');
        $rows[] = array_fill(0, self::LEFT_COLS + 1 + self::RIGHT_COLS, '');

        foreach ($nightMerged as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    protected function buildHorseBlock(?object $horse, string $shiftType): array
    {
        $rows = [];

        $shiftTypeName = $shiftType === self::DAY ? 'DAY SHIFT' : 'NIGHT SHIFT';

        // Resolve shift
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

        // Find first trip arrive_lp time as "Arrive dome"
        $firstTrip  = $shift?->trips->sortBy('depart_lp')->first();
        $arriveDome = $firstTrip && $firstTrip->arrive_lp
            ? Carbon::parse($firstTrip->arrive_lp)->format('H:i')
            : '';

        // ── Header rows
        $rows[] = ['SHIFT ACTIVITIES MONITORING SHORTHAUL', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = array_fill(0, self::LEFT_COLS, '');
        $rows[] = ['Team: ' . $teamName, '', 'Team Leader: ' . $teamLeader, '', '', $shiftTypeName, '', '', '', '', ''];
        $rows[] = ['Fleet #', 'Depart Workshop', $departWorkshop, '', '', '', '', '', '', '', ''];
        $rows[] = [$fleetNum, 'Arrive dome', $arriveDome, '', '', '', '', '', '', '', ''];

        // ── Column headers
        $rows[] = [
            'Load No',
            'Loading Point',
            'Depart Loading Point',
            'Loading Time',
            'Arrive Offloading Point',
            'Driving Time (Loaded)',
            'Depart Offloading Point',
            'Offloading Time',
            'Arrive Loading Point',
            'Driving Time (Empty)',
            'Total Trip Cycle Time',
        ];

        // ── Trip rows
        $trips = $shift ? $shift->trips->sortBy('depart_lp')->values() : collect();

        $totalCycleMinutes = 0;

        for ($i = 0; $i < self::MAX_TRIPS; $i++) {
            $trip = $trips->get($i);
            $num  = $i + 1;

            if ($trip) {
                $departLP  = $trip->depart_lp  ? Carbon::parse($trip->depart_lp)  : null;
                $arriveOP  = $trip->arrive_op  ? Carbon::parse($trip->arrive_op)  : null;
                $departOP  = $trip->depart_op  ? Carbon::parse($trip->depart_op)  : null;
                $arriveLP  = $trip->arrive_lp  ? Carbon::parse($trip->arrive_lp)  : null;

                $loadingTime   = ($departLP && $arriveOP)  ? $this->diffMinutes($departLP, $arriveOP)  : null;
                $drivingLoaded = ($arriveOP && $departLP)  ? $this->diffMinutes($departLP, $arriveOP)  : null;
                $offloadTime   = ($departOP && $arriveOP)  ? $this->diffMinutes($arriveOP, $departOP)  : null;
                $drivingEmpty  = ($arriveLP && $departOP)  ? $this->diffMinutes($departOP, $arriveLP)  : null;
                $cycleTime     = ($departLP && $arriveLP)  ? $this->diffMinutes($departLP, $arriveLP)  : null;

                if ($cycleTime !== null) {
                    $totalCycleMinutes += $cycleTime;
                }

                $rows[] = [
                    $num,
                    $trip->loadingPoint?->name ?? '',
                    $departLP  ? $departLP->format('H:i')  : '',
                    $loadingTime   !== null ? $this->fmtMinutes($loadingTime)   : '0:00',
                    $arriveOP  ? $arriveOP->format('H:i')  : '',
                    $drivingLoaded !== null ? $this->fmtMinutes($drivingLoaded) : '0:00',
                    $departOP  ? $departOP->format('H:i')  : '',
                    $offloadTime   !== null ? $this->fmtMinutes($offloadTime)   : '0:00',
                    $arriveLP  ? $arriveLP->format('H:i')  : '',
                    $drivingEmpty  !== null ? $this->fmtMinutes($drivingEmpty)  : '0:00',
                    $cycleTime     !== null ? $this->fmtMinutes($cycleTime)     : '0:00',
                ];
            } else {
                $rows[] = [$num, '', '', '0:00', '', '0:00', '', '0:00', '', '0:00', '0:00'];
            }
        }

        // ── Totals row
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
            $right = $block2[$i] ?? array_fill(0, self::RIGHT_COLS, '');

            // Pad left to LEFT_COLS
            while (count($left) < self::LEFT_COLS) {
                $left[] = '';
            }

            $merged[] = array_merge($left, [''], $right); // '' = spacer col L
        }

        return $merged;
    }

    // ─────────────────────────────────────────────────────────
    // STYLES & EVENTS
    // ─────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Load No / Fleet
            'B' => 18,  // Loading Point
            'C' => 12,  // Depart LP
            'D' => 10,  // Loading Time
            'E' => 14,  // Arrive OP
            'F' => 14,  // Driving Loaded
            'G' => 14,  // Depart OP
            'H' => 12,  // Offloading Time
            'I' => 14,  // Arrive LP
            'J' => 14,  // Driving Empty
            'K' => 14,  // Cycle Time
            'L' => 3,   // Spacer
            'M' => 8,
            'N' => 18,
            'O' => 12,
            'P' => 10,
            'Q' => 14,
            'R' => 14,
            'S' => 14,
            'T' => 12,
            'U' => 14,
            'V' => 14,
            'W' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Total rows: day block rows + 2 spacers + night block rows
                // Each block = 6 header rows + 27 trip rows + 1 total row = 34
                $blockRows = 6 + self::MAX_TRIPS + 1; // 34

                $dayStart   = 1;
                $dayEnd     = $dayStart + $blockRows - 1;
                $nightStart = $dayEnd + 3; // +2 spacers +1
                $nightEnd   = $nightStart + $blockRows - 1;

                foreach ([$dayStart, $nightStart] as $blockStart) {
                    $this->styleBlock($sheet, $blockStart);
                    // Right-side block (same structure, offset to col M)
                    $this->styleBlock($sheet, $blockStart, true);
                }

                // Print settings
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                // Manual page break between day and night blocks
                $sheet->setBreak(
                    'A' . ($dayEnd + 1),
                    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW
                );

                $sheet->getHeaderFooter()
                    ->setOddHeader('&C&B Shift Activities Monitoring — ' . $this->date->format('d F Y'));
                $sheet->getHeaderFooter()
                    ->setOddFooter('&LGenerated: &D &T&R Page &P of &N');
            },
        ];
    }

    protected function styleBlock(Worksheet $sheet, int $startRow, bool $isRight = false): void
    {
        $offset = $isRight ? self::RIGHT_START - 1 : 0; // column offset (0-based addition)

        $col = fn(int $n) => $this->colLetter($n + $offset);

        $r1 = $startRow;     // Title
        $r2 = $startRow + 1; // blank
        $r3 = $startRow + 2; // Team Leader / shift type
        $r4 = $startRow + 3; // Fleet # / Depart Workshop
        $r5 = $startRow + 4; // Fleet value / Arrive dome
        $r6 = $startRow + 5; // Column headers
        $firstTrip  = $startRow + 6;
        $lastTrip   = $startRow + 5 + self::MAX_TRIPS;
        $totalsRow  = $lastTrip + 1;

        $leftCol  = $col(1);
        $rightCol = $col(self::LEFT_COLS);

        // ── R1: Title
        $sheet->mergeCells("{$leftCol}{$r1}:{$rightCol}{$r1}");
        $sheet->getStyle("{$leftCol}{$r1}:{$rightCol}{$r1}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($r1)->setRowHeight(22);

        // ── R3: Team Leader + Shift type
        $sheet->mergeCells("{$leftCol}{$r3}:{$col(2)}{$r3}");
        $sheet->mergeCells("{$col(3)}{$r3}:{$rightCol}{$r3}");
        $sheet->getStyle("{$leftCol}{$r3}:{$rightCol}{$r3}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("{$col(3)}{$r3}:{$rightCol}{$r3}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── R4 & R5: Fleet info
        foreach ([$r4, $r5] as $fr) {
            $sheet->getStyle("{$leftCol}{$fr}:{$rightCol}{$fr}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEF3FF']],
            ]);
        }

        // ── R6: Column headers
        $sheet->getStyle("{$leftCol}{$r6}:{$rightCol}{$r6}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E4057']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF7F8C8D']],
            ],
        ]);
        $sheet->getRowDimension($r6)->setRowHeight(36);

        // ── Trip rows: alternating + time-column formatting
        for ($r = $firstTrip; $r <= $lastTrip; $r++) {
            $fillColor = ($r % 2 === 0) ? 'FFF5F8FF' : 'FFFFFFFF';

            $sheet->getStyle("{$leftCol}{$r}:{$rightCol}{$r}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillColor]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFCCCCCC']],
                ],
                'font'    => ['size' => 8],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Load No left-aligned
            $sheet->getStyle("{$leftCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Loading Point left-aligned
            $sheet->getStyle("{$col(2)}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Highlight zero-filled rows slightly dimmer
            $lpCell = $sheet->getCell($col(2) . $r)->getValue();
            if (empty($lpCell)) {
                $sheet->getStyle("{$leftCol}{$r}:{$rightCol}{$r}")
                    ->getFont()->getColor()->setARGB('FF9E9E9E');
            }
        }

        // ── Totals row
        $sheet->mergeCells("{$leftCol}{$totalsRow}:{$col(10)}{$totalsRow}");
        $sheet->getStyle("{$leftCol}{$totalsRow}:{$rightCol}{$totalsRow}")->applyFromArray([
            'font'    => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
            'borders' => [
                'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF4472C4']],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ── Outer border for whole block
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
        // Handle overnight — if negative add 24h
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
