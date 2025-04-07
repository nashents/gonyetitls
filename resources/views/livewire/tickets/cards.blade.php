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
                                {{-- <div class="panel-title">
                                    <a href="{{route('tickets.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>ticket</a>
                                </div> --}}
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                                <table id="job_cardsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Booking#
                                        </th>
                                        <th class="th-sm">Inspection#
                                        </th>
                                        <th class="th-sm">Job For
                                        </th>
                                        <th class="th-sm">Service Type
                                        </th>
                                        <th class="th-sm">In Date
                                        </th>
                                        <th class="th-sm">In Time
                                        </th>
                                        <th class="th-sm">Out Date
                                        </th>
                                        <th class="th-sm">Out Time
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if (isset($tickets))
                                    @if ($tickets->count()>0)
                                    <tbody>
                                        @foreach ($tickets as $ticket)
                                      <tr>
                                        <td>{{$ticket->booking ? $ticket->booking->booking_number : ""}}</td>
                                        <td>
                                            @if ($ticket->inspection->inspection_results->count()>0)
                                            <a href="{{route('tickets.show', $ticket->id)}}" style="color: blue" target="_blank">{{$ticket->inspection ? $ticket->inspection->inspection_number : ""}}</a>
                                            @endif
                                            @if ($ticket->inspection->status == 1)
                                             <a href="{{route('inspections.show', $ticket->inspection->id)}}" style="color: blue" target="_blank">{{$ticket->inspection ? $ticket->inspection->inspection_number : ""}}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($ticket->booking->horse)
                                                Horse | {{$ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : ""}} {{$ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : ""}} {{$ticket->booking->horse ? $ticket->booking->horse->registration_number : ""}}
                                            @elseif ($ticket->booking->vehicle)
                                                Vehicle | {{$ticket->booking->vehicle->vehicle_make ? $ticket->booking->vehicle->vehicle_make->name : ""}} {{$ticket->booking->vehicle->vehicle_model ? $ticket->booking->vehicle->vehicle_model->name : ""}} {{$ticket->booking->vehicle ? $ticket->booking->vehicle->registration_number : ""}}
                                            @elseif ($ticket->booking->trailer)
                                                Trailer | {{$ticket->booking->trailer ? $ticket->booking->trailer->make : ""}} {{$ticket->booking->trailer ? $ticket->booking->trailer->make : ""}} {{$ticket->booking->trailer ? $ticket->booking->trailer->registration_number : ""}}
                                            @endif
                                        </td>
                                        <td>{{$ticket->service_type ? $ticket->service_type->name : ""}}</td>
                                        <td>{{$ticket->in_date}}</td>
                                        <td>{{$ticket->in_time}}</td>
                                        <td>{{$ticket->out_date}}</td>
                                        <td>{{$ticket->out_time}}</td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('tickets.show', $ticket->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('tickets.preview',$ticket->id)}}"   ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                    @if ($ticket->inspection->status == 1)
                                                    <li><a href="{{route('inspections.show', $ticket->inspection->id)}}"><i class="fa fa-search color-default"></i>Inspection</a></li>
                                                    @endif
                                                   
                                                    {{-- <li><a href="#" data-toggle="modal" data-target="#ticketDeleteModal{{$ticket->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                                                </ul>
                                            </div>
                                            @include('tickets.delete')

                                    </td>
                                      </tr>
                                      @endforeach
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif
                                    @endif
                                  </table>

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
