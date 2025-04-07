<?php

namespace App\Http\Controllers;

use App\Models\WorkshopService;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreWorkshopServiceRequest;
use App\Http\Requests\UpdateWorkshopServiceRequest;

class WorkshopServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('workshop_services.index');
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
     * @param  \App\Http\Requests\StoreWorkshopServiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkshopServiceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkshopService  $workshopService
     * @return \Illuminate\Http\Response
     */
    public function show(WorkshopService $workshopService)
    {
        return view('workshop_services.show')->with('workshop_service',$workshopService);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkshopService  $workshopService
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkshopService $workshopService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkshopServiceRequest  $request
     * @param  \App\Models\WorkshopService  $workshopService
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkshopServiceRequest $request, WorkshopService $workshopService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkshopService  $workshopService
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkshopService $workshopService)
    {
        $bill = $workshopService->bill;

        if (isset($bill)) {

            $bill_expenses = $bill->bill_expenses;

            if (isset($bill_expenses)) {

                foreach($bill_expenses as $bill_expense){
                    $bill_expense->delete();
                }

            }

            $bill->delete();
            
        }
      
        $workshopService->delete();
        Session::flash('success','Service Deleted Successfully!!');
        return redirect()->back();
    }
}
