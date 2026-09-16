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
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search deleted inventory...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">ID/Serial#
                                    </th>
                                    <th class="th-sm">Location
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Deleted On
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($inventories))
                                <tbody>
                                    @forelse ($inventories as $inventory)
                                  <tr>
                                    <td>
                                        <strong>{{$inventory->inventory_number}}</strong>
                                        <br>
                                        {{$inventory->product ? $inventory->product->name : ""}} {{$inventory->product && $inventory->product->brand ? "(".$inventory->product->brand->name.")" : ""}}
                                    </td>
                                    <td>
                                        {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}}
                                        {{$inventory->product && $inventory->product->identification_number ? "ID#: ".$inventory->product->identification_number : ""}}
                                    </td>
                                    <td>
                                        @if ($inventory->store)
                                            <strong>Store:</strong> {{$inventory->store->name}}<br>
                                        @endif
                                        @if ($inventory->rack)
                                            <strong>Rack:</strong> {{$inventory->rack->name}} {{$inventory->rack->rack_number}}<br>
                                        @endif
                                        @if ($inventory->bin)
                                            <strong>Bin:</strong> {{$inventory->bin->name}} {{$inventory->bin->bin_number}}
                                        @endif
                                    </td>
                                    <td>{{$inventory->qty}}</td>
                                    <td>{{$inventory->deleted_at ? \Carbon\Carbon::parse($inventory->deleted_at)->format('Y-m-d H:i') : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="showRestore({{$inventory->id}})"><i class="fa fa-refresh color-default"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Deleted Inventory Found ....
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
                                    @if (isset($inventories))
                                        {{ $inventories->links() }}
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inventoryRestoreModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to restore this inventory item?</strong></center>
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
