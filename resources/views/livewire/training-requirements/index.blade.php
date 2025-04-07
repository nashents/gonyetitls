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
                                <a href="#" data-toggle="modal" data-target="#training_requirementModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Requirement</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="training_requirementsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Department
                                    </th>
                                    <th class="th-sm">Item
                                    </th>
                                    <th class="th-sm">Requirement
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($training_requirements->count()>0)
                                <tbody>
                                    @foreach ($training_requirements as $training_requirement)
                                  <tr>
                                    <td>{{$training_requirement->training_department ? $training_requirement->training_department->name : ""}}</td>
                                    <td>{{$training_requirement->training_item ? $training_requirement->training_item->name : ""}}</td>
                                    <td>{{$training_requirement->required}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$training_requirement->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#training_requirementDeleteModal{{ $training_requirement->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('training_requirements.delete')
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

  
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="training_requirementModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Requirement <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Training Department<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="training_department_id" class="form-control">
                                <option value="">Select Training Item</option>
                                @foreach ($training_departments as $department)
                                    <option value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                              </select>
                                @error('training_department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Training Item<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="training_item_id" class="form-control">
                                <option value="">Select Training Item</option>
                                @foreach ($training_items as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                              </select>
                                @error('training_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                   
                    <div class="form-group">
                        <label for="name">Requirement Level<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="required" class="form-control">
                            <option value="">Select Option</option>
                            <option value="Skilled">Skilled</option>
                            <option value="Basic">Basic</option>
                            <option value="Not Required">Not Required</option>
                        </select>
                        @error('required') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="training_requirementEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Training Requirement <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Training Department<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="training_department_id" class="form-control">
                                    <option value="">Select Training Item</option>
                                    @foreach ($training_departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                                    </select>
                                    @error('training_department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Training Item<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="training_item_id" class="form-control">
                                <option value="">Select Training Item</option>
                                @foreach ($training_items as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                              </select>
                                @error('training_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                   
                    <div class="form-group">
                        <label for="name">Requirement Level<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="required" class="form-control">
                            <option value="">Select Option</option>
                            <option value="Skilled">Skilled</option>
                            <option value="Basic">Basic</option>
                            <option value="Not Required">Not Required</option>
                        </select>
                        @error('required') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

