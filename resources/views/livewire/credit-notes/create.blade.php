<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Credit Note</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" >
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
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name">Attach to Invoice?</label>
                                                <div class="mb-10">
                                                    <input type="radio" wire:model="invoice_attached" value="Yes" class="line-style" id="invoice_attached_yes" />
                                                    <label for="invoice_attached_yes" class="radio-label">Yes</label>
                                                    <input type="radio" wire:model="invoice_attached" value="No" class="line-style" id="invoice_attached_no" />
                                                    <label for="invoice_attached_no" class="radio-label">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($invoice_attached === 'Yes')
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="country">Invoice(s)<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search" placeholder="Search with invoice number, customer, date..." class="form-control">
                                                <select wire:model="selectedInvoice" class="form-control" size="4" required >
                                                   <option value="" disabled>Select Invoice</option>
                                                    @foreach ($invoices as $invoice)
                                                        <option value="{{$invoice->id}}">{{$invoice->invoice_number}} | {{$invoice->customer ? $invoice->customer->name : ""}} | {{ $invoice->date }} | {{ $invoice->currency ? $invoice->currency->name : "" }} {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}} </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedInvoice') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="vat">Customer<span class="required" style="color: red">*</span></label>
                                               <select class="form-control" wire:model="selectedCustomer" required>
                                                <option value="">Select Customer</option>
                                                @foreach ($customers as $customerOption)
                                                        <option value="{{ $customerOption->id }}">{{ $customerOption->name }} </option>
                                                @endforeach
                                               </select>
                                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if ($invoice_attached === 'Yes' && $invoice)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Invoice Line Items <small>(click "Add" to credit a specific item, or add a custom line below)</small></label>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Description</th>
                                                        <th class="text-right">Invoice Amount</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($invoiceItems as $invoiceItem)
                                                        <tr>
                                                            <td>{{ $invoiceItem->description ?: $invoiceItem->trip_details }}</td>
                                                            <td class="text-right">{{ $invoice->currency ? $invoice->currency->symbol : "" }}{{ number_format($invoiceItem->subtotal, 2) }}</td>
                                                            <td class="text-right">
                                                                <button type="button" class="btn btn-xs bg-primary" wire:click="addFromInvoiceItem({{ $invoiceItem->id }})"><i class="fa fa-plus"></i> Add</button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3">No line items found on this invoice.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif

                                    @if (($invoice_attached === 'Yes' && $invoice) || ($invoice_attached === 'No' && $selectedCustomer))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Credit Note Item(s)<span class="required" style="color: red">*</span></label>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Description</th>
                                                        <th style="width:200px" class="text-right">Amount</th>
                                                        <th style="width:60px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($rows as $index => $row)
                                                        <tr wire:key="credit-note-row-{{ $index }}">
                                                            <td>
                                                                <input type="text" class="form-control" wire:model.lazy="rows.{{ $index }}.description" placeholder="Enter description" required>
                                                                @error('rows.'.$index.'.description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </td>
                                                            <td>
                                                                <input type="number" step="any" class="form-control" wire:model.lazy="rows.{{ $index }}.amount" placeholder="0.00" required>
                                                                @error('rows.'.$index.'.amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-xs bg-danger" wire:click="removeRow({{ $index }})"><i class="fa fa-trash"></i></button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @error('rows') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            <button type="button" class="btn btn-sm bg-gray" wire:click="addRow"><i class="fa fa-plus"></i> Add Custom Line</button>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vat">Currency<span class="required" style="color: red">*</span></label>
                                               <select class="form-control" wire:model.debounce.300ms="currency_id" required @if ($invoice_attached === 'Yes') disabled @endif>
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
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="tax_amount"/>
                                                @error('tax_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>

                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Subtotal</label>
                                                <input type="text" class="form-control" value="{{ number_format($subtotal ?? 0, 2) }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input type="text" class="form-control" value="{{ number_format($total ?? 0, 2) }}" disabled>
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
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="store" wire:loading.class="opacity-50 cursor-not-allowed"><i class="fa fa-save"></i>Save</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="invoice_productModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeProduct()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="comment">Product<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="product_name" placeholder="Enter Name" required>
                        @error('product_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
