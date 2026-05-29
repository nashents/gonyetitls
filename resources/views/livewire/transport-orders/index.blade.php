<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                       @php
                                        $notDriver = !$driver;
                                      $showFreight = !$driver && (
                                        !$company->rates_managed_by_finance
                                        || in_array('Finance', $department_names)
                                        || in_array('Super Admin', $role_names)
                                    );

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

                                    $statusMap = [
                                        'Completed'        => ['row' => '#5cb85c', 'cell' => 'table-success', 'badge' => 'success'],
                                        'Scheduled'        => ['row' => '#f0ad4e', 'cell' => 'table-warning', 'badge' => 'warning'],
                                        'Started'    => ['row' => '#adb5bd', 'cell' => 'table-secondary', 'badge' => 'info'],
                                        'Cancelled'           => ['row' => '#5bc0de', 'cell' => 'table-info', 'badge' => 'danger'],
                                    ];
                                @endphp
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                    
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="panel-title">
                                    <h5>Date range</h5>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="transport_order_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Created At</option>
                                                    <option value="date">Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
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
                                @if ($notDriver)
                                    
                              
                                    <div class="mb-15 mt-15">
                                        <input type="checkbox" wire:model.debounce.300ms="use_filters"   class="line-style" />
                                        <label for="one" class="radio-label">Use Additional Filters</label>
                                        @error('use_filters') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if ($use_filters == True)
                                    <h5>Filter reports by</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Origins
                                                </span>
                                                <select wire:model.debounce.300ms="filter_from" class="form-control  " aria-label="..." >
                                                        <option value="">Select Origin</option>
                                                        @foreach ($destinations as $destination)
                                                            <option value="{{ $destination->id }}"  >{{ ucfirst($destination->country ? $destination->country->name : "") }} {{ ucfirst($destination->city) }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Loading Points
                                                </span>
                                                <select wire:model.debounce.300ms="filter_loading_point_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Loading Point</option>
                                                        @foreach ($loading_points as $loading_point)
                                                            <option value="{{ $loading_point->id }}"  >{{$loading_point->name}}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Destinations
                                                </span>
                                                <select wire:model.debounce.300ms="filter_to" class="form-control  " aria-label="..." >
                                                        <option value="">Select Destination</option>
                                                        @foreach ($destinations as $destination)
                                                            <option value="{{ $destination->id }}"  >{{ ucfirst($destination->country ? $destination->country->name : "") }} {{ ucfirst($destination->city) }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                         <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Offloading Points
                                                </span>
                                                <select wire:model.debounce.300ms="filter_loading_point_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Offloading Point</option>
                                                        @foreach ($offloading_points as $offloading_point)
                                                            <option value="{{ $offloading_point->id }}"  >{{$offloading_point->name}}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Cargos
                                                </span>
                                                <select wire:model.debounce.300ms="filter_cargo_id" class="form-control  " aria-label="..." >
                                                        <option value=""></option>
                                                        @foreach ($cargos as $cargo)
                                                        <option value="{{ $cargo->id }}"  >{{ ucfirst($cargo->name) }}</option>
                                                        @endforeach
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                         <div class="col-md-2">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Currency
                                                </span>
                                                <select wire:model.debounce.300ms="filter_currency_id" class="form-control  " aria-label="..." >
                                                        <option value=""></option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Status
                                                </span>
                                                <select wire:model.debounce.300ms="filter_transport_order_status" class="form-control  " aria-label="..." >
                                                        <option value=""></option>
                                                        <option value="Scheduled">Scheduled</option>
                                                        <option value="Started">Started</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Cancelled">Cancelled</option>
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Trip Type
                                                </span>
                                                <select wire:model.debounce.300ms="filter_trip_type_id" class="form-control  " aria-label="..." >
                                                        <option value=""></option>
                                                        @foreach ($trip_types as $trip_type)
                                                            <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option> 
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Customers
                                                </span>
                                                <select wire:model.debounce.300ms="filter_customer_id" class="form-control" aria-label="..." >
                                                        <option value=""></option>
                                                        @foreach ($customers as $customer)
                                                            <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
                                                        @endforeach
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                       <div class="col-md-2">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Consignees
                                                </span>
                                                <select wire:model.debounce.300ms="filter_consignee_id" class="form-control  " aria-label="..." >
                                                        <option value=""></option>
                                                        @foreach ($consignees as $consignee)
                                                            <option value="{{ $consignee->id }}"  >{{ $consignee->name }} </option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                       
                                    </div>
                                    @endif
                                    <div class="row mt-15">
                                        <div class="col-md-5">
                                            <a href="#" data-toggle="modal" data-target="#storeModal"  class="btn btn-default btn-wide" aria-haspopup="true" aria-expanded="true"><i class="fa fa-plus-square-o"></i>Transport Order</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                        </div> 
                                    </div>
                                      @endif
                                </div>
                                <br>
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search transport orders...">
                                    </div>
                                </div>
                         
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>Order#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Date</th>
                                                <th>Cargo</th>
                                                <th>Customer<hr style="margin-top:2px; margin-bottom:2px">Consignee</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                @if($showFreight)
                                                    <th>
                                                        Rate
                                                        <hr style="margin-top:2px; margin-bottom:2px">
                                                        Freight
                                                    </th>
                                                @endif
                                                <th>Invoice</th>
                                                <th>Auth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($transport_orders as $transport_order)
                                            @php
                                                $s = $statusMap[$transport_order->status] ?? ['row' => null, 'cell' => '', 'badge' => 'secondary'];
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}" @endif>
                                                <td>
                                                    <strong>{{ $transport_order->transport_order_number }}@if($transport_order->custom_ref)/{{ $transport_order->custom_ref }}@endif</strong>
                                                    <br>
                                                    <small>
                                                        <strong>CreatedBy:</strong>  {{ $transport_order->user?->name }} {{ $transport_order->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $transport_order->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $transport_order->trip_type?->name }}
                                                    @if ($transport_order->bill_of_entry)  
                                                        <br>
                                                        <small><strong>BillOfEntry#: {{$transport_order->bill_of_entry}}</strong></small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $formatDate($transport_order->start_date) }}
                                                </td>
                                                <td>
                                                     {{ ucfirst($transport_order->cargo?->name ?? '') }} <br>
                                                    <small class="text-muted">
                                                        <strong>Weight: </strong>{{$transport_order->weight}} <br>
                                                        <strong>Qty: </strong>{{$transport_order->quantity}} {{$transport_order->units_of_measure?->name}} <br>
                                                        <strong>AddInfo:</strong> {{$transport_order->cargo_details}} <br>
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ ucfirst($transport_order->customer?->name ?? '') }}
                                                   <hr class="my-1">
                                                        {{ ucfirst($transport_order->consignee?->name ?? '') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $fromRoutes = collect();

                                                        if ($transport_order->trip_origins && $transport_order->trip_origins->count()) {
                                                            $fromRoutes = $transport_order->trip_origins
                                                                ->map(function ($trip_origin) {
                                                                    $from = $trip_origin->destination
                                                                        ? trim(($trip_origin->destination->country?->name ?? '') . ' ' . ($trip_origin->destination->city ?? ''))
                                                                        : null;

                                                                    $loadingPoint = $trip_origin->loading_point?->name;

                                                                    return [
                                                                        'label' => trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -'),
                                                                        'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $from = $transport_order->fromDestination
                                                                ? trim(($transport_order->fromDestination->country?->name ?? '') . ' ' . ($transport_order->fromDestination->city ?? ''))
                                                                : null;

                                                            $loadingPoint = $transport_order->loading_point?->name;

                                                            $label = trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -');

                                                            if (!empty($label)) {
                                                                $fromRoutes = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($fromRoutes as $route)
                                                        {{ $route['label'] }}

                                                        @if(!$loop->last && $fromRoutes->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>

                                                <td>
                                                    @php
                                                        $toRoutes = collect();

                                                        if ($transport_order->trip_destinations && $transport_order->trip_destinations->count()) {
                                                            $toRoutes = $transport_order->trip_destinations
                                                                ->map(function ($trip_destination) {
                                                                    $to = $trip_destination->destination
                                                                        ? trim(($trip_destination->destination->country?->name ?? '') . ' ' . ($trip_destination->destination->city ?? ''))
                                                                        : null;

                                                                    $offloadingPoint = $trip_destination->offloading_point?->name;

                                                                    return [
                                                                        'label' => trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -'),
                                                                        'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $to = $transport_order->toDestination
                                                                ? trim(($transport_order->toDestination->country?->name ?? '') . ' ' . ($transport_order->toDestination->city ?? ''))
                                                                : null;

                                                            $offloadingPoint = $transport_order->offloading_point?->name;

                                                            $label = trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -');

                                                            if (!empty($label)) {
                                                                $toRoutes = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($toRoutes as $route)
                                                        {{ $route['label'] }}

                                                        @if(!$loop->last && $toRoutes->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>

                                                <td class="{{ $s['cell'] }}" style="padding: 5px;">
                                                    <span class="label label-{{ $s['badge'] }} label-wide">
                                                        {{ $transport_order->status }}
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $transport_order->currency?->symbol }}{{ number_format($transport_order->rate ?: 0,2) }}
                                                        <hr class="my-1">
                                                        {{ $transport_order->currency?->symbol }}{{ number_format($transport_order->freight ?: 0,2) }}
                                                    </td>
                                                @endif

                                                <td>
                                                    @if($transport_order->invoice_items?->count())
                                                        <span class="label label-success">issued</span>
                                                        <small>
                                                            <strong>Invoice#(s):</strong>
                                                            @foreach($transport_order->invoice_items as $invoice_item)
                                                                {{ $invoice_item->invoice?->invoice_number }}@if(!$loop->last),@endif
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        <span class="label label-warning">pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $authClass = $transport_order->authorization === 'approved' ? 'success' : ($transport_order->authorization === 'rejected' ? 'danger' : 'warning');
                                                        $authText  = $transport_order->authorization === 'approved' ? 'approved' : ($transport_order->authorization === 'rejected' ? 'rejected' : 'pending');
                                                    @endphp
                                                    <span class="label label-{{ $authClass }}">{{ $authText }}</span>

                                                    @if($transport_order->authorization_date)
                                                        <br><small><strong style="background-color: orange">AuthorizedOn: {{ $transport_order->authorization_date }}</strong></small>
                                                    @endif
                                                    @if($transport_order->authorized_by_id)
                                                        <br><small><strong style="background-color: orange">AuthorizedBy: {{ $this->getAuthorizer($transport_order->authorized_by_id) }}</strong></small>
                                                    @endif
                                                    @if($transport_order->reason)
                                                        <br><small><strong style="background-color: orange">Comments: {{ $transport_order->reason }}</strong></small>
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
                                                                <a href="{{ route('transport_orders.show',$transport_order->id)}}">
                                                                    <i class="fas fa-eye color-default"></i> View
                                                                </a>
                                                            </li>
                                                           @if ($notDriver)
                                                            @if ($employee)
                                                                <li>
                                                                    <a href="{{ route('transport_orders.preview', $transport_order->id) }}">
                                                                        <i class="fas fa-file color-warning"></i> Transport Order
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" wire:click.prevent="edit({{$transport_order->id}})">
                                                                        <i class="fas fa-edit color-success"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#"  wire:click.prevent="delete({{$transport_order->id}})">
                                                                        <i class="fa fa-trash color-danger"></i> Delete
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @endif
                                                        </ul>
                                                    </div> 
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No Transport Orders Found ....
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                {{-- </div> --}}
                                  
                                    <nav class="text-center" style="float: right">
                                        <ul class="pagination rounded-corners">
                                            @if (isset($transport_orders))
                                                {{ $transport_orders->links() }} 
                                            @endif 
                                        </ul>
                                    </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Transport Order?</strong> </center> 
                </div>
                <form wire:submit.prevent="destroy()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Create Transport Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="with_quotation"   class="line-style" />
                            <label for="one" class="radio-label">Attach a quotation</label>
                            @error('with_quotation') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        @if ($with_quotation == True)
                            <div class="form-group">
                                <label for="trip_type">
                                    <a href="{{ route('quotations.index') }}" target="_blank" style="color: blue">Quotations</a>
                                    <span class="required text-danger">*</span>
                                </label>
                                <select class="form-control" wire:model.debounce.300ms="selectedQuotation" required>
                                    <option value="">Select Quotation</option>
                                    @foreach ($quotations as $quotation)
                                        <option value="{{ $quotation->id }}">{{ $quotation->quotation_number }}{{ $quotation->custom_ref ? " / ".$quotation->custom_ref : "" }} {{ $quotation->customer ? $quotation->customer->name : "" }} {{$quotation->currency ? $quotation->currency->name : ""}} {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->total ? $quotation->total : 0, 2)}}</option>
                                    @endforeach
                                </select>
                                @error('selectedQuotation') <span class="text-danger error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="transport_order_ref">Custom Reference</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Custom Order Reference#"  />
                                    @error('custom_ref') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <!-- Trip Type -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trip_type">
                                        <a href="{{ route('trip_types.index') }}" target="_blank" style="color: blue">Trip Types</a>
                                        <span class="required text-danger">*</span>
                                    </label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedTripType" required>
                                        <option value="">Select Trip Type</option>
                                        @foreach ($trip_types as $trip_type)
                                            <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedTripType') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>   
                           
                            <!-- Trip Group -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="status" required>
                                        <option value="">Select Status</option>
                                            <option value="Scheduled">Scheduled</option>
                                            <option value="Started">Started</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                    </select>
                                    @error('status') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                      
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer"><a href="{{ route('customers.index') }}" target="_blank" style="color: blue">Customer(s)</a><span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"><a href="{{route('consignees.index')}}" style="color: blue" target="_blank">Consignees</a></label>
                                    <select class="form-control" wire:model.debounce.300ms="consignee_id">
                                        <option value="">Select Consignee</option>
                                        @foreach ($consignees as $consignee)
                                            <option value="{{$consignee->id}}">{{$consignee->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('consignees.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Consignee</a></small> <a href="#" wire:click.prevent="refresh('consignees')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('consignee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <h5 class="underline mt-30">Cargo Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer"><a href="{{ route('cargos.index') }}" target="_blank" style="color: blue">Cargo(s)</a><span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCargo" required>
                                        <option value="">Select Cargo</option>
                                        @foreach ($cargos as $cargo)
                                            <option value="{{$cargo->id}}">{{$cargo->name}} {{$cargo->sku}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCargo') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> <a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="additional">Additional Cargo Details</label>
                                    <input type="text"  class="form-control" wire:model.debounce.300ms="cargo_details" placeholder="Additional Remarks">
                                    @error('cargo_details') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="weight">Weight(t)</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Gross Weight" >
                                    @error('weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if (isset($selectedCargo))
                            <div class="row">
                                <div class="col-md-6">
                                    @if (isset($cargo_type) && $cargo_type == "Solid")
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Cargo Quantity" >
                                            @error('quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    @elseif(isset($cargo_type) && $cargo_type == "Liquid")   
                                        <div class="form-group">
                                            <label for="quantity">Litreage</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Cargo Litreage" >
                                            @error('litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div> 
                                    @endif
                                   
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer">Units Of Measure</label>
                                        <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                            <option value="">Select Unit Of Measure</option>
                                                @foreach ($units_of_measures as $units_of_measure)
                                                    <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                @endforeach
                                        </select>
                                        @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>    
                        @endif
                           
                       
                        @if ($company->rates_managed_by_finance == 0 || ($company->rates_managed_by_finance == 1 && (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))))
                            <h5 class="underline mt-30">Freight Calculation Method<span class="required" style="color: red">*</span></h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required />
                                            <label for="one" class="radio-label">Flat Rate</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance</label>
                                            @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                       
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('currencies.index') }}" target="_blank" style="color: blue">Currencies
                                            @if ($rate)
                                                <span class="required" style="color: red">*</span>
                                            @endif    
                                            </a></label>
                                            <select class="form-control" wire:model.debounce.300ms="selectedCurrency" {{$rate ? "required" : ""}}  {{ !isset($company->currency_id) ? "disabled" : ""  }} >
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                            @if (!isset($company->currency_id))
                                            <small style="color:red">Default company trading currency not set</small>
                                            <br>
                                            @endif
                                            <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                        @if (!is_null($selectedCurrency))
                                            @if ($company)
                                                @if ($selectedCurrency != $company->currency_id)
                                                <div class="form-group">
                                                    <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                    <small>{{$exchange_customer_freight ? "The customer converted amount is: ".$exchange_customer_freight : ""}}</small> <br>
                                                    <small>{{$exchange_transporter_freight ? "The transporter converted amount is: ".$exchange_transporter_freight : ""}}</small>
                                                </div> 
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <h5 class="underline mt-10">Customer Freight Agreement</h5>
                                <div class="form-group" >
                                    <label for="name">Customer Rates</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="rates" name="optradio_customer" >Predefined Rates
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="custom" name="optradio_customer">Custom Rate
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        @if (!is_null($with_customer_rates))
                                            @if ($with_customer_rates == "rates")
                                            <div class="form-group">
                                                <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="selectedDefinedCustomerRate" required>
                                                    <option value="">Select Rate</option>
                                                    @foreach ($defined_customer_rates as $rate)
                                                        {{ $this->getDestination($rate->from) ? $this->getDestination($rate->from)->country->name : ""}}
                                                        <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedDefinedCustomerRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                
                                                @if (in_array('Finance', $department_names) || in_array('Management', $rank_names) || in_array('Super Admin', $role_names))
                                                <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                @endif
                                            </div>
                                            @elseif($with_customer_rates == "custom")
                                            <div class="form-group">
                                                <label for="weight">Rate</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="rate" placeholder="Enter Rate" >
                                                @error('rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="weight">Freight</label>
                                            <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="freight" disabled placeholder="Enter Freight"  >
                                            @error('freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>  
                                          <h5 class="underline mt-10">Transporter Freight Agreement</h5>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="transporter_agreement"   class="line-style" />
                                        <label for="one" class="radio-label">Transporter</label>
                                        @error('transporter_agreement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if ($transporter_agreement == True)
                                    <div class="form-group" >
                                        <label for="name">Transporter Rates</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="rates" name="optradio_transporter" >Predefined Rates
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="custom" name="optradio_transporter">Custom Rate
                                          </label>
                                    </div>
                                    <div class="row">
                                      
                                        <div class="col-md-6">
                                                  @if (!is_null($with_transporter_rates))
                                                @if ($with_transporter_rates == "rates")
                                                <div class="form-group">
                                                    <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                  <select class="form-control" wire:model.debounce.300ms="selectedDefinedTransporterRate" required>
                                                      <option value="">Select Rate</option>
                                                      @foreach ($defined_transporter_rates as $rate)
                                                        <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                      @endforeach
                                                  </select>
                                                    @error('selectedDefinedTransporterRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                                @elseif($with_transporter_rates == "custom")
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" max="{{ $rate }}"  class="form-control"  wire:model.debounce.300ms="transporter_rate" placeholder="Enter Transporter Rate" >
                                                    @error('transporter_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    @if ($transporter_rate > $rate)
                                                    <small style="color: red"> Transporter agreed rate cannot be greater than customer agreed rate.</small>
                                                @endif
                                                </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="weight">Freight</label>
                                                <input type="number" step="any" min="0"  max="{{ $freight }}" class="form-control"  wire:model.debounce.300ms="transporter_freight" placeholder=" Transporter Freight" />
                                                @error('transporter_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                                @if ($transporter_freight > $freight)
                                                    <small style="color: red"> Transporter agreed freight cannot be greater than customer agreed freight.</small>
                                                @endif
                                            </div>
                                        </div>
    
                                        <!-- /.col-md-6 -->
                                    </div>
                                   
                                    @endif 
                            @endif
                            <h5 class="underline mt-30">Location Details</h5>
                            <div class="mb-15 mt-15">
                                <input type="checkbox" wire:model.debounce.300ms="multiple_destinations"   class="line-style" />
                                <label for="one" class="radio-label">Add multiple loading / offloading points</label>
                                @error('multiple_destinations') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if ($multiple_destinations == False)
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">From</a><span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchFrom" placeholder="Search origin locations..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedFrom" size="4" required>
                                                <option value="">Select From Location</option>
                                                @foreach ($from_destinations as $destination)
                                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedFrom') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                            <input type="text" wire:model.debounce.300ms="searchLoadingPoint" placeholder="Search loading points..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="loading_point_id"  size="4">
                                                <option value="">Select Loading Point</option>
                                                @foreach ($loading_points as $loading_point)
                                                <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                                @endforeach
                                            </select>
                                            @error('loading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search destination locations..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedTo" size="4" required>
                                                <option value="">Select To Location</option>
                                                @foreach ($to_destinations as $destination)
                                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTo') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                            <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search offloading points..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="offloading_point_id" size="4" >
                                                <option value="">Select Offloading Point</option>
                                                @foreach ($offloading_points as $offloading_point)
                                                    <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                                @endforeach
                                            </select>
                                            @error('offloading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="origin"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">From</a><span class="required" style="color: red">*</span></label>
                                                    <input type="text" wire:model.debounce.300ms="searchFrom" placeholder="Search origin locations..." class="form-control">
                                                    <select class="form-control" wire:model.debounce.300ms="destinations_selectedFrom.0" size="4" required>
                                                        <option value="">Select From Location</option>
                                                        @foreach ($from_destinations as $destination)
                                                            <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('destinations_selectedFrom.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="destination"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                                    <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search loading points..." class="form-control">
                                                    <select class="form-control" wire:model.debounce.300ms="destinations_loading_point_id.0" size="4" >
                                                        <option value="">Select Loading Point</option>
                                                        @foreach ($loading_points as $loading_point)
                                                            <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('destinations_loading_point_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Points</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="weight">Weight(t)</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_weight.0" placeholder="Loading Weight" >
                                                    @error('loaded_weight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @if ($cargo_type == "Solid")
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Quantity</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_quantity.0" placeholder="Loading Qty" >
                                                        @error('loaded_quantity.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="customer">Units Of Measure</label>
                                                        <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                            <option value="">Select Unit Of Measure</option>
                                                                @foreach ($units_of_measures as $units_of_measure)
                                                                    <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            @elseif($cargo_type == "Liquid")
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Litreage @ Ambient</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage.0" placeholder="Loading Litreage @ Ambient" >
                                                        @error('loaded_litreage.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Litreage @ 20</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage_at_20.0" placeholder="Loading Litreage @ 20" >
                                                        @error('loaded_litreage_at_20.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_rate.0" placeholder="Loading Rate" >
                                                    @error('loaded_rate.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="weight">Freight</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_freight.0" placeholder="Loading Freight" >
                                                    @error('loaded_freight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach ($origins_inputs as $key => $value)
                                        <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="origin"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                                        <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search origin locations..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_selectedFrom.{{$value}}" size="4" required>
                                                            <option value="">Select From Location</option>
                                                            @foreach ($from_destinations as $destination)
                                                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_selectedFrom.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('origins')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="origin"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                                        <input type="text" wire:model.debounce.300ms="searchLoadingPoint" placeholder="Search loading points..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_loading_point_id.{{$value}}" size="4" >
                                                            <option value="">Select Loading Point</option>
                                                            @foreach ($loading_points as $loading_point)
                                                                <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_loading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Points</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weight">Weight(t)</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_weight.{{$value}}" placeholder="Loading Weight(t)" >
                                                        @error('loaded_weight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @if ($cargo_type == "Solid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Quantity</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_quantity.{{$value}}" placeholder="Loading Qty" >
                                                            @error('loaded_quantity.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="customer">Units Of Measure</label>
                                                            <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                                <option value="">Select Unit Of Measure</option>
                                                                    @foreach ($units_of_measures as $units_of_measure)
                                                                        <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                    @endforeach
                                                            </select>
                                                            @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @elseif($cargo_type == "Liquid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Litreage @ Ambient</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage.{{$value}}" placeholder="Loading Litreage @ Ambient" >
                                                            @error('loaded_litreage.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Litreage @ 20</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage_at_20.{{$value}}" placeholder="Loading Litreage @ 20" >
                                                            @error('loaded_litreage_at_20.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Rate</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_rate.{{$value}}" placeholder="Loading Rate" >
                                                        @error('loaded_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="weight">Freight</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_freight.{{$value}}" placeholder="Loading Freight" >
                                                        @error('loaded_freight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group" style="margin-top:30%">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="marging-left:-25px"   wire:click.prevent="removeOrigin({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="row mt-10 mb-15">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="addOrigin({{$or}})"> <i class="fa fa-plus"></i> Loading Point</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                                    <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search destination locations..." class="form-control">
                                                    <select class="form-control" wire:model.debounce.300ms="destinations_selectedTo.0" size="4" required>
                                                        <option value="">Select To Location</option>
                                                        @foreach ($to_destinations as $destination)
                                                            <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('destinations_selectedTo.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                                    <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search offloading points..." class="form-control">
                                                    <select class="form-control" wire:model.debounce.300ms="destinations_offloading_point_id.0" size="4" >
                                                        <option value="">Select Offloading Point</option>
                                                        @foreach ($offloading_points as $offloading_point)
                                                            <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('destinations_offloading_point_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="weight">Weight(t)</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_weight.0" placeholder="Offloading Weight" >
                                                    @error('offloaded_weight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @if ($cargo_type == "Solid")
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Quantity</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_quantity.0" placeholder="Offloading Qty" >
                                                        @error('offloaded_quantity.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="customer">Units Of Measure</label>
                                                        <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                            <option value="">Select Unit Of Measure</option>
                                                                @foreach ($units_of_measures as $units_of_measure)
                                                                    <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            @elseif($cargo_type == "Liquid")
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Litreage @ Ambient</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage.0" placeholder="Offloading Litreage @ Ambient" >
                                                        @error('offloaded_litreage.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Litreage @ 20</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage_at_20.0" placeholder="Offloading Litreage @ 20" >
                                                        @error('offloaded_litreage_at_20.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_rate.0" placeholder="Offloading Rate" >
                                                    @error('offloaded_rate.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="weight">Freight</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_freight.0" placeholder="Offloading Freight" >
                                                    @error('offloaded_freight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach ($destinations_inputs as $key => $value)
                                        <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                                        <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search destination locations..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_selectedTo.{{$value}}" size="4" required>
                                                            <option value="">Select To Location</option>
                                                            @foreach ($to_destinations as $destination)
                                                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_selectedTo.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                                        <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search offloading points..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_offloading_point_id.{{$value}}" size="4" >
                                                            <option value="">Select Offloading Point</option>
                                                            @foreach ($offloading_points as $offloading_point)
                                                                <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_offloading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weight">Weight(t)</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_weight.{{$value}}" placeholder="Offloading Weight(t)" >
                                                        @error('offloaded_weight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @if ($cargo_type == "Solid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Quantity</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_quantity.{{$value}}" placeholder="Offloading Qty" >
                                                            @error('offloaded_quantity.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="customer">Units Of Measure</label>
                                                            <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                                <option value="">Select Unit Of Measure</option>
                                                                    @foreach ($units_of_measures as $units_of_measure)
                                                                        <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                    @endforeach
                                                            </select>
                                                            @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @elseif($cargo_type == "Liquid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Litreage @ Ambient</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage.{{$value}}" placeholder="Offloading Litreage @ Ambient" >
                                                            @error('offloaded_litreage.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Litreage @ 20</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage_at_20.{{$value}}" placeholder="Offloading Litreage @ 20" >
                                                            @error('offloaded_litreage_at_20.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Rate</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_rate.{{$value}}" placeholder="Offloading Rate" >
                                                        @error('offloaded_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="weight">Freight</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_freight.{{$value}}" placeholder="Offloading Freight" >
                                                        @error('offloaded_freight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group" style="margin-top:30%">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="marging-left:-25px"   wire:click.prevent="removeDestination({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="row mt-10 mb-15">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="addDestination({{$d}})"> <i class="fa fa-plus"></i>Offloading Point</button>
                                            </div>
                                        </div>
                                    </div>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="distance">Trip Distance</label>
                                        <input type="number" min="1" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance (Kms)"  >
                                        @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>      
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">Trip Start Date<span class="required" style="color: red">*</span></label>
                                        <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                        @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">Estimated Trip End Date<span class="required" style="color: red">*</span></label>
                                        <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter End Date" required>
                                        @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>         
                        </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="editModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Update Transport Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="with_quotation"   class="line-style" />
                            <label for="one" class="radio-label">Attach a quotation</label>
                            @error('with_quotation') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        @if ($with_quotation == True)
                            <div class="form-group">
                                <label for="trip_type">
                                    <a href="{{ route('quotations.index') }}" target="_blank" style="color: blue">Quotations</a>
                                    <span class="required text-danger">*</span>
                                </label>
                                <select class="form-control" wire:model.debounce.300ms="selectedQuotation" required>
                                    <option value="">Select Quotation</option>
                                    @foreach ($quotations as $quotation)
                                        <option value="{{ $quotation->id }}">{{ $quotation->quotation_number }}{{ $quotation->custom_ref ? " / ".$quotation->custom_ref : "" }} {{ $quotation->customer ? $quotation->customer->name : "" }} {{$quotation->currency ? $quotation->currency->name : ""}} {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->total ? $quotation->total : 0, 2)}}</option>
                                    @endforeach
                                </select>
                                @error('selectedQuotation') <span class="text-danger error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="transport_order_ref">Custom Reference</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Custom Order Reference#"  />
                                    @error('custom_ref') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <!-- Trip Type -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trip_type">
                                        <a href="{{ route('trip_types.index') }}" target="_blank" style="color: blue">Trip Types</a>
                                        <span class="required text-danger">*</span>
                                    </label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedTripType" required>
                                        <option value="">Select Trip Type</option>
                                        @foreach ($trip_types as $trip_type)
                                            <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedTripType') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>   
                           
                            <!-- Trip Group -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="status" required>
                                        <option value="">Select Status</option>
                                            <option value="Scheduled">Scheduled</option>
                                            <option value="Started">Started</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                    </select>
                                    @error('status') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                      
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer"><a href="{{ route('customers.index') }}" target="_blank" style="color: blue">Customer(s)</a><span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"><a href="{{route('consignees.index')}}" style="color: blue" target="_blank">Consignees</a></label>
                                    <select class="form-control" wire:model.debounce.300ms="consignee_id">
                                        <option value="">Select Consignee</option>
                                        @foreach ($consignees as $consignee)
                                            <option value="{{$consignee->id}}">{{$consignee->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('consignees.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Consignee</a></small> <a href="#" wire:click.prevent="refresh('consignees')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('consignee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <h5 class="underline mt-30">Cargo Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer"><a href="{{ route('cargos.index') }}" target="_blank" style="color: blue">Cargo(s)</a><span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCargo" required>
                                        <option value="">Select Cargo</option>
                                        @foreach ($cargos as $cargo)
                                            <option value="{{$cargo->id}}">{{$cargo->name}} {{$cargo->sku}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCargo') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> <a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="additional">Additional Cargo Details</label>
                                    <input type="text"  class="form-control" wire:model.debounce.300ms="cargo_details" placeholder="Additional Remarks">
                                    @error('cargo_details') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="weight">Weight(t)</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Gross Weight" >
                                    @error('weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if (isset($selectedCargo))
                            <div class="row">
                                <div class="col-md-6">
                                    @if (isset($cargo_type) && $cargo_type == "Solid")
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Cargo Quantity" >
                                            @error('quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    @elseif(isset($cargo_type) && $cargo_type == "Liquid")   
                                        <div class="form-group">
                                            <label for="quantity">Litreage</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Cargo Litreage" >
                                            @error('litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div> 
                                    @endif
                                   
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer">Units Of Measure</label>
                                        <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                            <option value="">Select Unit Of Measure</option>
                                                @foreach ($units_of_measures as $units_of_measure)
                                                    <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                @endforeach
                                        </select>
                                        @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>    
                        @endif
                           
                       
                        @if ($company->rates_managed_by_finance == 0 || ($company->rates_managed_by_finance == 1 && (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))))
                            <h5 class="underline mt-30">Freight Calculation Method<span class="required" style="color: red">*</span></h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required />
                                            <label for="one" class="radio-label">Flat Rate</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance</label>
                                            @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                       
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('currencies.index') }}" target="_blank" style="color: blue">Currencies
                                            @if ($rate)
                                                <span class="required" style="color: red">*</span>
                                            @endif    
                                            </a></label>
                                            <select class="form-control" wire:model.debounce.300ms="selectedCurrency" {{$rate ? "required" : ""}}  {{ !isset($company->currency_id) ? "disabled" : ""  }} >
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                            @if (!isset($company->currency_id))
                                            <small style="color:red">Default company trading currency not set</small>
                                            <br>
                                            @endif
                                            <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                        @if (!is_null($selectedCurrency))
                                            @if ($company)
                                                @if ($selectedCurrency != $company->currency_id)
                                                <div class="form-group">
                                                    <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                    <small>{{$exchange_customer_freight ? "The customer converted amount is: ".$exchange_customer_freight : ""}}</small> <br>
                                                    <small>{{$exchange_transporter_freight ? "The transporter converted amount is: ".$exchange_transporter_freight : ""}}</small>
                                                </div> 
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <h5 class="underline mt-10">Customer Freight Agreement</h5>
                                <div class="form-group" >
                                    <label for="name">Customer Rates</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="rates" name="optradio_customer" >Predefined Rates
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="custom" name="optradio_customer">Custom Rate
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        @if (!is_null($with_customer_rates))
                                            @if ($with_customer_rates == "rates")
                                            <div class="form-group">
                                                <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="selectedDefinedCustomerRate" required>
                                                    <option value="">Select Rate</option>
                                                    @foreach ($defined_customer_rates as $rate)
                                                        {{ $this->getDestination($rate->from) ? $this->getDestination($rate->from)->country->name : ""}}
                                                        <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedDefinedCustomerRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                
                                                @if (in_array('Finance', $department_names) || in_array('Management', $rank_names) || in_array('Super Admin', $role_names))
                                                <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                @endif
                                            </div>
                                            @elseif($with_customer_rates == "custom")
                                            <div class="form-group">
                                                <label for="weight">Rate</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="rate" placeholder="Enter Rate" >
                                                @error('rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="weight">Freight</label>
                                            <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="freight" disabled placeholder="Enter Freight"  >
                                            @error('freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>  
                                          <h5 class="underline mt-10">Transporter Freight Agreement</h5>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="transporter_agreement"   class="line-style" />
                                        <label for="one" class="radio-label">Transporter</label>
                                        @error('transporter_agreement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if ($transporter_agreement == True)
                                    <div class="form-group" >
                                        <label for="name">Transporter Rates</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="rates" name="optradio_transporter" >Predefined Rates
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="custom" name="optradio_transporter">Custom Rate
                                          </label>
                                    </div>
                                    <div class="row">
                                      
                                        <div class="col-md-6">
                                                  @if (!is_null($with_transporter_rates))
                                                @if ($with_transporter_rates == "rates")
                                                <div class="form-group">
                                                    <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                  <select class="form-control" wire:model.debounce.300ms="selectedDefinedTransporterRate" required>
                                                      <option value="">Select Rate</option>
                                                      @foreach ($defined_transporter_rates as $rate)
                                                        <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                      @endforeach
                                                  </select>
                                                    @error('selectedDefinedTransporterRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                                @elseif($with_transporter_rates == "custom")
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" max="{{ $rate }}"  class="form-control"  wire:model.debounce.300ms="transporter_rate" placeholder="Enter Transporter Rate" >
                                                    @error('transporter_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    @if ($transporter_rate > $rate)
                                                    <small style="color: red"> Transporter agreed rate cannot be greater than customer agreed rate.</small>
                                                @endif
                                                </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="weight">Freight</label>
                                                <input type="number" step="any" min="0"  max="{{ $freight }}" class="form-control"  wire:model.debounce.300ms="transporter_freight" placeholder=" Transporter Freight" />
                                                @error('transporter_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                                @if ($transporter_freight > $freight)
                                                    <small style="color: red"> Transporter agreed freight cannot be greater than customer agreed freight.</small>
                                                @endif
                                            </div>
                                        </div>
    
                                        <!-- /.col-md-6 -->
                                    </div>
                                    @endif 
                            @endif
                            <h5 class="underline mt-30">Location Details</h5>
                            <div class="mb-15 mt-15">
                                <input type="checkbox" wire:model.debounce.300ms="multiple_destinations"   class="line-style" />
                                <label for="one" class="radio-label">Add multiple loading / offloading points</label>
                                @error('multiple_destinations') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if ($multiple_destinations == False)
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">From</a><span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchFrom" placeholder="Search origin locations..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedFrom" size="4" required>
                                                <option value="">Select From Location</option>
                                                @foreach ($from_destinations as $destination)
                                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedFrom') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                            <input type="text" wire:model.debounce.300ms="searchLoadingPoint" placeholder="Search loading points..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="loading_point_id"  size="4">
                                                <option value="">Select Loading Point</option>
                                                @foreach ($loading_points as $loading_point)
                                                <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                                @endforeach
                                            </select>
                                            @error('loading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search destination locations..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedTo" size="4" required>
                                                <option value="">Select To Location</option>
                                                @foreach ($to_destinations as $destination)
                                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTo') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                            <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search offloading points..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="offloading_point_id" size="4" >
                                                <option value="">Select Offloading Point</option>
                                                @foreach ($offloading_points as $offloading_point)
                                                    <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                                @endforeach
                                            </select>
                                            @error('offloading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                 @foreach ($trip_origins as $key => $value)
                                        <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">From</a><span class="required" style="color: red">*</span></label>
                                                        <input type="text" wire:model.debounce.300ms="searchFrom" placeholder="Search destination locations..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_selectedFrom.{{$key}}" size="4" required>
                                                            <option value="">Select From Location</option>
                                                            @foreach ($to_destinations as $destination)
                                                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_selectedFrom.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                                        <input type="text" wire:model.debounce.300ms="searchLoadingPoint" placeholder="Search offloading points..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_loading_point_id.{{$key}}" size="4" >
                                                            <option value="">Select Loading Point</option>
                                                            @foreach ($loading_points as $loading_point)
                                                                <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_loading_point_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Points</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weight">Loaded Weight(t)</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_weight.{{$key}}" placeholder="Weight" >
                                                        @error('loaded_weight.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @if ($cargo_type == "Solid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Loaded Quantity</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_quantity.{{$key}}" placeholder="Qty" >
                                                            @error('loaded_quantity.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="customer">Units Of Measure</label>
                                                            <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                                <option value="">Select Unit Of Measure</option>
                                                                    @foreach ($units_of_measures as $units_of_measure)
                                                                        <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                    @endforeach
                                                            </select>
                                                            @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @elseif($cargo_type == "Liquid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Loaded Litreage @ Ambient</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage.{{$key}}" placeholder="Litreage" >
                                                            @error('loaded_litreage.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Loaded Litreage @ 20</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_litreage_at_20.{{$key}}" placeholder="Litreage @ 20" >
                                                            @error('loaded_litreage_at_20.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight"> Loaded Rate</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_rate.{{$key}}" placeholder="Rate" >
                                                        @error('loaded_rate.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Loaded Freight</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="loaded_freight.{{$key}}" placeholder="Freight" >
                                                        @error('loaded_freight.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach ($trip_destinations as $key => $value)
                                        <div class="mt-15 mb-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                                        <input type="text" wire:model.debounce.300ms="searchTo" placeholder="Search destination locations..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_selectedTo.{{$key}}" size="4" required>
                                                            <option value="">Select To Location</option>
                                                            @foreach ($to_destinations as $destination)
                                                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_selectedTo.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                                        <input type="text" wire:model.debounce.300ms="searchOffloadingPoint" placeholder="Search offloading points..." class="form-control">
                                                        <select class="form-control" wire:model.debounce.300ms="destinations_offloading_point_id.{{$key}}" size="4" >
                                                            <option value="">Select Offloading Point</option>
                                                            @foreach ($offloading_points as $offloading_point)
                                                                <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('destinations_offloading_point_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="weight">Offloaded Weight(t)</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_weight.{{$key}}" placeholder="Weight" >
                                                        @error('offloaded_weight.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @if ($cargo_type == "Solid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Offloaded Quantity</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_quantity.{{$key}}" placeholder="Qty" >
                                                            @error('offloaded_quantity.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="customer">Units Of Measure</label>
                                                            <select class="form-control" wire:model.debounce.300ms="units_of_measure_id" >
                                                                <option value="">Select Unit Of Measure</option>
                                                                    @foreach ($units_of_measures as $units_of_measure)
                                                                        <option value="{{ $units_of_measure->id }}">{{ $units_of_measure->name }} {{ $units_of_measure->abbreviation ? "(".$units_of_measure->abbreviation.")" : "" }}</option>
                                                                    @endforeach
                                                            </select>
                                                            @error('units_of_measure_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @elseif($cargo_type == "Liquid")
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Offloaded Litreage @ Ambient</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage.{{$key}}" placeholder="Litreage" >
                                                            @error('offloaded_litreage.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="weight">Offloaded Litreage @ 20</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_litreage_at_20.{{$key}}" placeholder="Litreage @ 20" >
                                                            @error('offloaded_litreage_at_20.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Offloaded Rate</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_rate.{{$key}}" placeholder="Rate" >
                                                        @error('offloaded_rate.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="weight">Offloaded Freight</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="offloaded_freight.{{$key}}" placeholder="Freight" >
                                                        @error('offloaded_freight.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="distance">Trip Distance</label>
                                        <input type="number" min="1" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance (Kms)"  >
                                        @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>      
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">Trip Start Date<span class="required" style="color: red">*</span></label>
                                        <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                        @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">Estimated Trip End Date<span class="required" style="color: red">*</span></label>
                                        <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter End Date" required>
                                        @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>         
                        </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>

    

</div>

