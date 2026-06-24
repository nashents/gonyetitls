<div>
    <div class="tab-content bg-white p-15">
        <div role="tabpanel" class="tab-pane active" id="personal">
            <form wire:submit.prevent="update()">
                <div class="form-group">
                    <label for="name">Company Name<span class="required" style="color: red">*</span></label>
                    <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email<span class="required" style="color: red">*</span></label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="email" placeholder="Email used to receive emails" required>
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div> 
                            <div class="form-group">
                                <label for="email">2nd Email</label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="second_email" placeholder="2nd Email" >
                                @error('second_email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div> 
                            <div class="form-group">
                                <label for="email">3rd Email</label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="third_email" placeholder="3rd Email" >
                                @error('third_email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div> 
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" required>
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="phonenumber">2nd Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="second_phonenumber" placeholder="2nd Phonenumber">
                                @error('second_phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="phonenumber">3rd Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="third_phonenumber" placeholder="3rd Phonenumber">
                                @error('third_phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="country" >
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div> 
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City/Town</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="city" >
                             @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="suburb">Suburb</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="suburb">
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="street_address">
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>  
                    <div class="row">
                        <div class="col-md-4">
                            <label for="one" class="radio-label">Vat Number</label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="vat_number" placeholder="Enter VAT Number">
                            @error('vat_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="one" class="radio-label">TIN Number</label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="tin_number" placeholder="Enter TIN Number" >
                            @error('tin_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="one" class="radio-label">Vendor Number</label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="vendor_number" placeholder="Enter Vendor Number" >
                            @error('vendor_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>    
                    <div class="btn-group" role="group" style="float: right;">
                        <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <br>
                    <hr>     
            </form>
          
        </div>
        <div role="tabpanel" class="tab-pane" id="hr">
            @livewire('companies.hr', ['id' => $company->id])
          </div>
        <div role="tabpanel" class="tab-pane" id="invoices">
            @livewire('companies.invoices', ['id' => $company->id])
          </div>
        <div role="tabpanel" class="tab-pane" id="dates">
            @livewire('companies.dates', ['id' => $company->id])
          </div>
        <div role="tabpanel" class="tab-pane" id="documents">
            @livewire('documents.index', ['id' => $company->id,'category' =>'company'])
          </div>
          <div role="tabpanel" class="tab-pane" id="bank_accounts">
            @livewire('bank-accounts.index', ['id' => $company->id])
          </div>
        <div role="tabpanel" class="tab-pane" id="notifications">
            @livewire('notifications.index')
        </div>
        <div role="tabpanel" class="tab-pane" id="modules">
            @livewire('modules.index', ['id' => $company->id])
        </div>
        <div role="tabpanel" class="tab-pane" id="settings">
            @livewire('companies.settings', ['id' => $company->id])
        </div>
        <div role="tabpanel" class="tab-pane" id="budgets">
            @livewire('budgets.index', ['id' => $company->id])
        </div>
        <div role="tabpanel" class="tab-pane" id="integrations">
            @livewire('company-integrations.index', ['id' => $company->id])
        </div>
      
      
       
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right mt-10" >
                   <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                </div>
            </div>
            </div>
    </div>
</div>
