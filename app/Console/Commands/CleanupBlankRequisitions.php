<?php

namespace App\Console\Commands;

use App\Models\Requisition;
use Illuminate\Console\Command;

class CleanupBlankRequisitions extends Command
{
    /**
     * php artisan requisitions:cleanup-blank {--delete}
     */
    protected $signature = 'requisitions:cleanup-blank {--delete : Soft-delete the orphaned rows instead of just listing them}';

    protected $description = 'Find (and optionally soft-delete) blank requisitions created by duplicate/queued form submissions (empty type, department_id and no line items)';

    public function handle()
    {
        $orphans = Requisition::query()
            ->whereNull('type')
            ->whereNull('department_id')
            ->doesntHave('requisition_items')
            ->orderBy('id')
            ->get(['id', 'requisition_number', 'user_id', 'created_at']);

        if ($orphans->isEmpty()) {
            $this->info('No orphaned blank requisitions found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Requisition #', 'User ID', 'Created At'],
            $orphans->map(fn ($r) => [$r->id, $r->requisition_number, $r->user_id, $r->created_at])
        );

        if (! $this->option('delete')) {
            $this->warn("{$orphans->count()} orphaned blank requisition(s) found. Re-run with --delete to soft-delete them.");

            return self::SUCCESS;
        }

        Requisition::whereIn('id', $orphans->pluck('id'))->delete();

        $this->info("Soft-deleted {$orphans->count()} orphaned blank requisition(s).");

        return self::SUCCESS;
    }
}
