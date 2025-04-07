<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#order" aria-controls="basic" role="tab" data-toggle="tab"><strong>Tyre Assignment Details</strong> </a></li>


            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Tyre Number</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->user ? $tyre_assignment->user->name : ""}} {{$tyre_assignment->user ? $tyre_assignment->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product</th>
                                <td class="w-20 line-height-35"> {{$tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : ""}} {{$tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->tyre ? $tyre_assignment->tyre->type : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Specification</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->tyre->width}} / {{$tyre_assignment->tyre->aspect_ratio}} R {{$tyre_assignment->tyre->diameter}}</td>
                            </tr>
                            @if (isset($tyre_assignment->horse))
                            <tr>
                                <th class="w-10 text-center line-height-35">Horse</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->horse->registration_number}} {{$tyre_assignment->horse->horse_make ? $tyre_assignment->horse->horse_make->name : ""}} {{$tyre_assignment->horse->horse_model ? $tyre_assignment->horse->horse_model->name : ""}}</td>
                            </tr>
                            @endif
                            @if (isset($tyre_assignment->vehicle))
                            <tr>
                                <th class="w-10 text-center line-height-35">Vehicle</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->vehicle->registration_number}} {{$tyre_assignment->vehicle->vehicle_make ? $tyre_assignment->vehicle->vehicle_make->name : ""}} {{$tyre_assignment->vehicle->vehicle_model ? $tyre_assignment->vehicle->vehicle_model->name : ""}}</td>
                            </tr>
                            @endif

                            @if (isset($tyre_assignment->trailer))
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailer</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->trailer ? $tyre_assignment->trailer->registration_number : ""}} {{$tyre_assignment->trailer ? $tyre_assignment->trailer->make : ""}}  {{$tyre_assignment->trailer ? $tyre_assignment->trailer->model : "model"}}</td>
                            </tr>
                            @endif

                            <tr>
                                <th class="w-10 text-center line-height-35">Tyre Life(Kms)</th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre_assignment->tyre)
                                    {{$tyre_assignment->tyre->mileage ? $tyre_assignment->tyre->mileage."Kms" : ""}}
                                    @endif
                                </td>
                            </tr>
                            
                            <tr>
                                <th class="w-10 text-center line-height-35">Starting Odometer</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->starting_odometer ? $tyre_assignment->starting_odometer."Kms" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Actual
                                    @if ($tyre_assignment->horse)
                                    Horse
                                    @elseif ($tyre_assignment->vehicle)
                                    Vehicle
                                    @elseif ($tyre_assignment->trailer)
                                    Trailer
                                    @endif
                                    Mileage
                                </th>
                                <td class="w-20 line-height-35">
                                    @if ($tyre_assignment->horse)
                                    {{$tyre_assignment->horse->mileage ? $tyre_assignment->horse->mileage."Kms" : ""}}
                                    @elseif ($tyre_assignment->vehicle)
                                    {{$tyre_assignment->vehicle->mileage ? $tyre_assignment->vehicle->mileage."Kms" : ""}}
                                    @elseif ($tyre_assignment->trailer)
                                    {{$tyre_assignment->trailer->mileage ? $tyre_assignment->trailer->mileage."Kms" : ""}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Ending Odometer</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->ending_odometer ? $tyre_assignment->ending_odometer."Kms" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Axle</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->axle}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Position</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->position}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$tyre_assignment->description}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$tyre_assignment->status == 1 ? "success" : "warning"}}">{{$tyre_assignment->status == 1 ? "Assigned" : "Unassigned"}}</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group pull-right mt-10" >
                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                            </div>
                        </div>
                        </div>
                </div>



                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
