<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PayrollSalary;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePayrollSalaryRequest;
use App\Http\Requests\UpdatePayrollSalaryRequest;

class PayrollSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function preview($id){
        $payroll_salary = PayrollSalary::findOrFail($id);
        $company = Auth::user()->employee->company;
        $payroll_salary_items = $payroll_salary->payroll_salary_items;
        $employee = $payroll_salary->employee;
        $salary =  $payroll_salary?->salary;
        $currency = $salary?->currency;
        return view('payroll_salaries.preview')->with([
            'payroll_salary' => $payroll_salary,
            'payroll_salary_items' => $payroll_salary_items,
            'employee' => $employee,
            'company' => $company,
            'salary' => $salary,
            'currency' => $currency,
        ]);
    }

    public function print($id){
        $payroll_salary = PayrollSalary::findOrFail($id);
        $company = Auth::user()->employee->company;
        $payroll_salary_items = $payroll_salary->payroll_salary_items;
        $employee = $payroll_salary->employee;
        return view('payroll_salaries.print')->with([
            'payroll_salary' => $payroll_salary,
            'payroll_salary_items' => $payroll_salary_items,
            'employee' => $employee,
            'company' => $company,
        ]);
    }

    public function generatePDF($id){
        // Find or 404 — never silently return null
        $payroll_salary = PayrollSalary::findOrFail($id);

        // Authorization: employee may only download their own payslip.
        // HR, Finance, Management and Super Admin may download any.
        $user       = Auth::user();
        $employee   = $user->employee;
        $userRoles  = $user->roles->pluck('name');
        $userDepts  = $employee?->departments->pluck('name') ?? collect();

        $isPrivileged = $userRoles->contains('Super Admin')
            || $userRoles->contains('Admin')
            || $userDepts->contains('Human Resources')
            || $userDepts->contains('Finance')
            || $userDepts->contains('Management');

        $isOwnPayslip = $employee?->id === $payroll_salary->employee_id;

        if (!$isPrivileged && !$isOwnPayslip) {
            abort(403, 'You are not authorised to download this payslip.');
        }

        $company              = $user->employee->company;
        $payroll_salary_items = $payroll_salary->payroll_salary_items;
        $payslipEmployee      = $payroll_salary->employee;

        $data = [
            'payroll_salary'       => $payroll_salary,
            'payroll_salary_items' => $payroll_salary_items,
            'employee'             => $payslipEmployee,
            'company'              => $company,
        ];

        $pdf      = PDF::loadView('payroll_salaries.payslip', $data);
        $filename = 'payslip_' . ($payslipEmployee?->payroll_number ?? $payroll_salary->id) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePayrollSalaryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayrollSalaryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrollSalary  $payrollSalary
     * @return \Illuminate\Http\Response
     */
    public function show(PayrollSalary $payrollSalary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayrollSalary  $payrollSalary
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrollSalary $payrollSalary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayrollSalaryRequest  $request
     * @param  \App\Models\PayrollSalary  $payrollSalary
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrollSalaryRequest $request, PayrollSalary $payrollSalary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayrollSalary  $payrollSalary
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayrollSalary $payrollSalary)
    {
        //
    }
}
