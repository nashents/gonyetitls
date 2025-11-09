<div>
    <a href="" data-toggle="modal" data-target="#category_checklistModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i> Add item(s) on board</a>
        <br>
        <br>
            <div class="col-md-3" style="float: right; padding-right:0px">
            <div class="form-group">
                <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search items in checklist...">
            </div>
        </div>
        <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <tr>
                    <th class="th-sm">Group
                    </th>
                    <th class="th-sm">Item
                    </th>
                    <th class="th-sm">Condition
                    </th>
                    <th class="th-sm">Actions
                    </th>
                </tr>
            </thead>
            <tbody>      
                @forelse ($category_checklists as $category_checklist)
                    <tr>
                        <td>{{$category_checklist->checklist_sub_category ? $category_checklist->checklist_sub_category->name : ""}}</td>
                        <td>{{$category_checklist->checklist_item ? $category_checklist->checklist_item->name : ""}}</td>
                        <td>{{$category_checklist->condition}}</td>
                         <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"  wire:click="edit({{$category_checklist->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                        <li><a href="#" wire:click="showDelete({{$category_checklist->id}})" ><i class="fa fa-remove color-danger"></i>Remove</a></li>
                                    </ul>
                                </div>
                              
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                No stock on board recorded ....
                            </div>
                        </td>
                    </tr> 
                @endforelse
                  
            </tbody>
        </table>
        <nav class="text-center" style="float: right">
            <ul class="pagination rounded-corners">
                @if (isset($category_checklists))
                    @if ($category_checklists->count()>0)
                        {{ $category_checklists->links() }} 
                    @endif
                @endif 
            </ul>
        </nav> 
    
<div data-backdrop="static" data-keyboard="false" class="modal fade" id="category_checklistDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to remove this item on board?</strong> </center> 
            </div>
            <form wire:submit.prevent="delete()" >
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
              
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="category_checklistModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Item(s) on board <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()"> 
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_sub_category_id.0">
                                    <option value="">Select Group</option>
                                    @foreach ($checklist_sub_categories as $checklist_sub_category)
                                        <option value="{{ $checklist_sub_category->id }}">{{ $checklist_sub_category->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_sub_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Group</a></small><a href="#" wire:click.prevent="refresh('checklist_sub_categories')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('checklist_sub_category_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="title">Item(s)<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_item_id.0" required>
                                    <option value="">Select Item</option>
                                    @foreach ($checklist_items as $checklist_item)
                                        <option value="{{ $checklist_item->id }}"
                                          
                                            >{{ $checklist_item->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small><a href="#" wire:click.prevent="refresh('checklist_items')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('checklist_item_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="condition.0" cols="30" rows="2" placeholder="Item Description / Condition"></textarea>
                                @error('condition.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-2">
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
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="title">Item(s)<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="checklist_item_id.{{ $value }}" required>
                                        <option value="">Select Item</option>
                                        @foreach ($checklist_items as $checklist_item)
                                            <option value="{{ $checklist_item->id }}"
                                               
                                                >{{ $checklist_item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('checklist_item_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Description</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="condition.{{ $value }}" cols="30" rows="2" placeholder="Item Description / Condition"></textarea>
                                    @error('condition.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1" style="margin-top:37px;">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded btn-xs"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
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
            
      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="category_checklistEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit item on board<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_sub_category_id">
                                    <option value="">Select Group</option>
                                    @foreach ($checklist_sub_categories as $checklist_sub_category)
                                        <option value="{{ $checklist_sub_category->id }}">{{ $checklist_sub_category->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_sub_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection</a></small> <a href="#" wire:click.prevent="refresh('checklist_sub_categories')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('checklist_sub_category_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Item(s)<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="checklist_item_id" required>
                                    <option value="">Select Item</option>
                                    @foreach ($checklist_items as $checklist_item)
                                        <option value="{{ $checklist_item->id }}">{{ $checklist_item->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('checklist_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Inspection Item</a></small><a href="#" wire:click.prevent="refresh('checklist_items')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('checklist_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="condition" cols="30" rows="2" placeholder="Item Description / Condition"></textarea>
                                @error('condition') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
