<div>
    {{-- <div class="panel-title">
        <a href="#" wire:click="exportTripsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
        <a href="#" wire:click="exportTripsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
        <a href="#" wire:click="exportTripsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
    </div> --}}
    <br>
        <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;">
            <thead >
                <th class="th-sm">Trip#
                </th>
                <th class="th-sm">Date
                </th>
               <th class="th-sm">
                    Transporter
                    <hr style="margin-top:2px; margin-bottom:2px">
                    Driver

                </th>
                <th class="th-sm">Horse
                </th>
                <th class="th-sm">
                    Customer
                    <hr style="margin-top:2px; margin-bottom:2px">
                    Cargo
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
              <tr >
              
                  <td>
                    {{ucfirst($trip->trip_number)}}
                    @if ($trip->trip_ref)
                    /{{$trip->trip_ref}}
                    @endif
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
                </td>
                <td>
                  {{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                     <hr>
                    @if ($trip->driver)
                         {{$trip->driver->employee ? $trip->driver->employee->name : ""}}  {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                    @endif
                </td>
                <td>
                    @if ($trip->horse)
                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                    @endif
                </td>
                <td>
                    {{ucfirst($trip->customer ? $trip->customer->name : "")}}
                    @if ($trip->cargo)
                    <hr>  
                    {{ucfirst($trip->cargo ? $trip->cargo->name : "")}}
                    @endif 
                </td>
               
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
                @if ($company->rates_managed_by_finance == True)
                                            @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight : 0,2)}}</td>
                                            @endif
                                        @else
                                            <td>{{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}} {{number_format($trip->freight ? $trip->freight : 0,2)}}</td>
                                        @endif
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
                  
            </td>
              </tr>
          
              @empty
                    <tr>
                    <td colspan="9">
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
    
</div>
