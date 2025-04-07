<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Credit Note</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                        <div class="modal-body">
                                    
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">CreditNote#<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="credit_note_number" placeholder="Enter Credit Note Number" required >
                                        @error('credit_note_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Credit Note Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subheading">Subheading</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="subheading" placeholder="Enter Subheading">
                                        @error('subheading') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    </div>
                            </div>
                            
                            


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Invoice(s)<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="search" placeholder="Search with invoice number, customer, date..." class="form-control">
                                        <select wire:model.debounce.300ms="selectedInvoice" class="form-control" size="4" required >
                                           <option value="" disabled>Select Invoice</option>
                                            @foreach ($invoices as $invoice)
                                                <option value="{{$invoice->id}}">{{$invoice->invoice_number}} | {{$invoice->customer ? $invoice->customer->name : ""}} | {{ $invoice->date }} | {{ $invoice->currency ? $invoice->currency->name : "" }} {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}} </option> 
                                            @endforeach
                                       </select>
                                        @error('selectedInvoice') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" min="1" max="1" class="form-control" wire:model.debounce.300ms="qty" disabled required >
                                        @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount"  required disabled>
                                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vat">Currency<span class="required" style="color: red">*</span></label>
                                       <select class="form-control" wire:model.debounce.300ms="currency_id" required disabled>
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} </option>                                        
                                        @endforeach
                                       </select>
                                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @if (!is_null($currency_id))
                                    @if (Auth::user()->employee->company)
                                        @if ($currency_id != Auth::user()->employee->company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Exchange Rate</label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate" placeholder="The exchange rate @ trip date">
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div> 
                                        @endif
                                    @endif
                                @endif  
                                    </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vat">Tax Amount</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="tax_amount"  disabled/>
                                        @error('tax_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                            
                                </div>
                                
                            </div>
           
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Reason</label>
                                       <textarea wire:model.debounce.300ms="credit_note_reason" class="form-control" cols="30" rows="4" placeholder="Enter reason for credit note"></textarea>
                                        @error('credit_note_reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="footer">Footer</label>
                                       <textarea class="form-control" wire:model.debounce.300ms="footer" cols="30" rows="3"></textarea>
                                        @error('footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                            </div>
                            <!-- /.btn-group -->
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
