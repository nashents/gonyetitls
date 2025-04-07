<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="{{ route('checklists.add',$gate_pass_id) }}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inspection</a>
        <br>
        <br>
        <table id="checklistsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Inspection#
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">CheckedBy
                </th>
                <th class="th-sm">DrivenBy
                </th>
                <th class="th-sm">Inpection For
                </th>
                <th class="th-sm">Action
                </th>
              </tr>
            </thead>
            @if ($checklists->count()>0)
            <tbody>
                @foreach ($checklists as $checklist)
              <tr>
                <td>{{$checklist->checklist_number}}</td>
                <td>{{$checklist->date}}</td>
                <td>{{$checklist->user ? $checklist->user->name : ""}} {{$checklist->user ? $checklist->user->surname : ""}}</td>
                <td>
                    @if ($checklist->employee)
                    {{$checklist->employee ? $checklist->employee->name : ""}} {{$checklist->employee ? $checklist->employee->surname : ""}}        
                    @elseif($checklist->driver)
                    {{$checklist->driver->employee ? $checklist->driver->employee->name : ""}} {{$checklist->driver->employee ? $checklist->driver->employee->surname : ""}}
                    @endif
                </td>
                <td>
                    @if ($checklist->horse)
                    {{$checklist->horse->registration_number}} | {{$checklist->horse->horse_make ? $checklist->horse->horse_make->name : ""}} {{$checklist->horse->horse_model ? $checklist->horse->horse_model->name : ""}} 
                    @elseif($checklist->vehicle)
                    {{$checklist->vehicle->registration_number}} | {{$checklist->vehicle->vehicle_make ? $checklist->vehicle->vehicle_make->name : ""}} {{$checklist->vehicle->vehicle_model ? $checklist->vehicle->vehicle_model->name : ""}} 
                    @elseif ($checklist->trailer)
                    {{$checklist->trailer ? $checklist->trailer->registration_number : ""}} | {{$checklist->trailer ? $checklist->trailer->make : ""}} {{$checklist->trailer ? $checklist->trailer->model : ""}} 
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
                            <li><a href="#"  wire:click="edit({{$checklist->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#checklistDeleteModal{{ $checklist->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('checklists.delete')
            </td>
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>
    {{-- </blockquote> --}}

</div>
