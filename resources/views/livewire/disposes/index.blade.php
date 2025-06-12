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

                            <table id="disposesTable"  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                    <th class="th-sm">Item
                                    </th>
                                    <th class="th-sm">DisposedBy
                                    </th>
                                    <th class="th-sm">DisposedOn
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($disposes))
                                <tbody>
                                    @forelse ($disposes as $dispose)
                                  <tr>
                                     <td>
                                        @if ($dispose->inventory)
                                            <strong>Item:</strong> {{$dispose->inventory ? $dispose->inventory->inventory_number : ""}}
                                            <br>
                                            <strong>Product:</strong> {{$dispose->inventory->product->brand ? $dispose->inventory->product->brand->name : ""}} {{$dispose->inventory->product ? $dispose->inventory->product->name : ""}} {{$dispose->inventory->product ? $dispose->inventory->product->model : ""}}
                                            <br>
                                            <strong>S/N</strong>{{$dispose->inventory->serial_number ? "(".$dispose->inventory->serial_number.")" : ""}}
                                        @elseif ($dispose->asset)
                                            <strong>Asset:</strong> {{$dispose->asset ? $dispose->asset->asset_number : ""}}
                                            <br>
                                            <strong>Product:</strong> {{$dispose->asset->product->brand ? $dispose->asset->product->brand->name : ""}} {{$dispose->asset->product ? $dispose->asset->product->name : ""}} {{$dispose->asset->product ? $dispose->asset->product->model : ""}}
                                            <br>
                                            <strong>S/N:</strong> {{$dispose->asset->serial_number ? "(".$dispose->asset->serial_number.")" : ""}}
                                        @elseif ($dispose->tyre)
                                            <strong>Tyre:</strong> {{$dispose->tyre ? $dispose->tyre->tyre_number : ""}}
                                            <br>
                                            <strong>Product:</strong> {{$dispose->tyre->product->brand ? $dispose->tyre->product->brand->name : ""}} {{$dispose->tyre->product ? $dispose->tyre->product->name : ""}}
                                            <br>
                                            <strong>Dimensions:</strong> {{$dispose->tyre->width}} / {{$dispose->tyre->aspect_ratio}} R {{$dispose->tyre->diameter}}
                                            <br>
                                            <strong>S/N:</strong> {{$dispose->tyre->serial_number ? "(".$dispose->tyre->serial_number.")" : ""}}
                                        @endif
                                    </td>
                                    <td>{{$dispose->user ? $dispose->user->name : ""}} {{$dispose->user ? $dispose->user->surname : ""}}</td>
                                    <td>{{$dispose->date}}</td>
                                    <td>{{$dispose->comments}}</td>
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

