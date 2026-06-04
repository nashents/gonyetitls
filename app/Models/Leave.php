<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Leave extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function employee_leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function leave_type(){
        return $this->belongsTo('App\Models\LeaveType');
    }

    protected $fillable = [
        'user_id',
        'to',
        'from',
        'reason',
        'days',
        'leave_type_id',
        'available_leave_days',
    ];

    public function scopeFilterLeaves($query, $filter = null, $from = null, $to = null, $search = null)
    {
        $today = Carbon::today();

        switch ($filter) {
            case 'all':
                if ($from && $to) {
                    $query->where(function ($q) use ($from, $to) {
                        $q->whereBetween('from', [$from, $to])
                            ->orWhereBetween('to', [$from, $to])
                            ->orWhere(function ($q) use ($from, $to) {
                                $q->where('from', '<', $from)
                                    ->where('to', '>', $to);
                            });
                    });
                } else {
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                }
                break;

            case 'inprogress':
                $query->where('status', 'approved')
                    ->whereDate('from', '<=', $today)
                    ->whereDate('to', '>=', $today);
                break;

            case 'cancelled':
                $query->where('is_cancelled', true);
                break;

            case 'last_month':
                $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
                $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

                $query->where(function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                    $q->whereBetween('from', [$startOfLastMonth, $endOfLastMonth])
                        ->orWhereBetween('to', [$startOfLastMonth, $endOfLastMonth])
                        ->orWhere(function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                            $q->where('from', '<', $startOfLastMonth)
                                ->where('to', '>', $endOfLastMonth);
                        });
                });
                break;

            case 'greater_than_10_days':
                $query->where('days', '>=', 10);
                break;

            default:
                if ($from && $to) {
                    $query->where(function ($q) use ($from, $to) {
                        $q->whereBetween('from', [$from, $to])
                            ->orWhereBetween('to', [$from, $to])
                            ->orWhere(function ($q) use ($from, $to) {
                                $q->where('from', '<', $from)
                                    ->where('to', '>', $to);
                            });
                    });
                }
                break;
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('days', 'like', '%' . $search . '%')
                    ->orWhere('reason', 'like', '%' . $search . '%')
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->whereRaw("concat(name, ' ', surname) like ?", ['%' . $search . '%'])
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('surname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->whereRaw("concat(name, ' ', surname) like ?", ['%' . $search . '%'])
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('surname', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('leave_type', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }
}
