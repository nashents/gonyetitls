<div>
    <div class="row mt-30">
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ucfirst($transporter->name)}}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <img src="{{asset('images/'.$transporter->user->profile)}}" alt="{{$transporter->name}}" class="img-responsive">
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
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>{{$transporter->email}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Passcode</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$transporter->pin}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>No Of Drivers</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$transporter->drivers ? $transporter->drivers->count() : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>No Of Horses</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$transporter->horses ? $transporter->horses->count() : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>No Of Trailers</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$transporter->trailers ? $transporter->trailers->count() : ""}}</small>
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
                        <h5>Transporter Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">transporter</span>
                    <span class="label label-danger label-rounded label-bordered">tags</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Transporter Info</a></li>
                <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
                <li role="presentation"><a href="#cargos" aria-controls="cargos" role="tab" data-toggle="tab">Cargos</a></li>
                <li role="presentation"><a href="#drivers" aria-controls="drivers" role="tab" data-toggle="tab">Drivers</a></li>
                <li role="presentation"><a href="#horses" aria-controls="horses" role="tab" data-toggle="tab">Horses</a></li>
                <li role="presentation"><a href="#trailers" aria-controls="trailers" role="tab" data-toggle="tab">Trailers</a></li>
                <li role="presentation"><a href="#corridors" aria-controls="corridors" role="tab" data-toggle="tab">Corridors</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                <li role="presentation"><a href="#bills" aria-controls="bills" role="tab" data-toggle="tab">Bills</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Transporter#</th>
                                <td class="w-20 line-height-35">{{$transporter->transporter_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$transporter->name}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$transporter->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$transporter->phonenumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Country</th>
                                    <td class="w-20 line-height-35">{{$transporter->country}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">City</th>
                                    <td class="w-20 line-height-35">{{$transporter->city}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Suburb</th>
                                    <td class="w-20 line-height-35">{{$transporter->suburb}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Address</th>
                                    <td class="w-20 line-height-35">{{$transporter->street_address}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35">{{$transporter->phonenumber}}</td>
                                </tr>
                        
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="contacts">
                  @livewire('contacts.index', ['id' => $transporter->id,'category' =>'transporter'])
                </div>
                <div role="tabpanel" class="tab-pane" id="cargos">
                  @livewire('transporters.cargos', ['id' => $transporter->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="drivers">
                  @livewire('transporters.drivers', ['id' => $transporter->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="horses">
                  @livewire('transporters.horses', ['id' => $transporter->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="trailers">
                  @livewire('transporters.trailers', ['id' => $transporter->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="corridors">
                  @livewire('transporters.corridors', ['id' => $transporter->id])
                </div>
              
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $transporter->id,'category' =>'transporter'])
                </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Trip#
                            </th>
                            <th class="th-sm">Driver
                            </th>
                            <th class="th-sm">Customer
                            </th>
                            <th class="th-sm">From
                            </th>
                            <th class="th-sm">To
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Turnover
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
                            
                            <td><a href="{{ route('trips.show',$trip->id) }}" style="color: blue">{{$trip->trip_number}}</a></td>
                            <td>
                                @if ($trip->driver)
                                    {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}        
                                @endif
                            </td>
                            <td>{{$trip->customer ? $trip->customer->name : ""}}</td>
                            <td>
                                @if (isset($from))
                                    {{$from->country ? $from->country->name : ""}} {{$from->city}}
                                @endif
                             
                            </td>
                            <td>
                                @if (isset($to))
                                     {{$to->country ? $to->country->name : ""}} {{$to->city}}
                                @endif 
                            </td>
                            <td>{{$trip->currency ? $trip->currency->name : ""}}</td>
                            <td>
                                @if ($trip->turnover)
                                {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->turnover,2)}}        
                                @endif
                            </td>
                            @if ($trip->trip_status == "Offloaded")
                            <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Scheduled")
                            <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Loading Point")
                            <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Loaded")
                            <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "InTransit")
                            <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "OnHold")
                            <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Offloading Point")
                            <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                            @endif
                          </tr>
                          @endforeach
                        </tbody>
    
    
                      </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="bills">
                    <table id="billsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>

                            <th class="th-sm">Bill#
                            </th>
                            <th class="th-sm">Category
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Total
                            </th>
                            <th class="th-sm">Amount Due
                            </th>
                            <th class="th-sm">Payment Status
                            </th>
                          </tr>
                        </thead>
                        @if ($bills->count()>0)
                        <tbody>
                            @foreach ($bills as $bill)
                          <tr>
                            <td>{{$bill->bill_number}}</td>
                            <td>
                                @if ($bill->transporter)
                                    Transporter | <a href="{{ route('transporters.show',$bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill->transporter->name }}</a> 
                                @elseif($bill->vendor)
                                    Vendor | <a href="{{ route('vendors.show',$bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill->vendor->name }}</a> 
                                @elseif ( $bill->container || $bill->top_up)
                                    Fueling Station | <a href="{{ route('containers.show', $bill->container->id) }}" style="color: blue" target="_blank">{{ $bill->container->name }}</a> 
                                @elseif ($bill->trip && ($bill->horse || $bill->driver))
                                    Trip Expense | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                @endif
                            </td>
                            <td>{{$bill->bill_date}}</td>
                            <td>{{$bill->currency ? $bill->currency->name : ""}}</td> 
                            <td>
                                @if ($bill->total)
                                     {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                                @endif
                            </td>
                            <td>
                                {{-- @if ($bill->balance) --}}
                                     {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance,2)}}
                                {{-- @endif --}}
                            </td>
                            <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                          
                          </tr>
                          @endforeach
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
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="updateEmployeeLeaveDaysModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-calendar"></i> Update Available Leave Days Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">

                    <div class="form-group">
                        <label for="name">Available Leave Days</label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Enter Leave Days" required >
                        @error('leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    @section('extra-js')
    <script>
        $(document).ready( function () {
            $('#transporter_cargosTable').DataTable();
        } );
    </script>
     <script>
        $(document).ready( function () {
            $('#tripsTable').DataTable();
        } );
        </script>
     <script>
        $(document).ready( function () {
            $('#driversTable').DataTable();
        } );
        </script>
     <script>
        $(document).ready( function () {
            $('#trailersTable').DataTable();
        } );
        </script>
     <script>
        $(document).ready( function () {
            $('#horsesTable').DataTable();
        } );
        </script>
     <script>
        $(document).ready( function () {
            $('#vehiclesTable').DataTable();
        } );
        </script>
 @endsection
    

 
</div>
