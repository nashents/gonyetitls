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
                                      <input type="date" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                             
                                   
                                    <!-- /input-group -->
                                </div>
                              
                                </div>
                                <div class="panel-title">
                                    <a href="{{route('incidents.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Incident</a>
                                </div>
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search incidents...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Incident#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">IncidentFor
                                        </th>
                                        <th class="th-sm">MOT
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($incidents))
                                    <tbody>
                                        @forelse  ($incidents as $incident)
                                        
                                      <tr>
                                        <td>{{ucfirst($incident->incident_number)}}</td>
                                        <td>{{ucfirst($incident->user ? $incident->user->name : "")}} {{ucfirst($incident->user ? $incident->user->surname : "")}}</td>
                                        <td>
                                            @if ($incident->driver)
                                            {{ucfirst($incident->driver->employee ? $incident->driver->employee->name : "")}} {{ucfirst($incident->driver->employee ? $incident->driver->employee->surname : "")}}        
                                            @elseif($incident->employee)
                                            {{ucfirst($incident->employee ? $incident->employee->name : "")}} {{ucfirst($incident->employee ? $incident->employee->surname : "")}}        
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($incident->horse))
                                            Horse | {{ucfirst($incident->horse->horse_make ? $incident->horse->horse_make->name : "")}} {{ucfirst($incident->horse->horse_model ? $incident->horse->horse_model->name : "" )}} {{ucfirst($incident->horse ? $incident->horse->registration_number : "")}} {{ucfirst($incident->horse ? "| ".$incident->horse->fleet_number : "")}}
                                            @elseif(isset($incident->vehicle))
                                            Vehicle | {{ucfirst($incident->vehicle->vehicle_make ? $incident->vehicle->vehicle_make->name : "")}} {{ucfirst($incident->vehicle->vehicle_model ? $incident->vehicle->vehicle_model->name : "")}} {{ucfirst($incident->vehicle ? $incident->vehicle->registration_number : "")}} {{ucfirst($incident->vehicle ? "| ".$incident->vehicle->fleet_number : "")}}
                                            @elseif(isset($incident->trailer))
                                            Trailer | {{ucfirst($incident->trailer ? $incident->trailer->make : "")}} {{ucfirst($incident->trailer ? $incident->trailer->model : "")}} {{ucfirst($incident->trailer ? $incident->trailer->registration_number : "")}} {{ucfirst($incident->trailer ? "| ".$incident->trailer->fleet_number : "")}}
                                            @endif
                                        </td>
                                        <td>{{$incident->date}}</td>
                                        <td><span class="badge bg-{{($incident->authorization == 'approved') ? 'success' : (($incident->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($incident->authorization == 'approved') ? 'approved' : (($incident->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td><span class="badge bg-{{$incident->status == 1 ? "warning" : "success"}}">{{$incident->status == 1 ? "Open" : "Closed"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('incidents.show', $incident->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    {{-- <li><a href="{{route('incidents.edit', $incident->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#"  wire:click="showincident({{$incident->id}})"><i class="fa fa-window-close color-success"></i> Close Incident</a></li> --}}
                                                    {{-- @if ($incident->authorization == "pending" || $incident->authorization == "rejected") --}}
                                                    <li><a href="#" data-toggle="modal" data-target="#incidentDeleteModal{{$incident->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    {{-- @endif --}}
                                                   

                                                </ul>
                                            </div>
                                            @include('incidents.delete')

                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="10">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No incidents Found ....
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
                                        @if (isset($incidents))
                                            {{ $incidents->links() }} 
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

{{-- 
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-window-close"></i> Close incident {{ $incident->incident_number }}<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                        <p> Assigned to | @foreach ($incident->employees as $employee)
                            {{ $employee->name }} {{ $employee->surname }},
                        @endforeach</p>
                    </div>
                    <form wire:submit.prevent="closeincident()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Decision</label>
                        <select class="form-control" wire:model.debounce.300ms="status" required>
                            <option value="">Select Decision</option>
                            <option value="0">Close</option>
                            <option value="1">Open</option>
                        </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="reason">Comments</label>
                           <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
        </div> --}}

    </div>
