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
                                <a href="" data-toggle="modal" data-target="#clusterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Cluster</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search clusters...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Trailers
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($clusters))
                                <tbody>
                                    @forelse ($clusters as $cluster)
                                  <tr>
                                    <td>{{$cluster->name}}</td>
                                    <td>
                                        @if ($cluster->horse)
                                            {{$cluster->horse->registration_number}} {{$cluster->horse->fleet_number ? "(".$cluster->horse->fleet_number.")" : ""}} 
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($cluster->trailers as $trailer)
                                            {{$trailer->trailer_type ? $trailer->trailer_type->name : ""}} {{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}} <br>
                                        @endforeach
                                    </td>
                                    <td><span class="badge bg-{{$cluster->status == 1 ? "success" : "danger"}}">{{$cluster->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$cluster->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#clusterDeleteModal{{ $cluster->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('clusters.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="5">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Clusters Found ....
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
                                    @if (isset($clusters))
                                        {{ $clusters->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="clusterModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Cluster <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Cluster Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Cluster Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Horses</label>
                                <select wire:model.debounce.300ms="horse_id" class="form-control">
                                    <option value="">Select Horse</option>
                                    @foreach ($horses as $horse)
                                        <option value="{{$horse->id}}"> {{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                    @endforeach
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="trailer_id" class="form-control" required multiple>
                                    <option value="">Select Cluster Trailer(s)</option>
                                    @foreach ($trailers as $trailer)
                                        <option value="{{$trailer->id}}">{{$trailer->trailer_type ? $trailer->trailer_type->name : ""}} {{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                    @endforeach
                                </select>
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="clusterEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Cluster <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Cluster Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Cluster Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Horses</label>
                                <select wire:model.debounce.300ms="horse_id" class="form-control">
                                    <option value="">Select Horse</option>
                                    @foreach ($horses as $horse)
                                        <option value="{{$horse->id}}"> {{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                    @endforeach
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="trailer_id" class="form-control" required multiple>
                                    <option value="">Select Cluster Trailer(s)</option>
                                    @foreach ($trailers as $trailer)
                                        <option value="{{$trailer->id}}">{{$trailer->trailer_type ? $trailer->trailer_type->name : ""}} {{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                    @endforeach
                                </select>
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Status<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">InActive</option>
                        </select>
                        @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

