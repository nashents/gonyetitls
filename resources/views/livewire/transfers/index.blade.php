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
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <a href="#" data-toggle="modal" data-target="#transferModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transfer</a>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: -80px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 15px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                               
                                <!-- /input-group -->
                            </div>
                          
                           
                            </div>
                            <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search transfers...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">#
                                    </th>
                                    <th class="th-sm">Item
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($transfers))
                                <tbody>
                                    @forelse ($transfers as $transfer)
                                  <tr>
                                    <td>{{$transfer->id}}</td>
                                    <td>
                                        @if ($transfer->inventory)
                                            @php
                                                $inventory = $transfer->inventory;
                                            @endphp
                                            {{$inventory->inventory_number}} {{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}}   
                                            <br>
                                            {{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}} 
                                        @elseif ($transfer->tyre)
                                            @php
                                            $tyre = $transfer->tyre;
                                            @endphp
                                           {{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} )
                                        @endif
                                    </td>
                                    @php
                                        $from = App\Models\Store::find($transfer->from);
                                        $to = App\Models\Store::find($transfer->to);
                                    @endphp
                                    <td>{{$from ? $from->name : "" }}</td>
                                    <td>{{$to ? $to->name : "" }}</td>
                                    <td>{{$transfer->date }}</td>
                                    <td>{{$transfer->comments }}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($transfer->reversal == 0)
                                                <li><a href="#" wire:click="showReverse({{$transfer->id}})"  ><i class="fas fa-refresh color-default"></i> Reverse</a></li>
                                                @endif
                                               
                                                
                                            </ul>
                                        </div>
                                       
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No transfers found ....
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
                                    @if (isset($transfers))
                                        {{ $transfers->links() }} 
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="reverseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to reverse this Transfer?</strong> </center> 
                </div>
                <form wire:submit.prevent="reverse()" method="POST" >
                <div class="modal-footer no-border">
                    <div class="btn-gate_passe" role="gate_passe">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Reverse</button>
                    </div>
                    <!-- /.btn-gate_passe -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Transfer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">From Store<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedStore" required>
                                        <option value="">Select Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{$store->id}}">{{$store->name}}</option>
                                        @endforeach
                                </select>
                                @error('selectedStore') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="name">Inventory Items<span class="required" style="color: red">*</span></label>
                                <small style="color:red">*You can multi select items by clicking ctrl and selecting multiple items</small>
                                <input type="text" wire:model.debounce.300ms="searchInventory" placeholder="Search inventory..." class="form-control">
                                <select class="form-control" wire:model.debounce.300ms="inventory_id" required multiple>
                                        <option value="">Select Item</option>
                                        @foreach ($inventories as $inventory)
                                            <option value="{{$inventory->id}}">{{$inventory->inventory_number}} {{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}} {{$inventory->serial_number ? "(".$inventory->serial_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('inventory_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">To Store<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="to_store_id" required>
                                        <option value="">Select Store</option>
                                        @if (isset($selectedStore))
                                            @foreach ($stores as $store)
                                                @if ($store->id != $selectedStore)
                                                    <option value="{{$store->id}}">{{$store->name}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                       
                                </select>
                                @error('to_store_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Comments</label>
                                <textarea  class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="4"></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
   



</div>

