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
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Cargos
                                                </span>
                                                <select wire:model.debounce.300ms="filter_cargo_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Cargo</option>
                                                        @foreach ($cargos as $cargo)
                                                        <option value="{{ $cargo->id }}"  >{{ ucfirst($cargo->name) }}</option>
                                                        @endforeach
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                         <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Currency
                                                </span>
                                                <select wire:model.debounce.300ms="filter_currency_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name}}</option> 
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Status
                                                </span>
                                                <select wire:model.debounce.300ms="filter_transport_order_status" class="form-control  " aria-label="..." >
                                                        <option value="">Select Status</option>
                                                        <option value="Scheduled">Pending</option>
                                                        <option value="Loading Point">Ongoing</option>
                                                        <option value="Loaded">Loaded</option>
                                                        <option value="Instransit">Instransit</option>
                                                        <option value="Offloading Point">Offloading Point</option>
                                                        <option value="Offloaded">Offloaded</option>
                                                        <option value="Onhold">Onhold</option>
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Trip Type
                                                </span>
                                                <select wire:model.debounce.300ms="filter_trip_type_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Trip Type</option>
                                                        @foreach ($trip_types as $trip_type)
                                                            <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option> 
                                                        @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Customers
                                                </span>
                                                <select wire:model.debounce.300ms="filter_customer_id" class="form-control" aria-label="..." >
                                                        <option value="">Select Customer</option>
                                                        @foreach ($customers as $customer)
                                                            <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
                                                        @endforeach
                                                </select>	
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                       <div class="col-md-3">
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    Consignees
                                                </span>
                                                <select wire:model.debounce.300ms="filter_consignee_id" class="form-control  " aria-label="..." >
                                                        <option value="">Select Consignee</option>
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
                                            <a href="#"  class="btn btn-default btn-wide" aria-haspopup="true" aria-expanded="true"><i class="fa fa-plus-square-o"></i>Transport Order</a>
                                            <a href="" data-toggle="modal" data-target="#transport_ordersImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                            <a href="#" wire:click.prevent="exporttransport_ordersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                        </div>
                                         
                                    </div>
                                </div>
                                <br>
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search: transport_order#, date(yyyy-mm-dd), transporter,customer,VRN/HRN,CreatedBy,POD#...">
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

                                    $statusMap = [
                                        'Offloaded'        => ['row' => '#5cb85c', 'cell' => 'table-success', 'badge' => 'success'],
                                        'Scheduled'        => ['row' => '#f0ad4e', 'cell' => 'table-warning', 'badge' => 'warning'],
                                        'Loading Point'    => ['row' => '#adb5bd', 'cell' => 'table-secondary', 'badge' => 'secondary'],
                                        'Loaded'           => ['row' => '#5bc0de', 'cell' => 'table-info', 'badge' => 'info'],
                                        'Started'          => ['row' => '#1976D2', 'cell' => 'table-primary', 'badge' => 'primary'],
                                        'InTransit'        => ['row' => '#1976D2', 'cell' => 'table-primary', 'badge' => 'primary'],
                                        'Offloading Point' => ['row' => '#82B1FF', 'cell' => 'table-info', 'badge' => 'info'],
                                        'OnHold'           => ['row' => '#d9534f', 'cell' => 'table-danger', 'badge' => 'danger'],
                                        'Cancelled'        => ['row' => '#C4A484', 'cell' => 'table-light', 'badge' => 'light'],
                                    ];
                                @endphp
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-stransport_ordered table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>Order#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Departure <hr style="margin-top:2px; margin-bottom:2px">Offloaded</th>
                                                <th>Customer<hr style="margin-top:2px; margin-bottom:2px">Cargo</th>
                                                <th>Transporter<hr style="margin-top:2px; margin-bottom:2px">Driver</th>
                                                <th>Horse/Vehicle<hr style="margin-top:2px; margin-bottom:2px">Trailer</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                @if($showFreight)
                                                    <th>Freight</th>
                                                @endif
                                                <th>Invoice<hr style="margin-top:2px; margin-bottom:2px">POD</th>
                                                <th>Auth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($transport_orders as $transport_order)
                                            @php
                                                $s = $statusMap[$transport_order->transport_order_status] ?? ['row' => null, 'cell' => '', 'badge' => 'secondary'];
                                                $offloadedDate = $transport_order->delivery_note?->offloaded_date;
                                                $pod = $transport_order->pod;
                                                $proofOfDelivery = App\Models\transport_orderDocument::where('transport_order_id', $transport_order->id)->where('title', 'POD')->first();
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}" @endif>
                                                <td>
                                                    <strong>{{ $transport_order->transport_order_number }}@if($transport_order->transport_order_ref)/{{ $transport_order->transport_order_ref }}@endif</strong>
                                                    <br>
                                                    <small>
                                                        <strong>CreatedBy:</strong>  {{ $transport_order->user?->name }} {{ $transport_order->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $transport_order->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $transport_order->transport_order_type?->name }}
                                                    @if($transport_order->haulage_type)
                                                        <small><strong>{{ $transport_order->haulage_type == "short_haul" ? "(Short Haul)" : "(Long Haul)"}}</strong></small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $formatDate($transport_order->start_date) }}
                                                    <hr class="my-1">
                                                    {{ $formatDate($offloadedDate) }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($transport_order->customer?->name ?? '') }}
                                                    @if($transport_order->cargo)
                                                        <hr class="my-1">
                                                        {{ ucfirst($transport_order->cargo?->name ?? '') }}
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ ucfirst($transport_order->transporter?->name ?? '') }}
                                                    @if($transport_order->driver)
                                                        <hr class="my-1">
                                                        {{ $transport_order->driver?->employee?->name }} {{ $transport_order->driver?->employee?->surname }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($transport_order->horse)
                                                        Horse | {{ $transport_order->horse->registration_number }} {{ $transport_order->horse->fleet_number ? "({$transport_order->horse->fleet_number})" : "" }}
                                                    @elseif($transport_order->vehicle)
                                                        Vehicle | {{ $transport_order->vehicle->registration_number }} {{ $transport_order->vehicle->fleet_number ? "({$transport_order->vehicle->fleet_number})" : "" }}
                                                    @endif

                                                    @if($transport_order->trailers?->count())
                                                        <hr class="my-1">
                                                        @foreach($transport_order->trailers as $trailer)
                                                            {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "({$trailer->fleet_number})" : "" }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @endif
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

                                                <td class="{{ $s['cell'] }}" style="padding: 5px;">
                                                    <span class="label label-{{ $s['badge'] }} label-wide">
                                                        {{ $transport_order->transport_order_status }}
                                                        @if($transport_order->authorization === "approved")
                                                            <a href="#" wire:click="status({{ $transport_order->id }})" class="ms-1">
                                                                <i class="fa fa-edit" style="color:black"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $transport_order->currency?->name }} {{ $transport_order->currency?->symbol }}
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

                                                    <hr class="my-1">
                                                    @if (isset($pod))
                                                        <span class="label label-{{ $pod ? 'success' : 'warning' }}">
                                                            {{ $pod ? "Submitted On: {$pod->date}" : "pending" }}
                                                        </span>
                                                       <div class="text-center"> {{ $pod->document_number ? "POD#: ".$pod->document_number : "" }}</div>
                                                    @elseif (isset($proofOfDelivery))
                                                        <span class="label label-{{ $proofOfDelivery ? 'success' : 'warning' }}">
                                                            {{ $proofOfDelivery ? "Submitted On: {$proofOfDelivery->date}" : "pending" }}
                                                        </span>
                                                        <div class="text-center"> {{ $proofOfDelivery->document_number ? "POD#: ".$proofOfDelivery->document_number : "" }}</div>
                                                    @else
                                                        <span class="label label-warning">
                                                            pending
                                                        </span>
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
                                                    @include('transport_orders.partials.actions', ['transport_order' => $transport_order, 'user' => $user, 'employee' => $employee])
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No transport_orders Found ....
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="teamModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Team <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                                     <h5 class="underline mt-n">Trip Details</h5>
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
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="trip_ref">Custom Reference</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="trip_ref" placeholder="Custom Trip Reference#"  />
                                            @error('trip_ref') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <!-- Trip Type -->
                                    <div class="col-md-3">
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
                                
                                    <!-- Trip Reference -->
                                   
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="trip_type">Trip Haulage</a>
                                            </label>
                                            <select class="form-control" wire:model.debounce.300ms="haulage_type" {{$trip_type_name != "Local" ? "disabled" : ""}}>
                                                <option value="">Select Option</option>
                                                <option value="short_haul">Short Haul</option>
                                                <option value="long_haul">Long Haul</option>
                                            </select>
                                            @error('haulage_type') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <!-- Trip Group -->
                                    
                                </div>
                             
                                <!-- Borders & Clearing Agents Section -->
                                @if (in_array($trip_type_name, ['Intransit', 'Cross Border', 'Inward', 'Outward', 'Return']))
                                    <div class="row">
                                        <!-- First Border & Agent -->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="borders">
                                                            <a href="{{ route('borders.index') }}" target="_blank" style="color: blue">Border(s)</a>
                                                        </label>
                                                        <select class="form-control" wire:model.debounce.300ms="selectedBorder.0">
                                                            <option value="">Select Border</option>
                                                            @if (!is_null($selectedTripType))
                                                                @foreach ($borders as $border)
                                                                    <option value="{{ $border->id }}">{{ $border->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <small>
                                                            <a href="{{ route('borders.index') }}" target="_blank">
                                                                <i class="fa fa-plus-square-o"></i> New Border
                                                            </a>
                                                        </small>
                                                        <a href="#" wire:click.prevent="refresh('borders')" class="float-end">
                                                            <i class="fa fa-refresh"></i>
                                                        </a>
                                                        @error('selectedBorder.0') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="clearing_agents">
                                                            <a href="{{ route('clearing_agents.index') }}" target="_blank" style="color: blue">Clearing Agent(s)</a>
                                                        </label>
                                                        <select class="form-control" wire:model.debounce.300ms="clearing_agent_id.0">
                                                            <option value="">Select Agent</option>
                                                            @if (!is_null($selectedBorder))
                                                                @foreach ($clearing_agents as $clearing_agent)
                                                                    <option value="{{ $clearing_agent->id }}">{{ $clearing_agent->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('clearing_agent_id.0') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        <small>
                                                            <a href="{{ route('clearing_agents.index') }}" target="_blank">
                                                                <i class="fa fa-plus-square-o"></i> New Clearing Agent
                                                            </a>
                                                        </small>
                                                        <a href="#" wire:click.prevent="refresh('clearing_agents')" class="float-end">
                                                            <i class="fa fa-refresh"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <!-- Dynamic Borders -->
                                            @foreach ($border_inputs as $key => $value)
                                                <div class="row mt-10">
                                                    <div class="col-md-6">
                                                        <select class="form-control" wire:model.debounce.300ms="selectedBorder.{{ $value }}">
                                                            <option value="">Select Border</option>
                                                            @if (!is_null($selectedTripType))
                                                                @foreach ($borders as $border)
                                                                    <option value="{{ $border->id }}">{{ $border->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('selectedBorder.' . $value) <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-control" wire:model.debounce.300ms="clearing_agent_id.{{ $value }}">
                                                            <option value="">Select Agent</option>
                                                            @if (!is_null($selectedBorder))
                                                                @foreach ($clearing_agents as $clearing_agent)
                                                                    <option value="{{ $clearing_agent->id }}">{{ $clearing_agent->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('clearing_agent_id.' . $value) <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="col-md-1" style="margin-left:-20px; margin-top:3px;">
                                                        <button class="btn btn-danger btn-rounded btn-sm" wire:click.prevent="borderRemove({{ $key }})">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                
                                            <!-- Add Border Button -->
                                            <div class="form-group text-end mt-10">
                                                <button class="btn btn-success btn-rounded btn-sm" wire:click.prevent="borderAdd({{ $b }})">
                                                    <i class="fa fa-plus"></i> Border
                                                </button>
                                            </div>
                                        </div>
                                
                                        <!-- CD Numbers -->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="cd3_number">CD3 Number</label>
                                                        <input type="text" class="form-control" wire:model.debounce.300ms="cd3_number" placeholder="Enter CD3 Number">
                                                        @error('cd3_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="boe">Bill Of Entry #</label>
                                                        <input type="text" class="form-control" wire:model.debounce.300ms="bill_of_entry" placeholder="Enter BOE #">
                                                        @error('bill_of_entry') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    @if ($trip_type_name == 'Outward')
                                                        <div class="form-group">
                                                            <label for="cd1_number">CD1 Number</label>
                                                            <input type="text" class="form-control" wire:model.debounce.300ms="cd1_number" placeholder="Enter CD1 Number">
                                                            @error('cd1_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        </div>
                                                    @else
                                                        <div class="form-group">
                                                            <label for="manifest_number">Manifest Number</label>
                                                            <input type="text" class="form-control" wire:model.debounce.300ms="manifest_number" placeholder="Enter Manifest Number">
                                                            @error('manifest_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer">Trip Status<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedStatus" required>
                                              <option value="">Select Status</option>
                                                  <option value="Scheduled">Scheduled</option>
                                                  <option value="Started">Started</option>
                                                  <option value="Loading Point">Loading Point</option>
                                                  <option value="Loaded">Loaded</option>
                                                  <option value="InTransit">InTransit</option>
                                                  <option value="Offloading Point">Offloading Point</option>
                                                  <option value="Offloaded">Offloaded</option>
                                                  <option value="OnHold">OnHold</option>
                                                  <option value="Cancelled">Cancelled</option>
                                          </select>
                                            @error('selectedStatus') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                               
                                </div>
                              
                                <div class="row">
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
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
                                <div class="mb-10">
                                   <input type="checkbox" wire:model.debounce.300ms="with_cargos"   class="line-style" />
                                   <label for="one" class="radio-label">With Cargo</label>
                                   @error('with_cargos') <span class="text-danger error">{{ $message }}</span>@enderror
                               </div>
                               @if ($with_cargos == True)
                                <div class="row">
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="additional">Additional Cargo Details</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="cargo_details" placeholder="Additional Remarks">
                                            @error('cargo_details') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Gross Weight(t)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Gross Weight" >
                                            @error('weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Net Weight(t)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="net_weight" placeholder="Net Weight" >
                                            @error('net_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                @if (!is_null($selectedCargo))
                             
                                <div class="row">
                                    @if ($cargo_type == "Solid")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Cargo Quantity" >
                                            @error('quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement" >
                                              <option value="">Select Measurement</option>
                                                  @foreach ($solid_measurements as $measurement)
                                                      <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                  @endforeach
                                          </select>
                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> <a href="#" wire:click.prevent="refresh('measurements')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    @elseif ($cargo_type == "Liquid")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="litreage">Litreage @ Ambient</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Litreage @ Ambient Temperature" >
                                            @error('litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="litreage">Litreage @ 20 Degrees</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage_at_20" placeholder="Enter Litreage @ 20 Degrees">
                                            @error('litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement" >
                                              <option value="">Select Measurement</option>
                                                @foreach ($liquid_measurements as $measurement)
                                                    <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                @endforeach
                                          </select>
                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> <a href="#" wire:click.prevent="refresh('measurements')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                               
                                @endif
                                @if ($cargo_type == "Solid")
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Volume(m<sup>3</sup>)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="volume" placeholder="Cargo Volume" >
                                            @error('volume') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Temparature(<span>&deg;C</span>)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="temparature" placeholder="Cargo Temparature" >
                                            @error('temparature') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Container Number(s)</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="container_number" placeholder="Seperate Container#s by ," >
                                            @error('container_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="weight">Seal Number(s)</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="seal_number" placeholder="Seperate Seal#s by ," >
                                            @error('seal_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                               </div>
                               @endif
                            @endif

                                                @if (
                                    $company->rates_managed_by_finance == 0 || 
                                    ($company->rates_managed_by_finance == 1 && 
                                    (in_array('Finance', $department_names) || in_array('Super Admin', $role_names)))
                                )
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
                                            @if (isset($freight_calculation) && isset($cargo_type) )
                                                @if ($freight_calculation == "rate_weight" || $freight_calculation == "rate_weight_distance")
                                                    <div class="mb-10">
                                                        <caption style="color: green">Select what to use to calculate freight<span class="required" style="color: red">*</span>.</caption> <br>
                                                        @if ($cargo_type == "Solid")
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="weight"  class="line-style" required />
                                                        <label for="one" class="radio-label">Weight</label>
                                                        @endif
                                                        @if ($cargo_type == "Liquid")
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="litreage_at_ambient"  class="line-style" required />
                                                        <label for="one" class="radio-label">Litreage @ Ambient Temp</label>
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="litreage_at_20"  class="line-style" required />
                                                        <label for="one" class="radio-label">Litreage @ 20 Degrees</label>  
                                                        @endif
                                                        @error('calculation_measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                @endif
                                            @endif
                                           
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
                                   <label for="one" class="radio-label">Add multiple offloading points</label>
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
                                    <div class="row">
                                        <div class="col-md-6">
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
                                        <div class="col-md-6">
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
                                                        <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                                        <select class="form-control" wire:model.debounce.300ms="measurement" >
                                                            <option value="">Select Measurement</option>
                                                                @foreach ($solid_measurements as $measurement)
                                                                    <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
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
                                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                                            <select class="form-control" wire:model.debounce.300ms="measurement" >
                                                                <option value="">Select Measurement</option>
                                                                    @foreach ($solid_measurements as $measurement)
                                                                        <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                                    @endforeach
                                                            </select>
                                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="route"><a href="{{ route('routes.index') }}" target="_blank" style="color: blue">Route(s)</a></label>
                                            <select class="form-control" wire:model.debounce.300ms="selectedRoute" >
                                                <option value="">Select Route</option>
                                                @foreach ($routes as $route)
                                                    <option value="{{$route->id}}">{{ucfirst($route->name)}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedRoute') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('routes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Route</a></small> <a href="#" wire:click.prevent="refresh('routes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="row">  
                                            <div class="form-group">
                                                <label for="truck_stops"><a href="{{ route('truck_stops.index') }}" target="_blank" style="color: blue">Truck Stop(s)</a></label>
                                                <div class="col-md-12">
                                                    <select wire:model.debounce.300ms="truck_stop_id.0" class="form-control">
                                                        <option value="">Select Truck Stop</option>
                                                        @if (!is_null($selectedRoute))
                                                        @foreach ($truck_stops as $truck_stop)
                                                            <option value="{{ $truck_stop->id }}"
                                                                  @if(in_array($truck_stop->id, $truck_stop_id ?? []) && ($truck_stop_id[0] ?? null) != $truck_stop->id) 
                                                                    disabled 
                                                                @endif
                                                                >{{ $truck_stop->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                    @error('truck_stop_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('truck_stops.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Truck Stop</a></small> <a href="#" wire:click.prevent="refresh('truck_stops')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach ($inputs as $key => $value)
                                            
                                                <div class="col-md-9">
                                                    <select wire:model.debounce.300ms="truck_stop_id.{{ $value }}" class="form-control">
                                                        <option value="">Select Truck Stop </option>
                                                       @if (!is_null($selectedRoute))
                                                            @foreach ($truck_stops as $truck_stop)
                                                                 <option value="{{ $truck_stop->id }}"
                                                                      @if(in_array($truck_stop->id, $truck_stop_id ?? []) && ($truck_stop_id[$value] ?? null) != $truck_stop->id) 
                                                                    disabled 
                                                                @endif
                                                                    >{{ $truck_stop->name }}</option>
                                                            @endforeach
                                                       @endif
                                                       
                                                    </select>
                                                    @error('truck_stop_id.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="marging-left:-25px"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                       
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Truck Stop</button>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        </div>
                                  
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_date">Trip Start Date<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                            @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="end_date">Estimated Trip End Date<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter End Date" required>
                                            @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                    
                                </div>

                                            
                            <div class="row">
                                   
                                   

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="distance">Trip Distance</label>
                                            <input type="number" min="1" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance (Kms)"  >
                                            @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
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

</div>

