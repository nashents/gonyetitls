
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
                                <a href="{{route('recoveries.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Recovery</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                           
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Recovery#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Deduction
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Balance
                                    </th>
                                    <th class="th-sm">Payment
                                    </th>
                                    <th class="th-sm">Progress
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($recoveries))
                                <tbody>
                                   
                                    @forelse ($recoveries as $recovery)
                                  <tr>
                                    <td>{{$recovery->recovery_number}}</td>
                                    <td>{{$recovery->date}}</td>
                                    <td>{{$recovery->driver ? $recovery->driver->driver_number : ""}} {{$recovery->driver ? $recovery->driver->employee->name : ""}} {{$recovery->driver ? $recovery->driver->employee->surname : ""}}</td>
                                    
                                    <td>{{$recovery->deduction ? $recovery->deduction->name : ""}} | {{$recovery->type}}</td>
                                    <td>{{$recovery->currency ? $recovery->currency->name : ""}}</td>  
                                    <td>
                                        @if ($recovery->amount)
                                            {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->amount,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($recovery->balance)
                                            {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->balance,2)}}
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($recovery->status == 'Paid') ? 'success' : (($recovery->status == 'Partial') ? 'warning' : 'danger') }}">{{ $recovery->status }}</span></td>
                                    <td><span class="label label-{{$recovery->progress == "Open"  ? 'warning' : 'success' }}">{{$recovery->progress}}</span></td>
                                    <td><span class="badge bg-{{($recovery->authorization == 'approved') ? 'success' : (($recovery->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($recovery->authorization == 'approved') ? 'approved' : (($recovery->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('recoveries.show',$recovery->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                @if ($recovery->authorization == "approved")
                                                <li><a href="#" wire:click="showPayment({{$recovery->id}})"  ><i class="fas fa-credit-card color-primary"></i> Record Payment</a></li>
                                                @endif
                                                @if ($recovery->payments->count()>0)
                                                @else 
                                                <li><a href="{{route('recoveries.edit',$recovery->id)}}" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#recoveryDeleteModal{{ $recovery->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('recoveries.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Recoveries Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($recoveries))
                                        {{ $recoveries->links() }} 
                                    @endif 
                                </ul>
                            </nav> 
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Payment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="recordPayment()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name Of Payer<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" disabled required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname Of Payer<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Surname" disabled required />
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Date Of Payment<span class="required" style="color: red">*</span></label>
                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required >
                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Method Of Payment<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="mode_of_payment" class="form-control" required >
                           <option value="">Select Method Of Payment</option>
                           <option value="CASH">CASH</option>
                           <option value="ECOCASH">ECOCASH</option>
                           <option value="NOSTRO">FCA NOSTRO</option>
                           <option value="RTGS">RTGS/ZIPIT TRANSFER</option>
                           <option value="VISA">VISA/MASTER CARD</option>
                           <option value="OTHER">OTHER</option>   
                       </select>
                        @error('mode_of_payment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @if ($mode_of_payment == "RTGS" || $mode_of_payment == "ECOCASH" || $mode_of_payment == "NOSTRO" || $mode_of_payment == "VISA")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Reference Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="reference_code" placeholder="Enter Reference / Approval code"  >
                                @error('reference_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
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
                            @foreach ($inputs as $key => $value)
                            <div class="col-md-5">
                                <div class="form-group">
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
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.{{ $value }}" placeholder="Enter Quantity" required >
                                @error('denomination_qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Denomination</button>
                                </div>
                            </div>
                        </div>
        
                
                    @endif
                    <div class="form-group">
                        <label for="country">Payment Accounts</label>
                       <select wire:model.debounce.300ms="account_id" class="form-control" required >
                           <option value="">Select Payment Account</option>
                           @foreach ($accounts as $account)
                           @if ($recovery_currency)
                           @if ($account->currency->id == $recovery_currency->id)
                           <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>
                           @endif     
                           @else  
                           select currency for recovery
                           @endif
                           @endforeach
                       </select>
                        @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        @if ($mode_of_payment == "OTHER")
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Value<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Value" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else   
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Balance<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_balance" placeholder="Current Balance" disabled required />
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

