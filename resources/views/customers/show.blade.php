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
    Customers | @if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

            <!-- ========== TOP NAVBAR ========== -->
           @include('includes.navbar')

            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                  @include('includes.sidebar')
                    <!-- /.left-sidebar -->

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
            							<li><a href="{{route('customers.index')}}"><i class="fa fa-list"></i>Customers</a></li>
            							<li class="active"> <i class="fa fa-eye"></i>Customer</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('customers.show',['customer' => $customer])


                    </div>


                </div>
                <!-- /.content-container -->
            </div>



@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#customersTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#tripsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#invoicesTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#contactsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#contractsTable').DataTable();
    } );
    </script>
@endsection

