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
                                    <h5>Date range</h5>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="transport_order_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Created At</option>
                                                    <option value="start_date">Start Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
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
                                @if ($selectedRows)
                                    <div class="row">
                                        <div class="col-lg-2" >
                                            <div class="dropdown">
                                                <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    <i class="fa fa-bars"></i> Bulk Actions
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                                    <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Transport Orders</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-3" style="margin-top: 5px; margin-left: -30px;">
                                        <span >selected {{ count($selectedRows) }} bill(s) to authorize.</span>
                                        </div>
                                    </div>
                                    <br>
                                @endif
                                <div class="col-md-5" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search transport orders">
                                    </div>
                                </div>
                            @php
                                    $showFreight = !$company->rates_managed_by_finance
                                        || in_array('Finance', $department_names)
                                        || in_array('Super Admin', $role_names);

                                    $dtPattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    $formatDate = function ($value) use ($dtPattern) {
                                        if (!$value) return null;

                                        // datetime-local like 2026-01-09T06:30
                                        if (is_string($value) && preg_match($dtPattern, $value)) {
                                            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $value)->format('d M Y g:i A');
                                        }

                                        // already a Carbon/date string
                                        try {
                                            return \Carbon\Carbon::parse($value)->format('d M Y g:i A');
                                        } catch (\Throwable $e) {
                                            return $value; // fallback
                                        }
                                    };

                                    $statusMap = [
                                        'Completed'        => ['row' => '#5cb85c', 'cell' => 'table-success', 'badge' => 'success'],
                                        'Scheduled'        => ['row' => '#f0ad4e', 'cell' => 'table-warning', 'badge' => 'warning'],
                                        'Started'    => ['row' => '#adb5bd', 'cell' => 'table-secondary', 'badge' => 'info'],
                                        'Cancelled'           => ['row' => '#5bc0de', 'cell' => 'table-info', 'badge' => 'danger'],
                                    ];
                                @endphp
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th class="th-sm">
                                                    <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                                </th>
                                                <th>Order#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Date</th>
                                                <th>Cargo</th>
                                                <th>Customer<hr style="margin-top:2px; margin-bottom:2px">Consignee</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                @if($showFreight)
                                                    <th>
                                                        Rate
                                                        <hr style="margin-top:2px; margin-bottom:2px">
                                                        Freight
                                                    </th>
                                                @endif
                                                <th>Invoice</th>
                                                <th>Auth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($transport_orders as $transport_order)
                                            @php
                                                $s = $statusMap[$transport_order->status] ?? ['row' => null, 'cell' => '', 'badge' => 'secondary'];
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}" @endif>
                                                <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $transport_order->id }}" value="{{ $transport_order->id }}"></td>
                                                <td>
                                                    <strong>{{ $transport_order->transport_order_number }}@if($transport_order->transport_order_ref)/{{ $transport_order->transport_order_ref }}@endif</strong>
                                                    <br>
                                                    <small>
                                                        <strong>CreatedBy:</strong>  {{ $transport_order->user?->name }} {{ $transport_order->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $transport_order->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $transport_order->trip_type?->name }}
                                                 
                                                </td>
                                                <td>
                                                    {{ $formatDate($transport_order->start_date) }}
                                                </td>
                                                <td>
                                                     {{ ucfirst($transport_order->cargo?->name ?? '') }} <br>
                                                    <small class="text-muted">
                                                        <strong>Weight: </strong>{{$transport_order->weight}} <br>
                                                        <strong>Qty: </strong>{{$transport_order->quantity}} {{$transport_order->units_of_measure?->name}} <br>
                                                        <strong>AddInfo:</strong> {{$transport_order->cargo_details}} <br>
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ ucfirst($transport_order->customer?->name ?? '') }}
                                                   <hr class="my-1">
                                                        {{ ucfirst($transport_order->consignee?->name ?? '') }}
                                                </td>
                                                <td>
                                                    @if($transport_order->fromDestination)
                                                        {{ $transport_order->fromDestination->country?->name }} {{ $transport_order->fromDestination->city }}
                                                    @endif
                                                    @if($transport_order->loading_point)
                                                        <hr class="my-1">
                                                        {{ $transport_order->loading_point->name }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($transport_order->toDestination)
                                                        {{ $transport_order->toDestination->country?->name }} {{ $transport_order->toDestination->city }}
                                                    @endif
                                                    @if($transport_order->offloading_point)
                                                        <hr class="my-1">
                                                        {{ $transport_order->offloading_point->name }}
                                                    @endif
                                                </td>

                                                <td class="{{ $s['cell'] }}" style="padding: 5px;">
                                                    <span class="label label-{{ $s['badge'] }} label-wide">
                                                        {{ $transport_order->status }}
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $transport_order->currency?->symbol }}
                                                        {{ number_format(
                                                            (float) (is_numeric($transport_order->rate)
                                                                ? $transport_order->rate
                                                                : preg_replace('/[^\d\.\-]/', '', (string) ($transport_order->rate ?? 0))
                                                            ),
                                                            2
                                                        ) }}
                                                        <hr class="my-1">
                                                        {{ $transport_order->currency?->symbol }}
                                                        {{ number_format(
                                                            (float) (is_numeric($transport_order->freight)
                                                                ? $transport_order->freight
                                                                : preg_replace('/[^\d\.\-]/', '', (string) ($transport_order->freight ?? 0))
                                                            ),
                                                            2
                                                        ) }}
                                                    </td>
                                                @endif

                                                <td>
                                                    @if($transport_order->invoices?->count())
                                                        <span class="label label-success">issued</span>
                                                        <small>
                                                            <strong>Invoice#(s):</strong>
                                                            @foreach($transport_order->invoices as $invoice)
                                                                {{ $invoice->invoice_number }}@if(!$loop->last),@endif
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        <span class="label label-warning">pending</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @php
                                                        $authClass = $transport_order->authorization === 'approved' ? 'success' : ($transport_order->authorization === 'rejected' ? 'danger' : 'warning');
                                                        $authText  = $transport_order->authorization === 'approved' ? 'approved' : ($transport_order->authorization === 'rejected' ? 'rejected' : 'pending');
                                                    @endphp
                                                    <span class="label label-{{ $authClass }}">{{ $authText }}</span>

                                                    @if($transport_order->authorization_date)
                                                        <br><small><strong style="background-color: orange">AuthorizedOn: {{ $transport_order->authorization_date }}</strong></small>
                                                    @endif
                                                    @if($transport_order->authorized_by_id)
                                                        <br><small><strong style="background-color: orange">AuthorizedBy: {{ $this->getAuthorizer($transport_order->authorized_by_id) }}</strong></small>
                                                    @endif
                                                    @if($transport_order->reason)
                                                        <br><small><strong style="background-color: orange">Comments: {{ $transport_order->reason }}</strong></small>
                                                    @endif
                                                </td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                   <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="{{ route('transport_orders.show',$transport_order->id)}}">
                                                                    <i class="fas fa-eye color-default"></i> View
                                                                </a>
                                                            </li>
                                                             {{-- <li><a href="#" wire:click="authorize({{$transport_order->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li> --}}
                                                          
                                                        </ul>
                                                    </div> 
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No Rejected Transport Orders Found ....
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                {{-- </div> --}}
                                  
                                    <nav class="text-center" style="float: right">
                                        <ul class="pagination rounded-corners">
                                            @if (isset($transport_orders))
                                                {{ $transport_orders->links() }} 
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
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Transport Order<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
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

