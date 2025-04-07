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
                                <a href="" data-toggle="modal" data-target="#horse_makeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Horse Make</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="horse_makesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Horse Make
                                    </th>
                                    <th class="th-sm">Horse Model(s)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($horse_makes->count()>0)
                                <tbody>
                                    @foreach ($horse_makes as $horse_make)
                                    @foreach ($horse_make->horse_models as $horse_model)
                                  <tr>
                                    <td>{{$horse_make->name}}</td>
                                    <td>{{$horse_model->name}}</td>
                                    <td><span class="badge bg-{{$horse_make->status == 1 ? "success" : "danger"}}">{{$horse_make->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" data-toggle="modal" data-target="#horse_makeEditModal" wire:click="edit({{$horse_make->id}})" ><i class="fa fa-edit color-success"></i> Edit Make</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#horse_modelEditModal" wire:click="editModel({{$horse_model->id}})" ><i class="fa fa-edit color-success"></i> Edit Model</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#horse_makeDeleteModal{{ $horse_make->id }}" ><i class="fa fa-trash color-danger"></i>Delete Make</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#horse_modelDeleteModal{{ $horse_model->id }}" ><i class="fa fa-trash color-danger"></i>Delete Model</a></li>
                                            </ul>
                                        </div>
                                        @include('horse_makes.delete')
                                        @include('horse_models.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="horse_makeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Horse Make(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                   <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Horse Make(s)</label>
                            <select wire:model.debounce.300ms="horse_make_id" class="form-control" {{$make ? "disabled" : ""}}>
                                <option value="">Select Make</option>
                                @foreach ($horse_makes as $horse_make)
                                    <option value="{{$horse_make->id}}">{{$horse_make->name}}</option>
                                @endforeach
                            </select>
                            @error('horse_make_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Horse Make</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="make" {{$horse_make_id ? "disabled" : ""}} >
                            @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Models</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="model.0" placeholder="Enter Horse Model" required >
                                @error('model.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="name">Model</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="model.{{$value}}" placeholder="Enter Horse Model" required>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="horse_makeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Horse Make <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Make</label>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="horse_modelEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Horse Model <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

