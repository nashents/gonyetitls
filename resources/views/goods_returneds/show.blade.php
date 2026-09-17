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
    Goods Return | @if (isset(Auth::user()->employee->company))
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
            							<li><a href="{{route('goods_returneds.index')}}"><i class="fas fa-list"></i> Goods Returned</a></li>
            							<li class="active"> <i class="fas fa-eye"></i> {{ $goodsReturned->goods_returned_number }}</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            {{ $goodsReturned->goods_returned_number }} ({{ $goodsReturned->return_reference }})
                                            @php
                                                $authColors = ['pending' => 'label-warning', 'approved' => 'label-primary', 'rejected' => 'label-danger'];
                                            @endphp
                                            @if (is_null($goodsReturned->authorization))
                                                <span class="label label-default">Draft</span>
                                            @else
                                                <span class="label {{ $authColors[$goodsReturned->authorization] ?? 'label-default' }}">{{ ucfirst($goodsReturned->authorization) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="panel-body p-20">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>GRN#</strong><br>{{ $goodsReturned->goods_received?->goods_received_number }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Vendor</strong><br>{{ $goodsReturned->vendor?->name }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Returned By</strong><br>{{ $goodsReturned->employee?->name }} {{ $goodsReturned->employee?->surname }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Return Date</strong><br>{{ $goodsReturned->return_date }}
                                            </div>
                                        </div>
                                        <div class="row" style="margin-top:15px">
                                            <div class="col-md-3">
                                                <strong>Department</strong><br>{{ ucfirst($goodsReturned->department) }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Value</strong><br>{{ number_format($goodsReturned->total_return_value, 2) }} {{ $goodsReturned->currency }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Debit Note</strong><br>{{ $goodsReturned->debit_note?->debit_note_number ?? '-' }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Created By</strong><br>{{ $goodsReturned->user?->name }} {{ $goodsReturned->user?->surname }}
                                            </div>
                                        </div>
                                        @if ($goodsReturned->reason)
                                        <div class="row" style="margin-top:15px">
                                            <div class="col-md-12">
                                                <strong>Reason</strong><br>{{ $goodsReturned->reason }}
                                            </div>
                                        </div>
                                        @endif
                                        @if (!is_null($goodsReturned->authorization) && $goodsReturned->authorization !== 'pending')
                                        <div class="row" style="margin-top:15px">
                                            <div class="col-md-6">
                                                <strong>{{ ucfirst($goodsReturned->authorization) }} By</strong><br>
                                                {{ $goodsReturned->authorized_by?->name }} {{ $goodsReturned->authorized_by?->surname }} on {{ $goodsReturned->authorization_date }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Comments</strong><br>{{ $goodsReturned->authorization_comments ?: '-' }}
                                            </div>
                                        </div>
                                        @endif

                                        <hr>
                                        <h4>Returned Item(s)</h4>
                                        <table class="table table-striped table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty Returned</th>
                                                    <th>Unit Cost</th>
                                                    <th>Total Value</th>
                                                    <th>Reason</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($goodsReturned->goods_returned_items as $item)
                                                <tr>
                                                    <td>{{ $item->product?->name }}</td>
                                                    <td>{{ $item->qty_returned }}</td>
                                                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                                                    <td>{{ number_format($item->total_value, 2) }}</td>
                                                    <td>{{ $item->return_reason }}</td>
                                                    <td>{{ $item->notes }}</td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="text-center">No items on this return.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        @if (is_null($goodsReturned->authorization))
                                        <a href="{{ route('goods_returneds.new', ['department' => $goodsReturned->department, 'goodsReturned' => $goodsReturned->id]) }}" class="btn bg-success btn-wide btn-rounded">
                                            <i class="fa fa-edit"></i> Continue Editing Draft
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

@endsection
