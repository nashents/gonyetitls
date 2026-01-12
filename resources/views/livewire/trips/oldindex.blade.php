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
                                            
                                            @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                                <span class="label label-success"> issued</span>
                                                <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                            @endif
                                            @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                             @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                             @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                  <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                        @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                 <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                           @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                           @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                             @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                           
                                                <span class="label label-success"> issued</span>
                                                 <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                            @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                                <span class="label label-success"> issued</span>
                                                <strong>Invoice#(s):</strong>
                                                  <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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
                                            @if (isset($trip->invoices) && $trip->invoices->count() > 0)
                                                <span class="label label-success"> issued</span>
                                                <small>
                                                    <strong>Invoice#(s):</strong>
                                                    @foreach ($trip->invoices as $invoice)
                                                        {{$invoice->invoice_number}} @if (!$loop->last),@endif
                                                    @endforeach
                                                </small>
                                                
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
                                                 <small><strong style="background-color: orange">AuthorizedOn: {{$trip->authorization_date}}</strong></small>  
                                               
                                            @endif
                                             @if ($trip->authorized_by_id)
                                                <br>
                                                 <small><strong style="background-color: orange">AuthorizedBy: {{$this->getAuthorizer($trip->authorized_by_id)}}</strong></small>  
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