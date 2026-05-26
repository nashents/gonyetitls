<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(){
        return view('reports.index');
    }
    public function incomeStatement(){
        return view('reports.income_statements.index');
    }
    public function fuelConsumption(){
        return view('reports.fuel_consumption');
    }
    public function cashflow(){
        return view('reports.cashflows.index');
    }
    public function balanceSheet(){
        return view('reports.balance_sheets.index');
    }
    public function trialBalance(){
        return view('reports.trial_balance');
    }
}
