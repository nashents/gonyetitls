<div>
    <div>
        <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;">
            <thead >
                <th class="th-sm">Trip#
                </th>
                <th class="th-sm">Customer
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">From
                </th>
                <th class="th-sm">To
                </th>
                <th class="th-sm">Status
                </th>
                <th class="th-sm">PODS
                </th>
                <th class="th-sm">Actions
                </th>
    
              </tr>
            </thead>
            @if (isset($trips))
            <tbody>
                @foreach ($trips as $trip)
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
                </td>
               
   
                <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
               
               
                
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
               
                    
                 @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                <td> 
                    <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                    @if (isset($pod))
                    <center>
                        POD#: {{ $pod->document_number }}
                    </center>
                       
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
                </td>
               
                
                 <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
               
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
               
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
               
           
                
                @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                    <td> 
                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                        @if (isset($pod))
                        <center>
                            POD#: {{ $pod->document_number }}
                        </center>
                           
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
                           @if (Auth::user()->employee)
                           <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                           {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
                           <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                           <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
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
                </td>
           
     
             <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
            <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
           
            <td>
                @if (isset($from))
                {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                @endif
                @if ($trip->loading_point)
                    @if (isset($from))
                    <hr> 
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
                    <hr>  
                    @endif
                    {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                @endif
            </td>
            @if ($trip->trip_status == "Offloaded")
            <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                <span class="label label-success label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "Scheduled")
            <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "Loading Point")
            <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "Loaded")
            <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                <span class="label label-info label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "InTransit")
            <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "OnHold")
            <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
            </td>
            @elseif($trip->trip_status == "Offloading Point")
            <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
            </td>
            @endif
            
                
           
            @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                   <td> 
                    <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                    @if (isset($pod))
                    <center>
                        POD#: {{ $pod->document_number }}
                    </center>
                       
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
                        
                        @if (Auth::user()->employee)
                        <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                        {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
                        <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
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
                </td>
               
         
                 <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
               
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
              
               
                @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                    <td> 
                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                        @if (isset($pod))
                        <center>
                            POD#: {{ $pod->document_number }}
                        </center>
                           
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
                            
                            @if (Auth::user()->employee)
                            <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                            {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
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
                </td>
               
          
                 <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
               
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
               
                    
                @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                    <td> 
                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                        @if (isset($pod))
                        <center>
                            POD#: {{ $pod->document_number }}
                        </center>
                           
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
                            @if (Auth::user()->employee)
                            <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                            {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
                            <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
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
                </td>
               
          
                 <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
              
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
               
                    @php
                        $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                    @endphp
                    <td> 
                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                        @if (isset($pod))
                        <center>
                            POD#:{{ $pod->document_number }}
                        </center>
                           
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
                           @if (Auth::user()->employee)
                           <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                           {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
                           <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                           <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
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
                </td>
               
     
                 <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
              
                <td>
                    @if (isset($from))
                    {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                    @endif
                    @if ($trip->loading_point)
                        @if (isset($from))
                        <hr> 
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
                        <hr>  
                        @endif
                        {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                    @endif
                </td>
                @if ($trip->trip_status == "Offloaded")
                <td class="table-success" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-success label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Scheduled")
                <td class="table-warning" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-warning label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loading Point")
                <td class="table-gray" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-gray label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Loaded")
                <td class="table-info" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-info label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "InTransit")
                <td class="table-primary" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-primary label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "OnHold")
                <td class="table-danger" style="padding-left: 5px; padding-right: 5px;">
                    <span class="label label-danger label-wide">{{$trip->trip_status}} </span>
                </td>
                @elseif($trip->trip_status == "Offloading Point")
                <td class="table-accent" style="padding-left: 5px; padding-right: 5px;" >
                    <span class="label label-accent label-wide" >{{$trip->trip_status}} </span>
                </td>
                @endif
              
                    
                @php
                    $pod = App\Models\TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
                @endphp
                    <td> 
                        <span class="label label-{{isset($pod) ? "success" : "warning"}}"> {{isset($pod) ? "submitted" : "pending"}}</span>
                        @if (isset($pod))
                        <center>
                            POD#: {{ $pod->document_number }}
                        </center>
                           
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
                           @if (Auth::user()->employee)
                           <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-file color-warning"></i> Trip Sheet</a></li>
                           {{-- <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-file-invoice-dollar color-primary"></i> Invoice</a></li> --}}
                                <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                           @endif
                        </ul>
                    </div>
                    @include('trips.delete')
    
            </td>
              </tr>
              @endif
             @endforeach
            </tbody>
            @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
            @endif
    
          </table>
    </div>
    
</div>
