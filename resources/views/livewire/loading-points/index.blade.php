<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                       
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#loadingPointModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Loading Point</a>
                                <a href="" data-toggle="modal" data-target="#loading_pointsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportLoadingPointsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportLoadingPointsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportLoadingPointsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Loading Points...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Name
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
                                @if (isset($loading_points))
                                <tbody>
                                    @forelse($loading_points as $loading_point)
                                  <tr>
                                    <td>
                                        @if ($loading_point->location)
                                        <a href="{{$loading_point->location}}" target="_blank"><i class="fa fa-map-marker"></i> {{ucfirst($loading_point->name)}}</a>
                                        @else
                                        {{ucfirst($loading_point->name)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($loading_point->contact_name)
                                        <i class="fa fa-user"></i> {{ucfirst($loading_point->contact_name)}} {{ucfirst($loading_point->contact_surname)}}
                                        @endif
                                      
                                        <br>
                                        @if ($loading_point->email)
                                        <i class="fa fa-envelope"></i> {{$loading_point->email}}
                                        @endif
                                        @if ($loading_point->phonenumber)
                                        <i class="fa fa-phone"></i> {{$loading_point->phonenumber}}
                                        @endif
                                      
                                    </td>
                                    <td>
                                        @if ($loading_point->expiry_date >= now()->toDateTimeString())
                                        <span class="badge bg-success">{{$loading_point->expiry_date}}</span>
                                        @else
                                        <span class="badge bg-danger">{{$loading_point->expiry_date}}</span>        
                                        @endif
                                    </td>
                                    <td>  {{$loading_point->lat}}</td>
                                    <td>  {{$loading_point->long}}</td>
                                    <td><span class="badge bg-{{$loading_point->status == 1 ? "success" : "danger"}}">{{$loading_point->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('loading_points.show', $loading_point->id) }}"   ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$loading_point->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#loading_pointDeleteModal{{ $loading_point->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            
                                            </ul>
                                        </div>
                                        @include('loading_points.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Loading Points Found ....
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
                                    @if (isset($loading_points))
                                        {{ $loading_points->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loading_pointsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import  Loading Point(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('loading_points.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Loading Point(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Loading Points File" >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loadingPointModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Loading Point <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" id="autocomplete" placeholder="Enter Name" required />
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
                        <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Copy and Paste Loading Point Location Pin from Google Maps" required>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loadingPointEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Loading Point <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
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
                        <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Copy and Paste Loading Point Location Pin from Google Maps" required>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date"  />
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                         <div class="form-group">
                            <label for="country">Status<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="status" required>
                                <option value="">Select Option</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
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

