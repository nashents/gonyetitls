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
                                <div class="panel-title" >
                                    <a href="" data-toggle="modal" data-target="#trailersImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportTrailersExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportTrailersCSV()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportTrailersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>

                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                                <table id="trailersTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">Trailer#
                                            </th>
                                            <th class="th-sm">Fleet#
                                            </th>
                                            <th class="th-sm">Transporter
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">Model
                                            </th>
                                            <th class="th-sm">TRN
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
                                    @if ($trailers->count()>0)
                                    <tbody>
                                        @foreach ($trailers as $trailer)
                                        <tr>
                                            <td>{{$trailer->trailer_number}}</td>
                                            <td>{{$trailer->fleet_number}}</td>
                                            <td>{{$trailer->transporter ? $trailer->transporter->name : ""}}</td>
                                            <td>{{$trailer->make}}</td>
                                            <td>{{$trailer->model}}</td>
                                            <td>{{$trailer->registration_number}}</td>
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
                                                        <li><a href="{{ route('trailers.edit',$trailer->id) }}" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#trailerDeleteModal{{$trailer->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                        @if ($trailer->status == 1)
                                                        <li><a href="{{route('trailers.deactivate',$trailer->id)}}"  ><i class="fa fa-toggle-off color-danger"></i>Deactivate</a></li>
                                                        @else
                                                        <li><a href="{{route('trailers.activate',$trailer->id)}}" ><i class="fa fa-toggle-on color-success"></i>Activate</a></li>
                                                        @endif

                                                    </ul>
                                                </div>
                                                @include('trailers.delete')

                                        </td>
                                          </tr>
                                      @endforeach
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif

                                  </table>

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
                            <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailerEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-trailer"></i> Edit Trailer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
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
                        <div class="form-group">
                            <label for="number">Fleet Number</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="fleet_number" placeholder="Enter Fleet Number" />
                            @error('fleet_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="make">Make<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="make" placeholder="Enter Make Name" required/>
                            @error('make') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="model">Model<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="model" placeholder="Enter Model Name" required />
                            @error('model') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter Trailer Registration Number" required/>
                            @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="year">Year<span class="required" style="color: red">*</span></label>
                            <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Trailer Year" required/>
                                @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Trailer Color " />
                            @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="color">No of wheels<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No Of Wheels " required />
                            @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail13">Manufacturer</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" >
                            @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="contact13">Country of Origin</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " >
                            @error('origin') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
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
