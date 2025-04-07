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
                                <a href="" data-toggle="modal" data-target="#truck_stopModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Truck Stop</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="truck_stopsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Route
                                    </th>
                                    <th class="th-sm">Rating
                                    </th>
                                    <th class="th-sm">Assessment Expires
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($truck_stops->count()>0)
                                <tbody>
                                    @foreach ($truck_stops as $truck_stop)
                                  <tr>
                                    <td>{{ucfirst($truck_stop->name)}}</td>
                                    <td>{{ucfirst($truck_stop->route ? $truck_stop->route->name : "")}}</td>
                                    <td>{{$truck_stop->rating}}</td>
                                    <td>
                                        @if ($truck_stop->expiry_date >= now()->toDateTimeString())
                                        <span class="badge bg-success">{{$truck_stop->expiry_date}}</span>
                                        @else
                                        <span class="badge bg-danger">{{$truck_stop->expiry_date}}</span>        
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$truck_stop->status == 1 ? "success" : "danger"}}">{{$truck_stop->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('truck_stops.show',$truck_stop->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$truck_stop->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#truck_stopDeleteModal{{ $truck_stop->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('truck_stops.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="truck_stopModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Truck Stop <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Routes<span class="required" style="color: red">*</span></label>
                       <select class="form-control" wire:model.debounce.300ms="route_id" required>
                        <option value="">Select Route</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                       </select>
                        @error('route_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name.0" placeholder="Enter Truck Stop" required />
                                @error('name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Rating<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="rating.0">
                                <option value="">Select Rating</option>
                                <option value="Quick Stop">Quick Stop</option>
                                <option value="Short Park">Short Park</option>
                                <option value="Overnight Stop">Overnight Stop</option>
                               </select>
                                @error('rating.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                      
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date.0"  />
                                @error('expiry_date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description.0" cols="30" rows="3"></textarea>
                                @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name.{{ $value }}" placeholder="Enter Truck Stop" required />
                                    @error('name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Rating<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="rating.{{ $value }}">
                                    <option value="">Select Rating</option>
                                    <option value="Quick Stop">Quick Stop</option>
                                    <option value="Short Park">Short Park</option>
                                    <option value="Overnight Stop">Overnight Stop</option>
                                   </select>
                                    @error('rating.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                          
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="name">Assessment Expires</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date.{{ $value }}"  />
                                    @error('expiry_date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="name">Description</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="description.{{ $value }}" cols="30" rows="3"></textarea>
                                    @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="remove({{$key}})" style="margin-top: 24px;"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                           
                           
                        @endforeach
                    
                   
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})" > <i class="fa fa-plus"></i>Truck Stop</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="truck_stopEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Truck Stop <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Routes<span class="required" style="color: red">*</span></label>
                       <select class="form-control" wire:model.debounce.300ms="route_id" required>
                        <option value="">Select Route</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                       </select>
                        @error('route_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Truck Stop" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Rating<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="rating">
                                <option value="">Select Rating</option>
                                <option value="Quick Stop">Quick Stop</option>
                                <option value="Short Park">Short Park</option>
                                <option value="Overnight Stop">Overnight Stop</option>
                               </select>
                                @error('rating') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                      
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date"  />
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Statuses<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="status" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Description</label>
                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

