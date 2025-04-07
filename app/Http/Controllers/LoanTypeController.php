<?php

namespace App\Http\Controllers;

use App\Models\LoanType;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreLoanTypeRequest;
use App\Http\Requests\UpdateLoanTypeRequest;

class LoanTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('loan_types.index');
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
     * @param  \App\Http\Requests\StoreLoanTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLoanTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LoanType  $loanType
     * @return \Illuminate\Http\Response
     */
    public function show(LoanType $loanType)
    {
        return view('loan_types.show')->with('loan_type', $loanType);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LoanType  $loanType
     * @return \Illuminate\Http\Response
     */
    public function edit(LoanType $loanType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLoanTypeRequest  $request
     * @param  \App\Models\LoanType  $loanType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLoanTypeRequest $request, LoanType $loanType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoanType  $loanType
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoanType $loanType)
    {
        $loanType->delete();
        Session::flash('success','Loan Type Deleted Successfully');
        return redirect()->back();
    }
}
