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
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search deleted payments...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Payment#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">MOP
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amt
                                    </th>
                                    <th class="th-sm">Deleted By
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($payments))
                                <tbody>
                                    @forelse ($payments as $payment)
                                  <tr wire:key="deleted-payment-row-{{ $payment->id }}">
                                    <td>
                                        {{$payment->payment_number}}
                                        <br>
                                        <small>
                                            <strong>Recorded By:</strong> {{$payment->user?->name}} {{$payment->user?->surname}} <br>
                                            <strong>On:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i:s') }}
                                        </small>
                                    </td>
                                    <td>{{Carbon\Carbon::parse($payment->date)->format('d M Y')}}</td>
                                    <td>
                                        @if ($payment->invoice)
                                            {{$payment->customer ? $payment->customer->name : ""}} Payment for invoice# {{$payment->invoice ? $payment->invoice->invoice_number : ""}} <br>
                                        @elseif ($payment->bill)
                                            Bill# {{$payment->bill ? $payment->bill->bill_number : ""}} Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                        @elseif ($payment->customer && !$payment->invoice)
                                            {{$payment->customer ? $payment->customer->name : ""}} deposit
                                        @elseif ($payment->vendor && !$payment->bill)
                                            {{$payment->vendor ? $payment->vendor->name : ""}} payment
                                        @endif
                                        @if ($payment->description)
                                            {{$payment->description}}
                                        @endif
                                    </td>
                                    <td>{{$payment->mode_of_payment}}</td>
                                    <td>{{$payment->account ? $payment->account->name : ""}}</td>
                                    <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                                    <td>
                                        @if ($payment->amount)
                                            {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        {{$payment->deleted_by ? $payment->deleted_by->name : ""}} {{$payment->deleted_by ? $payment->deleted_by->surname : ""}}
                                        <br>
                                        <small>{{$payment->deleted_at}}</small>
                                    </td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                               <li><a href="#" wire:click="restore({{$payment->id}})"><i class="fas fa-refresh color-success"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Deleted Payments Found ....
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
                                        @if (isset($payments))
                                            {{ $payments->links() }}
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="paymentRestoreModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                    <center> <strong>Are you sure you want to restore this Payment?</strong> </center>
                    </div>
                    <form wire:submit.prevent="update()" >

                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i>Restore</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

</div>
