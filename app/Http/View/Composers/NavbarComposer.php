<?php

namespace App\Http\View\Composers;

use App\Models\Attendance;
use App\Models\Bill;
use App\Models\Booking;
use App\Models\CreditNote;
use App\Models\Dispatch;
use App\Models\Fitness;
use App\Models\Fuel;
use App\Models\FuelRequest;
use App\Models\GatePass;
use App\Models\Horse;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\Payroll;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Rental;
use App\Models\Requisition;
use App\Models\Retread;
use App\Models\Ticket;
use App\Models\TopUp;
use App\Models\Trailer;
use App\Models\Transfer;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WasteCollection;
use App\Models\WasteDisposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $user = User::find(Auth::user()->id);

        $now         = now();                     // uses app timezone (Africa/Harare)

        $pendingCounts = [
            'trips'            => Trip::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'bookings'         => Booking::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'invoices'         => Invoice::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'bills'            => Bill::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'credit_notes'     => CreditNote::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'purchases'        => Purchase::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'transfers'        => Transfer::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'dispatches'       => Dispatch::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'retreads'         => Retread::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'recoveries'       => Recovery::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'rentals'          => Rental::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'topups'           => TopUp::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            // 'fuel_requests'    => FuelRequest::where('authorization', 'pending')->count(),
            'gate_passes'      => GatePass::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'waste_collections'=> WasteCollection::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'waste_disposals'  => WasteDisposal::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            
            'requisitions'     => Requisition::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'payrolls'         => Payroll::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'fuels'         => Fuel::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'loans'            => Loan::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'leaves'           => Leave::where('management_decision', 'pending')->whereYear('created_at',date('Y'))->count(),
            'attendances'      => Attendance::where('authorization', 'pending')->whereYear('created_at',date('Y'))->count(),
            'overdue_tickets' => Booking::query()
                ->where('status', 1)   // or whatever your "open" status is
                ->where('authorization', 'approved') // only approved bookings
                ->whereYear('in_date', date('Y'))
                ->whereNotNull('estimated_out_date')
                ->whereNotNull('estimated_out_time')
                ->whereRaw(
                    "TIMESTAMP(estimated_out_date, estimated_out_time) < ?",
                    [$now]  // Carbon is cast to 'Y-m-d H:i:s'
                )
                ->count()
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
          
            $expiry_date = Carbon::now()->endOfMonth();

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

        $mileage_reminders = collect();

        $isWorkshopUser = (in_array('Workshop', $department_names) && in_array('Admin', $role_names)) 
                    || in_array('Super Admin', $role_names);

        if ($isWorkshopUser) {
            $threshhold = 2500;

            $horses = Horse::whereNotNull('next_service')
                ->whereNotNull('mileage')
                ->where('next_service', '>', 0)
                ->where('mileage', '>', 0)
                ->get()
                ->map(function ($horse) {
                    $horse->mileage_gap    = $horse->next_service - $horse->mileage;
                    $horse->asset_type     = 'horse';
                    $horse->asset_label    = $horse->registration_number;
                    $horse->asset_route    = route('horses.show', $horse->id);
                    return $horse;
                })
                ->filter(fn($h) => $h->mileage_gap <= $threshhold);

            $vehicles = Vehicle::whereNotNull('next_service')
                ->whereNotNull('mileage')
                ->where('next_service', '>', 0)
                ->where('mileage', '>', 0)
                ->get()
                ->map(function ($vehicle) {
                    $vehicle->mileage_gap  = $vehicle->next_service - $vehicle->mileage;
                    $vehicle->asset_type   = 'vehicle';
                    $vehicle->asset_label  = $vehicle->registration_number;
                    $vehicle->asset_route  = route('vehicles.show', $vehicle->id);
                    return $vehicle;
                })
                ->filter(fn($v) => $v->mileage_gap <= $threshhold);

            $trailers = Trailer::whereNotNull('next_service')
                ->whereNotNull('mileage')
                ->where('next_service', '>', 0)
                ->where('mileage', '>', 0)
                ->get()
                ->map(function ($trailer) {
                    $trailer->mileage_gap  = $trailer->next_service - $trailer->mileage;
                    $trailer->asset_type   = 'trailer';
                    $trailer->asset_label  = $trailer->registration_number;
                    $trailer->asset_route  = route('trailers.show', $trailer->id);
                    return $trailer;
                })
                ->filter(fn($t) => $t->mileage_gap <= $threshhold);

            $mileage_reminders = $horses
                ->concat($vehicles)
                ->concat($trailers)
                ->sortBy('mileage_gap')
                ->values();
        }

        $reminders_count = $reminders->count()
            + $expired_reminders->count()
            + $mileage_reminders->count();

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
            'mileage_reminders' => $mileage_reminders,
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
                'fuels'          => $pendingCounts['fuels'] ?? 0,
                'loans'             => $pendingCounts['loans'] ?? 0,
                'leaves'            => $pendingCounts['leaves'] ?? 0,
                'attendances'       => $pendingCounts['attendances'] ?? 0,
                'overdue_tickets'       => $pendingCounts['overdue_tickets'] ?? 0,
            ],
        ]);
    }
}