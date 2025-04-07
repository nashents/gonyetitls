<div>
    <div class="row mt-30">
        
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Consignee Details</a></li>
                <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$consignee->name}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$consignee->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$consignee->phonenumber}}</td>
                                </tr>
                               
                              
                         
                                <tr>
                                    <th class="w-10 text-center line-height-35">Country</th>
                                    <td class="w-20 line-height-35">{{$consignee->country}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">City</th>
                                    <td class="w-20 line-height-35">{{$consignee->city}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Suburb</th>
                                    <td class="w-20 line-height-35">{{$consignee->suburb}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Address</th>
                                    <td class="w-20 line-height-35">{{$consignee->street_address}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$consignee->status == 1 ? "success" : "danger"}}">{{$consignee->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                        
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="contacts">
                  @livewire('contacts.index', ['id' => $consignee->id,'category' =>'consignee'])
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $consignee->id,'category' =>'consignee'])
                </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Trip#
                            </th>
                            <th class="th-sm">Horse
                            </th>
                            <th class="th-sm">Driver
                            </th>
                            <th class="th-sm">From
                            </th>
                            <th class="th-sm">To
                            </th>
                            <th class="th-sm">Trip Value
                            </th>
                            <th class="th-sm">Status
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                            @if ($trips->count()>0)
                           
                            @foreach ($trips as $trip)
                            @php
                            $to = App\Models\Destination::find($trip->to);
                            $from = App\Models\Destination::find($trip->from);
                            @endphp
                          <tr>
                            <td>{{$trip->trip_number}}</td>
                            <td>
                                @if ($trip->horse)
                                    {{$trip->horse->horse_make ? $trip->horse->horse_make->name : ""}} {{$trip->horse->horse_model ? $trip->horse->horse_model->name : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}}
                                @endif
                            </td>
                            <td>
                                @if ($trip->driver)
                                    {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                @endif
                            </td>
                            <td>{{$from->country ? $from->country->name : ""}} {{$from->city}}</td>
                            <td>{{$to->country ? $to->country->name : ""}} {{$to->city}}</td>
                            <td>${{$trip->freight}}</td>
                            @if ($trip->trip_status == "Offloaded")
                            <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Scheduled")
                            <td><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Loaded")
                            <td><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "InTransit")
                            <td><span class="label label-secondary label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "OnHold")
                            <td><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Offloading Point")
                            <td><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                            @endif
                          </tr>
                          @endforeach
                          @else
                          <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                          @endif
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
