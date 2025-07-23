<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Shift Details</a></li>
                @if ($shift->fuel_order == True)
                <li role="presentation" ><a href="#fuel" aria-controls="fuel" role="tab" data-toggle="tab">Fuel Order</a></li>
                @endif
                @if ($shift->for === "Trips")
                    <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                @elseif($shift->for === "Rehandling")
                    <li role="presentation"><a href="#rehandling" aria-controls="rehandling" role="tab" data-toggle="tab">Rehandling Work</a></li>
                @endif 
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$shift->user ? $shift->user->name : ""}} {{$shift->user ? $shift->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$shift->type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">For</th>
                                <td class="w-20 line-height-35">{{$shift->for}}</td>
                            </tr>
                              <tr>
                                <th class="w-10 text-center line-height-35">Transporter</th>
                                <td class="w-20 line-height-35">{{$shift->transporter ? $shift->transporter->name : ""}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Driver</th>
                                <td class="w-20 line-height-35">
                                    @if ($shift->driver)
                                        {{$shift->driver->employee ? $shift->driver->employee->name : ""}} {{$shift->driver->employee ? $shift->driver->employee->surname : ""}}
                                    @endif
                                </td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Equipment</th>
                                <td class="w-20 line-height-35">
                                    @if ($shift->horse)
                                        {{ucfirst($shift->horse->horse_make ? $shift->horse->horse_make->name : "")}} {{ucfirst($shift->horse->horse_model ? $shift->horse->horse_model->name : "")}} ({{ucfirst($shift->horse ? $shift->horse->registration_number : "")}})     
                                    @elseif($shift->vehicle)
                                       {{ucfirst($shift->vehicle->vehicle_make ? $shift->vehicle->vehicle_make->name : "")}} {{ucfirst($shift->vehicle->vehicle_model ? $shift->vehicle->vehicle_model->name : "")}} ({{ucfirst($shift->vehicle ? $shift->vehicle->registration_number : "")}})     
                                    @endif
                                </td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Customer</th>
                                <td class="w-20 line-height-35">{{$shift->customer ? $shift->customer->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo</th>
                                <td class="w-20 line-height-35">{{$shift->cargo ? $shift->cargo->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Loading Points</th>
                                <td class="w-20 line-height-35">
                                    @if ($shift->loading_points && $shift->loading_points->count()>0)
                                        @foreach ($shift->loading_points as $loading_point)
                                            {{ $loading_point->name }}@if (!$loop->last), @endif
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Offloading Points</th>
                                <td class="w-20 line-height-35">
                                    @if ($shift->offloading_points && $shift->offloading_points->count()>0)
                                        @foreach ($shift->offloading_points as $offloading_point)
                                            {{ $offloading_point->name }}@if (!$loop->last), @endif
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$shift->date}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Shift Start Time</th>
                                <td class="w-20 line-height-35">{{$shift->shift_start_time}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Shift End Time</th>
                                <td class="w-20 line-height-35">{{$shift->shift_end_time}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Total Loads</th>
                                <td class="w-20 line-height-35">{{$shift->total_loads}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total Weight</th>
                                <td class="w-20 line-height-35">{{$shift->total_weight ? $shift->total_weight : "tons"}}</td>
                            </tr>
                           
                             <tr>
                                <th class="w-10 text-center line-height-35">Total Freight</th>
                                <td class="w-20 line-height-35">{{$shift->currency ? $shift->currency->name : ""}} {{$shift->currency ? $shift->currency->symbol : ""}}{{number_format($shift->total_freight,2)}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Shift Open Mileage</th>
                                <td class="w-20 line-height-35">{{$shift->open_mileage ? number_format($shift->open_mileage,2)." Kms" : ""}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Shift Closing Mileage</th>
                                <td class="w-20 line-height-35">{{$shift->close_mileage ? number_format($shift->close_mileage,2)." Kms" : ""}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Actual Mileage</th>
                                <td class="w-20 line-height-35">{{$shift->actual_mileage ? $shift->actual_mileage." Kms" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Calculated Mileage</th>
                                <td class="w-20 line-height-35">{{$shift->calculated_mileage ? number_format($shift->calculated_mileage,2)." Kms" : ""}}</td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Qty</th>
                                <td class="w-20 line-height-35">{{$shift->total_fuel ? $shift->total_fuel." Litres" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Consumption (Mileage)</th>
                                <td class="w-20 line-height-35">{{$shift->fuel_consumption_mileage ? number_format($shift->fuel_consumption_mileage,2)." L/Km" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$shift->status == 1 ? "warning" : "success"}}">{{$shift->status == 1 ? "Open" : "Closed"}}</span></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"> <span class="badge bg-{{($shift->authorization == 'approved') ? 'success' : (($shift->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($shift->authorization == 'approved') ? 'approved' : (($shift->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            @php
                                $authorizer = App\Models\User::find($shift->authorized_by_id);
                            @endphp
                            @if ($authorizer)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorized By</th>
                                    <td class="w-20 line-height-35"> {{$authorizer->name}} {{$authorizer->surname}}</td>
                                </tr>
                            @endif
                            
                            @if ($shift->reason)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization Comments</th>
                                    <td class="w-20 line-height-35">{{$shift->reason}}</td>
                                </tr>
                            @endif
                               
                    
                           
                           
                       
                                
                        </tbody>
                    </table>
                </div>
                @if ($shift->fuel_order)
                    <div role="tabpanel" class="tab-pane" id="fuel">
                         <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                        @if ($shift->fuel_order == True)
                            @php
                                $fuel = $shift->fuel;
                            @endphp
                        @if ($fuel)
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Order#</th>
                                <td class="w-20 line-height-35"> {{$fuel->order_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35"> Refueling Date</th>
                                <td class="w-20 line-height-35">
                                    @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                        @if ((preg_match($pattern, $fuel->date)) )
                                            {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                        @else
                                        {{$fuel->date}}
                                        @endif  
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Fueling Station</th>
                                <td class="w-20 line-height-35">{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Quantity</th>
                                <td class="w-20 line-height-35"> {{$fuel->quantity ? $fuel->quantity."L" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$fuel->currency ? $fuel->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Rate</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->unit_price)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->unit_price,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->amount)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            @if ($fuel->odometer)
                            <tr>
                                <th class="w-10 text-center line-height-35">Mileage</th>
                                <td class="w-20 line-height-35">{{$fuel->odometer ? $fuel->odometer."Kms" : ""}}</td>
                            </tr>
                            @endif
                            @if ($fuel->hours)
                            <tr>
                                <th class="w-10 text-center line-height-35">Engine Hours</th>
                                <td class="w-20 line-height-35">{{$fuel->hours ? $fuel->hours."Hours" : ""}}</td>
                            </tr>
                            @endif
                            @if ($fuel->comments)
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Order Comments</th>
                                <td class="w-20 line-height-35"> {{$fuel->comments? $fuel->comments : "No comment recorded"}}</td>
                            </tr>
                            @endif
                           
                           
                        @endif
                        @endif
                                
                        </tbody>
                    </table>
                    </div> 
                @endif
                @if ($shift->for === "Trips")
                    <div role="tabpanel" class="tab-pane" id="trips">
                    @livewire('shifts.trips', ['id' => $shift->id])
                    </div> 
                @elseif ($shift->for === "Rehandling")
                  <div role="tabpanel" class="tab-pane" id="rehandling">
                    @livewire('shifts.rehandlings', ['id' => $shift->id])
                    </div> 
                @endif
              
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
