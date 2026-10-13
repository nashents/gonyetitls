<?php

namespace Database\Seeders;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeRateTier;
use App\Models\ChargeType;
use App\Models\Consolidation;
use App\Models\CustomsDeclaration;
use App\Models\FreightJob;
use App\Models\ShippingContainer;
use App\Models\ShippingLine;
use App\Services\Freight\ConsolidationService;
use App\Services\Freight\CustomsDeclarationService;
use App\Services\Freight\FreightChargeService;
use App\Services\Freight\FreightCostService;
use App\Services\Freight\FreightJobService;
use App\Services\Freight\ShipmentLegService;
use App\Services\Freight\ShippingContainerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Walkthrough example data for the Freight Forwarding & Clearing module:
 * one Freight Job per major scenario (sea FCL at port, sea FCL delivered,
 * sea LCL in transit + consolidation, air export, road cross-border with
 * own-fleet dispatch), each built through the module's own services so
 * milestones, port-exposure tracking and job cost/revenue totals come out
 * exactly as they would from the real screens. Safe to re-run: master data
 * uses updateOrCreate / firstOrCreate, and Freight Jobs are only created if
 * none exist yet for this demo (checked via job_number prefix).
 */
class FreightForwardingDemoSeeder extends Seeder
{
    private int $companyId = 2;
    private int $userId = 200;

    private array $currencies = [];
    private array $destinations = [];
    private array $loadingPoints = [];
    private array $offloadingPoints = [];
    private array $clearingAgents = [];
    private array $consignees = [];
    private array $brokers = [];
    private array $cargos = [];
    private array $chargeTypes = [];
    private array $shippingLines = [];
    private array $countries = [];

    public function run()
    {
        if (FreightJob::where('customer_reference', 'like', 'DEMO-%')->exists()) {
            $this->command?->info('Freight demo data already present, skipping.');

            return;
        }

        Auth::loginUsingId($this->userId);

        $this->loadReferenceData();
        $this->seedChargeTypes();
        $this->seedShippingLines();
        $this->seedFreeDayPolicyAndTiers();

        $this->seedSeaImportAtPort();
        $this->seedSeaImportDelivered();
        $jobC = $this->seedSeaExportInTransit();
        $this->seedAirExport();
        $this->seedRoadCrossBorder();
        $this->seedConsolidation($jobC);

        Auth::logout();

        $this->command?->info('Freight Forwarding demo data seeded.');
    }

    private function loadReferenceData(): void
    {
        $this->currencies = \App\Models\Currency::pluck('id', 'name')->toArray();
        $this->destinations = \App\Models\Destination::pluck('id')->take(6)->toArray();
        $this->loadingPoints = \App\Models\LoadingPoint::pluck('id')->take(3)->toArray();
        $this->offloadingPoints = \App\Models\OffloadingPoint::pluck('id')->take(3)->toArray();
        $this->clearingAgents = \App\Models\ClearingAgent::pluck('id')->take(3)->toArray();
        $this->consignees = \App\Models\Consignee::pluck('id')->take(3)->toArray();
        $this->brokers = \App\Models\Broker::pluck('id')->take(2)->toArray();
        $this->cargos = \App\Models\Cargo::pluck('id', 'name')->take(5)->toArray();
        $this->countries = \App\Models\Country::pluck('id', 'name')->take(5)->toArray();
    }

    private function seedChargeTypes(): void
    {
        foreach ([
            'Ocean Freight', 'Air Freight', 'Road Freight',
            'Terminal Handling Charge', 'Documentation Fee',
            'Customs Clearance Fee', 'Port Storage / Demurrage',
        ] as $name) {
            $this->chargeTypes[$name] = ChargeType::firstOrCreate(['name' => $name])->id;
        }
    }

    private function seedShippingLines(): void
    {
        foreach (['Maersk Line', 'MSC Mediterranean Shipping', 'CMA CGM'] as $name) {
            $this->shippingLines[$name] = ShippingLine::firstOrCreate(['name' => $name], ['is_active' => true])->id;
        }
    }

    private function seedFreeDayPolicyAndTiers(): void
    {
        ChargeFreeDayPolicy::firstOrCreate(
            ['charge_type' => 'port_storage', 'shipping_line_id' => null],
            ['free_days' => 7]
        );

        ChargeRateTier::firstOrCreate(
            ['charge_type' => 'port_storage', 'shipping_line_id' => null, 'day_from' => 1],
            ['day_to' => null, 'rate' => 25, 'currency_id' => $this->currencies['USD']]
        );
    }

