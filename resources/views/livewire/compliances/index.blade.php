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
                                <a href="#" data-toggle="modal" data-target="#complianceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Compliance</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="compliancesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Route
                                    </th>
                                    <th class="th-sm">Driver - Route Compliance 
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($compliances->count()>0)
                                <tbody>
                                    @foreach ($compliances as $compliance)
                                  <tr>
                                    <td>{{$compliance->user ? $compliance->user->name : ""}} {{$compliance->user ? $compliance->user->surname : ""}}</td>
                                    <td>{{$compliance->customer ? $compliance->customer->name : ""}}</td>
                                    <td>{{$compliance->driver->employee ? $compliance->driver->employee->name : ""}} {{$compliance->driver->employee ? $compliance->driver->employee->surname : ""}}</td>
                                    <td>{{$compliance->route ? $compliance->route->name : ""}}</td>
                                    <td>{{$compliance->compliant}}</td>
                                    <td>{{$compliance->comments}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$compliance->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#complianceDeleteModal{{ $compliance->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('compliances.delete')
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

  
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="complianceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Compliance <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Customers<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="customer_id" class="form-control" required >
                                   <option value="">Select Customer</option>
                                   @foreach ($customers as $customer)
                                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                                   @endforeach  
                               </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Drivers<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="driver_id" class="form-control" required >
                                   <option value="">Select Driver</option>
                                   @foreach ($drivers as $driver)
                                        <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                   @endforeach  
                               </select>
                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Routes<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="route_id" class="form-control" required >
                               <option value="">Select Roure</option>
                               @foreach ($routes as $route)
                                    <option value="{{$route->id}}">{{$route->name}}</option>
                               @endforeach  
                           </select>
                            @error('route_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Is Driver Route Compliant?<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="compliant" class="form-control" required>
                               <option value="">Select Option</option>
                               <option value="Compliant">Compliant</option>
                               <option value="Non Compliant">Non Compliant</option>
                           </select>
                            @error('compliant') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                  

                <div class="form-group">
                    <label for="country">Comment</label>
                  <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="5"></textarea>
                    @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="complianceEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Customers<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="customer_id" class="form-control" required >
                                   <option value="">Select Customer</option>
                                   @foreach ($customers as $customer)
                                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                                   @endforeach  
                               </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Drivers<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="driver_id" class="form-control" required >
                                   <option value="">Select Driver</option>
                                   @foreach ($drivers as $driver)
                                        <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                   @endforeach  
                               </select>
                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Routes<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="route_id" class="form-control" required >
                               <option value="">Select Roure</option>
                               @foreach ($routes as $route)
                                    <option value="{{$route->id}}">{{$route->name}}</option>
                               @endforeach  
                           </select>
                            @error('route_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Is Driver Route Compliant?<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="compliant" class="form-control" required>
                               <option value="">Select Option</option>
                               <option value="Compliant">Compliant</option>
                               <option value="Non Compliant">Non Compliant</option>
                           </select>
                            @error('compliant') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                  

                <div class="form-group">
                    <label for="country">Comment</label>
                  <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="5"></textarea>
                    @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

