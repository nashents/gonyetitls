@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
   Purchase Orders | @if (Auth::user()->employee->company)
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
                                        <li><a href="{{route('inventory_purchases.index')}}"><i class="fa fa-list"></i> All Inventory Purchase Orders</a></li>
            							<li class="active"> <i class="fa fa-check"></i> Approved Inventory Purchase Orders</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('purchases.approved',['category'=>'inventory'])


                    </div>


@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#purchasesTable').DataTable();
    } );
    </script>
@endsection

