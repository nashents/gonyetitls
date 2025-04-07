<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Allocation Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Allocation#</th>
                                <td class="w-20 line-height-35">{{$allocation->allocation_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$allocation->allocation_type}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Employee</th>
                                    <td class="w-20 line-height-35">{{$allocation->employee ? $allocation->employee->name : ""}} {{$allocation->employee ? $allocation->employee->surname : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Station</th>
                                    <td class="w-20 line-height-35">{{$allocation->container ? $allocation->container->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vehicle</th>
                                    <td class="w-20 line-height-35">{{$allocation->vehicle->vehicle_make ? $allocation->vehicle->vehicle_make->name : "" }} {{$allocation->vehicle->vehicle_model ? $allocation->vehicle->vehicle_model->name : "" }} {{$allocation->vehicle ? $allocation->vehicle->registration_number : "" }}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Fuel Type</th>
                                    <td class="w-20 line-height-35">{{$allocation->fuel_type}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Quantity</th>
                                    <td class="w-20 line-height-35">{{$allocation->quantity}}L</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Rate</th>
                                    <td class="w-20 line-height-35">{{$allocation->container->currency ? $allocation->container->currency->symbol : ""}}{{number_format($allocation->rate,2)}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">{{$allocation->container->currency ? $allocation->container->currency->symbol : ""}}{{number_format($allocation->amount,2)}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Balance</th>
                                    <td class="w-20 line-height-35">{{$allocation->balance}}L</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Validity</th>
                                    <td class="w-20 line-height-35">{{$allocation->expiry_date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="label label-{{$allocation->status == 1 ? "success" : "danger"}} label-rounded">{{$allocation->status == 1 ? "Active" : "Expired"}}</span></td>
                                </tr>
                             
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
