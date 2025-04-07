<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title">
            <a href="#" data-toggle="modal" data-target="#trailerModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trailer</a>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Trailer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                   
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
                    <div class="form-group">
                        <label for="number">Registration Number<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="registration_number" placeholder="Enter Trailer Registration Number" required/>
                        @error('registration_number') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" min="1900" max="2099" step="1" class="form-control" wire:model.debounce.300ms="year" placeholder="Enter Trailer Year" />
                            @error('year') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="color"  placeholder="Enter Trailer Color " />
                        @error('color') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="color">No of wheels</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="no_of_wheels"  placeholder="Enter No Of Wheels " />
                        @error('no_of_wheels') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail13">Manufacturer</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="manufacturer" placeholder="Enter Manufacturer" />
                        @error('manufacturer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="contact13">Country of Origin</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="origin" placeholder="Enter Country of origin " />
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
</div>
