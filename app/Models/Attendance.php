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
     * DEFAULT department it is. In Transport & Logistics the drivers flag
     * splits drivers from non-drivers; elsewhere it limits to drivers only.
     * Same list the marking form shows.
     */
    public static function registerEmployeesQuery(?Department $department, $isDrivers = false)
    {
        if (!$department) {
            return Employee::query()->whereRaw('1 = 0');
        }

        $query = $department->defaultEmployees()->where('employees.archive', 0);

        if ($department->name === 'Transport & Logistics') {
            $isDrivers ? $query->whereHas('driver') : $query->whereDoesntHave('driver');
        } elseif ($isDrivers) {
            $query->whereHas('driver');
        }

        return $query;
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
