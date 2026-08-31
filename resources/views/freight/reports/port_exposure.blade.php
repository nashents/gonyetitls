@extends('layouts.app')

@section('title')
    Port & Demurrage Exposure
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
            							<li class="active"> <i class="fas fa-chart-bar"></i> Port &amp; Demurrage Exposure</li>
            						</ul>
                                </div>
                            </div>
                        </div>

                        @livewire('freight.reports.port-exposure.index')

                    </div>

@endsection
