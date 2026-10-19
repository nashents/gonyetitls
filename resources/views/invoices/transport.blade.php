@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap">
@include('invoices.layouts.transport-styles', ['company' => $company])
@endsection
@section('title')
Invoice Preview | {{Auth::user()->employee->company->name}}
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                @livewire('invoices.templates.transport', [
                    'invoice' => $invoice,
                    'company' => $company,
                    'invoice_items' => $invoice_items,])
            </div>
        </div>
    </div>
@endsection