    /**
     * Job A — Sea FCL import, container currently AT PORT accruing demurrage.
     */
    private function seedSeaImportAtPort(): FreightJob
    {
        $jobService = app(FreightJobService::class);

        $job = $jobService->create([
            'user_id' => $this->userId,
            'customer_id' => 1,
            'customer_reference' => 'DEMO-PO-10234',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 3,
            'import_export_type' => 'import',
            'shipment_type' => 'FCL',
            'primary_transport_mode' => 'sea',
            'from' => $this->destinations[0],
            'to' => $this->destinations[2],
            'incoterm' => 'CIF',
            'currency_id' => $this->currencies['USD'],
            'status' => 'port_storage',
            'notes' => 'Demo data: bulk maize import, container currently at port accruing demurrage.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'sea',
            'shipment_type' => 'FCL',
            'loading_point_id' => $this->loadingPoints[0] ?? null,
            'offloading_point_id' => $this->offloadingPoints[0] ?? null,
            'etd' => now()->subDays(18),
            'eta' => now()->subDays(2),
            'actual_departure' => now()->subDays(18),
            'booking_reference' => 'BKG-DEMO-A1',
            'freight_terms' => 'prepaid',
            'incoterm' => 'CIF',
            'status' => 'port_storage',
        ], [
            ['cargo_id' => $this->cargos['Bulk Maize'] ?? null, 'commodity' => 'Bulk Maize', 'quantity' => 25, 'uom' => 'MT', 'gross_weight' => 25000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->consignees[0], 'role' => 'Consignee'],
            ['party_type' => 'clearing_agent', 'party_id' => $this->clearingAgents[0], 'role' => 'Clearing Agent'],
        ]);

        $shipment = $job->shipments()->first();

        \App\Models\TransportDocument::create([
            'shipment_id' => $shipment->id,
            'document_type' => 'master_bill_of_lading',
            'document_number' => 'MBL-DEMO-A1',
            'issue_date' => now()->subDays(19),
            'number_of_originals' => 3,
            'status' => 'issued',
        ]);

        $containerService = app(ShippingContainerService::class);
        $container = $containerService->create([
            'shipment_id' => $shipment->id,
            'container_number' => 'MSKU1000001',
            'container_type' => '40ft',
            'shipping_line_id' => $this->shippingLines['Maersk Line'],
            'gross_weight' => 26500,
        ]);
        foreach (['empty_released', 'stuffed', 'gate_in', 'vessel_loaded', 'discharged'] as $stage) {
            $containerService->transitionStatus($container, $stage, ['actual_at' => now()->subDays(match ($stage) {
                'empty_released' => 20, 'stuffed' => 19, 'gate_in' => 19, 'vessel_loaded' => 18, 'discharged' => 2,
            })]);
        }

        $declarationService = app(CustomsDeclarationService::class);
        $declaration = $declarationService->create([
            'shipment_id' => $shipment->id,
            'clearing_agent_id' => $this->clearingAgents[0],
            'country_id' => $this->countries['Zimbabwe'] ?? null,
            'currency_id' => $this->currencies['USD'],
            'customs_office' => 'Beitbridge',
            'declaration_type' => 'Import',
            'declaration_date' => now()->subDays(2),
        ], [
            ['hs_code' => '1005.90', 'description' => 'Bulk Maize', 'country_of_origin_id' => $this->countries['South Africa'] ?? null, 'quantity' => 25, 'uom' => 'MT', 'customs_value' => 6250, 'currency_id' => $this->currencies['USD'], 'duty_rate' => 5, 'duty_amount' => 312.50, 'vat_rate' => 15, 'vat_amount' => 985.31],
        ]);
        $declarationService->transitionStatus($declaration, 'documents_complete');
        $declarationService->transitionStatus($declaration, 'entry_submitted');
        $declarationService->transitionStatus($declaration, 'customs_processing');

        app(ShipmentLegService::class)->create([
            'shipment_id' => $shipment->id,
            'transport_mode' => 'sea',
            'carrier_vendor_id' => null,
            'carrier_name' => 'Maersk Line',
            'origin_location_id' => null,
            'destination_location_id' => null,
            'planned_departure' => now()->subDays(18),
            'planned_arrival' => now()->subDays(2),
        ]);

        $costService = app(FreightCostService::class);
        $costService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'vendor_id' => 1, 'charge_type_id' => $this->chargeTypes['Ocean Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 2400, 'exchange_rate' => 1, 'recoverable' => false]);
        $costService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'vendor_id' => 2, 'charge_type_id' => $this->chargeTypes['Terminal Handling Charge'], 'currency_id' => $this->currencies['USD'], 'amount' => 380, 'exchange_rate' => 1]);

