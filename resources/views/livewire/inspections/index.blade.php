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
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Inspections...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Inspection#
                                        </th>
                                        <th class="th-sm">Booking#
                                        </th>
                                        <th class="th-sm">Ticket#
                                        </th>
                                        <th class="th-sm">ServiceType
                                        </th>
                                        <th class="th-sm">AssignedTo
                                        </th>
                                        <th class="th-sm">Inspection For
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if (isset($inspections))
                                    <tbody>
                                        @forelse ($inspections as $inspection)
                                      <tr>
                                        <td>{{$inspection->inspection_number}}</td>
                                        <td>
                                            @if ($inspection->booking)
                                                <a href="{{route('bookings.show',$inspection->booking->id)}}" target="_blank" style="color: blue">{{$inspection->booking ? $inspection->booking->booking_number : ""}}</a>        
                                            @endif
                                        </td>
                                        <td>
                                            @if ($inspection->ticket)
                                                <a href="{{route('tickets.show',$inspection->ticket->id)}}" target="_blank" style="color: blue">{{$inspection->ticket ? $inspection->ticket->ticket_number : ""}}</a>        
                                            @endif
                                        </td>
                                        <td>{{$inspection->service_type ? $inspection->service_type->name : ""}}</td> 
                                        <td>
                                            @if (isset($inspection->booking->employees) && $inspection->booking->employees->count()>0)
                                                @foreach ($inspection->booking->employees as $mechanic)
                                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                                    <br>
                                                @endforeach
                                            @elseif(isset($inspection->booking->vendor))
                                                {{ucfirst($inspection->booking->vendor->name)}}  
                                            @endif
                                        </td>
                                  
                                        <td>
                                            @if (isset($inspection->horse))
                                            Horse | {{ucfirst($inspection->horse->horse_make ? $inspection->horse->horse_make->name : "")}} {{ucfirst($inspection->horse->horse_model ? $inspection->horse->horse_model->name : "" )}} {{ucfirst($inspection->horse->registration_number)}}
                                            @elseif(isset($inspection->vehicle))
                                            Vehicle | {{ucfirst($inspection->vehicle->vehicle_make->name)}} {{ucfirst($inspection->vehicle->vehicle_model->name)}} {{ucfirst($inspection->vehicle->registration_number)}}
                                            @elseif(isset($inspection->trailer))
                                            Trailer | {{ucfirst($inspection->trailer->name)}} {{ucfirst($inspection->trailer->registration_number)}}
                                            @endif
                                           </td>
                                        <td><span class="badge bg-{{$inspection->status == 1 ? "warning" : "success"}}">{{$inspection->status == 1 ? "Open" : "Closed"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if ($inspection->inspection_results->count()>0)
                                                    <li><a href="{{route('tickets.show', $inspection->ticket->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    @endif
                                                    @if ($inspection->status == 1)
                                                     <li><a href="{{route('inspections.show', $inspection->id)}}"><i class="fa fa-search color-default"></i>Inspect</a></li>
                                                    @endif
                                                   
                                                    {{-- <li><a href="{{route('inspections.edit', $inspection->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li> --}}
                                                    {{-- <li><a href="#" data-toggle="modal" data-target="#inspectionDeleteModal{{$inspection->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                                                </ul>
                                            </div>
                                            @include('inspections.delete')

                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="6">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Inspections Found ....
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
                                        @if (isset($inspections))
                                            @if ($inspections->count()>0)
                                                {{ $inspections->links() }} 
                                            @endif
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
