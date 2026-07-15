<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqRiskAssessment;

class SheqRiskAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_risk_assessments.index');
    }
}
