<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FuelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fuels.index');
    }
    public function deleted()
    {
        return view('fuels.deleted');
    }
    public function rejected()
    {
        return view('fuels.rejected');
    }
    public function pending()
    {
        return view('fuels.pending');
    }
    public function approved()
    {
        return view('fuels.approved');
    }
    
    public function preview(Fuel $fuel){
        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $container = $fuel->container;
        return view('fuels.preview')->with([
            'fuel' => $fuel,
            'company' => $company,
            'container' => $container,
            'source_horse' => $fuel->source_horse,
          ]);
    }
    /**
     * Fuel order schedule (Fuel Orders > Bulk Actions): the fuel order
     * document for several trucks at once, one sheet per station. Only
     * approved orders go on it - the station shouldn't fuel anything else.
     */
    public function schedule(Request $request){
        $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($id) => (int) $id)->filter()->unique();

        $fuels = Fuel::with(['horse.horse_make', 'horse.horse_model', 'vehicle', 'asset.product.brand', 'driver.employee', 'employee', 'trip', 'container', 'source_horse', 'user.employee'])
            ->whereIn('id', $ids)
            ->orderBy('order_number')
            ->get();

        $company = Auth::user()->employee->company ?? Auth::user()->company;

        return view('fuels.schedule')->with([
            'company' => $company,
            'groups' => $fuels->where('authorization', 'approved')->groupBy(fn ($fuel) => $fuel->container_id ? 'c'.$fuel->container_id : 'h'.$fuel->source_horse_id),
            'skipped' => $fuels->where('authorization', '!=', 'approved'),
        ]);
    }

    public function print(Fuel $fuel){

        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $container = $fuel->container;
        return view('fuels.print')->with([
            'fuel' => $fuel,
            'company' => $company,
            'container' => $container,
            'source_horse' => $fuel->source_horse,
          ]);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function show(Fuel $fuel)
    {
        return view('fuels.show')->with('fuel',$fuel);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function edit(Fuel $fuel)
    {
        return view('fuels.edit')->with('fuel',$fuel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fuel $fuel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fuel $fuel)
    {
        $fuel->delete();
        Session::flash('success','Fuel Order Deleted Successfully!!');
        return redirect()->back();
    }
}
