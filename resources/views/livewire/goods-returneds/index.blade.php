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
                                            <span class="input-group-addon">Filter By</span>
                                            <select wire:model.debounce.300ms="goods_returned_filter" class="form-control">
                                                <option value="created_at">Created At</option>
                                                <option value="return_date">Return Date</option>
                                                <option value="expected_resolution_date">Expected Resolution</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('goods_returneds.new', ['department' => $department]) }}" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Goods Returned Voucher
                                </a>
                            </div>
                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search GRVs...">
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">GR#</th>
                                        <th class="th-sm">GRN Details</th>
                                        <th class="th-sm">Vendor</th>
                                        <th class="th-sm">Return Details</th>
                                        <th class="th-sm">Ccy</th>
                                        <th class="th-sm">Total</th>
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Action</th>
                                    </tr>
                                </thead>
                                @if (isset($goods_returneds))
                                <tbody>
                                    @forelse ($goods_returneds as $goods_returned)
                                    <tr>
                                        <td>
                                            {{ ucfirst($goods_returned->goods_returned_number) }}
                                            <small>
                                                <strong>CreatedBy:</strong> {{ $goods_returned->user?->name }} {{ $goods_returned->user?->surname }}<br>
                                                <strong>CreatedOn:</strong> {{ $goods_returned->created_at }}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>GRN#</strong> {{ $goods_returned->goods_received?->goods_received_number }}<br>
                                                <strong>Item(s):</strong> {{ $goods_returned->goods_returned_items->count() }}
                                            </small>
                                        </td>
                                        <td>{{ $goods_returned->vendor?->name }}</td>
                                        <td>
                                            <small class="text-muted">
                                                <strong>ReturnedBy:</strong> {{ $goods_returned->employee?->name }} {{ $goods_returned->employee?->surname }}<br>
                                                <strong>Return Date:</strong> {{ $goods_returned->return_date }}<br>
                                                <strong>Ref#:</strong> {{ $goods_returned->return_reference }}<br>
                                                <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $goods_returned->return_type)) }}<br>
                                                @if ($goods_returned->expected_resolution_date)
                                                    <strong>Expected:</strong> {{ $goods_returned->expected_resolution_date }}<br>
                                                @endif
                                                @if ($goods_returned->reason)
                                                    <strong>Reason:</strong> {{ Str::limit($goods_returned->reason, 50) }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>{{ $goods_returned->currency }}</td>
                                        <td>{{ number_format($goods_returned->total_return_value, 2) }}</td>
                                        <td>
                                            @php
                                                $authColors = ['pending' => 'label-warning', 'approved' => 'label-primary', 'rejected' => 'label-danger'];
                                                $statusColors = [
                                                    'draft'                  => 'label-default',
                                                    'approved'               => 'label-primary',
                                                    'dispatched_to_supplier' => 'label-info',
                                                    'pending_replacement'    => 'label-warning',
                                                    'replacement_received'   => 'label-success',
                                                    'refunded'               => 'label-success',
                                                    'credited'               => 'label-success',
                                                    'cancelled'              => 'label-danger',
                                                ];
                                            @endphp
                                            @if (is_null($goods_returned->authorization))
                                                <span class="label label-default">Draft</span>
                                            @else
                                                <span class="label {{ $authColors[$goods_returned->authorization] ?? 'label-default' }}">
                                                    {{ ucfirst($goods_returned->authorization) }}
                                                </span>
                                            @endif
                                            <br>
                                            <span class="label {{ $statusColors[$goods_returned->status] ?? 'label-default' }}">
                                                {{ ucfirst(str_replace('_', ' ', $goods_returned->status)) }}
                                            </span>
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{ route('goods_returneds.show', $goods_returned->id) }}"><i class="fa fa-eye color-default"></i> View</a></li>
                                                    @if ($goods_returned->authorization === 'approved')
                                                    <li><a href="#" wire:click="showStatusModal({{ $goods_returned->id }})"><i class="fas fa-exchange-alt color-success"></i> Update Fulfilment Status</a></li>
                                                    @endif
                                                    @if (is_null($goods_returned->authorization))
                                                    <li><a href="{{ route('goods_returneds.new', ['department' => $department, 'goodsReturned' => $goods_returned->id]) }}"><i class="fa fa-edit color-success"></i> Edit Draft</a></li>
                                                    <li><a href="#" wire:click.prevent="delete({{$goods_returned->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    @endif
                                                </ul>
                                            </div>

                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8">
                                            <div style="text-align:center; color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Goods Returned Found ....
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{ asset('images/nodata.png') }}" alt="">
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

    <div  wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Goods Returned Record?</strong> </center> 
            </div>
            <form wire:submit.prevent="destroy()">
            <div class="modal-footer no-border">
                <div class="btn-group" role="group">
                    <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>

    {{-- STATUS UPDATE MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-exchange-alt"></i> Update Status
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="updateStatus()">
                    <div class="modal-body">
                        <p>Update status for: <strong>{{ $goods_returned_number }}</strong></p>
                        <div class="form-group">
                            <label>New Status <span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="new_status">
                                <option value="dispatched_to_supplier">Dispatched to Supplier</option>
                                <option value="pending_replacement">Pending Replacement</option>
                                <option value="replacement_received">Replacement Received</option>
                                <option value="refunded">Refunded</option>
                                <option value="credited">Credited</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('new_status') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i> Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
