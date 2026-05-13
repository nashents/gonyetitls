<?php

namespace App\Http\View\Composers;

use App\Models\{
    Allocation, Attendance, Agent, Bill, Booking, Company, CreditNote, Customer, Department, DepartmentHead, Dispatch, Fuel, FuelRequest,
    GatePass, Incident, Invoice, Leave, Loan, ModuleGroup, Payroll, Purchase, Recovery, Rental, Requisition, Retread, Shift, TopUp, Transfer,
    Transporter, TransportOrder, Trip, User, WasteCollection, WasteDisposal,
};

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        $logged_in_user = User::find($user->id);
        $isSystemAdmin =  $logged_in_user->is_admin();
        $employee = $user?->employee;
        $not_driver = !$employee->driver;
        $has_vehicle_assignment = $employee->vehicle_assignment;

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
        $today       = Carbon::today();
        
       

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

      

        $hrdepartment   = $departments->get('Human Resources');
        $hrdepartment_head     = $employee && $hrdepartment
            ? DepartmentHead::where('department_id', $hrdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $hseq_department   = $departments->get('HSEQ');
        $hseqdepartment_head     = $employee && $hseq_department
            ? DepartmentHead::where('department_id', $hseq_department->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
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
       
        $sdepartment   = $departments->get('Security');
        $sdepartment_head     = $employee && $sdepartment
            ? DepartmentHead::where('department_id', $sdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $isHOD = $employee
            ? DepartmentHead::where('employee_id', $employee->id)
                  ->first()
            : null;

        // ... same pattern for Finance, HSEQ, etc.

        $isManagement      = in_array('Management', $rank_names);
        $isEmployee      = in_array('Employee', $rank_names);
        $isDirector      = in_array('Directors', $rank_names);
        $isSuperAdmin      = in_array('Super Admin', $role_names);
        $isAdmin           = in_array('Admin', $role_names);
        $isUser           = in_array('User', $role_names);
        $inHR              = in_array('Human Resources', $department_names);
        $inFinance         = in_array('Finance', $department_names);
        $inHSEQ            = in_array('HSEQ', $department_names);
        $inSecurity            = in_array('Security', $department_names);
        $inTransport       = in_array('Transport & Logistics', $department_names);
        $inStores       = in_array('Stores', $department_names);
        $inWorkshop       = in_array('Workshop', $department_names);
  
       
        $companyColor =
        $employee?->company->color ??
        $user?->company->color ??
        $user?->transporter?->company->color ??
        $user?->customer?->company->color ??
        $user?->agent?->company->color;
        $user?->company->color ??
        $user?->transporter?->company->color ??
        $user?->customer?->company->color ??
        $user?->agent?->company->color;
        $user?->company->color ??
        $user?->transporter?->company->color ??
        $user?->customer?->company->color ??
        $user?->agent?->company->color;

         // Permission: who can see ALL leaves
        $canSeeAllLeaves =
            $isSuperAdmin ||
            ($isAdmin && $inHR) ||
            ($isManagement && $inHR);

        // Decide which decision column to use
        $decisionColumn = $canSeeAllLeaves ? 'management_decision' : 'hod_decision';

        // Helper: base query for this user’s visibility + week range
        $weeklyLeavesQuery = function () use ($canSeeAllLeaves, $isHOD, $startOfWeek, $endOfWeek) {
            $query = Leave::query();

            if ($canSeeAllLeaves) {
                // Management: all leaves
                // Optionally enforce that management_decision is not null:
                // $query->whereNotNull('management_decision');
            } elseif (isset($isHOD)) {
                // HOD: only their department
                $query->where('department_id', $isHOD->department->id);
            } else {
                // No access → always zero results
                return Leave::query()->whereRaw('1 = 0');
            }

            return $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        };

        // Counts by decision (weekly)
        $leavesPendingCount = $weeklyLeavesQuery()
            ->where($decisionColumn, 'pending')
            ->count();

        $leavesApprovedCount = $weeklyLeavesQuery()
            ->where($decisionColumn, 'approved')
            ->count();

        $leavesRejectedCount = $weeklyLeavesQuery()
            ->where($decisionColumn, 'rejected')
            ->count();

        // Deleted leaves (today), still respecting visibility
        $deletedQuery = Leave::onlyTrashed();

        if ($canSeeAllLeaves) {
            // management sees all deleted
        } elseif (isset($isHOD)) {
            $deletedQuery->where('department_id', $isHOD->department->id);
        } else {
            $deletedQuery->whereRaw('1 = 0');
        }

        $leavesDeletedCount = $deletedQuery
            ->whereDate('deleted_at', $today)   // better than created_at for deletions
            ->count();


        
        $transportOrdersPendingCount = TransportOrder::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $transportOrdersApprovedCount = TransportOrder::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $transportOrdersRejectedCount = TransportOrder::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        
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

    

        $attendancesPendingCount = Attendance::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $attendancesApprovedCount = Attendance::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $attendancesRejectedCount = Attendance::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        
        $incidentsPendingCount = Incident::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $incidentsApprovedCount = Incident::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $incidentsRejectedCount = Incident::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
       
     
        $waste_disposalsPendingCount = WasteDisposal::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $waste_disposalsApprovedCount = WasteDisposal::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $waste_disposalsRejectedCount = WasteDisposal::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
       
        $waste_collectionsPendingCount = WasteCollection::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $waste_collectionsApprovedCount = WasteCollection::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $waste_collectionsRejectedCount = WasteCollection::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
       
        $fuel_requestsPendingCount = FuelRequest::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuel_requestsApprovedCount = FuelRequest::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $fuel_requestsRejectedCount = FuelRequest::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();

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
        
        $rentalsPendingCount = Rental::where('authorization','pending')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $rentalsApprovedCount = Rental::where('authorization','approved')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $rentalsRejectedCount = Rental::where('authorization','rejected')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $rentalsDeletedCount = Rental::onlyTrashed()
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
       
        $asset_transfersPendingCount = Transfer::where('authorization','pending')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        // ->whereDate('created_at', Carbon::today())->get()->count();
        $asset_transfersApprovedCount = Transfer::where('authorization','approved')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
        $asset_transfersRejectedCount = Transfer::where('authorization','rejected')
        ->where('department','asset')
        ->where('created_at', '>', Carbon::now()->startOfWeek())
        ->where('created_at', '<', Carbon::now()->endOfWeek())->get()->count();
     
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

        $jobCardsCount = $employee->tickets
                                ->where('created_at', '>', Carbon::now()->startOfWeek())
                                ->where('created_at', '<', Carbon::now()->endOfWeek())->where('status',1)->count();
        $inspectionsCount = $employee->inspections
                            ->where('created_at', '>', Carbon::now()->startOfWeek())
                            ->where('created_at', '<', Carbon::now()->endOfWeek())->where('status',1)->count();
        $companies = Company::where('type','!=','admin')->get();
        $admin_company = Company::where('type','admin')->get()->first();

        $in_transport_admin = $isAdmin && $inTransport;
        $in_transport_management = $isManagement && $inTransport;

           // Build your context keys ONCE (map your booleans + ids here)
        $ctx = [
             // roles / flags (use what you computed, not session())
            'isAdmin'       => (bool) $isAdmin,
            'isSuperAdmin'  => (bool) $isSuperAdmin,
            'isHOD'  => (bool) $isHOD,
            'isUser'        => (bool) $isUser,
            'isManagement'  => (bool) $isManagement,
            'isEmployee'    => (bool) $isEmployee,
            'isDirector'    => (bool) $isDirector,
            'isDriver'      => (bool) ($user?->driver ?? false),
            'isSystemAdmin'      => (bool) $isSystemAdmin,
            'in_transport_admin' => (bool) $in_transport_admin,
            'in_transport_management' => (bool) $in_transport_management,
            'inHR'          => (bool) $inHR,
            'inFinance'     => (bool) $inFinance,
            'inTransport'   => (bool) $inTransport,
            'inHSEQ'        => (bool) $inHSEQ,
            'inSecurity'    => (bool) $inSecurity,
            'inStores'      => (bool) $inStores,
            'inWorkshop'    => (bool) $inWorkshop,
            'isNotDriver'    => (bool) $not_driver,
            'hasStoresDeptHead'    => (bool) $stdepartment_head,
            'hasWorkshopDeptHead'    => (bool) $wsdepartment_head,
            'hasHRDeptHead'    => (bool) $hrdepartment_head,
            'hasHSEQDeptHead'    => (bool) $hseqdepartment_head,
            'hasTLDeptHead'    => (bool) $tldepartment_head,
            'hasFinanceDeptHead'    => (bool) $fndepartment_head,
            'hasVehicleAssignment'    => (bool) ($employee?->vehicleAssignments?->where('status',1)->count() > 0),
            'admin_company_id' => $admin_company?->id,
            'driver_id'        => $employee?->driver?->id,     // only if you have a driver relation
            'department_id'    => $employee?->departments->first()?->id,

            'roles'       => $role_names ?? [],        // from DB/session/computed user roles
            'departments' => $department_names ?? [],  // from DB/session/computed user departments
            'ranks'       => $rank_names ?? [],  
            // ids for placeholders (MOST IMPORTANT PART)
            'user_id'       => $user?->id,
            'employee_id'   => $employee?->id ?? $user?->employee_id,  // ✅ reliable
            'company_id'   => $employee?->company_id ?? $user?->employee?->company_id,  // ✅ reliable
            'hseq_department_id' => $hseq_department?->id,
        ];


        // Your badge map (connect to your real computed counts)
        $menuBadges = [
        // Leave Management
        'leaves_pending_count'  => (int) ($leavesPendingCount ?? 0),
        'leaves_approved_count' => (int) ($leavesApprovedCount ?? 0),
        'leaves_rejected_count' => (int) ($leavesRejectedCount ?? 0),
      
        'fuel_requests_pending_count'  => (int) ($fuel_requestsPendingCount ?? 0),
        'fuel_requests_approved_count' => (int) ($fuel_requestsApprovedCount ?? 0),
        'fuel_requests_rejected_count' => (int) ($fuel_requestsRejectedCount ?? 0),


        'attendances_pending_count'  => (int) ($attendancesPendingCount ?? 0),
        'attendances_approved_count' => (int) ($attendancesApprovedCount ?? 0),
        'attendances_rejected_count' => (int) ($attendancesRejectedCount ?? 0),

        'incidents_pending_count'  => (int) ($incidentsPendingCount ?? 0),
        'incidents_approved_count' => (int) ($incidentsApprovedCount ?? 0),
        'incidents_rejected_count' => (int) ($incidentsRejectedCount ?? 0),
        
        'waste_collections_pending_count'  => (int) ($waste_collectionsPendingCount ?? 0),
        'waste_collections_approved_count' => (int) ($waste_collectionsApprovedCount ?? 0),
        'waste_collections_rejected_count' => (int) ($waste_collectionsRejectedCount ?? 0),
        
        'waste_disposals_pending_count'  => (int) ($waste_disposalsPendingCount ?? 0),
        'waste_disposals_approved_count' => (int) ($waste_disposalsApprovedCount ?? 0),
        'waste_disposals_rejected_count' => (int) ($waste_disposalsRejectedCount ?? 0),

        // Loans
        'loans_pending_count'   => (int) ($loansPendingCount ?? 0),
        'loans_approved_count'  => (int) ($loansApprovedCount ?? 0),
        'loans_rejected_count'  => (int) ($loansRejectedCount ?? 0),

        // Payroll
        'payrolls_pending_count'  => (int) ($payrollsPendingCount ?? 0),
        'payrolls_approved_count' => (int) ($payrollsApprovedCount ?? 0),
        'payrolls_rejected_count' => (int) ($payrollsRejectedCount ?? 0),

        // Invoices
        'invoices_pending_count'  => (int) ($invoicesPendingCount ?? 0),
        'invoices_approved_count' => (int) ($invoicesApprovedCount ?? 0),
        'invoices_rejected_count' => (int) ($invoicesRejectedCount ?? 0),
        'invoices_deleted_count'  => (int) ($invoicesDeletedCount ?? 0),

        // Credit Notes
        'credit_notes_pending_count'  => (int) ($credit_notesPendingCount ?? 0),
        'credit_notes_approved_count' => (int) ($credit_notesApprovedCount ?? 0),
        'credit_notes_rejected_count' => (int) ($credit_notesRejectedCount ?? 0),
        'credit_notes_deleted_count'  => (int) ($credit_notesDeletedCount ?? 0),

        // Bills
        'bills_pending_count'  => (int) ($billsPendingCount ?? 0),
        'bills_approved_count' => (int) ($billsApprovedCount ?? 0),
        'bills_rejected_count' => (int) ($billsRejectedCount ?? 0),
       
        // TransportOrders
        'transport_orders_pending_count'  => (int) ($transportOrdersPendingCount ?? 0),
        'transport_orders_approved_count' => (int) ($transportOrdersApprovedCount ?? 0),
        'transport_orders_rejected_count' => (int) ($transportOrdersRejectedCount ?? 0),

        // Requisitions
        'requisitions_pending_count'  => (int) ($requisitionsPendingCount ?? 0),
        'requisitions_approved_count' => (int) ($requisitionsApprovedCount ?? 0),
        'requisitions_rejected_count' => (int) ($requisitionsRejectedCount ?? 0),

        // Gatepasses (Security)
        'gate_passes_pending_count'  => (int) ($gate_passesPendingCount ?? 0),
        'gate_passes_approved_count' => (int) ($gate_passesApprovedCount ?? 0),
        'gate_passes_rejected_count' => (int) ($gate_passesRejectedCount ?? 0),

        // Gatepasses (Logistics)
        'logistics_gate_passes_pending_count'  => (int) ($logistics_gate_passesPendingCount ?? 0),
        'logistics_gate_passes_approved_count' => (int) ($logistics_gate_passesApprovedCount ?? 0),
        'logistics_gate_passes_rejected_count' => (int) ($logistics_gate_passesRejectedCount ?? 0),

        // Gatepasses (Workshop)
        'workshop_gate_passes_pending_count'  => (int) ($workshop_gate_passesPendingCount ?? 0),
        'workshop_gate_passes_approved_count' => (int) ($workshop_gate_passesApprovedCount ?? 0),
        'workshop_gate_passes_rejected_count' => (int) ($workshop_gate_passesRejectedCount ?? 0),

        // Fuel Top Ups
        'top_ups_pending_count'  => (int) ($top_upsPendingCount ?? 0),
        'top_ups_approved_count' => (int) ($top_upsApprovedCount ?? 0),
        'top_ups_rejected_count' => (int) ($top_upsRejectedCount ?? 0),

        // Fuel Orders
        'fuels_pending_count'  => (int) ($fuelsPendingCount ?? 0),
        'fuels_approved_count' => (int) ($fuelsApprovedCount ?? 0),
        'fuels_rejected_count' => (int) ($fuelsRejectedCount ?? 0),
        'fuels_deleted_count'  => (int) ($fuelsDelectedCount ?? 0), // note: your variable is spelled Delected

        // Fuel Allocations / Requests
        'my_allocation_count' => (int) ($myAllocationCount ?? 0),

       

        // Trips
        'trips_pending_count'  => (int) ($tripsPendingCount ?? 0),
        'trips_approved_count' => (int) ($tripsApprovedCount ?? 0),
        'trips_rejected_count' => (int) ($tripsRejectedCount ?? 0),
        'trips_deleted_count'  => (int) ($tripsDelectedCount ?? 0), // note: your variable is spelled Delected

        // Transporters
        'transporters_pending_count'  => (int) ($transportersPendingCount ?? 0),
        'transporters_approved_count' => (int) ($transportersApprovedCount ?? 0),
        'transporters_rejected_count' => (int) ($transportersRejectedCount ?? 0),
  
        // Transporters
        'rentals_pending_count'  => (int) ($rentalsPendingCount ?? 0),
        'rentals_approved_count' => (int) ($rentalsApprovedCount ?? 0),
        'rentals_rejected_count' => (int) ($rentalsRejectedCount ?? 0),

        // Recoveries
        'recoveries_pending_count'  => (int) ($recoveriesPendingCount ?? 0),
        'recoveries_approved_count' => (int) ($recoveriesApprovedCount ?? 0),
        'recoveries_rejected_count' => (int) ($recoveriesRejectedCount ?? 0),

        // Workshop Bookings
        'bookings_pending_count'  => (int) ($bookingsPendingCount ?? 0),
        'bookings_approved_count' => (int) ($bookingsApprovedCount ?? 0),
        'bookings_rejected_count' => (int) ($bookingsRejectedCount ?? 0),

        // Tickets / Inspections (Workshop “My” counters)
        'job_cards_count'    => (int) ($jobCardsCount ?? 0),
        'inspections_count'  => (int) ($inspectionsCount ?? 0),

        // Asset Purchases (POs)
        'asset_purchases_pending_count'  => (int) ($asset_purchasesPendingCount ?? 0),
        'asset_purchases_approved_count' => (int) ($asset_purchasesApprovedCount ?? 0),
        'asset_purchases_rejected_count' => (int) ($asset_purchasesRejectedCount ?? 0),
        'asset_purchases_deleted_count'  => (int) ($asset_purchasesDeletedCount ?? 0),

        // Asset Dispatches
        'asset_dispatches_pending_count'  => (int) ($asset_dispatchesPendingCount ?? 0),
        'asset_dispatches_approved_count' => (int) ($asset_dispatchesApprovedCount ?? 0),
        'asset_dispatches_rejected_count' => (int) ($asset_dispatchesRejectedCount ?? 0),

        // Inventory Transfers
        'inventory_transfers_pending_count'  => (int) ($inventory_transfersPendingCount ?? 0),
        'inventory_transfers_approved_count' => (int) ($inventory_transfersApprovedCount ?? 0),
        'inventory_transfers_rejected_count' => (int) ($inventory_transfersRejectedCount ?? 0),
     
        // Asset Transfers
        'asset_transfers_pending_count'  => (int) ($asset_transfersPendingCount ?? 0),
        'asset_transfers_approved_count' => (int) ($asset_transfersApprovedCount ?? 0),
        'asset_transfers_rejected_count' => (int) ($asset_transfersRejectedCount ?? 0),

        // Inventory Purchases
        'inventory_purchases_pending_count'  => (int) ($inventory_purchasesPendingCount ?? 0),
        'inventory_purchases_approved_count' => (int) ($inventory_purchasesApprovedCount ?? 0),
        'inventory_purchases_rejected_count' => (int) ($inventory_purchasesRejectedCount ?? 0),
        'inventory_purchases_deleted_count'  => (int) ($inventory_purchasesDeletedCount ?? 0),

        // Inventory Dispatches
        'inventory_dispatches_pending_count'  => (int) ($inventory_dispatchesPendingCount ?? 0),
        'inventory_dispatches_approved_count' => (int) ($inventory_dispatchesApprovedCount ?? 0),
        'inventory_dispatches_rejected_count' => (int) ($inventory_dispatchesRejectedCount ?? 0),

        // Tyre Transfers
        'tyre_transfers_pending_count'  => (int) ($tyre_transfersPendingCount ?? 0),
        'tyre_transfers_approved_count' => (int) ($tyre_transfersApprovedCount ?? 0),
        'tyre_transfers_rejected_count' => (int) ($tyre_transfersRejectedCount ?? 0),

        // Tyre Purchases
        'tyre_purchases_pending_count'  => (int) ($tyre_purchasesPendingCount ?? 0),
        'tyre_purchases_approved_count' => (int) ($tyre_purchasesApprovedCount ?? 0),
        'tyre_purchases_rejected_count' => (int) ($tyre_purchasesRejectedCount ?? 0),
        'tyre_purchases_deleted_count'  => (int) ($tyre_purchasesDeletedCount ?? 0),

        // Retreads
        'retreads_pending_count'  => (int) ($retreadsPendingCount ?? 0),
        'retreads_approved_count' => (int) ($retreadsApprovedCount ?? 0),
        'retreads_rejected_count' => (int) ($retreadsRejectedCount ?? 0),

        // Tyre Dispatches
        'tyre_dispatches_pending_count'  => (int) ($tyre_dispatchesPendingCount ?? 0),
        'tyre_dispatches_approved_count' => (int) ($tyre_dispatchesApprovedCount ?? 0),
        'tyre_dispatches_rejected_count' => (int) ($tyre_dispatchesRejectedCount ?? 0),
    ];

       $menuGroups = ModuleGroup::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->with(['modules' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order')
            ->with(['sub_modules' => function ($sq) {
                $sq->where('is_active', true)->orderBy('sort_order');
            }]);
        }])
        ->get();


       

        $view->with([
            'menuCtx' => $ctx,
            'menuBadges' => $menuBadges,
            'menuGroups' => $menuGroups,
            'user'         => $user,
            'employee'     => $employee,
            'not_driver'     => $not_driver,
            'not_driver'     => $not_driver,
            'department_names'     => $department_names,
            'companies'     => $companies,
            'admin_company'     => $admin_company,
            'jobCardsCount'     => $jobCardsCount,
            'inspectionsCount'     => $inspectionsCount,
            'role_names'           => $role_names,
            'rank_names'           => $rank_names,
            'myAllocationCount'   => $myAllocationCount,
            'departmentsMap'      => $departments,
           
            'in_transport_management' => $in_transport_management,
            'in_transport_admin' => $in_transport_admin,
            'inTransport'          => $inTransport,
            'inHSEQ'          => $inHSEQ,
            'inSecurity'          => $inSecurity,
            'inFinance'          => $inFinance,
            'inHR'          => $inHR,
            'isAdmin'          => $isAdmin,
            'isSuperAdmin'          => $isSuperAdmin,
            'isHOD'          => $isHOD,
            'isUser'          => $isUser,
            'isEmployee'          => $isEmployee,
            'isManagement'          => $isManagement,
            'isDirector'     => $isDirector,
            'companyColor'          => $companyColor,
            'hseqdepartment_head'        => $hseqdepartment_head,
            'hrdepartment_head'          => $hrdepartment_head,
            'tldepartment_head'          => $tldepartment_head,
            'fndepartment_head'          => $fndepartment_head,
            'stdepartment_head'          => $stdepartment_head,
            'wsdepartment_head'          => $wsdepartment_head,
            'sdepartment_head'          => $sdepartment_head,
            'isSystemAdmin'          => $isSystemAdmin,
            'attendancesPendingCount'  => $attendancesPendingCount,
            'attendancesApprovedCount' => $attendancesApprovedCount,
            'attendancesRejectedCount' => $attendancesRejectedCount,
          
            'incidentsPendingCount'  => $incidentsPendingCount,
            'incidentsApprovedCount' => $incidentsApprovedCount,
            'incidentsRejectedCount' => $incidentsRejectedCount,
            
            'waste_disposalsPendingCount'  => $waste_disposalsPendingCount,
            'waste_disposalsApprovedCount' => $waste_disposalsApprovedCount,
            'waste_disposalsRejectedCount' => $waste_disposalsRejectedCount,
           
            'fuel_requestsPendingCount'  => $fuel_requestsPendingCount,
            'fuel_requestsApprovedCount' => $fuel_requestsApprovedCount,
            'fuel_requestsRejectedCount' => $fuel_requestsRejectedCount,
            
            'waste_collectionsPendingCount'  => $waste_collectionsPendingCount,
            'waste_collectionsApprovedCount' => $waste_collectionsApprovedCount,
            'waste_collectionsRejectedCount' => $waste_collectionsRejectedCount,

            'leavesPendingCount'  => $leavesPendingCount,
            'leavesApprovedCount' => $leavesApprovedCount,
            'leavesRejectedCount' => $leavesRejectedCount,
            'leavesDeletedCount'  => $leavesDeletedCount,
            'billsDeletedCount'          => $billsDeletedCount,
            'billsRejectedCount'          => $billsRejectedCount,
            'billsApprovedCount'          => $billsApprovedCount,
            'billsPendingCount'          => $billsPendingCount,
            'transportOrdersRejectedCount'          => $transportOrdersRejectedCount,
            'transportOrdersApprovedCount'          => $transportOrdersApprovedCount,
            'transportOrdersPendingCount'          => $transportOrdersPendingCount,
            
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
            'rentalsDeletedCount'          => $rentalsDeletedCount,
            'rentalsRejectedCount'          => $rentalsRejectedCount,
            'rentalsApprovedCount'          => $rentalsApprovedCount,
            'rentalsPendingCount'          => $rentalsPendingCount,
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
           
            'inventory_transfersRejectedCount'          => $inventory_transfersRejectedCount,
            'inventory_transfersApprovedCount'          => $inventory_transfersApprovedCount,
            'inventory_transfersPendingCount'          => $inventory_transfersPendingCount,
           
            'asset_transfersRejectedCount'          => $asset_transfersRejectedCount,
            'asset_transfersApprovedCount'          => $asset_transfersApprovedCount,
            'asset_transfersPendingCount'          => $asset_transfersPendingCount,

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