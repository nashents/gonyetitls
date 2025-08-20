<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Salary</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="store()" >
                        <div class="modal-body">
                          
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                   <option value="">Select Employee</option>
                                 @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}"> {{$employee->name}} {{$employee->surname}} {{$employee->employee_number ? "(".$employee->employee_number.")" : ""}}</option>
                                 @endforeach
                               </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Salary Frequency<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="frequency" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="fortnightly">Fortnightly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                        @error('frequency') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Currencies<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                            @endforeach
                                        </select>
                                        @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if (!is_null($selectedCurrency))
                                    @if (Auth::user()->employee->company)
                                        @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate" required  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{Auth::user()->employee->company->currency ? "To ".Auth::user()->employee->company->currency->name : ""}}"  />
                                            @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                            <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                        </div> 
                                        @endif
                                    @endif
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Basic Salary<span class="required" style="color: red">*</span></label>
                                        <input  type="number" step="any"  class="form-control" wire:model.debounce.300ms="basic" placeholder="Enter Basic Salary" required>
                                        @error('basic') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                               
                            </div>
                        
                         
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="underline mt-10">Earnings</h5>
                                   <label for="">Allowances</label> 
                                   <div class="row">  
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select wire:model.debounce.300ms="selectedAllowance.0" class="form-control">
                                                    <option value="">Select an allowance</option>
                                                    @foreach ($allowances as $allowance)
                                                        <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small><a href="{{ route('allowances.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Allowance</a></small> 
                                                @error('selectedAllowance.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input  type="number" step="any"  class="form-control" wire:model.debounce.300ms="allowance_amount.0" placeholder="Enter Amount">
                                                @error('allowance_amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        @foreach ($inputs as $key => $value)
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="selectedAllowance.{{ $value }}" class="form-control">
                                                        <option value="">Select an allowance</option>
                                                        @foreach ($allowances as $allowance)
                                                            <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <small><a href="{{ route('allowances.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Allowance</a></small> 
                                                @error('selectedAllowance.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <input  type="number" step="any"  class="form-control" wire:model.debounce.300ms="allowance_amount.{{ $value }}" placeholder="Enter Amount">
                                                    @error('allowance_amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded btn-sm"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded btn-sm" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Earning</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="underline mt-10">Deductions</h5>
                                    <label for="">Default Deductions</label>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="paye"   class="line-style" />
                                        <label for="one" class="radio-label">PAYE</label>
                                        @error('paye') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="aids_levy"   class="line-style" />
                                        <label for="one" class="radio-label">Aids Levy</label>
                                        @error('aids_levy') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    <label for="">Custom Deductions</label>
                                    <div class="row">  
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select wire:model.debounce.300ms="selectedDeduction.0" class="form-control">
                                                    <option value="">Select a deduction</option>
                                                    @foreach ($deductions as $deduction)
                                                        <option value="{{ $deduction->id }}">{{ $deduction->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small><a href="{{ route('deductions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Deductions</a></small> 
                                                @error('selectedDeduction.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input  type="number" step="any"  class="form-control" wire:model.debounce.300ms="deduction_amount.0" placeholder="Enter Amount">
                                                @error('deduction_amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        @foreach ($deductions_inputs as $key => $value)
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="selectedDeduction.{{ $value }}" class="form-control">
                                                        <option value="">Select Deduction Item</option>
                                                        @foreach ($deductions as $deduction)
                                                            <option value="{{ $deduction->id }}">{{ $deduction->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('selectedDeduction.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <input  type="number" step="any"  class="form-control" wire:model.debounce.300ms="deduction_amount.{{ $value }}" placeholder="Enter Amount">
                                                    @error('deduction_amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded btn-sm"   wire:click.prevent="deductionsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded btn-sm" style="float: right" wire:click.prevent="deductionsAdd({{$l}})"> <i class="fa fa-plus"></i>Deduction</button>
                                            </div>
                                        </div>
                                    </div>

                                    <label for="">Recoveries</label>
                                    <div class="row">  
                                        <div class="col-md-12">
                                        <div class="form-group">
                                            <select wire:model.debounce.300ms="selectedRecovery.0" class="form-control">
                                                <option value="">Select Recovery</option>
                                                @foreach ($deduction_recoveries as $recovery)
                                                    <option value="{{ $recovery->id }}"> {{ $recovery->recovery_number }} </option>
                                                @endforeach
                                            </select>
                                            @error('selectedRecovery.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        @foreach ($recoveries_inputs as $key => $value)
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="selectedRecovery.{{ $value }}" class="form-control">
                                                        <option value="">Select Recovery </option>
                                                        @foreach ($deduction_recoveries as $recovery)
                                                            <option value="{{ $recovery->id }}"> {{ $recovery->recovery_number }} </option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedRecovery.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>     
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="recoveriesRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <label for="">Loan</label>
                                    <div class="row">  
                                        <div class="col-md-12">
                                        <div class="form-group">
                                            <select wire:model.debounce.300ms="selectedLoan.0" class="form-control">
                                                <option value="">Select Loan</option>
                                                @foreach ($loans as $loan)
                                                    <option value="{{ $loan->id }}"> {{ $loan->loan_number }} {{ $loan->loan_type ? $loan->loan_type->name : "" }} Monthly Installments: {{$loan->currency ? $loan->currency->name : ""}} {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month ? $loan->payment_per_month : 0,2)}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedLoan.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        @foreach ($loans_inputs as $key => $value)
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <select wire:model.debounce.300ms="selectedLoan.{{ $value }}" class="form-control">
                                                        <option value="">Select Loan </option>
                                                        @foreach ($loans as $loan)
                                                        <option value="{{ $loan->id }}"> {{ $loan->loan_number }} {{ $loan->loan_type ? $loan->loan_type->name : "" }} Monthly Installments: {{$loan->currency ? $loan->currency->name : ""}} {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month ? $loan->payment_per_month : 0,2)}}</option>
                                                        @endforeach
                                                    </select>
                                                     @error('selectedLoan.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>     
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="loansRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="loansAdd({{$j}})"> <i class="fa fa-plus"></i>Loan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" onclick="goBack()" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-arrow-left"></i>Back</button>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>





                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


</div>
