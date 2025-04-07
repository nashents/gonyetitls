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
                                <a href="#" data-toggle="modal" data-target="#payrollModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Payroll</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="payrollsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Payroll#
                                    </th>
                                    <th class="th-sm">Created By
                                    </th>
                                    <th class="th-sm">Month
                                    </th>
                                    <th class="th-sm">Year
                                    </th>
                                    <th class="th-sm">Total Deductions
                                    </th>
                                    <th class="th-sm">Total Earnings
                                    </th>
                                    <th class="th-sm">Total Gross
                                    </th>
                                    <th class="th-sm">Total Net
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($payrolls->count()>0)
                                <tbody>
                                    @foreach ($payrolls as $payroll)
                                  <tr>
                                    <td>{{$payroll->payroll_number}}</td>
                                    <td>{{$payroll->user ? $payroll->user->name : ""}} {{$payroll->user ? $payroll->user->surname : ""}}</td>
                                    <td>{{$payroll->month}}</td>
                                    <td>{{$payroll->year}}</td>
                                    <td>{{number_format($payroll->payroll_salaries->sum('total_deductions'),2)}}</td>
                                    <td>
                                        @php
                                            $total_allowances = $payroll->payroll_salaries->sum('total_allowances');
                                            $total_basics = $payroll->payroll_salaries->sum('basic');
                                            $total_earnings = $total_allowances + $total_basics;
                                        @endphp
                                        {{number_format($total_earnings ?  $total_earnings : 0,2)}}
                                       
                                    </td>
                                    <td>{{number_format($payroll->payroll_salaries->sum('gross'),2)}}</td>
                                    <td>{{number_format($payroll->payroll_salaries->sum('net'),2)}}</td>
                                    <td><span class="badge bg-{{($payroll->authorization == 'approved') ? 'success' : (($payroll->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($payroll->authorization == 'approved') ? 'approved' : (($payroll->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('payrolls.show', $payroll->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$payroll->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#payrollDeleteModal{{ $payroll->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('payrolls.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="payrollModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Payroll <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                 @endforeach
                              
                               </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="month">Month<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="month" class="form-control" required >
                                   <option value="">Select Month</option>
                                   <option value="January">January</option>
                                   <option value="February">February</option>
                                   <option value="March">March</option>
                                   <option value="April">April</option>
                                   <option value="May">May</option>
                                   <option value="June">June</option>
                                   <option value="July">July</option>
                                   <option value="August">August</option>
                                   <option value="September">September</option>
                                   <option value="October">October</option>
                                   <option value="November">November</option>
                                   <option value="December">December</option>
                               </select>
                                @error('month') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div> 
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="year" class="form-control" required >
                                   <option value="">Select Year</option>
                                   <option value="2023">2023</option>
                                   <option value="2024">2024</option>
                                   <option value="2025">2025</option>
                                   <option value="2026">2026</option>
                                   <option value="2027">2027</option>
                                   <option value="2028">2028</option>
                                   <option value="2029">2029</option>
                                   <option value="2030">2030</option>
                               </select>
                                @error('year') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="payrollEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Payroll <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                 @endforeach
                              
                               </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="month">Month<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="month" class="form-control" required >
                                   <option value="">Select Month</option>
                                   <option value="January">January</option>
                                   <option value="February">February</option>
                                   <option value="March">March</option>
                                   <option value="April">April</option>
                                   <option value="May">May</option>
                                   <option value="June">June</option>
                                   <option value="July">July</option>
                                   <option value="August">August</option>
                                   <option value="September">September</option>
                                   <option value="October">October</option>
                                   <option value="November">November</option>
                                   <option value="December">December</option>
                               </select>
                                @error('month') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div> 
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="year" class="form-control" required >
                                   <option value="">Select Year</option>
                                   <option value="2023">2023</option>
                                   <option value="2024">2024</option>
                                   <option value="2025">2025</option>
                                   <option value="2026">2026</option>
                                   <option value="2027">2027</option>
                                   <option value="2028">2028</option>
                                   <option value="2029">2029</option>
                                   <option value="2030">2030</option>
                               </select>
                                @error('year') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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

