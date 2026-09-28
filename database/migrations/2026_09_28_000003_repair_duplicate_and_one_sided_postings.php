<?php

use App\Services\Accounting\LedgerDoublePostingRepairService;
use Illuminate\Database\Migrations\Migration;

/**
 * Cleans up bills/invoices/credit/debit notes left in the ledger more than
 * once, or with an Accounts Payable credit and no debit leg - see
 * LedgerDoublePostingRepairService for the two bugs behind it. Each damaged
 * document gets its live postings reversed and one fresh entry posted from
 * its current figures.
 *
 * Idempotent: a document with a single balanced posting is left alone, so
 * re-running finds nothing to do.
 */
class RepairDuplicateAndOneSidedPostings extends Migration
{
    public function up()
    {
        $result = app(LedgerDoublePostingRepairService::class)->run();

        echo '  Repaired: ' . count($result['repaired']) . ', skipped: ' . count($result['skipped'])
            . ', failed: ' . count($result['failed']) . "\n";

        foreach (['skipped', 'failed'] as $bucket) {
            foreach ($result[$bucket] as $row) {
                echo "  {$bucket}: {$row['type']} #{$row['id']} {$row['number']}"
                    . (isset($row['error']) ? " - {$row['error']}" : '') . "\n";
            }
        }
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
