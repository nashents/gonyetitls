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
                                <a href="" data-toggle="modal" data-target="#departmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Department</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="departmentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Dpt Code
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if ($departments->count()>0)
                                <tbody>
                                    @foreach ($departments as $department)
                                  <tr>
                                    <td>{{$department->name}}</td>
                                    <td>{{$department->department_code}}</td>
                                    <td>{{$department->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($department->name == 'Information Technology' || $department->name == 'Human Resources' || $department->name == 'Finance' || $department->name == 'Transport & Logistics' || $department->name == 'Stores' || $department->name == 'Workshop' || $department->name == 'Security' || $department->name == 'HSEQ')
                                                @else    
                                                <li><a href="#" data-toggle="modal" data-target="#departmentEditModal" wire:click.prevent="edit({{$department->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#departmentDeleteModal{{$department->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                               
                                            </ul>
                                        </div>
                                        @include('departments.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="departmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Department <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                  <div class="form-group">
                      <label for="name">Name<span class="required" style="color: red">*</span></label>
                      <input type="text" wire:model.debounce.300ms="name" class="form-control"  required>
                      @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                      <label for="code">Department Code</label>
                      <input type="text" wire:model.debounce.300ms="department_code" class="form-control" >
                      @error('department_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                      <label for="description">Description</label>
                      <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5"></textarea>
                      @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="store()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Create</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="departmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Department <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                  <div class="form-group">
                      <label for="name">Name<span class="required" style="color: red">*</span></label>
                      <input type="text" wire:model.debounce.300ms="name" class="form-control" required >
                      @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                      <label for="code">Department Code</label>
                      <input type="text" wire:model.debounce.300ms="department_code" class="form-control" >
                      @error('department_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                      <label for="description">Description</label>
                      <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5"></textarea>
                      @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="update()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
