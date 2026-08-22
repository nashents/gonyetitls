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
                                    <select wire:model.debounce.300ms="gate_pass_filter" class="form-control" aria-label="..." >
                                        <option value="created_at">Gate Pass Created At</option>
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
                          
                           
                            </div>
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                <li role="presentation" class="active"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">{{ucfirst($department)}} Authorization</a></li>
                            </ul>
                            <div class="tab-content bg-white p-15">
                                <div role="tabpanel" class="tab-pane active" id="trips">
    
                                @if ($selectedRows)
                                <br>
                                <div class="row" style="margin-left:3px;">
                                    <div class="col-lg-2" >
                                        <div class="dropdown">
                                            <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="fa fa-bars"></i> Bulk Actions
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                                <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Gate Passes</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" style="margin-top: 3px; margin-left: -20px;">
                                    <span >selected {{ count($selectedRows) }} gate passe(s) to authorize.</span>
                                    </div>
                                </div>
                                <br>
                                @endif
    
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption>{{ucfirst($department)}} Pending GatePasses</caption>
                                    <thead>
                                      <tr>
                                        <th class="th-sm">
                                            <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                        </th>
                                        <th class="th-sm">GatePass#
                                        </th>
                                        <th class="th-sm">Branch
                                        </th>
                                        <th class="th-sm">Trip
                                        </th>
                                        <th class="th-sm">Horse
                                        </th>
                                        <th class="th-sm">Driver
                                        </th>
                                        <th class="th-sm">Entry
                                        </th>
                                        <th class="th-sm">Exit
                                        </th>
                                        <th class="th-sm">Workshop
                                        </th>
                                        <th class="th-sm">Logistics
                                        </th>
                                      
                                        <th class="th-sm">Security
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($trip_gate_passes))
                                    <tbody>
                                        @forelse ($trip_gate_passes as $gate_pass)
                                      <tr>
                                        @php
                                            $workshop = App\Models\Employee::find($gate_pass->workshop_authorized_by_id);
                                            $logistics = App\Models\Employee::find($gate_pass->logistics_authorized_by_id);
                                            $authorized_by = App\Models\Employee::find($gate_pass->authorized_by_id);
                                        @endphp
                                          <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $gate_pass->id }}" value="{{ $gate_pass->id }}"></td>
                                        <td>{{$gate_pass->gate_pass_number}}</td>
                                        <td>{{$gate_pass->branch ? $gate_pass->branch->name : ""}}</td>
                                        <td>{{ucfirst($gate_pass->trip ? $gate_pass->trip->trip_number : "")}}</td>
                                        <td>{{$gate_pass->horse ? $gate_pass->horse->registration_number : ""}}</td>
                                        <td>
                                            @if (isset($gate_pass->driver))
                                            {{ucfirst($gate_pass->driver->employee ? $gate_pass->driver->employee->name : "")}} {{ucfirst($gate_pass->driver->employee ? $gate_pass->driver->employee->surname : "")}}
                                            @endif
                                        </td>
                                        <td>
                                            {{$gate_pass->entry}}
                                        </td>
                                        <td>
                                            {{$gate_pass->exit}}
                                        </td>
                                        <td><span class="label label-{{($gate_pass->workshop_authorization == 'approved') ? 'success' : (($gate_pass->workshop_authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->workshop_authorization == 'approved') ? 'approved' : (($gate_pass->workshop_authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                        <td><span class="label label-{{($gate_pass->logistics_authorization == 'approved') ? 'success' : (($gate_pass->logistics_authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->logistics_authorization == 'approved') ? 'approved' : (($gate_pass->logistics_authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                        <td><span class="label label-{{($gate_pass->authorization == 'approved') ? 'success' : (($gate_pass->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->authorization == 'approved') ? 'approved' : (($gate_pass->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{ route('gate_passes.show', $gate_pass->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                    @if ($department === "security")
                                                        @if ($gate_pass->logistics_authorization == 'approved' && $gate_pass->workshop_authorization == 'approved')
                                                             <li><a href="#" wire:click="authorize({{$gate_pass->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                        @endif
                                                    @else
                                                     <li><a href="#" wire:click="authorize({{$gate_pass->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                    @endif
                                                   
                                                </ul>
                                            </div>
                                            @include('gate_passes.delete')
                                    </td>
                                      </tr>
                                      @empty
                                        <tr>
                                            <td colspan="11">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Trip Gate Passes Found ....
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
                                        @if (isset($trip_gate_passes))
                                            {{ $trip_gate_passes->links() }} 
                                        @endif 
                                    </ul>
                                </nav>   
                            </div>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i>Bulk Authorize Gate Passes<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Gate Pass<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                        <label for="reason">Comments</label>
                       <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
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

