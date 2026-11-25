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
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="attendance_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Attendance Created At</option>
                                                    <option value="date">Attendance Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    To
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <!-- /input-group -->
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Departments
                                                </span>
                                                <select wire:model.debounce.300ms="department_id" class="form-control" aria-label="..." >
                                                    <option value="created_at">Select Department</option>
                                                    @foreach ($departments as $department)
                                                         <option value="{{$department->id}}">{{$department->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                </div>
                           
                                <div class="panel-title">
                                    <a href="#" data-toggle="modal" data-target="#attendanceModal" wire:click="newRegister" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Attendance Register</a>
                                    <a href="#" wire:click="exportAttendanceRegisterExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportAttendanceRegisterCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportAttendanceRegisterPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search attendance register...">
                                    </div>
                                </div>
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Attendance#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Date & Time
                                        </th>
                                        <th class="th-sm">Department
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($attendances))
                                    <tbody>
                                        @forelse  ($attendances as $attendance)
                                       
                                      <tr wire:key="{{ 'abfe12-100-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($attendance->id ?? '') }}">
                                        <td>
                                            {{$attendance->attendance_number}}
                                            @if ($attendance->isDraft())
                                                <span class="label label-warning" title="Draft - can still be edited">Draft</span>
                                            @else
                                                <span class="label label-success" title="Final - locked; only unmarked employees can be filled in">Final</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ucfirst($attendance->user ? $attendance->user->name : "")}} {{ucfirst($attendance->user ? $attendance->user->surname : "")}}
                                            <br><small class="text-muted">{{ optional($attendance->created_at)->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{$attendance->date}} {{$attendance->time ? "@ ".$attendance->time : ""}}</td>
                                        <td>{{$attendance->department ? $attendance->department->name : ""}}</td>
                                      
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('attendances.show', $attendance->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{ route('audit_logs.index', ['attendance' => $attendance->id]) }}"><i class="fa fa-history color-default"></i> Audit Trail</a></li>
                                                    @if ($attendance->user_id == Auth::user()->id)
                                                        @if ($attendance->isDraft())
                                                            <li><a href="#" wire:click.prevent="edit({{$attendance->id}})"><i class="fa fa-edit color-success"></i> Edit Draft</a></li>
                                                        @else
                                                            <li><a href="#" wire:click.prevent="openUnmarked({{$attendance->id}})"><i class="fa fa-user-plus color-success"></i> Update Unmarked</a></li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </div>
                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Attendance Register Found ....
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
                                        @if (isset($attendances))
                                            @if ($attendances->count()>0)
                                                {{ $attendances->links() }} 
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
 <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-calendar"></i> Mark Attendance <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bin_number">Time<span class="required" style="color: red">*</span></label>
                                    <input type="time" class="form-control" wire:model.debounce.300ms="time" placeholder="Enter Time" required>
                                    @error('time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>     
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Departments<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="selectedDepartment" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                   </select>
                                    @error('selectedDepartment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-10" style="margin-top: 30px;">
                                    <input type="checkbox" wire:model.debounce.300ms="is_drivers" {{$selected_department?->name == "Transport & Logistics" ? "" : "disabled"}}  class="line-style" />
                                    <label for="one" class="radio-label">Mark drivers attendance</label>
                                    @error('is_drivers') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>   
                        @if (isset($selectedDepartment))
                            <div  style="height: 500px; overflow: auto" >
                        @endif 
                            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" >
                                <caption><strong>{{$selected_department?->name}}</strong></caption>
                                <thead>
                                    <tr>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Shift
                                    </th>
                                    <th class="th-sm">Checkin Time
                                    </th>
                                    <th class="th-sm">Checkout Time
                                    </th>
                                    <th class="th-sm">Notes
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('livewire.attendances.partials.register-rows', ['prefix' => 'create'])
                                </tbody>
                            </table>
                    @if (isset($selectedDepartment))
                        </div>
                    @endif
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn btn-warning btn-wide btn-rounded" wire:loading.attr="disabled"><i class="fa fa-file-o"></i>Save as Draft</button>
                            <button type="button" class="btn bg-success btn-wide btn-rounded" wire:click="store(true)" wire:loading.attr="disabled"
                                onclick="if(!confirm('Save as Final? It can no longer be edited - only unmarked employees can be filled in later.')){event.stopImmediatePropagation();return false;}"><i class="fa fa-check"></i>Save as Final</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attendanceEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Attendance Register <span class="label label-warning">Draft</span><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bin_number">Time<span class="required" style="color: red">*</span></label>
                                    <input type="time" class="form-control" wire:model.debounce.300ms="time" placeholder="Enter Time" required>
                                    @error('time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>     
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Departments<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="selectedDepartment" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                   </select>
                                    @error('selectedDepartment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-10" style="margin-top: 30px;">
                                    <input type="checkbox" wire:model.debounce.300ms="is_drivers" {{$selected_department?->name == "Transport & Logistics" ? "" : "disabled"}}  class="line-style" />
                                    <label for="one" class="radio-label">Mark drivers attendance</label>
                                    @error('is_drivers') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>   
                        @if (isset($selectedDepartment))
                            <div  style="height: 500px; overflow: auto" >
                        @endif 
                            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" >
                                <caption><strong>{{$selected_department?->name}}</strong></caption>
                                <thead>
                                    <tr>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Shift
                                    </th>
                                    <th class="th-sm">Checkin Time
                                    </th>
                                    <th class="th-sm">Checkout Time
                                    </th>
                                    <th class="th-sm">Notes
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('livewire.attendances.partials.register-rows', ['prefix' => 'edit'])
                                </tbody>
                            </table>
                    @if (isset($selectedDepartment))
                        </div>
                    @endif
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn btn-warning btn-wide btn-rounded" wire:loading.attr="disabled"><i class="fa fa-file-o"></i>Save Draft</button>
                            <button type="button" class="btn bg-success btn-wide btn-rounded" wire:click="update(true)" wire:loading.attr="disabled"
                                onclick="if(!confirm('Approve as Final? It can no longer be edited - only unmarked employees can be filled in later.')){event.stopImmediatePropagation();return false;}"><i class="fa fa-check"></i>Approve as Final</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Final registers: only employees left unmarked can be filled in --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attendanceUnmarkedModal" tabindex="-1" role="dialog" aria-labelledby="attendanceUnmarkedLabel">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="attendanceUnmarkedLabel"><i class="fas fa-user-plus"></i> Update Unmarked Employees <span class="label label-success">Final</span><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="saveUnmarked()">
                    <div class="modal-body">
                        @if ($unmarkedAttendance)
                            <p>
                                <strong>{{ $unmarkedAttendance->attendance_number }}</strong>
                                &middot; {{ $unmarkedAttendance->department->name ?? '' }}
                                &middot; {{ $unmarkedAttendance->date }} {{ $unmarkedAttendance->time ? '@ ' . $unmarkedAttendance->time : '' }}
                            </p>
                            <div class="alert alert-info" style="padding:8px 12px">
                                <i class="fa fa-lock"></i> This register is final. Only the employees below, who were not marked, can be marked now. Everyone already marked stays as it is.
                            </div>
                        @endif
                        @error('unmarked') <div class="alert alert-danger" style="padding:8px 12px">{{ $message }}</div> @enderror
                        <div style="max-height: 500px; overflow: auto">
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Employee</th>
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Shift</th>
                                        <th class="th-sm">Checkin Time</th>
                                        <th class="th-sm">Checkout Time</th>
                                        <th class="th-sm">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('livewire.attendances.partials.register-rows', ['prefix' => 'unmarked', 'emptyMessage' => 'Everyone on this register has been marked.'])
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            @if (count($employees))
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled"><i class="fa fa-save"></i>Save</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
