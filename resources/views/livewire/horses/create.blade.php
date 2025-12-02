<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Add New Horse</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="store()" class="p-20" enctype="multipart/form-data">
                                <h5 class="underline mt-n">Horse Info</h5>
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horse Type</label>
                                       <select wire:model.debounce.300ms="horse_type_id" class="form-control" >
                                           <option value="">Select Horse Type</option>
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
                                       <select wire:model.debounce.300ms="horse_group_id" class="form-control">
                                           <option value="">Select Horse Group</option>
                                           @foreach ($horse_groups as $horse_group)
                                               <option value="{{$horse_group->id}}">{{$horse_group->name}}</option>
                                           @endforeach
                                       </select>
                                       <small><a href="{{ route('horse_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i>New Horse Group</a></small> 
                                            @error('horse_group_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>


                                    <!-- /.col-md-6 -->
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Fleet Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter Fleet Number" >
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
                                       <small>  <a href="{{ route('horse_makes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Horse Make</a></small> 
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
                                            <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter horse Registration Number" required/>
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
                                            <label for="make">Engine Types</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="engine_type" >
                                                <option value="">Select Engine Type</option>
                                                <option value="CUMMINS N 14 SELECT PLUS">CUMMINS N 14 SELECT PLUS</option>
                                                <option value="BENZ 450HP 12.8L 400">BENZ 450HP 12.8L 400</option>
                                                <option value="BENZ 450HP 12.8L 400">MBE4000 BENZ 12.8L</option>
                                            </select>
                                            @error('engine_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Engine Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="engine_number" placeholder="Engine Number">
                                            @error('engine_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Engine CPL</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="engine_cpl" placeholder="Engine CPL">
                                            @error('engine_cpl') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Gearbox Types</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="gearbox_type" >
                                                <option value="">Select Gearbox Type</option>
                                                <option value="EATON FULLER 9 SPEED No OVERDRIVE">EATON FULLER 9 SPEED No OVERDRIVE</option>
                                                <option value="EATON 13 SPEED">EATON 13 SPEED</option>
                                                <option value="ZF MERITOR">ZF MERITOR</option>
                                            </select>
                                            @error('gearbox_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Differential Types</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="differential_type" >
                                                <option value="">Select Differential Type</option>
                                                <option value="ROCKWELL R740">ROCKWELL R740</option>
                                                <option value="ROCKWELL RT40">ROCKWELL RT40</option>
                                            </select>
                                            @error('differential_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                  
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Differential Ratio</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="differential_ratio" placeholder="Differential ratio">
                                            @error('differential_ratio') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Compressor Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="compressor_type" >
                                                <option value="">Select Compressor Type</option>
                                                <option value="Single Barrel">Single Barrel</option>
                                            </select>
                                            @error('compressor_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Comppressor Size</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="compressor_size" placeholder="Compressor Size">
                                            @error('compressor_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Universal J Size</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="universal_j_size" placeholder="Universal J Size, No 1, 2 & 3">
                                            @error('universal_j_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Rear Spring Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="rear_spring_type" >
                                                <option value="">Select Rear Spring Type</option>
                                                <option value="AIR SUSPENSION J SPRING">AIR SUSPENSION J SPRING</option>
                                            </select>
                                            @error('rear_spring_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Front Spring Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="front_spring_type" >
                                                <option value="">Select Front Spring Type</option>
                                                <option value="2 LEAF RUBBER BUSHES">2 LEAF RUBBER BUSHES</option>
                                                <option value="FULL DOUBLE RUBBER BUSHED">FULL DOUBLE RUBBER BUSHED</option>
                                                <option value="1 & 1/2 RUBBER BUSH & PIN">1 & 1/2 RUBBER BUSH & PIN</option>
                                                <option value="Z SPRING with Air Bag">Z SPRING with Air Bag</option>
                                            </select>
                                            @error('front_spring_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Flange Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="flange_size" >
                                                <option value="">Select Flange Type</option>
                                                <option value="1 Inch">1 Inch</option>
                                                <option value="2 Inch">2 Inch</option>
                                                <option value="3 Inch">3 Inch</option>
                                                <option value="4 Inch">4 Inch</option>
                                                <option value="5 Inch">5 Inch</option>
                                                <option value="6 Inch">6 Inch</option>
                                                <option value="7 Inch">7 Inch</option>
                                                <option value="8 Inch">8 Inch</option>
                                            </select>
                                            @error('flange_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Steering Box Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="steering_box_type" >
                                                <option value="">Select Steering Box Type</option>
                                                <option value="TRW">TRW</option>
                                                <option value="TRUU">TRUU</option>
                                            </select>
                                            @error('steering_box_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Cab Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="cab_type" >
                                                <option value="">Select Cab Type</option>
                                                <option value="FLD">FLD</option>
                                                <option value="COLUMBIA">COLUMBIA</option>
                                            </select>
                                            @error('cab_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Air Dryer System</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="air_dryer_system" >
                                                <option value="">Select Air Dryer System</option>
                                                <option value="Big Type">Big Type</option>
                                                <option value="Small Catridge Type">Small Catridge Type</option>
                                            </select>
                                            @error('air_dryer_system') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">5th Wheel Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="fifth_wheel_type" >
                                                <option value="">Select 5th Wheel Type</option>
                                                <option value="SIMPLEX LITE">SIMPLEX LITE</option>
                                                <option value="Fontaine no slack 11">Fontaine no slack 11</option>
                                                <option value="SIMPLEX FIRTH WHEEL">SIMPLEX FIRTH WHEEL</option>
                                            </select>
                                            @error('fifth_wheel_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Starter Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="starter_type" >
                                                <option value="">Select Starter Type</option>
                                                <option value="DELCO RENNY">DELCO RENNY</option>
                                                <option value="MT41">MT41</option>
                                                <option value="MT39">MT39</option>
                                            </select>
                                            @error('starter_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Starter Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="starter_size" >
                                                <option value="">Select Starter Size</option>
                                                <option value="BIG">BIG</option>
                                                <option value="SMALL">SMALL</option>
                                            </select>
                                            @error('starter_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Alternator Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="alternator_type" >
                                                <option value="">Select Alternator Type</option>
                                                <option value="DELLO">DELLO</option>
                                                <option value="DELIO REMNY">DELIO REMNY</option>
                                                <option value="DELCO REMY">DELCO REMY</option>
                                            </select>
                                            @error('alternator_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Alternator Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="alternator_size" >
                                                <option value="">Select Alternator Size</option>
                                                <option value="BIG">BIG</option>
                                                <option value="SMALL">SMALL</option>
                                            </select>
                                            @error('alternator_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Fuel Filtering Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="fuel_filtering_type" >
                                                <option value="">Select Fuel Filtering Type</option>
                                                <option value="FS1000">FS1000</option>
                                                <option value="Z366">Z366</option>
                                                <option value="WATER SEPERATOR 2366 AND CATRIC">WATER SEPERATOR 2366 AND CATRIC</option>
                                            </select>
                                            @error('fuel_filtering_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Kingpin Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="king_pin_type" >
                                                <option value="">Select Kingpin Type</option>
                                                <option value="DOUBLE QUARTER PIN">DOUBLE QUARTER PIN</option>
                                            </select>
                                            @error('king_pin_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Waterpump Belt Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="water_pump_belt_type" >
                                                <option value="">Select Waterpump Belt Type</option>
                                                <option value="5 SPLINE">5 SPLINE</option>
                                                <option value="7 SPLINE">7 SPLINE</option>
                                            </select>
                                            @error('water_pump_belt_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Waterpump Belt Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="water_pump_belt_size" >
                                                <option value="">Select Waterpump Belt Size</option>
                                            </select>
                                            @error('water_pump_belt_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                   
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Fan Belt Type</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="fan_belt_type" >
                                                <option value="">Select Fan Belt Type</option>
                                                <option value="8PK1705">8PK1705</option>
                                                <option value="7 SPLINE">7 SPLINE</option>
                                            </select>
                                            @error('fan_belt_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Fan Belt Sizes</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="fan_belt_size" >
                                                <option value="">Select Fan Belt Size</option>
                                            </select>
                                            @error('fan_belt_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Engine Mounting Types</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="engine_mounting_type" >
                                                <option value="">Select Engine Mounting Type</option>
                                                <option value="FLD">FLD</option>
                                                <option value="U MOUNTING">U MOUNTING</option>
                                            </select>
                                            @error('engine_mounting_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Steering Reservoirs</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="steering_reservoir" >
                                                <option value="">Select Steering Reservoir</option>
                                                <option value="CYLINDRICAL TANK">CYLINDRICAL TANK</option>
                                                <option value="ROUND PLASTIC">ROUND PLASTIC</option>
                                                <option value="PLASTIC SMALL (west-joint)">PLASTIC SMALL (west-joint)</option>
                                                <option value="SQUARE PLASTIC">SQUARE PLASTIC</option>
                                                <option value="BOX TYPE">BOX TYPE</option>
                                            </select>
                                            @error('steering_reservoir') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="make">Braking System Types</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="braking_system_type" >
                                                <option value="">Select Braking System Type</option>
                                                <option value="7 Inch">7 Inch </option>
                                                <option value="7 Inch Shoes">7 Inch Shoes</option>
                                                <option value="7 Inch Ordinary">7 Inch Ordinary</option>
                                            </select>
                                            @error('braking_system_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="clutch_size">Clutch Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="clutch_size" >
                                                <option value="">Select Clutch Size</option>
                                                <option value="15 Inch">15 Inch</option>
                                            </select>
                                            @error('clutch_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="make">Tnak HRS</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="tnak_hrs" >
                                                <option value="">Select Tnak HRS</option>
                                                <option value="530L">530L</option>
                                            </select>
                                            @error('tnak_hrs') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="make">Battery Size</label>
                                            <select name="" class="form-control" wire:model.debounce.300ms="battery_size" >
                                                <option value="">Select Battery Size</option>
                                            </select>
                                            @error('battery_size') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                              
                                @endif
                         

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="model">Horse Image(s)</label>
                                            <small style="color:red;">Accepted image types: jpg , jpeg , png</small>
                                            <input type="file" accept="image" wire:model.debounce.300ms="images" class="form-control" multiple>
                                            @error('images') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <h5 class="underline mt-n">Upload Files(<i>Registration Book, COF e.t.c</i>)</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="title.0" placeholder="Enter File Title eg Identity Card" />
                                            @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="file">File</label>
                                            <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File " />
                                            @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
        
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="expires_at">Expiry Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.0" placeholder="dd/mm/yy" />
                                            @error('expires_at.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                @foreach ($inputs as $key => $value)
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter File Title eg Identity Card"/>
                                            @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="file">File</label>
                                            <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File "/>
                                            @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="file">Expiry Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.{{$value}}"  placeholder="dd/mm/yy"/>
                                            @error('expires_at.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for=""></label>
                                            <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                @endforeach
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> File</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button>
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
