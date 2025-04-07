<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Trailer</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="update()" >
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="exampleInputEmail13">Transporters<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="transporter_id" class="form-control" required >
                                                    <option value="">Select Transporter</option>
                                                    @foreach ($transporters as $transporter)
                                                        <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('transporter_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="trailer_type">Trailer Types<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="trailer_type_id" class="form-control" required >
                                                   <option value="">Select Trailer Type</option>
                                                  @foreach ($trailer_types as $trailer_type)
                                                    <option value="{{$trailer_type->id}}">{{$trailer_type->name}}</option>
                                                  @endforeach
                                               </select>
                                                @error('trailer_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="number">Fleet Number</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter Fleet Number"/>
                                                @error('fleet_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                   
                                   <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Make<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="make" placeholder="Enter Make Name" required/>
                                            @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Model<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="model" placeholder="Enter Model Name" required />
                                            @error('model') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter Trailer Registration Number" required/>
                                            @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   </div>
                                   
                                   <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="year">Year</label>
                                                <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Trailer Year"  />
                                                    @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="color">Color</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Trailer Color " />
                                                @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="color">No of wheels</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No Of Wheels"/>
                                                @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                   </div>
                                  
                                  <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail13">Manufacturer</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" />
                                                @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="contact13">Country of Origin</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " />
                                                @error('origin') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="color">Condition</label>
                                               <select wire:model.debounce.300ms="condition" class="form-control" >
                                                   <option value="">Select Trailer Condition</option>
                                                   <option value="new">New</option>
                                                   <option value="second hand">Second Hand</option>
                                                   <option value="accident damaged">Accident Damaged</option>
                                               </select>
                                                @error('condition') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="color">Suspension Type</label>
                                               <select wire:model.debounce.300ms="suspension_type" class="form-control" >
                                                   <option value="">Select Suspension Type</option>
                                                   <option value="Henred">Henred</option>
                                                   <option value="Load">Load</option>
                                                   <option value="SPW">SPW</option>
                                                   <option value="ROR">ROR</option>
                                               </select>
                                                @error('suspension_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                  </div>
                                  
                                </div>
                                <div class="modal-footer">
                                    <div class="btn-group" role="group">
                                        <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                                    </div>
                                    <!-- /.btn-group -->
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


</div>
