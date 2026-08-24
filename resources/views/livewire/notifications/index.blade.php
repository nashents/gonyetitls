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
                                <a href="#" data-toggle="modal" data-target="#notificationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Notification</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="notificationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">When
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Notification To
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($notifications->count()>0)
                                <tbody>
                                    @foreach ($notifications as $notification)
                                  <tr>
                                    <td>{{$notification->user ? $notification->user->name : ""}} {{$notification->user ? $notification->user->surname : ""}}</td>
                                    <td>{{$notification->when}}</td>
                                    <td>{{$notification->category}}</td>
                                    <td>
                                        @if ($notification->employee)
                                            {{$notification->employee ? $notification->employee->name : ""}} {{$notification->employee ? $notification->employee->surname : ""}} {{$notification->employee ? $notification->employee->email : ""}}
                                        @else
                                            {{$notification->email}}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$notification->status == 1 ? "success" : "danger"}}">{{$notification->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$notification->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#notificationDeleteModal{{ $notification->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('notifications.delete')
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

            @if ($isSuperAdmin)
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <a href="#" data-toggle="modal" data-target="#tripEditAuthorizerModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trip Edit Authorizer</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <table id="tripEditAuthorizersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">User</th>
                                    <th class="th-sm">Assigned By</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if ($trip_edit_authorizers->count() > 0)
                                <tbody>
                                    @foreach ($trip_edit_authorizers as $authorizer)
                                  <tr>
                                    <td>{{$authorizer->user ? $authorizer->user->name : ""}} {{$authorizer->user ? $authorizer->user->surname : ""}}</td>
                                    <td>{{$authorizer->creator ? $authorizer->creator->name : ""}} {{$authorizer->creator ? $authorizer->creator->surname : ""}}</td>
                                    <td><span class="badge bg-{{$authorizer->status == 1 ? "success" : "danger"}}">{{$authorizer->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="editTripEditAuthorizer({{$authorizer->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tripEditAuthorizerDeleteModal{{$authorizer->id}}"><i class="fa fa-trash color-danger"></i> Remove</a></li>
                                            </ul>
                                        </div>
                                        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="tripEditAuthorizerDeleteModal{{$authorizer->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content bg-danger">
                                                    <div class="modal-body">
                                                        <center><strong>Remove this Trip Edit Authorizer?</strong></center>
                                                    </div>
                                                    <div class="modal-footer no-border">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                                            <button type="button" class="btn bg-black btn-wide btn-rounded" data-dismiss="modal" wire:click="deleteTripEditAuthorizer({{$authorizer->id}})"><i class="fa fa-trash"></i>Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
        <!-- /.container-fluid -->
    </section>

  
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Notification User <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        @if (isset($email))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees</label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" disabled>
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} < {{$employee->email}} ></option>
                                   @endforeach
                                  
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} < {{$employee->email}} ></option>
                                   @endforeach
                                  
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        @if (isset($employee_id))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Email</label>
                              <input type="text" wire:model.debounce.300ms="email" class="form-control" disabled>
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Email<span class="required" style="color: red">*</span></label>
                                <input type="text" wire:model.debounce.300ms="email" class="form-control" required>
                                    @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                        
                    </div>
                  
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">When<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="when" class="form-control" required >
                                   <option value="">Select Option</option>
                                   <option value="Before">Before</option>
                                   <option value="After">After</option>
                               </select>
                                @error('when') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                               <label for="country">Notification Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="category" class="form-control" required >
                                   <option value="">Select Category</option>
                                   <option value="Bill Authorization">Bill Authorization</option>
                                   <option value="Credit Note Authorization">Credit Note Authorization</option>
                                   <option value="Dispatch Authorization">Dispatch Authorization</option>
                                   <option value="Fuel Request Authorization">Fuel Request Authorization</option> 
                                   <option value="Fuel Order Authorization">Fuel Order Authorization</option> 
                                   <option value="Fuel Top Up Authorization">Fuel Top Up Authorization</option> 
                                   <option value="Garage Booking Authorization">Garage Booking Authorization</option>
                                   <option value="Invoice Authorization">Invoice Authorization</option>
                                   <option value="Purchase Order Authorization">Purchase Order Authorization</option>
                                   <option value="Requisition Authorization">Requisition Authorization</option>
                                   <option value="Trip Authorization">Trip Authorization</option>
                               </select>
                               @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Status</label>
                               <select wire:model.debounce.300ms="status" class="form-control" >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="notificationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Notification <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        @if (isset($email))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees</label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" disabled>
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} < {{$employee->email}} ></option>
                                   @endforeach
                                  
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} < {{$employee->email}} ></option>
                                   @endforeach
                                  
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        @if (isset($employee_id))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Email</label>
                              <input type="email" wire:model.debounce.300ms="email" class="form-control" disabled>
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Email<span class="required" style="color: red">*</span></label>
                                <input type="email" wire:model.debounce.300ms="email" class="form-control" required>
                                    @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                  
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">When<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="when" class="form-control" required >
                                   <option value="">Select Option</option>
                                   <option value="Before">Before</option>
                                   <option value="After">After</option>
                               </select>
                                @error('when') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="country">Notification Categories<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="category" class="form-control" required >
                                    <option value="">Select Category</option>
                                    <option value="Bill Authorization">Bill Authorization</option>
                                    <option value="Credit Note Authorization">Credit Note Authorization</option>
                                    <option value="Dispatch Authorization">Dispatch Authorization</option>
                                    <option value="Fuel Request Authorization">Fuel Request Authorization</option> 
                                    <option value="Fuel Order Authorization">Fuel Order Authorization</option> 
                                    <option value="Fuel Top Up Authorization">Fuel Top Up Authorization</option> 
                                    <option value="Garage Booking Authorization">Garage Booking Authorization</option>
                                    <option value="Invoice Authorization">Invoice Authorization</option>
                                    <option value="Purchase Order Authorization">Purchase Order Authorization</option>
                                    <option value="Requisition Authorization">Requisition Authorization</option>
                                    <option value="Trip Authorization">Trip Authorization</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Status</label>
                               <select wire:model.debounce.300ms="status" class="form-control" >
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
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    @if ($isSuperAdmin)
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripEditAuthorizerModal" tabindex="-1" role="dialog" aria-labelledby="tripEditAuthorizerModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="tripEditAuthorizerModalLabel"><i class="fas fa-plus"></i> Add Trip Edit Authorizer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="storeTripEditAuthorizer()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="trip_edit_authorizer_user_id">User<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="trip_edit_authorizer_user_id" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach ($trip_edit_authorizer_users as $authorizer_user)
                                    <option value="{{$authorizer_user->id}}">{{$authorizer_user->name}} {{$authorizer_user->surname}}</option>
                                @endforeach
                            </select>
                            @error('trip_edit_authorizer_user_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="trip_edit_authorizer_status">Status</label>
                            <select wire:model.debounce.300ms="trip_edit_authorizer_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('trip_edit_authorizer_status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripEditAuthorizerEditModal" tabindex="-1" role="dialog" aria-labelledby="tripEditAuthorizerEditModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="tripEditAuthorizerEditModalLabel"><i class="fas fa-edit"></i> Edit Trip Edit Authorizer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="updateTripEditAuthorizer()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="trip_edit_authorizer_user_id">User<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="trip_edit_authorizer_user_id" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach ($trip_edit_authorizer_users as $authorizer_user)
                                    <option value="{{$authorizer_user->id}}">{{$authorizer_user->name}} {{$authorizer_user->surname}}</option>
                                @endforeach
                            </select>
                            @error('trip_edit_authorizer_user_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="trip_edit_authorizer_status">Status</label>
                            <select wire:model.debounce.300ms="trip_edit_authorizer_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('trip_edit_authorizer_status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>

