<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#offloadingPointModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Offloading Point</a>
                                <a href="" data-toggle="modal" data-target="#offloading_pointsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportOffloadingPointsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportOffloadingPointsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportOffloadingPointsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Offloading Points...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Consignee
                                    </th>
                                    <th class="th-sm">Contact
                                    </th>
                                    <th class="th-sm">Assessment Expires
                                    </th>
                                    <th class="th-sm">Latitude
                                    </th>
                                    <th class="th-sm">Longitude
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($offloading_points))
                                <tbody>
                                    @forelse ($offloading_points as $offloading_point)
                                  <tr>
                                    <td>
                                        @if ($offloading_point->location)
                                        <a href="{{$offloading_point->location}}" target="_blank"><i class="fa fa-map-marker"></i> {{ucfirst($offloading_point->name)}}</a>
                                        @else
                                        {{ucfirst($offloading_point->name)}}
                                        @endif
                                    </td>
                                    <td>{{$offloading_point->consignee ? $offloading_point->consignee->name : ""}}</td>
                                    <td>
                                        @if ($offloading_point->contact_name)
                                        <i class="fa fa-user"></i> {{ucfirst($offloading_point->contact_name)}} {{ucfirst($offloading_point->contact_surname)}}
                                        @endif
                                      
                                        <br>
                                        @if ($offloading_point->email)
                                        <i class="fa fa-envelope"></i> {{$offloading_point->email}}
                                        @endif
                                        @if ($offloading_point->phonenumber)
                                        <i class="fa fa-phone"></i> {{$offloading_point->phonenumber}}
                                        @endif
                                      
                                    </td>
                                    <td>
                                        @if ($offloading_point->expiry_date >= now()->toDateTimeString())
                                        <span class="badge bg-success">{{$offloading_point->expiry_date}}</span>
                                        @else
                                        <span class="badge bg-danger">{{$offloading_point->expiry_date}}</span>        
                                        @endif
                                    </td>
                                    <td>{{$offloading_point->lat}}</td>
                                    <td>{{$offloading_point->long}}</td>
                                    <td><span class="badge bg-{{$offloading_point->status == 1 ? "success" : "danger"}}">{{$offloading_point->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('offloading_points.show', $offloading_point->id) }}"   ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$offloading_point->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#offloadingPointDeleteModal{{ $offloading_point->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                
                                            </ul>
                                        </div>
                                        @include('offloading_points.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Offloading Points Found ....
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
                                    @if (isset($offloading_points))
                                        {{ $offloading_points->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="offloading_pointsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import  Offloading Point(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('offloading_points.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Offloading Point(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Offloading Points File" >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="offloadingPointModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Offloading Point <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Consignees</label>
                        <select class="form-control" wire:model.debounce.300ms="consignee_id">
                            <option value="">Select Consignee</option>
                            @foreach ($consignees as $consignee)
                                <option value="{{$consignee->id}}">{{$consignee->name}}</option>
                            @endforeach
                        </select>
                        <small>  <a href="{{ route('consignees.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Consignee</a></small> 
                        @error('consignee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" id="autocomplete" placeholder="Enter Name" required/>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name" placeholder="Enter Name" />
                                @error('contact_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname" placeholder="Enter Surname" />
                                @error('contact_surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <center> <small style="color: red"><a href="https://www.google.com/maps" target="_blank">Click me to go on Google Maps</a></small></center>
                    </div>
                    <div class="form-group">
                        <label for="description">Google Maps Location Pin<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Copy and Paste Offloading Point Location Pin from Google Maps" required>
                        @error('location') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Latitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="lat" placeholder="Enter Latitude" required disabled>
                                @error('lat') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Longitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="long" placeholder="Enter Longitude" required disabled>
                                @error('long') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date"  />
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="offloadingPointEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Offloading Point <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Consignees</label>
                        <select class="form-control" wire:model.debounce.300ms="consignee_id">
                            <option value="">Select Consignee</option>
                            @foreach ($consignees as $consignee)
                                <option value="{{$consignee->id}}">{{$consignee->name}}</option>
                            @endforeach
                        </select>
                        <small>  <a href="{{ route('consignees.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Consignee</a></small> 
                        @error('consignee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" id="autocomplete-edit" placeholder="Enter Name" required/>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name" placeholder="Enter Name" />
                                @error('contact_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname" placeholder="Enter Surname" />
                                @error('contact_surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <center> <small style="color: red"><a href="https://www.google.com/maps" target="_blank">Click me to go on Google Maps</a></small></center>
                    </div>
                    <div class="form-group">
                        <label for="description">Google Maps Location Pin<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Copy and Paste Offloading Point Location Pin from Google Maps" required>
                        @error('location') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Latitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="lat" placeholder="Enter Latitude" required disabled>
                                @error('lat') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Longitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="long" placeholder="Enter Longitude" required disabled>
                                @error('long') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date"  />
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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


    @push('custom-scripts')
    
    <script async defer 
        src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAPS_API_KEY')}}&libraries=places">
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initAutocomplete();
        });

        window.addEventListener('initializeAutocomplete', event => {
            document.getElementById('autocomplete-edit').value = event.detail.name;
            initAutocomplete();
        });

        function initAutocomplete() {
            let input = document.getElementById('autocomplete-edit');
            let autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                if (!place.geometry) {
                    console.log("No details available for input: '" + place.name + "'");
                    return;
                }

                Livewire.emit('setLocationData', {
                    name: place.name,
                    lat: place.geometry.location.lat(),
                    long: place.geometry.location.lng(),
                    location: place.url || ''
                });
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let input = document.getElementById('autocomplete');
            let autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                @this.set('name', place.name);
                @this.set('lat', place.geometry.location.lat());
                @this.set('long', place.geometry.location.lng());
                @this.set('location', place.url || '');
            });
        });
    </script>
    @endpush


</div>

