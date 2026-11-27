<?php

namespace App\Services\Freight;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeRateTier;
use App\Models\ChargeType;
use App\Models\FreightJob;
use App\Models\FreightJobReminder;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\DemoDataBatch;
use App\Services\DemoData\DemoDataRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Freight Forwarding demo data for a live instance (Freight > Demo Data,
 * is_admin only), loaded into the admin's own company and rolled back when
 * the client is done exploring.
 *
 * Self-contained: it creates its own "DEMO ..." customers, vendors, consignees,
 * clearing agent and broker instead of attaching jobs to real parties, and
 * reuses (or creates) shared lookups - currencies must already exist (USD).
 * Built through the module's own services so milestones, free-time clocks and
 * job totals behave exactly as on screen. It never creates invoices, bills,
 * journals or trips, sends no email, and nothing reaches a linked transport
 * instance (DemoDataRecorder::$suppressExternal).
 */
class FreightDemoDataService
{
    public const MODULE = 'freight';

    /** What one load creates, for the Demo Data page. */
    public const SCENARIOS = [
        ['Sea FCL import at port', 'Container discharged 2 days ago with demurrage / storage clocks running, customs in processing, costs and charges, plus a follow-up reminder.'],
        ['Sea FCL import delivered', 'Container through every stage up to customer delivery, customs cleared, a verified cost and an approved charge ready to bill.'],
        ['Sea LCL export in transit', 'Container loaded on the vessel, export declaration, master shipment of a consolidation.'],
        ['LCL house booking', 'Second client\'s cargo grouped into the consolidation above (cost split by weight).'],
        ['Air export', 'Master air waybill issued, export declaration, air leg awaiting uplift.'],
        ['Road cross-border', 'Two planned road legs and a transit declaration (no trips are created).'],
    ];

    private int $companyId;
    private int $userId;
    private array $ids = [];

    private DemoDataRecorder $recorder;

    public function __construct()
    {
        // Always the freight rules - never a container-built recorder with no config.
        $this->recorder = self::recorder();
    }

    /** Rollback rules for freight demo data. */
    public static function recorder(): DemoDataRecorder
    {
        $parties = ['customers' => 'customer', 'vendors' => 'vendor', 'consignees' => 'consignee', 'clearing_agents' => 'clearing_agent', 'brokers' => 'broker'];

        return new DemoDataRecorder([
            'cascade_prefixes' => ['freight_', 'shipment', 'shipping_container', 'container_', 'consolidation', 'customs_declaration', 'transport_document'],
            'cascade_tables' => ['documents'],
            'master_tables' => [
                'customers', 'vendors', 'consignees', 'clearing_agents', 'brokers', 'destinations', 'cargos',
                'charge_types', 'shipping_lines', 'freight_service_types', 'charge_free_day_policies',
                'charge_rate_tiers', 'customs_offices',
            ],
            'aliases' => [
                'destinations' => ['from', 'to', 'origin_id', 'destination_id'],
                'shipments' => ['master_shipment_id'],
                'vendors' => ['carrier_vendor_id', 'shipping_line_vendor_id'],
            ],
            'polymorphic' => collect($parties)->map(fn ($type) => [['shipment_parties', 'party_id', 'party_type', $type]])->all(),
        ]);
    }

    public static function companyIdFor(User $user): ?int
    {
        return $user->employee?->company_id;
    }

    public function activeBatch(int $companyId): ?DemoDataBatch
    {
        return DemoDataBatch::where('module', self::MODULE)->where('company_id', $companyId)->where('status', 'loaded')->latest('id')->first();
    }

    public function load(User $user): DemoDataBatch
    {
        $companyId = self::companyIdFor($user);
        if (!$companyId) {
            throw new RuntimeException('Your login has no company (employee record) to load demo data into.');
        }
        if ($this->activeBatch($companyId)) {
            throw new RuntimeException('Demo data is already loaded for this company. Roll it back before loading it again.');
        }
        if (!DB::table('currencies')->where('name', 'USD')->exists()) {
            throw new RuntimeException('A USD currency is needed for the demo jobs. Add it under Accounting first.');
        }

        $this->companyId = $companyId;
        $this->userId = $user->id;

        $batch = DemoDataBatch::create([
            'module' => self::MODULE,
            'company_id' => $companyId,
            'user_id' => $user->id,
            'status' => 'loaded',
            'loaded_at' => now(),
        ]);

        try {
            $this->recorder->record($batch, function () {
                $this->masterData();
                $this->jobAtPort();
                $this->jobDelivered();
                $master = $this->jobLclExport();
                $this->houseBookingInConsolidation($master);
                $this->jobAir();
                $this->jobRoad();
            });
        } catch (\Throwable $e) {
            $batch->delete(); // nothing was recorded - the load transaction rolled back
            throw $e;
        }

        $this->flushReminderCache();

        return $batch->fresh();
    }

