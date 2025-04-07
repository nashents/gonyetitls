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

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <div class="panel-title">
                               
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="fuel_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Order Created At</option>
                              </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @if ($fuel_filter == "created_at")
                                <div class="col-lg-2" style="margin-right: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @elseif ($fuel_filter == "start_date")
                                <div class="col-lg-2" style="margin-right: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @endif
                               
                                <!-- /input-group -->
                            </div>
                        
                            @if ($selectedRows)
                            <br>
                            <div class="row">
                                <div class="col-lg-2" >
                                    <div class="dropdown">
                                        <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="fa fa-bars"></i> Bulk Actions
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                            <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Fuel Orders</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="margin-top: 3px; margin-left: -20px;">
                                <span >selected {{ count($selectedRows) }} fuel order(s) to authorize.</span>
                                </div>
                            </div>
                            <br>
                            @endif
                            
                            </div>
                      
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">
                                            <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                        </th>
                                        <th class="th-sm">Order#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Category
                                        </th>
                                        <th class="th-sm">Station
                                        </th>
                                        <th class="th-sm">FillUp
                                        </th>
                                        <th class="th-sm">Quantity
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">RejectedBy
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if ($fuels->count()>0)
                                <tbody>
                                    @foreach ($fuels as $fuel)
                                    @if ($fuel->fillup == 1)
                                    <tr style="background-color: #4CAF50">
                                        <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $fuel->id }}" value="{{ $fuel->id }}"></td>
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                                      <td>{{$fuel->date}}</td>
                                      <td>
                                        @if ($fuel->horse)
                                        Horse | {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                        @if (isset($fuel->trip))
                                        <br>
                                            @php
                                                $from = App\Models\Destination::find($fuel->trip->from);
                                                $to = App\Models\Destination::find($fuel->trip->from);
                                            @endphp
                                            Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}
                                            @if (isset($from))
                                                {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                            @endif
                                            @if (isset($to))
                                                {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                            @endif
                                    
                                        @endif
                                        @elseif($fuel->asset)
                                        @if ($fuel->asset)
                                            Asset | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        @endif
                                        @elseif($fuel->vehicle) 
                                            Vehicle | {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                        @endif
                                      </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td>
                                        @php
                                            $auth_by = App\Models\User::find($fuel->authorized_by_id);
                                        @endphp
                                        @if (isset($auth_by))
                                            {{ $auth_by->name }} {{ $auth_by->surname }}
                                        @endif
                                      </td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#" wire:click="authorize({{$fuel->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                              </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @else
                                    <tr style="background-color: #FFC107">
                                        <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $fuel->id }}" value="{{ $fuel->id }}"></td>
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->user ? $fuel->user->name : ""}} {{$fuel->user ? $fuel->user->surname : ""}}</td>
                                      <td>{{$fuel->date}}</td>
                                      <td>
                                        @if ($fuel->horse)
                                        Horse | {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                        @if (isset($fuel->trip))
                                        <br>
                                            @php
                                                $from = App\Models\Destination::find($fuel->trip->from);
                                                $to = App\Models\Destination::find($fuel->trip->from);
                                            @endphp
                                            Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}
                                            @if (isset($from))
                                                {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                            @endif
                                            @if (isset($to))
                                                {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                            @endif
                                    
                                        @endif
                                        @elseif($fuel->asset)
                                        @if ($fuel->asset)
                                            Asset | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        @endif
                                        @elseif($fuel->vehicle) 
                                            Vehicle | {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                        @endif
                                    </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td>
                                        @php
                                            $auth_by = App\Models\User::find($fuel->authorized_by_id);
                                        @endphp
                                        @if (isset($auth_by))
                                            {{ $auth_by->name }} {{ $auth_by->surname }}
                                        @endif
                                      </td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#" wire:click="authorize({{$fuel->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                            </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @endif
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($fuels))
                                        {{ $fuels->links() }} 
                                    @endif 
                                </ul>
                            </nav>    
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i>Bulk Authorize Fuel Order(s)<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="authorizeSelectedRows()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                    <select class="form-control" wire:model.debounce.300ms="authorize" required>
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="reason">Comments</label>
                       <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gas-pump"></i> Authorize Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                    <select class="form-control" wire:model.debounce.300ms="authorize">
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                  <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
