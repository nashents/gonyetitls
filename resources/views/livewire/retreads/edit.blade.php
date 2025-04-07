<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Retread</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >

                            <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Vendors<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                               <option value="">Select Vendor</option>
                                             @foreach ($vendors as $vendor)
                                                <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                             @endforeach
                                           </select>
                                           <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                            @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Expense Account<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                               <option value="">Select Accounts</option>
                                             @foreach ($accounts as $account)
                                                <option value="{{$account->id}}">{{$account->name}}</option>
                                             @endforeach
                                           </select>
                                           {{-- <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small>  --}}
                                            @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="name">Currencies<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="currency_id" class="form-control">
                                                <option value="">Select Currency</option>
                                              @foreach ($currencies as $currency)
                                                 <option value="{{$currency->id}}">{{$currency->name}}</option>
                                              @endforeach
                                            </select>
                                            <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                                            @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="width">Total</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount"  placeholder="Total Amount">
                                            @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                 
                                   
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required/>
                                        @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Collection Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Collection Date" />
                                        @error('collection_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                               
                            </div>
                           <div class="row">
                            <div class="col-md-8">
                               
                                    <div class="form-group">
                                        <label for="country">Tyres<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchTyre" placeholder="Search tyres..." class="form-control">
                                        <select wire:model.debounce.300ms="tyre_id" class="form-control" required  multiple>
                                            <option value="">Select Tyre</option>
                                            @foreach ($tyres as $tyre)
                                                <option value="{{$tyre->id}}">{{$tyre->tyre_number}}  {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} - {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}})</option>
                                            @endforeach
                                        </select>
                                        @error('tyre_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                               
                         
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Comments</label>
                                  <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="3"></textarea>
                                    @error('description') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                           </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group pull-right mt-10" >
                                   <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-refresh"></i>Update</button>
                                </div>
                            </div>
                            </div>
                    </form>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>




</div>
