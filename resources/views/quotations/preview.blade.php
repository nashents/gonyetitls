@extends('layouts.main')
@section('title')
{{ ($document ?? '') === 'sales_order' ? 'Sales Order' : 'Quotation' }} Preview | {{Auth::user()->employee->company->name}} 
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('quotations.preview', ['quotation' => $quotation,
            'company' => $company,
            'quotation_items' => $quotation_items,
            'document' => $document ?? 'quotation',])
        </div>
    </div>
</div>
@endsection

    
 
    