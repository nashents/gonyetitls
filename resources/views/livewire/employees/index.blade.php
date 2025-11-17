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
                                    <a href="{{route('employees.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Employee</a>
                                    <a href="" data-toggle="modal" data-target="#employeesImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportEmployeesExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportEmployeesCSV()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportEmployeesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                    <a href="#" wire:click="bulkSendCredentials()" class="btn btn-default border-primary btn-rounded btn-wide" style="float: right"><i class="fa fa-send-o"></i>Bulk Send Credentails</a> 
                                    @if (Auth::user()->is_admin())
                                    <a href="#" wire:click="setUsernames()" class="btn btn-default border-primary btn-rounded btn-wide" style="float: right"><i class="fa fa-key"></i>Set Usernames</a> 
                                    @endif
                                   
                                </div>

                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search employees...">
                                    </div>
                                </div>
                                <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">
                                            Profile
                                        </th>
                                        <th class="th-sm">Emp#
                                        </th>
                                        <th class="th-sm">Fullname
                                        </th>
                                        <th class="th-sm">Gender
                                        </th>
                                        <th class="th-sm">Email
                                        </th>
                                        <th class="th-sm">Work Details
                                        </th>
                                        <th class="th-sm">Account
                                        </th>
                                       
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if (isset($employees))
                                    <tbody>
                                        @forelse ($employees as $employee)
                                        @if (!$employee->driver)
                                        <tr>
                                            <td class="line-height-35"><img src="{{asset('images/uploads/'.$employee->user->profile)}}" alt="" class="border-radius-50 img-circle profile-img " style="width: 50px; height:50px"></td>
                                            <td>{{ucfirst($employee->employee_number)}}
                                                <br>
                                                <small><strong>Created: </strong> 
                                                {{Carbon\Carbon::parse($employee->created_at)->format('d F Y')}}</small>
                                            </td>
                                            <td>{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</td>
                                            <td>{{$employee->gender}}</td>
                                            <td>{{$employee->email}}</td>
                                            <td>
                                                <strong>Deparments:</strong>
                                                @foreach ($employee->departments as $department)
                                                    {{$department->name}} @if (!$loop->last),@endif
                                                @endforeach
                                                <br>
                                                <strong>Post: </strong>{{$employee->post}} <br>
                                                <strong>Branch: </strong>{{$employee->branch ? $employee->branch->name : ""}} <br>
                                                <strong>Rank: </strong>{{$employee->ranks ? $employee->ranks->first()->name : ""}} <br>
                                                <strong>Role(s):</strong> 
                                                    @if ($employee->user->roles)
                                                        @foreach ($employee->user->roles as $role)
                                                            {{$role->name}} @if (!$loop->last),@endif
                                                        @endforeach
                                                    @endif
                                            </td>
                                            <td>
                                                @if ($employee->user)
                                                    <span class="badge bg-{{$employee->user->active == 1 ? "success" : "danger"}}">{{$employee->user->active == 1 ? "Active" : "Inactive"}}</span> <br>
                                                    @if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL))
                                                            <button type="button"  wire:click.prevent="sendCredentials({{$employee->id}})" class="btn btn-default btn-rounded btn-xs mt-5"><i class="fa fa-send-o"></i>{{$employee->user->sent_credentials == False ? "Send Credentials" : "Resend Credentials"}}</button>
                                                    @endif
                                                @else
                                                <span class="badge bg-danger">Deleted</span>
                                                @endif
                                            </td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="{{route('employees.show', $employee->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                        <li><a href="{{route('employees.edit', $employee->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" wire:click.prevent="changePosition({{$employee->id}})"><i class="fa fa-edit color-success"></i> Change Position</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#employeeDeleteModal{{$employee->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                       @if ($employee->user)
                                                       @if ($employee->user->active == 1)
                                                       <li><a href="{{route('employees.deactivate', $employee->id)}}"  ><i class="fa fa-ban color-danger"></i>Suspend</a></li>
                                                       @else
                                                       <li><a href="{{route('employees.activate', $employee->id)}}"  ><i class="fa fa-toggle-off color-success"></i>Unsuspend</a></li>
                                                       <li><a href="{{route('employees.archive', $employee->id)}}"  ><i class="fa fa-archive color-primary"></i>Archive</a></li>
                                                       @endif
                                                       @endif
    
                                                    </ul>
                                                </div>
                                                @include('employees.delete')
    
                                        </td>
                                          </tr>
                                        @endif
                            
                                     @empty
                                  <tr>
                                    <td colspan="10">
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="employeesImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Employees <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('employees.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Employee(s) Excel File</label>
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

          <!-- Modal -->

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="changePositionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Change 
                        @if (isset($employee))
                            {{$employee ? $employee->name : ""}} {{$employee ? $employee->surname."` " : ""}}
                        @endif
                        Position <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <div style="padding-left: 20px; padding-right:10px;">
                    <small style="color:green"> PS: if the user has access to modules in different departments after this change you have to add more departments to the user`s account via employees -> employee view -> departments tab -> add department</small>
                </div>
                
                <form wire:submit.prevent="changeUpdate()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail13">Job Titles<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="job_title_id" class="form-control" required>
                                    <option value="" selected > Select Job Title</option>
                                    @foreach ($job_titles as $job_title)
                                        <option value="{{$job_title->id}}">{{$job_title->title}}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('job_titles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Job Title</a></small> <a href="#" wire:click.prevent="refresh('job_titles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('job_title_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Grades</label>
                                <select class="form-control" wire:model.debounce.300ms="grade_id" >
                                    <option value="">Select Grade</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->grade_code }} {{ $grade->grade_name }}</option>
                                    @endforeach
                                </select>
                                 <small><a href="{{ route('grades.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Grade</a></small> <a href="#" wire:click.prevent="refresh('grades')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                  
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Ranks</label>
                                <select class="form-control" wire:model.debounce.300ms="rank_id" >
                                    <option value="">Select Rank</option>
                                    @foreach ($ranks as $rank)
                                        <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                                    @endforeach
                                </select>
                                @error('rank_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Departments</label>
                                <select class="form-control" wire:model.debounce.300ms="department_id">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"> {{$department->name }} </option>
                                    @endforeach
                                </select>
                                @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Branches</label>
                                <select class="form-control" wire:model.debounce.300ms="branch_id" >
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"> {{$branch->name }} </option>
                                    @endforeach
                                </select>
                                 <small><a href="{{ route('branches.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Branch</a></small> <a href="#" wire:click.prevent="refresh('branches')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('branch_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">End Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Previous position end date"/>
                                @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date" placeholder="New position start date"/>
                                @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Change Reason<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="change_reason" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Acting">Acting</option>
                                    <option value="Appointment">Appointment</option>
                                    <option value="Demotion">Demotion</option>
                                    <option value="Update">Information Update</option>
                                    <option value="Promotion">Promotion</option>
                                    <option value="Transfer">Transfer</option>
                                </select>
                                @error('change_reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Remarks</label>
                                <textarea wire:model.debounce.300ms="remarks" class="form-control" cols="30" rows="3"></textarea>
                                @error('remarks') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
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
