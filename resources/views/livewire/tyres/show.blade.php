<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#order" aria-controls="basic" role="tab" data-toggle="tab"><strong>Tyre Details</strong> </a></li>
                <li role="presentation" ><a href="#assignments" aria-controls="assignments" role="tab" data-toggle="tab"><strong>Assignments</strong> </a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Tyre Number</th>
                                <td class="w-20 line-height-35">{{$tyre->tyre_number}} </td>
                            </tr>
                            @if ($tyre->tyre_assignment)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tyre Assignment</th>
                                    <td class="w-20 line-height-35">
                                        @if ($tyre->tyre_assignment->horse)
                                            Horse: {{$tyre->tyre_assignment->horse ? $tyre->tyre_assignment->horse->registration_number : ""}} {{$tyre->tyre_assignment->horse->fleet_number ? "(".$tyre->tyre_assignment->horse->fleet_number.")" : ""}}
                                        @elseif($tyre->tyre_assignment->trailer)        
                                            Trailer: {{$tyre->tyre_assignment->trailer ? $tyre->tyre_assignment->trailer->registration_number : ""}} {{$tyre->tyre_assignment->trailer->fleet_number ? "(".$tyre->tyre_assignment->trailer->fleet_number.")" : ""}}
                                        @elseif($tyre->tyre_assignment->vehicle)        
                                            Vehicle: {{$tyre->tyre_assignment->vehicle ? $tyre->tyre_assignment->vehicle->registration_number : ""}} {{$tyre->tyre_assignment->vehicle->fleet_number ? "(".$tyre->tyre_assignment->vehicle->fleet_number.")" : ""}}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$tyre->user ? $tyre->user->name : ""}} {{$tyre->user ? $tyre->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$tyre->purchase_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35"> {{$tyre->vendor ? $tyre->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product</th>
                                <td class="w-20 line-height-35">{{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$tyre->type}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Specifications</th>
                                <td class="w-20 line-height-35">{{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}</td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35"> {{$tyre->currency ? $tyre->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Subtotal (Excl)</th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre->subtotal)
                                        {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">
                                    {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->tax_amount ? $tyre->tax_amount : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Subtotal (Incl)</th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre->subtotal_incl)
                                        {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}
                                    @endif
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Date</th>
                                <td class="w-20 line-height-35">{{$tyre->purchase_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Type</th>
                                <td class="w-20 line-height-35">{{$tyre->purchase_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Warrant Expiry Date</th>
                                <td class="w-20 line-height-35">{{$tyre->warranty_exp_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Useful Life</th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre->life)
                                         {{$tyre->life}}Year(s)
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Residual Value</th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre->residual_value)
                                    {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->residual_value,2)}}        
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$tyre->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"> <span class="badge bg-{{$tyre->status == 1 ? "warning" : "success"}}">{{$tyre->status == 1 ? "Unassigned" : "Assigned"}}</span>        </td>
                            </tr>
                        </tbody>
                    </table>
                   
                </div>
                <div role="tabpanel" class="tab-pane" id="assignments">
                           <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Tyre#
                                    </th>
                                    <th class="th-sm">Tyre
                                    </th>
                                    <th class="th-sm">Specifications
                                    </th>
                                    <th class="th-sm">Assigned On
                                    </th>
                                    <th class="th-sm">Fitting Mileage
                                    </th>
                                    <th class="th-sm">Ending Mileage
                                    </th>
                                    <th class="th-sm">Tyre Life(Kms)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($tyre_assignments))
                                <tbody>
                                    @forelse ($tyre_assignments as $tyre_assignment)
                                  <tr>
                                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}}</td>
                                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->width : ""}}/ {{$tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : ""}} R {{$tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : ""}}</td>
                                    <td>
                                        @if ($tyre_assignment->horse)
                                        Horse | {{$tyre_assignment->horse->horse_make ? $tyre_assignment->horse->horse_make->name : ""}} {{$tyre_assignment->horse->horse_model ? $tyre_assignment->horse->horse_model->name : ""}} [ {{$tyre_assignment->horse ? $tyre_assignment->horse->registration_number : ""}} ]
                                        @elseif ($tyre_assignment->trailer)
                                        Trailer | {{$tyre_assignment->trailer ? $tyre_assignment->trailer->make : ""}} {{$tyre_assignment->trailer ? $tyre_assignment->trailer->model : ""}} [{{$tyre_assignment->trailer ? $tyre_assignment->trailer->registration_number : ""}}]
                                        @elseif ($tyre_assignment->vehicle)
                                        Vehicle | {{$tyre_assignment->vehicle->vehicle_make ? $tyre_assignment->vehicle->vehicle_make->name : ""}} {{$tyre_assignment->vehicle->vehicle_model ? $tyre_assignment->vehicle->vehicle_model->name : ""}} [{{$tyre_assignment->vehicle ? $tyre_assignment->vehicle->registration_number : ""}}]
                                        @endif
                                    </td>
                                    <td>{{$tyre_assignment->starting_odometer ? $tyre_assignment->starting_odometer."Kms" : ""}}</td>
                                    <td>{{$tyre_assignment->ending_odometer ? $tyre_assignment->ending_odometer."Kms" : ""}}</td>
                                    <td>
                                        @if ($tyre_assignment->tyre)
                                            {{$tyre_assignment->tyre->mileage ? $tyre_assignment->tyre->mileage."Kms" : ""}}
                                        @endif
                                    </td>
                                    <td>
                                         <span class="badge bg-{{$tyre_assignment->status == 1 ? "success" : "warning"}}">{{$tyre_assignment->status == 1 ? "Current" : "Past"}}</span>      
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Tyre Assignments Found ....
                                        </div>
                                       
                                    </td>
                                  </tr> 
                                  @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                   
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                        </div>
                    </div>
                    </div>

                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
