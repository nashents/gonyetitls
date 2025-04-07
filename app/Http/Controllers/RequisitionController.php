<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\StoreRequisitionRequest;
use App\Http\Requests\UpdateRequisitionRequest;

class RequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('requisitions.index');
    }

    public function deleted()
    {
        return view('requisitions.deleted');
    }
    public function rejected()
    {
        return view('requisitions.rejected');
    }
    public function pending()
    {
        return view('requisitions.pending');
    }
    public function approved()
    {
        return view('requisitions.approved');
    }

    public function preview($id){
        $requisition = Requisition::find($id);
        $company = $requisition->employee->company;
        return view('requisitions.preview')->with([
            'requisition' => $requisition,
            'company' => $company,
            ]);
    }

    public function print($id){
        $requisition = Requisition::find($id);
        $company = $requisition->employee->company;
        return view('requisitions.print')->with([
            'requisition' => $requisition,
            'company' => $company,

        ]);
    }

    public function generatePDF($id){
        $requisition = Requisition::find($id);
        $company = $requisition->employee->company;
        $data = [
            'requisition' => $requisition,
            'company' => $company,
        ];
        $pdf = PDF::loadView('requisitions.requisition', $data);

        return $pdf->download('requisition.pdf');

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
     * @param  \App\Http\Requests\StoreRequisitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequisitionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Requisition  $requisition
     * @return \Illuminate\Http\Response
     */
    public function show(Requisition $requisition)
    {
        return view('requisitions.show')->with('requisition',$requisition);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Requisition  $requisition
     * @return \Illuminate\Http\Response
     */
    public function edit(Requisition $requisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRequisitionRequest  $request
     * @param  \App\Models\Requisition  $requisition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequisitionRequest $request, Requisition $requisition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Requisition  $requisition
     * @return \Illuminate\Http\Response
     */
    public function destroy(Requisition $requisition)
    {
        
        $requisition_items = $requisition->requisition_items;

        if (isset($requisition_items)) {
            foreach ($requisition_items as $requisition_item) {
                $requisition_item->delete();
            }
        }
        $requisition->delete();
        Session::flash('success','Requisition Deleted Successfully');
        return redirect()->back();
    }
}
