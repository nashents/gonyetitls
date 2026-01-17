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
                                <a href="" data-toggle="modal" data-target="#companyModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Company</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">


                            <table id="companiesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Phonenumber
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Pin
                                    </th>
                                    <th class="th-sm">Address
                                    </th>
                                    <th class="th-sm">Expiry
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($companies->count()>0)
                                <tbody>
                                    @foreach ($companies as $company)
                                  <tr>
                                    <td>{{$company->type}}</td>
                                    <td>{{$company->name}}</td>
                                    <td>{{$company->phonenumber}}</td>
                                    <td>{{$company->email}}</td>
                                    <td>{{$company->pin}}</td>
                                    <td>{{$company->street_address}} {{$company->suburb}} {{$company->city ? ", ".$company->city : ""}} {{$company->country ? $company->country : ""}}</td>
                                    <td>{{$company->expiry_date}}</td>
                                    <td><span class="badge bg-{{$company->status == 1 ? "success" : "danger"}}">{{$company->status == 1 ? "Active" : "Suspended"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('companies.show',$company->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$company->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#companyDeleteModal{{ $company->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('companies.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="companyModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Create Company <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >

               <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Name<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="name" class="form-control" placeholder="Enter Company Name" required>
                            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Types<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="selectedType" class="form-control" required>
                                <option value="">Select Type</option>
                                @if (Auth::user()->is_admin())
                                <option value="Admin">Admin</option> 
                                @endif
                                <option value="Rental">Car Rental</option>
                                <option value="Broker">Broker</option>
                                <option value="Transporter">Transporter</option>
                            </select>
                            @error('selectedType') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                @if (Auth::user()->is_admin())
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Currencies<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="license_currency_id" class="form-control" required>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                @endforeach
                            
                            </select>
                            @error('license_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Plan<span class="required" style="color: red">*</span></label>
                            @if (Auth::user()->is_admin() && $selectedType == "Transporter")
                            <select wire:model.debounce.300ms="selectedPlan" class="form-control" required>
                                <option value="">Select Plan</option>
                                <option value="10">0 - 10</option> 
                                <option value="25">11 - 25</option>
                                <option value="50">26 - 50</option>
                                <option value="75">51 - 75</option>
                                <option value="100">76 - 100</option>
                                <option value="125">101 - 125</option>
                                <option value="150">126 - 150</option>
                                <option value="175">151 - 175</option>
                                <option value="200">176 - 200</option>
                                <option value="201">200></option>
                            </select>
                            @else   
                            <select wire:model.debounce.300ms="selectedPlan" class="form-control"  disabled>
                                <option value="">Select Plan</option>
                                <option value="10">0 - 10</option> 
                                <option value="25">11 - 25</option>
                                <option value="50">26 - 50</option>
                                <option value="75">51 - 75</option>
                                <option value="100">76 - 100</option>
                                <option value="125">101 - 125</option>
                                <option value="150">126 - 150</option>
                                <option value="175">151 - 175</option>
                                <option value="200">176 - 200</option>
                                <option value="201">200></option>
                            </select>
                            @endif
                            @error('selectedPlan') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email">License Fee<span class="required" style="color: red">*</span></label>
                           <input type="number" step="any" wire:model.debounce.300ms="fee" class="form-control" placeholder="Enter License Fee" required>
                            @error('fee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                @endif
               <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email<span class="required" style="color: red">*</span></label>
                       <input type="email" wire:model.debounce.300ms="email" class="form-control" placeholder="Enter Company Email" required>
                        @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phonenumber">Phonenumber<span class="required" style="color: red">*</span></label>
                       <input type="text" wire:model.debounce.300ms="phonenumber" class="form-control" placeholder="Enter Company Phonenumber" required>
                        @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
               </div>
               
               
                @if (Auth::user()->is_admin())
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">No Reply Email<span class="required" style="color: red">*</span></label>
                           <input type="email" wire:model.debounce.300ms="noreply" class="form-control" placeholder="Enter No Reply Email" required>
                            @error('noreply') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">ERP URL<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="website" placeholder="ERP URL eg http://www.erp.gonyetilts.co.zw" >
                            @error('website') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                @endif
              <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Country<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="country" class="form-control" placeholder="Enter Company Country" required>
                            @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city">City<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="city" class="form-control" placeholder="Enter Company City" required>
                            @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
              </div>
              <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="suburb">Suburb<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="suburb" class="form-control" placeholder="Enter Company Suburb" required>
                            @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="street_address">Street Address<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="street_address" class="form-control" placeholder="Enter Company Street Address" required>
                            @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
              </div>
             
                @if (Auth::user()->is_admin())
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Roles<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="role_id" class="form-control" required>
                                <option value="" selected>Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="suburb">Expiry Date</label>
                           <input type="date" wire:model.debounce.300ms="expiry_date" class="form-control" placeholder="Enter Expiry Date">
                            @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Status<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="status" class="form-control" required>
                                <option value="" selected>Select Status</option>
                                <option value="1" selected>Active</option>
                                <option value="0" selected>Suspended</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                  
                @endif
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

<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="companyEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Company <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                               <input type="text" wire:model.debounce.300ms="name" class="form-control" placeholder="Enter Company Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Types<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedType" class="form-control" required>
                                    <option value="">Select Type</option>
                                    @if (Auth::user()->is_admin())
                                    <option value="Admin">Admin</option> 
                                    @endif
                                     <option value="Broker">Broker</option>
                                     <option value="Rental">Car Rental</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('selectedType') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   @if (Auth::user()->is_admin())
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="license_currency_id" class="form-control" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                
                                </select>
                                @error('license_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Plan<span class="required" style="color: red">*</span></label>
                                @if (Auth::user()->is_admin() && $selectedType == "Transporter")
                                <select wire:model.debounce.300ms="selectedPlan" class="form-control" required>
                                    <option value="">Select Plan</option>
                                    <option value="10">0 - 10</option> 
                                    <option value="25">11 - 25</option>
                                    <option value="50">26 - 50</option>
                                    <option value="75">51 - 75</option>
                                    <option value="100">76 - 100</option>
                                    <option value="125">101 - 125</option>
                                    <option value="150">126 - 150</option>
                                    <option value="175">151 - 175</option>
                                    <option value="200">176 - 200</option>
                                    <option value="201">200></option>
                                </select>
                                @else   
                                <select wire:model.debounce.300ms="selectedPlan" class="form-control" required disabled>
                                    <option value="">Select Plan</option>
                                    <option value="10">0 - 10</option> 
                                    <option value="25">11 - 25</option>
                                    <option value="50">26 - 50</option>
                                    <option value="75">51 - 75</option>
                                    <option value="100">76 - 100</option>
                                    <option value="125">101 - 125</option>
                                    <option value="150">126 - 150</option>
                                    <option value="175">151 - 175</option>
                                    <option value="200">176 - 200</option>
                                    <option value="201">200></option>
                                </select>
                                @endif
                                @error('selectedPlan') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">License Fee<span class="required" style="color: red">*</span></label>
                               <input type="number" step="any" wire:model.debounce.300ms="fee" class="form-control" placeholder="Enter License Fee" required>
                                @error('fee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                   <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email<span class="required" style="color: red">*</span></label>
                           <input type="email" wire:model.debounce.300ms="email" class="form-control" placeholder="Enter Company Email" required>
                            @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phonenumber">Phonenumber<span class="required" style="color: red">*</span></label>
                           <input type="text" wire:model.debounce.300ms="phonenumber" class="form-control" placeholder="Enter Company Phonenumber" required>
                            @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   </div>
                   
                   
                    @if (Auth::user()->is_admin())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">No Reply Email<span class="required" style="color: red">*</span></label>
                               <input type="email" wire:model.debounce.300ms="noreply" class="form-control" placeholder="Enter No Reply Email" required>
                                @error('noreply') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">ERP URL<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="website" placeholder="ERP URL eg http://www.erp.gonyetilts.co.zw" >
                                @error('website') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    @endif
                  <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Country<span class="required" style="color: red">*</span></label>
                               <input type="text" wire:model.debounce.300ms="country" class="form-control" placeholder="Enter Company Country" required>
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City<span class="required" style="color: red">*</span></label>
                               <input type="text" wire:model.debounce.300ms="city" class="form-control" placeholder="Enter Company City" required>
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                  </div>
                  <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="suburb">Suburb<span class="required" style="color: red">*</span></label>
                               <input type="text" wire:model.debounce.300ms="suburb" class="form-control" placeholder="Enter Company Suburb" required>
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address<span class="required" style="color: red">*</span></label>
                               <input type="text" wire:model.debounce.300ms="street_address" class="form-control" placeholder="Enter Company Street Address" required>
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                  </div>
                 
                    @if (Auth::user()->is_admin())
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Roles</label>
                                <select wire:model.debounce.300ms="role_id" class="form-control" >
                                    <option value="" selected>Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                                @error('role_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="suburb">Expiry Date</label>
                               <input type="date" wire:model.debounce.300ms="expiry_date" class="form-control" placeholder="Enter Expiry Date">
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Status<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="status" class="form-control" required>
                                    <option value="" selected>Select Status</option>
                                    <option value="1" selected>Active</option>
                                    <option value="0" selected>Suspended</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                      
                    @endif
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
