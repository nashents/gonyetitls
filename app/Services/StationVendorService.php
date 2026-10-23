<?php

namespace App\Services;

use App\Models\Container;
use App\Models\Fuel;
use App\Models\TopUp;
use App\Models\Vendor;
use App\Models\VendorType;
use Illuminate\Support\Facades\Auth;

/**
 * The supplier behind a fuel bill. A fuel order's own trip expense names
 * it; when that's blank the station's linked vendor is used and written
 * back onto the trip expense; a station with no vendor gets one created
 * (Fuel type, from the station's details) and linked - the same record
 * Fuel Stations > Sync to Vendor creates.
 */
class StationVendorService
{
    public function forFuel(Fuel $fuel): ?int
    {
        $tripExpense = $fuel->trip_expense;
        if ($tripExpense && $tripExpense->vendor_id) {
            return (int) $tripExpense->vendor_id;
        }

        $vendorId = $this->forStation($fuel->container);

        if ($vendorId && $tripExpense) {
            $tripExpense->vendor_id = $vendorId;
            $tripExpense->saveQuietly();
        }

        return $vendorId;
    }

    public function forTopUp(TopUp $topUp): ?int
    {
        return $topUp->vendor_id ? (int) $topUp->vendor_id : $this->forStation($topUp->container);
    }

    /** The station's vendor, creating and linking one when it has none. */
    public function forStation(?Container $container): ?int
    {
        if (!$container) {
            return null;
        }

        if ($container->vendor_id && Vendor::whereKey($container->vendor_id)->exists()) {
            return (int) $container->vendor_id;
        }

        $container->vendor_id = $this->createForStation($container)->id;
        $container->saveQuietly();

        return (int) $container->vendor_id;
    }

    public function createForStation(Container $container): Vendor
    {
        $user = Auth::user();
        $company = $user ? ($user->company ?? optional($user->employee)->company) : null;

        $vendor = new Vendor;
        $vendor->creator_id = optional($user)->id;
        $vendor->company_id = $company->id ?? null;
        $vendor->vendor_type_id = optional(VendorType::where('name', 'Fuel')->first())->id;
        $vendor->vendor_number = Vendor::nextVendorNumber();
        $vendor->name = trim((string) $container->name);
        $vendor->email = $container->email;
        $vendor->phonenumber = $container->phonenumber;
        $vendor->street_address = $container->address;
        $vendor->currency_id = $container->currency_id ?: null;
        $vendor->status = 1;
        $vendor->save();

        return $vendor;
    }
}
