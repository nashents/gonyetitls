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
                                    <th class="th-sm">Transfer#
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Item(s)
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($transfers))
                                <tbody>
                                    @forelse ($transfers as $transfer)
                                  <tr>
                                    <td>{{$transfer->transfer_number}}</td>
                                    @php
                                        $from = App\Models\Store::find($transfer->from);
                                        $to = App\Models\Store::find($transfer->to);
                                    @endphp
                                    <td>{{$from?->name}}</td>
                                    <td>{{$to?->name}}</td>
                                    <td>{{$transfer->date }}</td>
                                    <td>{{$transfer->comments }}</td>
                                    <td>
                                        @php
                                            $transfer_items = App\Models\TransferItem::where('transfer_id',$transfer->id)->get();
                                        @endphp
                                        @if (!empty($transfer_items))
                                            @foreach ($transfer_items as $transfer_item)
                                                @if ($transfer_item->inventory)
                                                        @php
                                                            $inventory = $transfer_item->inventory;
                                                        @endphp
                                                        {{$inventory->inventory_number}} {{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}}   
                                       
                                                @elseif($transfer_item->tyre)
                                                        @php
                                                            $tyre = $transfer_item->tyre;
                                                        @endphp
                                                        {{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} )
                                                @endif
                                                ({{$transfer_item->qty}})
                                                @if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                        <td>
                                        <span class="badge bg-{{($transfer->authorization == 'approved') ? 'success' : (($transfer->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($transfer->authorization == 'approved') ? 'approved' : (($transfer->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                        @php
                                            $user = App\Models\User::find($transfer->authorized_by_id);
                                        @endphp
                                        <small>
                                        @if ($user)
                                            <br>
                                          <strong>{{$transfer->authorization == "approved" ? "ApprovedBy" : "RejectedBy"}}:</strong> {{$user->name}} {{$user->surname}}
                                        @endif
                                        @if ($transfer->authorization_date)
                                            <br>
                                         <strong>AuthorizedOn:</strong> {{Carbon\Carbon::parse($transfer->authorization_date)->format('Y-m-d')}}
                                        @endif
                                        @if ($transfer->authorization_comments)
                                            <br>
                                         <strong>Comments:</strong> {{$transfer->authorization_comments}}
                                        @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{$transfer->is_received == True ? 'success' :  'primary' }}">{{ $transfer->is_received == True ? "Received" : "Intransit" }}</span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                              <li><a href="{{route('transfers.show',$transfer->id)}}"   ><i class="fas fa-eye color-default"></i> View</a></li>
                                            </ul>
                                        </div>
                                       
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Transfers Found ....
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
        <div class="modal-dialog mw-100 w-80" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Transfer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Reason for transfer<span class="required" style="color: red">*</span></label>
                                <textarea  class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="2" placeholder="Specific " required></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                            <label for="horse">Products<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/Model/Part#,..." class="form-control" >
                            <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                    <option value="" disabled>Select Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{$product->id}}"
                                            @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[0] ?? null) != $product->id) 
                                                disabled 
                                            @endif
                                            >  {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Inventory#:  ".$product->product_number : ""}}, {{$product->identification_number ? "ID#: ".$product->identification_number : ""}} 
                                            
                                        @php
                                            $relation = match ($department) {
                                                'inventory' => 'inventories',
                                                'tyre'      => 'tyres',
                                                'asset'     => 'assets',
                                                default     => null,
                                            };

                                            $count = null;

                                            if ($relation) {
                                                $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                    ->where('status', 1)
                                                    ->where('balance', '>', 0)
                                                    ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                    ->sum('balance');
                                            }
                                        @endphp

                                        @if(!is_null($count))
                                            {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                        @endif
                                            
                                        </option>
                                    @endforeach
                            </select>
                            @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        </div>
                               @if(in_array($department,['asset','inventory']))
                                <div class="col-md-4">
                                    @if ($department == "inventory")
                                        <div class="form-group">
                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}"
                                                            @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[0] ?? null) != $inventory->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($inventory->total)
                                                                {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}} {{ number_format((float)($inventory->balance ?? 0) * (float)($inventory->amount ?? 0),2)}}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($department == "asset")
                                         <div class="form-group">
                                            <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedAsset.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($assets as $asset)
                                                        <option value="{{$asset->id}}"
                                                            @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[0] ?? null) != $asset->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($asset->total)
                                                                {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{ number_format((float)($asset->balance ?? 0) * (float)($asset->amount ?? 0),2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedAsset.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                                @elseif($department == "tyre")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedTyre.0" class="form-control" required size = "4">
                                                    <option value="" selected>Select Item</option>
                                                    @foreach ($tyres as $tyre)
                                                        <option value="{{$tyre->id}}"
                                                            @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[0] ?? null) != $tyre->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($tyre->total)
                                                                {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{ number_format((float)($tyre->balance ?? 0) * (float)($tyre->amount ?? 0),2)}} 
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedTyre.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                @if ($max)
                                    <input type="number" step="any" min="0"  max="{{ $max[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
                                @else
                                    <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
                                @endif
                                @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/Model/Part#,..." class="form-control" >
                                    <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                            <option value="" disabled>Select Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}"
                                                    @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[0] ?? null) != $product->id) 
                                                        disabled 
                                                    @endif
                                                    >  {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Inventory#:  ".$product->product_number : ""}}, {{$product->identification_number ? "ID#: ".$product->identification_number : ""}} 
                                                    
                                                @php
                                                    $relation = match ($department) {
                                                        'inventory' => 'inventories',
                                                        'tyre'      => 'tyres',
                                                        'asset'     => 'assets',
                                                        default     => null,
                                                    };

                                                    $count = null;

                                                    if ($relation) {
                                                        $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                            ->where('status', 1)
                                                            ->where('balance', '>', 0)
                                                            ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                            ->sum('balance');
                                                    }
                                                @endphp

                                                @if(!is_null($count))
                                                    {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                @endif
                                                    
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if(in_array($department,['asset','inventory']))
                                <div class="col-md-4">
                                    @if ($department == "inventory")
                                        <div class="form-group">
                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}"
                                                            @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[0] ?? null) != $inventory->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($inventory->total)
                                                                {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}} {{ number_format((float)($inventory->balance ?? 0) * (float)($inventory->amount ?? 0),2)}}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($department == "asset")
                                         <div class="form-group">
                                            <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedAsset.{{$value}}" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($assets as $asset)
                                                        <option value="{{$asset->id}}"
                                                            @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[0] ?? null) != $asset->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($asset->total)
                                                                {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{ number_format((float)($asset->balance ?? 0) * (float)($asset->amount ?? 0),2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedAsset.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                                @elseif($department == "tyre")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedTyre.{{$value}}" class="form-control" required size = "4">
                                                    <option value="" selected>Select Item</option>
                                                    @foreach ($tyres as $tyre)
                                                        <option value="{{$tyre->id}}"
                                                            @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[0] ?? null) != $tyre->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($tyre->total)
                                                                {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{ number_format((float)($tyre->balance ?? 0) * (float)($tyre->amount ?? 0),2)}} 
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedTyre.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                    @if ($max)
                                        <input type="number" step="any" min="0"  max="{{ $max[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Qty" required>
                                    @else
                                        <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
                                    @endif
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div> 
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Item</button>
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

