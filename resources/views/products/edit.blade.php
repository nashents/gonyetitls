@extends('layouts.app')

@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Products | @if (Auth::user()->employee->company)
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
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                        @if ($product->department == "inventory")
                                        <li><a href="{{route('inventory_products.index')}}"><i class="fa fa-list"></i> Products</a></li>
                                        @elseif ($product->department == "asset")
                                        <li><a href="{{route('products.index')}}"><i class="fa fa-list"></i> Products</a></li>
                                        @elseif ($product->department == "tyre")
                                        <li><a href="{{route('tyre_products.index')}}"><i class="fa fa-list"></i> Products</a></li>
                                        @endif
                                        <li class="active"> <i class="fas fa-edit"></i> Products</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        @livewire('products.edit',['product' =>$product])
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->


        <!-- ========== PAGE JS FILES ========== -->


@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#productsTable').DataTable();
    } );
    </script>
@endsection