    public function plan(DemoDataBatch $batch): array
    {
        return $this->recorder->plan($batch);
    }

    public function rollback(DemoDataBatch $batch, User $user): array
    {
        $plan = $this->recorder->rollback($batch, $user->id);
        $this->flushReminderCache();

        return $plan;
    }

    private function flushReminderCache(): void
    {
        if (method_exists(FreightReminderService::class, 'flushCache')) {
            FreightReminderService::flushCache($this->companyId ?? null);
        }
    }

    // ------------------------------------------------------------ master data

    /** New row (fires created events, so it's recorded) with guarded fields allowed. */
    private function make(string $class, array $attributes): Model
    {
        $model = new $class;
        $model->forceFill($attributes);
        $model->save();

        return $model;
    }

    /** Existing row matched on $match (any company), else a new recorded one. */
    private function reuseOrMake(string $class, array $match, array $extra = []): int
    {
        $query = method_exists($class, 'withoutCompanyScope') ? $class::withoutCompanyScope() : $class::query();
        $existing = $query->where($match)->first();

        return $existing ? $existing->id : $this->make($class, $match + $extra)->id;
    }

    private function party(string $class, string $name, array $extra = []): int
    {
        $table = (new $class)->getTable();
        $attributes = ['name' => $name] + $extra;
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id')) {
            $attributes['company_id'] = $this->companyId;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'user_id')) {
            $attributes['user_id'] = $this->userId;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'status')) {
            $attributes['status'] = 1;
        }

        return $this->make($class, $attributes)->id;
    }

    private function masterData(): void
    {
        $usd = (int) DB::table('currencies')->where('name', 'USD')->value('id');
        $this->ids['usd'] = $usd;

        $country = fn (string $name) => DB::table('countries')->where('name', $name)->value('id');
        $this->ids['countries'] = [
            'Zimbabwe' => $country('Zimbabwe'), 'South Africa' => $country('South Africa'),
            'Mozambique' => $country('Mozambique'), 'Zambia' => $country('Zambia'),
        ];

        foreach ([
            'Durban' => 'South Africa', 'Beira' => 'Mozambique', 'Harare' => 'Zimbabwe',
            'Bulawayo' => 'Zimbabwe', 'Lusaka' => 'Zambia', 'Johannesburg' => 'South Africa',
        ] as $city => $countryName) {
            $this->ids['dest'][$city] = $this->reuseOrMake(\App\Models\Destination::class, ['city' => $city], [
                'country_id' => $this->ids['countries'][$countryName] ?? null, 'user_id' => $this->userId,
            ]);
        }

        foreach (['Bulk Maize', 'Bulk Wheat', 'Sorghum', 'Granite', 'Bagged Barley'] as $cargo) {
            $this->ids['cargo'][$cargo] = $this->reuseOrMake(\App\Models\Cargo::class, ['name' => $cargo], ['user_id' => $this->userId, 'status' => 1]);
        }

        foreach ([
            'Ocean Freight', 'Air Freight', 'Road Freight', 'Terminal Handling Charge',
            'Documentation Fee', 'Customs Clearance Fee',
        ] as $name) {
            $this->ids['charge'][$name] = $this->reuseOrMake(ChargeType::class, ['name' => $name]);
        }

        foreach (['Maersk Line', 'MSC Mediterranean Shipping', 'CMA CGM'] as $name) {
            $this->ids['line'][$name] = $this->reuseOrMake(ShippingLine::class, ['name' => $name], ['is_active' => true]);
        }

        foreach ([
            'fcl' => 'Freight Forwarding & Customs Clearing', 'lcl' => 'Consolidation (LCL/Groupage)',
            'air' => 'Freight Forwarding', 'road' => 'Cross-Border Transport',
        ] as $key => $name) {
            $this->ids['service'][$key] = $this->reuseOrMake(\App\Models\FreightServiceType::class, ['name' => $name], ['is_active' => true, 'user_id' => $this->userId]);
        }

        if (!ChargeFreeDayPolicy::where('charge_type', 'port_storage')->whereNull('shipping_line_id')->exists()) {
            $this->make(ChargeFreeDayPolicy::class, ['charge_type' => 'port_storage', 'shipping_line_id' => null, 'free_days' => 7]);
        }
        if (!ChargeRateTier::where('charge_type', 'port_storage')->whereNull('shipping_line_id')->exists()) {
            $this->make(ChargeRateTier::class, ['charge_type' => 'port_storage', 'shipping_line_id' => null, 'day_from' => 1, 'day_to' => null, 'rate' => 25, 'currency_id' => $usd]);
        }

        foreach (['Beitbridge', 'Chirundu', 'Robert Gabriel Mugabe International Airport'] as $office) {
            if (\Illuminate\Support\Facades\Schema::hasTable('customs_offices')) {
                $this->reuseOrMake(\App\Models\CustomsOffice::class, ['name' => $office], ['is_active' => true, 'country_id' => $this->ids['countries']['Zimbabwe'] ?? null]);
            }
        }

        $this->ids['customer'] = [
            'grain' => $this->party(\App\Models\Customer::class, 'DEMO Harare Grain Traders', ['email' => 'grain.demo@example.com', 'city' => 'Harare', 'country' => 'Zimbabwe']),
            'motors' => $this->party(\App\Models\Customer::class, 'DEMO Mutare Motors', ['email' => 'motors.demo@example.com', 'city' => 'Mutare', 'country' => 'Zimbabwe']),
            'agro' => $this->party(\App\Models\Customer::class, 'DEMO Bulawayo Agro Supplies', ['email' => 'agro.demo@example.com', 'city' => 'Bulawayo', 'country' => 'Zimbabwe']),
        ];
        $this->ids['vendor'] = [
            'ocean' => $this->party(\App\Models\Vendor::class, 'DEMO Ocean Carriers Agency', ['email' => 'ocean.demo@example.com']),
            'terminal' => $this->party(\App\Models\Vendor::class, 'DEMO Beira Terminal Services', ['email' => 'terminal.demo@example.com']),
            'customs' => $this->party(\App\Models\Vendor::class, 'DEMO Border Customs Services', ['email' => 'customs.demo@example.com']),
        ];
        $this->ids['consignee'] = [
            $this->party(\App\Models\Consignee::class, 'DEMO Consignee - Lusaka Mills', ['city' => 'Lusaka', 'country' => 'Zambia']),
            $this->party(\App\Models\Consignee::class, 'DEMO Consignee - Joburg Distributors', ['city' => 'Johannesburg', 'country' => 'South Africa']),
            $this->party(\App\Models\Consignee::class, 'DEMO Consignee - Beira Exporters', ['city' => 'Beira', 'country' => 'Mozambique']),
        ];
        $this->ids['clearing'] = $this->party(\App\Models\ClearingAgent::class, 'DEMO Clearing Agents (Beitbridge)', ['city' => 'Beitbridge', 'country' => 'Zimbabwe']);
        $this->ids['broker'] = $this->party(\App\Models\Broker::class, 'DEMO Freight Brokers');
    }

    // ---------------------------------------------------------------- jobs

    private function job(array $job, array $shipment, array $cargo, array $parties = []): FreightJob
    {
        return app(FreightJobService::class)->create($job + [
            'user_id' => $this->userId,
            'company_id' => $this->companyId,
            'currency_id' => $this->ids['usd'],
            'authorization' => 'approved',
            'authorized_by_id' => $this->userId,
            'authorization_date' => now(),
        ], $shipment + ['user_id' => $this->userId], $cargo, $parties);
    }

    private function cost(FreightJob $job, string $vendor, string $charge, float $amount, array $extra = [])
    {
        return app(FreightCostService::class)->create($extra + [
            'freight_job_id' => $job->id, 'shipment_id' => $job->shipments()->value('id'),
            'vendor_id' => $this->ids['vendor'][$vendor], 'charge_type_id' => $this->ids['charge'][$charge],
            'currency_id' => $this->ids['usd'], 'amount' => $amount, 'exchange_rate' => 1,
        ]);
    }

    private function charge(FreightJob $job, string $customer, string $charge, float $amount)
    {
        return app(FreightChargeService::class)->create([
            'freight_job_id' => $job->id, 'shipment_id' => $job->shipments()->value('id'),
            'customer_id' => $this->ids['customer'][$customer], 'charge_type_id' => $this->ids['charge'][$charge],
            'currency_id' => $this->ids['usd'], 'amount' => $amount,
        ]);
    }

    private function container(int $shipmentId, string $number, string $type, string $line, float $weight, array $stages)
    {
        $service = app(ShippingContainerService::class);
        $container = $service->create([
            'shipment_id' => $shipmentId, 'container_number' => $number, 'container_type' => $type,
            'shipping_line_id' => $this->ids['line'][$line], 'gross_weight' => $weight,
        ]);
        foreach ($stages as $stage => $daysAgo) {
            $service->transitionStatus($container, $stage, ['actual_at' => now()->subDays($daysAgo)]);
        }

        return $container;
    }

    private function declaration(int $shipmentId, string $type, string $office, int $daysAgo, array $lines = [], array $stages = [])
    {
        $service = app(CustomsDeclarationService::class);
        $declaration = $service->create([
            'shipment_id' => $shipmentId, 'clearing_agent_id' => $this->ids['clearing'],
            'country_id' => $this->ids['countries']['Zimbabwe'], 'currency_id' => $this->ids['usd'],
            'customs_office' => $office, 'declaration_type' => $type, 'declaration_date' => now()->subDays($daysAgo),
        ], $lines);
        foreach ($stages as $stage) {
            $service->transitionStatus($declaration, $stage);
        }

        return $declaration;
    }

    private function document(int $shipmentId, string $type, string $number, int $daysAgo, string $status): void
    {
        \App\Models\TransportDocument::create([
            'shipment_id' => $shipmentId, 'document_type' => $type, 'document_number' => $number,
            'issue_date' => now()->subDays($daysAgo), 'number_of_originals' => 3, 'status' => $status,
        ]);
    }

    private function jobAtPort(): void
    {
        $job = $this->job([
            'customer_id' => $this->ids['customer']['grain'], 'customer_reference' => 'DEMO-PO-10234',
            'freight_service_type_id' => $this->ids['service']['fcl'], 'import_export_type' => 'import', 'shipment_type' => 'FCL',
            'primary_transport_mode' => 'sea', 'from' => $this->ids['dest']['Durban'], 'to' => $this->ids['dest']['Harare'],
            'incoterm' => 'CIF', 'status' => 'customs_clearance',
            'notes' => 'DEMO: bulk maize import, container at port with demurrage / storage free time running out.',
        ], [
            'mode' => 'sea', 'shipment_type' => 'FCL', 'etd' => now()->subDays(18), 'eta' => now()->subDays(2),
            'actual_departure' => now()->subDays(18), 'booking_reference' => 'BKG-DEMO-A1', 'freight_terms' => 'prepaid',
            'incoterm' => 'CIF', 'status' => 'in_progress',
        ], [
            ['cargo_id' => $this->ids['cargo']['Bulk Maize'], 'commodity' => 'Bulk Maize', 'quantity' => 25, 'uom' => 'MT', 'gross_weight' => 25000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->ids['consignee'][0], 'role' => 'Consignee'],
            ['party_type' => 'clearing_agent', 'party_id' => $this->ids['clearing'], 'role' => 'Clearing Agent'],
        ]);
        $shipmentId = $job->shipments()->value('id');

        $this->document($shipmentId, 'master_bill_of_lading', 'MBL-DEMO-A1', 19, 'issued');
        $this->container($shipmentId, 'MSKU1000001', '40ft', 'Maersk Line', 26500, [
            'empty_released' => 20, 'stuffed' => 19, 'gate_in' => 19, 'vessel_loaded' => 18, 'discharged' => 2,
        ]);
        $this->declaration($shipmentId, 'Import', 'Beitbridge', 2, [
            ['hs_code' => '1005.90', 'description' => 'Bulk Maize', 'country_of_origin_id' => $this->ids['countries']['South Africa'], 'quantity' => 25, 'uom' => 'MT', 'customs_value' => 6250, 'currency_id' => $this->ids['usd'], 'duty_rate' => 5, 'duty_amount' => 312.50, 'vat_rate' => 15, 'vat_amount' => 985.31],
        ], ['documents_complete', 'entry_submitted', 'customs_processing']);

        app(ShipmentLegService::class)->create([
            'shipment_id' => $shipmentId, 'transport_mode' => 'sea', 'carrier_name' => 'Maersk Line',
            'origin_id' => $job->from, 'destination_id' => $job->to,
            'planned_departure' => now()->subDays(18), 'planned_arrival' => now()->subDays(2),
        ]);

        $this->cost($job, 'ocean', 'Ocean Freight', 2400, ['recoverable' => false]);
        $this->cost($job, 'terminal', 'Terminal Handling Charge', 380);
        $this->charge($job, 'grain', 'Ocean Freight', 2950);
        $this->charge($job, 'grain', 'Documentation Fee', 95);

        // Follow-up reminder: shows in the bell, emails nobody (no remind days).
        $this->make(FreightJobReminder::class, [
            'company_id' => $this->companyId, 'freight_job_id' => $job->id, 'user_id' => $this->userId,
            'title' => 'DEMO: import permit ready before release', 'due_at' => now()->addDays(2)->setTime(9, 0),
            'remind_days' => [], 'sent_days' => [], 'staff_user_ids' => [$this->userId],
            'notify_client' => false, 'status' => 'open',
            'notes' => 'Demo follow-up - mark it done or edit it on the job\'s Reminders tab.',
        ]);
    }

    private function jobDelivered(): void
    {
        $job = $this->job([
            'customer_id' => $this->ids['customer']['motors'], 'customer_reference' => 'DEMO-PO-10287',
            'freight_service_type_id' => $this->ids['service']['fcl'], 'import_export_type' => 'import', 'shipment_type' => 'FCL',
            'primary_transport_mode' => 'sea', 'from' => $this->ids['dest']['Beira'], 'to' => $this->ids['dest']['Harare'],
            'incoterm' => 'FOB', 'status' => 'delivered', 'notes' => 'DEMO: cleared and delivered FCL import.',
        ], [
            'mode' => 'sea', 'shipment_type' => 'FCL', 'etd' => now()->subDays(40), 'eta' => now()->subDays(25),
            'actual_departure' => now()->subDays(40), 'actual_arrival' => now()->subDays(25), 'booking_reference' => 'BKG-DEMO-B1',
            'freight_terms' => 'prepaid', 'incoterm' => 'FOB', 'status' => 'delivered',
        ], [
            ['cargo_id' => $this->ids['cargo']['Bulk Wheat'], 'commodity' => 'Bulk Wheat', 'quantity' => 24, 'uom' => 'MT', 'gross_weight' => 24000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->ids['consignee'][1], 'role' => 'Consignee'],
            ['party_type' => 'broker', 'party_id' => $this->ids['broker'], 'role' => 'Freight Broker'],
        ]);
        $shipmentId = $job->shipments()->value('id');

        $stages = [];
        foreach (['empty_released', 'stuffed', 'gate_in', 'vessel_loaded', 'discharged', 'customs_cleared', 'port_released', 'collected_from_port', 'customer_delivery'] as $i => $stage) {
            $stages[$stage] = 42 - $i * 3;
        }
        $this->container($shipmentId, 'MSCU2000002', '40ft', 'MSC Mediterranean Shipping', 25400, $stages);
        $this->declaration($shipmentId, 'Import', 'Beitbridge', 26, [
            ['hs_code' => '1001.99', 'description' => 'Bulk Wheat', 'country_of_origin_id' => $this->ids['countries']['South Africa'], 'quantity' => 24, 'uom' => 'MT', 'customs_value' => 6000, 'currency_id' => $this->ids['usd'], 'duty_rate' => 5, 'duty_amount' => 300, 'vat_rate' => 15, 'vat_amount' => 945],
        ], ['documents_complete', 'entry_submitted', 'customs_processing', 'duties_paid', 'customs_released', 'cleared']);
        $this->document($shipmentId, 'master_bill_of_lading', 'MBL-DEMO-B1', 41, 'surrendered');

        $cost = $this->cost($job, 'ocean', 'Ocean Freight', 2350);
        app(FreightCostService::class)->transitionVerification($cost, 'verified');
        $charge = $this->charge($job, 'motors', 'Ocean Freight', 2890);
        app(FreightChargeService::class)->transitionStatus($charge, 'approved');
    }

    private function jobLclExport(): FreightJob
    {
        $job = $this->job([
            'customer_id' => $this->ids['customer']['agro'], 'customer_reference' => 'DEMO-PO-10311',
            'freight_service_type_id' => $this->ids['service']['lcl'], 'import_export_type' => 'export', 'shipment_type' => 'LCL',
            'primary_transport_mode' => 'sea', 'from' => $this->ids['dest']['Harare'], 'to' => $this->ids['dest']['Durban'],
            'incoterm' => 'FOB', 'status' => 'in_progress', 'notes' => 'DEMO: LCL export, master shipment of a consolidation.',
        ], [
            'mode' => 'sea', 'shipment_type' => 'LCL', 'etd' => now()->subDays(4), 'eta' => now()->addDays(10),
            'actual_departure' => now()->subDays(4), 'booking_reference' => 'BKG-DEMO-C1', 'freight_terms' => 'collect',
            'incoterm' => 'FOB', 'status' => 'in_progress',
        ], [
            ['cargo_id' => $this->ids['cargo']['Sorghum'], 'commodity' => 'Sorghum', 'quantity' => 8, 'uom' => 'MT', 'gross_weight' => 8000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->ids['consignee'][2], 'role' => 'Consignee'],
        ]);
        $shipmentId = $job->shipments()->value('id');

        $this->container($shipmentId, 'CMAU3000003', '20ft', 'CMA CGM', 8900, [
            'empty_released' => 6, 'stuffed' => 5, 'gate_in' => 4, 'vessel_loaded' => 3,
        ]);
        $this->declaration($shipmentId, 'Export', 'Beitbridge', 5, [
            ['hs_code' => '1007.90', 'description' => 'Sorghum', 'country_of_origin_id' => $this->ids['countries']['Zimbabwe'], 'quantity' => 8, 'uom' => 'MT', 'customs_value' => 1600, 'currency_id' => $this->ids['usd']],
        ]);
        $this->charge($job, 'agro', 'Ocean Freight', 1150);

        return $job;
    }

    private function houseBookingInConsolidation(FreightJob $master): void
    {
        $house = $this->job([
            'customer_id' => $this->ids['customer']['grain'], 'customer_reference' => 'DEMO-PO-10312',
            'freight_service_type_id' => $this->ids['service']['lcl'], 'import_export_type' => 'export', 'shipment_type' => 'LCL',
            'primary_transport_mode' => 'sea', 'from' => $this->ids['dest']['Bulawayo'], 'to' => $this->ids['dest']['Durban'],
            'incoterm' => 'FOB', 'status' => 'in_progress', 'notes' => 'DEMO: small LCL booking grouped into the consolidation.',
        ], [
            'mode' => 'sea', 'shipment_type' => 'LCL', 'etd' => now()->subDays(4), 'eta' => now()->addDays(10),
            'actual_departure' => now()->subDays(4), 'booking_reference' => 'BKG-DEMO-C2', 'freight_terms' => 'collect',
            'incoterm' => 'FOB', 'status' => 'in_progress',
        ], [
            ['cargo_id' => $this->ids['cargo']['Sorghum'], 'commodity' => 'Sorghum', 'quantity' => 2, 'uom' => 'MT', 'gross_weight' => 2000],
        ]);

        $consolidations = app(ConsolidationService::class);
        $consolidation = $consolidations->create([
            'master_shipment_id' => $master->shipments()->value('id'),
            'cost_allocation_basis' => 'chargeable_weight',
            'notes' => 'DEMO: two LCL export bookings in one container.',
        ]);
        $consolidations->attachShipment($consolidation, $house->shipments()->value('id'), [
            'allocation_value' => 2000, 'notes' => 'House shipment share of the consolidated container.',
        ]);
    }

    private function jobAir(): void
    {
        $job = $this->job([
            'customer_id' => $this->ids['customer']['motors'], 'customer_reference' => 'DEMO-PO-10355',
            'freight_service_type_id' => $this->ids['service']['air'], 'import_export_type' => 'export',
            'primary_transport_mode' => 'air', 'from' => $this->ids['dest']['Harare'], 'to' => $this->ids['dest']['Johannesburg'],
            'incoterm' => 'EXW', 'status' => 'confirmed', 'notes' => 'DEMO: air export, documents complete, awaiting uplift.',
        ], [
            'mode' => 'air', 'etd' => now()->addDays(2), 'eta' => now()->addDays(3), 'booking_reference' => 'BKG-DEMO-D1',
            'freight_terms' => 'prepaid', 'incoterm' => 'EXW', 'status' => 'confirmed',
        ], [
            ['cargo_id' => $this->ids['cargo']['Granite'], 'commodity' => 'Granite samples', 'quantity' => 450, 'uom' => 'KG', 'gross_weight' => 450],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->ids['consignee'][1], 'role' => 'Consignee'],
        ]);
        $shipmentId = $job->shipments()->value('id');

        $this->document($shipmentId, 'master_air_waybill', 'MAWB-DEMO-D1', 0, 'issued');
        $this->declaration($shipmentId, 'Export', 'Robert Gabriel Mugabe International Airport', 0);
        app(ShipmentLegService::class)->create([
            'shipment_id' => $shipmentId, 'transport_mode' => 'air', 'carrier_name' => 'Demo Air Cargo',
            'origin_id' => $job->from, 'destination_id' => $job->to,
            'planned_departure' => now()->addDays(2), 'planned_arrival' => now()->addDays(3),
        ]);
        $this->charge($job, 'motors', 'Air Freight', 1890);
    }

    private function jobRoad(): void
    {
        $job = $this->job([
            'customer_id' => $this->ids['customer']['agro'], 'customer_reference' => 'DEMO-PO-10401',
            'freight_service_type_id' => $this->ids['service']['road'], 'import_export_type' => 'export', 'shipment_type' => 'FTL',
            'primary_transport_mode' => 'road', 'from' => $this->ids['dest']['Harare'], 'to' => $this->ids['dest']['Lusaka'],
            'incoterm' => 'DAP', 'status' => 'transport_arranged', 'notes' => 'DEMO: road cross-border via Chirundu, legs planned.',
        ], [
            'mode' => 'road', 'shipment_type' => 'FTL', 'etd' => now()->addDay(), 'eta' => now()->addDays(3),
            'booking_reference' => 'BKG-DEMO-E1', 'freight_terms' => 'prepaid', 'incoterm' => 'DAP', 'status' => 'confirmed',
        ], [
            ['cargo_id' => $this->ids['cargo']['Bagged Barley'], 'commodity' => 'Bagged Barley', 'quantity' => 30, 'uom' => 'MT', 'gross_weight' => 30000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->ids['consignee'][0], 'role' => 'Consignee'],
        ]);
        $shipmentId = $job->shipments()->value('id');

        $legs = app(ShipmentLegService::class);
        $legs->create([
            'shipment_id' => $shipmentId, 'transport_mode' => 'road', 'carrier_name' => 'Demo Haulage (line haul via Chirundu)',
            'origin_id' => $this->ids['dest']['Harare'], 'destination_id' => $this->ids['dest']['Lusaka'],
            'planned_departure' => now()->addDay(), 'planned_arrival' => now()->addDays(2),
        ]);
        $legs->create([
            'shipment_id' => $shipmentId, 'transport_mode' => 'road', 'carrier_name' => 'Demo Local Delivery',
            'origin_id' => $this->ids['dest']['Lusaka'], 'destination_id' => $this->ids['dest']['Lusaka'],
            'planned_departure' => now()->addDays(2), 'planned_arrival' => now()->addDays(3),
        ]);
        $this->declaration($shipmentId, 'Transit', 'Chirundu', 0);
        $this->cost($job, 'customs', 'Customs Clearance Fee', 220);
        $this->charge($job, 'agro', 'Road Freight', 3200);
    }
}
