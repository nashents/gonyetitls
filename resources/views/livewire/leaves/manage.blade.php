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
                                        Filter Leaves By
                                        </span>
                                        <select wire:model.debounce.300ms="leave_filter" class="form-control" aria-label="..." >
                                            <option value="all">All Leaves</option>
                                            <option value="inprogress">In Progress Leaves</option>
                                            <option value="cancelled">Cancelled Leaves</option>
                                            <option value="last_month">Last Month Leaves</option>
                                            <option value="greater_than_10_days">Leave Duration (>10)</option>
                                        </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                            
                                <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                From
                                </span>
                                <input type="date" wire:model.debounce.300ms="range_from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                To
                                </span>
                                <input type="date" wire:model.debounce.300ms="range_to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                               
                               
                                <!-- /input-group -->
                            </div>
                                <a href="" data-toggle="modal" data-target="#leaveModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Apply Leave</a>
                                <a href="#" wire:click="exportLeavesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportLeavesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportLeavesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search leaves using days,type,employee,department,dates,reason...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                     <th class="th-sm">Date Applied
                                    </th>
                                    <th class="th-sm">Start Date
                                    </th>
                                    <th class="th-sm">End Date
                                    </th>
                                    <th class="th-sm">Duration
                                    </th>
                                    <th class="th-sm">Reason
                                    </th>
                                    <th class="th-sm">Flags
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if (isset($leaves))
                                <tbody>
                                    @forelse ($leaves as $leave)
                                  <tr>
                                    <td>
                                        {{$leave->user?->name}} {{$leave->user?->surname}}
                                    </td>
                                    <td>
                                        {{ucfirst($leave->employee ? $leave->employee->name : '')}} {{ucfirst($leave->employee ? $leave->employee->surname : '')}}
                                        @if ($leave->department)
                                            <br>
                                            <small><strong><i>{{$leave->department ? $leave->department->name : ""}}</i></strong></small>
                                        @endif
                                    </td>
                                    <td>{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->created_at)->format('d F Y')}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->from)->format('d F Y')}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->to)->format('d F Y')}}</td>
                                    <td>{{$leave->days ? $leave->days." Days" : ""}}</td>
                                    <td>{{$leave->reason}}</td>
                                    <td>
                                        @if ($leave->is_backdated)
                                            <span class="badge bg-danger">Backdated</span>
                                        @endif    
                                        @if ($leave->is_emergency)
                                            <span class="badge bg-warning">Emergency</span>
                                        @endif    
                                    </td>
                                 
                                    <td>
                                         @php
                                            $today = Carbon\Carbon::today();
                                            $start = Carbon\Carbon::parse($leave->from);
                                            $end = Carbon\Carbon::parse($leave->to);

                                            if ($today->lt($start)) {
                                                $status = 'Not Yet Started';
                                                $badgeClass = 'badge bg-warning text-dark';
                                            } elseif ($today->between($start, $end)) {
                                                $status = 'In Progress';
                                                $badgeClass = 'badge bg-info text-white';
                                            } else {
                                                $status = 'Completed';
                                                $badgeClass = 'badge bg-success';
                                            }
                                        @endphp
                                       <span class="badge bg-{{($leave->status == 'approved') ? 'success' : (($leave->status == 'rejected') ? 'danger' : 'warning') }}">{{($leave->status == 'approved') ? 'approved' : (($leave->status == 'rejected') ? 'rejected' : 'pending' )}}</span>
                                        <small><span class="{{ $badgeClass }}">{{ $status }}</span></small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($leave->management_decision == "pending" && Auth::user()->id === $leave->user_id)
                                                    <li><a href="#" data-toggle="modal" data-target="#leaveEditModal" wire:click.prevent="edit({{$leave->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#leaveDeleteModal{{$leave->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                                
                                            </ul>
                                        </div>
                                        @include('leaves.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Leave Applications Found ....
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
                                    @if (isset($leaves))
                                        {{ $leaves->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Apply Leave <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                 
                    <div class="row">
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Employees<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                  <option value="" selected>Select Employee</option>
                                  @foreach ($employees as $employee)
                                      <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                  @endforeach
                              </select>
                                @error('selectedEmployee') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Departments<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="department_id" class="form-control" required >
                                  <option value="" selected>Select Department</option>
                                  @foreach ($employee_departments as $department)
                                      <option value="{{$department->id}}">{{ucfirst($department->name)}}</option>
                                  @endforeach
                              </select>
                                @error('department_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Leave Type<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="leave_type_id" class="form-control" required >
                                    <option value="" selected>Select Leave Type</option>
                                    @foreach ($leave_types as $leave_type)
                                        <option value="{{$leave_type->id}}">{{$leave_type->name}}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('leave_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Leave Type</a></small><a href="#" wire:click.prevent="refresh('leave_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('leave_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_days">Available Leave Days<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="available_leave_days" disabled >
                                @error('available_leave_days') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="is_backdated"   class="line-style" />
                                <label for="one" class="radio-label">Backdated Leave Application</label>
                                @error('is_backdated') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="ignore_public_holidays"   class="line-style" />
                                <label for="one" class="radio-label">Ignore Public Holidays</label>
                                @error('ignore_public_holidays') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="vat">Days Calculation<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="days_calculation" required>
                                    <option value="">Select Option</option>
                                    <option value="include_weekends">Include Weekends</option>
                                    <option value="exclude_weekends">Exclude Weekends</option>
                                    <option value="one_weekend_day">1 Weekend Day</option>
                                </select>
                                @error('days_calculation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($this->is_backdated == True)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="from" required />
                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">End Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="to" required />
                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        @else
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" min="<?= date('Y-m-d'); ?>" wire:model.debounce.300ms="from" required />
                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">End Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" min="<?= date('Y-m-d'); ?>" wire:model.debounce.300ms="to" required />
                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">Days Applied<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="days" required disabled/>
                                @error('days') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Reason for leave<span class="required" style="color: red">*</span></label>
                               <textarea class="form-control" wire:model.debounce.300ms="reason"  cols="30" rows="5" required ></textarea>
                                @error('reason') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- /.col-md-6 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if (isset($available_leave_days) && $available_leave_days > 0)
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Apply</button>
                        @else
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" disabled><i class="fa fa-save"></i>Apply</button>
                        @endif
                        
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Leave Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                  <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Employees<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                  <option value="" selected>Select Employee</option>
                                  @foreach ($employees as $employee)
                                      <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                  @endforeach
                              </select>
                                @error('selectedEmployee') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Departments<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="department_id" class="form-control" required >
                                  <option value="" selected>Select Department</option>
                                  @foreach ($employee_departments as $department)
                                      <option value="{{$department->id}}">{{ucfirst($department->name)}}</option>
                                  @endforeach
                              </select>
                                @error('department_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                          <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type">Leave Type<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="leave_type_id" class="form-control" required >
                                    <option value="" selected>Select Leave Type</option>
                                    @foreach ($leave_types as $leave_type)
                                        <option value="{{$leave_type->id}}">{{$leave_type->name}}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('leave_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Leave Type</a></small><a href="#" wire:click.prevent="refresh('leave_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('leave_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_days">Available Leave Days<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="available_leave_days" disabled >
                                @error('available_leave_days') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                      
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="is_backdated"   class="line-style" />
                                <label for="one" class="radio-label">Backdated Leave Application</label>
                                @error('is_backdated') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="ignore_public_holidays"   class="line-style" />
                                <label for="one" class="radio-label">Ignore Public Holidays</label>
                                @error('ignore_public_holidays') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                   
                      <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="vat">Days Calculation<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="days_calculation" required>
                                    <option value="">Select Option</option>
                                    <option value="include_weekends">Include Weekends</option>
                                    <option value="exclude_weekends">Exclude Weekends</option>
                                    <option value="one_weekend_day">1 Weekend Day</option>
                                </select>
                                @error('days_calculation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($this->is_backdated == True)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="from" required />
                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">End Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="to" required />
                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        @else
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" min="<?= date('Y-m-d'); ?>" wire:model.debounce.300ms="from" required />
                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">End Leave Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" min="<?= date('Y-m-d'); ?>" wire:model.debounce.300ms="to" required />
                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to">Days Applied<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="days" required disabled/>
                                @error('days') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Reason for leave<span class="required" style="color: red">*</span></label>
                                   <textarea class="form-control" wire:model.debounce.300ms="reason"  cols="30" rows="5" required ></textarea>
                                    @error('reason') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
    
                            <!-- /.col-md-6 -->
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

</div>
