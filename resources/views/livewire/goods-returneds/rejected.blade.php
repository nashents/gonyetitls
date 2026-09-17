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
                                Rejected Goods Returns
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
                                    <th class="th-sm">Rejected By</th>
                                    <th class="th-sm">Comments</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($goods_returneds))
                                <tbody>
                                    @forelse ($goods_returneds as $goods_returned)
                                  <tr>
                                    <td>{{ucfirst($goods_returned->goods_returned_number)}}</td>
                                    <td>{{ucfirst($goods_returned->department)}}</td>
                                    <td>{{ $goods_returned->goods_received?->goods_received_number }}</td>
                                    <td>{{ $goods_returned->vendor?->name }}</td>
                                    <td>
                                        @foreach ($goods_returned->goods_returned_items as $item)
                                            <div>{{ $item->qty_returned }} x {{ $item->product?->name }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        {{ $goods_returned->authorized_by?->name }} {{ $goods_returned->authorized_by?->surname }}
                                        <br><small class="text-muted">{{ $goods_returned->authorization_date }}</small>
                                    </td>
                                    <td>{{ $goods_returned->authorization_comments }}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('goods_returneds.show',$goods_returned->id)}}"><i class="fas fa-eye color-default"></i> View</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rejected Returns Found ....
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
</div>
