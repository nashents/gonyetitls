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
                                <a href="#" data-toggle="modal" data-target="#routeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Route</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search routes by name or rank...">
                                </div>
                            </div>

                            <table id="routesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">
                                        From
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        To
                                    </th>
                                    <th class="th-sm">Expenses
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm" title="Estimated fuel cost for the full route, using distance x consumption rate x fuel price">Est. Fuel Cost
                                    </th>
                                    <th class="th-sm" title="Sum of all preset route expenses, including fuel">Est. Total Cost
                                    </th>
                                    <th class="th-sm" title="Cost per Kilometre - standard freight/logistics costing KPI (Est. Total Cost / Distance)">Cost/Km (CPK)
                                    </th>
                                    <th class="th-sm">Assessment Expires
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($routes->count()>0)
                                <tbody>
                                    @foreach ($routes as $route)
                                    @php
                                        $from = $destinationsById->get($route->from);
                                        $to = $destinationsById->get($route->to);
                                        $expiry = $route->expiry_date ? \Carbon\Carbon::parse($route->expiry_date) : null;
                                    @endphp
                                  <tr>
                                    <td>{{ucfirst($route->name)}}</td>
                                    <td>
                                        @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{$from->city}}
                                        @endif
                                        <hr style="margin-top:5px; margin-bottom:5px">
                                        @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{$to->city}}
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($route->route_expenses as $route_expense)
                                            {{$route_expense->expense ? $route_expense->expense->name : ""}}@if ($route_expense->source == 'fuel') <span class="badge bg-info">Auto</span>@endif @if (!$loop->last), @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        <strong>Distance:</strong> {{$route->distance ? $route->distance."Kms" : ""}} <br>
                                        <strong>Tollgates:</strong> {{$route->tollgates}} <br>
                                        <strong>Rank:</strong> {{$route->rank}} <br>
                                        <strong>Borders:</strong>

                                                @foreach ($route->borders as $border)
                                                    {{$border->name}} @if (!$loop->last), @endif
                                                @endforeach

                                    </td>
                                    <td>
                                        @if (!is_null($route->fuel_cost))
                                            {{number_format($route->fuel_cost,2)}} {{$route->fuel_currency->name ?? ""}}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>{{number_format($route->estimated_expense_total,2)}}</td>
                                    <td>
                                        @if (!is_null($route->cost_per_km))
                                            {{number_format($route->cost_per_km,2)}}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!$expiry)
                                            <span class="badge bg-secondary">Not set</span>
                                        @elseif ($expiry->isPast())
                                            <span class="badge bg-danger">{{$route->expiry_date}}</span>
                                        @elseif ($expiry->lessThanOrEqualTo(now()->addDays(30)))
                                            <span class="badge bg-warning">{{$route->expiry_date}} (Expiring Soon)</span>
                                        @else
                                            <span class="badge bg-success">{{$route->expiry_date}}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$route->status == 1 ? "success" : "danger"}}">{{$route->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('routes.show',$route->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$route->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#routeDeleteModal{{ $route->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('routes.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                                {{ $routes->links() }}

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="routeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Route <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Route Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Route Descriptive Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">From<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="from" class="form-control" required >
                                   <option value="">Select From Location</option>
                                   @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                   @endforeach
                               </select>
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from">To<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="to" class="form-control" required >
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Distance</label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter route distance"/>
                                @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Number of Tollgates</label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="tollgates" placeholder="Enter number of tollgates"/>
                                @error('tollgates') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Consumption Rate (L/100km)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_consumption_rate" placeholder="e.g. 35"/>
                                @error('fuel_consumption_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Price per Litre</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_price_per_litre" placeholder="Enter current fuel price"/>
                                @error('fuel_price_per_litre') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Currency</label>
                                <select wire:model.debounce.300ms="fuel_currency_id" class="form-control" >
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                @error('fuel_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($distance && $fuel_consumption_rate && $fuel_price_per_litre)
                        <div class="col-md-12">
                            <small class="text-muted">Estimated Fuel Cost: {{ number_format(((float)$distance/100)*(float)$fuel_consumption_rate*(float)$fuel_price_per_litre,2) }} — this is auto-saved as a preset "Fuel" expense on the route.</small>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stops"><a href="{{ route('borders.index') }}" target="_blank" style="color: blue">Border(s)</a></label>
                                <select class="form-control" wire:model.debounce.300ms="border_id.0" >
                                    <option value="">Select Border </option>
                                      @foreach ($borders as $border)
                                          <option value="{{$border->id}}">{{$border->name}}</option>
                                      @endforeach
                                  </select>
                                  <small><a href="{{ route('borders.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Border</a></small> <a href="#" wire:click.prevent="refresh('borders')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                  @error('border_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>

                                @foreach ($inputs as $key => $value)
                                <div class="row">
                                    <div class="col-md-9">
                                        <select class="form-control" wire:model.debounce.300ms="border_id.{{ $value }}" >
                                            <option value="">Select Border</option>
                                            
                                              @foreach ($borders as $border)
                                                  <option value="{{$border->id}}">{{$border->name}}</option>
                                              @endforeach
                                         
                                          </select>
                                         
                                        @error('border_id.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for=""></label>
                                            <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>

                                </div>
                              
                                @endforeach
                           
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-rounded btn-sm" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Border</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Route Rank</label>
                               <select wire:model.debounce.300ms="rank" class="form-control" >
                                   <option value="">Select Route Rank</option>
                                   <option value="Very Good">Very Good</option>
                                   <option value="Good">Good</option>
                                   <option value="Ok">Ok</option>
                                   <option value="Bad">Bad</option>
                                   <option value="Very Bad">Very Bad</option>
                               </select>
                                @error('rank') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Risk Assessment Expiry Date</label>
                            <input type="date" min="1" class="form-control" wire:model.debounce.300ms="expiry_date" placeholder="Risk Assessment Expiry Date"/>
                            @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Description</label>
                         <textarea wire:model.debounce.300ms="description" placeholder="Enter comments on route" cols="30" rows="5" class="form-control"></textarea>
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="routeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Route <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Route Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Route Descriptive Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">From<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="from" class="form-control" required >
                                   <option value="">Select From Location</option>
                                   @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                   @endforeach
                               </select>
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from">To<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="to" class="form-control" required >
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                    @endforeach
                                </select>
                                    @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Distance</label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter route distance"/>
                                @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Number of Tollgates</label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="tollgates" placeholder="Enter number of tollgates"/>
                                @error('tollgates') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Consumption Rate (L/100km)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_consumption_rate" placeholder="e.g. 35"/>
                                @error('fuel_consumption_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Price per Litre</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_price_per_litre" placeholder="Enter current fuel price"/>
                                @error('fuel_price_per_litre') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Fuel Currency</label>
                                <select wire:model.debounce.300ms="fuel_currency_id" class="form-control" >
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                @error('fuel_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($distance && $fuel_consumption_rate && $fuel_price_per_litre)
                        <div class="col-md-12">
                            <small class="text-muted">Estimated Fuel Cost: {{ number_format(((float)$distance/100)*(float)$fuel_consumption_rate*(float)$fuel_price_per_litre,2) }} — this is auto-saved as a preset "Fuel" expense on the route.</small>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stops"><a href="{{ route('borders.index') }}" target="_blank" style="color: blue">Border(s)</a></label>
                                <select class="form-control" wire:model.debounce.300ms="border_id" multiple >
                                    <option value="">Select Border </option>
                                      @foreach ($borders as $border)
                                          <option value="{{$border->id}}">{{$border->name}}</option>
                                      @endforeach
                                  </select>
                                  <small><a href="{{ route('borders.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Border</a></small> 
                                  @error('border_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Route Rank</label>
                               <select wire:model.debounce.300ms="rank" class="form-control" >
                                   <option value="">Select Route Rank</option>
                                   <option value="Very Good">Very Good</option>
                                   <option value="Good">Good</option>
                                   <option value="Ok">Ok</option>
                                   <option value="Bad">Bad</option>
                                   <option value="Very Bad">Very Bad</option>
                               </select>
                                @error('rank') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Risk Assessment Expiry Date</label>
                            <input type="date" min="1" class="form-control" wire:model.debounce.300ms="expiry_date" placeholder="Risk Assessment Expiry Date"/>
                            @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Status</label>
                           <select wire:model.debounce.300ms="status" class="form-control" >
                               <option value="">Select Status</option>
                               <option value="1">Active</option>
                               <option value="0">Inactive</option>
                           </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="country">Description</label>
                 <textarea wire:model.debounce.300ms="description" placeholder="Enter comments on route" cols="30" rows="5" class="form-control"></textarea>
                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

