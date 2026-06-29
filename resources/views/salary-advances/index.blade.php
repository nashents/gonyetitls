@extends('layouts.app')

@section('extra-css')
    @if(isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/uploads/'.Auth::user()->employee->company->logo) }}">
    @endif
@endsection

@section('title')Salary Advances | {{ Auth::user()->employee?->company?->name }}@endsection
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
                    <li><a href="{{ route('payrolls.index') }}">Payroll</a></li>
                    <li class="active"><i class="fa fa-money"></i> Salary Advances</li>
                </ul>
            </div>
        </div>
    </div>

    @livewire('salary-advances.index')
</div>
@endsection
