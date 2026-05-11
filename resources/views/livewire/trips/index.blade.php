<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
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
                            @if (isset($trips))
                                <div class="row mt-30">
                                    <div class="col-md-4 col-md-offset-4">
                                        <div class="alert-info" role="alert" style="border-radius: 5px;">
                                            <center><strong>Total Trips: </strong> {{ $trips->total() }}</center>
                                            @if ($showFreight)
                                                    @foreach ($trips_currencies as $currency)
                                                        @php
                                                            $total_revenue = $totalsByCurrency[$currency->id] ?? 0;
                                                        @endphp

                                                        <center>
                                                            <strong>Total Revenue {{ $currency->name }}: </strong>
                                                            {{ $currency->symbol }}{{ number_format($total_revenue, 2) }}
                                                        </center>
                                                    @endforeach
                                            @endif
                                            @foreach ($expense_currencies as $currency)
                                                @php
                                                    $total_expenses = $expenseTotalsByCurrency[$currency->id] ?? 0;
                                                @endphp

                                                <center>
                                                    <strong>Total Expenses {{ $currency->name }}: </strong>
                                                    {{ $currency->symbol }}{{ number_format($total_expenses, 2) }}
                                                </center>
                                            @endforeach 
                                        </div>
                                        <!-- /.alert alert-info -->
                                    </div>
                                <!-- /.col-md-12 -->
                                </div>
                            @endif
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                <div class="panel-title">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Trip Created At</option>
                                                    <option value="offloaded_date">Trip Offloading Date</option>
                                                    <option value="start_date">Trip Start Date</option>
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
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                               <a href="#" wire:click.prevent="clearFilters()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-refresh"></i>CLEAR FILTERS</a>
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
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            Transporters
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter_transporter_id" class="form-control" aria-label="..." >
                                                                <option value="">Select Transporter</option>
                                                                @foreach ($transporters as $transporter)
                                                                    <option value="{{ $transporter->id }}"  >{{ ucfirst($transporter->name) }}</option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            Horses
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter_horse_id" class="form-control" aria-label="..." >
                                                                <option value="">Select Horse</option>
                                                                @foreach ($horses as $horse)
                                                                    <option value="{{ $horse->id }}"  > {{ $horse->registration_number }} {{ $horse->fleet_number ? "(".$horse->fleet_number.")" : "" }} </option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            Drivers
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter_driver_id" class="form-control" aria-label="..." >
                                                                <option value="">Select Driver</option>
                                                                @foreach ($drivers as $driver)
                                                                    <option value="{{ $driver->id }}"  >{{ ucfirst($driver->employee ? $driver->employee->name : " employee") }} {{ ucfirst($driver->employee ? $driver->employee->surname : "") }}</option>
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
                                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
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
                                                            Routes
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter_route_id" class="form-control  " aria-label="..." >
                                                            <option value="">Select Route</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
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
                                                            Trip Statuses
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter_trip_status" class="form-control  " aria-label="..." >
                                                                <option value="">Select Status</option>
                                                                <option value="Scheduled">Scheduled</option>
                                                                <option value="Loading Point">Loading Point</option>
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
                                                <a href="{{ route('trips.create') }}"  class="btn btn-default btn-wide" aria-haspopup="true" aria-expanded="true"><i class="fa fa-plus-square-o"></i>Trip</a>
                                                <a href="" data-toggle="modal" data-target="#tripsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                                <a href="#" wire:click.prevent="exportTripsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                                <a href="#" wire:click.prevent="exportTripsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                                <a href="#" wire:click.prevent="exportTripsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                            </div>
                                            <div class="col-md-6">
                                            <div class="dropdown">
                                                    <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                        <i class="fa fa-bars"></i> More Actions
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu bg-gray" aria-labelledby="menu12" style="float: right;" >
                                                        <li><a href="#"  wire:click="editLocations()"><i class="fa fa-refresh"></i>Bulk Status Update</a></li>
                                                        <li><a href="{{ route('trip_groups.index') }}"><i class="fa fa-map-marker"></i>Trip Tracking</a></li>
                                                        @if (isset($from) && isset($to))
                                                            <li><a href="{{ route('trips.summary.range',['from' => $from, 'to' => $to,'trip_filter'=>$trip_filter]) }}" ><i class="fa fa-download"></i>Trips Summary</a></li>
                                                        @elseif (isset($from) && isset($to) && isset($search))
                                                            <li><a href="{{ route('trips.summary.all',['from' => $from, 'to' => $to, 'search' => $search,'trip_filter'=> $trip_filter]) }}" ><i class="fa fa-download"></i>Trips Summary</a></li>
                                                        @elseif (isset($search))
                                                        <li><a href="{{ route('trips.summary.search',['search' => $search,'trip_filter'=>$trip_filter]) }}" ><i class="fa fa-download"></i>Trips Summary</a></li>
                                                        @else
                                                        <li><a href="{{ route('trips.summary',['trip_filter'=>$trip_filter]) }}" ><i class="fa fa-download"></i>Trips Summary</a></li>
                                                        @endif
                                                        <li><a href="#" wire:click="exportPodTrackerExcel()"><i class="fa fa-download"></i>POD Tracker</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    </div>
                                <br>
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search: trip#, date(yyyy-mm-dd), transporter,customer,VRN/HRN,CreatedBy,POD#...">
                                    </div>
                                </div>

                               
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>Trip#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Departure <hr style="margin-top:2px; margin-bottom:2px">Est/Offloaded</th>
                                                <th>Customer (Cargo)</th>
                                                <th>Transporter<hr style="margin-top:2px; margin-bottom:2px">Driver</th>
                                                <th>Horse/Vehicle<hr style="margin-top:2px; margin-bottom:2px">Trailer</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                @if($showFreight)
                                                    <th>Freight</th>
                                                @endif
                                                <th>
                                                    @if ($notDriver)
                                                         Invoice<hr style="margin-top:2px; margin-bottom:2px">
                                                    @endif
                                                    POD
                                                </th>
                                                <th>Auth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($trips as $trip)
                                            @php
                                                $s = $statusMap[$trip->trip_status] ?? ['row' => null, 'cell' => '', 'badge' => 'secondary'];
                                                // $offloadedDate = ($trip->trip_status === 'Offloaded' && !empty($trip->trip_status_date))
                                                //                     ? $trip->trip_status_date
                                                //                     : $trip->end_date;

                                            $formatDate = function (?string $date): string {
                                                if (!$date) return '';
                                                return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $date)
                                                    ? Carbon\Carbon::parse($date)->format('d M Y g:i A')
                                                    : $date;
                                            };

                                           
                                            $offloadedDate = $trip->trip_transport_orders
                                                ->map(fn($tto) => $formatDate($tto->delivery_note?->offloaded_date))
                                                ->filter()
                                                ->unique()
                                                ->join(', ');
                                                
                                                $pod = $trip->pod;
                                                $proofOfDelivery = App\Models\TripDocument::where('trip_id', $trip->id)->where('title', 'POD')->first();
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}" @endif>
                                                <td>
                                                    <strong>{{ $trip->trip_number }}@if($trip->trip_ref)/{{ $trip->trip_ref }}@endif</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        @if ($trip->trip_transport_orders)
                                                            @foreach ($trip->trip_transport_orders as $tto)
                                                                @if ($tto->tto_number)
                                                                    TTO#: {{$tto->tto_number}} @if (!$loop->last) , @endif
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                        <strong>CreatedBy:</strong>  {{ $trip->user?->name }} {{ $trip->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $trip->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $trip->trip_type?->name }}
                                                    @if($trip->haulage_type)
                                                        <small><strong>{{ $trip->haulage_type == "short_haul" ? "(Short Haul)" : "(Long Haul)"}}</strong></small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $formatDate($trip->start_date) }}
                                                    <hr class="my-1">
                                                    {{ $formatDate($offloadedDate) }}
                                                </td>
                                                <td>
                                                    @php
                                                        $items = collect();

                                                        if ($trip->trip_transport_orders && $trip->trip_transport_orders->count()) {

                                                            $items = $trip->trip_transport_orders
                                                                ->map(function ($trip_transport_order) {
                                                                    $to = $trip_transport_order->transport_order;

                                                                    $customer = ucfirst($to->customer?->name ?? '');
                                                                    $cargo    = ucfirst($to->cargo?->name ?? '');

                                                                    return [
                                                                        'label' => trim($customer . ' ' . $cargo),
                                                                        'key'   => md5(($to->customer_id ?? '') . '|' . ($to->cargo_id ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();

                                                        } else {

                                                            // fallback (old structure)
                                                            $customer = ucfirst($trip->customer?->name ?? '');
                                                            $cargo    = ucfirst($trip->cargo?->name ?? '');

                                                            $label = trim($customer . ' ' . $cargo);

                                                            if (!empty($label)) {
                                                                $items = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($trip->customer_id ?? '') . '|' . ($trip->cargo_id ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($items as $item)
                                                        {{ $item['label'] }}

                                                        @if(!$loop->last && $items->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>

                                                <td>
                                                    {{ ucfirst($trip->transporter?->name ?? '') }}
                                                    @if($trip->driver)
                                                        <hr class="my-1">
                                                        {{ $trip->driver?->employee?->name }} {{ $trip->driver?->employee?->surname }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($trip->horse)
                                                        Horse | {{ $trip->horse->registration_number }} {{ $trip->horse->fleet_number ? "({$trip->horse->fleet_number})" : "" }}
                                                    @elseif($trip->vehicle)
                                                        Vehicle | {{ $trip->vehicle->registration_number }} {{ $trip->vehicle->fleet_number ? "({$trip->vehicle->fleet_number})" : "" }}
                                                    @endif

                                                    @if($trip->trailers?->count())
                                                        <hr class="my-1">
                                                        @foreach($trip->trailers as $trailer)
                                                            {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "({$trailer->fleet_number})" : "" }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @endif
                                                </td>

                                               <td>
                                                    @php
                                                        $fromRoutes = collect();

                                                        if ($trip->transport_orders && $trip->transport_orders->count()) {
                                                            $fromRoutes = $trip->transport_orders
                                                                ->map(function ($transportOrder) {
                                                                    $from = $transportOrder->fromDestination
                                                                        ? trim(($transportOrder->fromDestination->country?->name ?? '') . ' ' . ($transportOrder->fromDestination->city ?? ''))
                                                                        : null;

                                                                    $loadingPoint = $transportOrder->loading_point?->name;

                                                                    return [
                                                                        'label' => trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -'),
                                                                        'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $from = $trip->fromDestination
                                                                ? trim(($trip->fromDestination->country?->name ?? '') . ' ' . ($trip->fromDestination->city ?? ''))
                                                                : null;

                                                            $loadingPoint = $trip->loading_point?->name;

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

                                                        if ($trip->transport_orders && $trip->transport_orders->count()) {
                                                            $toRoutes = $trip->transport_orders
                                                                ->flatMap(function ($transport_order) {
                                                                    return $transport_order->trip_destinations->map(function ($trip_destination) {
                                                                        $to = $trip_destination->destination
                                                                            ? trim(($trip_destination->destination->country?->name ?? '') . ' ' . ($trip_destination->destination->city ?? ''))
                                                                            : null;

                                                                        $offloadingPoint = $trip_destination->offloading_point?->name;

                                                                        return [
                                                                            'label' => trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -'),
                                                                            'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                                                                        ];
                                                                    });
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $to = $trip->toDestination
                                                                ? trim(($trip->toDestination->country?->name ?? '') . ' ' . ($trip->toDestination->city ?? ''))
                                                                : null;

                                                            $offloadingPoint = $trip->offloading_point?->name;

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
                                                        {{ $trip->trip_status }}
                                                        @if($trip->authorization === "approved")
                                                            <a href="#" wire:click.prevent="$emit('openTripStatusModal', {{ $trip->id }})" class="ms-1">
                                                                <i class="fa fa-edit" style="color:black"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $trip->currency?->name }} {{ $trip->currency?->symbol }}
                                                        {{ number_format(
                                                                (float) (is_numeric($trip->freight)
                                                                    ? $trip->freight
                                                                    : preg_replace('/[^\d\.\-]/', '', (string) ($trip->freight ?? 0))
                                                                ),
                                                                2
                                                            ) }}
                                                    </td>
                                                @endif

                                                <td>
                                                     @if ($notDriver)
                                                        @if($trip->invoices?->count())
                                                            <span class="label label-success">issued</span>
                                                            <small>
                                                                <strong>Invoice#(s):</strong>
                                                                @foreach($trip->invoices as $invoice)
                                                                    {{ $invoice->invoice_number }}@if(!$loop->last),@endif
                                                                @endforeach
                                                            </small>
                                                        @else
                                                            <span class="label label-warning">pending</span>
                                                        @endif
                                                        <hr class="my-1">
                                                    @endif
                                                
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
                                                        $authClass = $trip->authorization === 'approved' ? 'success' : ($trip->authorization === 'rejected' ? 'danger' : 'warning');
                                                        $authText  = $trip->authorization === 'approved' ? 'approved' : ($trip->authorization === 'rejected' ? 'rejected' : 'pending');
                                                    @endphp
                                                    <span class="label label-{{ $authClass }}">{{ $authText }}</span>

                                                    @if($trip->authorization_date)
                                                        <br><small><strong style="background-color: orange">AuthorizedOn: {{ $trip->authorization_date }}</strong></small>
                                                    @endif
                                                    @if($trip->authorized_by_id)
                                                        <br><small><strong style="background-color: orange">AuthorizedBy: {{ $this->getAuthorizer($trip->authorized_by_id) }}</strong></small>
                                                    @endif
                                                    @if($trip->reason)
                                                        <br><small><strong style="background-color: orange">Comments: {{ $trip->reason }}</strong></small>
                                                    @endif
                                                </td>

                                                <td class="w-10 line-height-35 table-dropdown">
                                                    @include('trips.partials.actions', ['trip' => $trip, 'user' => $user, 'employee' => $employee])
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No Trips Found ....
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                {{-- </div> --}}
                 
                                    {{ $trips->links() }}

                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>


        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="locationsEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog  mw-100 w-90" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i>Update Trip Status<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="updateTripStatus()" >
                    <div class="modal-body">
                        <h5 class="underline mt-30" style="color: red">Only authorized intransit trips appear on this list</h5>
                        <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                            <thead>
                              <tr>
                                <th class="th-sm">Trip
                                </th>
                                <th class="th-sm">Date
                                </th>
                                <th class="th-sm">Status
                                </th>
                                <th class="th-sm">Country
                                </th>
                                <th class="th-sm">Description
                                </th>
                              </tr>
                            </thead>
                          
                            <tbody>
                                @if (isset($intransit_trips))
                                @forelse ($intransit_trips as $trip)
                              <tr>
                                @php
                                    $from = App\Models\Destination::find($trip->from);
                                    $to = App\Models\Destination::find($trip->to);
                                @endphp
                                <td>
                                    {{ $trip->trip_number }}/{{ $trip->trip_ref }}<br>
                                    @if (isset($from))
                                    {{$from->country ? $from->country->name : ""}} {{$from->city ? $from->city : ""}} : {{$trip->loading_point ? $trip->loading_point->name : ""}} - 
                                    @endif
                                    @if (isset($to))
                                    {{$to->country ? $to->country->name : ""}} {{$to->city ? $to->city : ""}} : {{$trip->offloading_point ? $trip->offloading_point->name : ""}}
                                    @endif
                                      <br>
                                    @if ($trip->horse)
                                    Horse | {{ $trip->horse->horse_make ? $trip->horse->horse_make->name : "" }} {{ $trip->horse->horse_model ? $trip->horse->horse_model->name : "" }} {{ $trip->horse ? $trip->horse->registration_number : "" }}
                                    @elseif ($trip->vehicle)
                                    Vehicle | {{ $trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "" }} {{ $trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "" }} {{ $trip->vehicle ? $trip->vehicle->registration_number : "" }}
                                    @endif
                                    <br>
                                    @if ($trip->driver)
                                    Driver | {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }} <br>
                                    @endif
                                   
                                    Current Status :   
                                    @if ($trip->trip_status == "Offloaded")
                                    <span class="label label-success label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Scheduled")
                                    <span class="label label-warning label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Loading Point")
                                    <span class="label label-default label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Loaded")
                                    <span class="label label-info label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "InTransit")
                                    <span class="label label-primary label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Started")
                                    <span class="label label-primary label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "OnHold")
                                    <span class="label label-danger label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Cancelled")
                                    <span class="label label-light label-wide">{{$trip->trip_status}}</span>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <span class="label label-default label-wide">{{$trip->trip_status}}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date.{{$trip->id}}" wire:key="{{ $trip->id }}">
                                        @error('date.'.$trip->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select class="form-control" wire:model.debounce.300ms="status.{{$trip->id}}"  wire:key="{{ $trip->id}}" >
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
                                        @error('status.'.$trip->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </td>
                         
                                <td>
                                    <div class="form-group">
                                        <select class="form-control" wire:model.debounce.300ms="country_id.{{$trip->id}}"  wire:key="{{ $trip->id }}" >
                                            <option value="">Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category.'.$trip->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <textarea class="form-control" wire:model.debounce.300ms="description.{{$trip->id}}"  wire:key="{{ $trip->id }}" placeholder="Enter Location Description" cols="30" rows="5"></textarea>
                                        @error('description.'.$trip->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </td>     
                              </tr>
                              @empty
                              <tr>
                                <td colspan="5">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Active Trips Found ....
                                    </div>
                                   
                                </td>
                              </tr>
                             @endforelse
                           
                             
                              @endif
                            </tbody>
                            
                          </table>
                      
                     
               
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

     


<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Trips <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form wire:submit.prevent="importTrips"  enctype="multipart/form-data">
            <div class="modal-body">
                <small style="color: green">
                    1) Complete fields as is in the system eg Loading Point GMB Eastlea should be GMB Eastlea in the system, else the system will not recognize the location.
                    <br>
                    2) Check and remove duplications in the system in horse, trailers, drivers, locations, cargos etc, to avoid confusing the system.
                    <br>
                    3) Make sure all the trips in your excel import file are not already captured in the system to avoid trip duplications.
                    <br>
                    4) Make sure to all dates in your excel file esp the start_date.
                    <br>
                    5) Lets limit to 2500 Trips per upload.
                </small>
                <br>
                <div class="form-group">
                    <label for="name">Upload Trip(s) Excel File<span class="required" style="color: red">*</span></label>
                    <input type="file" class="form-control" wire:model.debounce.300ms="importFile" placeholder="Upload horse File" required>
                    @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button type="submit"  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div><!-- Modal -->

        @livewire('trips.trip-status-manager')

</div>
