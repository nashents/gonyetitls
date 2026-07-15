<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqManagementReview;

class SheqManagementReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_management_reviews.index');
    }
}
