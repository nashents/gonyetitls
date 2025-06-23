<div>
    <form wire:submit.prevent="update()">
            <div class="row">
                @if (Auth::user()->is_admin())
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                      <select class="form-control" wire:model.debounce.300ms="currency_id" required >
                            <option value="">Select Reporting Currency</option>
                            @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                            @endforeach
                      </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                @else
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                      <select class="form-control" wire:model.debounce.300ms="currency_id" required disabled >
                            <option value="">Select Reporting Currency</option>
                            @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                            @endforeach
                      </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif  
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vat">Exchange Rates Frequency<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="frequency" required >
                            <option value="">Select Exchange Rate Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        @error('frequency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small style="color: blue"> <a href="{{route('exchange_rates.index')}}" target="_blank">Click me to update exchange rates</a></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vat">Interest Rate</label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="interest" placeholder="Company Loans Interest %" >
                        @error('interest') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-10" style="margin-top:30px;">
                        <input type="checkbox" wire:model.debounce.300ms="rates_managed_by_finance"   class="line-style" />
                        <label for="one" class="radio-label">Trip Rates Managed By Finance</label>
                        @error('rates_managed_by_finance') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
         
            <div class="btn-group" role="group" style="float: right;">
                <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
            </div>
            <br>
            <hr>
            
           
    </form>
</div>
