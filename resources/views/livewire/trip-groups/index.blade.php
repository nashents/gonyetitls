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
                              
                               
                                <a href="" data-toggle="modal" data-target="#trip_groupModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tracking Group</a>
                                <div class="col-lg-3" style="float: right">
                                    <div class="input-group">
                                      <span class="input-group-addon">Tracking Filter</span>
                                      <select wire:model.debounce.300ms="tracking_filter" class="form-control" aria-label="..." >
                                        <option value="1">Ongoing</option>
                                        <option value="0">Completed</option>
                                        <option value="all">All</option>
                                      </select>
                                    </div>
                                        <!-- /input-group -->
                                    </div>
                            </div>
                        </div>
                       
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($trip_groups))
                                <tbody>
                                    @forelse ($trip_groups as $trip_group)
                                    @php
                                        $from = App\Models\Destination::find($trip_group->from);
                                        $to = App\Models\Destination::find($trip_group->to);
                                    @endphp
                                  <tr>
                                    <td>{{ucfirst($trip_group->name)}}</td>
                                    <td>{{$trip_group->date}}</td>
                                    <td>{{$trip_group->customer ? $trip_group->customer->name : ""}}</td>
                                    <td>
                                        @if (isset($from))
                                        {{$from->country ? $from->country->name : ""}} {{$from->city}} 
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($to))
                                        {{$to->country ? $to->country->name : ""}} {{$to->city}} 
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$trip_group->status == 1 ? "warning" : "success"}}">{{$trip_group->status == 1 ? "Ongoing" : "Completed"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('trip_groups.show',$trip_group->id) }}"   ><i class="fa fa-eye color-default"></i> Show</a></li> 
                                                <li><a href="#"  wire:click="edit({{$trip_group->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li> 
                                                <li><a href="#" data-toggle="modal" data-target="#trip_groupDeleteModal{{ $trip_group->id }}"><i class="fa fa-trash color-danger"></i> Delete</a></li> 
                                              
                                            </ul>
                                        </div>
                                        @include('trip_groups.delete')
                                </td>
                                  </tr>
                                
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Trip Tracking Groups Found ....
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
                                    @if (isset($trip_groups))
                                        {{ $trip_groups->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_groupModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i>New Tracking Group<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Customer(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="customer_id" class="form-control" wire:change="makeName()" required>
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Tracking Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" wire:change="makeName()" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">From<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="from" class="form-control" required wire:change="makeName()">
                                    <option value="">Select From Location</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city}}</option>
                                    @endforeach
                                </select>
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">To<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="to" class="form-control" required wire:change="makeName()">
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city}}</option>
                                    @endforeach
                                </select>
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Tracking Group Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required >
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                       
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
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_groupEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Tracking Name <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Customer(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="customer_id" class="form-control" required wire:change="makeName()">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Tracking Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required wire:change="makeName()">
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">From<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="from" class="form-control" required wire:change="makeName()">
                                    <option value="">Select From Location</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city}}</option>
                                    @endforeach
                                </select>
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">To<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="to" class="form-control" required wire:change="makeName()">
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city}}</option>
                                    @endforeach
                                </select>
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Tracking Status<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="status" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="1">Open</option>
                                    <option value="0">Close</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Tracking Group Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required >
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                       
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

