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
                                    @if (Auth::user()->is_admin())
                                    <a href="#" wire:click="setUsernames()" class="btn btn-default border-primary btn-rounded btn-wide" style="float: right"><i class="fa fa-key"></i>Set Usernames</a> 
                                    @endif
                                   
                                </div>

                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                
    
                                <table id="employeesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Emp#
                                        </th>
                                        <th class="th-sm">Name
                                        </th>
                                        <th class="th-sm">Surname
                                        </th>
                                        <th class="th-sm">Gender
                                        </th>
                                        <th class="th-sm">Email
                                        </th>
                                        <th class="th-sm">Dpt
                                        </th>
                                        <th class="th-sm">Post
                                        </th>
                                        <th class="th-sm">Account
                                        </th>
                                        @if (Auth::user()->is_admin())
                                        <th class="th-sm">Username
                                        </th> 
                                        @endif
                                      
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if ($employees->count()>0)
                                    <tbody>
                                        @foreach ($employees as $employee)
                                        @if (!$employee->driver)
                                        <tr>
                                            <td>{{ucfirst($employee->employee_number)}}</td>
                                            <td>{{ucfirst($employee->name)}}</td>
                                            <td>{{ucfirst($employee->surname)}}</td>
                                            <td>{{$employee->gender}}</td>
                                            <td>{{$employee->email}}</td>
                                            <td>@foreach ($employee->departments as $department)
                                                {{$department->name}}
                                            @endforeach</td>
                                            <td>{{$employee->post}}</td>
                                            <td>
                                                @if ($employee->user)
                                                <span class="badge bg-{{$employee->user->active == 1 ? "success" : "danger"}}">{{$employee->user->active == 1 ? "Active" : "Inactive"}}</span>
                                                @else
                                                <span class="badge bg-danger">Deleted</span>
                                                @endif
                                            </td>

                                            @if (Auth::user()->is_admin())
                                            <td>
                                                @if ($employee->user)
                                                    @if (!is_null($employee->user->username))
                                                    <span class="badge bg-success">Username set</span>
                                                    @else
                                                    <span class="badge bg-warning">Username not set</span>
                                                    @endif
                                                @endif
                                            </td>
                                            @endif
                                           
    
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="{{route('employees.show', $employee->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                        <li><a href="{{route('employees.edit', $employee->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
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


    </div>
