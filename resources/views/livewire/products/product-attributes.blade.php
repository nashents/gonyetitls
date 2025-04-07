<div>
    <a href="" data-toggle="modal" data-target="#attributeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Attribute</a>
    <br>
    <br>
    <table id="attributesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >

            <th class="th-sm">Attribute
            </th>
            <th class="th-sm">Value
            </th>
            <th class="th-sm">Actions
            </th>
          </tr>
        </thead>

        <tbody>
            @foreach ($product_attributes as $product_attribute)
        
          <tr>
            <td>{{$product_attribute->attribute ? $product_attribute->attribute->name : ""}}</td>
            <td>{{$product_attribute->attribute_value ? $product_attribute->attribute_value->name : ""}}</td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        @if ($product_attribute->attribute)
                        <li><a href="#" wire:click.prevent="showRemoveAttribute({{$product_attribute->attribute->id}})"><i class="fa fa-trash color-danger"></i>Remove Attribute</a></li>
                        @endif
                        @if ($product_attribute->attribute_value)
                        <li><a href="#" wire:click.prevent="showRemoveAttributeValue({{$product_attribute->attribute_value->id}})"><i class="fa fa-trash color-danger"></i>Remove Value</a></li>
                        @endif
                      
                    </ul>
                </div>
        </td>
          </tr>
         
          @endforeach

        </tbody>


      </table>

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attributeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="deparment">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Attribute(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addAttributes()" >
                <div class="modal-body"> 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Category<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                    <option value="">Select Attribute</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Sub Category</label>
                                <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                                    <option value="">Select Sub Category</option>
                                       @if (!is_null($selectedCategory))
                                       @foreach ($category_values as $category_value)
                                            <option value="{{ $category_value->id }}">{{ $category_value->name }}</option>
                                       @endforeach
                                       @endif  
                                </select>
                                @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                   <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Attributes<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAttribute" class="form-control" required>
                                    <option value="">Select Attribute</option>
                                    @if (!is_null($selectedCategoryValue))
                                        @foreach ($attributes as $attribute)
                                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                        @endforeach
                                    @endif   
                                       
                                </select>
                                @error('selectedAttribute') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Values<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="attribute_value_id.0" class="form-control" required>
                                      <option value="">Select Attribute</option>
                                      @if (!is_null($selectedAttribute))
                                        @foreach ($attribute_values as $attribute_value)
                                            <option value="{{ $attribute_value->id }}">{{ $attribute_value->name }}</option>
                                        @endforeach
                                      @endif
                                       
                                </select>
                                @error('attribute_value_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @foreach ($inputs as $key => $value)
               
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="title">Values<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="attribute_value_id.{{ $value }}" class="form-control" required>
                                              <option value="">Select Attribute</option>
                                              @if (!is_null($selectedAttribute))
                                                @foreach ($attribute_values as $attribute_value)
                                                    <option value="{{ $attribute_value->id }}">{{ $attribute_value->name }}</option>
                                                @endforeach
                                              @endif
                                               
                                        </select>
                                        @error('attribute_value_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Attribute Value</button>
                                    </div>
                                </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeProductAttributeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this Attribute? </strong> </center>
                </div>
                <form wire:submit.prevent="removeProductAttribute()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeProductAttributeValueModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this Attribute Value? </strong> </center>
                </div>
                <form wire:submit.prevent="removeProductAttributeValue()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
