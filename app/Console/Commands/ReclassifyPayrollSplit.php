<?php

namespace App\Console\Commands;

use App\Models\PayrollRun;
use App\Services\Accounting\PayrollJournalService;
use Illuminate\Console\Command;

class ReclassifyPayrollSplit extends Command
{
    /**
     * php artisan payroll:reclassify-split --dry-run
     * php artisan payroll:reclassify-split
     * php artisan payroll:reclassify-split --run=1234 --run=1240 --force
     */
    protected $signature = 'payroll:reclassify-split
        {--dry-run : Preview only, post nothing}
        {--force : Skip the confirmation prompt}
        {--run=* : Limit to these payroll run IDs (repeatable)}';

    protected $description = "Reclassify driver wages/statutory employer cost out of the Admin/Ops GL accounts into the Drivers/COGS accounts, for payroll runs posted before split-by-employee-type accounting was turned on for their company. Posts one balanced adjusting journal entry per run; the original entry, PAYE/NSSA/NEC/Pension payables and net pay are untouched.";

    public function handle(PayrollJournalService $service)
    {
        $runIds = $this->option('run') ? array_map('intval', $this->option('run')) : null;

        $candidates = $service->candidatesForReclassification($runIds);

        if ($candidates->isEmpty()) {
            $this->info('No payroll runs need reclassification.');

            return self::SUCCESS;
        }

        $this->table(
            ['Run #', 'Company', 'Payroll Date', 'Name'],
            $candidates->map(fn (PayrollRun $run) => [
                $run->id,
                $run->company->name ?? $run->company_id,
                optional($run->payroll_date)->format('Y-m-d'),
                $run->name,
            ])->all()
        );

        if ($this->option('dry-run')) {
            $this->warn(
                count($candidates) . ' payroll run(s) above would get one adjusting journal entry each, moving driver-attributable '
                . 'wages/NSSA/NEC/Pension employer cost out of the Admin/Ops GL accounts into the Drivers/COGS accounts. '
                . 'Nothing has changed. Re-run without --dry-run to apply.'
            );

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm(
            'Post a reclassification entry for ' . count($candidates) . ' payroll run(s) above?'
        )) {
            $this->line('Aborted - nothing changed.');

            return self::SUCCESS;
        }

        $fixed = 0;

        foreach ($candidates as $run) {
            try {
                $entry = $service->reclassifySplit($run);
                $this->line("  Run #{$run->id}: posted {$entry->journal_number}");
                $fixed++;
            } catch (\Throwable $e) {
                $this->error("  Run #{$run->id}: {$e->getMessage()}");
            }
        }

        $this->info("Reclassified {$fixed} / " . count($candidates) . ' payroll run(s).');

        return self::SUCCESS;
    }
}
