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
        $payroll_salary = PayrollSalary::find($id);
        $company = Auth::user()->employee->company;
        $payroll_salary_details = $payroll_salary->payroll_salary_details;
        $employee = $payroll_salary->employee;
        return view('payroll_salaries.preview')->with([
            'payroll_salary' => $payroll_salary,
            'payroll_salary_details' => $payroll_salary_details,
            'employee' => $employee,
            'company' => $company,
        ]);
    }

    public function print($id){
        $payroll_salary = PayrollSalary::find($id);
        $company = Auth::user()->employee->company;
        $payroll_salary_details = $payroll_salary->payroll_salary_details;
        $employee = $payroll_salary->employee;
        return view('payroll_salaries.print')->with([
            'payroll_salary' => $payroll_salary,
            'payroll_salary_details' => $payroll_salary_details,
            'employee' => $employee,
            'company' => $company,
        ]);
    }

    public function generatePDF($id){
      $payroll_salary = PayrollSalary::find($id);
      $company = Auth::user()->employee->company;
      $payroll_salary_details = $payroll_salary->payroll_salary_details;
      $employee = $payroll_salary->employee;
        $data = [
            'payroll_salary' => $payroll_salary,
            'payroll_salary_details' => $payroll_salary_details,
            'employee' => $employee,
            'company' => $company,
        ];
        $pdf = PDF::loadView('payroll_salaries.payslip', $data);

        return $pdf->download('payslip.pdf');

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
