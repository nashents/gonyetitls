<?php

namespace App\Exports;

use App\Models\BillExpense;
use App\Models\Cargo;
use App\Models\Consignee;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\InvoiceItem;
use App\Models\Route;
use App\Models\Transporter;
use App\Models\TripDocument;
use App\Models\TripType;
use App\Services\Accounting\TransporterPaymentBillService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Manifest report (Frazier layout): one row per trip with orders, deals,
 * meter/dip/shoretank litreages, losses, freight incl VAT, fuel, expenses
 * and running totals. Reuses TripsReportExport's filters/search/date range
 * (query()), logo and generated/filters line, but not its summary metrics.
 *
 * VAT comes from the trip's invoice lines (customer) and Transporter Payment
 * bill lines (transporter); allowable/chargeable loss is on meter @20.
 * Fuel: Amt = cost, Retail Amt = what the transporter is charged (deducted
 * from Net Payable, cost when no retail total).
 */
class ManifestReportExport extends TripsReportExport
{
    private const LITRE_FIELDS = [
        'loaded_litreage', 'loaded_litreage_at_20',
        'loaded_dip_litreage', 'loaded_dip_litreage_at_20',
        'offloaded_litreage', 'offloaded_litreage_at_20',
        'offloaded_dip_litreage', 'offloaded_dip_litreage_at_20',
        'shoretank_litreage', 'shoretank_litreage_at_20',
    ];

    // Rows above it hold the generated/filters line, logo and title block
    private const HEADER_ROW = 9;

    protected float $cumulativeTransporter = 0.0;
    protected float $cumulativeCustomer = 0.0;

    public function __construct($from, $to, $trip_filter, $search, array $filters = [])
    {
        // Parent constructor builds the summary metrics - not needed here
        $this->from = $from;
        $this->to = $to;
        $this->trip_filter = $trip_filter;
        $this->search = $search;

        $this->horse_id            = $filters['horse_id'] ?? null;
        $this->driver_id           = $filters['driver_id'] ?? null;
        $this->customer_id         = $filters['customer_id'] ?? null;
        $this->currency_id         = $filters['currency_id'] ?? null;
        $this->cargo_id            = $filters['cargo_id'] ?? null;
        $this->from_destination_id = $filters['from_destination_id'] ?? null;
        $this->to_destination_id   = $filters['to_destination_id'] ?? null;
        $this->consignee_id        = $filters['consignee_id'] ?? null;
        $this->trip_type_id        = $filters['trip_type_id'] ?? null;
        $this->transporter_id      = $filters['transporter_id'] ?? null;
        $this->route_id            = $filters['route_id'] ?? null;
        $this->trip_status         = $filters['trip_status'] ?? null;
        $this->auth_status         = $filters['auth_status'] ?? null;

        // Names for the "Applied filters" line
        $this->horse       = Horse::find($this->horse_id);
        $this->driver      = Driver::find($this->driver_id);
        $this->customer    = Customer::find($this->customer_id);
        $this->currency    = Currency::find($this->currency_id);
        $this->cargo       = Cargo::find($this->cargo_id);
        $this->origin      = Destination::find($this->from_destination_id);
        $this->destination = Destination::find($this->to_destination_id);
        $this->consignee   = Consignee::find($this->consignee_id);
        $this->trip_type   = TripType::find($this->trip_type_id);
        $this->transporter = Transporter::find($this->transporter_id);
        $this->route       = Route::find($this->route_id);

        $this->user = Auth::user();
        $this->company = $this->user->employee->company ?? null;
    }

    public function query()
    {
        return parent::query()->with([
            'transport_orders.deal',
            'deal',
            'trip_origins.destination.country',
            'trip_origins.loading_point',
            'trip_destinations.destination.country',
            'trip_destinations.offloading_point',
            'fromDestination.country',
            'toDestination.country',
            'customer',
            'company',
        ]);
    }

