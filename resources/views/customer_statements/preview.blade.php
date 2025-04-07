
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Customer Statement Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('customer-statements.preview', [
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                ])
        </div>
    </div>
</div>


@endsection
