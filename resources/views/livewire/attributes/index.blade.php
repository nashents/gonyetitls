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
                                <a href="" data-toggle="modal" data-target="#attributeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Attribute</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search attributes...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Attribute
                                    </th>
                                    <th class="th-sm">Att Value
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($attributes))
                                <tbody>
                                    @forelse ($attributes as $attribute)
                                            @forelse ($attribute->attribute_values as $value)
                                                <tr>
                                                    <td>{{ucfirst($attribute->name)}}</td>
                                                    <td>{{ucfirst($value->name)}}</td>
                                                    <td><span class="badge bg-{{$attribute->status == 1 ? "success" : "danger"}}">{{$attribute->status == 1 ? "Active" : "Inactive"}}</span></td>
                                                    <td class="w-10 line-height-35 table-dropdown">
                                                        <div class="dropdown">
                                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa fa-bars"></i>
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#"  wire:click="showValue({{$attribute->id}})" ><i class="fa fa-plus color-default"></i>Add Value</a></li>
                                                                <li><a href="#"  wire:click="editValue({{$value->id}})" ><i class="fa fa-edit color-success"></i>Edit Value</a></li>
                                                                <li><a href="#"  wire:click="edit({{$attribute->id}})" ><i class="fa fa-edit color-success"></i> Edit Attribute</a></li>
                                                                <li><a href="#" data-toggle="modal" data-target="#attributeDeleteModal{{ $attribute->id }}" ><i class="fa fa-trash color-danger"></i>Delete Attribute</a></li>
                                                                <li><a href="#" data-toggle="modal" data-target="#attribute_valueDeleteModal{{ $value->id }}" ><i class="fa fa-trash color-danger"></i>Delete Value</a></li>
                                                            </ul>
                                                        </div>
                                                        @include('attributes.delete')
                                                        @include('attributes.delete_value')
                                                </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                  <td colspan="4">
                                                      <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                          No Attribute Values Found ....
                                                      </div>
                                                     
                                                  </td>
                                                </tr>  
                                                  @endforelse
                                        @empty
                                        <tr>
                                          <td colspan="4">
                                              <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                  No Attributes Found ....
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
                                    @if (isset($attributes))
                                        {{ $attributes->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attributeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Attribute  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Categories<span class="required" style="color: red">*</span></label>
                             <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                             </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Sub Categories</label>
                             <select wire:model.debounce.300ms="category_value_id" class="form-control">
                                <option value="">Select Sub Category</option>
                                @if (!is_null($selectedCategory))
                                @foreach ($category_values as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                                @endif
                             </select>
                                @error('category_value_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="name">Attribute<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="attribute" placeholder="Enter attribute name" required>
                        @error('attribute') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Value(s)<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="value.0" placeholder="Enter attribute value" required >
                                @error('value.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="name">Value<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="value.{{$value}}" placeholder="Enter attribute value" required>
                                @error('value.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Value</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attributeValueModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Value <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeValue()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Attributes<span class="required" style="color: red">*</span></label>
                     <select wire:model.debounce.300ms="attribute_id" class="form-control" aria-placeholder="Enter attribute name" required>
                        <option value="">Select Attribute</option>
                        @foreach ($attributes as $attribute)
                        <option value="{{$attribute->id}}">{{$attribute->name}}</option>
                        @endforeach
                     </select>
                        @error('attribute_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Value<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="value" placeholder="Enter attribute value" required>
                        @error('value') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attributeValueEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Value <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateValue()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Attributes<span class="required" style="color: red">*</span></label>
                     <select wire:model.debounce.300ms="attribute_id" class="form-control" required >
                        <option value="">Select Attribute</option>
                        @foreach ($attributes as $attribute)
                        <option value="{{$attribute->id}}">{{$attribute->name}}</option>
                        @endforeach
                     </select>
                        @error('attribute_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Value<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="value"  required>
                        @error('value') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attributeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Attribute <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Categories<span class="required" style="color: red">*</span></label>
                             <select wire:model.debounce.300ms="selectedCategory" class="form-control" required >
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                             </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Sub Categories</label>
                             <select wire:model.debounce.300ms="category_value_id" class="form-control" >
                                <option value="">Select Sub Category</option>
                                @if (!is_null($selectedCategory))
                                @foreach ($category_values as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                                @endif
                             </select>
                                @error('category_value_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                   
                    <div class="form-group">
                        <label for="name">Attribute<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="attribute" placeholder="Enter Name" required>
                        @error('attribute') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Status<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">InActive</option>
                       </select>
                        @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

