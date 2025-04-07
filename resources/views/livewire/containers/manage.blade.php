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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search fueling stations...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Station#
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Capacity(l)
                                    </th>
                                    <th class="th-sm">Balance(l)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($containers))
                                <tbody>
                                    @forelse ($containers as $container)
                                  <tr>

                                    <td>{{$container->container_number}}</td>
                                    <td>{{$container->name}}</td>
                                    <td>{{$container->fuel_type}}</td>
                                    <td>{{$container->currency ? $container->currency->name : ""}}</td>
                                    <td>
                                        @if ($container->capacity)
                                        {{$container->capacity}} Litres        
                                        @endif
                                    </td>
                                    <td>{{$container->balance}} Litres</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('containers.show', $container->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#"  wire:click="showTopUpModal({{$container->id}})" ><i class="fa fa-gas-pump color-success"></i> Fuel Top Up</a></li>
                                                <li><a href="#"  wire:click="edit({{$container->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#containerDeleteModal{{ $container->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('containers.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                           No Fueling Stations Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                               
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($containers))
                                        {{ $containers->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="top_upModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i>Fuel TopUp <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="topup()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="container_id">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="container_id" class="form-control" required disabled>
                                   <option value="">Select Station</option>
                                   @foreach ($containers as $container)
                                    <option value="{{$container->id}}">{{$container->name}}</option>
                                   @endforeach
                               </select>
                                @error('container_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendor_id">Vendors</label>
                               <select wire:model.debounce.300ms="vendor_id" class="form-control"  >
                                   <option value="">Select Vendor</option>
                                   @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                   @endforeach
                               </select>
                               <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name"> Date<span class="required" style="color: red">*</span></label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="date" placeholder="Enter TopUp date" required >
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required disabled>
                                   <option value="">Select Fuel Type</option>
                                    <option value="{{ $fuel_type }}">{{ $fuel_type }}</option>
                                   
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">Currency</label>
                               <select wire:model.debounce.300ms="currency_id" class="form-control"  >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                   @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   <div class="row">
                    @if (isset($capacity) && $capacity > 0)
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="required" style="color: red">*</span></label>
                            <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Initial Fuel Deposit Quantity" required/>
                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            @if ($total_fuel > $capacity)
                            <small style="color: red" >Quantity + Balance exceeds station capacity</small>
                            @endif
                        </div>
                    </div>
                    @else 
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="required" style="color: red">*</span></label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Initial Fuel Deposit Quantity" required/>
                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @endif
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" >
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="containerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Fueling Station <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name"> Name<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Station Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small style="color: green">Name Example: Zuva Msasa (Diesel)</small>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Station Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Station Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  
                  
                    <div class="form-group">
                        <label for="address">Address<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="address" placeholder="Enter Station Address" required />
                        @error('address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                   <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">Currency<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="container_currency_id" class="form-control" required >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                   @endforeach
                               </select>
                                @error('container_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="capacity" placeholder="Enter Tank Capacity" />
                                @error('capacity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Initial Deposit / Current Fuel Balance Details</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vendor_id">Vendors</label>
                               <select wire:model.debounce.300ms="vendor_id" class="form-control"  >
                                   <option value="">Select Vendor</option>
                                   @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                   @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">Currency</label>
                               <select wire:model.debounce.300ms="currency_id" class="form-control"  >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                   @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($capacity) && $capacity > 0)
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Quantity <span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Initial Fuel Deposit Quantity" required/>
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($quantity > $capacity)
                                    <small style="color: red" >Quantity exceeds station capacity</small>
                                @endif
                            </div>
                        </div>
                        @else 
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Quantity <span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Initial Fuel Deposit Quantity" required/>
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                    </div>
                  
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rate">Rate</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" />
                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount"/>
                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="balance">Balance</label>
                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" disabled />
                    @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="containerEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Fueling Station <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name"> Name<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Station Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small style="color: green">Name Example: Zuva Msasa (Diesel)</small>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Station Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Station Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  
                  
                    <div class="form-group">
                        <label for="address">Address<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="address" placeholder="Enter Station Address" required />
                        @error('address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                   <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">Currency<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="container_currency_id" class="form-control" required >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                   @endforeach
                               </select>
                                @error('container_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="capacity" placeholder="Enter Tank Capacity" />
                                @error('capacity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @if (isset($capacity) && $capacity > 0)
                    <div class="form-group">
                      <label for="balance">Balance</label>
                      <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="balance" />
                      @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                      @else   
                      <div class="form-group">
                          <label for="balance">Balance</label>
                          <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" />
                          @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                          @if ($balance > $capacity)
                          <small style="color: red" >Balance exceeds station capacity</small>
                      @endif
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

