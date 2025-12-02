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
                                            <select wire:model.debounce.300ms="dispatch_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Dispatch Created At</option>
                                                <option value="date">Dispatch Date</option>
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
                                </div>
                             </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search dispatches...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Dispatch#
                                    </th>
                                    <th class="th-sm">DispatchedBy
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">RequestedBy
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">Items
                                    </th>
                                    
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                 @if (isset($dispatches))
                                <tbody>
                                    @forelse ($dispatches as $dispatch)
                                  <tr>
                                                                        <td>{{$dispatch->dispatch_number}}</td>
                                    <td>{{$dispatch->user ? $dispatch->user->name : ""}} {{$dispatch->user ? $dispatch->user->surname : ""}}</td>
                                    <td>{{$dispatch->date}}</td>
                                    <td>
                                        @php
                                            $requested_by = App\Models\Employee::find($dispatch->requested_by_id);
                                        @endphp
                                        @if ($requested_by)
                                            {{$requested_by->name}} {{$requested_by->surname}}
                                        @endif
                                    </td>
                                <td>
                                        @if ($dispatch->ticket)
                                           <strong>Ticket#: </strong><a href="">{{$dispatch->ticket ? $dispatch->ticket->ticket_number : ""}}</a>
                                        @endif
                                        @if ($dispatch->horse)
                                           <strong>Horse: </strong><a href="">{{$dispatch->horse ? $dispatch->horse->registration_number : ""}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}} 
                                            @if ($dispatch->horse->horse_make)
                                                {{$dispatch->horse->horse_make ? $dispatch->horse->horse_make->name : ""}} {{$dispatch->horse->horse_model ? $dispatch->horse->horse_model->name : ""}}       
                                           @endif
                                        </a>
                                        @endif
                                        @if ($dispatch->vehicle)
                                            <strong>Vehicle: </strong><a href="">{{$dispatch->vehicle ? $dispatch->vehicle->registration_number : ""}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->trailer)
                                           <strong></strong>  Trailer:<a href="">{{$dispatch->trailer ? $dispatch->trailer->registration_number : ""}} {{$dispatch->trailer->fleet_number ? "(".$dispatch->trailer->fleet_number.")" : ""}}</a>
                                        @endif
                                                @if ($dispatch->employee)
                                                    <strong>Employee:</strong> {{$dispatch->employee ? $dispatch->employee->name : ""}} {{$dispatch->employee ? $dispatch->employee->surname : ""}} <br>
                                                @php
                                                    $asset_department = App\Models\Department::find($dispatch->department_id);
                                                @endphp
                                                @if ( $asset_department )
                                                    <strong>Department:</strong> {{ $asset_department->name}} <br>
                                                @endif
                                                @if ($dispatch->branch)
                                                    <strong>Branch:</strong> {{$dispatch->branch ? $dispatch->branch->name : ""}}
                                                @endif
                                        @endif
                                         @if ($dispatch->vendor)
                                            <strong>Vendor: </strong>{{$dispatch->vendor ? $dispatch->vendor->name : ""}}
                                        @endif
                                    </td>
                                  <td>
                                        @if ($dispatch->dispatch_items)
                                            @foreach ($dispatch->dispatch_items as $dispatch_item)
                                                @if ($dispatch_item->inventory)
                                                    {{$dispatch_item->inventory->product ? $dispatch_item->inventory->product->name : ""}} 
                                                @elseif($dispatch_item->asset)
                                                    {{$dispatch_item->asset->product ? $dispatch_item->asset->product->name : ""}}
                                                @elseif($dispatch_item->product)
                                                    {{$dispatch_item->product ? $dispatch_item->product->name : ""}}
                                                @elseif($dispatch_item->tyre)
                                                    {{$dispatch_item->tyre->product ? $dispatch_item->tyre->product->name : ""}}
                                                @endif
                                                @if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{$dispatch->dispatch_items->sum('qty')}}</td>
                                    <td>{{$dispatch->currency ? $dispatch->currency->name : ""}}</td>
                                    <td> {{$dispatch->currency ? $dispatch->currency->symbol : ""}}{{number_format($dispatch->total ? $dispatch->total : 0 , 2)}}</td>
                                     <td>
                                        <span class="badge bg-{{($dispatch->authorization == 'approved') ? 'success' : (($dispatch->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($dispatch->authorization == 'approved') ? 'approved' : (($dispatch->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                        @php
                                            $user = App\Models\User::find($dispatch->authorized_by_id);
                                        @endphp
                                        <small>
                                        @if ($user)
                                            <br>
                                          <strong>{{$dispatch->authorization == "approved" ? "ApprovedBy" : "RejectedBy"}}:</strong> {{$user->name}} {{$user->surname}}
                                        @endif
                                        @if ($dispatch->authorization_date)
                                            <br>
                                         <strong>AuthorizedOn:</strong> {{Carbon\Carbon::parse($dispatch->authorization_date)->format('Y-m-d')}}
                                        @endif
                                        @if ($dispatch->authorization_comments)
                                            <br>
                                         <strong>Comments:</strong> {{$dispatch->authorization_comments}}
                                        @endif
                                        </small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                               <li><a href="{{route('dispatches.show',$dispatch->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                            </ul>
                                        </div>
                                        @include('dispatches.delete')
                                </td>
                                  </tr>
                                    @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rejected Dispatches Found ....
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
                                    @if (isset($dispatches))
                                        {{ $dispatches->links() }} 
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

     <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Dispatch <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

