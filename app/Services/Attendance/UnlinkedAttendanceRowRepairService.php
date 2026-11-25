<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceRegister;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

/**
 * Re-links attendance_registers rows saved with no attendance_id.
 *
 * Before 2026-11-25 AttendanceRegister::$fillable lacked attendance_id, so
 * anyone marked for the first time from the Edit screen was saved to a row
 * that belonged to no register. The register then showed them as unmarked.
 *
 * Each orphan is matched to the register that was being worked on when it was
 * saved: activity on a register (its own audits, or audits of rows linked to
 * it) by the same user within WINDOW seconds. If audits are missing it falls
 * back to attendances.updated_at. When more than one register could be the
 * match, the one whose department expects the employee wins. If it is still
 * unclear, the row is skipped and reported.
 */
class UnlinkedAttendanceRowRepairService
{
    const WINDOW = 120;

    /**
     * @return Collection<int, array{row_id:int, employee:string, saved_at:string, user:?string, register:?string, action:string, note:string}>
     */
    public function run(bool $apply): Collection
    {
        $orphans = AttendanceRegister::query()
            ->whereNull('attendance_id')
            ->with('employee:id,name,surname,employee_number')
            ->orderBy('id')
            ->get();

        $results = collect();

        foreach ($orphans as $row) {
            [$userId, $savedAt] = $this->savedBy($row);
            $match = $savedAt ? $this->matchRegister($row, $userId, $savedAt) : ['register' => null, 'note' => 'No save time recorded'];

            $result = [
                'row_id' => $row->id,
                'employee' => $row->employee ? trim($row->employee->name . ' ' . $row->employee->surname) : "Employee #{$row->employee_id}",
                'status' => $row->status ?: '(blank)',
                'saved_at' => $savedAt ? $savedAt->format('Y-m-d H:i:s') : '',
                'user' => $userId ? optional(\App\Models\User::find($userId), fn ($u) => trim($u->name . ' ' . $u->surname)) : null,
                'register' => $match['register']?->attendance_number,
                'action' => 'skip',
                'note' => $match['note'],
            ];

            if ($register = $match['register']) {
                $existing = AttendanceRegister::where('attendance_id', $register->id)
                    ->where('employee_id', $row->employee_id)
                    ->first();

                if ($existing && filled($existing->status)) {
                    $result['action'] = 'skip';
                    $result['note'] = "Already marked '{$existing->status}' on {$register->attendance_number}; orphan left as is";
                } elseif ($existing) {
                    $result['action'] = 'fill blank row';
                    $result['note'] = "Copies onto blank row #{$existing->id}, orphan removed";
                    if ($apply) {
                        DB::transaction(function () use ($existing, $row) {
                            $existing->forceFill([
                                'status' => $row->status,
                                'shift' => $row->shift,
                                'checkin' => $row->checkin,
                                'checkout' => $row->checkout,
                                'notes' => $row->notes,
                            ])->save();
                            $row->delete();
                        });
                    }
                } else {
                    $result['action'] = 'link';
                    if ($apply) {
                        $row->forceFill(['attendance_id' => $register->id])->save();
                    }
                }
            }

            $results->push($result);
        }

        return $results;
    }

    /** [user_id, saved_at] from the row's "created" audit, else its timestamps. */
    protected function savedBy(AttendanceRegister $row): array
    {
        $audit = Audit::query()
            ->where('auditable_type', AttendanceRegister::class)
            ->where('auditable_id', $row->id)
            ->where('event', 'created')
            ->orderBy('id')
            ->first(['user_id', 'created_at']);

        if ($audit) {
            return [$audit->user_id, Carbon::parse($audit->created_at)];
        }

        return [null, $row->created_at ? Carbon::parse($row->created_at) : null];
    }

