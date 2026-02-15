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
                                <a href="" data-toggle="modal" data-target="#waste_typeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Waste Type</a>
                                {{-- <a href="#" wire:click="exportwaste_typesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportwaste_typesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportwaste_typesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> --}}
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Composition
                                    </th>
                                    <th class="th-sm">Area
                                    </th>
                                    <th class="th-sm">Impact
                                    </th>
                                    <th class="th-sm">Qty Collected
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($waste_types))
                                <tbody>
                                    @forelse ($waste_types as $waste_type)
                                  <tr>
                                    <td>{{$waste_type->name}}</td>
                                    <td>{{$waste_type->category}}</td>
                                    <td>{{$waste_type->general_composition}}</td>
                                    <td>{{$waste_type->generation_area}}</td>
                                    <td>{{$waste_type->impact}}</td>
                                    <td>{{$waste_type->control_methods}}</td>
                                    <td>{{$waste_type->waste_collection_items->sum('balance') ?? 0}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$waste_type->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$waste_type->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                      
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Waste Types Found ....
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
                                    @if (isset($waste_types))
                                        {{ $waste_types->links() }} 
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="waste_typeDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Waste Type?</strong> </center> 
                </div>
                <form wire:submit.prevent="destroy()" >
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
    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="waste_typeModal" tawaste_typedex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Waste Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Type/Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="category" required>
                                    <option value="">Select Option</option>
                                    <option value="Hazardous - Chemical">Hazardous - Chemical</option>
                                    <option value="Metal">Metal</option>
                                    <option value="Plastic">Plastic</option>
                                    <option value="Rubber">Rubber</option>
                                    <option value="Food and Biodegradable">Food and Biodegradable</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Generation Area<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="generation_area" required>
                                @error('generation_area') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">General Composition<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="general_composition" required>
                                @error('general_composition') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div> 
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                            <label for="name">Waste Impact<span class="required" style="color: red">*</span></label>
                               <textarea class="form-control" wire:model.debounce.300ms="impact" cols="30" rows="3" required></textarea>
                                @error('impact') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Control Methods<span class="required" style="color: red">*</span></label>
                               <textarea class="form-control" wire:model.debounce.300ms="control_methods" cols="30" rows="3" required></textarea>
                                @error('control_methods') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="waste_typeEditModal" tawaste_typedex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Waste Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="waste_type_id">
                <div class="modal-body">
                                          <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Type/Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="category" required>
                                    <option value="">Select Option</option>
                                    <option value="Hazardous - Chemical">Hazardous - Chemical</option>
                                    <option value="Metal">Metal</option>
                                    <option value="Plastic">Plastic</option>
                                    <option value="Rubber">Rubber</option>
                                    <option value="Food and Biodegradable">Food and Biodegradable</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Generation Area<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="generation_area" required>
                                @error('generation_area') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">General Composition<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="general_composition" required>
                                @error('general_composition') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div> 
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                            <label for="name">Waste Impact<span class="required" style="color: red">*</span></label>
                               <textarea class="form-control" wire:model.debounce.300ms="impact" cols="30" rows="3" required></textarea>
                                @error('impact') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Control Methods<span class="required" style="color: red">*</span></label>
                               <textarea class="form-control" wire:model.debounce.300ms="control_methods" cols="30" rows="3" required></textarea>
                                @error('control_methods') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

