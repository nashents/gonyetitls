<div>
    <div class="row mt-30">
        @include('includes.messages')
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                      <center> <h5>{{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</h5></center> 
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            @php
                                $image = $vehicle->vehicle_images->first()
                            @endphp
                            @if ($image)
                            <img src="{{asset('images/uploads/'.$image->filename)}}" alt="Vehicle Avatar" class="img-responsive">
                            @else
                            <img src="{{asset('images/vehicle1.jpg')}}" alt="Vehicle Avatar" class="img-responsive">
                            @endif

                            {{-- <div class="text-center">
                                <button type="button" class="btn btn-primary btn-xs btn-labeled mt-10">Edit Picture<span class="btn-label btn-label-right"><i class="fa fa-pencil"></i></span></button>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                      <center><h5>{{ $vehicle->registration_number }} {{ $vehicle->fleet_number ? "| ".$vehicle->fleet_number : "" }}</h5></center>  
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Trips</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$vehicle_trips}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> <span class="badge bg-{{$vehicle->status == 1 ? "success" : "danger"}}">{{$vehicle->status == 1 ? "Active" : "Inactive"}}</span></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><a href="#" wire:click="odometer({{$vehicle->id}})"><i class="fa fa-tachometer-alt color-default"></i> Mileage & Hours</a></th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$vehicle->mileage ? $vehicle->mileage."Kms" : ""}} {{$vehicle->hours ? "& ".$vehicle->hours." Hours" : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><a href="#" wire:click="nextService({{$vehicle->id}})"><i class="fa fa-wrench color-default"></i> Next Service</a></th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$vehicle->next_service ? $vehicle->next_service."Kms" : ""}} {{$vehicle->next_service_hours ? "& ".$vehicle->next_service_hours." Hours" : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><a href="#" wire:click="fuelTankCapacity({{$vehicle->id}})"><i class="fas fa-gas-pump color-warning"></i> Tank Capacity</a></th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>  {{$vehicle->fuel_tank_capacity ? $vehicle->fuel_tank_capacity."Litres" : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><a href="#" wire:click="fuelTank({{$vehicle->id}})"><i class="fas fa-gas-pump color-warning"></i> Tank Balance </a></th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>  {{$vehicle->fuel_balance ? $vehicle->fuel_balance."Litres" : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>No Of Wheels</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$vehicle->no_of_wheels}}</small>
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
                        <h5>Vehicle Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">{{$vehicle->vehicle_group ? $vehicle->vehicle_group->name : ""}}</span>
                    <span class="label label-danger label-rounded label-bordered">{{$vehicle->vehicle_type ? $vehicle->vehicle_type->name : ""}}</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">

            <div class="row mb-30">

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat-2 bg-success" href="#">
                        <div class="stat-content">
                            <span>{{$vehicle->mileage ? $vehicle->mileage."Kms" : ""}}</span>
                        </div>
                        <span class="stat-footer"><i class="fa fa-arrow-up color-success"></i> MILEAGE</span>
                    </a>
                    <!-- /.dashboard-stat-2 -->
                </div>
                 <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <a class="dashboard-stat-2 bg-danger" href="#">
                    <div class="stat-content">
                        <span>{{$vehicle->hours ? $vehicle->hours." Horse" : ""}}</span>
                    </div>
                    <span class="stat-footer"><i class="fa fa-arrow-up color-success"></i> HOURS</span>
                </a>
                <!-- /.dashboard-stat-2 -->
            </div>
    
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat-2 bg-primary" href="#">
                        <div class="stat-content">
    
                            <span class="name">{{($vehicle->next_service ? $vehicle->next_service."Kms" : "")}} | {{($vehicle->next_service_hours ? $vehicle->next_service_hours." Hours" : "")}}</span>
                        </div>
                        <span class="stat-footer"><i class="fa fa-arrow-up color-success"></i> NEXT SERVICE</span>
                    </a>
                    <!-- /.dashboard-stat-2 -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

    
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat-2 bg-warning" href="#">
                        <div class="stat-content">
                            <span >{{$vehicle->fuel_consumption}}</span>
                        </div>
                        <span class="stat-footer"><i class="fa fa-arrow-up color-success"></i> FUEL CONSUMPTION</span>
                    </a>
                    <!-- /.dashboard-stat-2 -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
    
              
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
    
            </div>
            <!-- /.row -->

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Vehicle Details</a></li>
                 <li role="presentation" ><a href="#stock_on_board" aria-controls="stock_on_board" role="tab" data-toggle="tab">Stock On Board</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#images" aria-controls="images" role="tab" data-toggle="tab">Images</a></li>
                <li role="presentation"><a href="#fitness" aria-controls="fitness" role="tab" data-toggle="tab">Reminders</a></li>
                <li role="presentation"><a href="#tyres" aria-controls="tyres" role="tab" data-toggle="tab">Tyres</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                <li role="presentation"><a href="#orders" aria-controls="orders" role="tab" data-toggle="tab">Fuel Orders</a></li>
                <li role="presentation"><a href="#bills" aria-controls="bills" role="tab" data-toggle="tab">Bills</a></li>
                <li role="presentation"><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">Logs</a></li>
                <li role="presentation"><a href="#service" aria-controls="service" role="tab" data-toggle="tab">Service History</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Transporter</th>
                                <td class="w-20 line-height-35"> {{$vehicle->transporter ? $vehicle->transporter->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Registration #</th>
                                <td class="w-20 line-height-35"> {{$vehicle->registration_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Make</th>
                                <td class="w-20 line-height-35">{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "")}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Model</th>
                                <td class="w-20 line-height-35">{{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "")}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Chasis #</th>
                                    <td class="w-20 line-height-35">{{$vehicle->chasis_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Engine #</th>
                                    <td class="w-20 line-height-35">{{$vehicle->engine_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Year</th>
                                    <td class="w-20 line-height-35">{{$vehicle->year}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Acquisition Date</th>
                                    <td class="w-20 line-height-35">{{$vehicle->start_date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Dispose Date</th>
                                    <td class="w-20 line-height-35">{{$vehicle->end_date}}</td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">GVM</th>
                                    <td class="w-20 line-height-35">{{$vehicle->gvm ? number_format($vehicle->gvm,2)."Kgs" : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">NVM</th>
                                    <td class="w-20 line-height-35">{{$vehicle->nvm ? number_format($vehicle->nvm,2)."Kgs" : ""}}</td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">Color</th>
                                    <td class="w-20 line-height-35">{{$vehicle->color}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">No of wheels</th>
                                    <td class="w-20 line-height-35">{{$vehicle->no_of_wheels}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Fuel Type</th>
                                    <td class="w-20 line-height-35">{{$vehicle->fuel_type}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Fuel Consumption (Empty)</th>
                                    <td class="w-20 line-height-35">{{$vehicle->fuel_consumption_empty_standard}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Fuel Consumption (Loaded)</th>
                                    <td class="w-20 line-height-35">{{$vehicle->fuel_consumption_loaded_standard}}</td>
                                </tr>
                               
                                <tr>
                                    <th class="w-10 text-center line-height-35">Manufatured By</th>
                                    <td class="w-20 line-height-35">{{$vehicle->manufacturer}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Condition</th>
                                    <td class="w-20 line-height-35">{{$vehicle->condition}}</td>
                                </tr>
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vehicle Type</th>
                                    <td class="w-20 line-height-35"> {{$vehicle->vehicle_type ? $vehicle->vehicle_type->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vehicle Group</th>
                                    <td class="w-20 line-height-35"> {{$vehicle->vehicle_group ? $vehicle->vehicle_group->name : ""}}</td>
                                </tr>

                        </tbody>
                    </table>
                </div>
                 <div role="tabpanel" class="tab-pane " id="stock_on_board">
                    @livewire('stock-on-board.index', ['id' => $vehicle->id, 'equipment' => "vehicle"])
            </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $vehicle->id,'category'=>'vehicle'])
                </div>
                <div role="tabpanel" class="tab-pane" id="images">

                    @livewire('vehicles.images', ['id' => $vehicle->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="fitness">
             @livewire('fitnesses.index', ['id' => $vehicle->id,'type' => "Vehicle"])
                </div>
                 <div role="tabpanel" class="tab-pane" id="tyres">
                @livewire('tyres.tyre-assignments', ['id' => $vehicle->id, 'type' => "Vehicle"])
            </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    @livewire('vehicles.trips', ['id' => $vehicle->id])
                </div>
               
                <div role="tabpanel" class="tab-pane" id="orders">
                    <div class="panel-title">
                        <a href="#" wire:click="exportFuelsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                        <a href="#" wire:click="exportFuelsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                        <a href="#" wire:click="exportFuelsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                    </div>
                    <br>
                    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <tr>
                                <th class="th-sm">Order#
                                </th>
                                <th class="th-sm">CreatedBy
                                </th>
                                <th class="th-sm">Date
                                </th>
                                <th class="th-sm">Category
                                </th>
                                <th class="th-sm">Station
                                </th>
                                <th class="th-sm">FillUp
                                </th>
                                <th class="th-sm">Quantity
                                </th>
                                <th class="th-sm">Comments
                                </th>
                                <th class="th-sm">Action
                                </th>
                          </tr>
                        </thead>
                        @if (isset($fuels))
                        <tbody>
                            @forelse  ($fuels as $fuel)
                            @if ($fuel->fillup == 1)
                            <tr style="background-color: #4CAF50">
                              <td>{{$fuel->order_number}}</td>
                              <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                              <td>
                                @php
                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                @endphp
                                @if ((preg_match($pattern, $fuel->date)) )
                                    {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                @else
                                {{$fuel->date}}
                                @endif    
                               </td>
                              <td>
    
                                @if ($fuel->type == "Horse")
                                    @if (isset($fuel->horse))
                                    Horse: {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}} {{$fuel->horse->horse_make ? "| ".$fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                        @if (isset($fuel->trip))
                                        <br>
                                            @php
                                                $from = App\Models\Destination::find($fuel->trip->from);
                                                $to = App\Models\Destination::find($fuel->trip->from);
                                            @endphp
                                            Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                            @if (isset($from))
                                                {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                            @endif
                                            @if (isset($to))
                                                {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                            @endif
                                    
                                        @endif
                                    @endif
                                
                                @endif
                              </td>
                              <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                              <td>{{$fuel->fillup == "1" ? "Initial" : ($fuel->fillup == "0" ? "Top Up" : "")}}</td>
                              <td>{{$fuel->quantity ? $fuel->quantity."Litres" : ""}}</td>
                              <td>{{$fuel->comments}}</td>
                              <td class="w-10 line-height-35 table-dropdown">
                                  <div class="dropdown">
                                      <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          <i class="fa fa-bars"></i>
                                          <span class="caret"></span>
                                      </button>
                                      <ul class="dropdown-menu">
                                          <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                          @if ($fuel->authorization == "approved")
                                          <li><a href="#" wire:click="topup({{$fuel->id}})"  ><i class="fa fa-gas-pump color-warning"></i>TopUp</a></li>
                                          <li><a href="{{route('fuels.preview',$fuel->id)}}"  ><i class="fa fa-file-invoice color-primary"></i>Preview</a></li>
                                          @endif
                                          @if ($fuel->authorization == "pending" || $fuel->authorization == "rejected" )
                                          <li><a href="#"  wire:click="edit({{$fuel->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                          <li><a href="#" data-toggle="modal" data-target="#fuelDeleteModal{{ $fuel->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                          @endif
                                      </ul>
                                  </div>
                                  @include('fuels.delete')
                          </td>
                            </tr>
                            @elseif ($fuel->fillup == 0)
                            <tr style="background-color: #FFC107">
                              <td>{{$fuel->order_number}}</td>
                              <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                              <td>
                                @php
                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                @endphp
                                @if ((preg_match($pattern, $fuel->date)) )
                                    {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                @else
                                {{$fuel->date}}
                                @endif  
                            </td>
                              <td>
                                @if ($fuel->type == "Horse")
                                @if (isset($fuel->horse))
                                Horse: {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}} {{$fuel->horse->horse_make ? "| ".$fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                    @if (isset($fuel->trip))
                                    <br>
                                        @php
                                            $from = App\Models\Destination::find($fuel->trip->from);
                                            $to = App\Models\Destination::find($fuel->trip->from);
                                        @endphp
                                        Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                        @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                        @endif
                                        @if (isset($to))
                                            {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                        @endif
                                
                                    @endif
                                @endif
                       
                            @endif
                              </td>
                              <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                              <td>{{$fuel->fillup == "1" ? "Initial" : ($fuel->fillup == "0" ? "Top Up" : "")}}</td>
                              <td>{{$fuel->quantity ? $fuel->quantity."Litres" : ""}}</td>
                              <td>{{$fuel->comments}}</td>
                              <td class="w-10 line-height-35 table-dropdown">
                                  <div class="dropdown">
                                      <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          <i class="fa fa-bars"></i>
                                          <span class="caret"></span>
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                        @if ($fuel->authorization == "approved")
                                        <li><a href="{{route('fuels.preview',$fuel->id)}}"  ><i class="fa fa-file-invoice color-primary"></i>Preview</a></li>
                                        @endif
                                        @if ($fuel->authorization == "pending" || $fuel->authorization == "rejected" )
                                        <li><a href="#"  wire:click="edit({{$fuel->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                        <li><a href="#" data-toggle="modal" data-target="#fuelDeleteModal{{ $fuel->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                        @endif
                                    </ul>
                                  </div>
                                  @include('fuels.delete')
                          </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="9">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Fuel Orders Found ....
                                    </div>
                                   
                                </td>
                              </tr> 
                            @endforelse
                        </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                         @endif
                      </table>
    
                      <nav class="text-center" style="float: right">
                        <ul class="pagination rounded-corners">
                            @if (isset($fuels))
                                {{ $fuels->links() }} 
                            @endif 
                        </ul>
                    </nav>   
                </div>
                <div role="tabpanel" class="tab-pane" id="bills">
                    <div class="panel-title">
                        <a href="#" wire:click="exportBillsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                        <a href="#" wire:click="exportBillsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                        <a href="#" wire:click="exportBillsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                    </div>
                    <br>
                    <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Bill#
                            </th>
                            <th class="th-sm">Bill Summary
                            </th>
                            <th class="th-sm">Item(s)
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Total
                            </th>
                            <th class="th-sm">Actions
                            </th>
                          </tr>
                        </thead>
                        @if (isset($bills))
                        <tbody>
                            @forelse ($bills as $bill)
                                
                           
                           
                          <tr>
                            <td>{{$bill->bill_number}}</td>
                            <td>
                                @if ($bill->transporter)
                                    Transporter | <a href="{{ route('transporters.show',$bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill->transporter ? $bill->transporter->name  : ""}}</a> 
                                @elseif($bill->vendor)
                                    Vendor | <a href="{{ route('vendors.show',$bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill->vendor ? $bill->vendor->name : "" }}</a> 
                                    @if ($bill->horse)
                                        <br>
                                        Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                    @elseif ($bill->vehicle)
                                        <br>
                                        Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                    @elseif ($bill->trailer)
                                        <br>
                                        Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                    @elseif ($bill->driver)
                                        <br>
                                        Driver | <a href="{{route('drivers.show', $bill->driver->id)}}" style="color: blue" target="_blank">{{$bill->driver->employee ? $bill->driver->employee->name : ""}} {{$bill->driver->employee ? $bill->driver->employee->surname : ""}} </a> 
                                    @endif
                                   
                                  
                                @elseif ( $bill->container && $bill->top_up)
                                    Fuel Topup | <a href="{{ route('containers.show', $bill->container->id) }}" style="color: blue" target="_blank">{{ $bill->container ? $bill->container->name : "" }}</a> 
                                @elseif ( $bill->fuel)
                                    @if ($bill->trip)
                                    Trip Expense - Fuel Order | <a href="{{ route('fuels.show', $bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill->fuel ? $bill->fuel->order_number : "" }}</a> | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                    @else
                                    Fuel Order | <a href="{{ route('fuels.show', $bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill->fuel ? $bill->fuel->order_number : "" }}</a> 
                                    @endif
                                   
                                @elseif ( $bill->invoice)
                                    Invoice VAT | <a href="{{ route('invoices.show', $bill->invoice->id) }}" style="color: blue" target="_blank">{{ $bill->invoice ? $bill->invoice->invoice_number : "" }}</a> 
                                @elseif ($bill->ticket || $bill->ticket_inventory || $bill->ticket_expense)
                                @if ($bill->ticket_inventory)
                                    Workshop Ticket | <a href="{{ route('tickets.show', $bill->ticket_inventory->ticket->id) }}" style="color: blue" target="_blank">{{  $bill->ticket_inventory->ticket ? $bill->ticket_inventory->ticket->ticket_number : "" }}</a> 
                                @else
                                     Workshop Ticket | <a href="{{ route('tickets.show', $bill->ticket->id) }}" style="color: blue" target="_blank">{{  $bill->ticket ? $bill->ticket->ticket_number : "" }}</a> 
                                @endif
                                @if ($bill->horse)
                                    <br>
                                    Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                @elseif ($bill->vehicle)
                                    <br>
                                    Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                @elseif ($bill->trailer)
                                    <br>
                                    Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                @endif
                                @elseif ($bill->trip && ($bill->horse || $bill->driver || $bill->driver))
                                    Trip Expense | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                @elseif ($bill->purchase)
                                    {{ $bill->category }} | <a href="{{ route('purchases.show', $bill->purchase->id) }}" style="color: blue" target="_blank">{{ $bill->purchase->purchase_number }}</a> 
                                @elseif ($bill->workshop_service)
                                    Service | {{$bill->workshop_service->account ? $bill->workshop_service->account->name : ""}} | <a href="{{ route('workshop_services.show', $bill->workshop_service->id) }}" style="color: blue" target="_blank">{{ $bill->workshop_service->workshop_service_number }}</a> 
                                @elseif ($bill->horse && !$bill->vendor)
                                   
                                    Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                @elseif ($bill->vehicle && !$bill->vendor)
                                   
                                    Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                @elseif ($bill->trailer && !$bill->vendor)
                                   
                                    Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                @elseif ($bill->driver && !$bill->vendor)
                                  
                                    Driver | <a href="{{route('drivers.show', $bill->driver->id)}}" style="color: blue" target="_blank">{{$bill->driver->employee ? $bill->driver->employee->name : ""}} {{$bill->driver->employee ? $bill->driver->employee->surname : ""}} </a> 
                                @endif
                                
                                @if ($bill->description)
                                <br>
                                {{$bill->description}}
                                @endif
                               
                            </td>
                            <td>
                               @if ($bill->bill_expenses)
                                @foreach ($bill->bill_expenses as $bill_expense)
                                    @if ($bill_expense->expense)
                                        {{$bill_expense->expense ? $bill_expense->expense->name : ""}}
                                    @elseif($bill_expense->product)
                                        {{ $bill_expense->product->brand ? $bill_expense->product->brand->name : ""}} {{ $bill_expense->product ? $bill_expense->product->name : ""}}
                                    @elseif($bill_expense->inventory)
                                        {{ $bill_expense->inventory->product->brand ? $bill_expense->inventory->product->brand->name : ""}} {{ $bill_expense->inventory->product ? $bill_expense->inventory->product->name : ""}}
                                    @endif
                                    @if (!$loop->last),@endif
                                @endforeach
                            @endif
                            </td>
                            <td>{{$bill->bill_date}}</td>
                            <td>{{$bill->currency ? $bill->currency->name : ""}}</td> 
                            <td>
                                @if ($bill->total)
                                     {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                                @endif
                            </td>
                            <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{route('bills.show',$bill->id)}}"  ><i class="fas fa-eye color-default"></i>View</a></li>
                                    </ul>
                                </div>
                                @include('bills.delete')
                        </td>
                          </tr>
                          @empty
                          <tr>
                            <td colspan="10">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Bills Found ....
                                </div>
                               
                            </td>
                          </tr>  
                            @endforelse
                        </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                         @endif
                      </table>
                      <nav class="text-center" style="float: right">
                        <ul class="pagination rounded-corners">
                            @if (isset($bills))
                                {{ $bills->links() }} 
                            @endif 
                        </ul>
                    </nav>   
                </div>
                <div role="tabpanel" class="tab-pane" id="logs">
                    <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Log#
                            </th>
                            <th class="th-sm">Employee
                            </th>
                            <th class="th-sm">From
                            </th>
                            <th class="th-sm">To
                            </th>
                            <th class="th-sm">Departure
                            </th>
                            <th class="th-sm">Starting Mileage
                            </th>
                            <th class="th-sm">Arrival
                            </th>
                            <th class="th-sm">Ending Mileage
                            </th>
                            <th class="th-sm">Distance
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          
                            @forelse ($logs as $log)
                          <tr>
                            <td>{{$log->log_number}}</td>
                            <td>{{$log->employee ? $log->employee->name : "" }} {{$log->employee ? $log->employee->surname : "" }}</td>
                            <td>{{$log->from}} </td>
                            <td>{{$log->to}} </td>
                            <td>{{$log->departure_datetime}} </td>
                            <td>
                                {{$log->starting_mileage ? $log->starting_mileage."Kms" : ""}} 
                               </td>
                            <td>{{$log->arrival_datetime}} </td>
                            <td>
                                {{$log->ending_mileage ? $log->ending_mileage."Kms" : ""}}
                            </td>
                            <td>
                                {{$log->distance ? $log->distance."Kms" : ""}}
                            </td>
                          </tr>
                          @empty
                              <tr>
                                <td colspan="9">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Vehicle Logs Found ....
                                    </div>
                                   
                                </td>
                              </tr> 
                              @endforelse
                      
                        </tbody>
    
    
                      </table>
                      <nav class="text-center" style="float: right">
                        <ul class="pagination rounded-corners">
                            @if (isset($logs))
                                @if ($logs->count()>0)
                                    {{ $logs->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav>   
                </div>

                <div role="tabpanel" class="tab-pane" id="service">
                    <div class="panel-title">
                        <a href="#" wire:click="exportBookingsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                        <a href="#" wire:click="exportBookingsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                        <a href="#" wire:click="exportBookingsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                    </div>
                    <br>
                    <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Booking#
                            </th>
                            <th class="th-sm">RequestedBy
                            </th>
                            <th class="th-sm">AssignedTo
                            </th>
                            <th class="th-sm">Type
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Station
                            </th>
                            <th class="th-sm">Mileage
                            </th>
                            <th class="th-sm">Status
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                        
                            @forelse ($bookings as $booking)
                            <tr>
                                <td>{{$booking->booking_number}}</td>
                                <td>{{ucfirst($booking->employee ? $booking->employee->name : "")}} {{ucfirst($booking->employee ? $booking->employee->surname : "")}}</td>
                                <td>
                                    @if (isset($booking->employees) && $booking->employees->count()>0)
                                        @foreach ($booking->employees as $mechanic)
                                            {{ $mechanic->name }} {{ $mechanic->surname }}
                                            <br>
                                        @endforeach
                                    @elseif(isset($booking->vendor))
                                        {{ucfirst($booking->vendor->name)}}  
                                    @endif
                                </td>
                                <td>{{$booking->service_type ? $booking->service_type->name : ""}}</td>
                                <td>{{$booking->in_date}} {{$booking->in_time}}</td>
                                <td>{{$booking->station}}</td>
                                <td>{{$booking->odometer ? $booking->odometer."Kms" : ""}}  </td>
                                <td><span class="badge bg-{{$booking->status == 1 ? "warning" : "success"}}">{{$booking->status == 1 ? "Open" : "Closed"}}</span></td>
                              </tr>
                              @empty
                              <tr>
                                <td colspan="8">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Bookings Found ....
                                    </div>
                                   
                                </td>
                              </tr> 
                              @endforelse
                        
                        </tbody>
                      </table>
                      <nav class="text-center" style="float: right">
                        <ul class="pagination rounded-corners">
                            @if (isset($bookings))
                                @if ($bookings->count()>0)
                                    {{ $bookings->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav>   
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
        <!-- /.col-md-9 -->
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelTankCapacityModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Update Fuel Tank Capacity <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateFuelTankCapacity()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Fuel Tank Capacity in Litres<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="fuel_tank_capacity" placeholder="Enter Fuel Tank Capacity in Litres" required />
                        @error('fuel_tank_capacity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelTankModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Update Fuel Level <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateFuelTank()" >
                <div class="modal-body">

                    <div class="form-group">
                        <label for="name">Fuel Level in Litres<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" min="1" max="{{ $vehicle->fuel_tank_capacity }}" class="form-control" wire:model.debounce.300ms="fuel_balance" {{ $vehicle->fuel_tank_capacity ? "" : "disabled" }} placeholder="Enter Fuel Level" required />
                        <small style="color: red">{{ $vehicle->fuel_tank_capacity ? "" : "Please set vehicle fuel tank capacity first before setting vehicle fuel level"  }}</small>
                        @error('fuel_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="odometerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Update Vehicle Mileage <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateOdometer()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Vehicle Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Vehicle Mileage" required />
                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vehicle Hours</label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Vehicle Hours"/>
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="nextServiceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Update Next Service Mileage <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateNextService()" >
                <div class="modal-body">
                        <div class="row">
                                <div class="col-md-6">
                                      <div class="form-group">
                                            <label for="name">Next Service Mileage<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="{{ $vehicle->mileage }}" class="form-control" wire:model.debounce.300ms="next_service" {{ $vehicle->mileage ? "" : "disabled" }} placeholder="Enter vehicle Next Service Mileage" required />
                                            <small style="color: red">{{ !isset($vehicle->mileage) ? "Please set vehicle mileage first before setting vehicle next mileage" : ""  }}</small>
                                            @error('next_service') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                </div>
                                <div class="col-md-6">
                                          <div class="form-group">
                                            <label for="name">Next Service Hours</label>
                                            <input type="number" step="any" min="{{ $vehicle->hours }}" class="form-control" wire:model.debounce.300ms="next_service_hours" {{ $vehicle->hours ? "" : "disabled" }} placeholder="Enter vehicle Next Service Engine Hours" required />
                                            <small style="color: red">{{ !isset($vehicle->hours) ? "Please set vehicle hours first before setting vehicle next hours" : ""  }}</small>
                                            @error('next_service_hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="cargoModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-building-o"></i> Add Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" >
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="issue">Issue</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="issue" placeholder="Enter Issue Date" >
                                @error('issue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry">Expiry</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="expiry" placeholder="Enter Expiry Date" >
                                @error('expiry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
