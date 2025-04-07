<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#corridor_transporterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transporter</a>
        <br>
        <br>
        <table id="transportersTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Email
                </th>
                <th class="th-sm">Phonenumber
                </th>
                <th class="th-sm">Address
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @foreach ($corridor_transporters as $corridor_transporter)
              <tr>
                <a href="{{ route('transporters.show',$corridor_transporter->id) }}">
                <td>{{$corridor_transporter->name}}</td>
                <td>{{$corridor_transporter->email}}</td>
                <td>{{$corridor_transporter->phonenumber}}</td>
                <td>{{$corridor_transporter->street_address}} {{$corridor_transporter->suburb}}, {{$corridor_transporter->city}}, {{$corridor_transporter->country}}</td>
                </a>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#"  wire:click="removeShow({{$corridor_transporter->id}})"><i class="fa fa-trash color-danger"></i>Remove</a></li>
                        </ul>
                    </div>
            </td>
            </tr>
              @endforeach
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="corridorDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this Transporter?</strong> </center> 
                </div>
                <form wire:submit.prevent="removeTransporter()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="corridor_transporterModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Transporter(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    
                            <div class="form-group">
                                <label for="title">Transporter</label>
                                <select wire:model.debounce.300ms="transporter_id.0" class="form-control">
                                    <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                                    @endforeach
                                </select>
                                @error('transporter_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="title">Transporter</label>
                                    <select wire:model.debounce.300ms="transporter_id.{{ $value }}" class="form-control">
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('transporter_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Transporter</button>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Add</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transporterEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="transporter">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Transporter(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Enter Name" >
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="surname">Surname</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="surname" placeholder="Enter Surname" >
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Email</label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="email" placeholder="Enter Email" >
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="surname">Phonenumber</label>
                                <input type="number" class="form-control"  wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" >
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Department</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="department" placeholder="Enter Department" >
                                @error('department') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
