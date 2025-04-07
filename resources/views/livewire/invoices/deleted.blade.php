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
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  
                                  <tr>

                                    <th class="th-sm">Invoice#
                                    </th>
                                    <th class="th-sm">Customer
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
                                    <td>{{$invoice->invoice_number}}</td>
                                    <td>
                                        {{$invoice->customer ? $invoice->customer->name : ""}}
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
                                                <li><a href="#" wire:click="restore({{$invoice->id}})"><i class="fas fa-refresh color-success"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Invoices Found ....
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



    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="invoiceRestoreModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to restore this Invoice?</strong> </center>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>

