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
                                    <a href="{{route('inspections.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>inspection</a>
                                </div> --}}
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                                <table id="inspectionsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Inspection#
                                        </th>
                                        <th class="th-sm">CreatedBy
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
                                    @if ($inspections->count()>0)
                                    <tbody>
                                        @foreach ($inspections as $inspection)
                                      <tr>
                                        <td>{{ucfirst($inspection->inspection_number)}}</td>
                                        <td>{{ucfirst($inspection->user ? $inspection->user->name : "undefined")}} {{ucfirst($inspection->user ? $inspection->user->surname : "undefined")}}</td> 
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
                                      @endforeach
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
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
