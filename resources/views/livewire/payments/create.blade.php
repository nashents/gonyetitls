<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Bill</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="store()" >
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Payment#<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="payment_number" placeholder="Enter Bill Number" required disabled>
                                        @error('payment_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Invoice(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="invoice_id.0" class="form-control" required {{$bill_id ? "disabled" : ""}}>
                                           <option value="">Select Invoice</option>
                                         @foreach ($invoices as $invoice)
                                              <option value="{{$invoice->id}}">Invoice#: {{$invoice->invoice_number}}, {{$invoice->customer ? $invoice->customer->name : ""}}, {{ $invoice->total }}</option>
                                         @endforeach
                                       </select>
                                        @error('invoice_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                              
                                    <div class="row">
                                        @foreach ($inputs as $key => $value)
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="invoice_id.{{ $value }}" class="form-control" required>
                                                    <option value="">Select Invoice</option>
                                                        @foreach ($invoices as $invoice)
                                                        <option value="{{$invoice->id}}">Invoice#: {{$invoice->invoice_number}}, {{$invoice->customer ? $invoice->customer->name : ""}}, {{ $invoice->total }}</option>
                                                        @endforeach
                                                </select>
                                                    @error('invoice_id.'. $value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                               
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})" {{$bill_id ? "disabled" : ""}}> <i class="fa fa-plus"></i>Invoice</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Bill(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="bill_id.0" class="form-control" required {{$invoice_id ? "disabled" : ""}}>
                                           <option value="">Select Bill</option>
                                         @foreach ($bills as $bill)
                                              <option value="{{$bill->id}}">Bill#: {{$bill->bill_number}}, {{$bill->vendor ? $bill->vendor->name : $bill->transporter->name}}, {{ $bill->total }}</option>
                                         @endforeach
                                       </select>
                                        @error('bill_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                              
                                    <div class="row">
                                        @foreach ($bill_inputs as $key => $value)
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="bill_id.{{ $value }}" class="form-control" required>
                                                    <option value="">Select Bill</option>
                                                    @foreach ($bills as $bill)
                                                        <option value="{{$bill->id}}">Bill#: {{$bill->bill_number}}, {{$bill->vendor ? $bill->vendor->name : $bill->transporter->name}}, {{ $bill->total }}</option>
                                                    @endforeach
                                                </select>
                                                    @error('bill_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="removeBill({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                               
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="addBill({{$o}})" {{$invoice_id ? "disabled" : ""}}> <i class="fa fa-plus"></i>Bill</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name Of Payer</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required >
                                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Surname Of Payer</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Surname" required >
                                        @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Date Of Payment</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Method Of Payment</label>
                                       <select wire:model.debounce.300ms="mode_of_payment" class="form-control" required >
                                           <option value="">Select Cargo Group</option>
                                           <option value="CASH">CASH</option>
                                           <option value="ECOCASH">ECOCASH</option>
                                           <option value="NOSTRO">FCA NOSTRO</option>
                                           <option value="RTGS">RTGS/ZIPIT TRANSFER</option>
                                       </select>
                                        @error('mode_of_payment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @if ($mode_of_payment == "RTGS" || $mode_of_payment == "ECOCASH" || $mode_of_payment == "NOSTRO" )
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Bank Accounts</label>
                                       <select wire:model.debounce.300ms="bank_account_id" class="form-control" required >
                                           <option value="">Select Bank Account</option>
                                         @foreach ($bank_accounts as $bank_account)
                                                <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_number }}</option>
                                         @endforeach
                                       </select>
                                        @error('bank_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Reference Code</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="reference_code" placeholder="Enter Reference/ Approval code"  >
                                        @error('reference_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Proof Of Payment</label>
                                        <input type="file" class="form-control" wire:model.debounce.300ms="pop" placeholder="Upload Pop" >
                                        @error('pop') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                              
                            </div>
                            @elseif ($mode_of_payment == "CASH")
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Denomination</label>
                                       <select wire:model.debounce.300ms="denomination.0" class="form-control" required >
                                           <option value="">Select Denomination</option>
                                           <option value="1">1</option>
                                           <option value="2">2</option>
                                           <option value="5">5</option>
                                           <option value="10">10</option>
                                           <option value="20">20</option>
                                           <option value="50">50</option>
                                           <option value="100">100</option>
                                           <option value="200">200</option>
                                       </select>
                                        @error('denomination.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="name">Quantity</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.0" placeholder="Enter Quantity" required >
                                    @error('denomination_qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                              
                                <div class="row">
                                    @foreach ($denomination_inputs as $key => $value)
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="country">Denomination</label>
                                           <select wire:model.debounce.300ms="denomination.{{ $value }}" class="form-control" required >
                                               <option value="">Select Denomination</option>
                                               <option value="1">1</option>
                                               <option value="2">2</option>
                                               <option value="5">5</option>
                                               <option value="10">10</option>
                                               <option value="20">20</option>
                                               <option value="50">50</option>
                                               <option value="100">100</option>
                                               <option value="200">200</option>
                                           </select>
                                            @error('denomination.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="name">Quantity</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.{{ $value }}" placeholder="Enter Quantity" required >
                                        @error('denomination_qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for=""></label>
                                            <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="removeDenomination({{$key}})"> <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
        
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="addDenomination({{$s}})"> <i class="fa fa-plus"></i>Denomination</button>
                                        </div>
                                    </div>
                                </div>
                
                        
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Amount</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
                                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Balance</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_balance" placeholder="Current Balance" required >
                                        @error('current_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Memo / Notes (Optional)</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="5"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
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
