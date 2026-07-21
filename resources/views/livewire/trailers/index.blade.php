<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                                    <a href="#" data-toggle="modal" data-target="#trailerModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trailer</a>
                                    <a href="" data-toggle="modal" data-target="#trailersImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportTrailersExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportTrailersCSV()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportTrailersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                    @if ($this->sageEnabled)
                                    <button wire:click="bulkSyncToSage" wire:loading.attr="disabled" class="btn btn-default border-success btn-rounded btn-wide"><i class="fa fa-cloud-upload"></i>Sync selected to Sage</button>
                                    @endif
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search trailers...">
                                    </div>
                                </div>
                                <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Trailer#
                                        </th>
                                        <th class="th-sm">Transporter
                                        </th>
                                        <th class="th-sm">Make
                                        </th>
                                        <th class="th-sm">
                                            (Fleet#) HRN
                                        </th>
                                        <th class="th-sm">Year
                                        </th>
                                        <th class="th-sm">Availability
                                        </th>
                                        <th class="th-sm">Service
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($trailers))
                                    <tbody>
                                        @forelse ($trailers as $trailer)
                                      <tr>
                                        <td>
                                            @if ($this->sageEnabled)
                                            <input type="checkbox" wire:model="sageSelected" value="{{ $trailer->id }}" title="Select for Sage bulk sync">
                                            @endif
                                            {{$trailer->trailer_number}}
                                            @if ($trailer->custom_ref)
                                            <br>
                                                <small>Custom Ref:{{$trailer->custom_ref}}</small>
                                            @endif
                                            @if ($this->sageEnabled)
                                            <br>
                                            @php $sm = $trailer->sageMapping; @endphp
                                            <small class="badge bg-{{ $sm ? ($sm->sync_status === 'synced' ? 'success' : ($sm->sync_status === 'failed' ? 'danger' : ($sm->sync_status === 'requires_attention' ? 'warning' : 'secondary'))) : 'secondary' }}"
                                                   title="{{ $sm->last_error ?? '' }}">Sage: {{ $sm ? ucwords(str_replace('_',' ', $sm->sync_status)) : 'Not synced' }}</small>
                                            @endif
                                        </td>
                                        <td>{{$trailer->transporter ? $trailer->transporter->name : ""}}</td>
                                        <td>{{$trailer->make}} {{$trailer->model}}</td>
                                         <td width="150">
                                            {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}} {{ucfirst($trailer->registration_number)}}
                                        </td>
                                        <td>{{$trailer->year}}</td>
                                        <td><span class="badge bg-{{$trailer->status == 1 ? "success" : "danger"}}">{{$trailer->status == 1 ? "Available" : "Unavailable"}}</span></td>
                                        <td><span class="badge bg-{{$trailer->service == 0 ? "success" : "danger"}}">{{$trailer->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trailers.show', $trailer->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="#" wire:click="edit({{$trailer->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    @if ($this->sageEnabled)
                                                    @php $ss = optional($trailer->sageMapping)->sync_status; @endphp
                                                    @if ($ss === 'synced')
                                                    <li><a href="#" wire:click.prevent="syncToSage({{$trailer->id}})" wire:loading.attr="disabled"><i class="fa fa-refresh color-success"></i> Re-sync to Sage</a></li>
                                                    @elseif (in_array($ss, ['failed','requires_attention']))
                                                    <li><a href="#" wire:click.prevent="retrySync({{$trailer->id}})" wire:loading.attr="disabled"><i class="fa fa-refresh color-warning"></i> Retry Sage Sync</a></li>
                                                    @else
                                                    <li><a href="#" wire:click.prevent="syncToSage({{$trailer->id}})" wire:loading.attr="disabled"><i class="fa fa-cloud-upload color-primary"></i> Sync to Sage</a></li>
                                                    @endif
                                                    @endif
                                                    <li><a href="#" data-toggle="modal" data-target="#trailerDeleteModal{{$trailer->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @if ($trailer->status == 1)
                                                    <li><a href="{{route('trailers.deactivate',$trailer->id)}}"  ><i class="fa fa-toggle-on color-danger"></i>Deactivate</a></li>
                                                    @else
                                                    <li><a href="{{route('trailers.activate',$trailer->id)}}" ><i class="fa fa-toggle-off color-success"></i>Activate</a></li>
                                                    @endif
                                                    @if ($trailer->service == 1)
                                                    <li><a href="{{route('trailers.service', $trailer->id)}}"  ><i class="fa fa-remove color-success"></i>Close Ticket(s)</a></li>
                                                    @endif
                                                    @if ($trailer->archive == 0)
                                                    <li><a href="{{route('trailers.archive', $trailer->id)}}"  ><i class="fa fa-archive color-primary"></i>Archive</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('trailers.delete')

                                    </td>
                                      </tr>
                                      @empty
                                        <tr>
                                            <td colspan="10">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Trailers Found ....
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
                                        @if (isset($trailers))
                                            {{ $trailers->links() }} 
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailersImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Trailers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('trailers.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Trailer(s) Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload Trailer File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button tonClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog mw-100 w-50" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Trailer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Transporters<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="transporter_id" class="form-control" required>
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> 
                                    @error('transporter_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trailer_type">Trailer Types<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="trailer_type_id" class="form-control" required >
                                        <option value="">Select Trailer Type</option>
                                        @foreach ($trailer_types as $trailer_type)
                                            <option value="{{$trailer_type->id}}">{{$trailer_type->name}}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('trailer_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Trailer Type</a></small> 
                                    @error('trailer_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <label for="make">Make</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="make" placeholder="Enter Make Name"/>
                                    @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model">Model</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="model" placeholder="Enter Model Name"/>
                                    @error('model') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter Trailer Registration Number" required/>
                                    @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Fleet Number</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter Fleet Number" />
                                    @error('fleet_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Chasis#</label>
                                    <input type="text"  class="form-control" wire:model.debounce.300ms="chasis_number" placeholder="Enter Chasis#" />
                                    @error('chasis_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Trailer Year" />
                                    @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Trailer Color " />
                                    @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">GVM</label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="gvm"  placeholder="Enter GVM in Kgs " />
                                    @error('gvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">NVM</label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="nvm"  placeholder="Enter NVM in Kgs " />
                                    @error('nvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                   <label for="year">Cargo Type<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="cargo_type" required>
                                    <option value="">Select Cargo Type</option>
                                    <option value="Solid">Solid</option>
                                    <option value="Liquid">Liquid</option>
                                   </select>
                                       @error('cargo_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                   </div>
                           </div>
                           <div class="col-md-6">
                            <div class="form-group">
                                <label for="color">Compartments Information</label>
                                <textarea class="form-control" wire:model.debounce.300ms="compartments" cols="30" rows="4" placeholder="Enter Total Compartments & Compartments Breakdown"></textarea>
                                @error('compartments') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>

                        <h5 class="underline mt-n">Capacity Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Cargos</label>
                                    <select class="form-control" wire:model.debounce.300ms="cargo_id.0">
                                     <option value="">Select Cargo</option>
                                     @foreach ($cargos as $cargo)
                                     <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> 
                                    @error('cargo_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="capacity.0"  placeholder="Trailer Capacity " required/>
                                    @error('capacity.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Measurement<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="measurement_id.0" required>
                                     <option value="">Select Measurement</option>
                                     @foreach ($measurements as $measurement)
                                     <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                    @error('measurement_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @foreach ($capacity_inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Cargos</label>
                                    <select class="form-control" wire:model.debounce.300ms="cargo_id.{{$value}}">
                                     <option value="">Select Cargo</option>
                                     @foreach ($cargos as $cargo)
                                     <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                     @endforeach
                                    </select>
                                    @error('cargo_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="capacity.{{$value}}"  placeholder="Trailer Capacity " required />
                                    @error('capacity.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Measurement<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="measurement_id.{{$value}}" required>
                                     <option value="">Select Measurement</option>
                                     @foreach ($measurements as $measurement)
                                     <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                    @error('measurement_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="capacityRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="capacityAdd({{$c}})"> <i class="fa fa-plus"></i> Capacity</button>
                                </div>
                            </div>
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
                                    <label for="color">No of wheels</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No Of Wheels " />
                                    @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Manufacturer</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" />
                                    @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact13">Country of Origin</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " />
                                    @error('origin') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Condition</label>
                                    <select wire:model.debounce.300ms="condition" class="form-control" >
                                        <option value="">Select Trailer Condition</option>
                                        <option value="new">New</option>
                                        <option value="second hand">Second Hand</option>
                                        <option value="accident damaged">Accident Damaged</option>
                                    </select>
                                    @error('condition') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Suspension Type</label>
                                    <select wire:model.debounce.300ms="suspension_type" class="form-control" >
                                        <option value="">Select Suspension Type</option>
                                        <option value="Henred">Henred</option>
                                        <option value="Load">Load</option>
                                        <option value="SPW">SPW</option>
                                        <option value="ROR">ROR</option>
                                    </select>
                                    @error('suspension_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    
                            <div class="form-group">
                                <label for="model">Trailer Image(s)</label>
                                <small style="color:red;">Accepted image types: jpg , jpeg , png</small>
                                <input type="file" accept="image" wire:model.debounce.300ms="images" class="form-control" multiple>
                                @error('images') <span class="text-danger error">{{ $message }}</span>@enderror
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailersImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Trailers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('trailers.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Trailer(s) Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload Trailer File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailerEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog mw-100 w-50" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Trailers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Transporters<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="transporter_id" class="form-control" required>
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('transporter_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trailer_type">Trailer Types<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="trailer_type_id" class="form-control" required >
                                        <option value="">Select Trailer Type</option>
                                        @foreach ($trailer_types as $trailer_type)
                                            <option value="{{$trailer_type->id}}">{{$trailer_type->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('trailer_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <label for="make">Make<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="make" placeholder="Enter Make Name" required/>
                                    @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model">Model<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="model" placeholder="Enter Model Name" required/>
                                    @error('model') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter Trailer Registration Number" required/>
                                    @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">Fleet Number</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter Fleet Number" />
                                    @error('fleet_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Chasis#</label>
                                    <input type="text"  class="form-control" wire:model.debounce.300ms="chasis_number" placeholder="Enter Chasis#" />
                                    @error('chasis_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Trailer Year" />
                                    @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Trailer Color " />
                                    @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
            

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">GVM</label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="gvm"  placeholder="Enter GVM in Kgs " />
                                    @error('gvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">NVM</label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="nvm"  placeholder="Enter NVM in Kgs " />
                                    @error('nvm') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                   <label for="year">Cargo Type<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="cargo_type" required>
                                    <option value="">Select Cargo Type</option>
                                    <option value="Solid">Solid</option>
                                    <option value="Liquid">Liquid</option>
                                   </select>
                                       @error('cargo_type') <span class="text-danger error">{{ $message }}</span>@enderror
                                   </div>
                           </div>
                           <div class="col-md-6">
                            <div class="form-group">
                                <label for="color">Compartments Information</label>
                                <textarea class="form-control" wire:model.debounce.300ms="compartments" cols="30" rows="4" placeholder="Enter Total Compartments & Compartments Breakdown"></textarea>
                                @error('compartments') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>

                        <h5 class="underline mt-n">Capacity Information</h5>
                        @if (isset($trailer_capacities) && $trailer_capacities->count() > 0)
                        @foreach ($trailer_capacities as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Cargos</label>
                                    <select class="form-control" wire:model.debounce.300ms="cargo_id.{{$key}}">
                                     <option value="">Select Cargo</option>
                                     @foreach ($cargos as $cargo)
                                     <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> 
                                    @error('cargo_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="capacity.{{$key}}"  placeholder="Trailer Capacity " required/>
                                    @error('capacity.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Measurement<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="measurement_id.{{$key}}" required>
                                     <option value="">Select Measurement</option>
                                     @foreach ($measurements as $measurement)
                                     <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                    @error('measurement_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        @else
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Cargos</label>
                                    <select class="form-control" wire:model.debounce.300ms="cargo_id.0">
                                     <option value="">Select Cargo</option>
                                     @foreach ($cargos as $cargo)
                                     <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> 
                                    @error('cargo_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="capacity.0"  placeholder="Trailer Capacity " required/>
                                    @error('capacity.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Measurement<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="measurement_id.0" required>
                                     <option value="">Select Measurement</option>
                                     @foreach ($measurements as $measurement)
                                     <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                    @error('measurement_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @foreach ($capacity_inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Cargos</label>
                                    <select class="form-control" wire:model.debounce.300ms="cargo_id.{{$value}}">
                                     <option value="">Select Cargo</option>
                                     @foreach ($cargos as $cargo)
                                     <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                     @endforeach
                                    </select>
                                    @error('cargo_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="file">Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="capacity.{{$value}}"  placeholder="Trailer Capacity " required />
                                    @error('capacity.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">Measurement<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="measurement_id.{{$value}}" required>
                                     <option value="">Select Measurement</option>
                                     @foreach ($measurements as $measurement)
                                     <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                     @endforeach
                                    </select>
                                    <small><a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                    @error('measurement_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="capacityRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="capacityAdd({{$c}})"> <i class="fa fa-plus"></i> Capacity</button>
                                </div>
                            </div>
                        </div>
                        @endif
                
            
              


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
                                    <label for="color">No of wheels</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No Of Wheels " />
                                    @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Manufacturer</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" />
                                    @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact13">Country of Origin</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " />
                                    @error('origin') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Condition</label>
                                    <select wire:model.debounce.300ms="condition" class="form-control" >
                                        <option value="">Select Trailer Condition</option>
                                        <option value="new">New</option>
                                        <option value="second hand">Second Hand</option>
                                        <option value="accident damaged">Accident Damaged</option>
                                    </select>
                                    @error('condition') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Suspension Type</label>
                                    <select wire:model.debounce.300ms="suspension_type" class="form-control" >
                                        <option value="">Select Suspension Type</option>
                                        <option value="Henred">Henred</option>
                                        <option value="Load">Load</option>
                                        <option value="SPW">SPW</option>
                                        <option value="ROR">ROR</option>
                                    </select>
                                    @error('suspension_type') <span class="text-danger error">{{ $message }}</span>@enderror
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


    </div>
