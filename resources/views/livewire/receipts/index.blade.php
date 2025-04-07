<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>

                            {{-- <div class="panel-title">
                                <a href="{{route('receipts.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>receipt</a>
                            </div> --}}
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="receiptsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Payment#
                                    </th>
                                    <th class="th-sm">Receipt#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Balance
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($receipts->count()>0)
                                <tbody>
                                    @foreach ($receipts as $receipt)
                                  <tr>
                                    <td>{{ $receipt->payment ? $receipt->payment->payment_number : "undefined" }}</td>
                                    <td>{{$receipt->receipt_number}}</td>
                                    <td>{{$receipt->date}}</td>
                                    <td>{{$receipt->currency ? $receipt->currency->name : "undefined"}}</td>
                                    <td>
                                        @if ($receipt->amount)
                                        {{$receipt->currency ? $receipt->currency->symbol : "undefined"}}{{number_format($receipt->amount,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($receipt->balance)
                                        {{$receipt->currency ? $receipt->currency->symbol : "undefined"}}{{number_format($receipt->balance,2)}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('receipts.preview',$receipt->id)}}"  ><i class="fas fa-eye color-default"></i> Preview</a></li>
                                            </ul>
                                        </div>
                                        @include('receipts.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-receipt"></i> Add Payment Receipt <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeReceipt()" >
                <div class="modal-body">

                    <div class="form-group">
                        <label for="name">Invoices<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="invoice_id" required>
                            <option value="">Select Invoice</option>
                            @foreach ($invoices as $invoice)
                                <option value="{{$invoice->id}}">{{$invoice->number}}</option>
                            @endforeach
                        </select>
                                @error('invoice_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Invoice Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="invoice_number" placeholder="Enter Invoice Number" required disabled>
                                @error('invoice_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Receipt Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="receipt_number" placeholder="Enter Receipt Number" required disabled >
                                @error('receipt_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Payment Date<span class="required" style="color: red">*</span></label>
                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Payment Date" required >
                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                           <option value="">Select Currency</option>
                          @foreach ($currencies as $currency)
                          <option value="{{$currency->id}}">{{$currency->name}}</option>
                          @endforeach
                       </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required disabled>
                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Upload Receipt</label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="receipt" placeholder="Upload Receipt">
                        @error('receipt') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="receiptEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-receipt"></i> Edit Payment Receipt <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateReceipt()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Invoice Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="invoice_number" placeholder="Enter Receipt Number" required disabled>
                                @error('invoice_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Receipt Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="receipt_number" placeholder="Enter Receipt Number" required disabled >
                                @error('receipt_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Payment Date<span class="required" style="color: red">*</span></label>
                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Payment Date" required >
                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                           <option value="">Select Currency</option>
                          @foreach ($currencies as $currency)
                          <option value="{{$currency->id}}">{{$currency->name}}</option>
                          @endforeach
                       </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    {{-- <div class="form-group">
                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div> --}}
                    <div class="form-group">
                        <label for="name">Upload Receipt</label>
                        <small>Selected File: <a href="{{asset('myfiles/receipts/'.$old_receipt)}}" style="color: red">{{$old_receipt}}</a></small>
                        <input type="file" class="form-control" wire:model.debounce.300ms="receipt" placeholder="Upload Receipt">
                        @error('receipt') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-update"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

