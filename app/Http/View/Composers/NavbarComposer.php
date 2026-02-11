<?php

namespace App\Http\View\Composers;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Fuel;

use App\Models\Loan;
use App\Models\Trip;
use App\Models\User;
use App\Models\Leave;
use App\Models\TopUp;
use App\Models\Rental;
use App\Models\Booking;
use App\Models\Fitness;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\Retread;
use App\Models\Dispatch;
use App\Models\GatePass;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Transfer;
use Illuminate\View\View;
use App\Models\Attendance;
use App\Models\CreditNote;
use App\Models\FuelRequest;
use App\Models\Requisition;
use App\Models\WasteDisposal;
use App\Models\WasteCollection;
use Illuminate\Support\Facades\Auth;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $user = User::find(Auth::user()->id);

              $pendingCounts = [
            'trips'            => Trip::where('authorization', 'pending')->count(),
            'bookings'         => Booking::where('authorization', 'pending')->count(),
            'invoices'         => Invoice::where('authorization', 'pending')->count(),
            'bills'            => Bill::where('authorization', 'pending')->count(),
            'credit_notes'     => CreditNote::where('authorization', 'pending')->count(),
            'purchases'        => Purchase::where('authorization', 'pending')->count(),
            'transfers'        => Transfer::where('authorization', 'pending')->count(),
            'dispatches'       => Dispatch::where('authorization', 'pending')->count(),
            'retreads'         => Retread::where('authorization', 'pending')->count(),
            'recoveries'       => Recovery::where('authorization', 'pending')->count(),
            'rentals'          => Rental::where('authorization', 'pending')->count(),
            'topups'           => TopUp::where('authorization', 'pending')->count(),
            // 'fuel_requests'    => FuelRequest::where('authorization', 'pending')->count(),
            'gate_passes'      => GatePass::where('authorization', 'pending')->count(),
            'waste_collections'=> WasteCollection::where('authorization', 'pending')->count(),
            'waste_disposals'  => WasteDisposal::where('authorization', 'pending')->count(),
            'requisitions'     => Requisition::where('authorization', 'pending')->count(),
            'payrolls'         => Payroll::where('authorization', 'pending')->count(),
            'loans'            => Loan::where('authorization', 'pending')->count(),
            'leaves'           => Leave::where('management_decision', 'pending')->count(),
            'attendances'      => Attendance::where('authorization', 'pending')->count(),
        ];

        // If guest, just share empties (prevents errors)
        if (! $user) {
            $view->with([
                'user' => null,
                'employee' => null,
                'company' => null,
                'department_names' => [],
                'role_names' => [],
                'license' => null,
                'reminders' => collect(),
                'expired_reminders' => collect(),
                'reminders_count' => 0,
            ]);
            return;
        }

        // Avoid N+1: load everything your navbar touches
        $user->loadMissing([
            'employee.company',
            'employee.departments',
            'roles',
            'company',
            'transporter.company',
            'customer.company',
            'agent.company',
        ]);

        $employee = $user->employee;

        // Determine company (same fallback logic you had, but centralized)
        $company =
            optional($employee)->company
            ?? $user->company
            ?? optional($user->transporter)->company
            ?? optional($user->customer)->company
            ?? optional($user->agent)->company;

        $department_names = $employee
            ? $employee->departments->pluck('name')->values()->all()
            : [];

        $role_names = $user->roles->pluck('name')->values()->all();

        // -----------------------------
        // License expiry message (employee/company based)
        // -----------------------------
        $license = null;

        if ($employee && optional($employee->company)->exists) {
            $expiresRaw  = $employee->company->expiry_date;
            $expiry_date = $expiresRaw ? Carbon::parse($expiresRaw) : Carbon::now()->endOfMonth();

            // signed diff (negative => already expired)
            $diff = Carbon::today()->diffInDays($expiry_date, false);

            if ($diff > 7) {
                $license = ['color' => 'green', 'text' => "License expires in {$diff} day(s)"];
            } elseif ($diff <= 7 && $diff > 1) {
                $license = ['color' => 'orange', 'text' => "License expires in {$diff} day(s)"];
            } elseif ($diff === 1) {
                $license = ['color' => 'red', 'text' => "License expires in {$diff} day(s)"];
            } elseif ($diff === 0) {
                $license = ['color' => 'red', 'text' => "License expires today"];
            } else {
                $license = ['color' => 'red', 'text' => "License expired ".$expiry_date->format('Y-m-d')];
            }
        }

        // -----------------------------
        // Reminders (IMPORTANT: grouped OR logic)
        // Your original query had orWhere chains that can “leak” results.
        // This version correctly applies user_id/closed to ALL OR branches.
        // -----------------------------
        $today = Carbon::today();
        $now   = now();

        $validRemindersQuery = Fitness::query()
            ->where('user_id', $user->id)
            ->where('closed', 0)
            ->where(function ($q) use ($today, $now) {
                $q->where(function ($q) use ($today, $now) {
                    $q->whereNotNull('first_reminder_at')
                      ->whereDate('first_reminder_at', '<=', $today)
                      ->where('first_reminder_at_status', false)
                      ->where('expires_at', '>=', $now);
                })
                ->orWhere(function ($q) use ($today, $now) {
                    $q->whereNotNull('second_reminder_at')
                      ->whereDate('second_reminder_at', '<=', $today)
                      ->where('second_reminder_at_status', false)
                      ->where('expires_at', '>=', $now);
                })
                ->orWhere(function ($q) use ($today, $now) {
                    $q->whereNotNull('third_reminder_at')
                      ->whereDate('third_reminder_at', '<=', $today)
                      ->where('third_reminder_at_status', false)
                      ->where('expires_at', '>=', $now);
                });
            })
            ->with(['reminder_item', 'horse', 'vehicle', 'trailer', 'employee'])
            ->orderBy('expires_at', 'asc');

        $reminders = $validRemindersQuery->get();

        $expired_reminders = Fitness::query()
            ->where('user_id', $user->id)
            ->where('closed', 0)
            ->whereDate('expires_at', '<', $today)
            ->with(['reminder_item', 'horse', 'vehicle', 'trailer', 'employee'])
            ->orderBy('expires_at', 'desc')
            ->get();

        $reminders_count = $reminders->count() + $expired_reminders->count();

        // Share everything to the navbar view
        $view->with([
            'user' => $user,
            'employee' => $employee,
            'company' => $company,
            'department_names' => $department_names,
            'role_names' => $role_names,
            'license' => $license,
            'reminders' => $reminders,
            'expired_reminders' => $expired_reminders,
            'reminders_count' => $reminders_count,
           
            // Pending authorizations (Operations Control Tower)
            'pendingCounts' => [
                'trips'             => $pendingCounts['trips'] ?? 0,
                'bookings'          => $pendingCounts['bookings'] ?? 0,
                'invoices'          => $pendingCounts['invoices'] ?? 0,
                'bills'             => $pendingCounts['bills'] ?? 0,
                'credit_notes'      => $pendingCounts['credit_notes'] ?? 0,
                'purchases'         => $pendingCounts['purchases'] ?? 0,
                'transfers'         => $pendingCounts['transfers'] ?? 0,
                'dispatches'        => $pendingCounts['dispatches'] ?? 0,
                'retreads'          => $pendingCounts['retreads'] ?? 0,
                'recoveries'        => $pendingCounts['recoveries'] ?? 0,
                'rentals'           => $pendingCounts['rentals'] ?? 0,
                'topups'            => $pendingCounts['topups'] ?? 0,
                // 'fuel_requests'     => $pendingCounts['fuel_requests'] ?? 0,
                'gate_passes'       => $pendingCounts['gate_passes'] ?? 0,
                'waste_collections' => $pendingCounts['waste_collections'] ?? 0,
                'waste_disposals'   => $pendingCounts['waste_disposals'] ?? 0,
                'requisitions'      => $pendingCounts['requisitions'] ?? 0,
                'payrolls'          => $pendingCounts['payrolls'] ?? 0,
                'loans'             => $pendingCounts['loans'] ?? 0,
                'leaves'            => $pendingCounts['leaves'] ?? 0,
                'attendances'       => $pendingCounts['attendances'] ?? 0,
            ],
        ]);
    }
}