<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gonyeti Instance Link: two Gonyeti instances of one client (a freight
 * forwarding instance and a transport instance) talk to each other.
 *  - freight side: a road leg sends a trip request; the leg tracks the remote trip;
 *  - transport side: requests land in trip_requests (Trip Management > Trip Requests);
 *  - every message goes through gonyeti_link_messages (outbox/inbox log).
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('integration_providers')->where('key', 'gonyeti_link')->exists();
        DB::table('integration_providers')->updateOrInsert(['key' => 'gonyeti_link'], [
            'name' => 'Gonyeti Instance Link',
            'type' => 'instance_link',
            'driver' => 'App\\Integrations\\GonyetiLink\\GonyetiLinkDriver',
            // base_url = the OTHER instance's address; shared_secret = the same value on both sides.
            'required_credentials' => json_encode(['base_url', 'shared_secret']),
            'optional_credentials' => json_encode([]),
            // role: "freight" (sends road legs) or "transport" (receives them as trip requests).
            'default_config' => json_encode(['role' => 'freight', 'timeout' => 15]),
            'is_active' => true,
            'updated_at' => now(),
        ] + ($exists ? [] : ['created_at' => now()]));

        if (!Schema::hasTable('trip_requests')) {
            Schema::create('trip_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('company_integration_id')->nullable()->index();
                $table->string('remote_ref', 64)->unique();          // the freight leg's link ref
                $table->string('request_number')->nullable();
                $table->string('source_company')->nullable();
                $table->string('job_number')->nullable();
                $table->string('shipment_number')->nullable();
                $table->string('job_customer')->nullable();          // the forwarder's own customer (info)
                $table->text('cargo_description')->nullable();
                $table->decimal('gross_weight', 14, 3)->nullable();
                $table->decimal('volume_cbm', 14, 3)->nullable();
                $table->integer('package_count')->nullable();
                $table->text('containers')->nullable();              // "MSCU1234567 (40HC), ..."
                $table->string('origin_text')->nullable();
                $table->string('destination_text')->nullable();
                $table->unsignedBigInteger('origin_id')->nullable(); // matched local Destination
                $table->unsignedBigInteger('destination_id')->nullable();
                $table->dateTime('planned_departure')->nullable();
                $table->dateTime('planned_arrival')->nullable();
                $table->string('carrier_reference')->nullable();
                $table->text('notes')->nullable();
                // pending -> trip_created | rejected; cancelled by the freight side at any time.
                $table->string('status', 20)->default('pending')->index();
                $table->boolean('changed_since_trip')->default(false); // freight edited the leg after the trip was made
                $table->unsignedBigInteger('trip_id')->nullable()->index();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('responded_by')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('gonyeti_link_messages')) {
            Schema::create('gonyeti_link_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_integration_id')->index();
                $table->string('direction', 3);                      // out | in
                $table->string('event', 50);
                $table->string('ref', 64)->nullable()->index();
                $table->json('payload')->nullable();
                $table->string('status', 10)->default('pending')->index(); // pending | sent | failed | received
                $table->unsignedInteger('attempts')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('shipment_legs', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_legs', 'link_ref')) {
                $table->string('link_ref', 64)->nullable()->unique();
                // requested | failed | trip_created | rejected | cancelled
                $table->string('link_status', 20)->nullable();
                $table->string('link_trip_number')->nullable();
                $table->string('link_trip_status')->nullable();
                $table->text('link_message')->nullable();
                $table->timestamp('link_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_legs', 'link_ref')) {
                $table->dropUnique(['link_ref']);
                $table->dropColumn(['link_ref', 'link_status', 'link_trip_number', 'link_trip_status', 'link_message', 'link_synced_at']);
            }
        });
        Schema::dropIfExists('gonyeti_link_messages');
        Schema::dropIfExists('trip_requests');
        DB::table('integration_providers')->where('key', 'gonyeti_link')->delete();
    }
};
