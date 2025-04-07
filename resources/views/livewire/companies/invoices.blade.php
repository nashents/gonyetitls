<div>
    <form wire:submit.prevent="update()">
            <div class="form-group">
                <label for="color">Company Color</label>
                <input type="color" class="form-control"  wire:model.debounce.300ms="color" class="form-control">
                @error('color') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
               <div class="row">
                <div class="col-md-6">
                    <div class="mb-10 mt-10">
                        <input type="checkbox" wire:model.debounce.300ms="invoice_serialize_by_customer"   class="line-style" />
                        <label for="one" class="radio-label">Serialize Invoices By Customer</label>
                        @error('invoice_serialize_by_customer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-10 mt-10">
                        <input type="checkbox" wire:model.debounce.300ms="quotation_serialize_by_customer"   class="line-style" />
                        <label for="one" class="radio-label">Serialize Quotations By Customer</label>
                        @error('quotation_serialize_by_customer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
               </div>
               <h5 class="underline mt-5">Invoices</h5>
               <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Default notes / terms</label>
                       <textarea wire:model.debounce.300ms="invoice_memo" cols="30" rows="2" class="form-control" placeholder="Invoice notes / terms"></textarea>
                       @error('invoice_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="invoice_footer">Default Footer</label>
                       <textarea wire:model.debounce.300ms="invoice_footer" cols="30" rows="2" class="form-control" placeholder="Invoice footer"></textarea>
                       @error('invoice_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <h5 class="underline mt-5">Quotations</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quotation_memo">Default Notes / Terms</label>
                       <textarea wire:model.debounce.300ms="quotation_memo" cols="30" rows="2" class="form-control" placeholder="Quotation notes / terms" ></textarea>
                       @error('quotation_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quotation_footer">Default Footer</label>
                       <textarea wire:model.debounce.300ms="quotation_footer" cols="30" rows="2" class="form-control" placeholder="Quotation footer"></textarea>
                       @error('quotation_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <h5 class="underline mt-5">Receipts</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Default notes / terms</label>
                       <textarea wire:model.debounce.300ms="receipt_memo" cols="30" rows="2" class="form-control" placeholder="Receipt notes / terms"></textarea>
                       @error('receipt_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="invoice_footer">Default footer</label>
                       <textarea wire:model.debounce.300ms="receipt_footer" cols="30" rows="2" class="form-control" placeholder="Receipt footer"></textarea>
                       @error('receipt_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
