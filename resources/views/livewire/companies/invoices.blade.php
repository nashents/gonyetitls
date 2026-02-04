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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Payment Terms</label>
                            <select  wire:model.debounce.300ms="invoice_due_when" class="form-control">
                                <option value="">Select Option</option>
                                <option value="0">Due upon receipt</option>
                                <option value="15">Due within 15 Days</option>
                                <option value="30">Due within 30 Days</option>
                                <option value="45">Due within 45 Days</option>
                                <option value="60">Due within 60 Days</option>
                                <option value="90">Due within 90 Days</option>
                            </select>
                            <small style="color: green">The default title for all invoices. You can change this on each invoice.</small>
                            @error('invoice_due_when') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Title</label>
                            <input type="text" wire:model.debounce.300ms="invoice_title" class="form-control" placeholder="Default Invoice Title">
                            <small style="color: green">The default title for all invoices. You can change this on each invoice.</small>
                            @error('invoice_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Subheading</label>
                            <input type="text" wire:model.debounce.300ms="invoice_subheading" class="form-control" placeholder="Default Invoice Subheading">
                            <small style="color: green">This will be displayed below the title of each invoice. Useful for things like Vendor numbers.</small>
                            @error('invoice_subheading') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
               </div>
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
             <h5 class="underline mt-5">Invoice Column Settings</h5>
            <div class="row">
                <div class="col-md-6">
                    <label for="one" class="radio-label">Items</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Items"  class="line-style"  />
                        <label for="one" class="radio-label">Items<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Description"  class="line-style"  />
                        <label for="one" class="radio-label">Description</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Products"  class="line-style"  />
                        <label for="one" class="radio-label">Products</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Services"  class="line-style"  />
                        <label for="one" class="radio-label">Services</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('items_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                <div class="col-md-6">
                    <label for="one" class="radio-label">Units</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Qty"  class="line-style"  />
                        <label for="one" class="radio-label">Quantity<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Hours"  class="line-style"  />
                        <label for="one" class="radio-label">Hours</label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Days"  class="line-style"  />
                        <label for="one" class="radio-label">Days</label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('units_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="one" class="radio-label">Price</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Price"  class="line-style"  />
                        <label for="one" class="radio-label">Price<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Rate"  class="line-style"  />
                        <label for="one" class="radio-label">Rate</label>
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('price_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                <div class="col-md-6">
                     <label for="one" class="radio-label">Amount</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Total"  class="line-style"  />
                        <label for="one" class="radio-label">Total<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Amount"  class="line-style"  />
                        <label for="one" class="radio-label">Amount</label>
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('total_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
            </div>
            <label for="one" class="radio-label">Choose which columns on your invoices to hide:</label>
            <div class="row">
                 <div class="col-md-6">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="hide_items"   class="line-style" />
                        <label for="one" class="radio-label">Hide Items</label>
                        @error('hide_items') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_description"   class="line-style" />
                        <label for="one" class="radio-label">Hide Description</label>
                        @error('hide_description') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="hide_price"   class="line-style" />
                        <label for="one" class="radio-label">Hide Price</label>
                        @error('hide_price') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_quantity"   class="line-style" />
                        <label for="one" class="radio-label">Hide Quantity</label>
                        @error('hide_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_amount"   class="line-style" />
                        <label for="one" class="radio-label">Hide Amount</label> <br>
                        <small style="color: green">Your invoice must show at least one of the above.</small>
                        @error('hide_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <h5 class="underline mt-5">Quotations</h5>
               <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default valid until</label>
                            <select  wire:model.debounce.300ms="quote_valid_until" class="form-control">
                                <option value="">Select Option</option>
                                <option value="1">1 Day</option>
                                <option value="7">7 Days</option>
                                <option value="15">15 Days</option>
                                <option value="30">30 Days</option>
                                <option value="45">45 Days</option>
                                <option value="60">60 Days</option>
                            </select>
                            <small style="color: green">The default validity period for all quotations. You can change this on each quotation</small>
                            @error('quote_valid_until') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Title</label>
                            <input type="text" wire:model.debounce.300ms="quotation_title" class="form-control" placeholder="Default Quotation Title">
                            <small style="color: green">The default title for all quotations. You can change this on each quotation.</small>
                            @error('quotation_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Subheading</label>
                            <input type="text" wire:model.debounce.300ms="quotation_subheading" class="form-control" placeholder="Default Quotation Subheading">
                            <small style="color: green">This will be displayed below the title of each quotation. Useful for things like Vendor numbers.</small>
                            @error('quotation_subheading') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
               </div>
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
