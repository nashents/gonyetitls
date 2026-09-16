<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a nullable shipping_line_id FK to the three tables that previously
 * pointed "shipping line" straight at a raw Vendor, then backfills: for
 * every distinct shipping_line_vendor_id already in use, creates one
 * ShippingLine row (name = that vendor's name) and points the new column
 * at it, so existing free-day policies/rate tiers/containers keep
 * matching correctly with zero manual re-entry. The old
 * shipping_line_vendor_id/shipping_line_name columns are left untouched
 * - not dropped, not renamed - purely additive.
 */
class AddShippingLineIdAndBackfill extends Migration
{
    protected array $tables = ['shipping_containers', 'charge_free_day_policies', 'charge_rate_tiers'];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('shipping_line_id')->nullable()->after('shipping_line_vendor_id');
                $blueprint->foreign('shipping_line_id')->references('id')->on('shipping_lines')->onDelete('set null');
            });
        }

        $vendorIds = collect();
        foreach ($this->tables as $table) {
            $vendorIds = $vendorIds->merge(
                DB::table($table)->whereNotNull('shipping_line_vendor_id')->distinct()->pluck('shipping_line_vendor_id')
            );
        }
        $vendorIds = $vendorIds->unique()->values();

        $shippingLineIdByVendorId = [];

        foreach ($vendorIds as $vendorId) {
            $vendor = DB::table('vendors')->where('id', $vendorId)->first(['id', 'name']);

            if (!$vendor) {
                continue;
            }

            $existing = DB::table('shipping_lines')->where('vendor_id', $vendor->id)->first();

            $shippingLineId = $existing?->id ?? DB::table('shipping_lines')->insertGetId([
                'name' => $vendor->name,
                'vendor_id' => $vendor->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $shippingLineIdByVendorId[$vendorId] = $shippingLineId;
        }

        foreach ($this->tables as $table) {
            foreach ($shippingLineIdByVendorId as $vendorId => $shippingLineId) {
                DB::table($table)->where('shipping_line_vendor_id', $vendorId)->update([
                    'shipping_line_id' => $shippingLineId,
                ]);
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign($table . '_shipping_line_id_foreign');
                $blueprint->dropColumn('shipping_line_id');
            });
        }
    }
}
