<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqNonConformity;

class SheqNonConformityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_non_conformities.index');
    }
}
