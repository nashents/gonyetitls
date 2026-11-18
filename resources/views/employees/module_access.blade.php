@extends('layouts.app')

@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Module Access | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

@section('content')
    <div class="main-page">
        <div class="container-fluid">
            <div class="row page-title-div">
                @include('includes.top-message')
            </div>
            <div class="row breadcrumb-div">
                <div class="col-md-6">
                    <ul class="breadcrumb">
                        <li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                        <li><a href="{{route('employees.edit', $employee->id)}}"><i class="fa fa-user"></i> {{$employee->name}} {{$employee->surname}}</a></li>
                        <li class="active"><i class="fas fa-lock"></i> Module Access</li>
                    </ul>
                </div>
            </div>
        </div>

        @livewire('employees.module-access', ['id' => $employee->id])
    </div>
@endsection
