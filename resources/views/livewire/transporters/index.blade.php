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
                                <a href="" data-toggle="modal" data-target="#transporterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transporter</a>
                                <a href="" data-toggle="modal" data-target="#transportersImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportTransportersExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportTransportersCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportTransportersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search transporters...">
                                </div>
                              
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter#
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Phonenumber
                                    </th>
                                    <th class="th-sm">Address
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($transporters))
                                <tbody>
                                    @forelse ($transporters as $transporter)
                                  <tr>
                                    <td>
                                        {{$transporter->transporter_number}}
                                          @if ($transporter->custom_ref)
                                          <br>
                                            <small><strong>Custom Ref:</strong> {{$transporter->custom_ref}}</small>
                                        @endif
                                    </td>
                                    <td>{{ucfirst($transporter->name)}}</td>
                                    <td>{{$transporter->email}}</td>
                                    <td>{{$transporter->phonenumber}}</td>
                                    <td>
                                      
                                        @if ($transporter->street_address)
                                        {{$transporter->street_address}}
                                        @endif
                                        @if ($transporter->suburb)
                                        {{$transporter->suburb}}, 
                                        @endif
                                        @if ($transporter->city)
                                        {{$transporter->city}},
                                        @endif
                                        @if ($transporter->country)
                                        {{$transporter->country}},
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{($transporter->authorization == 'approved') ? 'success' : (($transporter->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($transporter->authorization == 'approved') ? 'approved' : (($transporter->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td><span class="badge bg-{{$transporter->status == 1 ? "success" : "danger"}}">{{$transporter->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('transporters.show', $transporter->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="edit({{$transporter->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#transporterDeleteModal{{ $transporter->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('transporters.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Transporters Found ....
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
                                        @if (isset($transporters))
                                            {{ $transporters->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transportersImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Transporters <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('transporters.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Transporter(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Transporter File" >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transporterModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Transporter <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="worknumber">Landline</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="worknumber" placeholder="Enter Landline" />
                                @error('worknumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Contacts Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.0" placeholder="Enter Name"  />
                                @error('contact_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.0" placeholder="Enter Surname"  />
                                @error('contact_surname.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.0" placeholder="Enter Email" />
                                @error('contact_email.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.0" placeholder="Enter Mobile Number"  />
                                @error('contact_phonenumber.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="department.0" placeholder="Enter Department"  />
                                @error('department.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @foreach ($contacts_inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.{{ $value }}" placeholder="Enter Name" />
                                @error('contact_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.{{ $value }}" placeholder="Enter Surname" />
                                @error('contact_surname.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.{{ $value }}" placeholder="Enter Email" />
                                @error('contact_email.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.{{ $value }}" placeholder="Enter Mobile#"  />
                                @error('contact_phonenumber.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="department.{{ $value }}" placeholder="Enter Dpt"  />
                                @error('department.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for=""></label>
                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="contactsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="contactsAdd({{$o}})"> <i class="fa fa-plus"></i> Contact</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country"  />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="city" placeholder="Enter City"  />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address" />
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <br>
                   
                    <div style="height: 400px; overflow: auto">
                        <div class="form-group">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Specify Transporter Cargos<span class="required" style="color: red">*</span></th>
                                  </tr>
                                </thead>
                                @if ($cargos->count()>0)
                                <tbody>
                                    @foreach ($cargos as $cargo)
                                  <tr>
                                    <td>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="cargo_id.{{$cargo->id}}" wire:key="{{ $cargo->id }}" value="{{ $cargo->id }}" class="line-style"  />
                                            <label for="one" class="radio-label">{{$cargo->name}} </label>
                                            @error('cargo_id.'.$cargo->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @endif
                              </table>  
                        </div>
               
                    </div>

                 
                    <h5 class="underline mt-n">Specify Transporter Corridors</h5>
                    <div class="form-group">
                        <label for="title">Corridor</label>
                        <select class="form-control" wire:model.debounce.300ms="corridor_id.0">
                            <option value="">Select Corridor</option>
                            @foreach ($corridors as $corridor)
                                <option value="{{ $corridor->id }}">{{ $corridor->name }}</option>
                            @endforeach
                        </select>
                        @error('corridor_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                        @foreach ($corridors_inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <select class="form-control" wire:model.debounce.300ms="corridor_id.{{ $value }}">
                                        <option value="">Select Corridor</option>
                                        @foreach ($corridors as $corridor)
                                            <option value="{{ $corridor->id }}">{{ $corridor->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('corridor_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group" style="margin-top: -23px;">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="corridorsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="corridorsAdd({{$l}})"> <i class="fa fa-plus"></i> Corridor</button>
                                </div>
                            </div>
                        </div>
                    <h5 class="underline mt-n">Upload Files(<i>Company Profile, Tax Clearance</i>)</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="title.0" placeholder="Enter File Title eg Tax Clearance" />
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
                                <input type="text" class="form-control" wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter File Title eg tax Clearance"/>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transporterEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Transporter <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="worknumber">Landline</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="worknumber" placeholder="Enter Landline" />
                                @error('worknumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country"  />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City"  />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address" />
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

