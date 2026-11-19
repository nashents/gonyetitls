@extends('layouts.app')
@section('content')

@section('title')
    My Document Hand-overs | @if (isset(Auth::user()->employee->company))
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
                                        <li class="active"><i class="fa fa-exchange"></i> My Document Hand-overs</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @livewire('documents.custody-inbox')
                    </div>

@endsection
