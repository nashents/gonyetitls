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

                            <div class="panel-title">
                                <a href="{{route('credit_notes.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Credit Note</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="credit_notesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Credit Note#
                                    </th>
                                    <th class="th-sm">Invoice#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($credit_notes->count()>0)
                                <tbody>
                                    @foreach ($credit_notes as $credit_note)
                                  <tr>
                                    <td>{{$credit_note->credit_note_number}}</td>
                                    <td>
                                        @if ($credit_note->invoice)
                                        <a href="{{ route('invoices.show',$credit_note->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">{{$credit_note->invoice ? $credit_note->invoice->invoice_number : ""}} </a>
                                        @endif
                                    </td>
                                    <td>{{$credit_note->customer ? $credit_note->customer->name : "undefined"}}</td>
                                    <td>{{$credit_note->date}}</td>
                                    <td>{{$credit_note->currency ? $credit_note->currency->name : "undefined"}}</td>
                                    <td>
                                        @if ($credit_note->total)
                                             {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->total,2)}}        
                                        @endif
                                    </td> 
                                    <td><span class="badge bg-{{($credit_note->authorization == 'approved') ? 'success' : (($credit_note->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($credit_note->authorization == 'approved') ? 'approved' : (($credit_note->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('credit_notes.show',$credit_note->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                                @if ($credit_note->authorization == "approved")
                                                <li><a href="{{route('credit_notes.preview',$credit_note->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                @endif
                                                <li><a href="{{route('credit_notes.edit',$credit_note->id)}}"  ><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#credit_noteDeleteModal{{ $credit_note->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('credit_notes.delete')
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
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">credit_note Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="credit_note_number" placeholder="Enter Receipt Number" required disabled>
                                @error('credit_note_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                       <select wire:model.debounce.300ms="currency_id" class="form-control" required required >
                           <option value="">Select Currency</option>
                          @foreach ($currencies as $currency)
                          <option value="{{$currency->id}}">{{$currency->name}}</option>
                          @endforeach
                       </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required>
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
</div>

