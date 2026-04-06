<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                    
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="panel-title">
                                    <div class="row">
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    To
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                </div>
                                <br>
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search TTOs...">
                                    </div>
                                </div>
                            @php
                                    $showFreight = !$company->rates_managed_by_finance
                                        || in_array('Finance', $department_names)
                                        || in_array('Super Admin', $role_names);

                                    $dtPattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    $formatDate = function ($value) use ($dtPattern) {
                                        if (!$value) return null;

                                        // datetime-local like 2026-01-09T06:30
                                        if (is_string($value) && preg_match($dtPattern, $value)) {
                                            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $value)->format('d M Y g:i A');
                                        }

                                        // already a Carbon/date string
                                        try {
                                            return \Carbon\Carbon::parse($value)->format('d M Y g:i A');
                                        } catch (\Throwable $e) {
                                            return $value; // fallback
                                        }
                                    };

                                   
                                @endphp
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>TTO#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Date</th>
                                                <th>Cargo</th>
                                                <th>Customer<hr style="margin-top:2px; margin-bottom:2px">Consignee</th>
                                                <th>From</th>
                                                <th>To</th>
                                                @if($showFreight)
                                                    <th>
                                                        Rate
                                                        <hr style="margin-top:2px; margin-bottom:2px">
                                                        Freight
                                                    </th>
                                                @endif
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($trip_transport_orders as $tto)
                                        @php
                                            $trip = App\Models\Trip::find($tto->trip_id);
                                            $transport_order = App\Models\TransportOrder::find($tto->transport_order_id);
                                        @endphp
                                            <tr >
                                                <td>
                                                    <strong>{{ $tto->tto_number }}</strong>
                                                    <br>
                                                    <small>
                                                        <strong>CreatedBy:</strong>  {{ $tto->user?->name }} {{ $tto->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $tto->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $transport_order->trip_type?->name }}
                                                 
                                                </td>
                                                <td>
                                                    {{ $formatDate($transport_order->start_date) }}
                                                </td>
                                                <td>
                                                     {{ ucfirst($transport_order->cargo?->name ?? '') }} <br>
                                                    <small class="text-muted">
                                                        <strong>Weight: </strong>{{$transport_order->weight}} <br>
                                                        <strong>Qty: </strong>{{$tto->allocated_quantity}} {{$transport_order->allocated_units_of_measure?->name}} <br>
                                                        <strong>AddInfo:</strong> {{$transport_order->cargo_details}} <br>
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ ucfirst($transport_order->customer?->name ?? '') }}
                                                   <hr class="my-1">
                                                        {{ ucfirst($transport_order->consignee?->name ?? '') }}
                                                </td>
                                                <td>
                                                    @if($transport_order->fromDestination)
                                                        {{ $transport_order->fromDestination->country?->name }} {{ $transport_order->fromDestination->city }}
                                                    @endif
                                                    @if($transport_order->loading_point)
                                                        <hr class="my-1">
                                                        {{ $transport_order->loading_point->name }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($transport_order->toDestination)
                                                        {{ $transport_order->toDestination->country?->name }} {{ $transport_order->toDestination->city }}
                                                    @endif
                                                    @if($transport_order->offloading_point)
                                                        <hr class="my-1">
                                                        {{ $transport_order->offloading_point->name }}
                                                    @endif
                                                </td>
                                                @if($showFreight)
                                                    <td>
                                                        {{ $tto->currency?->symbol }}
                                                        {{ number_format(
                                                            (float) (is_numeric($tto->allocated_rate)
                                                                ? $tto->allocated_rate
                                                                : preg_replace('/[^\d\.\-]/', '', (string) ($tto->allocated_rate ?? 0))
                                                            ),
                                                            2
                                                        ) }}
                                                        <hr class="my-1">
                                                        {{ $transport_order->currency?->symbol }}
                                                        {{ number_format(
                                                            (float) (is_numeric($transport_order->freight)
                                                                ? $transport_order->freight
                                                                : preg_replace('/[^\d\.\-]/', '', (string) ($transport_order->freight ?? 0))
                                                            ),
                                                            2
                                                        ) }}
                                                    </td>
                                                @endif

                                                <td>
                                                    @if($transport_order->invoices?->count())
                                                        <span class="label label-success">issued</span>
                                                        <small>
                                                            <strong>Invoice#(s):</strong>
                                                            @foreach($transport_order->invoices as $invoice)
                                                                {{ $invoice->invoice_number }}@if(!$loop->last),@endif
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        <span class="label label-warning">pending</span>
                                                    @endif
                                                </td>
                                                 <td class="w-10 line-height-35 table-dropdown">
                                                   <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="{{ route('trip_transport_orders.show',$tto->id)}}">
                                                                    <i class="fas fa-eye color-default"></i> View
                                                                </a>
                                                            </li>
                                                       
                                                        </ul>
                                                    </div> 
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No TTos Found ....
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                {{-- </div> --}}
                        
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

</div>

