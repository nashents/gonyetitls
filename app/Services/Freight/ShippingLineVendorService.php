<?php

namespace App\Services\Freight;

use App\Models\Company;
use App\Models\ShippingLine;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

/**
 * Keeps every shipping line paired with a vendor (so it can be billed and
 * picked as a carrier) and keeps the two names in step:
 *  - a shipping line saved without a vendor attaches the same-named vendor,
 *    or creates one (ShippingLine::saving);
 *  - renaming a shipping line renames its vendor (ShippingLine::updated);
 *  - renaming a vendor renames its shipping line(s) (Vendor::updated).
 */
class ShippingLineVendorService
{
    /** Guards against the two rename hooks re-triggering each other. */
    private static bool $syncing = false;

    /** Id of the vendor created by the most recent ensureVendor() call, for UI messages. */
    public static ?int $lastCreatedVendorId = null;

    public function ensureVendor(ShippingLine $line): void
    {
        if ($line->vendor_id || blank($line->name)) {
            return;
        }

        $vendor = $this->matchingVendor($line->name);
        if (! $vendor) {
            $company = $this->company();
            $vendor = new Vendor;
            $vendor->creator_id = Auth::id();
            $vendor->company_id = $company?->id;
            $vendor->vendor_number = Vendor::nextVendorNumber($company);
            $vendor->name = trim($line->name);
            $vendor->status = 1;
            $vendor->save();
            self::$lastCreatedVendorId = $vendor->id;
        }

        $line->vendor_id = $vendor->id;
    }

    public function matchingVendor(?string $name): ?Vendor
    {
        $name = mb_strtolower(trim((string) $name));

        return $name === '' ? null : Vendor::whereRaw('LOWER(TRIM(name)) = ?', [$name])->first();
    }

    /** Shipping line renamed: rename its vendor. */
    public function shippingLineRenamed(ShippingLine $line): void
    {
        if (self::$syncing || ! $line->vendor_id) {
            return;
        }

        $vendor = Vendor::find($line->vendor_id);
        if (! $vendor || $vendor->name === $line->name) {
            return;
        }

        $this->whileSyncing(function () use ($vendor, $line) {
            $vendor->name = $line->name;
            $vendor->save();
        });
    }

    /** Vendor renamed: rename every shipping line linked to it. */
    public function vendorRenamed(Vendor $vendor): void
    {
        if (self::$syncing) {
            return;
        }

        $this->whileSyncing(function () use ($vendor) {
            ShippingLine::where('vendor_id', $vendor->id)->where('name', '!=', $vendor->name)->get()
                ->each(function (ShippingLine $line) use ($vendor) {
                    $line->name = $vendor->name;
                    $line->save();
                });
        });
    }

    private function whileSyncing(callable $callback): void
    {
        self::$syncing = true;
        try {
            $callback();
        } finally {
            self::$syncing = false;
        }
    }

    /** The signed-in user's company, else the freight-forwarding company (seeders/CLI). */
    private function company(): ?Company
    {
        $user = Auth::user();
        $company = $user?->employee?->company ?? $user?->company;
        if ($company) {
            return $company;
        }

        return Company::whereHas('company_types', fn ($q) => $q->where('name', 'Freight Forwarding & Clearing'))->orderBy('id')->first()
            ?? Company::where('type', '!=', 'admin')->orderBy('id')->first()
            ?? Company::orderBy('id')->first();
    }
}
