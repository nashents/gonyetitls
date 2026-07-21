<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationMappingsTable extends Migration
{
    /**
     * Generic, reusable link between a local Gonyeti record and its external
     * (Sage) record. One table for every entity type (transporter, horse,
     * trailer, trip, and future entities) instead of per-table sage_* columns.
     *
     * Scoped to a company via company_integration_id (which carries both the
     * company and the provider), so it is multi-company aware by construction.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_mappings', function (Blueprint $table) {
            $table->id();

            // Company + provider scope (FK to the existing framework table).
            $table->foreignId('company_integration_id')
                ->constrained('company_integrations')
                ->cascadeOnDelete();

            $table->string('entity_type');          // transporter|horse|trailer|trip
            $table->string('local_model')->nullable(); // FQCN, e.g. App\Models\Horse
            $table->unsignedBigInteger('local_id');
            $table->string('local_reference')->nullable();  // e.g. registration / trip_number

            $table->string('external_id')->nullable();        // Sage CLASSID / PROJECTID
            $table->string('external_reference')->nullable();  // Sage RECORDNO / NAME

            // not_synced|pending|synced|failed|requires_attention
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->text('last_error')->nullable();

            // Redacted payloads for troubleshooting — function XML only, NEVER
            // the auth envelope (no credentials are ever written here).
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_integration_id', 'entity_type', 'local_id'], 'integration_mappings_scope_unique');
            $table->index(['entity_type', 'local_id']);
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_mappings');
    }
}
