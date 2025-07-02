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

                            <table id="dispatchesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
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
                                    <th class="th-sm">Total Items
                                    </th>
                                    <th class="th-sm">Total Value
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($dispatches->count()>0)
                                <tbody>
                                    @foreach ($dispatches as $dispatch)
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
                                            Ticket#: <a href="">{{$dispatch->ticket ? $dispatch->ticket->ticket_number : ""}}</a>
                                        @endif
                                        @if ($dispatch->horse)
                                            Horse: <a href="">{{$dispatch->horse ? $dispatch->horse->registration_number : ""}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->vehicle)
                                            Vehicle: <a href="">{{$dispatch->vehicle ? $dispatch->vehicle->registration_number : ""}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->trailer)
                                            Trailer: <a href="">{{$dispatch->trailer ? $dispatch->trailer->registration_number : ""}} {{$dispatch->trailer->fleet_number ? "(".$dispatch->trailer->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->employee)
                                            Employee: {{$dispatch->employee ? $dispatch->employee->name : ""}} {{$dispatch->employee ? $dispatch->employee->surname : ""}}
                                                @php
                                                    $asset_department = App\Models\Department::find($dispatch->department_id);
                                                @endphp
                                                @if ( $asset_department )
                                                    Department: {{ $asset_department->name}}
                                                @endif
                                                @if ($dispatch->branch)
                                                    Branch: {{$dispatch->branch ? $dispatch->branch->name : ""}}
                                                @endif
                                        @endif
                                       
                                    </td>
                                    <td>
                                        @if ($dispatch->dispatch_items)
                                            @foreach ($dispatch->dispatch_items as $dispatch_item)
                                                @if ($dispatch_item->inventory)
                                                    {{$dispatch_item->inventory->product ? $dispatch_item->inventory->product->name : ""}}
                                                @elseif($dispatch_item->asset)
                                                    {{$dispatch_item->asset->product ? $dispatch_item->asset->product->name : ""}}
                                                @elseif($dispatch_item->tyre)
                                                    {{$dispatch_item->tyre->product ? $dispatch_item->tyre->product->name : ""}}
                                                @endif
                                                
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{$dispatch->dispatch_items->count()}}</td>
                                    <td></td>
                                    <td><span class="badge bg-{{($dispatch->authorization == 'approved') ? 'success' : (($dispatch->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($dispatch->authorization == 'approved') ? 'approved' : (($dispatch->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                               <li><a href="{{route('dispatches.show',$dispatch->id)}}"  ><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="#" wire:click="authorize({{$dispatch->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                            </ul>
                                        </div>
                                        @include('dispatches.delete')
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

