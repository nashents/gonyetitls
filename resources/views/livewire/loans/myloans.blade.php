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
                            <div class="panel-title" >
                                My Loan Applications
                             </div>
                             <br>
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#loanModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Apply Loan</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Loan#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Details
                                    </th>
                                    <th class="th-sm">Bal
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if (isset($loans))
                                <tbody>
                                    @forelse ($loans as $loan)
                                  <tr>
                                    <td>{{$loan->loan_number}}</td>
                                    <td>{{ucfirst($loan->employee ? $loan->employee->name : "")}} {{ucfirst($loan->employee ? $loan->employee->surname : "")}}</td>
                                    <td>{{$loan->loan_type ? $loan->loan_type->name : ""}}</td>
                                    <td>{{$loan->start_date}}</td>
                                    <td>{{$loan->currency ? $loan->currency->name : ""}}</td>
                                    <td>
                                        @if ($loan->amount)
                                         {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->amount,2)}}        
                                        @endif
                                        {{$loan->interest ? "@ ".$loan->interest."%" : ""}} {{$loan->period ? "For ".$loan->period." Months" : ""}}
                                        @if ($loan->total)
                                        , Total: {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->total,2)}}        
                                        @endif
                                        @if ($loan->payment_per_month)
                                        Installments: {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month,2)}}.        
                                    @endif
                                    </td>
                                    <td> 
                                        @if ($loan->balance)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->balance,2)}}        
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($loan->status == 'Paid') ? 'success' : (($loan->status == 'Partial') ? 'warning' : 'danger') }}">{{ $loan->status }}</span></td>
                                    <td><span class="badge bg-{{($loan->authorization == 'approved') ? 'success' : (($loan->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($loan->authorization == 'approved') ? 'approved' : (($loan->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('loans.show',$loan->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($loan->authorization == "pending")
                                                <li><a href="#" data-toggle="modal" data-target="#loanEditModal" wire:click.prevent="edit({{$loan->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#loanDeleteModal{{$loan->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                              
                                            </ul>
                                        </div>
                                        @include('loans.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Loans Found ....
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
                                    @if (isset($loans))
                                        {{ $loans->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Apply Loan <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Loan Type<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="loan_type_id" class="form-control" required >
                                  <option value="" selected>Select Loan Type</option>
                                  @foreach ($loan_types as $loan_type)
                                      <option value="{{$loan_type->id}}">{{$loan_type->name}}</option>
                                  @endforeach
                              </select>
                                @error('loan_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>         
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date"  required />
                                @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">Period<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="period" required>
                                <option value="">Select Period</option>
                                <option value="1">1 Month</option>
                                <option value="2">2 Month</option>
                                <option value="3">3 Month</option>
                                <option value="4">4 Month</option>
                                <option value="5">5 Month</option>
                                <option value="6">6 Month</option>
                                <option value="7">7 Month</option>
                                <option value="8">8 Month</option>
                                <option value="9">9 Month</option>
                                <option value="10">10 Month</option>
                                <option value="11">11 Month</option>
                                <option value="12">12 Month</option>
                                <option value="13">13 Month</option>
                                <option value="14">14 Month</option>
                                <option value="15">15 Month</option>
                                <option value="16">16 Month</option>
                                <option value="17">17 Month</option>
                                <option value="18">18 Month</option>
                                <option value="19">19 Month</option>
                                <option value="20">20 Month</option>
                                <option value="21">21 Month</option>
                                <option value="22">22 Month</option>
                                <option value="23">23 Month</option>
                                <option value="24">24 Month</option>
                               </select>
                                @error('period') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Interest</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="interest" placeholder="%"/>
                                @error('interest') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
           

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Total<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="total" placeholder="$" required disabled />
                                @error('total') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Monthly Payment<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="payment_per_month" placeholder="$" required disabled/>
                                @error('payment_per_month') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Purpose of loan</label>
                               <textarea class="form-control" wire:model.debounce.300ms="purpose"  cols="30" rows="5"></textarea>
                                @error('purpose') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- /.col-md-6 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Apply</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loanEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Loan Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-body">
                   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Loan Type<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="loan_type_id" class="form-control" required >
                                  <option value="" selected>Select Loan Type</option>
                                  @foreach ($loan_types as $loan_type)
                                      <option value="{{$loan_type->id}}">{{$loan_type->name}}</option>
                                  @endforeach
                              </select>
                                @error('loan_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>         
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date"  required />
                                @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">Period<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="period" required>
                                <option value="">Select Period</option>
                                <option value="1">1 Month</option>
                                <option value="2">2 Month</option>
                                <option value="3">3 Month</option>
                                <option value="4">4 Month</option>
                                <option value="5">5 Month</option>
                                <option value="6">6 Month</option>
                                <option value="7">7 Month</option>
                                <option value="8">8 Month</option>
                                <option value="9">9 Month</option>
                                <option value="10">10 Month</option>
                                <option value="11">11 Month</option>
                                <option value="12">12 Month</option>
                                <option value="13">13 Month</option>
                                <option value="14">14 Month</option>
                                <option value="15">15 Month</option>
                                <option value="16">16 Month</option>
                                <option value="17">17 Month</option>
                                <option value="18">18 Month</option>
                                <option value="19">19 Month</option>
                                <option value="20">20 Month</option>
                                <option value="21">21 Month</option>
                                <option value="22">22 Month</option>
                                <option value="23">23 Month</option>
                                <option value="24">24 Month</option>
                               </select>
                                @error('period') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Interest<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="interest" placeholder="%" required disabled/>
                                @error('interest') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
           

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Total<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="total" placeholder="$" required disabled/>
                                @error('total') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Monthly Payment<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="payment_per_month" placeholder="$" required disabled/>
                                @error('payment_per_month') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Purpose of loan</label>
                               <textarea class="form-control" wire:model.debounce.300ms="purpose"  cols="30" rows="5" required ></textarea>
                                @error('purpose') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- /.col-md-6 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
