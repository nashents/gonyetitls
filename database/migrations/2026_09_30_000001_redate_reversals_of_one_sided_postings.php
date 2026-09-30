<?php

use App\Services\Accounting\OneSidedReversalRedateService;
use Illuminate\Database\Migrations\Migration;

/**
 * The Trial Balance balanced to date but not for a range ending in August:
 * the one-sided bill postings repaired by 2026_09_28_000003 are dated August,
 * the reversals that cancel them September. Moves those reversals onto the
 * date of the one-sided entry they cancel - see OneSidedReversalRedateService.
 *
 * Idempotent: a chain already on one date is left alone, so re-running finds
 * nothing to do.
 */
class RedateReversalsOfOneSidedPostings extends Migration
{
    public function up()
    {
        $result = app(OneSidedReversalRedateService::class)->run();

        echo '  Chains re-dated: ' . count($result['redated'])
            . ' (' . array_sum(array_map(fn ($row) => count($row['entries']), $result['redated'])) . ' entries)'
            . ', skipped: ' . count($result['skipped']) . "\n";

        foreach ($result['skipped'] as $row) {
            echo "  skipped: {$row['root']} {$row['reference']} - {$row['reason']}\n";
        }
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
