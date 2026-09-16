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
                                    <div class="col-lg-2">
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
                                </div>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Deleted GRVs...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">GRV#</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Vendor</th>
                                    <th class="th-sm">Item(s)</th>
                                    <th class="th-sm">Deleted On</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($goods_receiveds))
                                <tbody>
                                    @forelse ($goods_receiveds as $goods_received)
                                  <tr>
                                    <td>
                                        {{ucfirst($goods_received->goods_received_number)}}
                                        <br>
                                        <small class="text-muted">
                                            <strong>CreatedBy:</strong> {{$goods_received->user ? $goods_received->user->name : ""}} {{$goods_received->user ? $goods_received->user->surname : ""}}<br>
                                            <strong>CreatedOn:</strong> {{$goods_received->created_at ? \Carbon\Carbon::parse($goods_received->created_at)->format('Y-m-d') : ""}}
                                        </small>
                                    </td>
                                    <td>{{ucfirst($goods_received->department)}}</td>
                                    <td>
                                        {{$goods_received->vendor ? $goods_received->vendor->name : ""}}
                                        <br>
                                        <small class="text-muted">
                                            <strong>Received By:</strong> {{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}}<br>
                                            <strong>Received On:</strong> {{$goods_received->date}}
                                        </small>
                                    </td>
                                    <td>
                                        @include('livewire.goods-receiveds.partials.items-summary', ['goods_received' => $goods_received])
                                    </td>
                                    <td>{{$goods_received->deleted_at ? \Carbon\Carbon::parse($goods_received->deleted_at)->format('Y-m-d H:i') : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="showRestore({{$goods_received->id}})"><i class="fa fa-refresh color-default"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Deleted GRVs Found ....
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
                                    @if (isset($goods_receiveds))
                                        {{ $goods_receiveds->links() }}
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="goods_receivedRestoreModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to restore this Goods Received Voucher?</strong></center>
                </div>
                <form wire:submit.prevent="restore()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded"><i class="fas fa-undo"></i> Restore</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
