<div>
    <div class="row mt-30">
        <div>
            @include('includes.messages')
        </div>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"> <strong>{{ $checklist_category->name }} Checklist</strong>  </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <a href="" data-toggle="modal" data-target="#category_checklistModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inspection Item</a>
                    <br>
                    <br>
                    <table id="category_checklistsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th class="th-sm">Inspection Group
                            </th>
                            <th class="th-sm">Inspection Item
                            </th>
                            <th class="th-sm">Action
                            </th>
                          </tr>
                        </thead>
                        @if ($category_checklists->count()>0)
                        <tbody>
                            @foreach ($category_checklists as $category_checklist)
                          <tr>
                            <td>{{$category_checklist->checklist_sub_category ? $category_checklist->checklist_sub_category->name : ""}}</td>
                            <td>{{$category_checklist->checklist_item ? $category_checklist->checklist_item->name : ""}}</td>
                            <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"  wire:click="edit({{$category_checklist->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                        <li><a href="#" data-toggle="modal" data-target="#category_checklistDeleteModal{{ $category_checklist->id }}" ><i class="fa fa-remove color-danger"></i>Remove</a></li>
                                    </ul>
                                </div>
                                @include('category_checklists.delete')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="category_checklistModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Inspection Item(s) to Checklist <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Inspection Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_sub_category_id.0">
                                    <option value="">Select Inspection Group</option>
                                    @foreach ($checklist_sub_categories as $checklist_sub_category)
                                        <option value="{{ $checklist_sub_category->id }}">{{ $checklist_sub_category->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_sub_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Group</a></small> <br> 
                                @error('checklist_sub_category_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Inspection Item(s)</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_item_id.0">
                                    <option value="">Select Inspection Item</option>
                                    @foreach ($checklist_items as $checklist_item)
                                        <option value="{{ $checklist_item->id }}">{{ $checklist_item->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small> <br> 
                                @error('checklist_item_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Group(s)</label>
                                    <select class="form-control" wire:model.debounce.300ms="checklist_sub_category_id.{{ $value }}">
                                        <option value="">Select Group</option>
                                        @foreach ($checklist_sub_categories as $checklist_sub_category)
                                            <option value="{{ $checklist_sub_category->id }}">{{ $checklist_sub_category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('checklist_sub_category_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="title">Inspection Item(s)</label>
                                    <select class="form-control" wire:model.debounce.300ms="checklist_item_id.{{ $value }}">
                                        <option value="">Select Inspection Item</option>
                                        @foreach ($checklist_items as $checklist_item)
                                            <option value="{{ $checklist_item->id }}">{{ $checklist_item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('checklist_item_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="category_checklistEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Inspection Item On Checklist<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Inspection Group</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_sub_category_id">
                                    <option value="">Select Inspection Group</option>
                                    @foreach ($checklist_sub_categories as $checklist_sub_category)
                                        <option value="{{ $checklist_sub_category->id }}">{{ $checklist_sub_category->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_sub_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection</a></small> <br> 
                                @error('checklist_sub_category_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Inspection Item(s)</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_item_id">
                                    <option value="">Select Inspection Item</option>
                                    @foreach ($checklist_items as $checklist_item)
                                        <option value="{{ $checklist_item->id }}">{{ $checklist_item->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small> <br> 
                                @error('checklist_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
