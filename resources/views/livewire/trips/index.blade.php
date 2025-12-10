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
                                    <div class="alert-info" role="alert">
                                        @php
                                        $employee_department = $employee->departments->first();
                 
                                        $departments = $employee->departments;
                                        foreach($departments as $department){
                                            $department_names[] = $department->name;
                                        }
                                        $roles = $user->roles;
                                        foreach($roles as $role){
                                            $role_names[] = $role->name;
                                        }
                                        $ranks = $employee->ranks;
                                        foreach($ranks as $rank){
                                            $rank_names[] = $rank->name;
                                        }
                                    @endphp
                                        <center><strong>Total Trips!</strong> {{ $trips->total() }}</center>
                                        @if ($company->rates_managed_by_finance == 1)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                @foreach ($currencies as $currency)
                                                    @php
                                                        $total_revenue = $totalsByCurrency[$currency->id] ?? 0;
                                                    @endphp

                                                    <center>
                                                        <strong>Total Revenue {{ $currency->name }} :</strong>
                                                        {{ $currency->symbol }}{{ number_format($total_revenue, 2) }}
                                                    </center>
                                                @endforeach
                                            @endif
                                        @else
                                           @foreach ($currencies as $currency)
                                                @php
                                                    $total_revenue = $totalsByCurrency[$currency->id] ?? 0;
                                                @endphp

                                                <center>
                                                    <strong>Total Revenue {{ $currency->name }} :</strong>
                                                    {{ $currency->symbol }}{{ number_format($total_revenue, 2) }}
                                                </center>
                                            @endforeach
                                        @endif
                                        
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

                                
                              
                                <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                    <thead >
                                        <th class="th-sm">Trip#
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Type
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">
                                            Departure 
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Offloaded
                                        </th>
                                        </th>
                                        <th class="th-sm">
                                            Customer
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Cargo
                                        </th>
                                        <th class="th-sm">
                                            Transporter
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Driver

                                        </th>
                                        <th class="th-sm">
                                            Horse/Vehicle
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Trailer
                                        </th>
                                        <th class="th-sm">From
                                        </th>
                                        <th class="th-sm">To
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <th>Freight</th>
                                                @endif
                                        @else 
                                            <th>Freight</th>
                                        @endif
                                        <th class="th-sm">
                                            Invoice
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            PODS
                                        </th>
                                     
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if (isset($trips))
                                    <tbody>
                                        @forelse ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                      
                                        @if ($trip->trip_status == "Offloaded")
                                      <tr style="background-color: #5cb85c">
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                            @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                            <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif
                                             
                                        </td>
                                       
                                        <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>
                                            {{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif
                                           
                                        </td>
                                       
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                        <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                    @endif
                                                    @if ($employee)
                                                    @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                   
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    @endif
                                                   

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Scheduled")
                                      <tr style="background-color: #f0ad4e" >
                                    
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                             $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                       <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                         @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                        
                                        <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                        <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                    @endif
                                                    @if ($employee)
                                                     @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                   <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                   <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                   @endif
                                               
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Cancelled")
                                      <tr style="background-color: #C4A484" >
                                    
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                            @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                             $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif  
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                        <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                    <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                @endif
                                                   @if ($employee)
                                                   @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-secondary"></i> Manifest</a></li>
                                                    @endif
                                                   <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                   <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                   @endif
                                               
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Loading Point")
                                      <tr  style="background-color: #adb5bd" >
                                      
                                      <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                    <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                         <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                    </td>
                                    <td>
                                        @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                        @if ((preg_match($pattern, $trip->start_date)) )
                                            {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                        @else
                                        {{$trip->start_date}}
                                        @endif    
                                        <hr style="margin-top:5px; margin-bottom:5px">
                                        @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                         $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                        @endphp
                                        @if ($offloaded_date)
                                                @if ((preg_match($pattern, $offloaded_date)) )
                                                {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                            @else
                                                {{$offloaded_date}}
                                            @endif  
                                        @endif  
                                    </td>
                                     <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                    <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                    <td>
                                        @if (isset($from))
                                        {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                        @endif
                                        @if ($trip->loading_point)
                                            @if (isset($from))
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            @endif
                                            {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                        @endif
                                       
                                    </td>
                                    <td>
                                        @if (isset($to))
                                        {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                        @endif
                                        @if ($trip->offloading_point)
                                            @if (isset($to))
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            @endif
                                            {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                        @endif
                                    </td>
                                    @if ($trip->trip_status == "Offloaded")
                                    <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Scheduled")
                                    <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                        <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Loading Point")
                                    <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                        <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Loaded")
                                    <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "InTransit")
                                    <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Started")
                                    <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Cancelled")
                                    <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                        <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                        <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                    </td>
                                    @endif
                                     @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                    <td> 
                                        @if ($trip->invoice_items->count()>0)
                                            <span class="label label-success"> issued</span>
                                        @else   
                                            <span class="label label-warning"> pending</span> 
                                        @endif
                                        <hr style="margin-top:5px; margin-bottom:5px">
                                        @php
                                            $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                        @endphp
                                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                        <center>
                                            @if (isset($pod->document_number))
                                                POD#: {{ $pod->document_number }}
                                            @endif
                                        </center>
                                    </td>
                                    <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                @if ($user->is_admin())
                                                    <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                @endif
                                                @if ($employee)
                                                  @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                @endif
                                              

                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Loaded")
                                      <tr  style="background-color: #5bc0de" >
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                              $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif  
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                         @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                    <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                @endif
                                                    @if ($employee)
                                                     @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    @endif
                                                  

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Started")
                                      <tr  style="background-color: #1976D2">
                                       
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                                {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                                  $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif 
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                        @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    @if ($user->is_admin())
                                                        <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                    @endif
                                                    @if ($employee)
                                                     @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "InTransit")
                                      <tr  style="background-color: #1976D2">
                                       
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                              $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif 
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                         <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                    <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                @endif
                                                    @if ($employee)
                                                      @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "OnHold")
                                      <tr  style="background-color: #d9534f">
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                             $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif 
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                      
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    @if ($user->is_admin())
                                                        <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                    @endif
                                                   @if ($employee)
                                                    @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                   <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                   <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                   @endif
                                                   

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Offloading Point")
                                      <tr  style="background-color: #82B1FF">
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                             @if ($trip->haulage_type)
                                                <small><strong>{{$trip->haulage_type}}</strong></small>
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user?->name}} {{ $trip->user?->surname}}
                                             <br>
                                            <small>CreatedOn: {{$trip->created_at}}</small>
                                        </td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)) )
                                                {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                            @else
                                            {{$trip->start_date}}
                                            @endif    
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                             $offloaded_date = $trip->delivery_note ? $trip->delivery_note->offloaded_date : null;
                                            @endphp
                                            @if ($offloaded_date)
                                                 @if ((preg_match($pattern, $offloaded_date)) )
                                                    {{ \Carbon\Carbon::parse($offloaded_date)->format('d M Y g:i A')}}
                                                @else
                                                    {{$offloaded_date}}
                                                @endif  
                                            @endif  
                                        </td>
                                         <td>
                                            {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                                            @if ($trip->cargo)
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                            {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                                            @endif 
                                        </td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                            @if ($trip->driver)
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                            @endif</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse |  {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle |   {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr style="margin-top:5px; margin-bottom:5px"> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-success label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-warning label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-gray label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-info label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-primary label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-danger label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light" style="padding-left: 5px; padding-right: 5px;">
                                            <span class="label label-light label-wide">{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                                            <span class="label label-accent label-wide" >{{$trip->trip_status}} @if($trip->authorization == "approved")
                                                                                                                    <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                                                                                                @endif</span>
                                        </td>
                                        @endif
                                        @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight: 0,2)}}</td>
                                        @endif
                                        <td> 
                                            @if ($trip->invoice_items->count()>0)
                                                <span class="label label-success"> issued</span>
                                            @else   
                                                <span class="label label-warning"> pending</span> 
                                            @endif
                                            <hr style="margin-top:5px; margin-bottom:5px">
                                            @php
                                                $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                                            @endphp
                                            <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "Submitted On: ".$pod->date : "pending"}}</span>
                                            <center>
                                                @if (isset($pod->document_number))
                                                    POD#: {{ $pod->document_number }}
                                                @endif
                                            </center>
                                        </td>
                                         <td>
                                            <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($trip->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($trip->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$trip->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                    
                                                    @if ($user->is_admin())
                                                        <li><a href="{{route('audits.index', ['id' =>$trip->id, 'category' => 'trip'])}}"><i class="fas fa-list color-default"></i> Audits</a></li>
                                                    @endif
                                                    @if ($employee)
                                                    @if ($trip->authorization == "approved")
                                                        <li><a href="{{route('transport_orders.preview',$trip->id)}}"   ><i class="fas fa-file color-warning"></i> Transport Order</a></li>
                                                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                                                        <li><a href="{{route('trips.manifest', $trip->id)}}"><i class="fas fa-file color-warning"></i> Manifest</a></li>
                                                    @endif
                                                        <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @endif
                                      @empty
                                      <tr>
                                        <td colspan="15">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Trips Found ....
                                            </div>
                                           
                                        </td>
                                      </tr>
                                      @endforelse
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif

                                  </table>
                                  
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
