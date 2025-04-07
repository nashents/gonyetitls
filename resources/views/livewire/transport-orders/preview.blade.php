<div>
    <div id="invoice">
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded" ><i class="fa fa-arrow-left"></i> Back</button>
                <a href="{{route('transport_orders.print',$transport_order->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('transport_orders.pdf', $transport_order->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color: red"></i> Export as PDF</a>
            </div>
            <hr>
        </div>
        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;">
                                <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                            </a>
                        </div>
                        <div class="col company-details">
                            <h4 class="name" >
                                <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                    {{$company->name}}
                                </a>
                            </h4>
                            <div>{{$company->street_address}} {{$company->suburb}} <br>
                                {{$company->city}}, {{$company->country}}</div>
                            <div>
                                {{$company->phonenumber}}
                                @if ($company->second_phonenumber)
                                | {{$company->second_phonenumber}}
                                @endif
                                @if ($company->third_phonenumber)
                                | {{$company->third_phonenumber}}
                                @endif
                            </div>
                          
                            
                            <div>{{$company->email}}</div>
                            @if ($company->second_email)
                            <div>{{$company->second_email}}</div>
                            @endif
                            @if ($company->third_email)
                            <div>{{$company->third_email}}</div>
                            <br>
                            @endif
                        </div>
                    </div>
                    <div style="padding-top: 25px; padding-bottom:15px">
                        <center><h2>TRANSPORTATION ORDER</h2>  </center>
                    </div>
                </header>
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to">
                            <div class="text-gray-light">TRANSPORTATION ORDER FOR</div>
                            <h5 class="to">{{$customer->name}}</h5>
                            <div class="address"> 
                                @if ($customer->street_address)
                                {{$customer->street_address}} 
                                @endif
                                @if ($customer->suburb)
                                {{$customer->suburb}}
                                @endif
                                {{$customer->city}} {{$customer->country}}
                            </div>
                            <div class="email"><a href="mailto:{{$customer->email}}">{{$customer->email}}</a> </div>
                        </div>
                        <div class="col invoice-details">
                            <div class="date"> <strong>Trip Number:</strong> {{$transport_order->trip ? $transport_order->trip->trip_number : ""}}{{ $transport_order->trip ? '/'.$transport_order->trip->trip_ref : ""  }}</div>
                            <div class="date"><strong>Issue Date:</strong>
                                @php
                                $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
                            @endphp
                            @if ((preg_match($pattern, $transport_order->created_at)))
                            {{Carbon\Carbon::parse($transport_order->created_at)->format('d M Y h:i A')}}
                            @else
                            {{ $transport_order->created_at }}
                            @endif
                               </div>
                            <div class="date"><strong>Loading Point:</strong> {{$transport_order->collection_point}}</div>
                        </div>
                    </div>
                    <table class="table table-striped">

                        <tbody>
                            @if ($transport_order->trip->start_date)
                            <tr>
                                <th class="text-center"><strong>Date & Time of Dispatch</strong></th>
                                <td class="text-center">
                                    @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    @endphp
                                    @if ((preg_match($pattern, $transport_order->trip->start_date)))
                                    {{Carbon\Carbon::parse($transport_order->trip->start_date)->format('d M Y h:i A')}}
                                    @else
                                    {{ $transport_order->trip->start_date }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-center"><strong>Transporter</strong></th>
                                <td class="text-center">  @if($transport_order->transporter_id)
                                    {{ $transport_order->transporter ? $transport_order->transporter->name : ""}}
                                    @else
                                    
                                    @endif</td>
                            </tr>
                            <tr>
                                <th class="text-center"> <strong>Driver</strong></th>
                                <td class="text-center">
                                    @if ($transport_order->driver)
                                    {{$transport_order->driver->employee ? $transport_order->driver->employee->name : ""}} {{$transport_order->driver->employee ? $transport_order->driver->employee->surname : ""}} {{$transport_order->driver->employee ? $transport_order->driver->employee->idnumber : ""}}
                                    @endif
                                   </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Horse</strong></th>
                                <td class="text-center">
                                    @if ($transport_order->horse)
                                         {{$transport_order->horse->horse_make ? $transport_order->horse->horse_make->name : "" }}  {{$transport_order->horse->horse_model ? $transport_order->horse->horse_model->name : "" }} {{$transport_order->horse ? $transport_order->horse->registration_number : "" }} {{$transport_order->horse->horse_model ? $transport_order->horse->horse_model->name : "" }} {{$transport_order->horse ? "| ".$transport_order->horse->fleet_number : "" }}
                                    @endif
                                </td>
                            </tr>
                            @if ($transport_order->trip->trailers->count()>0)
                                    <tr>
                                        <th class="text-center"> <strong>Trailer(s)</strong></th>
                                        <td class="text-center">
                                            @foreach ($transport_order->trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} {{$trailer->registration_number}} {{"| ".$trailer->fleet_number}} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif

                            @if ($transport_order->trip->driver_allowances->count()>0)
                                @foreach ($transport_order->trip->driver_allowances as $allowance)
                                <tr>
                                    <th class="text-center"><strong>{{ $allowance->allowance ? $allowance->allowance->name : "" }}</strong></th>
                                    <td class="text-center"> 
                                        {{ $allowance->currency ? $allowance->currency->name : ""}} {{ $allowance->currency ? $allowance->currency->symbol : ""}}{{ number_format($allowance->amount)}}
                                    </td>
                                </tr>
                                @endforeach
                            @endif

                            <tr>
                                <th class="text-center"><strong>Customer</strong></th>
                                <td class="text-center"> {{$transport_order->trip->customer ? $transport_order->trip->customer->name : ""}}</td>
                            </tr>
                            @php
                                $origin = App\Models\Destination::find($transport_order->trip->from);
                                $destination = App\Models\Destination::find($transport_order->trip->to);
                            @endphp
                            <tr>
                                <th class="text-center"><strong>From</strong></th>
                                <td class="text-center">
                                    @if (isset($origin))
                                    {{$origin->country ? $origin->country->name : ""}} {{ $origin->city }}        
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>To</strong></th>
                                <td class="text-center"> 
                                    @if ($destination)
                                        {{$destination->country ? $destination->country->name : ""}} {{ $destination->city }}
                                    @endif
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="text-center"><strong>Loading Point</strong></th>
                                <td class="text-center"> {{$transport_order->collection_point}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Offloading Point</strong></th>
                                <td class="text-center"> {{$transport_order->delivery_point}}</td>
                            </tr>
                            @if ($transport_order->trip->distance)
                            <tr>
                                <th class="text-center"><strong>Distance</strong></th>
                                <td class="text-center">{{$transport_order->trip->distance."Kms"}}</td>
                            </tr>
                            @endif
                            @if ($transport_order->trip->route)
                                <tr>
                                    <th class="text-center"><strong>Route</strong></th>
                                    <td class="text-center"> {{$transport_order->trip->route ? $transport_order->trip->route->name : ""}}</td>
                                </tr>
                            @endif
                            @if ($transport_order->trip->truck_stops->count()>0)
                                <tr>
                                    <th class="text-center"><strong>Designated Truck Stops</strong></th>
                                    <td class="text-center"> 
                                        @foreach ($transport_order->trip->truck_stops as $truck_stop)
                                            {{ $truck_stop->name }} {{ $truck_stop->rating }}
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            @if ($transport_order->trip->borders->count()>0)
                                <tr>
                                    <th class="text-center"><strong>Border(s)</strong></th>
                                    <td class="text-center">
                                        @foreach ($transport_order->trip->borders as $border)
                                        {{$border->name}} <br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            @if ($transport_order->trip->clearing_agents->count()>0)
                                <tr>
                                    <th class="text-center"><strong>Clearing Agent</strong></th>
                                    <td class="text-center"> 
                                        @foreach ($transport_order->trip->clearing_agents as $clearing_agent)
                                            {{$clearing_agent->name}} -> Email: {{$clearing_agent->email}} Phonenumber: {{$clearing_agent->phonenumber}} <br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        
                            <tr>
                                <th class="text-center"><strong>Cargo</strong></th>
                                <td class="text-center"> {{$transport_order->cargo}}</td>
                            </tr>
                           
                            @if ($transport_order->weight)
                            <tr>
                                <th class="text-center"><strong>Weight</strong></th>
                                <td class="text-center"> {{$transport_order->weight."Tons"}} </td>
                            </tr> 
                            @endif
                        
                            @if ($transport_order->quantity)
                            <tr>
                                <th class="text-center"><strong>Quantity</strong></th>
                                <td class="text-center"> {{$transport_order->quantity}} {{$transport_order->measurement}}</td>
                            </tr>
                            @endif
                            @if ($transport_order->litreage)
                            <tr>
                                <th class="text-center"><strong>Litreage @ Ambient</strong></th>
                                <td class="text-center"> {{$transport_order->litreage}} {{$transport_order->measurement}}</td>
                            </tr>
                            @endif
                            @if ($transport_order->trip->litreage_at_20)
                            <tr>
                                <th class="text-center"><strong>Litreage @ 20 Degrees</strong></th>
                                <td class="text-center"> {{$transport_order->trip->litreage_at_20}} {{$transport_order->measurement}}</td>
                            </tr>
                            @endif
                            @if ($transport_order->trip->fuels->where('authorization','approved')->count()>0)
                            <tr>
                                <th class="text-center"><strong>Fuel Orders</strong></th>
                                <td class="text-center"> 
                                    @foreach ($transport_order->trip->fuels as $fuel)
                                    {{$fuel->order_number}} {{$fuel->fillup == 1 ? "Initial" : "Topup"}} {{$fuel->container ? $fuel->container->name : ""}} {{$fuel->type}} {{ $fuel->quantity."Litres" }} <br>
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                        @if ($transport_order->trip->start_date && $transport_order->trip->end_date )
                        <tr>
                            <th class="text-center"><strong>Target day(s) for the trip</strong></th>
                            <td class="text-center">
                                
                                @php
                                    $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                @endphp
                                @if ((preg_match($pattern, $transport_order->trip->start_date)) && (preg_match($pattern, $transport_order->trip->end_date)) )
                                {{ \Carbon\Carbon::parse($transport_order->trip->start_date)->diffInDays($transport_order->trip->end_date) }} Day(s)
                                @else
                                From: {{ $transport_order->trip->start_date }} - To {{ $transport_order->trip->end_date }}
                                @endif
                            
                            </td>
                        </tr> 
                        @endif
                            
                            <tr>
                                <th class="text-center"><strong>Checked By</strong></th>
                                <td class="text-center"> {{$transport_order->checked_by}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Authorized By</strong></th>
                                <td class="text-center"> {{$transport_order->authorized_by}}</td>
                            </tr>

                        </tbody>

                    </table>
                </main>
              <center><footer>{{ucfirst($company->name)}} Transportation Order</footer></center>  
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
