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
                                Pending Goods Returns
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Returns...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Return#</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">GRN</th>
                                    <th class="th-sm">Vendor</th>
                                    <th class="th-sm">Item(s)</th>
                                    <th class="th-sm">Total</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($goods_returneds))
                                <tbody>
                                    @forelse ($goods_returneds as $goods_returned)
                                  <tr>
                                    <td>
                                        {{ucfirst($goods_returned->goods_returned_number)}}
                                        <br>
                                        <small class="text-muted">
                                            <strong>CreatedBy:</strong> {{ $goods_returned->user?->name }} {{ $goods_returned->user?->surname }}<br>
                                            <strong>CreatedOn:</strong> {{ $goods_returned->created_at ? \Carbon\Carbon::parse($goods_returned->created_at)->format('Y-m-d') : "" }}
                                        </small>
                                    </td>
                                    <td>{{ucfirst($goods_returned->department)}}</td>
                                    <td>{{ $goods_returned->goods_received?->goods_received_number }}</td>
                                    <td>{{ $goods_returned->vendor?->name }}</td>
                                    <td>
                                        @foreach ($goods_returned->goods_returned_items as $item)
                                            <div>{{ $item->qty_returned }} x {{ $item->product?->name }} <small class="text-muted">({{ $item->return_reason }})</small></div>
                                        @endforeach
                                    </td>
                                    <td>{{ number_format($goods_returned->total_return_value, 2) }} {{ $goods_returned->currency }}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('goods_returneds.show',$goods_returned->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="authorize({{$goods_returned->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Pending Returns Found ....
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
                                    @if (isset($goods_returneds))
                                        {{ $goods_returneds->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Return <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
