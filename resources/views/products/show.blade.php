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
            							<li class="active"> <i class="fas fa-eye"></i> Product</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('products.show', ['id' => $product->id])


                    </div>



            <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeAttributeModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content bg-danger">
                        <div class="modal-body">
                           <center> <strong>Are you sure you want to remove this Attribute?</strong> </center> 
                        </div>
                        <form wire:submit.prevent="removeAttribute()">
                        <div class="modal-footer no-border">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeAttributeValueModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content bg-danger">
                        <div class="modal-body">
                           <center> <strong>Are you sure you want to remove this Value?</strong> </center> 
                        </div>
                        <form wire:submit.prevent="removeAttributeValue()">
                        <div class="modal-footer no-border">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>
                    </div>
                </div>
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
        $('#attributesTable').DataTable();
    } );
    </script>
@endsection

