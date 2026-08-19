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
                                            <span class="input-group-addon">
                                                Filter By
                                            </span>
                                            <select wire:model.debounce.300ms="goods_received_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Created At</option>
                                                <option value="date">Received At</option>
                                            </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" >
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                From
                                            </span>
                                            <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" >
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
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search GRVs...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">GRV#</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">ReceivedBy</th>
                                    <th class="th-sm">Vendor</th>
                                    <th class="th-sm">Date</th>
                                    <th class="th-sm">Item(s)</th>
                                    <th class="th-sm">Auth</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($goods_receiveds))
                                <tbody>
                                    @forelse ($goods_receiveds as $goods_received)
                                  <tr>
                                    <td>{{ucfirst($goods_received->goods_received_number)}}</td>
                                    <td>{{ucfirst($goods_received->department)}}</td>
                                    <td>{{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}}</td>
                                    <td>{{$goods_received->vendor ? $goods_received->vendor->name : ""}}</td>
                                    <td>{{$goods_received->date}}</td>
                                    <td>
                                        {{$goods_received->inventories->count() + $goods_received->tyres->count() + $goods_received->assets->count()}}
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">rejected</span>
                                        <small>
                                        @if ($goods_received->authorized_by)
                                            <br><strong>RejectedBy:</strong> {{$goods_received->authorized_by->name}} {{$goods_received->authorized_by->surname}}
                                        @endif
                                        @if ($goods_received->authorization_date)
                                            <br><strong>On:</strong> {{ \Carbon\Carbon::parse($goods_received->authorization_date)->format('Y-m-d') }}
                                        @endif
                                        @if ($goods_received->authorization_comments)
                                            <br><strong>Comments:</strong> {{$goods_received->authorization_comments}}
                                        @endif
                                        </small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('goods_receiveds.show',$goods_received->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rejected GRVs Found ....
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
</div>
