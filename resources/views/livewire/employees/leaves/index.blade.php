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
                                    <a href="" data-toggle="modal" data-target="#leavesImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportEmployeesLeaveExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportEmployeesLeaveCSV()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportEmployeesLeavePDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                            </div>
                           
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search employees...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Employee
                                        </th>
                                        <th class="th-sm">Leave Taken
                                        </th>
                                        <th class="th-sm">Leave Day(s)
                                        </th>
                                        <th class="th-sm">Accrual Rate
                                        </th>
                                        <th class="th-sm">Maximum Leave Days
                                        </th>   
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($employees))
                                    <tbody>
                                        @forelse ($employees as $employee)
                                      
                                        <tr>
                                            <td>
                                                <strong>{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</strong> <br>
                                                <small class="text-muted">
                                                    <strong>Emp#:</strong> {{ucfirst($employee->employee_number)}} <br>
                                                    <strong>Post: </strong>{{ucfirst($employee->post)}}
                                                </small>
                                            </td>
                                            <td>
                                                @foreach($this->getLeavesTaken($employee->id) as $leave)
                                                    <div>
                                                        <strong>{{ $leave['leave_type'] }}</strong>: {{ $leave['days_taken'] }}
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>{{$employee->leave_days}}</td>
                                            <td>{{$employee->accrual_rate}}</td>
                                            <td>{{$employee->maximum_leave_days}}</td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" wire:click.prevent="showLeave({{$employee->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    </ul>
                                                </div>
                                        </td>
                                          </tr>
                                    
                                          @empty
                                          <tr>
                                            <td colspan="7">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Employees Found ....
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
                                        @if (isset($employees))
                                            {{ $employees->links() }} 
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leavesImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Leave Days Data <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('employees.leaves.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Employee Leave Data Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload Employee File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveDaysModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Leave Details <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Accrual Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="accrual_rate" placeholder="Enter Leave Accrual Rate" required>
                                    @error('accrual_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Available Leave Days<span class="required" style="color: red">*</span></label>
                                    <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Enter Leave Accrual Rate" required>
                                    @error('leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">Maximum Leave Days<span class="required" style="color: red">*</span></label>
                            <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="maximum_leave_days" placeholder="Enter maximum leave days one can acrrue" required>
                            @error('maximum_leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

          <!-- Modal -->


    </div>
