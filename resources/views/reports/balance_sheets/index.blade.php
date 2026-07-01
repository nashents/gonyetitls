@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
    <style>
        @media print {
            .top-navbar, #sidebar, .breadcrumb-div, .page-title-div, .hidden-print {
                display: none !important;
            }
            .content-container, .main-page, .container-fluid {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection
@section('title')
    Balance Sheet |@if (isset(Auth::user()->employee->company))
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
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li class="active"> <i class="fas fa-list"></i> Balance Sheet</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('reports.balance-sheets.index')


                    </div>





@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#invoicesTable').DataTable();
    } );
    </script>
@endsection

