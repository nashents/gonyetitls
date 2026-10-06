<?php

namespace App\Services;

use App\Exports;
use App\Exports\ModelBackupExport;
use App\Models;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Exit backup: runs every list-level Excel export with an all-time date range
 * and bundles the files into one zip, so a client leaving Gonyeti keeps their data.
 *
 * Most exports fall back to "current month" when from/to are empty, so an
 * explicit wide range is passed instead of nulls.
 */
class DataBackupService
{
    const FROM = '1900-01-01';

    /**
     * File name (without extension) => factory returning the export object.
     * Per-record exports (one horse's trips, one customer's statement, a single
     * day's shift sheet, import templates) are deliberately left out — the
     * list-level exports below already contain those rows.
     */
    public function exports(): array
    {
        $from = self::FROM;
        $to = now()->addYears(50)->toDateString();

        $billFilters = [
            'bill_filter' => 'created_at', 'tax_status' => null, 'customer_id' => null, 'transporter_id' => null,
            'asset_id' => null, 'trip_id' => null, 'trailer_id' => null, 'horse_id' => null,
            'currency_id' => null, 'vehicle_id' => null,
        ];
        $invoiceFilters = [
            'invoice_filter' => 'created_at', 'tax_status' => null, 'customer_id' => null,
            'transporter_id' => null, 'currency_id' => null,
        ];
        $shiftFilters = array_fill_keys([
            'filter_team_id', 'filter_transporter_id', 'filter_customer_id', 'filter_driver_id', 'filter_user_id',
            'filter_horse_id', 'filter_from_destination', 'filter_to_destination', 'filter_cargo_id',
            'filter_vehicle_id', 'filter_haulage_type', 'filter_loading_point_id', 'filter_offloading_point_id',
            'filter_shift_type', 'filter_for',
        ], null);

        return array_filter([
            // Operations
            'Operations/Trips'                   => fn () => new Exports\TripsReportExport($from, $to, 'created_at', null, []),
            'Operations/POD Tracker'             => fn () => new Exports\PodTracker($from, $to, 'created_at', null),
            'Operations/Shifts'                  => fn () => new Exports\ShiftsExport($from, $to, 'created_at', null, $shiftFilters),
            'Operations/Fuel Orders'             => fn () => new Exports\FuelsExport($from, $to, 'created_at', null),
            'Operations/Fuel Requests'           => fn () => new Exports\FuelRequestsExport(),
            'Operations/Allocations'             => fn () => new Exports\AllocationsExport(),
            'Operations/Assignments'             => fn () => new Exports\AssignmentsExport(),
            'Operations/Rehandlings'             => $this->table(Models\Rehandling::class),
            'Operations/Rentals'                 => $this->table(Models\Rental::class),
            'Operations/Waste Collections'       => $this->table(Models\WasteCollection::class),
            'Operations/Waste Receptacles'       => $this->table(Models\WasteReceptacle::class),
            'Operations/Waste Types'             => $this->table(Models\WasteType::class),
            'Operations/Works'                   => $this->table(Models\Work::class),

            // Finance
            'Finance/Invoices'                   => fn () => new Exports\SalesExport($from, $to, $invoiceFilters, null),
            // PaymentsExport is legacy (built for invoice columns), so dump the cash ledger table directly.
            'Finance/Payments'                   => $this->table(Models\Payment::class),
            'Finance/Bills'                      => fn () => new Exports\BillsExport($from, $to, $billFilters, null),
            'Finance/Requisitions'               => fn () => new Exports\RequisitionExport($from, $to, 'created_at', null),
            'Finance/Expenses'                   => fn () => new Exports\ExpensesExport(),
            'Finance/Debtors'                    => fn () => new Exports\DebtorsExport(),
            'Finance/Creditors'                  => fn () => new Exports\CreditorsExport(),
            'Finance/Customers Age Analysis'     => fn () => new Exports\CustomersAgeExport(),
            'Finance/Vendors Age Analysis'       => fn () => new Exports\VendorsAgeExport(),
            'Finance/Cash Flows'                 => $this->table(Models\CashFlow::class),
            'Finance/Rates'                      => $this->table(Models\Rate::class),

            // Contacts
            'Contacts/Customers'                 => fn () => new Exports\CustomersExport($from, $to),
            'Contacts/Vendors'                   => fn () => new Exports\VendorsExport(),
            'Contacts/Transporters'              => fn () => new Exports\TransportersExport(),
            'Contacts/Brokers'                   => fn () => new Exports\BrokersExport(),
            'Contacts/Agents'                    => fn () => new Exports\AgentsExport(),
            'Contacts/Consignees'                => fn () => new Exports\ConsigneesExport(),

            // Fleet
            'Fleet/Horses'                       => fn () => new Exports\HorsesExport(),
            'Fleet/Horses Report'                => fn () => new Exports\HorsesReportExport(null, $from, $to),
            'Fleet/Horses Performance'           => fn () => new Exports\HorsesPerformanceExport($from, $to, 'start_date'),
            'Fleet/Horses Mileage'               => fn () => new Exports\HorsesMileageExport(),
            'Fleet/Horses Age'                   => fn () => new Exports\HorsesAgeExport(),
            'Fleet/Trailers'                     => fn () => new Exports\TrailersExport(),
            'Fleet/Trailers Mileage'             => fn () => new Exports\TrailersMileageExport(),
            'Fleet/Trailers Age'                 => fn () => new Exports\TrailersAgeExport(),
            'Fleet/Vehicles'                     => fn () => new Exports\VehiclesExport(),
            'Fleet/Vehicles Mileage'             => fn () => new Exports\VehiclesMileageExport(),
            'Fleet/Vehicles Age'                 => fn () => new Exports\VehiclesAgeExport(),
            'Fleet/Drivers'                      => fn () => new Exports\DriversExport(),
            'Fleet/Drivers Performance'          => fn () => new Exports\DriversPerformanceExport($from, $to, 'start_date'),
            'Fleet/Drivers Age'                  => fn () => new Exports\DriversAgeExport(),
            'Fleet/Tyres'                        => fn () => new Exports\TyresExport(),
            'Fleet/Tyre Assignments'             => fn () => new Exports\TyreAssignmentsExport(),
            'Fleet/Reminders & Fitness'          => $this->table(Models\Fitness::class),

            // Workshop
            'Workshop/Bookings'                  => fn () => new Exports\BookingsExport('all', null, $from, $to),
            'Workshop/Tickets'                   => fn () => new Exports\TicketsExport(null, null, null, $from, $to),
            'Workshop/Workshop Services'         => fn () => new Exports\WorkshopServicesExport(),

            // Inventory & Procurement
            'Inventory/Purchase Orders'          => fn () => new Exports\PurchasesExport($from, $to, 'created_at', 'asset', null),
            'Inventory/Dispatches'               => fn () => new Exports\DispatchesExport(),
            'Inventory/Inventory Products'       => fn () => new Exports\ProductsExport('inventory'),
            'Inventory/Asset Products'           => fn () => new Exports\ProductsExport('asset'),
            'Inventory/Tyre Products'            => fn () => new Exports\ProductsExport('tyre'),
            'Inventory/Inventory Stock Valuation'=> fn () => new Exports\StockValuationExport('inventory'),
            'Inventory/Asset Stock Valuation'    => fn () => new Exports\StockValuationExport('asset'),
            'Inventory/Tyre Stock Valuation'     => fn () => new Exports\StockValuationExport('tyre'),
            'Inventory/Containers'               => fn () => new Exports\ContainersExport(),
            'Inventory/Bins'                     => fn () => new Exports\BinsExport(),
            'Inventory/Racks'                    => fn () => new Exports\RacksExport(),

            // HR
            'HR/Employees'                       => fn () => new Exports\EmployeesExport(),
            'HR/Employees Age'                   => fn () => new Exports\EmployeesAgeExport(),
            'HR/Leaves'                          => fn () => new Exports\LeavesExport($from, $to, 'all', null),
            'HR/Attendance Register'             => fn () => new Exports\AttendanceRegisterExport($from, $to, null, null, 'created_at'),
            'HR/Applications'                    => $this->table(Models\Application::class),

            // Master data
            'Master Data/Cargos'                 => fn () => new Exports\CargosExport(),
            'Master Data/Countries'              => fn () => new Exports\CountriesExport(),
            'Master Data/Provinces'              => $this->table(Models\Province::class),
            'Master Data/Destinations'           => fn () => new Exports\DestinationsExport(),
            'Master Data/Loading Points'         => fn () => new Exports\LoadingPointsExport(),
            'Master Data/Offloading Points'      => fn () => new Exports\OffloadingPointsExport(),
            'Master Data/Locations'              => $this->table(Models\Location::class),
            'Master Data/Parkings'               => $this->table(Models\Parking::class),
            'Master Data/Companies'              => $this->table(Models\Company::class),

            // System
            'System/Audit Logs'                  => fn () => new Exports\AuditLogsExport(),
        ]);
    }

