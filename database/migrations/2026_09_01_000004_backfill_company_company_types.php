<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Preserves every existing company's single `type` value as its first
 * tagged type in the new many-to-many system, matched case-insensitively
 * against the 4 legacy names (companies.type has a known 'admin'/'Admin'
 * case inconsistency across the codebase).
 */
class BackfillCompanyCompanyTypes extends Migration
{
    public function up()
    {
        $typeIdsByLowerName = DB::table('company_types')
            ->whereIn('name', ['Admin', 'Rental', 'Broker', 'Transporter'])
            ->get()
            ->keyBy(fn ($row) => strtolower($row->name));

        DB::table('companies')
            ->whereNotNull('type')
            ->orderBy('id')
            ->chunk(200, function ($companies) use ($typeIdsByLowerName) {
                foreach ($companies as $company) {
                    $match = $typeIdsByLowerName->get(strtolower(trim($company->type)));

                    if (!$match) {
                        continue;
                    }

                    DB::table('company_company_type')->insertOrIgnore([
                        'company_id' => $company->id,
                        'company_type_id' => $match->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down()
    {
        // Data backfill only; no schema to reverse. Leaving pivot rows in
        // place on rollback is safe and avoids destroying user-added tags.
    }
}
