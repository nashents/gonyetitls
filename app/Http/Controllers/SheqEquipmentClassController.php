<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqEquipmentClass;

class SheqEquipmentClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_equipment_classes.index');
    }
}
