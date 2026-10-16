<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes fiscalisation provider-pluggable:
 *  - fiscal_documents records which provider / company integration fiscalised
 *    each document (status checks and credit notes go back to it);
 *  - integration_providers can list optional credentials (Revmax's login is
 *    only needed when the web service has authentication switched on);
 *  - registers Revmax and brings the Fiscal Harmony row in line with what its
 *    driver actually needs (same values as IntegrationProviderSeeder).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('fiscal_documents', 'provider')) {
                $table->string('provider')->nullable()->after('document_type');
            }
            if (! Schema::hasColumn('fiscal_documents', 'company_integration_id')) {
                $table->unsignedBigInteger('company_integration_id')->nullable()->index()->after('provider');
            }
        });

        // Everything fiscalised so far went through Fiscal Harmony.
        DB::table('fiscal_documents')->whereNull('provider')->update(['provider' => 'fiscal_harmony']);

        Schema::table('integration_providers', function (Blueprint $table) {
            if (! Schema::hasColumn('integration_providers', 'optional_credentials')) {
                $table->json('optional_credentials')->nullable()->after('required_credentials');
            }
        });

        foreach (static::providers() as $provider) {
            $exists = DB::table('integration_providers')->where('key', $provider['key'])->exists();

            DB::table('integration_providers')->updateOrInsert(
                ['key' => $provider['key']],
                $provider + ['updated_at' => now()] + ($exists ? [] : ['created_at' => now()])
            );
        }
    }

    public function down(): void
    {
        DB::table('integration_providers')->where('key', 'revmax')->delete();

        Schema::table('integration_providers', function (Blueprint $table) {
            if (Schema::hasColumn('integration_providers', 'optional_credentials')) {
                $table->dropColumn('optional_credentials');
            }
        });

        Schema::table('fiscal_documents', function (Blueprint $table) {
            if (Schema::hasColumn('fiscal_documents', 'company_integration_id')) {
                $table->dropIndex(['company_integration_id']);
                $table->dropColumn('company_integration_id');
            }
            if (Schema::hasColumn('fiscal_documents', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }

    public static function providers(): array
    {
        return [
            [
                'key'                  => 'fiscal_harmony',
                'name'                 => 'Fiscal Harmony',
                'type'                 => 'fiscal',
                'driver'               => 'App\\Integrations\\FiscalHarmony\\FiscalHarmonyDriver',
                'required_credentials' => json_encode(['base_url', 'api_key', 'api_secret', 'app_name', 'app_station']),
                'optional_credentials' => json_encode([]),
                'default_config'       => json_encode(['payment_method' => 'Credit', 'app_version' => '1', 'timeout' => 30]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'revmax',
                'name'                 => 'Revmax',
                'type'                 => 'fiscal',
                'driver'               => 'App\\Integrations\\Revmax\\RevmaxDriver',
                'required_credentials' => json_encode(['base_url']),
                'optional_credentials' => json_encode(['email', 'password']),
                'default_config'       => json_encode([
                    'payment_method'           => 'Credit',
                    'receipt_print_form'       => 'InvoiceA4',
                    'tax_code_standard'        => 'A',
                    'tax_code_zero_rated'      => 'B',
                    'tax_code_exempt'          => 'C',
                    'auto_open_fiscal_day'     => 'yes',
                    'auto_rollover_fiscal_day' => 'yes',
                    'timeout'                  => 30,
                ]),
                'is_active'            => true,
            ],
        ];
    }
};
