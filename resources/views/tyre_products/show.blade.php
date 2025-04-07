@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Product|@if (isset(Auth::user()->employee->company))
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
            							<li><a href="{{route('inventory_products.index')}}"><i class="fas fa-list"></i> Products</a></li>
                                        <li class="active"> <i class="fas fa-eye"></i> Product</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        <div class="row mt-30">
                            <x-loading/>

                            <!-- /.col-md-3 -->

                            <div class="col-md-10 col-md-offset-1">

                                <ul class="nav nav-tabs nav-justified" role="tablist">
                                    <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">product Order</a></li>

                                    {{-- <li role="presentation"><a href="#products" aria-controls="products" role="tab" data-toggle="tab">Products</a></li> --}}

                                </ul>
                                <div class="tab-content bg-white p-15">
                                    <div role="tabpanel" class="tab-pane active" id="basic">
                                        <table class="table table-striped">
                                            <tbody class="text-center line-height-35 ">
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Product Number</th>
                                                    <td class="w-20 line-height-35">{{$product->product_number}} </td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Image</th>
                                                    <td class="w-20 line-height-35"> <img src="{{asset('images/uploads/'.$product->filename)}}" alt=""></td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Category</th>
                                                    <td class="w-20 line-height-35">{{$product->category ? $product->category->name : "undefined"}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Attributes</th>
                                                    <td class="w-20 line-height-35"></td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Brand</th>
                                                    <td class="w-20 line-height-35">{{$product->value}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Name</th>
                                                    <td class="w-20 line-height-35">{{$product->name}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Manufacturer</th>
                                                    <td class="w-20 line-height-35">{{$product->value}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Description</th>
                                                    <td class="w-20 line-height-35">{{$product->description}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">Status</th>
                                                    <td class="w-20 line-height-35">{{$product->status}}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>



                                    <!-- /.section-title -->
                                </div>
                            </div>
                            <!-- /.col-md-9 -->
                        </div>


                    </div>


                </div>
                <!-- /.content-container -->
            </div>



@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#productsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#productsTable').DataTable();
    } );
    </script>
@endsection

