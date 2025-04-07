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
                                    <th class="th-sm">#
                                    </th>
                                    <th class="th-sm">DisposedBy
                                    </th>
                                    <th class="th-sm">Item
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($disposes))
                                <tbody>
                                    @forelse ($disposes as $dispose)
                                  <tr>
                                    <td>{{$dispose->id}}</td>
                                    <td>{{$dispose->user ? $dispose->user->name : ""}} {{$dispose->user ? $dispose->user->surname : ""}}</td>
                                    <td>
                                        @if ($dispose->inventory)
                                            {{$dispose->inventory ? $dispose->inventory->inventory_number : ""}} {{$dispose->inventory->product->brand ? $dispose->inventory->product->brand->name : ""}} {{$dispose->inventory->product ? $dispose->inventory->product->name : ""}} {{$dispose->inventory->product ? $dispose->inventory->product->model : ""}} {{$dispose->inventory->serial_number ? "(".$dispose->inventory->serial_number.")" : ""}}
                                        @elseif ($dispose->asset)
                                            {{$dispose->asset ? $dispose->asset->asset_number : ""}} {{$dispose->asset->product->brand ? $dispose->asset->product->brand->name : ""}} {{$dispose->asset->product ? $dispose->asset->product->name : ""}} {{$dispose->asset->product ? $dispose->asset->product->model : ""}} {{$dispose->asset->serial_number ? "(".$dispose->asset->serial_number.")" : ""}}
                                        @elseif ($dispose->tyre)
                                            {{$dispose->tyre ? $dispose->tyre->tyre_number : ""}} {{$dispose->tyre->product->brand ? $dispose->tyre->product->brand->name : ""}} {{$dispose->tyre->product ? $dispose->tyre->product->name : ""}} {{$dispose->tyre->width}} / {{$dispose->tyre->aspect_ratio}} R {{$dispose->tyre->diameter}} {{$dispose->tyre->serial_number ? "(".$dispose->tyre->serial_number.")" : ""}}
                                        @endif
                                    </td>
                                    <td>{{$dispose->date}}</td>
                                    <td>{{$dispose->comments}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($dispose->inventory)
                                                <li><a href="{{ route('inventories.show', $dispose->inventory->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @elseif ($dispose->asset)
                                                <li><a href="{{ route('assets.show', $dispose->asset->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @elseif ($dispose->tyre)
                                                <li><a href="{{ route('tyres.show', $dispose->tyre->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @endif
                                               
                                              
                                            </ul>
                                        </div>
                                     
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Disposed Items Found ....
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
                                    @if (isset($disposes))
                                        {{ $disposes->links() }} 
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

