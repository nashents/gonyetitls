<div>

    <div class="col-md-10 col-md-offset-1">
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Fuel Order Details</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Fuel Order#</th>
                            <td class="w-20 line-height-35"> {{$fuel->order_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Type</th>
                            <td class="w-20 line-height-35">{{ucfirst($fuel->type)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Created By</th>
                            <td class="w-20 line-height-35">{{ucfirst($fuel->user ? $fuel->user->name : "")}} {{ucfirst($fuel->user ? $fuel->user->surname : "")}}</td>
                        </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
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
                            @if ($fuel->asset)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Asset</th>
                                    <td class="w-20 line-height-35">{{ucfirst($fuel->asset ? $fuel->asset->product->brand->name : "")}} {{ucfirst($fuel->asset ? $fuel->asset->product->name : "")}}</td>
                                </tr>
                            @endif
                            @if ($fuel->trip)
                            <tr>
                                <th class="w-10 text-center line-height-35">Trip</th>
                                <td class="w-20 line-height-35">
                                    {{ucfirst($fuel->trip ? $fuel->trip->trip_number : "")}} <strong>From:</strong> {{ucfirst($fuel->trip->loading_point ? $fuel->trip->loading_point->name : "")}} <strong>To:</strong> {{ucfirst($fuel->trip->offloading_point ? $fuel->trip->offloading_point->name : "")}}
                                </td>
                            </tr>
                            @endif
                            @if ($fuel->horse)
                            <tr>
                                <th class="w-10 text-center line-height-35">Horse</th>
                                <td class="w-20 line-height-35"> 
                                    {{ucfirst($fuel->horse->horse_make ? $fuel->horse->horse_make->name : "")}} {{ucfirst($fuel->horse->horse_model ? $fuel->horse->horse_model->name : "")}} ({{ucfirst($fuel->horse ? $fuel->horse->registration_number : "")}})
                                </td>
                            </tr>
                            @endif
                            @if ($fuel->driver)
                            <tr>
                                <th class="w-10 text-center line-height-35">Driver</th>
                                <td class="w-20 line-height-35">{{ucfirst($fuel->driver ? $fuel->driver->employee->name : "")}} {{ucfirst($fuel->driver ? $fuel->driver->employee->surname : "")}}</td>
                            </tr>
                            @endif
                            @if ($fuel->vehicle)
                            <tr>
                                <th class="w-10 text-center line-height-35">Vehicle</th>
                                <td class="w-20 line-height-35"> 
                                    {{ucfirst($fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : "")}} {{ucfirst($fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : "")}} ({{ucfirst($fuel->vehicle ? $fuel->vehicle->registration_number : "")}})
                                </td>
                            </tr>
                            @endif
                            @if ($fuel->employee)
                            <tr>
                                <th class="w-10 text-center line-height-35">Employee</th>
                                <td class="w-20 line-height-35"> 
                                    {{ucfirst($fuel->employee ? $fuel->employee->name : "")}} {{ucfirst($fuel->employee ? $fuel->employee->surname : "")}} 
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Fueling Station</th>
                                <td class="w-20 line-height-35">{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Quantity</th>
                                <td class="w-20 line-height-35"> {{$fuel->quantity}}L</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$fuel->currency ? $fuel->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Buying Rate</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->unit_price)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->unit_price,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Buying Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->amount)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            @if ($fuel->transporter_price)
                            <tr>
                                <th class="w-10 text-center line-height-35">Retail Rate</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->transporter_price)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->transporter_price,2)}}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if ($fuel->transporter_total)
                            <tr>
                                <th class="w-10 text-center line-height-35">Retail Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->amount)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->transporter_total,2)}}
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if ($fuel->profit)
                            <tr>
                                <th class="w-10 text-center line-height-35">Profit</th>
                                <td class="w-20 line-height-35">
                                    @if ($fuel->profit)
                                        {{$fuel->currency ? $fuel->currency->symbol : ""}}{{number_format($fuel->profit,2)}}
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if ($fuel->odometer)
                            <tr>
                                <th class="w-10 text-center line-height-35">Mileage</th>
                                <td class="w-20 line-height-35">{{$fuel->odometer ? $fuel->odometer : ""}}KMs</td>
                            </tr>
                            @endif
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Fillup</th>
                                <td class="w-20 line-height-35"> {{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                            </tr>
                            @if ($fuel->comments)
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Order Comments</th>
                                <td class="w-20 line-height-35"> {{$fuel->comments? $fuel->comments : "No comment recorded"}}</td>
                            </tr>
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"> <span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            @if ($fuel->reason)
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization Comments</th>
                                <td class="w-20 line-height-35"> {{$fuel->reason? $fuel->reason : ""}}</td>
                            </tr>
                            @endif
                         

                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>
            </div>






            <!-- /.section-title -->
        </div>
    </div>
</div>
