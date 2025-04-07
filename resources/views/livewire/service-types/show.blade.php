<div>
    <div class="row mt-30">
        <div>
            @include('includes.messages')
        </div>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"> <strong>{{ $service_type->name }} Checklist</strong>  </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <a href="" data-toggle="modal" data-target="#inspection_serviceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inspection Item</a>
                    <br>
                    <br>
                    <table id="inspection_servicesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <caption>{{ $service_type->name }}  Checklist</caption>
                        <thead>
                          <tr>
                            <th class="th-sm">Category
                            </th>
                            <th class="th-sm">Inspection Group
                            </th>
                            <th class="th-sm">Inspection Item
                            </th>
                            <th class="th-sm">Action
                            </th>
                          </tr>
                        </thead>
                        @if ($inspection_services->count()>0)
                        <tbody>
                            @foreach ($inspection_services as $inspection_service)
                          <tr>
                            <td>{{$inspection_service->category}}</td>
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
                                        <li><a href="#" data-toggle="modal" data-target="#inspection_serviceDeleteModal{{ $inspection_service->id }}" ><i class="fa fa-remove color-danger"></i>Remove</a></li>
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
                      
                </div>
              
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
           
        </div>
        <!-- /.col-md-9 -->
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inspection_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Inspection Item(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Category<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="category.0" required>
                                    <option value="">Select Category</option>
                                    <option value="Horse">Horse</option>
                                    <option value="Trailer">Trailer</option>
                                    <option value="Vehicle">Vehicle</option>
                                </select>
                                @error('category.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Inspection Item Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_group_id.0">
                                    <option value="">Select Inspection Item Group</option>
                                    @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{ $inspection_group->id }}">{{ $inspection_group->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item Group</a></small> <br> 
                                @error('inspection_group_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    <div class="form-group">
                        <label for="title">Inspection Item(s)<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="inspection_type_id.0" required>
                            <option value="">Select Inspection Item</option>
                            @foreach ($inspection_types as $inspection_type)
                                <option value="{{ $inspection_type->id }}">{{ $inspection_type->name }}</option>
                            @endforeach
                        </select>
                        <small>  <a href="{{ route('inspection_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small> <br> 
                        @error('inspection_type_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Category<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="category.{{ $value }}" required>
                                        <option value="">Select Category</option>
                                        <option value="Horse">Horse</option>
                                        <option value="Trailer">Trailer</option>
                                        <option value="Vehicle">Vehicle</option>
                                    </select>
                                    @error('category.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Inspection Item Groups</label>
                                    <select class="form-control" wire:model.debounce.300ms="inspection_group_id.{{ $value }}">
                                        <option value="">Select Inspection Item Group</option>
                                        @foreach ($inspection_groups as $inspection_group)
                                            <option value="{{ $inspection_group->id }}">{{ $inspection_group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('inspection_group_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="title">Inspection Items<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="inspection_type_id.{{ $value }}" required>
                                        <option value="">Select Inspection Item</option>
                                        @foreach ($inspection_types as $inspection_type)
                                            <option value="{{ $inspection_type->id }}">{{ $inspection_type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('inspection_type_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Checklist Item</button>
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
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Inspection Item<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Category<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="category" required>
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
                                <label for="title">Inspection Item Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_group_id">
                                    <option value="">Select Inspection Item Group</option>
                                    @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{ $inspection_group->id }}">{{ $inspection_group->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Service Item Group</a></small> <br> 
                                @error('inspection_group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="title">Inspection Items<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="inspection_type_id" required>
                            <option value="">Select Inspection Item</option>
                            @foreach ($inspection_types as $inspection_type)
                                <option value="{{ $inspection_type->id }}">{{ $inspection_type->name }}</option>
                            @endforeach
                        </select>
                        <small>  <a href="{{ route('inspection_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small> <br> 
                        @error('inspection_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
