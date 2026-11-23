<?php

namespace Database\Seeders;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeType;
use App\Models\ContainerChargeExposure;
use App\Models\ShippingLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Starter master data for Freight Forwarding & Clearing: common charge types,
 * major shipping lines and default container free days. Everything is
 * unlocked so companies can edit or delete what they don't use.
 *
 * Gap-filling only: nothing is recreated if a row with the same name exists,
 * including one the user deleted (soft-deleted rows count), so re-running
 * this never undoes a deletion or creates duplicates.
 */
class FreightMasterDataSeeder extends Seeder
{
    public const CHARGE_TYPES = [
        // Main freight
        'Ocean Freight' => 'Sea freight charged by the shipping line, per container or per W/M.',
        'Air Freight' => 'Airline freight charge, usually per chargeable kg.',
        'Road Freight' => 'Truck haulage between port/depot and the customer, or cross-border.',
        'Rail Freight' => 'Rail haulage charge.',
        // Origin / destination port & terminal
        'Terminal Handling Charge (THC)' => 'Port terminal charge for moving the container on/off the vessel.',
        'Port Storage' => 'Terminal storage charged after the free storage days run out.',
        'Demurrage' => 'Shipping line charge for keeping a full container in the port/terminal past its free days.',
        'Detention' => 'Shipping line charge for keeping a container outside the port past its free days.',
        'Container Deposit' => 'Refundable deposit the shipping line holds against the container.',
        'Delivery Order Fee' => 'Shipping line / agent fee to release the cargo (DO).',
        'Bill of Lading Fee' => 'Fee for issuing the bill of lading or air waybill.',
        'Container Cleaning / Repair' => 'Charges raised when the empty container is returned.',
        // Customs & compliance
        'Customs Clearance Fee' => 'Clearing agent fee for preparing and lodging the customs declaration.',
        'Customs Duty' => 'Import/export duty paid to the revenue authority (disbursement).',
        'Import VAT' => 'VAT paid to the revenue authority on import (disbursement).',
        'Inspection Fee' => 'Customs, port health or other inspection charges.',
        'Permit / Licence Fee' => 'Import/export permits and licences.',
        'Fumigation' => 'Fumigation or phytosanitary treatment.',
        'Bond / Transit Fee' => 'Customs bond or transit guarantee for in-bond cargo.',
        'Border Fees' => 'Border crossing, road access and related fees.',
        // Services
        'Documentation Fee' => 'Preparing and processing shipping documents.',
        'Agency Fee' => 'Forwarding or handling agent fee.',
        'Handling Fee' => 'Cargo handling, loading or offloading.',
        'Cargo Insurance' => 'Marine or transit insurance premium.',
        'Warehousing' => 'Warehouse storage and handling.',
        'Escort Fee' => 'Escort for abnormal, high-value or hazardous loads.',
        'Courier Fee' => 'Couriering original documents.',
    ];

    public const SHIPPING_LINES = [
        'Maersk',
        'MSC (Mediterranean Shipping Company)',
        'CMA CGM',
        'Hapag-Lloyd',
        'COSCO Shipping',
        'ONE (Ocean Network Express)',
        'Evergreen Line',
        'ZIM',
        'PIL (Pacific International Lines)',
        'Yang Ming',
        'HMM',
        'Safmarine',
    ];

    /**
     * Default free days (any shipping line), used until a line-specific
     * policy is set. Typical figures only, to be checked against each line's tariff.
     */
    public const DEFAULT_FREE_DAYS = [
        'port_storage' => 3,
        'demurrage' => 7,
        'detention' => 7,
    ];

    /**
     * Other spellings that count as "already there" (LIKE patterns), so a
     * company's existing "Maersk Line" or "Terminal Handling Charge" isn't
     * duplicated by our own name for it.
     */
    private const ALIASES = [
        'Terminal Handling Charge (THC)' => ['Terminal Handling%', 'THC'],
        'Bill of Lading Fee' => ['B/L Fee', 'BL Fee', 'Bill of Lading%'],
        'Delivery Order Fee' => ['DO Fee', 'Delivery Order%'],
        'Cargo Insurance' => ['Insurance', 'Marine Insurance%'],
        'Customs Duty' => ['Duty', 'Import Duty%'],
        'Maersk' => ['Maersk%'],
        'MSC (Mediterranean Shipping Company)' => ['MSC%', 'Mediterranean Shipping%'],
        'CMA CGM' => ['CMA%'],
        'Hapag-Lloyd' => ['Hapag%'],
        'COSCO Shipping' => ['COSCO%'],
        'ONE (Ocean Network Express)' => ['ONE', 'ONE %', 'Ocean Network Express%'],
        'Evergreen Line' => ['Evergreen%'],
        'ZIM' => ['ZIM', 'ZIM %'],
        'PIL (Pacific International Lines)' => ['PIL', 'PIL %', 'Pacific International%'],
        'Yang Ming' => ['Yang Ming%', 'YangMing%'],
        'HMM' => ['HMM', 'HMM %', 'Hyundai Merchant%'],
        'Safmarine' => ['Safmarine%'],
    ];

    public function run()
    {
        foreach (self::CHARGE_TYPES as $name => $description) {
            if (! $this->exists(ChargeType::class, $name)) {
                ChargeType::create(['name' => $name, 'description' => $description, 'is_locked' => false]);
            }
        }

        self::linkBuiltInContainerCharges();

        foreach (self::SHIPPING_LINES as $name) {
            if (! $this->exists(ShippingLine::class, $name)) {
                ShippingLine::create(['name' => $name, 'is_active' => true]);
            }
        }

        foreach (self::DEFAULT_FREE_DAYS as $chargeType => $days) {
            $hasDefault = ChargeFreeDayPolicy::withTrashed()
                ->where('charge_type', $chargeType)
                ->whereNull('shipping_line_id')
                ->exists();

            if (! $hasDefault) {
                ChargeFreeDayPolicy::create(['charge_type' => $chargeType, 'shipping_line_id' => null, 'free_days' => $days]);
            }
        }
    }

    /**
     * Point the Port Storage / Demurrage / Detention charge types at the
     * built-in container free-time codes, so Charge Config and Charge Types
     * share one list. A missing (or deleted) row is recreated: free-time
     * tracking for those codes always runs.
     */
    public static function linkBuiltInContainerCharges(): void
    {
        // The 2026_11_22 seed migration runs before 2026_11_23 adds these
        // columns; that later migration calls this again once they exist.
        if (! Schema::hasColumn('charge_types', 'exposure_code')) {
            return;
        }

        foreach (ContainerChargeExposure::CHARGE_TYPES as $code => $label) {
            if (ChargeType::where('exposure_code', $code)->exists()) {
                continue;
            }

            $type = ChargeType::whereNull('exposure_code')->where('name', $label)->orderBy('id')->first()
                ?? ChargeType::create([
                    'name' => $label,
                    'description' => self::CHARGE_TYPES[$label] ?? null,
                    'is_locked' => false,
                ]);

            $type->forceFill([
                'exposure_code' => $code,
                'is_line_specific' => $code !== 'port_storage',
            ])->save();
        }
    }

    /** Same name or a known alias, including soft-deleted rows. */
    private function exists(string $model, string $name): bool
    {
        return $model::withTrashed()
            ->where(function ($q) use ($name) {
                $q->where('name', $name);
                foreach (self::ALIASES[$name] ?? [] as $pattern) {
                    $q->orWhere('name', 'like', $pattern);
                }
            })
            ->exists();
    }
}
