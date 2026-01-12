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
                            @if (isset($trips))
                            <br>
                            <div class="row">
                                <div class="col-md-4 col-md-offset-4">

                                    <div class="alert-info" role="alert" style="border-radius: 5px;">
                                   
                                        <center><strong>Total Trips: </strong> {{ $trips->total() }}</center>
                                        @if ($company->rates_managed_by_finance == 1)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
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
                                        @else
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
                                            <span class="input-group-addon">Filter By</span>
                                            <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Trip Created At</option>
                                                <option value="offloaded_date">Trip Offloading Date</option>
                                                <option value="start_date">Trip Start Date</option>
                                            </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-2" style="margin-right: 7px">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-2" style="margin-left: 7px">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    To
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                    <div class="row mt-7">
                                        <div class="col-md-12">
                                            <a href="{{ route('trips.create') }}"  class="btn btn-default btn-wide" aria-haspopup="true" aria-expanded="true"><i class="fa fa-plus-square-o"></i>Trip</a>
                                            <a href="" data-toggle="modal" data-target="#tripsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                            <a href="#" wire:click="exportTripsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                            <a href="#" wire:click="exportTripsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                            <a href="#" wire:click="exportTripsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                        </div>
                                    </div>
                                    <div class="row mt-10">
                                        <div class="col-md-12">
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
                               
                                </div>
                                <br>
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search: trip#, date(yyyy-mm-dd), transporter,customer,VRN/HRN,CreatedBy,POD#...">
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
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>Trip#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
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
                                        @forelse($trips as $trip)
                                            @php
                                                $s = $statusMap[$trip->trip_status] ?? ['row' => null, 'cell' => '', 'badge' => 'secondary'];
                                                $offloadedDate = $trip->delivery_note?->offloaded_date;
                                                $pod = $trip->pod;
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}" @endif>
                                                <td>
                                                    <strong>{{ $trip->trip_number }}@if($trip->trip_ref)/{{ $trip->trip_ref }}@endif</strong>
                                                    <br>
                                                    <small>
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
                                                    {{ ucfirst($trip->customer?->name ?? '') }}
                                                    @if($trip->cargo)
                                                        <hr class="my-1">
                                                        {{ ucfirst($trip->cargo?->name ?? '') }}
                                                    @endif
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
                                                    @if($trip->fromDestination)
                                                        {{ $trip->fromDestination->country?->name }} {{ $trip->fromDestination->city }}
                                                    @endif
                                                    @if($trip->loading_point)
                                                        <hr class="my-1">
                                                        {{ $trip->loading_point->name }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($trip->toDestination)
                                                        {{ $trip->toDestination->country?->name }} {{ $trip->toDestination->city }}
                                                    @endif
                                                    @if($trip->offloading_point)
                                                        <hr class="my-1">
                                                        {{ $trip->offloading_point->name }}
                                                    @endif
                                                </td>

                                                <td class="{{ $s['cell'] }}" style="padding: 5px;">
                                                    <span class="label label-{{ $s['badge'] }} label-wide">
                                                        {{ $trip->trip_status }}
                                                        @if($trip->authorization === "approved")
                                                            <a href="#" wire:click="status({{ $trip->id }})" class="ms-1">
                                                                <i class="fa fa-edit" style="color:black"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $trip->currency?->name }} {{ $trip->currency?->symbol }}
                                                        {{ number_format($trip->freight ?? 0, 2) }}
                                                    </td>
                                                @endif

                                                <td>
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

                                                    <span class="label label-{{ $pod ? 'success' : 'warning' }}">
                                                        {{ $pod ? "Submitted On: {$pod->date}" : "pending" }}
                                                    </span>

                                                    @if($pod?->document_number)
                                                        <div class="text-center">POD#: {{ $pod->document_number }}</div>
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
                                  
                                    <nav class="text-center" style="float: right">
                                        <ul class="pagination rounded-corners">
                                            @if (isset($trips))
                                                {{ $trips->links() }} 
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

     
<!-- Modal -->
@include('includes.trip_status')

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

    </div>
