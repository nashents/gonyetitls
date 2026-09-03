<div>
    <div class="row mt-30">

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>Deal Details</strong></a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab"><strong>Trips</strong></a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Deal#</th>
                                <td class="w-20 line-height-35">{{$deal->deal_number}} {{$deal->reference ? "/".$deal->reference : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Customer</th>
                                <td class="w-20 line-height-35">{{$deal->customer ? $deal->customer->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo</th>
                                <td class="w-20 line-height-35">{{$deal->cargo ? $deal->cargo->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Weight</th>
                                <td class="w-20 line-height-35">{{$deal->weight ? $deal->weight."t" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Litreage</th>
                                <td class="w-20 line-height-35">{{$deal->litreage}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Quantity</th>
                                <td class="w-20 line-height-35">{{$deal->quantity}} {{$deal->units_of_measure ? $deal->units_of_measure->name : ""}} {{$deal->units_of_measure && $deal->units_of_measure->abbreviation ? "(".$deal->units_of_measure->abbreviation.")" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$deal->currency ? $deal->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Rate</th>
                                <td class="w-20 line-height-35">{{$deal->currency ? $deal->currency->symbol : ""}}{{$deal->rate ? number_format($deal->rate,2) : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Freight</th>
                                <td class="w-20 line-height-35">{{$deal->currency ? $deal->currency->symbol : ""}}{{$deal->freight ? number_format($deal->freight,2) : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Start Date</th>
                                <td class="w-20 line-height-35">{{$deal->start_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">End Date</th>
                                <td class="w-20 line-height-35">{{$deal->end_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Notes</th>
                                <td class="w-20 line-height-35">{{$deal->notes}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$deal->status == 1 ? "success" : "danger"}}">{{$deal->status == 1 ? "Active" : "Closed"}}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    <table id="tripsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="th-sm">Trip#</th>
                                <th class="th-sm">Weight</th>
                                <th class="th-sm">Litreage</th>
                                <th class="th-sm">Quantity</th>
                                <th class="th-sm">UOM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trips as $trip)
                                <tr>
                                    <td><a href="{{route('trips.show',$trip->id)}}" target="_blank" style="color:blue">{{$trip->trip_number}}</a></td>
                                    <td>{{$trip->weight}}</td>
                                    <td>{{$trip->litreage}}</td>
                                    <td>{{$trip->quantity}}</td>
                                    <td>{{$trip->units_of_measure ? $trip->units_of_measure->name : ""}} {{$trip->units_of_measure && $trip->units_of_measure->abbreviation ? "(".$trip->units_of_measure->abbreviation.")" : ""}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

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
