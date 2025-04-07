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
                                <a href="" data-toggle="modal" data-target="#vehicle_makeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Vehicle Make</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="vehicle_makesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Vehicle Make
                                    </th>
                                    <th class="th-sm">Vehicle Model(s)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($vehicle_makes->count()>0)
                                <tbody>
                                    @foreach ($vehicle_makes as $vehicle_make)
                                    @foreach ($vehicle_make->vehicle_models as $vehicle_model)
                                  <tr>
                                    <td>{{$vehicle_make->name}}</td>
                                    <td>{{$vehicle_model->name}}</td>
                                    <td><span class="badge bg-{{$vehicle_make->status == 1 ? "success" : "danger"}}">{{$vehicle_make->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" data-toggle="modal" data-target="#vehicle_makeEditModal" wire:click="edit({{$vehicle_make->id}})" ><i class="fa fa-edit color-success"></i> Edit Make</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#vehicle_modelEditModal" wire:click="editModel({{$vehicle_model->id}})" ><i class="fa fa-edit color-success"></i> Edit Model</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#vehicle_makeDeleteModal{{ $vehicle_make->id }}" ><i class="fa fa-trash color-danger"></i>Delete Make</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#vehicle_modelDeleteModal{{ $vehicle_model->id }}" ><i class="fa fa-trash color-danger"></i>Delete Model</a></li>
                                            </ul>
                                        </div>
                                        @include('vehicle_makes.delete')
                                        @include('vehicle_models.delete')
                                </td>
                                  </tr>
                                  @endforeach
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vehicle_makeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Vehicle Make(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <h5 class="underline mt-30">Select Horse Make | Add new Horse Make</h5>
                    <div class="row">
                     <div class="col-md-6">
                         <div class="form-group">
                             <label for="name">Vehicle Make(s)</label>
                             <select wire:model.debounce.300ms="vehicle_make_id" class="form-control" {{$make ? "disabled" : ""}}>
                                 <option value="">Select Make</option>
                                 @foreach ($vehicle_makes as $vehicle_make)
                                     <option value="{{$vehicle_make->id}}">{{$vehicle_make->name}}</option>
                                 @endforeach
                             </select>
                             @error('vehicle_make_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                         </div>
                     </div>
                     <div class="col-md-6">
                         <div class="form-group">
                             <label for="name">Vehicle Make</label>
                             <input type="text" class="form-control" wire:model.debounce.300ms="make" {{$vehicle_make_id ? "disabled" : ""}} >
                             @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                         </div>
                     </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Models</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="model.0" placeholder="Enter Vehicle Model" required >
                                @error('model.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="name">Model</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="model.{{$value}}" placeholder="Enter Vehicle Model" required>
                                @error('model.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Model</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vehicle_makeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Vehicle Make <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Make</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="make" required>
                        @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vehicle_modelEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Vehicle Model <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateModel()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Model</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="model" required>
                        @error('model') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

