<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- <a href="" data-toggle="modal" data-target="#top_upModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Top Up</a>
        <br>
        <br> --}}
        <table id="top_upsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Order#
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">Fuel Type
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Ccy
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Amount
                </th>
              </tr>
            </thead>
            @if ($top_ups->count()>0)
            <tbody>
                @foreach ($top_ups as $top_up)
              <tr>
                <td>{{$top_up->order_number}}</td>
                <td>{{$top_up->date}}</td>
                <td>{{$top_up->fuel_type}}</td>
                <td>{{$top_up->quantity ? $top_up->quantity." Litres" : ""}}</td>
                <td>{{$top_up->currency ? $top_up->currency->name : ""}}</td>
                <td>
                    @if ($top_up->rate)
                    {{$top_up->currency ? $top_up->currency->symbol : ""}}{{number_format($top_up->rate,2)}}        
                    @endif
                </td>
                <td>
                    @if ($top_up->amount)
                          {{$top_up->currency ? $top_up->currency->symbol : ""}}{{number_format($top_up->amount,2)}}        
                    @endif
                </td>
               
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="top_upModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i>Fuel TopUp <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="topup()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="container_id">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="container_id" class="form-control" required disabled>
                                       <option value="">Select Station</option>
                                       @foreach ($containers as $container)
                                        <option value="{{$container->id}}">{{$container->name}}</option>
                                       @endforeach
                                   </select>
                                    @error('container_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_id">Vendors</label>
                                   <select wire:model.debounce.300ms="vendor_id" class="form-control"  >
                                       <option value="">Select Vendor</option>
                                       @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                       @endforeach
                                   </select>
                                    @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name"> Date<span class="required" style="color: red">*</span></label>
                                    <input type="date"  class="form-control" wire:model.debounce.300ms="date" placeholder="Enter TopUp date" required >
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="fuel_type" class="form-control" required disabled>
                                       <option value="">Select Fuel Type</option>
                                        <option value="{{ $fuel_type }}">{{ $fuel_type }}</option>
                                       
                                   </select>
                                    @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="currency_id">Currency</label>
                                   <select wire:model.debounce.300ms="currency_id" class="form-control"  >
                                       <option value="">Select Currency</option>
                                       @foreach ($currencies as $currency)
                                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                                       @endforeach
                                   </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                       <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quantity">Quantity<span class="required" style="color: red">*</span> </label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required>
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rate">Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" >
                                    @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
