@extends('layouts.app')
@section('content')

@section('title')
    Sage Reconciliation | @if (Auth::user()->employee && Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                          @include('includes.top-message')
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active"><i class="fas fa-exchange-alt"></i> Sage Reconciliation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @livewire('sage.reconciliation')

                </div>

@endsection