    public function map($trip): array
    {
        $notes = $this->deliveryNotes($trip);
        $litres = [];
        foreach (self::LITRE_FIELDS as $field) {
            $litres[$field] = $this->sumField($notes, $field);
        }

        $loadedDate = $notes->pluck('loaded_date')->filter()->sort()->first();
        $offloadedDate = $notes->pluck('offloaded_date')->filter()->sort()->last()
            ?: ($trip->trip_status == 'Offloaded' ? $trip->trip_status_date : null);

        $orders = $trip->transport_orders;

        $deals = $orders->pluck('deal')->push($trip->deal)->filter()->unique('id');

        // Losses on meter @20 vs the trip's allowable %
        $loss = fn ($loaded, $offloaded) => (! is_null($loaded) && ! is_null($offloaded)) ? round($loaded - $offloaded, 3) : null;
        $loadedAt20 = $litres['loaded_litreage_at_20'];
        $meterLossAt20 = $loss($loadedAt20, $litres['offloaded_litreage_at_20']);
        $allowablePct = $trip->allowableLossPercentage();
        $allowableLoss = ($allowablePct && $loadedAt20 > 0) ? round($loadedAt20 * $allowablePct / 100, 3) : null;
        $chargeableLoss = (! is_null($meterLossAt20) && ! is_null($allowableLoss)) ? max(0, round($meterLossAt20 - $allowableLoss, 3)) : null;
        $chargeableLossAmount = $trip->chargeableLossAmount($chargeableLoss);

        // Freight + VAT
        $transporterFreight = is_numeric($trip->transporter_freight) ? (float) $trip->transporter_freight : 0.0;
        $transporterVat = $this->transporterVat($trip);
        $customerFreight = is_numeric($trip->freight) ? (float) $trip->freight : 0.0;
        $invoiceItems = InvoiceItem::with('invoice:id,invoice_number')
            ->where('trip_id', $trip->id)
            ->whereHas('invoice')
            ->get();
        $customerVat = round((float) $invoiceItems->sum(fn ($item) => is_numeric($item->tax_amount) ? $item->tax_amount : 0), 2);

        // Fuel orders - this trip's allocated share of each
        $fuels = $trip->allocatedFuels();
        $fuelCost = $fuels->sum(fn ($fuel) => is_numeric($fuel->amount) ? (float) $fuel->amount : 0.0);
        $fuelAmount = $fuels->sum(fn ($fuel) => $this->fuelDeduction($fuel));

        // Transporter expenses (fuel-order lines are already in the fuel columns)
        $companyCurrencyId = $this->company->currency_id ?? null;
        $expenses = $trip->trip_expenses
            ->whereNull('fuel_id')
            ->filter(fn ($expense) => strtolower((string) $expense->category) === 'transporter')
            ->map(function ($expense) use ($companyCurrencyId) {
                $amount = $expense->currency_id == $companyCurrencyId ? $expense->amount : $expense->exchange_amount;

                return [
                    'name'   => $expense->expense->name ?? 'Expense',
                    'amount' => is_numeric($amount) ? (float) $amount : 0.0,
                ];
            });
        $totalExpenses = round($expenses->sum('amount'), 2);

        $transporterIncl = round($transporterFreight + $transporterVat, 2);
        $customerIncl = round($customerFreight + $customerVat, 2);
        $netPayable = round($transporterIncl - $fuelAmount - $totalExpenses - (float) $chargeableLossAmount, 2);

        $this->cumulativeTransporter += $netPayable;
        $this->cumulativeCustomer += $customerIncl;

        $pod = TripDocument::where('trip_id', $trip->id)->where('title', 'POD')->first();

        return [
            // Reg# + fleet# ("primary (secondary)" per the company's fleet identifier setting)
            $trip->horse->identifier_label ?? ($trip->vehicle->identifier_label ?? ''),
            $trip->trailers->map(fn ($trailer) => $trailer->identifier_label)->filter()->implode(', '),
            $orders->pluck('transport_order_number')->filter()->implode(', '),
            $orders->pluck('custom_ref')->filter()->implode(', '),
            $deals->pluck('deal_number')->filter()->implode(', '),
            $deals->pluck('reference')->filter()->implode(', '),
            $orders->map(fn ($order) => $this->orderQuantity($order))->filter()->implode(', '),
            $this->formatDate($loadedDate ?: $trip->start_date, 'M Y'),
            $trip->customer->name ?? '',
            $pod->document_number ?? '',
            $trip->trip_number . ($trip->trip_ref ? ' / ' . $trip->trip_ref : ''),
            $trip->cargo->name ?? '',
            $trip->transporter->name ?? '',
            trim(($trip->driver->employee->name ?? '') . ' ' . ($trip->driver->employee->surname ?? '')),
            $this->places($trip->trip_origins, $trip->fromDestination),
            $this->points($trip->trip_origins, 'loading_point', $trip->loading_point),
            $this->formatDate($loadedDate, 'd M Y H:i'),
            $this->places($trip->trip_destinations, $trip->toDestination),
            $this->points($trip->trip_destinations, 'offloading_point', $trip->offloading_point),
            $this->formatDate($offloadedDate, 'd M Y H:i'),
            $this->durationDays($loadedDate, $offloadedDate),
            $this->mileage($trip),
            $trip->trip_status,
            $litres['loaded_litreage'],
            $litres['loaded_litreage_at_20'],
            $litres['loaded_dip_litreage'],
            $litres['loaded_dip_litreage_at_20'],
            $litres['shoretank_litreage_at_20'],
            $loss($litres['loaded_litreage_at_20'], $litres['shoretank_litreage_at_20']),
            $litres['offloaded_litreage'],
            $litres['offloaded_litreage_at_20'],
            $loss($litres['loaded_litreage'], $litres['offloaded_litreage']),
            $meterLossAt20,
            $litres['offloaded_dip_litreage'],
            $litres['offloaded_dip_litreage_at_20'],
            $loss($litres['loaded_dip_litreage'], $litres['offloaded_dip_litreage']),
            $loss($litres['loaded_dip_litreage_at_20'], $litres['offloaded_dip_litreage_at_20']),
            $allowablePct,
            $trip->chargeableLossRate(),
            $allowableLoss,
            $chargeableLoss,
            $chargeableLossAmount,
            is_numeric($trip->transporter_rate) ? (float) $trip->transporter_rate : null,
            $transporterFreight,
            $transporterVat,
            $transporterIncl,
            is_numeric($trip->rate) ? (float) $trip->rate : null,
            $customerFreight,
            $customerVat,
            $customerIncl,
            $fuels->pluck('order_number')->implode("\n"),
            $fuels->map(fn ($fuel) => $fuel->quantity)->implode("\n"),
            $fuels->map(fn ($fuel) => $fuel->unit_price)->implode("\n"),
            $fuels->map(fn ($fuel) => $fuel->transporter_price)->implode("\n"),
            round($fuelCost, 2),
            round($fuelAmount, 2),
            $expenses->map(fn ($expense) => $expense['name'] . ': ' . number_format($expense['amount'], 2))->implode("\n"),
            $totalExpenses,
            $netPayable,
            round($this->cumulativeTransporter, 2),
            round($this->cumulativeCustomer, 2),
            $invoiceItems->pluck('invoice.invoice_number')->filter()->unique()->implode(', '),
        ];
    }

