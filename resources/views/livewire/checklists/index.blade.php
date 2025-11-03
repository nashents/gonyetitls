<div>
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
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="checklist_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Inspection Created At</option>
                                    <option value="date">Inspection Date</option>
                                </select>
                                    </div>

                                    <!-- /input-group -->
                                </div>

                            
                                <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
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
                                <a href="{{ route('checklists.create') }}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inspection</a>
                               
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search inspection...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Inspection#
                                    </th>
                                    <th class="th-sm">Inspected On
                                    </th>
                                      <th class="th-sm">Inspected By
                                    </th>
                                    <th class="th-sm">Checklist
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Inpection For
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($checklists))
                                <tbody>
                                    @forelse ($checklists as $checklist)
                                  <tr>
                                    <td>{{$checklist->checklist_number}}</td>
                                     <td>{{Carbon\Carbon::parse($checklist->date)->format('F j, Y g:i A')}}</td>
                                     <td>{{$checklist->user ? $checklist->user->name : ""}} {{$checklist->user ? $checklist->user->surname : ""}}</td>
                                    <td>{{$checklist->checklist_category ? $checklist->checklist_category->name : ""}}</td>
                                    <td>
                                        @if ($checklist->employee)
                                        {{$checklist->employee ? $checklist->employee->name : ""}} {{$checklist->employee ? $checklist->employee->surname : ""}}        
                                        @elseif($checklist->driver)
                                        {{$checklist->driver->employee ? $checklist->driver->employee->name : ""}} {{$checklist->driver->employee ? $checklist->driver->employee->surname : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($checklist->horse)
                                            Horse | {{$checklist->horse->registration_number}} {{$checklist->horse->fleet_number ?  "(".$checklist->horse->fleet_number.")" : ""}}
                                        @elseif($checklist->vehicle)
                                            Vehicle | {{$checklist->vehicle->registration_number}} {{$checklist->vehicle->fleet_number ?  "(".$checklist->vehicle->fleet_number.")" : ""}}
                                        @elseif($checklist->trailer)
                                            Trailer | {{$checklist->trailer ? $checklist->trailer->registration_number : ""}} {{$checklist->trailer->fleet_number ?  "(".$checklist->trailer->fleet_number.")" : ""}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('checklists.show', $checklist->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="{{ route('checklists.edit', $checklist->id) }}"   ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#checklistDeleteModal{{ $checklist->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('checklists.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
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
                                    @if (isset($checklists))
                                        {{ $checklists->links() }} 
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

