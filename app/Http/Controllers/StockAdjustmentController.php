<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        return view('stock_adjustments.index');
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        return view('stock_adjustments.show', ['adjustment' => $stockAdjustment]);
    }
}
