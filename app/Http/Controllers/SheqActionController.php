<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqAction;

class SheqActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_actions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sheq_action = SheqAction::find($id);
        return view('sheq_actions.show',[
            'sheq_action' => $sheq_action
        ]);
    }
}
