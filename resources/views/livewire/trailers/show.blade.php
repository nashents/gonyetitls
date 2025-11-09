<div>
    <div class="col-md-3">

        <div class="panel border-primary no-border border-3-top">
            <div class="panel-heading">
                <div class="panel-title">
                    <center><h5>{{$trailer->make}} {{$trailer->model}}</h5></center>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        @php
                        if ($trailer->trailer_images->count()>0) {
                            $image = $trailer->trailer_images->first();
                        }
                        @endphp
                        @if (isset($image))
                        <img src="{{asset('images/uploads/'.$image->filename)}}" alt="trailer Avatar" class="img-responsive">
                        @else
                        <img src="{{asset('images/trailer.jpg')}}" alt="Trailer Avatar" class="img-responsive">
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
                  <center> <h5>{{ $trailer->registration_number }} {{ $trailer->fleet_number ? "| ".$trailer->fleet_number : "" }}</h5></center> 
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>Trips</th>
                                <td>
                                    <small class="color-success"><i class="fa fa-arrow-right"></i> {{$trailer->trips->count()}}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <small class="color-success"><i class="fa fa-arrow-right"></i> <span class="badge bg-{{$trailer->status == 1 ? "success" : "danger"}}">{{$trailer->status == 1 ? "Active" : "Inactive"}}</span></small>
                                </td>
                            </tr>
                            <tr>
                                <th>No Of Wheels</th>
                                <td>
                                    <small class="color-success"><i class="fa fa-arrow-right"></i> {{ $trailer->no_of_wheels }}</small>
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
                    <h5>Trailer Tags</h5>
                </div>
            </div>
            <div class="panel-body p-20">
                <span class="label label-danger label-rounded label-bordered">{{$trailer->trailer_type ? $trailer->trailer_type->name : ""}}</span>
            </div>
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-md-3 -->

    <div class="col-md-9">


        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Trailer Details</a></li>
             <li role="presentation" ><a href="#stock_on_board" aria-controls="stock_on_board" role="tab" data-toggle="tab">Stock On Board</a></li>
            <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Document(s)</a></li>
            <li role="presentation"><a href="#images" aria-controls="images" role="tab" data-toggle="tab">Image(s)</a></li>
            <li role="presentation"><a href="#fitness" aria-controls="fitness" role="tab" data-toggle="tab">Reminder(s)</a></li>
            <li role="presentation"><a href="#tyres" aria-controls="tyres" role="tab" data-toggle="tab">Tyre(s)</a></li>
            <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trip(s)</a></li>
            <li role="presentation"><a href="#service" aria-controls="service" role="tab" data-toggle="tab">Service History</a></li>
            <li role="presentation"><a href="#bills" aria-controls="bills" role="tab" data-toggle="tab">Bills</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Trailer#</th>
                            <td class="w-20 line-height-35"> {{$trailer->trailer_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Transporter</th>
                            <td class="w-20 line-height-35"> {{$trailer->transporter ? $trailer->transporter->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Fleet#</th>
                            <td class="w-20 line-height-35"> {{$trailer->fleet_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Registration#</th>
                            <td class="w-20 line-height-35"> {{$trailer->registration_number}}</td>
                        </tr>
                        @php
                          
                            $trailer_link = App\Models\TrailerLink::where('trailer_a',$trailer->id)
                                                                   ->orWhere('trailer_b',$trailer->id)->get()->first();
                        @endphp
                          @if (isset($trailer_link))  
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailer Link</th>
                                <td class="w-20 line-height-35"> {{App\Models\Trailer::find($trailer_link->trailer_a)->registration_number}} <i class="fas fa-link"></i> {{App\Models\Trailer::find($trailer_link->trailer_b)->registration_number}} </td>
                            </tr>
                         @endif
                      
                        <tr>
                            <th class="w-10 text-center line-height-35">Make</th>
                            <td class="w-20 line-height-35">{{ucfirst($trailer->make)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Model</th>
                            <td class="w-20 line-height-35">{{ucfirst($trailer->model)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Chasis Number</th>
                            <td class="w-20 line-height-35">{{$trailer->chasis_number}}</td>
                        </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Year</th>
                                <td class="w-20 line-height-35">{{$trailer->year}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">GVM</th>
                                <td class="w-20 line-height-35">{{$trailer->gvm ? number_format($trailer->gvm,2)."Kgs" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">NVM</th>
                                <td class="w-20 line-height-35">{{$trailer->nvm ? number_format($trailer->nvm,2)."Kgs" : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo Type</th>
                                <td class="w-20 line-height-35">{{$trailer->cargo_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Capacity Details</th>
                                <td class="w-20 line-height-35">
                                    @foreach ($trailer->capacities as $capacity)
                                        {{$capacity->cargo ? $capacity->cargo->name : ""}} {{$capacity->capacity}} {{$capacity->measurement ? $capacity->measurement->name : ""}}
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Compartment Details</th>
                                <td class="w-20 line-height-35">{{$trailer->compartments}}</td>
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Acquisition Date</th>
                                <td class="w-20 line-height-35">{{$trailer->start_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Dispose Date</th>
                                <td class="w-20 line-height-35">{{$trailer->end_date}}</td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Color</th>
                                <td class="w-20 line-height-35">{{$trailer->color}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Manufatured By</th>
                                <td class="w-20 line-height-35">{{$trailer->manufacturer}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Condition</th>
                                <td class="w-20 line-height-35">{{$trailer->condition}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Suspension</th>
                                <td class="w-20 line-height-35">{{$trailer->suspension_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailer Type</th>
                                <td class="w-20 line-height-35"> {{$trailer->trailer_type ? $trailer->trailer_type->name : ""}}</td>
                            </tr>

                    </tbody>
                </table>
            </div>
             <div role="tabpanel" class="tab-pane " id="stock_on_board">
                    @livewire('stock-on-board.index', ['id' => $trailer->id, 'equipment' => "trailer"])
            </div>
            <div role="tabpanel" class="tab-pane" id="documents">

                @livewire('documents.index', ['id' => $trailer->id,'category'=>'trailer'])
            </div>
            <div role="tabpanel" class="tab-pane" id="images">

                @livewire('trailers.images', ['id' => $trailer->id])
            </div>
            <div role="tabpanel" class="tab-pane" id="fitness">
                @livewire('fitnesses.index', ['id' => $trailer->id, 'category' => "Trailer"])
                   </div>

            <div role="tabpanel" class="tab-pane" id="tyres">
        <div class="panel-title">
            <a href="#" wire:click="exportTyreAssignmentsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
            <a href="#" wire:click="exportTyreAssignmentsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
            <a href="#" wire:click="exportTyreAssignmentsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
        </div>
        <br>
        <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Tyre#
                </th>
                <th class="th-sm">Product
                </th>
                <th class="th-sm">Serial#
                </th>
                <th class="th-sm">Specifications
                </th>
                <th class="th-sm">Axle
                </th>
                <th class="th-sm">Position
                </th>
                <th class="th-sm">Fitting Mileage
                </th>
                <th class="th-sm">Current Mileage
                </th>
            </tr>
            </thead>
            <tbody>
            
                @forelse ($tyre_assignments as $tyre_assignment)
                <tr>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}}</td>
                    <td>
                        @if ($tyre_assignment->tyre)
                        {{$tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : ""}} {{$tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}}
                        @endif
                    </td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->serial_number : ""}}</td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->width : ""}} / {{$tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : ""}} R {{$tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : ""}}</td>
                    <td>{{$tyre_assignment->position}}</td>
                    <td>{{$tyre_assignment->axle}}</td>
                    <td>{{$tyre_assignment->starting_odometer ? $tyre_assignment->starting_odometer." Kms" : ""}}</td>
                    <td>{{$tyre_assignment->trailer->mileage ? $tyre_assignment->trailer->mileage." Kms" : ""}}</td>
                </tr>
                @empty
                <tr>
                <td colspan="8">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No tyres assigned to horse found ....
                    </div>
                    
                </td>
                </tr> 
                @endforelse
        
        </tbody>
        </table>
        <nav class="text-center" style="float: right">
        <ul class="pagination rounded-corners">
            @if (isset($tyre_assignments))
                @if ($tyre_assignments->count()>0)
                    {{ $tyre_assignments->links() }} 
                @endif
            @endif 
        </ul>
    </nav>   
    </div>
            <div role="tabpanel" class="tab-pane" id="trips">
                @livewire('trailers.trips', ['id' => $trailer->id])
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

            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group pull-right mt-10" >
                       <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                    </div>
                </div>
                </div>
            <!-- /.section-title -->
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
</div>
