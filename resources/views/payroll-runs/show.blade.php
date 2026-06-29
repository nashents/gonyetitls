@extends('layouts.app')

@section('extra-css')
    @if(isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uploads/'.Auth::user()->employee->company->logo) }}">
    @endif
@endsection

@section('title')Payroll Run | {{ $run->name }} | {{ Auth::user()->employee?->company?->name }}@endsection

@section('body-id')<body class="top-navbar-fixed">@endsection

@section('content')
<div class="main-page">
    <div class="container-fluid">
        <div class="row page-title-div">
            @include('includes.top-message')
        </div>
        <div class="row breadcrumb-div">
            <div class="col-md-6">
                <ul class="breadcrumb">
                    <li><a href="{{ route('dashboard.index') }}"><i class="fa fa-home"></i> Home</a></li>
                    <li><a href="{{ route('payroll-runs.index') }}">Payroll Runs</a></li>
                    <li class="active">{{ $run->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    @livewire('payroll-runs.show', ['run' => $run])
</div>
@endsection
