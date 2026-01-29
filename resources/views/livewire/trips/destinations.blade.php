<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        @if (Auth::user()->employee)
        @if ($trip->authorization == "approved")
        <a href="" data-toggle="modal" data-target="#trip_destinationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Offloading Point</a>
        @endif
        <br>
        <br>
        @endif
        {{-- <x-loading/> --}}
        <table id="trip_destinationsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">AddedBy
                </th>
                <th class="th-sm">Offloading Date
                </th>
                <th class="th-sm">Destination
                </th>
                <th class="th-sm">Offloading Point
                </th>
                <th class="th-sm">Weight
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Litreage @ Ambient
                </th>
                <th class="th-sm">Litreage @ 20 Degrees
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Freight
                </th>
              
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($trip_destinations as $trip_destination)
              <tr>
                <td>
                    {{ $trip_destination->user ? $trip_destination->user->name : '' }} {{ $trip_destination->user ? $trip_destination->user->surname : '' }}
                    <br>
                    <small><strong>AddedOn: </strong> {{ date('d M, Y', strtotime($trip_destination->created_at)) }}</small>
                </td>
                <td>{{$trip_destination->offloading_date}}</td>
                <td>
                    @if ($trip_destination->destination)
                        {{$trip_destination->destination->country ? $trip_destination->destination->country->name : ""}} {{$trip_destination->destination ? $trip_destination->destination->city : ""}}        
                    @endif
                </td>
                <td>{{$trip_destination->offloading_point ? $trip_destination->offloading_point->name : ""}}</td>
                <td>
                    @if ($trip_destination->weight)
                        {{number_format($trip_destination->weight,2)}} tons   
                    @endif
                </td>
                <td>
                    @if ($trip_destination->quantity)
                        {{number_format($trip_destination->quantity,2)}} {{$trip->measurement ? $trip->measurement : ""}}
                    @endif
                   </td>
                <td>
                    @if ($trip_destination->litreage)
                        {{number_format($trip_destination->litreage,2)}} {{$trip->measurement ? $trip->measurement : ""}}  
                    @endif
                </td>
                <td>
                    @if ($trip_destination->litreage)
                        {{number_format($trip_destination->litreage_at_20,2)}} {{$trip->measurement ? $trip->measurement : ""}}  
                    @endif
                </td>
                <td>
                    @if ($trip_destination->rate)
                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip_destination->rate,2)}} 
                    @endif
                </td>
                <td>
                    @if ($trip_destination->freight)
                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip_destination->freight,2)}} 
                    @endif
                </td>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                             @if ($trip_destination->user_id == Auth::user()->id)
                            <li><a href="#" wire:click="edit({{$trip_destination->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#trip_destinationDeleteModal{{$trip_destination->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                            @endif
                        </ul>
                    </div>
                    @include('trips.destinations.delete')

            </td>
            @endif
            </tr>
            
            @empty
            <tr>
                <td colspan="11">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Offloading Points Found ....
                    </div>
                   
                </td>
              </tr> 
            @endforelse
            @if (isset($trip_destinations))
            @php
                $total_weight = $trip_destinations->where('weight','!=', null)->where('weight','!=',"")->sum('weight');
                $total_quantity = $trip_destinations->where('quantity','!=', null)->where('quantity','!=',"")->sum('quantity');
                $total_litreage = $trip_destinations->where('litreage','!=', null)->where('litreage','!=',"")->sum('litreage');
                $total_litreage_at_20 = $trip_destinations->where('litreage_at_20','!=', null)->where('litreage_at_20','!=',"")->sum('litreage_at_20');
            @endphp
            <tr>
                <th colspan="4"><h5 style="margin-top:-5px;"><strong>Total</strong></h5></th>
                <td  >
                    @if (isset($total_weight) && $total_weight > 0)
                    <strong>{{ number_format($total_weight,2) }} Tons </strong> 
                    @endif
                </td>
                <td  >
                    @if (isset($total_quantity) && $total_quantity > 0)
                       <strong>{{ number_format($total_quantity,2) }} {{$trip->measurement ? $trip->measurement : ""}} </strong> 
                    @endif
                </td>
                <td >
                    @if (isset($total_litreage) && $total_litreage > 0)
                    <strong>{{ number_format($total_litreage,2) }} {{$trip->measurement ? $trip->measurement : ""}}</strong> 
                    @endif
                </td>
                <td >
                    @if (isset($total_litreage_at_20) && $total_litreage_at_20 > 0)
                    <strong>{{ number_format($total_litreage_at_20,2) }} {{$trip->measurement ? $trip->measurement : ""}}</strong>
                    @endif
                </td>
                <td></td>
                <td></td>
            </tr>
            @endif
          
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_destinationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="trip_destination">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Offloading Point(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    
                    <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Offloading Date<span class="required" style="color: red">*</span></label>
                            <input type="date"  class="form-control"  wire:model.debounce.300ms="offloading_date.0" placeholder="Offloading Date" required>
                            @error('offloading_date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="destination_id">Destinations<span class="required" style="color: red">*</span></label>
                            <select  class="form-control"  wire:model.debounce.300ms="destination_id.0" required>
                                <option value="">Select Destination</option>
                                @foreach ($destinations as $destination)
                                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                @endforeach
                            </select>
                            @error('destination_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                             <a href="#" wire:click.prevent="refresh('destinations')" class="float-end">
                                    <i class="fa fa-refresh" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="destination_id">Offloading Points<span class="required" style="color: red">*</span></label>
                            <select  class="form-control"  wire:model.debounce.300ms="offloading_point_id.0" required>
                                <option value="">Select Offloading Point</option>
                                @foreach ($offloading_points as $offloading_point)
                                    <option value="{{ $offloading_point->id }}">{{ $offloading_point->name }}</option>
                                @endforeach
                               
                            </select>
                            @error('offloading_point_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Point</a></small> 
                             <a href="#" wire:click.prevent="refresh('offloading_points')" class="float-end">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                  
                        </div>
                    <div class="row">
                        @if (isset($cargo))
                        @if ($cargo->type == "Solid")
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="expiry_date">Weight<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight.0" placeholder="Weight in tons" required>
                                @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="file">Quantity</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="quantity.0" placeholder="Enter Quantity">
                                @error('quantity.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file">Measurements</label>
                              <select class="form-control" wire:model.debounce.300ms="measurement_id.0" disabled>
                                <option value="">Select Measurement</option>
                                @foreach ($measurements as $measurement)
                                    <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                @endforeach
                              </select>
                                @error('measurement_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="file">Rate</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate.0" placeholder="$" >
                                @error('rate.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="file">Freight</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight.0" placeholder="$">
                                @error('freight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else    
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="expiry_date">Weight</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight.0" placeholder="Weight in tons">
                                @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file">Litreage @ Ambient</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage.0" placeholder="Litreage in litres">
                                @error('litreage.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage_at_20.0" placeholder="Litreage in litres" required>
                                @error('litreage_at_20.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="file">Rate</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate.0" placeholder="$" >
                                @error('rate.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="file">Freight</label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight.0" placeholder="$">
                                @error('freight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="expiry_date">Offloading Date<span class="required" style="color: red">*</span></label>
                                    <input type="date"  class="form-control"  wire:model.debounce.300ms="offloading_date.{{ $value }}" placeholder="Offloading Date" required>
                                    @error('offloading_date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destination_id">Destinations<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control"  wire:model.debounce.300ms="destination_id.{{ $value }}" required>
                                        <option value="">Select Destination</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('destination_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                      <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                                     <a href="#" wire:click.prevent="refresh('destinations')" class="float-end">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destination_id">Offloading Points<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control"  wire:model.debounce.300ms="offloading_point_id.{{ $value }}" required>
                                        <option value="">Select Offloading Point</option>
                                        @foreach ($offloading_points as $offloading_point)
                                            <option value="{{ $offloading_point->id }}">{{ $offloading_point->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('offloading_point_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                     <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Point</a></small> 
                                     <a href="#" wire:click.prevent="refresh('offloading_points')" class="float-end">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </a>
                                </div>
                            </div>
                            
                                </div>
                            <div class="row">
                                @if (isset($cargo))
                                @if ($cargo->type == "Solid")
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="expiry_date">Weight<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight.{{ $value }}" placeholder="Weight in tons" required>
                                        @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="file">Quantity<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="quantity.{{ $value }}" placeholder="Enter Quantity" required>
                                        @error('quantity.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="file">Measurements</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement_id.{{ $value }}" disabled>
                                        <option value="">Select Measurement</option>
                                        @foreach ($measurements as $measurement)
                                            <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                        @endforeach
                                      </select>
                                        @error('measurement_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="file">Rate</label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate.{{ $value }}" placeholder="$" >
                                        @error('rate.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="file">Freight</label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight.{{ $value }}" placeholder="$">
                                        @error('freight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @else   
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="expiry_date">Weight</label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight.{{ $value }}" placeholder="Litreage in tons" >
                                        @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div> 
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="file">Litreage @ Ambient<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage.{{ $value }}" placeholder="Litreage in litres" required>
                                        @error('litreage.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="file">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage_at_20.{{ $value }}" placeholder="Litreage in litres" required>
                                        @error('litreage_at_20.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="file">Rate</label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate.{{ $value }}" placeholder="$" >
                                        @error('rate.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="file">Freight</label>
                                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight.{{ $value }}" placeholder="$">
                                        @error('freight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @endif
                                @endif
                                  
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Destination</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_destinationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="trip_destination">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Offloading Point<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Offloading Date<span class="required" style="color: red">*</span></label>
                                <input type="date"  class="form-control"  wire:model.debounce.300ms="offloading_date" placeholder="Offloading Date" required>
                                @error('offloading_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="destination_id">Destinations<span class="required" style="color: red">*</span></label>
                                <select  class="form-control"  wire:model.debounce.300ms="destination_id" required>
                                    <option value="">Select Destination</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }}</option>
                                    @endforeach
                                   
                                </select>
                                @error('destination_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="destination_id">Offloading Points<span class="required" style="color: red">*</span></label>
                                <select  class="form-control"  wire:model.debounce.300ms="offloading_point_id" required>
                                    <option value="">Select Offloading Point</option>
                                    @foreach ($offloading_points as $offloading_point)
                                        <option value="{{ $offloading_point->id }}">{{ $offloading_point->name }}</option>
                                    @endforeach
                                   
                                </select>
                                @error('offloading_point') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                      
                            </div>
                        <div class="row">
                            @if (isset($cargo))
                            @if ($cargo->type == "Solid")
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="expiry_date">Weight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight" placeholder="Weight in tons" required>
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">Quantity<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required>
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Measurements</label>
                                  <select class="form-control" wire:model.debounce.300ms="measurement_id" disabled>
                                    <option value="">Select Measurement</option>
                                    @foreach ($measurements as $measurement)
                                        <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                    @endforeach
                                  </select>
                                    @error('measurement_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Rate</label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate" placeholder="$" >
                                    @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Freight</label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight" placeholder="$">
                                    @error('freight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @else    
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="expiry_date">Weight</label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="weight" placeholder="Weight in tons">
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">Litreage @ Ambient<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage" placeholder="Litreage in litres" required>
                                    @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="litreage_at_20" placeholder="Litreage in litres" required>
                                    @error('litreage_at_20') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Rate</label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="rate" placeholder="$" >
                                    @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Freight</label>
                                    <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="freight" placeholder="$">
                                    @error('freight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                            @endif
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

@section('extra-js')
<script>
    $(trip_destination).ready( function () {
        $('#trip_destinationsTable').DataTable();
    } );
    </script>
@endsection