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
                                <a href="#" data-toggle="modal" data-target="#inspection_typeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Service Checklist Item</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Service Type
                                    </th>
                                    <th class="th-sm">Inspection Group
                                    </th>
                                    <th class="th-sm">Inspection Item
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($inspection_services))
                                <tbody>
                                    @foreach ($inspection_services as $inspection_service)
                                  <tr>
                                    <td>{{$inspection_service->category}}</td>
                                    <td>{{$inspection_service->service_type ? $inspection_service->service_type->name : ""}}</td>
                                    <td>{{$inspection_service->inspection_group ? $inspection_service->inspection_group->name : ""}}</td>
                                    <td>{{$inspection_service->inspection_type ? $inspection_service->inspection_type->name : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$inspection_service->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#inspection_serviceDeleteModal{{ $inspection_service->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('inspection_services.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($inspection_services))
                                        {{ $inspection_services->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inspection_typeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Service Checklist Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category<span class="required" style="color: red">*</span></label>
                               <select class="form-control"  wire:model.debounce.300ms="category" required>
                                <option value="">Select Category</option>
                                    <option value="Horse">Horse</option>
                                    <option value="Trailer">Trailer</option>
                                    <option value="Vehicle">Vehicle</option>
                               </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Service Types<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="service_type_id" class="form-control" required>
                                   <option value="">Select Service Type</option>
                                   @foreach ($service_types as $service_type)
                                        <option value="{{$service_type->id}}">{{$service_type->name}}</option>
                                   @endforeach
                               </select>
                                @error('service_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Inspection Groups</label>
                               <select wire:model.debounce.300ms="selectedInspectionGroup" class="form-control">
                                   <option value="">Select Inspection Group</option>
                                   @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{$inspection_group->id}}">{{$inspection_group->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedInspectionGroup') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Inspection Items<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="inspection_type_id" class="form-control" required>
                                   <option value="">Select Inspection Item</option>
                                   @foreach ($inspection_types as $inspection_type)
                                        <option value="{{$inspection_type->id}}">{{$inspection_type->name}}</option>
                                   @endforeach
                               </select>
                                @error('inspection_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inspection_serviceEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Service Checklist Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category<span class="required" style="color: red">*</span></label>
                               <select class="form-control"  wire:model.debounce.300ms="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Horse">Horse</option>
                                    <option value="Trailer">Trailer</option>
                                    <option value="Vehicle">Vehicle</option>
                               </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Service Types<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="service_type_id" class="form-control" required>
                                   <option value="">Select Service Type</option>
                                   @foreach ($service_types as $service_type)
                                        <option value="{{$service_type->id}}">{{$service_type->name}}</option>
                                   @endforeach
                               </select>
                                @error('service_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Inspection Groups</label>
                               <select wire:model.debounce.300ms="selectedInspectionGroup" class="form-control" >
                                   <option value="">Select Inspection Group</option>
                                   @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{$inspection_group->id}}">{{$inspection_group->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedInspectionGroup') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Inspection Items<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="inspection_type_id" class="form-control"  required>
                                   <option value="">Select Inspection Item</option>
                                   @foreach ($inspection_types as $inspection_type)
                                        <option value="{{$inspection_type->id}}">{{$inspection_type->name}}</option>
                                   @endforeach
                               </select>
                                @error('inspection_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