        $chargeService = app(FreightChargeService::class);
        $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 1, 'charge_type_id' => $this->chargeTypes['Ocean Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 2950]);
        $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 1, 'charge_type_id' => $this->chargeTypes['Documentation Fee'], 'currency_id' => $this->currencies['USD'], 'amount' => 95]);

        return $job;
    }

    /**
     * Job B — Sea FCL import, fully cleared and delivered (container OUT OF PORT).
     */
    private function seedSeaImportDelivered(): FreightJob
    {
        $jobService = app(FreightJobService::class);

        $job = $jobService->create([
            'user_id' => $this->userId,
            'customer_id' => 2,
            'customer_reference' => 'DEMO-PO-10287',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 3,
            'import_export_type' => 'import',
            'shipment_type' => 'FCL',
            'primary_transport_mode' => 'sea',
            'from' => $this->destinations[0],
            'to' => $this->destinations[3] ?? $this->destinations[0],
            'incoterm' => 'FOB',
            'currency_id' => $this->currencies['USD'],
            'status' => 'delivered',
            'notes' => 'Demo data: cleared and delivered FCL import.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'sea',
            'shipment_type' => 'FCL',
            'loading_point_id' => $this->loadingPoints[1] ?? null,
            'offloading_point_id' => $this->offloadingPoints[1] ?? null,
            'etd' => now()->subDays(40),
            'eta' => now()->subDays(25),
            'actual_departure' => now()->subDays(40),
            'actual_arrival' => now()->subDays(25),
            'booking_reference' => 'BKG-DEMO-B1',
            'freight_terms' => 'prepaid',
            'incoterm' => 'FOB',
            'status' => 'delivered',
        ], [
            ['cargo_id' => $this->cargos['Bulk Wheat'] ?? null, 'commodity' => 'Bulk Wheat', 'quantity' => 24, 'uom' => 'MT', 'gross_weight' => 24000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->consignees[1] ?? $this->consignees[0], 'role' => 'Consignee'],
            ['party_type' => 'broker', 'party_id' => $this->brokers[0], 'role' => 'Freight Broker'],
        ]);

        $shipment = $job->shipments()->first();

        $containerService = app(ShippingContainerService::class);
        $container = $containerService->create([
            'shipment_id' => $shipment->id,
            'container_number' => 'MSCU2000002',
            'container_type' => '40ft',
            'shipping_line_id' => $this->shippingLines['MSC Mediterranean Shipping'],
            'gross_weight' => 25400,
        ]);
        foreach (['empty_released', 'stuffed', 'gate_in', 'vessel_loaded', 'discharged', 'customs_cleared', 'port_released', 'collected_from_port', 'customer_delivery'] as $i => $stage) {
            $containerService->transitionStatus($container, $stage, ['actual_at' => now()->subDays(42 - $i * 3)]);
        }

        $declarationService = app(CustomsDeclarationService::class);
        $declaration = $declarationService->create([
            'shipment_id' => $shipment->id,
            'clearing_agent_id' => $this->clearingAgents[1] ?? $this->clearingAgents[0],
            'country_id' => $this->countries['Zimbabwe'] ?? null,
            'currency_id' => $this->currencies['USD'],
            'customs_office' => 'Beitbridge',
            'declaration_type' => 'Import',
            'declaration_date' => now()->subDays(26),
        ], [
            ['hs_code' => '1001.99', 'description' => 'Bulk Wheat', 'country_of_origin_id' => $this->countries['South Africa'] ?? null, 'quantity' => 24, 'uom' => 'MT', 'customs_value' => 6000, 'currency_id' => $this->currencies['USD'], 'duty_rate' => 5, 'duty_amount' => 300, 'vat_rate' => 15, 'vat_amount' => 945],
        ]);
        foreach (['documents_complete', 'entry_submitted', 'customs_processing', 'duties_paid', 'customs_released', 'cleared'] as $stage) {
            $declarationService->transitionStatus($declaration, $stage);
        }

        \App\Models\TransportDocument::create([
            'shipment_id' => $shipment->id,
            'document_type' => 'master_bill_of_lading',
            'document_number' => 'MBL-DEMO-B1',
            'issue_date' => now()->subDays(41),
            'number_of_originals' => 3,
            'status' => 'surrendered',
        ]);

        $costService = app(FreightCostService::class);
        $cost = $costService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'vendor_id' => 1, 'charge_type_id' => $this->chargeTypes['Ocean Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 2350, 'exchange_rate' => 1]);
        $costService->transitionVerification($cost, 'verified');

        $chargeService = app(FreightChargeService::class);
        $charge = $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 2, 'charge_type_id' => $this->chargeTypes['Ocean Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 2890]);
        $chargeService->transitionStatus($charge, 'approved');

        return $job;
    }

    /**
     * Job C — Sea LCL export, container still loaded on vessel (not yet at
     * port — lands in the "Arriving" dashboard bucket). Also the master
     * shipment for the Consolidation example.
     */
    private function seedSeaExportInTransit(): FreightJob
    {
        $jobService = app(FreightJobService::class);

        $job = $jobService->create([
            'user_id' => $this->userId,
            'customer_id' => 3,
            'customer_reference' => 'DEMO-PO-10311',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 6,
            'import_export_type' => 'export',
            'shipment_type' => 'LCL',
            'primary_transport_mode' => 'sea',
            'from' => $this->destinations[4] ?? $this->destinations[0],
            'to' => $this->destinations[0],
            'incoterm' => 'FOB',
            'currency_id' => $this->currencies['USD'],
            'status' => 'in_progress',
            'notes' => 'Demo data: LCL export, consolidated under a master shipment.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'sea',
            'shipment_type' => 'LCL',
            'loading_point_id' => $this->loadingPoints[2] ?? null,
            'etd' => now()->subDays(4),
            'eta' => now()->addDays(10),
            'actual_departure' => now()->subDays(4),
            'booking_reference' => 'BKG-DEMO-C1',
            'freight_terms' => 'collect',
            'incoterm' => 'FOB',
            'status' => 'in_progress',
        ], [
            ['cargo_id' => $this->cargos['Sorghum'] ?? null, 'commodity' => 'Sorghum', 'quantity' => 8, 'uom' => 'MT', 'gross_weight' => 8000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->consignees[2] ?? $this->consignees[0], 'role' => 'Consignee'],
        ]);

        $shipment = $job->shipments()->first();

        $containerService = app(ShippingContainerService::class);
        $container = $containerService->create([
            'shipment_id' => $shipment->id,
            'container_number' => 'CMAU3000003',
            'container_type' => '20ft',
            'shipping_line_id' => $this->shippingLines['CMA CGM'],
            'gross_weight' => 8900,
        ]);
        foreach (['empty_released', 'stuffed', 'gate_in', 'vessel_loaded'] as $i => $stage) {
            $containerService->transitionStatus($container, $stage, ['actual_at' => now()->subDays(6 - $i)]);
        }

        $declarationService = app(CustomsDeclarationService::class);
        $declarationService->create([
            'shipment_id' => $shipment->id,
            'clearing_agent_id' => $this->clearingAgents[0],
            'country_id' => $this->countries['Zimbabwe'] ?? null,
            'currency_id' => $this->currencies['USD'],
            'customs_office' => 'Beitbridge',
            'declaration_type' => 'Export',
            'declaration_date' => now()->subDays(5),
        ], [
            ['hs_code' => '1007.90', 'description' => 'Sorghum', 'country_of_origin_id' => $this->countries['Zimbabwe'] ?? null, 'quantity' => 8, 'uom' => 'MT', 'customs_value' => 1600, 'currency_id' => $this->currencies['USD']],
        ]);

        $chargeService = app(FreightChargeService::class);
        $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 3, 'charge_type_id' => $this->chargeTypes['Ocean Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 1150]);

        return $job;
    }

    /**
     * Job D — Air export, non-containerized.
     */
    private function seedAirExport(): FreightJob
    {
        $jobService = app(FreightJobService::class);

        $job = $jobService->create([
            'user_id' => $this->userId,
            'customer_id' => 1,
            'customer_reference' => 'DEMO-PO-10355',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 1,
            'import_export_type' => 'export',
            'primary_transport_mode' => 'air',
            'from' => $this->destinations[1] ?? $this->destinations[0],
            'to' => $this->destinations[0],
            'incoterm' => 'EXW',
            'currency_id' => $this->currencies['USD'],
            'status' => 'confirmed',
            'notes' => 'Demo data: air export, documents complete, awaiting uplift.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'air',
            'etd' => now()->addDays(2),
            'eta' => now()->addDays(3),
            'booking_reference' => 'BKG-DEMO-D1',
            'freight_terms' => 'prepaid',
            'incoterm' => 'EXW',
            'status' => 'confirmed',
        ], [
            ['cargo_id' => $this->cargos['Granite'] ?? null, 'commodity' => 'Granite samples', 'quantity' => 450, 'uom' => 'KG', 'gross_weight' => 450],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->consignees[0], 'role' => 'Consignee'],
        ]);

        $shipment = $job->shipments()->first();

        \App\Models\TransportDocument::create([
            'shipment_id' => $shipment->id,
            'document_type' => 'master_air_waybill',
            'document_number' => 'MAWB-DEMO-D1',
            'issue_date' => now(),
            'number_of_originals' => 3,
            'status' => 'issued',
        ]);

        app(CustomsDeclarationService::class)->create([
            'shipment_id' => $shipment->id,
            'clearing_agent_id' => $this->clearingAgents[0],
            'country_id' => $this->countries['Zimbabwe'] ?? null,
            'currency_id' => $this->currencies['USD'],
            'customs_office' => 'Robert Mugabe International',
            'declaration_type' => 'Export',
            'declaration_date' => now(),
        ]);

        app(ShipmentLegService::class)->create([
            'shipment_id' => $shipment->id,
            'transport_mode' => 'air',
            'carrier_name' => 'Ethiopian Airlines Cargo',
            'planned_departure' => now()->addDays(2),
            'planned_arrival' => now()->addDays(3),
        ]);

        $chargeService = app(FreightChargeService::class);
        $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 1, 'charge_type_id' => $this->chargeTypes['Air Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 1890]);

        return $job;
    }

    /**
     * Job E — Road cross-border, one leg dispatched via own fleet (creates a
     * real Trip), one border-crossing leg still planned.
     */
    private function seedRoadCrossBorder(): FreightJob
    {
        $jobService = app(FreightJobService::class);

        $job = $jobService->create([
            'user_id' => $this->userId,
            'customer_id' => 2,
            'customer_reference' => 'DEMO-PO-10402',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 9,
            'import_export_type' => 'cross_trade',
            'primary_transport_mode' => 'road',
            'from' => $this->destinations[0],
            'to' => $this->destinations[2],
            'incoterm' => 'DAP',
            'currency_id' => $this->currencies['USD'],
            'status' => 'transport_arranged',
            'notes' => 'Demo data: own-fleet road cross-border, first leg dispatched.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'road',
            'loading_point_id' => $this->loadingPoints[0] ?? null,
            'offloading_point_id' => $this->offloadingPoints[2] ?? null,
            'etd' => now()->subDay(),
            'eta' => now()->addDays(2),
            'actual_departure' => now()->subDay(),
            'booking_reference' => 'BKG-DEMO-E1',
            'freight_terms' => 'prepaid',
            'incoterm' => 'DAP',
            'status' => 'transport_arranged',
        ], [
            ['cargo_id' => $this->cargos['Barley'] ?? null, 'commodity' => 'Bagged Barley', 'quantity' => 30, 'uom' => 'MT', 'gross_weight' => 30000],
        ], [
            ['party_type' => 'consignee', 'party_id' => $this->consignees[1] ?? $this->consignees[0], 'role' => 'Consignee'],
            ['party_type' => 'transporter', 'party_id' => 2, 'role' => 'Own Fleet'],
        ]);

        $shipment = $job->shipments()->first();

        $legService = app(ShipmentLegService::class);

        $leg1 = $legService->create([
            'shipment_id' => $shipment->id,
            'transport_mode' => 'road',
            'carrier_name' => 'Fahrenheit (Own Fleet)',
            'planned_departure' => now()->subDay(),
            'planned_arrival' => now()->addDay(),
        ]);

        $horse = \App\Models\Horse::where('transporter_id', 2)->where('status', 1)->where('service', 0)->where('archive', 0)->first();
        $driver = \App\Models\Driver::where('transporter_id', 2)->where('archive', 0)->first();

        if ($horse && $driver) {
            $legService->dispatchViaOwnFleet($leg1, [
                'company_id' => $this->companyId,
                'customer_id' => 2,
                'currency_id' => $this->currencies['USD'],
                'transporter_id' => 2,
                'horse_id' => $horse->id,
                'driver_id' => $driver->id,
                'from' => $shipment->loading_point_id,
                'to' => $shipment->offloading_point_id,
                'cargo_details' => 'Bagged Barley - 30MT',
                'start_date' => now()->subDay()->toDateString(),
                'trip_status' => 'InTransit',
            ]);
            $legService->transitionStatus($leg1, 'in_progress');
        }

        $legService->create([
            'shipment_id' => $shipment->id,
            'transport_mode' => 'road',
            'carrier_name' => 'Fahrenheit (Own Fleet)',
            'planned_departure' => now()->addDay(),
            'planned_arrival' => now()->addDays(2),
        ]);

        app(CustomsDeclarationService::class)->create([
            'shipment_id' => $shipment->id,
            'clearing_agent_id' => $this->clearingAgents[0],
            'country_id' => $this->countries['Zimbabwe'] ?? null,
            'currency_id' => $this->currencies['USD'],
            'customs_office' => 'Chirundu',
            'declaration_type' => 'Transit',
            'declaration_date' => now()->subDay(),
        ]);

        $costService = app(FreightCostService::class);
        $costService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'charge_type_id' => $this->chargeTypes['Customs Clearance Fee'], 'currency_id' => $this->currencies['USD'], 'amount' => 220]);

        $chargeService = app(FreightChargeService::class);
        $chargeService->create(['freight_job_id' => $job->id, 'shipment_id' => $shipment->id, 'customer_id' => 2, 'charge_type_id' => $this->chargeTypes['Road Freight'], 'currency_id' => $this->currencies['USD'], 'amount' => 3200]);

        return $job;
    }

    /**
     * Groups the Job C LCL export shipment as the consolidation's master
     * shipment, with a second small LCL export booking (different customer,
     * same direction/lane) attached as its house shipment.
     */
    private function seedConsolidation(FreightJob $masterJob): void
    {
        $masterShipment = $masterJob->shipments()->first();

        $houseJob = app(FreightJobService::class)->create([
            'user_id' => $this->userId,
            'customer_id' => 3,
            'customer_reference' => 'DEMO-PO-10312',
            'company_id' => $this->companyId,
            'freight_service_type_id' => 6,
            'import_export_type' => 'export',
            'shipment_type' => 'LCL',
            'primary_transport_mode' => 'sea',
            'from' => $this->destinations[4] ?? $this->destinations[0],
            'to' => $this->destinations[0],
            'incoterm' => 'FOB',
            'currency_id' => $this->currencies['USD'],
            'status' => 'in_progress',
            'notes' => 'Demo data: small LCL export booking, house shipment in the Job C consolidation.',
        ], [
            'user_id' => $this->userId,
            'mode' => 'sea',
            'shipment_type' => 'LCL',
            'etd' => now()->subDays(4),
            'eta' => now()->addDays(10),
            'actual_departure' => now()->subDays(4),
            'booking_reference' => 'BKG-DEMO-C2',
            'freight_terms' => 'collect',
            'incoterm' => 'FOB',
            'status' => 'in_progress',
        ], [
            ['cargo_id' => $this->cargos['Sorghum'] ?? null, 'commodity' => 'Sorghum', 'quantity' => 2, 'uom' => 'MT', 'gross_weight' => 2000],
        ]);

        $houseShipment = $houseJob->shipments()->first();

        $consolidation = app(ConsolidationService::class)->create([
            'master_shipment_id' => $masterShipment->id,
            'cost_allocation_basis' => 'chargeable_weight',
            'notes' => 'Demo data: groups two LCL export bookings into one consolidated container.',
        ]);

        app(ConsolidationService::class)->attachShipment($consolidation, $houseShipment->id, [
            'allocation_value' => 2000,
            'notes' => 'House shipment share of the consolidated container.',
        ]);
    }
}
