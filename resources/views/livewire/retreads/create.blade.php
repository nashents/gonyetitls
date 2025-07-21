<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Add Tyre(s) for retread</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="store()" >

                            <div class="row">
                                    <div class="col-md-6">
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
                                    <div class="col-md-6">
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
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required/>
                                        @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                  <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name">Currencies<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="currency_id" class="form-control">
                                                <option value="">Select Currency</option>
                                              @foreach ($currencies as $currency)
                                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                              @endforeach
                                            </select>
                                            @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="width">Total</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount"  placeholder="Total Amount">
                                            @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                 
                                
                            </div>
                           <div class="row">
                            <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="country">Tyres<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchTyre" placeholder="Search tyres..." class="form-control">
                                        <select wire:model.debounce.300ms="tyre_id.0" class="form-control" required size="4">
                                            <option value="">Select Tyre</option>
                                            @foreach ($tyres as $tyre)
                                                <option value="{{$tyre->id}}">{{$tyre->tyre_number}}
                                                    @if ($tyre->product)
                                                        {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}
                                                    @endif
                                                     - {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tyre.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                               
                                @foreach ($inputs as $key => $value)
                                <div class="row">
                                    <div class="col-md-11">
                                        <div class="form-group">
                                            <label for="country">Tyres<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="tyre_id.{{$value}}" class="form-control" required size="4">
                                               <option value="">Select Tyre</option>
                                             @foreach ($tyres as $tyre)
                                                    <option value="{{$tyre->id}}">{{$tyre->tyre_number}}
                                                        @if ($tyre->product)
                                                            {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}
                                                        @endif
                                                        - {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}})
                                                    </option>
                                             @endforeach
                                           </select>
                                            @error('tyre.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1" style="margin-left:-15px">
                                        <div class="form-group">
                                            <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                @endforeach
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Tyre</button>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <br>
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
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Create</button>
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
