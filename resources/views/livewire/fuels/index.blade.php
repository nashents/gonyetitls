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
                            <div class="panel-title">
                                <h5>Fuel Orders Management</h5>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                        Fueling Station
                                      </span>
                                      <select wire:model.debounce.300ms="container_id" class="form-control" aria-label="..." >
                                        <option value="#">Select Station</option>
                                        @foreach ($containers as $container)
                                        <option value="{{ $container->id }}">{{ $container->name }}</option>
                                        @endforeach 
                                     </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="fuel_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Order Created At</option>
                              </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                        
                               
                                <!-- /input-group -->
                            </div>
                            <div class="row">
                                @if ($fuel_filter == "created_at")
                                <div class="col-lg-2" style="margin-right: 1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @elseif ($fuel_filter == "start_date")
                                <div class="col-lg-2" style="margin-right:1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @endif
                            </div>
                            <a href="" data-toggle="modal" data-target="#fuelModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Order</a>
                            <a href="#" wire:click="exportFuelsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                            <a href="#" wire:click="exportFuelsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                            <a href="#" wire:click="exportFuelsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            
                            </div>
                            <div class="col-md-5" style="float: right;">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Fuel Orders....">
                                </div>
                               
                            </div>
                            <br>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Order#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Category
                                        </th>
                                        <th class="th-sm">Station
                                        </th>
                                        <th class="th-sm">FillUp
                                        </th>
                                        <th class="th-sm">Quantity
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">Comments
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if (isset($fuels))
                                <tbody>
                                    @forelse  ($fuels as $fuel)
                                    @if ($fuel->fillup == 1)
                                    <tr style="background-color: #4CAF50">
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                                      <td>
                                        @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                        @if ((preg_match($pattern, $fuel->date)) )
                                            {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                        @else
                                        {{$fuel->date}}
                                        @endif    
                                       </td>
                                      <td>

                                        @if ($fuel->type == "Horse")
                                            @if (isset($fuel->horse))
                                            Horse: {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}} {{$fuel->horse->horse_make ? "| ".$fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                                @if (isset($fuel->trip))
                                                <br>
                                                    @php
                                                        $from = App\Models\Destination::find($fuel->trip->from);
                                                        $to = App\Models\Destination::find($fuel->trip->from);
                                                    @endphp
                                                    Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                                    @if (isset($from))
                                                        {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                                    @endif
                                                    @if (isset($to))
                                                        {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                                    @endif
                                            
                                                @endif
                                            @endif
                                        @elseif($fuel->type == "Vehicle")
                                            @if(isset($fuel->vehicle)) 
                                                Vehicle: {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                                @if (isset($fuel->trip))
                                                <br>
                                                    @php
                                                        $from = App\Models\Destination::find($fuel->trip->from);
                                                        $to = App\Models\Destination::find($fuel->trip->from);
                                                    @endphp
                                                    Trip: {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                                    @if (isset($from))
                                                        {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                                    @endif
                                                    @if (isset($to))
                                                        {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                                    @endif
                                            
                                                @endif
                                            @endif
                                        @elseif($fuel->type == "Asset")
                                            @if(isset($fuel->asset))
                                                Asset: | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                            @endif
                                        @elseif($fuel->type == "Other")
                                            Other
                                        @endif
                                      </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : ($fuel->fillup == "0" ? "Top Up" : "")}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td>{{$fuel->comments}}</td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                  <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                  @if ($fuel->authorization == "approved")
                                                  <li><a href="{{route('fuels.preview',$fuel->id)}}"  ><i class="fa fa-file-invoice color-primary"></i>Preview</a></li>
                                                  @endif
                                                  @if ($fuel->authorization == "pending" || $fuel->authorization == "rejected" )
                                                  <li><a href="#"  wire:click="edit({{$fuel->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                  <li><a href="#" data-toggle="modal" data-target="#fuelDeleteModal{{ $fuel->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                  @endif
                                              </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @elseif ($fuel->fillup == 0)
                                    <tr style="background-color: #FFC107">
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                                      <td>
                                        @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                        @if ((preg_match($pattern, $fuel->date)) )
                                            {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                        @else
                                        {{$fuel->date}}
                                        @endif  
                                    </td>
                                      <td>
                                        @if ($fuel->type == "Horse")
                                        @if (isset($fuel->horse))
                                        Horse: {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}} {{$fuel->horse->horse_make ? "| ".$fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                            @if (isset($fuel->trip))
                                            <br>
                                                @php
                                                    $from = App\Models\Destination::find($fuel->trip->from);
                                                    $to = App\Models\Destination::find($fuel->trip->from);
                                                @endphp
                                                Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                                @if (isset($from))
                                                    {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                                @endif
                                                @if (isset($to))
                                                    {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                                @endif
                                        
                                            @endif
                                        @endif
                                    @elseif($fuel->type == "Vehicle")
                                        @if(isset($fuel->vehicle)) 
                                            Vehicle: {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                            @if (isset($fuel->trip))
                                            <br>
                                                @php
                                                    $from = App\Models\Destination::find($fuel->trip->from);
                                                    $to = App\Models\Destination::find($fuel->trip->from);
                                                @endphp
                                                Trip: {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                                @if (isset($from))
                                                    {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                                @endif
                                                @if (isset($to))
                                                    {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                                @endif
                                        
                                            @endif
                                        @endif
                                    @elseif($fuel->type == "Asset")
                                        @if(isset($fuel->asset))
                                            Asset: | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        @endif
                                    @elseif($fuel->type == "Other")
                                        Other
                                    @endif
                                      </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : ($fuel->fillup == "0" ? "Top Up" : "")}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td>{{$fuel->comments}}</td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                @if ($fuel->authorization == "approved")
                                                <li><a href="{{route('fuels.preview',$fuel->id)}}"  ><i class="fa fa-file-invoice color-primary"></i>Preview</a></li>
                                                @endif
                                                @if ($fuel->authorization == "pending" || $fuel->authorization == "rejected" )
                                                <li><a href="#"  wire:click="edit({{$fuel->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#fuelDeleteModal{{ $fuel->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="9">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Fuel Orders Found ....
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
                                    @if (isset($fuels))
                                        {{ $fuels->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="form-group">
                             <div class="col-sm-10">
                                <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style" required  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                    <label for="one" class="radio-label">Asset</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                    <label for="one" class="radio-label">Other</label>
                                </div>  
                            </div>
                            @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                   
                     @if ($type == "Horse" || $type == "Vehicle")
                    <div class="row">
                        <div class="col-md-6">
                            @if ($type == "Horse")
                            <div class="form-group">
                                <label for="vehicles">Horses<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                   <option value="">Select Horse</option>
                                  @foreach ($horses as $horse)
                                      <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                  @endforeach
                               </select>
                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @elseif($type == "Vehicle")
                              <div class="form-group">
                                <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                   <option value="">Select Vehicle</option>
                                  @foreach ($vehicles as $vehicle)
                                      <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                  @endforeach
                               </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employees">Trips</label>
                               <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                                   <option value="">Select Trip</option>
                                  @foreach ($trips as $trip)
                                      <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                  @endforeach
                               </select>
                                @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @elseif($type == "Asset")

                        <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="drivers">Categories</label>
                           <select wire:model.debounce.300ms="selectedCategory" class="form-control">
                               <option value="">Select Category</option>
                              @foreach ($categories as $category)
                                  <option value="{{$category->id}}">{{$category->name}} </option>
                              @endforeach
                           </select>
                            @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="drivers">Sub Categories</label>
                           <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                               <option value="">Select Sub Category</option>
                              @foreach ($category_values as $category_value)
                                  <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                              @endforeach
                           </select>
                            @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="form-group">
                            <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                               <option value="">Select Asset</option>
                               @if (!is_null($selectedCategory))
                               @foreach ($assets as $asset)
                                  <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                              @endforeach
                              @endif
                           </select>
                            @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                    @endif
                    
                 
                    <div class="row">
                        @if ($type == "Horse" || $type == "Vehicle")
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                  @foreach ($containers as $container)
                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                  @endforeach
                               </select>
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                  @foreach ($containers as $container)
                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                  @endforeach
                               </select>
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                            </div>
                        </div>
                         @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required {{ isset($fuel_trip) ? "disabled" : "" }}>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" >
                                        Top Up
                                        </label>
                                    </div>
                                    @if (isset($fuel_trip))
                                    <small style="color:red">An initial fuel order created already.</small>
                                    @endif
                                    </div>
                                   
                                   
                                    @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                           
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  />
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  />
                                    @endif
                                @endif
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               @if (isset($trip_fuel) && isset($horse_fuel_total))
                                @if ($trip_fuel > $horse_fuel_total)
                                <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                @endif
                               @endif
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance) && isset($quantity))
                                            @if ($container_balance < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                            @endif
                                        @endif
                                    @endif
                                @endif
                               @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                @if ($fuel_tank_capacity < $quantity)
                                <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                @endif
                                @else   
                                @if ($selected_horse)
                                <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                @endif
                               
                               @endif
                               
                            </div>
                     
                       
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @if (isset($selected_trip))
                        @if ($selected_trip->transporter_agreement == True)
                            <div class="row">
                    
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_price">Retail Rate</label>
                                        <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="transporter_price" placeholder="Enter Transporter Price/Litre" />
                                        @error('transporter_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Retail Total</label>
                                        <input type="number" class="form-control" required="required" wire:model.debounce.300ms="transporter_total" placeholder="Enter Transporter Fuel Total" disabled/>
                                        @error('transporter_total') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                        
                            </div>
                        @endif
                    @endif
                

                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                     <div class="col-sm-10">
                                        <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                            <label for="one" class="radio-label">Horse</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                            <label for="one" class="radio-label">Vehicle</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                            <label for="one" class="radio-label">Asset</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                            <label for="one" class="radio-label">Other</label>
                                        </div>  
                                    </div>
                                    @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                         @if (isset($type) && $type == "Horse")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicles">Horses<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                       <option value="">Select Horse</option>
                                      @foreach ($horses as $horse)
                                          <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employees">Trips</label>
                                   <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                                       <option value="">Select Trip</option>
                                      @foreach ($trips as $trip)
                                      <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                   
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">  
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                        </div>
                                        @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @else
                                            <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @endif
                                    @endif
                                 
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @if (isset($selected_trip))
                        @if ($selected_trip->transporter_agreement == True)
                            <div class="row">
                    
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_price">Retail Rate</label>
                                        <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="transporter_price" placeholder="Enter Transporter Price/Litre" />
                                        @error('transporter_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Retail Total</label>
                                        <input type="number" class="form-control" required="required" wire:model.debounce.300ms="transporter_total" placeholder="Enter Transporter Fuel Total" disabled/>
                                        @error('transporter_total') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                        
                            </div>
                        @endif
                    @endif
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                        @elseif (isset($type) && $type == "Vehicle")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                       <option value="">Select Vehicle</option>
                                      @foreach ($vehicles as $vehicle)
                                          <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employees">Employees<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                       <option value="">Select Employee</option>
                                      @foreach ($employees as $employee)
                                          <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                      @endforeach
                                   </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Vehicle Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                                </div>
                            </div>
                        </div>
                     
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @else
                                            <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @endif
                                    @endif
                                 
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                                
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @if (isset($selected_trip))
                        @if ($selected_trip->transporter_agreement == True)
                            <div class="row">
                    
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_price">Retail Rate</label>
                                        <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="transporter_price" placeholder="Enter Transporter Price/Litre" />
                                        @error('transporter_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Retail Total</label>
                                        <input type="number" class="form-control" required="required" wire:model.debounce.300ms="transporter_total" placeholder="Enter Transporter Fuel Total" disabled/>
                                        @error('transporter_total') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                        
                            </div>
                        @endif
                    @endif
                      
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                    @elseif (isset($type) && $type == "Asset")
    

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategory" class="form-control" required >
                                   <option value="">Select Category</option>
                                  @foreach ($categories as $category)
                                      <option value="{{$category->id}}">{{$category->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Sub Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control" required >
                                   <option value="">Select Sub Category</option>
                                  @foreach ($category_values as $category_value)
                                      <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                                   <option value="">Select Asset</option>
                                   @if (!is_null($selectedCategory))
                                   @foreach ($assets as $asset)
                                      <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                  @endforeach
                                  @endif
                               </select>
                                @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                  @foreach ($containers as $container)
                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                  @endforeach
                               </select>
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                    @endif
                                @endif
                             
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if ($quantity > $container_balance)
                                        <small style="color:red">Quantity entered is greater than station balance</small>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
    
                    @elseif (isset($type) && $type == "Other")
                    <div class="form-group">
                        <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                           <option value="">Select Fueling Station</option>
                          @foreach ($containers as $container)
                              <option value="{{$container->id}}">{{$container->name}}</option>
                          @endforeach
                       </select>
                        @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                        @if (!is_null($selectedContainer) && isset($selected_container) )
                        @if ($selected_container->purchase_type == "Bulk Buy")
                            @if (isset($container_balance))
                                <br>
                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                            @endif
                        @endif 
                    @endif
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                    </div>
                                    @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @else
                                            <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @endif
                                    @endif
                                 
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                                <label for="file">Comments<span class="required" style="color: red">*</span></label>
                                    <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" required placeholder="Write fuel order comments..."></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelTopupModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i>Fuel Top Up  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeTopup()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                        <label for="one" class="radio-label">Horse</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                        <label for="one" class="radio-label">Vehicle</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                        <label for="one" class="radio-label">Asset</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                        <label for="one" class="radio-label">Other</label>
                                    </div>  
                                </div>
                                @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       
                         @if (isset($type) && $type == "Horse")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicles">Horses<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                       <option value="">Select Horse</option>
                                      @foreach ($horses as $horse)
                                          <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employees">Trips</label>
                                   <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                                       <option value="">Select Trip</option>
                                      @foreach ($trips as $trip)
                                      <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                     
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                        </div>
                                        @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  />
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                   @if (isset($trip_fuel) && isset($horse_fuel_total))
                                    @if ($trip_fuel > $horse_fuel_total)
                                    <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                    @endif
                                   @endif
                                   @if (isset($container_balance) && isset($quantity))
                                    @if ($container_balance < $quantity)
                                    <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                    @endif
                                   @endif
                                   @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                    @if ($fuel_tank_capacity < $quantity)
                                    <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                    @endif
                                    @else   
                                    @if ($selected_horse)
                                    <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                    @endif
                                   
                                   @endif
                                   
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                        @elseif (isset($type) && $type == "Vehicle")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                       <option value="">Select Vehicle</option>
                                      @foreach ($vehicles as $vehicle)
                                          <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employees">Employees<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                       <option value="">Select Employee</option>
                                      @foreach ($employees as $employee)
                                          <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                      @endforeach
                                   </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="employees">Trips</label>
                           <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                               <option value="">Select Trip</option>
                              @foreach ($trips as $trip)
                              <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                              @endforeach
                           </select>
                            @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Vehicle Mileage">
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                        @endif
                                    @endif 
                                @endif
                                </div>
                            </div>
                        </div>
                     
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @else
                                            <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @endif
                                    @endif
                                 
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                      
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                    @elseif (isset($type) && $type == "Asset")
    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategory" class="form-control" required >
                                   <option value="">Select Category</option>
                                  @foreach ($categories as $category)
                                      <option value="{{$category->id}}">{{$category->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Sub Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control" required >
                                   <option value="">Select Sub Category</option>
                                  @foreach ($category_values as $category_value)
                                      <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                                   <option value="">Select Asset</option>
                                   @if (!is_null($selectedCategory))
                                   @foreach ($assets as $asset)
                                      <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                  @endforeach
                                  @endif
                               </select>
                                @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                  @foreach ($containers as $container)
                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                  @endforeach
                               </select>
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                @if (!is_null($selectedContainer) && isset($selected_container) )
                                @if ($selected_container->purchase_type == "Bulk Buy")
                                    @if (isset($container_balance))
                                        <br>
                                        <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                    @endif
                                @endif 
                            @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                    @endif
                                @endif
                             
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($selected_container))
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if ($quantity > $container_balance)
                                        <small style="color:red">Quantity entered is greater than station balance</small>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
    
                    @elseif (isset($type) && $type == "Other")
                    <div class="form-group">
                        <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                           <option value="">Select Fueling Station</option>
                          @foreach ($containers as $container)
                              <option value="{{$container->id}}">{{$container->name}}</option>
                          @endforeach
                       </select>
                        @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                        @if (!is_null($selectedContainer) && isset($selected_container) )
                        @if ($selected_container->purchase_type == "Bulk Buy")
                            @if (isset($container_balance))
                                <br>
                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                            @endif
                        @endif 
                    @endif
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                    </div>
                                    @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                              
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @else
                                            <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity"  >
                                        @endif
                                    @endif
                                 
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($selected_container))
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                                <label for="file">Comments<span class="required" style="color: red">*</span></label>
                                    <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" required placeholder="Write fuel order comments..."></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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


</div>

