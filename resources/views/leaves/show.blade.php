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
    Leaves | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection


@section('body-class')
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
            							<li class="active"> <i class="fa fa-calendar"></i> Leave Application</li>
            						</ul>
                                </div>

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Leave Application By {{$leave->user->name}} {{$leave->user->surname}}</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">

                                                <table class="table table-clean">

                                                	<tbody class="text-center line-height-35">

                                                		<tr>
                                                            <th class="w-10 text-center line-height-35">Fullname</th>
                                                			<td class="w-20 line-height-35">
                                                                {{$leave->user ? $leave->user->name : ""}} {{$leave->user ? $leave->user->surname : ""}}
                                                                </td>
                                                		</tr>
                                                		<tr>
                                                            <th class="w-10 text-center line-height-35">Email</th>
                                                			<td class="w-20 line-height-35">{{$leave->user ? $leave->user->email : ""}}</td>
                                                		</tr>
                                                        <tr>
                                                            <th class="w-10 text-center line-height-35">Available Leave Days</th>
                                                			<td class="w-20 line-height-35">{{$leave->user ? $leave->user->employee->leave_days : ""}}</td>
                                                		</tr>
                                                		<tr>
                                                            <th class="w-10 text-center line-height-35">Leave Applied</th>
                                                			<td class="w-20 line-height-35">{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                                                		</tr>
                                                		<tr>
                                                            <th class="w-10 text-center line-height-35">Period</th>
                                                			<td class="w-20 line-height-35">From: {{$leave->from}} To: {{$leave->from}} </td>
                                                		</tr>
                                                		<tr>
                                                            <th class="w-10 text-center line-height-35">Reason for leave</th>
                                                			<td class="w-20 line-height-35">{{$leave->reason}} </td>
                                                		</tr>
                                                	</tbody>
                                                </table>

                                                    <div class="col-md-12">
                                                        <div class="btn-group pull-right mt-10" role="group">
                                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                                           {{-- <li><a href="#"  wire:click="authorize({{$leave->id}})" ><i class="fa fa-gavel color-default"></i> Authorization</a></li> --}}
                                                        </div>
                                                        {{-- @include('leaves.decision') --}}
                                                        <!-- /.btn-group -->
                                                    </div>


                                                <div class="col-md-12 mt-15 src-code">
                                                    <pre class="language-html"><code class="language-html">

                                                    </code></pre>
                                                </div>
                                                <!-- /.col-md-12 -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->


                                </div>

                            </div>
                            <!-- /.container-fluid -->
                        </section>
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->


                    <!-- /.right-sidebar -->


        <!-- ========== PAGE JS FILES ========== -->

@endsection
