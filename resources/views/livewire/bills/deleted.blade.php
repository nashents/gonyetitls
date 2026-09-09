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
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search deleted bills...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>

                                  <tr>
                                    <th class="th-sm">Bill#
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Deleted By
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($bills))
                                <tbody>
                                    @forelse ($bills as $bill)
                                  <tr wire:key="deleted-bill-row-{{ $bill->id }}">
                                    <td>
                                        {{$bill->bill_number}}
                                        <br>
                                        <small>
                                            <strong>CreatedBy:</strong> {{$bill->user ? $bill->user->name : ""}} {{$bill->user ? $bill->user->surname : ""}} <br>
                                            <strong>CreatedOn:</strong> {{$bill->created_at}}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($bill->transporter)
                                            Transporter | {{ $bill->transporter ? $bill->transporter->name  : ""}}
                                        @elseif($bill->vendor)
                                            Vendor | {{ $bill->vendor ? $bill->vendor->name : "" }}
                                        @endif
                                        @if ($bill->description)
                                            <br>
                                            {{$bill->description}}
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
                                        {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->payments->sum('amount'),2)}}
                                    </td>
                                    <td>
                                         {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance ? $bill->balance : 0,2)}}
                                    </td>
                                    <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                    <td>
                                        {{$bill->deleted_by ? $bill->deleted_by->name : ""}} {{$bill->deleted_by ? $bill->deleted_by->surname : ""}}
                                        <br>
                                        <small>{{$bill->deleted_at}}</small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="restore({{$bill->id}})"><i class="fas fa-refresh color-success"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Deleted Bills Found ....
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



    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="billRestoreModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to restore this Bill?</strong> </center>
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
