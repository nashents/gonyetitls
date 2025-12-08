@extends('layouts.main')
@section('title')
Quotation Preview | {{Auth::user()->employee->company->name}} 
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('quotations.preview', ['quotation' => $quotation,
            'company' => $company,
            'quotation_items' => $quotation_items,])
        </div>
    </div>
</div>
@endsection

    
 
    