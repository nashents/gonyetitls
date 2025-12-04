@extends('layouts.main')
@section('title')
Quotation Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('extra-css')
<style>
    /* Hide everything except #print-area when printing */
    @media print {
        body * {
            visibility: hidden;
        }

        #print-area, 
        #print-area * {
            visibility: visible;
        }

        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Hide toolbar/buttons on print */
        .hidden-print {
            display: none !important;
        }
    }
</style>
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
@section('extra-js')
<script>
    function printSection() {
        window.print();
    }
</script>
@endsection
    
 
    