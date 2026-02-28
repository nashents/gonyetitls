<div>
    <div class="row mt-30">

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"> <strong><span style="color: green">Checklist:</span> {{ $service_type->name }}</strong>  </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <a href="" data-toggle="modal" data-target="#inspection_serviceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i> Add item(s) to checklist</a>
                    <br>
                    <br>
                      <div class="col-md-3" style="float: right; padding-right:0px">
                        <div class="form-group">
                            <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search items in checklist...">
                        </div>
                    </div>
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <caption>{{ $service_type->name }}  Checklist</caption>
                        <thead>
                          <tr>
                            {{-- <th class="th-sm">Category
                            </th> --}}
                            <th class="th-sm">Group
                            </th>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Action
                            </th>
                          </tr>
                        </thead>
                        @if (isset($inspection_services))
                        <tbody>
                            @forelse ($inspection_services as $inspection_service)
                          <tr>
                            {{-- <td>{{$inspection_service->category}}</td> --}}
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
                                        <li><a href="#" data-toggle="modal"  wire:click="removeShow({{$inspection_service->id}})" ><i class="fa fa-remove color-danger"></i>Remove</a></li>
                                    </ul>
                                </div>
                                @include('inspection_services.delete')
                        </td>
                          </tr>
                           @empty
                                  <tr>
                                    <td colspan="4">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Inspection Items Found ....
                                        </div>
                                    </td>
                                  </tr>  
                                    @endforelse
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to remove this Inspection Item from {{ $inspection_service?->service_type ? $inspection_service?->service_type->name : ""}} Checklist ?</strong> </center>
            </div>
            <form  wire:submit.prevent="removeItem()">
              
            <div class="modal-footer no-border">
                <div class="btn-group" role="group">
                    <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inspection_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add inspection item(s) to checklist <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Inspection Item Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_group_id.0">
                                    <option value="">Select Inspection Item Group</option>
                                    @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{ $inspection_group->id }}">{{ $inspection_group->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item Group</a></small><a href="#" wire:click.prevent="refresh('inspection_groups')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> <br> 
                                @error('inspection_group_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="title">Inspection Item(s)<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_type_id.0" required>
                                    <option value="">Select Inspection Item</option>
                                    @foreach ($inspection_types as $inspection_type)
                                        <option value="{{ $inspection_type->id }}"
                                            @if(is_array($inspection_type_id))
                                                @if(in_array($inspection_type->id, $inspection_type_id ?? []) && ($inspection_type_id[0] ?? null) != $inspection_type->id) 
                                                    disabled 
                                                @endif
                                            @endif
                                            >{{ $inspection_type->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small><a href="#" wire:click.prevent="refresh('inspection_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> <br> 
                                @error('inspection_type_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
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
                           <div class="col-md-5">
                                 <div class="form-group">
                                    <label for="title">Inspection Items<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="inspection_type_id.{{ $value }}" required>
                                        <option value="">Select Inspection Item</option>
                                        @foreach ($inspection_types as $inspection_type)
                                            <option value="{{ $inspection_type->id }}"
                                                @if(is_array($inspection_type_id))
                                                    @if(in_array($inspection_type->id, $inspection_type_id ?? []) && ($inspection_type_id[$value] ?? null) != $inspection_type->id) 
                                                                disabled 
                                                    @endif
                                                @endif
                                                >{{ $inspection_type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('inspection_type_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                             <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded btn-xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            
                        </div>
                        
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Checklist Item</button>
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
        <div class="modal-dialog mw-100 w-50" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit inspection item in checklist<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                 
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Inspection Item Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_group_id">
                                    <option value="">Select Inspection Item Group</option>
                                    @foreach ($inspection_groups as $inspection_group)
                                        <option value="{{ $inspection_group->id }}">{{ $inspection_group->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item Group</a></small><a href="#" wire:click.prevent="refresh('inspection_groups')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> <br> 
                                @error('inspection_group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="title">Inspection Item(s)<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="inspection_type_id" required>
                                    <option value="">Select Inspection Item</option>
                                    @foreach ($inspection_types as $inspection_type)
                                        <option value="{{ $inspection_type->id }}">{{ $inspection_type->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('inspection_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small><a href="#" wire:click.prevent="refresh('inspection_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> <br> 
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
