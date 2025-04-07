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
                                  <select wire:model.debounce.300ms="bill_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Bill Created At</option>
                              </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @if ($bill_filter == "created_at")
                                <div class="col-lg-2" style="margin-right: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 30px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                                @endif
                               
                                <!-- /input-group -->
                            </div>
                          
                           
                            </div>
                            <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search bills...">
                                </div>
                            </div>

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Bill#
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Amount Due
                                    </th>
                                    <th class="th-sm">Payment Status
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($bills))
                                <tbody>
                                    @foreach ($bills as $bill)
                                  <tr>
                                    <td>{{$bill->bill_number}}</td>
                                    <td>
                                        @if ($bill->transporter)
                                            Transporter | <a href="{{ route('transporters.show',$bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill->transporter->name }}</a> 
                                        @elseif($bill->vendor && ($bill->container == NULL || $bill->top_up == NULL))
                                            Vendor | <a href="{{ route('vendors.show',$bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill->vendor->name }}</a> 
                                        @elseif ( $bill->container || $bill->top_up)
                                            Fuel Topup | <a href="{{ route('containers.show', $bill->container->id) }}" style="color: blue" target="_blank">{{ $bill->container->name }}</a> 
                                        @elseif ( $bill->invoice)
                                            Invoice VAT | <a href="{{ route('invoices.show', $bill->invoice->id) }}" style="color: blue" target="_blank">{{ $bill->invoice->invoice_number }}</a> 
                                            @elseif ( $bill->ticket)
                                            Ticket | <a href="{{ route('tickets.show', $bill->ticket->id) }}" style="color: blue" target="_blank">{{ $bill->ticket->ticket_number }}</a> 
                                            @elseif ($bill->trip && ($bill->horse || $bill->driver))
                                            Trip Expense | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                        @elseif ($bill->purchase)
                                            {{ $bill->category }} | <a href="{{ route('purchases.show', $bill->purchase->id) }}" style="color: blue" target="_blank">{{ $bill->purchase->purchase_number }}</a> 
                                        @endif
                                    </td>
                                    <td>{{$bill->bill_date}}</td>
                                    <td>{{$bill->currency ? $bill->currency->name : ""}}</td> 
                                    <td>
                                        @if ($bill->total)
                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($bill->payments)
                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->payments->sum('amount'),2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($bill->balance)
                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance,2)}}
                                        @else   
                                        {{$bill->currency ? $bill->currency->symbol : ""}}0.00
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                    <td><span class="badge bg-{{($bill->authorization == 'approved') ? 'success' : (($bill->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($bill->authorization == 'approved') ? 'approved' : (($bill->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('bills.show',$bill->id)}}"  ><i class="fas fa-eye color-default"></i>View</a></li>
                                            </ul>
                                        </div>
                                        @include('bills.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($bills))
                                        {{ $bills->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Bill <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

