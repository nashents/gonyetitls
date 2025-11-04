<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                         <div class="panel-title">
                                    <h5>Date Range</h5>
                                    <div class="row">
                                    <div class="col-lg-2" style="margin-right: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      From
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                             
                                   
                                    <!-- /input-group -->
                                </div>
                              
                                </div>
                                <div class="panel-title">
                                    <a href="#" wire:click="exportTicketsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportTicketsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportTicketsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                                    <br>
                                    <br>
                                    <div class="col-lg-3" style="margin-left:-15px">
                                        <div class="input-group">
                                          <span class="input-group-addon">Ticket Status</span>
                                          <select wire:model.debounce.300ms="ticket_status" class="form-control" aria-label="..." >
                                            <option value="all">All</option>
                                            <option value="0">Closed</option>
                                            <option value="1">Open</option>
                                          </select>
                                        </div>
                                            <!-- /input-group -->
                                        </div>
                                </div>

                          

                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Tickets...">
                                    </div>
                                </div>

                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">

                                    <thead >
                                        <tr>
                                        <th class="th-sm">Ticket#
                                        </th>
                                        <th class="th-sm">Inspection#
                                        </th>
                                        <th class="th-sm">TicketFor
                                        </th>
                                        <th class="th-sm">AssignedTo
                                        </th>
                                        <th class="th-sm" style="width: 20%">Timelines
                                        </th>
                                        <th class="th-sm">Narration
                                        </th>
                                        <th class="th-sm">Station
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($tickets))
                                   
                                    <tbody>
                                        @forelse ($tickets as $ticket)
                                      <tr>
                                       
                                        <td>
                                           {{$ticket->ticket_number}}
                                        </td>
                                        <td>
                                            @if (isset($ticket->inspection))
                                                <a href="{{route('bookings.show',$ticket->inspection->id)}}" style="color: blue">{{$ticket->inspection->inspection_number}}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($ticket->booking->horse))
                                                Horse |  {{$ticket->booking->horse->registration_number}} {{$ticket->booking->horse->fleet_number ? "(".$ticket->booking->horse->fleet_number.")" : ""}}
                                                @elseif(isset($ticket->booking->vehicle))
                                                Vehicle |  {{$ticket->booking->vehicle->registration_number}} {{$ticket->booking->vehicle->fleet_number ? "(".$ticket->booking->vehicle->fleet_number.")" : ""}}
                                                @elseif(isset($ticket->booking->asset))
                                                Asset | {{$ticket->booking->asset->product->brand ? $ticket->booking->asset->product->brand->name : ""}} {{ucfirst($ticket->booking->asset->product ? $ticket->booking->asset->product->name : "")}}  {{$ticket->booking->asset->serial_number}}
                                                @elseif(isset($ticket->booking->trailer))
                                                Trailer | {{$ticket->booking->trailer->registration_number}} {{$ticket->booking->trailer->registration_number ? "(".$ticket->booking->trailer->registration_number.")" : ""}} 
                                            @endif
                                           </td>
                                           <td>
                                            @if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0)
                                                @foreach ($ticket->booking->employees as $mechanic)
                                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                                    <br>
                                                @endforeach
                                            @elseif(isset($ticket->booking->vendor))
                                                {{ucfirst($ticket->booking->vendor->name)}}  
                                            @endif
                                        </td>
                                       <td>
                                            <strong>In: </strong> {{$ticket->booking->in_date}} {{$ticket->booking->in_time}} <br>
                                            <strong>Estimate: </strong> {{$ticket->booking->estimated_out_date}} {{$ticket->booking->estimated_out_time}} <br>
                                            <strong>Completed: </strong> {{$ticket->booking->out_date}} {{$ticket->booking->out_time}} <br>
                                            <strong>Out: </strong> {{$ticket->booking ? $ticket->booking->workshop_out_date : ""}} {{$ticket->booking ? $ticket->booking->workshop_out_time : ""}} <br>
                                        </td>
                                         <td>
                                            <strong>Job Type: </strong> {{$ticket->service_type ? $ticket->service_type->name : ""}}  <br>
                                            {{Str::limit($ticket->booking ? $ticket->booking->description : "",100,'...')}}
                                        </td>
                                        <td>{{ optional(\App\Models\Station::find($ticket->booking->station_id))->name ?? $ticket->booking->station }}</td>
                                        <td><span class="badge bg-{{$ticket->status == 1 ? "warning" : "success"}}">{{$ticket->status == 1 ? "Open" : "Closed"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('tickets.show', $ticket->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    {{-- <li><a href="{{route('tickets.preview',$ticket->id)}}"   ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li> --}}
                                                    <li><a href="{{route('tickets.jobcard',$ticket->id)}}"   ><i class="fas fa-file color-warning"></i> JobCard</a></li>
                                                    @if ($ticket->inspection->status == 1)
                                                    <li><a href="{{route('inspections.show', $ticket->inspection->id)}}"><i class="fa fa-search color-default"></i>Inspection</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('tickets.delete')

                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="9">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Tickets Found ....
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
                                        @if (isset($tickets))
                                            {{ $tickets->links() }} 
                                        @endif 
                                    </ul>
                                </nav>    

                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>

        


   

    </div>
