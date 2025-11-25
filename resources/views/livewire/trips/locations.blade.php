<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        @if (Auth::user()->employee)
        @if ($trip->authorization == "approved")
        <a href="" data-toggle="modal" data-target="#locationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Location Update</a>
        @endif
        <br>
        <br>
        @endif
        {{-- <x-loading/> --}}
        <table id="trip_locationsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Country
                </th>
                <th class="th-sm">City
                </th>
                <th class="th-sm">Suburb
                </th>
                <th class="th-sm">Street Address
                </th>
                <th class="th-sm">Description
                </th>
                <th class="th-sm">Updated@
                </th>
                <th class="th-sm">Location Pin
                </th>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "driver" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($trip_locations as $trip_location)
              <tr>
                <td>{{$trip_location->country ? $trip_location->country->name : ""}}</td>
                <td>{{$trip_location->city}}</td>
                <td>{{$trip_location->suburb}}</td>
                <td>{{$trip_location->street_address}}</td>
                <td>{{$trip_location->description}}</td>
                <td>{{$trip_location->created_at}}</td>
                <td>[{{$trip_location->location_pin ? $trip_location->location_pin->country : ""}}, {{$trip_location->location_pin ? $trip_location->location_pin->city : ""}}, {{$trip_location->location_pin ? $trip_location->location_pin->region : ""}}, Lat: {{$trip_location->location_pin ? $trip_location->location_pin->latitude : ""}}, Long:  {{$trip_location->location_pin ? $trip_location->location_pin->longitude : ""}}, Zip Code: {{$trip_location->location_pin ? $trip_location->location_pin->zipcode : ""}}]</td>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="edit({{$trip_location->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#trip_locationDeleteModal{{$trip_location->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('trip_locations.delete')

            </td>
            @endif
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Location Updates ....
                    </div>
                   
                </td>
              </tr> 
            @endforelse
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="trip_location">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Update <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Countries<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="country_id" required>
                                        <option value="">Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                    </select>
                                @error('country_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">City/Town<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" required/>
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Suburb</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="suburb">
                            @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Street Address</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="street_address">
                            @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   </div>
                    <div class="form-group">
                        <label for="">Details</label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write update details... "></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="locationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="trip_location">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Location Update <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Countries<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="country_id" required>
                                        <option value="">Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                    </select>
                                @error('country_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">City/Town<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" required/>
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Suburb</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="suburb">
                            @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Street Address</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="street_address">
                            @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   </div>
                    <div class="form-group">
                        <label for="">Details</label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write update details... "></textarea>
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
@section('extra-js')
<script>
    $(document).ready( function () {
        $('#trip_locationsTable').DataTable();
    } );
    </script>
@endsection
