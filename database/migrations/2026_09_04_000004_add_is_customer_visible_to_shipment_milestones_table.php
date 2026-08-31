<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Default true so every existing ShipmentMilestone-creation call site
 * (ShipmentMilestoneService, ShippingContainerService,
 * CustomsDeclarationService, ShipmentLegService) needs no changes -
 * staff can flag a specific milestone as internal-only afterward.
 */
class AddIsCustomerVisibleToShipmentMilestonesTable extends Migration
{
    public function up()
    {
        Schema::table('shipment_milestones', function (Blueprint $table) {
            $table->boolean('is_customer_visible')->default(true)->after('notes');
        });
    }

    public function down()
    {
        Schema::table('shipment_milestones', function (Blueprint $table) {
            $table->dropColumn('is_customer_visible');
        });
    }
}
