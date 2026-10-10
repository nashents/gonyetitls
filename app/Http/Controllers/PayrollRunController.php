<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;

class PayrollRunController extends Controller
{
    public function index()
    {
        return view('payroll-runs.index');
    }

    public function departmentCosts()
    {
        return view('payroll-runs.department-costs');
    }

    public function show(PayrollRun $payrollRun)
    {
        return view('payroll-runs.show', ['run' => $payrollRun]);
    }
}
