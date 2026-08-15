<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Debit Note</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">DebitNote#<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="debit_note_number" placeholder="Enter Debit Note Number" required >
                                        @error('debit_note_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Debit Note Date" required >
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
                                        <label for="name">Attach to Bill?</label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model="bill_attached" value="Yes" class="line-style" id="edit_bill_attached_yes" />
                                            <label for="edit_bill_attached_yes" class="radio-label">Yes</label>
                                            <input type="radio" wire:model="bill_attached" value="No" class="line-style" id="edit_bill_attached_no" />
                                            <label for="edit_bill_attached_no" class="radio-label">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($bill_attached === 'Yes')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="country">Bill(s)<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="search" placeholder="Search with bill number, vendor, date..." class="form-control">
                                        <select wire:model="selectedBill" class="form-control" size="4" required >
                                           <option value="" disabled>Select Bill</option>
                                            @foreach ($bills as $bill)
                                                <option value="{{$bill->id}}">{{$bill->bill_number}} | {{$bill->vendor ? $bill->vendor->name : ""}} | {{ $bill->bill_date }} | {{ $bill->currency ? $bill->currency->name : "" }} {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->total,2)}} </option>
                                            @endforeach
                                       </select>
                                        @error('selectedBill') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="vat">Vendor<span class="required" style="color: red">*</span></label>
                                       <select class="form-control" wire:model="selectedVendor" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendorOption)
                                                <option value="{{ $vendorOption->id }}">{{ $vendorOption->name }} </option>
                                        @endforeach
                                       </select>
                                        @error('selectedVendor') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if ($bill_attached === 'Yes' && $bill)
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Bill Expense Lines <small>(click "Add" to debit a specific line, or add a custom line below)</small></label>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-right">Bill Amount</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($billExpenses as $billExpense)
                                                <tr>
                                                    <td>{{ $billExpense->description ?: ($billExpense->account ? $billExpense->account->name : '') }}</td>
                                                    <td class="text-right">{{ $bill->currency ? $bill->currency->symbol : "" }}{{ number_format($billExpense->subtotal, 2) }}</td>
                                                    <td class="text-right">
                                                        <button type="button" class="btn btn-xs bg-primary" wire:click="addFromBillExpense({{ $billExpense->id }})"><i class="fa fa-plus"></i> Add</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3">No expense lines found on this bill.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if (($bill_attached === 'Yes' && $bill) || ($bill_attached === 'No' && $selectedVendor))
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Debit Note Item(s)<span class="required" style="color: red">*</span></label>
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
                                                <tr wire:key="debit-note-row-{{ $index }}">
                                                    <td>
                                                        <input type="text" class="form-control" wire:model.debounce.300ms="rows.{{ $index }}.description" placeholder="Enter description" required>
                                                        @error('rows.'.$index.'.description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rows.{{ $index }}.amount" placeholder="0.00" required>
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
                                       <select class="form-control" wire:model.debounce.300ms="currency_id" required @if ($bill_attached === 'Yes') disabled @endif>
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
                                                <label for="vendor">Exchange Rate</label>
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
                                       <textarea wire:model.debounce.300ms="debit_note_reason" class="form-control" cols="30" rows="4" placeholder="Enter reason for debit note"></textarea>
                                        @error('debit_note_reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
