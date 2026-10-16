<?php

use App\Services\Accounting\LedgerDoublePostingRepairService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Trial Balance out of balance (COMDELTIC -13,254.95) - two deletion bugs:
 *  - BillDeletionService/InvoiceDeletionService reversed every entry not yet
 *    'reversed', REV- cancellers included, which re-instated the postings
 *    they had cancelled: deleted fuel bills came back 2-5x, along with the
 *    old one-sided Accounts Payable credits (the whole imbalance);
 *  - trip expense removal (and other direct ->delete() callers) deleted the
 *    bill without un-posting it at all.
 * Both fixed (JournalReversalService::reverseLiveEffects + the deleted()
 * observers). This un-posts every deleted bill/invoice/note/payment that
 * still has a live posting, dated the day it was deleted. Idempotent.
 */
class UnpostDeletedDocuments extends Migration
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

        // 2026_10_15_000004's first version dated chained reversals from
        // their predecessor's OLD date (a month off) - re-run the fixed
        // command; a no-op where 000004 already ran the fixed version.
        Artisan::call('journals:redate-correction-reversals', ['--since' => '2000-01-01']);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
