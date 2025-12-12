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
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <a href="#" data-toggle="modal" data-target="#transferModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transfer</a>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: -80px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2">
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
                            <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search transfers...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transfer#
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Item(s)
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($transfers))
                                <tbody>
                                    @forelse ($transfers as $transfer)
                                  <tr>
                                    <td>{{$transfer->transfer_number}}</td>
                                    @php
                                        $from = App\Models\Store::find($transfer->from);
                                        $to = App\Models\Store::find($transfer->to);
                                    @endphp
                                    <td>{{$from?->name}}</td>
                                    <td>{{$to?->name}}</td>
                                    <td>{{$transfer->date }}</td>
                                    <td>{{$transfer->comments }}</td>
                                    <td>
                                        @php
                                            $transfer_items = App\Models\TransferItem::where('transfer_id',$transfer->id)->get();
                                        @endphp
                                        @if (!empty($transfer_items))
                                            @foreach ($transfer_items as $transfer_item)
                                                @if ($transfer_item->inventory)
                                                        @php
                                                            $inventory = $transfer_item->inventory;
                                                        @endphp
                                                        {{$inventory->inventory_number}} {{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}}   
                                       
                                                @elseif($transfer_item->tyre)
                                                        @php
                                                            $tyre = $transfer_item->tyre;
                                                        @endphp
                                                        {{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} )
                                                @endif
                                                ({{$transfer_item->qty}})
                                                @if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                        <td>
                                        <span class="badge bg-{{($transfer->authorization == 'approved') ? 'success' : (($transfer->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($transfer->authorization == 'approved') ? 'approved' : (($transfer->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                        @php
                                            $user = App\Models\User::find($transfer->authorized_by_id);
                                        @endphp
                                        <small>
                                        @if ($user)
                                            <br>
                                          <strong>{{$transfer->authorization == "approved" ? "ApprovedBy" : "RejectedBy"}}:</strong> {{$user->name}} {{$user->surname}}
                                        @endif
                                        @if ($transfer->authorization_date)
                                            <br>
                                         <strong>AuthorizedOn:</strong> {{Carbon\Carbon::parse($transfer->authorization_date)->format('Y-m-d')}}
                                        @endif
                                        @if ($transfer->authorization_comments)
                                            <br>
                                         <strong>Comments:</strong> {{$transfer->authorization_comments}}
                                        @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{$transfer->is_received == True ? 'success' :  'primary' }}">{{ $transfer->is_received == True ? "Received" : "Intransit" }}</span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                              <li><a href="{{route('transfers.show',$transfer->id)}}"   ><i class="fas fa-eye color-default"></i> View</a></li>
                                            <li><a href="#" wire:click="authorize({{$transfer->id}})"><i class="fas fa-gavel color-success"></i>Authorization</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Transfers Found ....
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
                                    @if (isset($transfers))
                                        {{ $transfers->links() }} 
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
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Transfers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

