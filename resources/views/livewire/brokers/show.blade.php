<div>
    <div class="row mt-30">
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ucfirst($broker->name)}}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <img src="{{asset('images/'.$broker->user->profile)}}" alt="{{$broker->name}}" class="img-responsive">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Account Credentials</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>{{$broker->email}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Passcode</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$broker->pin}}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.panel -->

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>broker Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">broker</span>
                    <span class="label label-danger label-rounded label-bordered">tags</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.col-md-3 -->
    <div class="col-md-9">
        <!-- /.row -->
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Broker Info</a></li>
            <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
            <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            <li role="presentation"><a href="#cargos" aria-controls="cargos" role="tab" data-toggle="tab">Cargos</a></li>
            <li role="presentation"><a href="#drivers" aria-controls="drivers" role="tab" data-toggle="tab">Drivers</a></li>
            <li role="presentation"><a href="#horses" aria-controls="horses" role="tab" data-toggle="tab">Horses</a></li>
            <li role="presentation"><a href="#trailers" aria-controls="trailers" role="tab" data-toggle="tab">Trailers</a></li>
            <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Broker#</th>
                            <td class="w-20 line-height-35"> {{$broker->broker_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35"> {{$broker->name}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Contact Name</th>
                            <td class="w-20 line-height-35">{{ucfirst($broker->contact_name)}} {{ucfirst($broker->contact_surname )}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Mobile Number</th>
                            <td class="w-20 line-height-35">{{$broker->phonenumber}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Landline</th>
                            <td class="w-20 line-height-35">{{$broker->worknumber}}</td>
                        </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Email</th>
                                <td class="w-20 line-height-35">{{$broker->email}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Country</th>
                                <td class="w-20 line-height-35">{{ucfirst($broker->country)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">City</th>
                                <td class="w-20 line-height-35">{{$broker->city}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Suburb</th>
                                <td class="w-20 line-height-35">{{$broker->suburb}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Street Address</th>
                                <td class="w-20 line-height-35">{{$broker->street_address}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$broker->status == 1 ? "success" : "danger"}}">{{$broker->status == 1 ? "Active" : "Inactive"}}</span></td>
                            </tr>
                    </tbody>
                </table>
              
            </div>

            <div role="tabpanel" class="tab-pane" id="contacts">
                @livewire('contacts.index', ['id' => $broker->id,'category' =>'broker'])
              </div>
            <div role="tabpanel" class="tab-pane" id="documents">
                @livewire('documents.index', ['id' => $broker->id,'category' =>'broker'])
              </div>
              <div role="tabpanel" class="tab-pane" id="drivers">
                @livewire('brokers.drivers', ['id' => $broker->id])
              </div>
              <div role="tabpanel" class="tab-pane" id="cargos">
                @livewire('brokers.cargos', ['id' => $broker->id])
              </div>
              <div role="tabpanel" class="tab-pane" id="horses">
                @livewire('brokers.horses', ['id' => $broker->id])
              </div>
              <div role="tabpanel" class="tab-pane" id="trailers">
                @livewire('brokers.trailers', ['id' => $broker->id])
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

            <!-- /.section-title -->
        </div>
    </div>
    </div>
</div>