    /**
     * Raw-table dump for a model, or null (dropped from the list) when that
     * module's table isn't present in this installation.
     */
    protected function table(string $modelClass): ?\Closure
    {
        if (! Schema::hasTable((new $modelClass)->getTable())) {
            return null;
        }

        return fn () => new ModelBackupExport($modelClass);
    }

    /**
     * Builds the zip. Each export runs independently: one failing export is
     * recorded in the README and skipped, not fatal.
     *
     * @return array{path: string, exported: string[], failed: array<string, string>}
     */
    public function build(): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/gonyeti_backup_'.now()->format('Y-m-d_His').'_'.Str::random(6).'.zip';

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create backup archive.');
        }

        $exported = [];
        $failed = [];

        foreach ($this->exports() as $name => $factory) {
            try {
                $zip->addFromString($name.'.xlsx', Excel::raw($factory(), ExcelWriter::XLSX));
                $exported[] = $name;
            } catch (Throwable $e) {
                $failed[$name] = $e->getMessage();
                Log::warning('Data backup export failed', ['export' => $name, 'error' => $e->getMessage()]);
            }
        }

        $zip->addFromString('README.txt', $this->readme($exported, $failed));
        $zip->close();

        return ['path' => $path, 'exported' => $exported, 'failed' => $failed];
    }

    protected function readme(array $exported, array $failed): string
    {
        $lines = [
            config('app.name').' data backup',
            'Generated: '.now()->toDateTimeString().' by '.optional(auth()->user())->email,
            'Date range: all records (no date filter).',
            '',
            'Exported files ('.count($exported).'):',
        ];
        foreach ($exported as $name) {
            $lines[] = '  - '.$name.'.xlsx';
        }

        if ($failed) {
            $lines[] = '';
            $lines[] = 'Could not be exported ('.count($failed).'):';
            foreach ($failed as $name => $error) {
                $lines[] = '  - '.$name.': '.$error;
            }
        }

        return implode("\r\n", $lines)."\r\n";
    }
}
