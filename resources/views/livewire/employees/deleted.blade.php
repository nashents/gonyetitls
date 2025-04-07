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
                                {{-- <div class="panel-title" style="float: right">
                                    <a href="{{route('employees.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                    <a href="{{route('employees.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                    <a href="{{route('employees.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                    <a href="" data-toggle="modal" data-target="#employeesImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a>
                                </div>
                                <div class="panel-title">
                                    <a href="{{route('employees.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Staff</a>
                                    <a href="{{route('drivers.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Driver</a>
                                    {{-- <a href="{{route('employees.export')}}"  class="btn btn-default"><i class="fa fa-download"></i>Export</a>
                                    <a href=""  class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                                {{-- </div> --}}

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
                                        <th class="th-sm">Department
                                        </th>
                                        <th class="th-sm">Post
                                        </th>
                                        <th class="th-sm">Account
                                        </th>
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
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" wire:click="restore({{$employee->id}})"><i class="fas fa-undo color-default"></i> Restore</a></li>
    
                                                    </ul>
                                                </div>
                                         
    
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


          <!-- Modal -->
          <div data-backdrop="static" data-keyboard="false" class="modal fade" id="employeeRestoreModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to restore this Employee?</strong> </center>
                    </div>
                    <form wire:submit.prevent="update()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

    </div>
