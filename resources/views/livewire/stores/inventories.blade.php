<div>
    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th class="th-sm">Inventory#
            </th>
            <th class="th-sm">Date
            </th>
            <th class="th-sm">Product
            </th>
            <th class="th-sm">ID#s
            </th>
            <th class="th-sm">Item Contents
            </th>
            <th class="th-sm">Currency
            </th>
            <th class="th-sm">Price
            </th>
            <th class="th-sm">Status
            </th>
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if (isset($inventories))
        <tbody>
            @forelse ($inventories as $inventory)
          <tr>
            <td>{{$inventory->inventory_number}}</td>
            <td>{{$inventory->purchase_date}}</td>
            <td>{{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}}</td>
            <td>{{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}}</td>
            <td>{{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}}</td>
            <td>{{$inventory->currency ? $inventory->currency->name : ""}}</td>
            <td>
                @if ($inventory->rate)
                    {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->rate,2)}}  
                @endif
            </td>
            <td><span class="badge bg-{{$inventory->status == 1 ? "success" : "danger"}}">{{$inventory->status == 1 ? "Instore" : "Out Of stock"}}</span></td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{route('inventories.show',$inventory->id )}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                        <li><a href="#" wire:click="showTransfer({{$inventory->id}})"  ><i class="fa fa-exchange color-success"></i> Transfer</a></li>
                    </ul>
                </div>
                @include('inventories.delete')
        </td>
          </tr>
          @empty
          <tr>
            <td colspan="9">
                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                    No inventory items from store found ....
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add transfer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
              </div>
              <form wire:submit.prevent="transfer()" >
              <div class="modal-body">
                  <div class="row">
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="name">From<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="store_id" required disabled>
                                      <option value="">Select Store</option>
                                      @foreach ($stores as $store)
                                          <option value="{{$store->id}}">{{$store->name}}</option>
                                      @endforeach
                              </select>
                              @error('store_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="name">To<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="to" required>
                                      <option value="">Select Store</option>
                                      @foreach ($stores as $store)
                                      @if ($store->id != $store_id)
                                      <option value="{{$store->id}}">{{$store->name}}</option> 
                                      @endif 
                                      @endforeach
                              </select>
                              @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
