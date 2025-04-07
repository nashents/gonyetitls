@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
    Data Summary | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection
@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('trips.summary',['from' => $from, 'to' => $to, 'search' => $search, 'company'=> $company, 'trip_filter' => $trip_filter, ])
        </div>
    </div>
</div>

@endsection