    public function headings(): array
    {
        return [
            'Equipment', 'Trailer(s)', 'Order#', 'OrderRef#', 'Deal#', 'DealRef#', 'OrderQty', 'Month',
            'Customer', 'POD#', 'Trip#', 'Cargo', 'Transporter', 'Driver', 'From', 'LoadingPoint(s)',
            'LoadingDate', 'To', 'Offloading Point(s)', 'OffloadingDate', 'Duration', 'Mileage', 'Trip Status',
            'LP Litreage @ Ambient (M)', 'LP Litreage @ 20 (M)', 'LP Litreage @ Ambient (D)', 'LP Litreage @ 20 (D)',
            'Shoretank @ 20', 'Shoretank @ 20 loss',
            'OP Litreage @Ambient (M)', 'OP Litreage @ 20 (M)', 'Gain/Loss @ Ambient (M)', 'Gain/Loss @ 20 (M)',
            'OP Litreage @ Ambient (D)', 'OP Litreage @ 20 (D)', 'Gain/Loss @ Ambient (D)', 'Gain/Loss @ 20 (D)',
            'Allowable Loss %', 'Chargeable Loss Rate', 'Allowable Loss', 'Chargeable Loss', 'Chargeable Loss Amt',
            'Transporter Rate', 'Transporter Freight(Excl VAT)', 'Transporter VAT', 'Transporter Freight(Incl VAT)',
            'Customer Rate', 'Customer Freight (Excl VAT)', 'Customer Freight VAT', 'Customer Freight (Incl VAT)',
            'FuelOrder#', 'FuelOrder Qty', 'FuelOrder Rate', 'FuelOrder Retail Rate', 'FuelOrderAmt', 'FuelOrder Retail Amt',
            'Expenses', 'Total Expenses', 'Net Payable (Transporter)',
            'CummulativeAmt Transporter', 'CummulativeAmt Customer', 'Invoice#',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                $h = self::HEADER_ROW;
                $first = $h + 1;
                $header = "A{$h}:{$lastColumn}{$h}";

                // Generated on / by + applied filters (same line as the Trips report)
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', $this->reportMetaText());
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // Title block beside the logo (logo sits at A3)
                $sheet->setCellValue('D3', 'MANIFEST REPORT');
                $sheet->setCellValue('D4', $this->company->name ?? '');
                $sheet->setCellValue('D5', 'Period');
                $sheet->setCellValue('E5', $this->periodText());
                $sheet->setCellValue('D6', 'Total Trips');
                $sheet->setCellValueExplicit('E6', (string) max(0, $lastRow - $h), DataType::TYPE_STRING);
                $sheet->getStyle('D3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('D4')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('D5:E6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE4D6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle($header)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E5']],
                    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("AB{$h}:AK{$h}")->getFill()->getStartColor()->setRGB('FFFF00');
                $sheet->getStyle("AQ{$h}:AX{$h}")->getFill()->getStartColor()->setRGB('C5E0B4');

                if ($lastRow > $h) {
                    $sheet->getStyle("A{$first}:{$lastColumn}{$lastRow}")->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
                    ]);
                    $sheet->getStyle("X{$first}:AK{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
                    $sheet->getStyle("AL{$first}:AL{$lastRow}")->getNumberFormat()->setFormatCode('0.00');
                    $sheet->getStyle("AM{$first}:AM{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.0000');
                    $sheet->getStyle("AN{$first}:AO{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.000');
                    $sheet->getStyle("AP{$first}:AX{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("BC{$first}:BD{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("BF{$first}:BI{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                $sheet->freezePane('A' . $first);
                $sheet->setAutoFilter("A{$h}:{$lastColumn}" . max($h, $lastRow));
            },
        ];
    }

    public function startCell(): string
    {
        return 'A' . self::HEADER_ROW;
    }

    /** Date basis + range, e.g. "Offloaded Date: 2026-10-01 to 2026-10-31". */
    private function periodText(): string
    {
        $basis = [
            'created_at'       => 'Date Created',
            'start_date'       => 'Start Date',
            'end_date'         => 'End Date',
            'trip_status_date' => 'Status Date',
            'offloaded_date'   => 'Offloaded Date',
        ][$this->trip_filter] ?? 'Date Created';

        // No range = the current month (see baseTripPeriodQuery)
        $range = (filled($this->from) && filled($this->to))
            ? $this->from . ' to ' . $this->to
            : now()->format('F Y');

        return $basis . ': ' . $range;
    }

    /** Transport-order delivery notes when the trip has any, else the trip's own (legacy) note. */
    protected function deliveryNotes($trip)
    {
        $notes = DeliveryNote::where('trip_id', $trip->id)->get();
        $orderNotes = $notes->whereNotNull('trip_transport_order_id');

        return $orderNotes->isNotEmpty() ? $orderNotes : $notes->whereNull('trip_transport_order_id')->take(1);
    }

    private function sumField($notes, string $field): ?float
    {
        $values = $notes->pluck($field)->filter(fn ($value) => is_numeric($value));

        return $values->isEmpty() ? null : round((float) $values->sum(), 3);
    }

    private function transporterVat($trip): float
    {
        return round((float) BillExpense::whereHas('bill', function ($q) use ($trip) {
                $q->where('trip_id', $trip->id)->where('category', TransporterPaymentBillService::CATEGORY);
            })
            ->get()
            ->sum(fn ($line) => is_numeric($line->tax_amount) ? $line->tax_amount : 0), 2);
    }

    /** What the transporter is charged for the fuel (falls back to cost when no transporter total). */
    private function fuelDeduction($fuel): float
    {
        if (is_numeric($fuel->transporter_total) && $fuel->transporter_total > 0) {
            return (float) $fuel->transporter_total;
        }

        return is_numeric($fuel->amount) ? (float) $fuel->amount : 0.0;
    }

    private function orderQuantity($order): string
    {
        foreach (['litreage' => 'L', 'weight' => 'T', 'quantity' => ''] as $field => $unit) {
            if (is_numeric($order->$field) && $order->$field > 0) {
                return rtrim(rtrim(number_format((float) $order->$field, 2, '.', ''), '0'), '.') . ($unit ? " {$unit}" : '');
            }
        }

        return '';
    }

    /** Unique "Country City" of the trip's points, else the header destination. */
    private function places($points, $fallback): string
    {
        $label = fn ($destination) => $destination ? trim(($destination->country->name ?? '') . ' ' . ($destination->city ?? '')) : '';

        $labels = $points->map(fn ($point) => $label($point->destination))->filter()->unique();

        return $labels->isNotEmpty() ? $labels->implode("\n") : $label($fallback);
    }

    private function points($points, string $relation, $fallback): string
    {
        $names = $points->map(fn ($point) => $point->$relation->name ?? null)->filter()->unique();

        return $names->isNotEmpty() ? $names->implode("\n") : ($fallback->name ?? '');
    }

    private function durationDays($start, $end): ?float
    {
        try {
            return ($start && $end) ? round(Carbon::parse($start)->diffInMinutes(Carbon::parse($end), false) / 1440, 1) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function mileage($trip)
    {
        if (is_numeric($trip->starting_mileage) && is_numeric($trip->ending_mileage) && $trip->ending_mileage >= $trip->starting_mileage) {
            return (float) $trip->ending_mileage - (float) $trip->starting_mileage;
        }

        return is_numeric($trip->distance) ? (float) $trip->distance : null;
    }

    private function formatDate($value, string $format): string
    {
        if (! $value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}
