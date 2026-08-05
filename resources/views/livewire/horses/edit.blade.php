<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Horse</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="update()" class="p-20" >
                                <h5 class="underline mt-n">Horse Info</h5>
                               <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Transporters<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="transporter_id" class="form-control" required>
                                                <option value="">Select Transporter</option>
                                                @foreach ($transporters as $transporter)
                                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                                @endforeach
                                            </select>
                                            <small>  <a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> 
                                            @error('transporter_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name">Custom Ref</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Enter Custom Reference #"/>
                                            @error('custom_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horse Type</label>
                                       <select wire:model.debounce.300ms="horse_type_id" class="form-control"  >
                                           <option value="">Select horse Type</option>
                                           @foreach ($horse_types as $horse_type)
                                               <option value="{{$horse_type->id}}">{{$horse_type->name}}</option>
                                           @endforeach
                                       </select>
                                       <small><a href="{{ route('horse_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i>New Horse Type</a></small> 
                                            @error('horse_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horse Group</label>
                                       <select wire:model.debounce.300ms="horse_group_id" class="form-control" >
                                           <option value="">Select horse Group</option>
                                           @foreach ($horse_groups as $horse_group)
                                               <option value="{{$horse_group->id}}">{{$horse_group->name}}</option>
                                           @endforeach
                                       </select>
                                       <small><a href="{{ route('horse_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i>New Horse Group</a></small> 
                                            @error('horse_group_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Fleet Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter horse Number" >
                                            @error('fleet_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horse Make<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="selectedMake" class="form-control" required>
                                           <option value="">Select Horse Make</option>
                                           @foreach ($horse_makes as $horse_make)
                                               <option value="{{$horse_make->id}}">{{$horse_make->name}}</option>
                                           @endforeach
                                       </select>
                                       <small><a href="{{ route('horse_makes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i>New Horse Make</a></small> 
                                            @error('selectedMake') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horse Model<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="horse_model_id" class="form-control" required>
                                           <option value="">Select Horse Model</option>
                                           @if (!is_null($selectedMake))
                                           @foreach ($horse_models as $horse_model)
                                               <option value="{{$horse_model->id}}">{{$horse_model->name}}</option>
                                           @endforeach
                                           @endif
                                       </select>
                                       <small><a href="{{ route('horse_makes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i>New Horse Model</a></small> 
                                            @error('horse_model_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter horse Registration Number" required>
                                            @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Chasis Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="chasis_number" placeholder="Enter horse Chasis Number" >
                                            @error('chasis_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Engine Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="engine_number" placeholder="Enter horse Engine Number" >
                                            @error('engine_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                         <div class="form-group">
                                            <label for="year">Year</label>
                                            <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Year">
                                                @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="color">Color</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Color " >
                                            @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="color">GVM</label>
                                            <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="gvm"  placeholder="Enter GVM in Kgs " />
                                            @error('gvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="color">NVM</label>
                                            <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="nvm"  placeholder="Enter NVM in Kgs " />
                                            @error('nvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    <!-- /.col-md-6 -->
                                </div>
                                <div class="row">
          
                                    <div class="col-md-6">
                                         <div class="form-group">
                                            <label for="year">Acquisition  Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Purchase Date">
                                                @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                    </div>
                                    <div class="col-md-6">
                                         <div class="form-group">
                                            <label for="year">Dispose Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter Dispose Date">
                                                @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="color">Number of wheels</label>
                                            <input type="number" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No of Wheels " />
                                            @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Manufacturer</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" >
                                            @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contact13">Country of Origin</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " >
                                            @error('origin') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                 <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="color">Condition</label>
                                           <select wire:model.debounce.300ms="condition" class="form-control" >
                                               <option value="">Select horse Condition</option>
                                               <option value="new">New</option>
                                               <option value="second hand">Second Hand</option>
                                               <option value="accident damaged">Accident Damaged</option>
                                           </select>
                                            @error('condition') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="contact13">Mileage</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter horse Mileage " />
                                            @error('mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="contact13">Engine Hours</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter horse engine hours " />
                                            @error('hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Fuel Type</label>
                                           <select name="" class="form-control" wire:model.debounce.300ms="fuel_type" >
                                               <option value="">Select Fuel Type</option>
                                               <option value="petrol">Petrol</option>
                                               <option value="diesel">Diesel</option>
                                               <option value="unleaded">Unleaded</option>
                                               <option value="gas">Gas</option>
                                               <option value="electric">Electric</option>
                                           </select>
                                            @error('fuel_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="model">Standard Fuel Consumption Empty (<i>Km/L</i>) </label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_consumption_empty_standard" placeholder="Fuel consumption of horse when empty Kilo per Litre" >
                                            @error('fuel_consumption_empty_standard') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="model">Standard Fuel Consumption Loaded (<i>Km/L</i>) </label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_consumption_loaded_standard" placeholder="Fuel consumption of horse when loaded Kilo per Litre" >
                                            @error('fuel_consumption_loaded_standard') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <h5 class="underline mt-n">Mechanical Details</h5>
                              
                                <div class="mb-10">
                                    <input type="checkbox" wire:model.debounce.300ms="mechanical"   class="line-style" />
                                    <label for="one" class="radio-label">Mechanical Details</label>
                                    @error('mechanical') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if ($mechanical == True)
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Engine Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="engine_number" placeholder="Engine Number">
                                            @error('engine_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                @include('livewire.partials.mechanical-details-fields')
                                @endif
                                <br>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-refresh"></i>Update</button>
                                        </div>
                                    </div>
                                    </div>
                            </form>





                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


</div>