    /** @return array{register: ?Attendance, note: string} */
    protected function matchRegister(AttendanceRegister $row, $userId, Carbon $savedAt): array
    {
        $from = $savedAt->copy()->subSeconds(self::WINDOW);
        $to = $savedAt->copy()->addSeconds(self::WINDOW);

        // Register id => seconds from the orphan's save.
        $gaps = [];
        $note = function ($id, $at) use (&$gaps, $savedAt) {
            if (!$id || !$at) {
                return;
            }
            $gap = abs($savedAt->diffInSeconds(Carbon::parse($at), false));
            $gaps[$id] = min($gaps[$id] ?? PHP_INT_MAX, $gap);
        };

        // 1) Audits by the same user around the save.
        $audits = Audit::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where(fn ($q) => $q->where('auditable_type', Attendance::class)->orWhere('auditable_type', AttendanceRegister::class))
            ->get(['auditable_type', 'auditable_id', 'created_at']);

        $rowRegisters = AttendanceRegister::withTrashed()
            ->whereIn('id', $audits->where('auditable_type', AttendanceRegister::class)->pluck('auditable_id'))
            ->whereNotNull('attendance_id')
            ->pluck('attendance_id', 'id');

        foreach ($audits as $audit) {
            $id = $audit->auditable_type === Attendance::class ? $audit->auditable_id : ($rowRegisters[$audit->auditable_id] ?? null);
            $note($id, $audit->created_at);
        }

        $source = 'audit';

        // 2) No audits: registers (or their rows) touched around the save.
        if (!$gaps) {
            $source = 'timestamps';
            foreach (Attendance::query()->whereBetween('updated_at', [$from, $to])->when($userId, fn ($q) => $q->where('user_id', $userId))->get(['id', 'updated_at']) as $a) {
                $note($a->id, $a->updated_at);
            }
            foreach (AttendanceRegister::query()->whereNotNull('attendance_id')->whereBetween('updated_at', [$from, $to])->get(['attendance_id', 'updated_at']) as $r) {
                $note($r->attendance_id, $r->updated_at);
            }
        }

        if (!$gaps) {
            return ['register' => null, 'note' => 'No register was being edited within ' . self::WINDOW . 's of this save'];
        }

        $candidates = Attendance::with('department')->whereIn('id', array_keys($gaps))->get();

        // Prefer registers whose department expects this employee.
        $expecting = $candidates->filter(fn ($a) => Attendance::registerEmployeesQuery($a->department, (bool) $a->is_drivers)
            ->where('employees.id', $row->employee_id)->exists());
        $pool = $expecting->isNotEmpty() ? $expecting : $candidates;

        if ($pool->count() > 1) {
            return ['register' => null, 'note' => 'Ambiguous: ' . $pool->pluck('attendance_number')->implode(', ') . ' were all being edited then'];
        }

        $register = $pool->first();
        $warn = $expecting->isEmpty() ? '; employee is not in that register\'s department now' : '';

        return ['register' => $register, 'note' => "Matched by {$source}, {$gaps[$register->id]}s apart{$warn}"];
    }

    /**
     * Read-only report of what makes a name appear twice on a register.
     *
     * @return array<string, Collection>
     */
    public function duplicateReport(): array
    {
        // Same employee twice on one register.
        $rows = AttendanceRegister::query()
            ->whereNotNull('attendance_id')
            ->select('attendance_id', 'employee_id', DB::raw('COUNT(*) as c'), DB::raw("GROUP_CONCAT(CONCAT(id, ':', COALESCE(status, 'blank'))) as entries"))
            ->groupBy('attendance_id', 'employee_id')
            ->having('c', '>', 1)
            ->get();

        // The same person held as two (or more) active employee records.
        $people = Employee::query()
            ->where('archive', 0)
            ->select(DB::raw('LOWER(TRIM(name)) as n'), DB::raw('LOWER(TRIM(surname)) as s'), DB::raw('COUNT(*) as c'),
                DB::raw("GROUP_CONCAT(CONCAT(id, ' ', COALESCE(employee_number, '-'))) as records"))
            ->groupBy('n', 's')
            ->having('c', '>', 1)
            ->get();

        // An employee linked to the same default department more than once.
        $pivots = DB::table('department_employee')
            ->where('is_default', true)
            ->select('employee_id', 'department_id', DB::raw('COUNT(*) as c'))
            ->groupBy('employee_id', 'department_id')
            ->having('c', '>', 1)
            ->get();

        return ['rows' => $rows, 'people' => $people, 'pivots' => $pivots];
    }
}
