<?php

use App\Models\ShippingLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sea legs and sea transport documents (bills of lading / sea waybills) now
 * pick their carrier from Shipping Lines instead of raw Vendors, the same
 * move containers and charge config made in 2026_09_17_000002. The line's
 * vendor is still stored in carrier_vendor_id for billing.
 *
 * Also pairs every existing shipping line with a vendor (attach by name or
 * create), which ShippingLine::saving now does for new ones.
 */
return new class extends Migration
{
    private const SEA_DOCUMENT_TYPES = ['master_bill_of_lading', 'house_bill_of_lading', 'sea_waybill'];

    public function up(): void
    {
        foreach (['shipment_legs', 'transport_documents'] as $table) {
            if (! Schema::hasColumn($table, 'shipping_line_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unsignedBigInteger('shipping_line_id')->nullable()->after('carrier_vendor_id');
                    $blueprint->foreign('shipping_line_id')->references('id')->on('shipping_lines')->onDelete('set null');
                });
            }
        }

        // Pair every shipping line with a vendor (ShippingLine::saving -> ShippingLineVendorService).
        ShippingLine::whereNull('vendor_id')->get()->each->save();

        // Backfill: a sea carrier vendor that belongs to a shipping line becomes that line.
        $lineByVendor = DB::table('shipping_lines')->whereNull('deleted_at')->whereNotNull('vendor_id')
            ->orderBy('id')->get(['id', 'vendor_id'])->unique('vendor_id')->pluck('id', 'vendor_id');

        foreach ($lineByVendor as $vendorId => $lineId) {
            DB::table('shipment_legs')->where('transport_mode', 'sea')->whereNull('shipping_line_id')
                ->where('carrier_vendor_id', $vendorId)->update(['shipping_line_id' => $lineId]);
            DB::table('transport_documents')->whereIn('document_type', self::SEA_DOCUMENT_TYPES)->whereNull('shipping_line_id')
                ->where('carrier_vendor_id', $vendorId)->update(['shipping_line_id' => $lineId]);
        }
    }

    public function down(): void
    {
        foreach (['shipment_legs', 'transport_documents'] as $table) {
            if (Schema::hasColumn($table, 'shipping_line_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['shipping_line_id']);
                    $blueprint->dropColumn('shipping_line_id');
                });
            }
        }
    }
};
