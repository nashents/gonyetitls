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
                                <a href="" data-toggle="modal" data-target="#department_headModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>HOD</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="department_headsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Department
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if ($department_heads->count()>0)
                                <tbody>
                                    @foreach ($department_heads as $department_head)
                                  <tr>
                                    <td>{{$department_head->department ? $department_head->department->name : ""}}</td>
                                    <td>{{$department_head->employee ? $department_head->employee->name : ""}} {{$department_head->employee ? $department_head->employee->surname : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="edit({{$department_head->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#department_headDeleteModal{{$department_head->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('department_heads.delete')
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
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="department_headModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Head Of Department <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                  <div class="form-group">
                      <label for="employee_id">Employees<span class="required" style="color: red">*</span></label>
                     <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                            <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }} ">{{ $employee->name }} {{ $employee->surname }}</option>
                                @endforeach
                     </select>
                      @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                      <label for="department_id">Departments<span class="required" style="color: red">*</span></label>
                     <select class="form-control" wire:model.debounce.300ms="department_id" required>
                            <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }} ">{{ $department->name }}</option>
                                @endforeach
                     </select>
                      @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                 
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Create</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="department_headEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Head Of Department <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                 <div class="form-group">
                    <label for="employee_id">Employees<span class="required" style="color: red">*</span></label>
                   <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                          <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }} ">{{ $employee->name }} {{ $employee->surname }}</option>
                        @endforeach
                   </select>
                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="department_id">Departments<span class="required" style="color: red">*</span></label>
                   <select class="form-control" wire:model.debounce.300ms="department_id" required>
                          <option value="">Select Department</option>
                              @foreach ($departments as $department)
                                  <option value="{{ $department->id }} ">{{ $department->name }}</option>
                              @endforeach
                   </select>
                    @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
