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
                                <a href="" data-toggle="modal" data-target="#asset_assignmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Assignment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="asset_assignmentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Asset#
                                    </th>
                                    <th class="th-sm">Asset
                                    </th>
                                    <th class="th-sm">Assigned To
                                    </th>
                                    <th class="th-sm">Assignment Date
                                    </th>
                                    <th class="th-sm">UnAssignment Date
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($asset_assignments->count()>0)
                                <tbody>
                                    @foreach ($asset_assignments as $assignment)
                                  <tr>
                                    <td>{{$assignment->asset ? $assignment->asset->asset_number : ""}}</td>       
                                    <td>
                                        @if (isset($assignment->asset->product))
                                        {{$assignment->asset->product->brand ? $assignment->asset->product->brand->name : ""}} {{$assignment->asset->product ? $assignment->asset->product->name : ""}} {{$assignment->asset->product ? $assignment->asset->product->model : ""}} {{$assignment->asset->product ? $assignment->asset->serial_number : ""}}
                                        @endif
                                    </td>
                                    <td>{{ucfirst($assignment->employee ? $assignment->employee->name : "")}} {{ucfirst($assignment->employee ? $assignment->employee->surname : "")}}{{ucfirst($assignment->department ? ", ".$assignment->department->name." (Dpt)" : "")}}{{ucfirst($assignment->branch ? ", ".$assignment->branch->name." (Branch)" : "")}}</td>
                                    <td>{{$assignment->start_date}}</td>
                                    <td>{{$assignment->end_date}}</td>
                                    <td><span class="label label-{{$assignment->status == 1 ? "success" : "danger"}} label-rounded">{{$assignment->status == 1 ? "Assigned" : "Unassigned"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('asset_assignments.show',$assignment->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($assignment->status == 1)
                                                <li><a href="#"  wire:click="unAssignment({{$assignment->id}})" ><i class="fa fa-undo color-success"></i> Unassign</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#asset_assignmentDeleteModal{{ $assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('asset_assignments.delete')
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

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="asset_assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Asset Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedAsset" class="form-control" required>
                                   <option value="" selected>Select Asset</option>
                                    @foreach ($assets as $asset)
                                    <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name: ""}} {{$asset->product ? $asset->product->model: ""}} SN#: {{$asset->serial_number}}</option>
                                    @endforeach
                               </select>
                                @error('selectedAsset') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="purchase_date">Asset Contents<span class="required" style="color: red">*</span></label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Required Quantities" required>
                                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>
                    </div>
                   
                    <h5 class="underline mt-30">Dispatch and assign it to:</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Employees</label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control">
                                    <option value="" selected>Select Employee</option>
                                     @foreach ($employees as $employee)
                                         <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                     @endforeach
                                </select>
                                 @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="horse">Departments</label>
                               <select wire:model.debounce.300ms="department_id" class="form-control">
                                   <option value="" selected>Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                               </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="horse">Branches</label>
                               <select wire:model.debounce.300ms="branch_id" class="form-control" >
                                   <option value="" selected>Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{$branch->id}}">{{$branch->name}}</option>
                                    @endforeach
                               </select>
                                @error('branch_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date"  placeholder="Assignment Date" required>
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="specifications">Description</label>
                                <textarea wire:model.debounce.300ms="specifications" class="form-control" cols="30" rows="5" placeholder="Write comments..."></textarea>
                                @error('specifications') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="asset_unassignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Asset UnAssignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="unAssign()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Date<span class="required" style="color: red">*</span></label>
                        <input type="date" class="form-control" wire:model.debounce.300ms="end_date"  placeholder="UnAssignment Date" required>
                        @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="asset_assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Asset Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedAsset" class="form-control" required>
                                       <option value="" selected>Select Asset</option>
                                        @foreach ($assets as $asset)
                                        <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name: ""}} {{$asset->product ? $asset->product->model: ""}} SN#: {{$asset->serial_number}}</option>
                                        @endforeach
                                   </select>
                                    @error('selectedAsset') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="purchase_date">Asset Contents<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Required Quantities" required>
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                        </div>
                       
                        <h5 class="underline mt-30">Dispatch and assign it to:</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Employees</label>
                                    <select wire:model.debounce.300ms="employee_id" class="form-control">
                                        <option value="" selected>Select Employee</option>
                                         @foreach ($employees as $employee)
                                             <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                         @endforeach
                                    </select>
                                     @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="horse">Departments</label>
                                   <select wire:model.debounce.300ms="department_id" class="form-control">
                                       <option value="" selected>Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                   </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="horse">Branches</label>
                                   <select wire:model.debounce.300ms="branch_id" class="form-control" >
                                       <option value="" selected>Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{$branch->id}}">{{$branch->name}}</option>
                                        @endforeach
                                   </select>
                                    @error('branch_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="start_date"  placeholder="Assignment Date" required>
                                    @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="specifications">Description</label>
                                    <textarea wire:model.debounce.300ms="specifications" class="form-control" cols="30" rows="5" placeholder="Write comments..."></textarea>
                                    @error('specifications') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
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

