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
                            @if ($selectedRows)
                            <div class="row">
                                <div class="col-lg-2" >
                                    <div class="dropdown">
                                        <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="fa fa-bars"></i> Bulk Actions
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                            <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Invoices</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-3" style="margin-top: 5px; margin-left: -30px;">
                                <span >selected {{ count($selectedRows) }} invoice(s) to authorize.</span>
                                </div>
                            </div>
                            <br>
                            @endif
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  
                                  <tr>
                                    <th class="th-sm">
                                        <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                    </th>
                                    <th class="th-sm">Invoice#
                                    </th>
                                    <th class="th-sm">InvoiceTo
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Payment Due
                                    </th>
                                    <th class="th-sm">Status
                                    </th>  
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Amount Due
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($invoices))
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                    @php
                                        $expiry = $invoice->expiry;
                                        $now = new DateTime();
                                        $expiry_date = new DateTime($expiry);
                                    @endphp
                                  <tr>
                                    <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $invoice->id }}" value="{{ $invoice->id }}"></td>
                                    <td>{{$invoice->invoice_number}}</td>
                                    <td>
                                        @if ($invoice->customer)
                                            {{$invoice->customer->name}}
                                        @elseif($invoice->transporter)
                                            {{$invoice->transporter->name}}
                                        @endif
                                    </td>
                                    <td>{{$invoice->date}}</td>
                                    <td><span class="label label-{{$now <= $expiry_date ? 'success' : 'danger' }}">{{$invoice->expiry}}</span></td>
                                    <td><span class="label label-{{($invoice->status == 'Paid') ? 'success' : (($invoice->status == 'Partial') ? 'warning' : 'danger') }}">{{ $invoice->status }}</span></td>
                                    <td>
                                        {{$invoice->currency ? $invoice->currency->name : ""}}
                                    </td>
                                    <td>
                                        @if ($invoice->total)
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->total,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->payments)
                                             {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->payments->sum('amount'),2)}}
                                        @endif
                                    </td>
                                    <td>
                                       
                                        {{-- @if ($invoice->balance) --}}
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->balance,2)}}
                                        {{-- @endif --}}
                                    </td>
                                    <td><span class="badge bg-{{($invoice->authorization == 'approved') ? 'success' : (($invoice->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($invoice->authorization == 'approved') ? 'approved' : (($invoice->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                   
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('invoices.show',$invoice->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="authorize({{$invoice->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                            </ul>
                                        </div>
                                        @include('invoices.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rejected Invoices Found ....
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
                                    @if (isset($invoices))
                                        {{ $invoices->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="invoiceAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Invoice <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

