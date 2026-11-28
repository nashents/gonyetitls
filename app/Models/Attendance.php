<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function attendance_registers(){
        return $this->hasMany('App\Models\AttendanceRegister');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }

    /**
     * Employees who belong on a register for $department: active staff whose
     * DEFAULT department it is. In EVERY department the drivers flag splits
     * the register: ticked = drivers only, unticked = non-drivers only
     * (drivers sit under Operations, Rehandle etc. in some companies, not
     * just Transport & Logistics). Same list the marking form shows.
     */
    public static function registerEmployeesQuery(?Department $department, $isDrivers = false)
    {
        if (!$department) {
            return Employee::query()->whereRaw('1 = 0');
        }

        $query = $department->defaultEmployees()->where('employees.archive', 0);

        $isDrivers ? $query->whereHas('driver') : $query->whereDoesntHave('driver');

        return $query;
    }

    /**
     * Flags for people captured twice by mistake: once as an employee and
     * once as a driver (two employee records, different employee numbers).
     * Returns [employee_id => "Also on file as driver DIE00311 (Rehandle)"]
     * for each of $employees with an active record of the OTHER kind that has
     * the same name (case/spacing ignored) or the same ID number.
     */
    public static function otherKindRecords($employees): array
    {
        $employees = collect($employees)->filter();
        if ($employees->isEmpty()) {
            return [];
        }

        $nameKey = fn ($e) => mb_strtolower(Employee::normalizeName($e->name) . '|' . Employee::normalizeName($e->surname));
        $idKey = fn ($e) => Employee::normalizeIdNumber($e->idnumber);

        $names = $employees->map(fn ($e) => mb_strtolower(Employee::normalizeName($e->name)))->unique()->values()->all();
        $surnames = $employees->map(fn ($e) => mb_strtolower(Employee::normalizeName($e->surname)))->unique()->values()->all();
        $ids = $employees->map($idKey)->filter()->unique()->values()->all();

        $placeholders = fn ($list) => implode(',', array_fill(0, count($list), '?'));

        $candidates = Employee::query()
            ->with(['driver:id,employee_id', 'default_departments:departments.id,departments.name'])
            ->where('employees.archive', 0)
            ->where(function ($q) use ($names, $surnames, $ids, $placeholders) {
                $q->where(function ($n) use ($names, $surnames, $placeholders) {
                    $n->whereRaw('LOWER(TRIM(employees.name)) IN (' . $placeholders($names) . ')', $names)
                      ->whereRaw('LOWER(TRIM(employees.surname)) IN (' . $placeholders($surnames) . ')', $surnames);
                });
                if ($ids) {
                    $q->orWhereRaw("UPPER(REPLACE(REPLACE(REPLACE(employees.idnumber, ' ', ''), '-', ''), '/', '')) IN (" . $placeholders($ids) . ')', $ids);
                }
            })
            ->get();

        $flags = [];
        foreach ($employees as $employee) {
            $isDriver = $employee->relationLoaded('driver') ? $employee->driver !== null : $employee->driver()->exists();

            $other = $candidates->first(function ($c) use ($employee, $isDriver, $nameKey, $idKey) {
                if ($c->id === $employee->id || ($c->driver !== null) === $isDriver) {
                    return false;
                }
                $sameId = $idKey($employee) !== '' && $idKey($c) === $idKey($employee);
                return $sameId || $nameKey($c) === $nameKey($employee);
            });

            if ($other) {
                $dept = $other->default_departments->first()?->name;
                $flags[$employee->id] = 'Also on file as ' . ($isDriver ? 'employee' : 'driver') . ' '
                    . ($other->employee_number ?: 'id ' . $other->id)
                    . ($dept ? " ({$dept})" : '');
            }
        }

        return $flags;
    }

    const DRAFT = 'draft';
    const FINAL = 'final';

    public function isDraft(): bool
    {
        return $this->register_status === self::DRAFT;
    }

    public function isFinal(): bool
    {
        return !$this->isDraft();
    }

    /** A register row counts as marked only once it has a status. */
    public static function isMarked(?AttendanceRegister $register): bool
    {
        return $register !== null && filled($register->status);
    }

    /** Expected employees with no status yet (no row, or a row left blank). */
    public function unmarkedEmployees()
    {
        $marked = $this->attendance_registers()->get()->keyBy('employee_id');

        return $this->expectedEmployees()
            ->filter(fn ($employee) => !static::isMarked($marked->get($employee->id)))
            ->values();
    }

    /** The employees this register should cover, marked or not. */
    public function expectedEmployees()
    {
        return static::registerEmployeesQuery($this->department, (bool) $this->is_drivers)
            ->with('driver:id,employee_id')
            ->orderBy('employees.name')
            ->orderBy('employees.surname')
            ->get();
    }

    protected $fillable = [
        'user_id',
        'department_id',
        'is_drivers',
        'register_status',
        'finalized_at',
        'finalized_by',
        'time',
        'date',
     
    ];
}
