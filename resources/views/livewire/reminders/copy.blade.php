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
                                <a href="" data-toggle="modal" data-target="#reminderCopyModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Copy</a>
            
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search reminder copies...">
                                </div>
                            </div>
                            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <th class="th-sm">Fullname
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Phonenumber
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($copies as $copy)
                                  <tr>
                                    <td>{{ $copy->name }} {{ $copy->surname }}</td>
                                    <td>{{ $copy->email }}</td>
                                    <td>{{ $copy->phonenumber }}</td>
                                   <td><span class="badge bg-{{$copy->status == 1 ? "success" : "danger"}}">{{$copy->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$copy->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click="delete({{$copy->id}})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                           
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Reminders Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($reminders))
                                        {{ $reminders->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="reminderCopyModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Copy User <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Employees<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required>
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }} <{{ $employee->email }}></option>
                                        @endforeach
                                    </select>
                                    @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Email<span class="required" style="color: red">*</span></label>
                                    <input type="email" class="form-control"  wire:model.debounce.300ms="email" placeholder="Email" {{isset($selectedEmployee) ? "disabled" : ""}} required>
                                    @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Name" required>
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Surname<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="surname" placeholder="Surname" required>
                                    @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Phonenumber<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="phonenumber" placeholder="Phonenumber" required>
                                    @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fitnessEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Reminder <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                          <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Employees<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required>
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }} <{{ $employee->email }}></option>
                                        @endforeach
                                    </select>
                                    @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Email<span class="required" style="color: red">*</span></label>
                                    <input type="email" class="form-control"  wire:model.debounce.300ms="email" placeholder="Email" required>
                                    @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Name" required>
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Surname<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="surname" placeholder="Surname" required>
                                    @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Phonenumber<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="phonenumber" placeholder="Phonenumber" required>
                                    @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>



</div>

