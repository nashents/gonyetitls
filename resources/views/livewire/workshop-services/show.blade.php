<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Service Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35">
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$workshop_service->user ? $workshop_service->user->name : ""}} {{$workshop_service->user ? $workshop_service->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Expense Account</th>
                                <td class="w-20 line-height-35">{{$workshop_service->account ? $workshop_service->account->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35">{{$workshop_service->vendor ? $workshop_service->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Transporter</th>
                                <td class="w-20 line-height-35">{{$workshop_service->transporter ? $workshop_service->transporter->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Load Status</th>
                                <td class="w-20 line-height-35">{{$workshop_service->load_status}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Horse</th>
                                <td class="w-20 line-height-35">{{$workshop_service->horse->registration_number}} {{$workshop_service->horse->fleet_number ? "(".$workshop_service->horse->fleet_number.")" : ""}} {{$workshop_service->horse->make ? $workshop_service->horse->make->name :""}} {{$workshop_service->horse->model ? $workshop_service->horse->model->name :""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailer</th>
                                <td class="w-20 line-height-35">{{$workshop_service->trailer->registration_number}} {{$workshop_service->trailer->fleet_number ? "(".$workshop_service->trailer->fleet_number.")" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Day(s)</th>
                                <td class="w-20 line-height-35">{{$workshop_service->days}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">In</th>
                                <td class="w-20 line-height-35">{{$workshop_service->start_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Out</th>
                                <td class="w-20 line-height-35">{{$workshop_service->end_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$workshop_service->currency ? $workshop_service->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Amount</th>
                                <td class="w-20 line-height-35">{{$workshop_service->currency ? $workshop_service->currency->symbol : ""}}{{$workshop_service->amount ? number_format($workshop_service->amount,2) : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Balance</th>
                                <td class="w-20 line-height-35">{{$workshop_service->currency ? $workshop_service->currency->symbol : ""}}{{$workshop_service->balance ? number_format($workshop_service->balance,2) : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="label label-{{($workshop_service->status == 'Paid') ? 'success' : (($workshop_service->status == 'Partial') ? 'warning' : 'danger') }}">{{ $workshop_service->status }}</span></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">{{$workshop_service->description}}</td>
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
