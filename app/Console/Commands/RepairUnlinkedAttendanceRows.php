<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Employee;
use App\Services\Attendance\UnlinkedAttendanceRowRepairService;
use Illuminate\Console\Command;

/**
 * Re-links attendance rows saved without a register (old Edit-screen bug), then
 * reports, without changing anything, what makes a name appear twice on a register.
 *
 * php artisan attendance:repair-unlinked --dry-run
 * php artisan attendance:repair-unlinked --force
 */
class RepairUnlinkedAttendanceRows extends Command
{
    protected $signature = 'attendance:repair-unlinked
        {--dry-run : Preview only, fix nothing}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Re-link attendance rows saved without a register, and report duplicate names on registers';

    public function handle(UnlinkedAttendanceRowRepairService $service)
    {
        $rows = $service->run(false);

        $this->info('1) Attendance rows saved without a register');
        if ($rows->isEmpty()) {
            $this->line('   None found.');
        } else {
            $this->table(['Row', 'Employee', 'Status', 'Saved at', 'Saved by', 'Register', 'Action', 'Note'], $rows->map(fn ($r) => [
                $r['row_id'], $r['employee'], $r['status'], $r['saved_at'], $r['user'] ?? '?', $r['register'] ?? '-', $r['action'], $r['note'],
            ]));
            $fixable = $rows->where('action', '!=', 'skip')->count();
            $this->line("   {$rows->count()} row(s): {$fixable} can be fixed, " . ($rows->count() - $fixable) . ' skipped.');
        }

        $this->reportDuplicates($service->duplicateReport());

        if ($rows->where('action', '!=', 'skip')->isEmpty()
            || $this->option('dry-run')
            || (!$this->option('force') && !$this->confirm('Apply the fixes in section 1?'))) {
            return 0;
        }

        $service->run(true);
        $this->info('Done. Section 2 is a report only; nothing there was changed.');

        return 0;
    }

    private function reportDuplicates(array $report): void
    {
        $this->newLine();
        $this->info('2) Why a name can appear twice on a register (report only)');

        $this->line('   a) Same employee record saved twice on one register:');
        if ($report['rows']->isEmpty()) {
            $this->line('      None.');
        } else {
            $this->table(['Register id', 'Employee id', 'Rows (id:status)'], $report['rows']->map(fn ($r) => [$r->attendance_id, $r->employee_id, $r->entries]));
        }

        $this->line('   b) One person held as two or more active employee records (each shows as its own line):');
        if ($report['people']->isEmpty()) {
            $this->line('      None.');
        } else {
            $this->table(['Name', 'Records (id employee#)'], $report['people']->map(fn ($p) => [ucwords("{$p->n} {$p->s}"), $p->records]));
        }

        $this->line('   c) Employee linked to the same default department more than once:');
        if ($report['pivots']->isEmpty()) {
            $this->line('      None.');
        } else {
            $this->table(['Employee', 'Department', 'Links'], $report['pivots']->map(fn ($p) => [
                optional(Employee::find($p->employee_id), fn ($e) => "{$e->name} {$e->surname} (#{$e->id})") ?? $p->employee_id,
                optional(Department::find($p->department_id))->name ?? $p->department_id,
                $p->c,
            ]));
        }
    }
}
