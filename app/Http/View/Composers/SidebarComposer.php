<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use  App\Models\{
    Allocation, Department, DepartmentHead, Leave, Loan,
    Payroll, Invoice, CreditNote, Bill, Requisition, TopUp,User,Purchase, Dispatch,
    GatePass, Fuel, FuelRequest, Trip, Transporter, Shift, TransportOrder, Recovery, Booking, Transfer, Retread
    // ... add all models you need here
};

class SidebarComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        $logged_in_user = User::find($user->id);

        $is_admin =  $logged_in_user->is_admin();

        $employee = $user?->employee;

        // Collections instead of foreach + push
        $department_names = $employee
            ? $employee->departments->pluck('name')->all()
            : [];

        $role_names = $user
            ? $user->roles->pluck('name')->all()
            : [];

        $rank_names = $employee
            ? $employee->ranks->pluck('name')->all()
            : [];

        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek();
        $endOfWeek   = $now->copy()->endOfWeek();

        // Example: a helper for weekly counts (you can DRY this further)
        $weeklyCount = function ($model, $extra = []) use ($startOfWeek, $endOfWeek) {
            return $model::where($extra)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();
        };

        $myAllocationCount = $employee
            ? Allocation::where('employee_id', $employee->id)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count()
            : 0;

        // Example departments (load once, reuse everywhere)
        $departments = Department::whereIn('name', [
            'HSEQ',
            'Workshop',
            'Finance',
            'Human Resources',
            'Security',
            'Transport & Logistics',
            'Stores',
        ])->get()->keyBy('name');

        // Example: leave stats
        $leavesPendingCount   = $weeklyCount(Leave::class,   ['status' => 'pending']);
        $leavesApprovedCount  = $weeklyCount(Leave::class,   ['status' => 'approved']);
        $leavesRejectedCount  = $weeklyCount(Leave::class,   ['status' => 'rejected']);
        $leavesDeletedCount   = Leave::onlyTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $hrDepartment   = $departments->get('Human Resources');
        $hrDeptHead     = $employee && $hrDepartment
            ? DepartmentHead::where('department_id', $hrDepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $hseq_department   = $departments->get('HSEQ');
        $wsdepartment   = $departments->get('Workshop');
        $wsdepartment_head     = $employee && $wsdepartment
            ? DepartmentHead::where('department_id', $wsdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $stdepartment   = $departments->get('Stores');
        $stdepartment_head     = $employee && $stdepartment
            ? DepartmentHead::where('department_id', $stdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $fndepartment   = $departments->get('Finance');
        $fndepartment_head     = $employee && $fndepartment
            ? DepartmentHead::where('department_id', $fndepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $tldepartment   = $departments->get('Transport & Logistics');
        $tldepartment_head     = $employee && $tldepartment
            ? DepartmentHead::where('department_id', $tldepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;

        // ... same pattern for Finance, HSEQ, etc.

        $isManagement      = in_array('Management', $rank_names);
        $isSuperAdmin      = in_array('Super Admin', $role_names);
        $isAdmin           = in_array('Admin', $role_names);
        $inHR              = in_array('Human Resources', $department_names);
        $inFinance         = in_array('Finance', $department_names);
        $inHSEQ            = in_array('HSEQ', $department_names);
        $inTransport       = in_array('Transport & Logistics', $department_names);

       
        $companyColor =
        $employee?->company->color ??
        $user?->company->color ??
        $user?->transporter?->company->color ??
        $user?->customer?->company->color ??
        $user?->agent?->company->color;

         $billsPendingCount = Bill::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $billsApprovedCount = Bill::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $billsRejectedCount = Bill::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $billsDeletedCount = Bill::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();

        $leavesPendingCount = Leave::where('status','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $leavesApprovedCount = Leave::where('status','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $leavesRejectedCount = Leave::where('status','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $leavesDeletedCount = Leave::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $loansPendingCount = Loan::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $loansApprovedCount = Loan::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $loansRejectedCount = Loan::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $loansDeletedCount = Loan::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $payrollsPendingCount = Payroll::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $payrollsApprovedCount = Payroll::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $payrollsRejectedCount = Payroll::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $payrollsDeletedCount = Payroll::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $invoicesPendingCount = Invoice::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $invoicesApprovedCount = Invoice::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $invoicesRejectedCount = Invoice::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $invoicesDeletedCount = Invoice::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $credit_notesPendingCount = CreditNote::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $credit_notesApprovedCount = CreditNote::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $credit_notesRejectedCount = CreditNote::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $credit_notesDeletedCount = CreditNote::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $requisitionsPendingCount = Requisition::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $requisitionsApprovedCount = Requisition::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $requisitionsRejectedCount = Requisition::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $requisitionsDeletedCount = Requisition::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $asset_purchasesPendingCount = Purchase::where('authorization','pending')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $asset_purchasesApprovedCount = Purchase::where('authorization','approved')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $asset_purchasesRejectedCount = Purchase::where('authorization','rejected')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $asset_purchasesDeletedCount = Purchase::onlyTrashed()
        ->where('department','asset')
        ->whereDate('created_at', Carbon::today())->get()->count();

        $asset_dispatchesPendingCount = Dispatch::where('authorization','pending')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $asset_dispatchesApprovedCount = Dispatch::where('authorization','approved')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $asset_dispatchesRejectedCount = Dispatch::where('authorization','rejected')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();

        $gate_passesPendingCount = GatePass::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $gate_passesApprovedCount = GatePass::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $gate_passesRejectedCount = GatePass::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
         $top_upsPendingCount = TopUp::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $top_upsApprovedCount = TopUp::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $top_upsRejectedCount = TopUp::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelsPendingCount = Fuel::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelsApprovedCount = Fuel::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelsRejectedCount = Fuel::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelsDelectedCount = Fuel::onlyTrashed()
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelRequesitionPendingCount = FuelRequest::where('status','pending')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $fuelRequesitionApprovedCount = FuelRequest::where('status','approved')
        ->whereDate('created_at', Carbon::today())->get()->count();
        //  ->where('created_at', '>', Carbon::now()->startOfWeek())
        //  ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuelRequesitionRejectedCount = FuelRequest::where('status','rejected')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $fuelRequesitionDelectedCount = FuelRequest::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tripPendingCount = Trip::where('authorization','pending')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tripApprovedCount = Trip::where('authorization','approved')
        ->whereDate('created_at', Carbon::today())->get()->count();
        //  ->where('created_at', '>', Carbon::now()->startOfWeek())
        //  ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tripCount = Trip::where('authorization','rejected')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tripCount = Trip::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();

        $transportersPendingCount = Transporter::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $transportersApprovedCount = Transporter::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $transportersRejectedCount = Transporter::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $transportersDeletedCount = Transporter::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();

        $shiftsPendingCount = Shift::where('authorization','pending')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $shiftsApprovedCount = Shift::where('authorization','approved')
        ->whereDate('created_at', Carbon::today())->get()->count();
        //  ->where('created_at', '>', Carbon::now()->startOfWeek())
        //  ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $shiftsRejectedCount = Shift::where('authorization','rejected')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tripsPendingCount = Trip::where('authorization','pending')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tripsApprovedCount = Trip::where('authorization','approved')
        ->whereDate('created_at', Carbon::today())->get()->count();
        //  ->where('created_at', '>', Carbon::now()->startOfWeek())
        //  ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tripsRejectedCount = Trip::where('authorization','rejected')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $transportOrdersCount = TransportOrder::whereDate('created_at', Carbon::today())->get()->count();
        $tripsDelectedCount = Trip::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $logistics_gate_passesPendingCount = GatePass::where('logistics_authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $logistics_gate_passesApprovedCount = GatePass::where('logistics_authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $logistics_gate_passesRejectedCount = GatePass::where('logistics_authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();

         $recoveriesPendingCount = Recovery::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $recoveriesApprovedCount = Recovery::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $recoveriesRejectedCount = Recovery::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $recoveriesDeletedCount = Recovery::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
         $bookingsPendingCount = Booking::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $bookingsApprovedCount = Booking::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $bookingsRejectedCount = Booking::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $bookingsDeletedCount = Booking::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();

        $workshop_gate_passesPendingCount = GatePass::where('workshop_authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $workshop_gate_passesApprovedCount = GatePass::where('workshop_authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $workshop_gate_passesRejectedCount = GatePass::where('workshop_authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();

        $inventory_transfersPendingCount = Transfer::where('authorization','pending')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $inventory_transfersApprovedCount = Transfer::where('authorization','approved')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $inventory_transfersRejectedCount = Transfer::where('authorization','rejected')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $inventory_transfersDeletedCount = Transfer::onlyTrashed()
        ->where('department','inventory')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $inventory_purchasesPendingCount = Purchase::where('authorization','pending')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $inventory_purchasesApprovedCount = Purchase::where('authorization','approved')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $inventory_purchasesRejectedCount = Purchase::where('authorization','rejected')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $inventory_purchasesDeletedCount = Purchase::onlyTrashed()
        ->where('department','inventory')
        ->whereDate('created_at', Carbon::today())->get()->count();
         $inventory_dispatchesPendingCount = Dispatch::where('authorization','pending')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $inventory_dispatchesApprovedCount = Dispatch::where('authorization','approved')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $inventory_dispatchesRejectedCount = Dispatch::where('authorization','rejected')
        ->where('department','inventory')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
         $tyre_transfersPendingCount = Transfer::where('authorization','pending')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $tyre_transfersApprovedCount = Transfer::where('authorization','approved')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tyre_transfersRejectedCount = Transfer::where('authorization','rejected')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tyre_transfersDeletedCount = Transfer::onlyTrashed()
        ->where('department','tyre')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tyre_purchasesPendingCount = Purchase::where('authorization','pending')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $tyre_purchasesApprovedCount = Purchase::where('authorization','approved')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tyre_purchasesRejectedCount = Purchase::where('authorization','rejected')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tyre_purchasesDeletedCount = Purchase::onlyTrashed()
        ->where('department','tyre')
        ->whereDate('created_at', Carbon::today())->get()->count();
        $retreadsPendingCount = Retread::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $retreadsApprovedCount = Retread::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $retreadsRejectedCount = Retread::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $retreadsDeletedCount = Retread::onlyTrashed()
        ->whereDate('created_at', Carbon::today())->get()->count();
        $tyre_dispatchesPendingCount = Dispatch::where('authorization','pending')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $tyre_dispatchesApprovedCount = Dispatch::where('authorization','approved')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $tyre_dispatchesRejectedCount = Dispatch::where('authorization','rejected')
        ->where('department','tyre')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();


        $view->with([
            'user'         => $user,
            'employee'     => $employee,
            'department_names'     => $department_names,
            'role_names'           => $role_names,
            'rank_names'           => $rank_names,
            'myAllocationCount'   => $myAllocationCount,
            'departmentsMap'      => $departments,
            'leavesPendingCount'  => $leavesPendingCount,
            'leavesApprovedCount' => $leavesApprovedCount,
            'leavesRejectedCount' => $leavesRejectedCount,
            'leavesDeletedCount'  => $leavesDeletedCount,
            'hrDeptHead'          => $hrDeptHead,
            'inTransport'          => $inTransport,
            'inHSEQ'          => $inHSEQ,
            'inFinance'          => $inFinance,
            'inHR'          => $inHR,
            'isAdmin'          => $isAdmin,
            'isSuperAdmin'          => $isSuperAdmin,
            'isManagement'          => $isManagement,
            'companyColor'          => $companyColor,
            'tldepartment_head'          => $tldepartment_head,
            'fndepartment_head'          => $fndepartment_head,
            'stdepartment_head'          => $stdepartment_head,
            'wsdepartment_head'          => $wsdepartment_head,
            'is_admin'          => $is_admin,
            'billsDeletedCount'          => $billsDeletedCount,
            'billsRejectedCount'          => $billsRejectedCount,
            'billsApprovedCount'          => $billsApprovedCount,
            'billsPendingCount'          => $billsPendingCount,
            'leavesDeletedCount'          => $leavesDeletedCount,
            'leavesRejectedCount'          => $leavesRejectedCount,
            'leavesApprovedCount'          => $leavesApprovedCount,
            'leavesPendingCount'          => $leavesPendingCount,
            'loansDeletedCount'          => $loansDeletedCount,
            'loansRejectedCount'          => $loansRejectedCount,
            'loansApprovedCount'          => $loansApprovedCount,
            'loansPendingCount'          => $loansPendingCount,
            'payrollsDeletedCount'          => $payrollsDeletedCount,
            'payrollsRejectedCount'          => $payrollsRejectedCount,
            'payrollsApprovedCount'          => $payrollsApprovedCount,
            'payrollsPendingCount'          => $payrollsPendingCount,
            'invoicesDeletedCount'          => $invoicesDeletedCount,
            'invoicesRejectedCount'          => $invoicesRejectedCount,
            'invoicesApprovedCount'          => $invoicesApprovedCount,
            'invoicesPendingCount'          => $invoicesPendingCount,
            'credit_notesDeletedCount'          => $credit_notesDeletedCount,
            'credit_notesRejectedCount'          => $credit_notesRejectedCount,
            'credit_notesApprovedCount'          => $credit_notesApprovedCount,
            'credit_notesPendingCount'          => $credit_notesPendingCount,
            'requisitionsDeletedCount'          => $requisitionsDeletedCount,
            'requisitionsRejectedCount'          => $requisitionsRejectedCount,
            'requisitionsApprovedCount'          => $requisitionsApprovedCount,
            'requisitionsPendingCount'          => $requisitionsPendingCount,
            'asset_purchasesDeletedCount'          => $asset_purchasesDeletedCount,
            'asset_purchasesRejectedCount'          => $asset_purchasesRejectedCount,
            'asset_purchasesApprovedCount'          => $asset_purchasesApprovedCount,
            'asset_purchasesPendingCount'          => $asset_purchasesPendingCount,
            'inventory_purchasesDeletedCount'          => $inventory_purchasesDeletedCount,
            'inventory_purchasesRejectedCount'          => $inventory_purchasesRejectedCount,
            'inventory_purchasesApprovedCount'          => $inventory_purchasesApprovedCount,
            'inventory_purchasesPendingCount'          => $inventory_purchasesPendingCount,
            'inventory_dispatchesRejectedCount'          => $inventory_dispatchesRejectedCount,
            'inventory_dispatchesApprovedCount'          => $inventory_dispatchesApprovedCount,
            'inventory_dispatchesPendingCount'          => $inventory_dispatchesPendingCount,
            'asset_dispatchesRejectedCount'          => $asset_dispatchesRejectedCount,
            'asset_dispatchesApprovedCount'          => $asset_dispatchesApprovedCount,
            'asset_dispatchesPendingCount'          => $asset_dispatchesPendingCount,
            'tyre_dispatchesRejectedCount'          => $tyre_dispatchesRejectedCount,
            'tyre_dispatchesApprovedCount'          => $tyre_dispatchesApprovedCount,
            'tyre_dispatchesPendingCount'          => $tyre_dispatchesPendingCount,
            'gate_passesRejectedCount'          => $gate_passesRejectedCount,
            'gate_passesApprovedCount'          => $gate_passesApprovedCount,
            'gate_passesPendingCount'          => $gate_passesPendingCount,
            'top_upsRejectedCount'          => $top_upsRejectedCount,
            'top_upsApprovedCount'          => $top_upsApprovedCount,
            'top_upsPendingCount'          => $top_upsPendingCount,
            'fuelsDelectedCount'          => $fuelsDelectedCount,
            'fuelsRejectedCount'          => $fuelsRejectedCount,
            'fuelsApprovedCount'          => $fuelsApprovedCount,
            'fuelsPendingCount'          => $fuelsPendingCount,
            'fuelRequesitionDelectedCount'          => $fuelRequesitionDelectedCount,
            'fuelRequesitionRejectedCount'          => $fuelRequesitionRejectedCount,
            'fuelRequesitionApprovedCount'          => $fuelRequesitionApprovedCount,
            'fuelRequesitionPendingCount'          => $fuelRequesitionPendingCount,
            'tripCount'          => $tripCount,
            'tripRejectedCount'          => $tripCount,
            'tripApprovedCount'          => $tripApprovedCount,
            'tripPendingCount'          => $tripPendingCount,
            'transportersDeletedCount'          => $transportersDeletedCount,
            'transportersRejectedCount'          => $transportersRejectedCount,
            'transportersApprovedCount'          => $transportersApprovedCount,
            'transportersPendingCount'          => $transportersPendingCount,
            'shiftsRejectedCount'          => $shiftsRejectedCount,
            'shiftsApprovedCount'          => $shiftsApprovedCount,
            'shiftsPendingCount'          => $shiftsPendingCount,
            'tripsRejectedCount'          => $tripsRejectedCount,
            'tripsApprovedCount'          => $tripsApprovedCount,
            'tripsPendingCount'          => $tripsPendingCount,
            'transportOrdersCount'          => $transportOrdersCount,
            'tripsDelectedCount'          => $tripsDelectedCount,
            'logistics_gate_passesRejectedCount'          => $logistics_gate_passesRejectedCount,
            'logistics_gate_passesApprovedCount'          => $logistics_gate_passesApprovedCount,
            'logistics_gate_passesPendingCount'          => $logistics_gate_passesPendingCount,
            'workshop_gate_passesRejectedCount'          => $workshop_gate_passesRejectedCount,
            'workshop_gate_passesApprovedCount'          => $workshop_gate_passesApprovedCount,
            'workshop_gate_passesPendingCount'          => $workshop_gate_passesPendingCount,

            'recoveriesDeletedCount'          => $recoveriesDeletedCount,
            'recoveriesRejectedCount'          => $recoveriesRejectedCount,
            'recoveriesApprovedCount'          => $recoveriesApprovedCount,
            'recoveriesPendingCount'          => $recoveriesPendingCount,
            'bookingsDeletedCount'          => $bookingsDeletedCount,
            'bookingsRejectedCount'          => $bookingsRejectedCount,
            'bookingsApprovedCount'          => $bookingsApprovedCount,
            'bookingsPendingCount'          => $bookingsPendingCount,
            'inventory_transfersDeletedCount'          => $inventory_transfersDeletedCount,
            'inventory_transfersRejectedCount'          => $inventory_transfersRejectedCount,
            'inventory_transfersApprovedCount'          => $inventory_transfersApprovedCount,
            'inventory_transfersPendingCount'          => $inventory_transfersPendingCount,
            'tyre_purchasesDeletedCount'          => $tyre_purchasesDeletedCount,
            'tyre_purchasesRejectedCount'          => $tyre_purchasesRejectedCount,
            'tyre_purchasesApprovedCount'          => $tyre_purchasesApprovedCount,
            'tyre_purchasesPendingCount'          => $tyre_purchasesPendingCount,
            'tyre_transfersRejectedCount'          => $tyre_transfersRejectedCount,
            'tyre_transfersApprovedCount'          => $tyre_transfersApprovedCount,
            'tyre_transfersPendingCount'          => $tyre_transfersPendingCount,
            'retreadsDeletedCount'          => $retreadsDeletedCount,
            'retreadsRejectedCount'          => $retreadsRejectedCount,
            'retreadsApprovedCount'          => $retreadsApprovedCount,
            'retreadsPendingCount'          => $retreadsPendingCount,
            // add other counters (loans, payrolls, invoices, bills, etc.)
        ]);
    }
}