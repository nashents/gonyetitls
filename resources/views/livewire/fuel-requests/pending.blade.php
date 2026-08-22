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
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            Filter By
                                        </span>
                                    <select wire:model.debounce.300ms="fuel_request_filter" class="form-control" aria-label="..." >
                                        <option value="created_at">Fuel Request Created At</option>
                                    </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                               
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
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
                                            <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Fuel Requests</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="margin-top: 3px; margin-left: -20px;">
                                <span >selected {{ count($selectedRows) }} fuel request(s) to authorize.</span>
                                </div>
                            </div>
                            <br>
                            @endif
                          
                           
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-5" style="float: right;">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search fuel requests....">
                                </div>   
                            </div>
                             <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">
                                        <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                    </th>
                                    <th class="th-sm">Request#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">
                                        RequestFor
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Reason
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($fuel_requests))
                                    <tbody>
                                        @forelse ($fuel_requests as $fuel_request)
                                            <tr>
                                                <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $fuel_request->id }}" value="{{ $fuel_request->id }}"></td>
                                                <td>
                                                    {{$fuel_request->request_number}} <br>
                                                    <small class="text-muted">
                                                        <strong>CreatedBy:</strong> {{$fuel_request->user?->name}} {{$fuel_request->user?->surname}} <br>
                                                        <strong>CreatedOn:</strong> {{$fuel_request->created_at}}<br>
                                                        @if ($fuel_request->allocation)
                                                            <strong>Allocation#:</strong>{{ucfirst($fuel_request->allocation ? $fuel_request->allocation->allocation_number : "")}}
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>{{ucfirst($fuel_request->employee->name)}} {{ucfirst($fuel_request->employee->surname)}}</td>
                                                <td>
                                                    @if ($fuel_request->horse)
                                                         Horse | {{$fuel_request->horse ? $fuel_request->horse->registration_number : ""}} {{$fuel_request->horse->fleet_number ? "(".$fuel_request->horse->fleet_number.")" : ""}}  
                                                    @elseif($fuel_request->vehicle)
                                                         Vehicle | {{  $fuel_request->vehicle ? $fuel_request->vehicle->registration_number : "" }} {{$fuel_request->vehicle->fleet_number ? "(".$fuel_request->vehicle->fleet_number.")" : ""}}  
                                                    @elseif($fuel_request->asset)
                                                         Asset | {{$fuel_request->asset->product->brand ? $fuel_request->asset->product->brand->name : ""}} {{$fuel_request->asset->product ? $fuel_request->asset->product->name : ""}}
                                                    @else
                                                        Other
                                                    @endif
                                                </td>
                                                <td>{{$fuel_request->fuel_type}}</td>
                                                <td>{{$fuel_request->quantity ? $fuel_request->quantity."Litres" : ""}}</td>
                                                <td>{{$fuel_request->reason }}</td>
                                                <td><span class="badge bg-{{($fuel_request->authorization == 'approved') ? 'success' : (($fuel_request->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel_request->authorization == 'approved') ? 'approved' : (($fuel_request->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a href="#" wire:click.prevent="authorize({{$fuel_request->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                        </ul>
                                                    </div>
                                                   
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Pending Fuel Requests Found ....
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
                                    @if (isset($fuel_requests))
                                        {{ $fuel_requests->links() }} 
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

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i>Bulk Authorize Fuel Request(s)<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="authorizeSelectedRows()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize<span class="required" style="color: red">*</span></label>
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
                    <div class="form-group">
                        <input type="checkbox" wire:model.debounce.300ms="stay_on_page" class="line-style" id="stay_on_page_bulk" />
                        <label for="stay_on_page_bulk" class="radio-label">Stay on this page after authorizing (don't redirect to Approved/Rejected)</label>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelRequestAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gas-pump"></i> Authorize Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
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
                        <label for="comment">Comment</label>
                  <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <input type="checkbox" wire:model.debounce.300ms="stay_on_page" class="line-style" id="stay_on_page" />
                        <label for="stay_on_page" class="radio-label">Stay on this page after authorizing (don't redirect to Approved/Rejected)</label>
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

