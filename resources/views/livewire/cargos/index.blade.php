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
                                <a href="#" data-toggle="modal" data-target="#cargoModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Cargo</a>
                                <a href="" data-toggle="modal" data-target="#cargosImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportCargosExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportCargosCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportCargosPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="cargosTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Group
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Measurement
                                    </th>
                                    <th class="th-sm">Risk Level
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($cargos->count()>0)
                                <tbody>
                                    @foreach ($cargos as $cargo)
                                  <tr>
                                    <td>{{ucfirst($cargo->user ? $cargo->user->name : "")}}</td>
                                    <td>{{ucfirst($cargo->type)}}</td>
                                    <td>{{ucfirst($cargo->group)}}</td>
                                    <td>{{ucfirst($cargo->name)}}</td>
                                    <td>{{ucfirst($cargo->measurement)}}</td>
                                    <td>{{ucfirst($cargo->risk)}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('cargos.show', $cargo->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$cargo->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#cargoDeleteModal{{ $cargo->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('cargos.delete')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="cargosImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Cargos <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('cargos.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Cargos(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Cargo File" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="cargoModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Cargo Types<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="type" class="form-control" required >
                           <option value="">Select Cargo Type</option>
                           <option value="Liquid">Liquid</option>
                           <option value="Solid">Solid</option>
                       </select>
                        @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Cargo Groups<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="group" class="form-control" required >
                           <option value="">Select Cargo Group</option>
                           <option value="Crop">Crop</option>
                           <option value="Fertilizer">Fertilizer</option>
                           <option value="Food & Drinks">Food & Drinks</option>
                           <option value="Fuel">Fuel</option>
                           <option value="Furniture">Furniture</option>
                           <option value="Mineral">Mineral</option>
                           <option value="Seed">Seed</option>
                           <option value="Other">Other</option>
                       </select>
                        @error('group') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="country">Cargo Measurement(s)<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="measurement" class="form-control" required>
                           <option value="">Select Cargo Measurement</option>
                           <option value="Tons">Tons</option>
                           <option value="KGs">KGs</option>
                           <option value="Litres">Litres</option>
                       </select>
                        @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Risk Level(s)</label>
                       <select wire:model.debounce.300ms="risk" class="form-control" >
                           <option value="">Select Risk Level</option>
                           <option value="High">High</option>
                           <option value="Medium">Medium</option>
                           <option value="Low">Low</option>
                       </select>
                        @error('risk') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div style="height: 400px; overflow: auto">
                        <div class="form-group">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Add Transporters<span class="required" style="color: red">*</span></th>
                                  </tr>
                                </thead>
                                @if ($transporters->count()>0)
                                <tbody>
                                    @foreach ($transporters as $transporter)
                                  <tr>
                                    <td>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="transporter_id.{{$transporter->id}}" wire:key="{{ $transporter->id }}" value="{{ $transporter->id }}" class="line-style"  />
                                            <label for="one" class="radio-label">{{$transporter->name}} </label>
                                            @error('transporter_id.'.$transporter->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @endif
                              </table>  
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="cargoEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                 
                        <div class="form-group">
                            <label for="country">Cargo Types<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="type" class="form-control" required >
                               <option value="">Select Cargo Type</option>
                               <option value="Liquid">Liquid</option>
                               <option value="Solid">Solid</option>
                           </select>
                            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                        <label for="country">Groups<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="group" class="form-control" required>
                           <option value="">Select Cargo Group</option>
                           <option value="Crop" {{$group == "Crop" ? "selected" : ""}}>Crop</option>
                           <option value="Fertilizer" {{$group == "Fertilizer" ? "selected" : ""}}>Fertilizer</option>
                           <option value="Food & Drinks" {{$group == "Food & Drinks" ? "selected" : ""}}>Food & Drinks</option>
                           <option value="Fuel" {{$group == "Fuel" ? "selected" : ""}}>Fuel</option>
                           <option value="Furniture" {{$group == "Furniture" ? "selected" : ""}}>Furniture</option>
                           <option value="Mineral" {{$group == "Mineral" ? "selected" : ""}}>Mineral</option>
                           <option value="Seed" {{$group == "Seed" ? "selected" : ""}}>Seed</option>
                           <option value="Other" {{$group == "Other" ? "selected" : ""}}>Other</option>
                       </select>
                        @error('group') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Cargo Measurement(s)<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="measurement" class="form-control" required >
                           <option value="">Select Cargo Measurement</option>
                           <option value="Tons" {{$measurement == "Tons" ? "selected" : ""}}>Tons</option>
                           <option value="KGs" {{$measurement == "KGs" ? "selected" : ""}}>KGs</option>
                           <option value="Litres" {{$measurement == "Litres" ? "selected" : ""}}>Litres</option>
                       </select>
                        @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Risk Level(s)</label>
                       <select wire:model.debounce.300ms="risk" class="form-control">
                           <option value="">Select Risk Level</option>
                           <option value="High">High</option>
                           <option value="Medium">Medium</option>
                           <option value="Low">Low</option>
                       </select>
                        @error('risk') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

