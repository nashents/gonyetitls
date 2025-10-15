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
                                <a href="#" data-toggle="modal" data-target="#deductionModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Deduction</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <table id="deductionsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">CalculateBy
                                    </th>
                                    <th class="th-sm">CalculateOn
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Percentage
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($deductions->count()>0)
                                <tbody>
                                    @foreach ($deductions as $deduction)
                                  <tr>
                                    <td>{{$deduction->name}}</td>
                                    <td>{{$deduction->currency ? $deduction->currency->name : ""}}</td>
                                    <td>{{$deduction->description}}</td>
                                    <td>{{$deduction->calculate_by}}</td>
                                    <td>{{$deduction->calculate_on}}</td>
                                    <td>{{$deduction->amount}}</td>
                                    <td>{{$deduction->percentage}}</td>
                                    <td><span class="badge bg-{{$deduction->status == 1 ? "success" : "danger"}}">{{$deduction->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($deduction->user_id != Null || Auth::user()->is_admin())
                                                <li><a href="#"  wire:click="edit({{$deduction->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#deductionDeleteModal{{ $deduction->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @include('deductions.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="deductionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Deduction <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currencies</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control"  >
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-10">Select item calculation.</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate By<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_by" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="currency">$</option>
                                    <option value="percentage">%</option>
                                </select>
                                @error('calculate_by') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate On<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_on" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="gross">Gross</option>
                                    <option value="net">Net</option>
                                </select>
                                @error('calculate_on') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @if ($calculate_by == "percentage")
                                <div class="form-group">
                                    <label for="name">Percentage</label>
                                    <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Enter Percentage"  />
                                    @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($calculate_by == "currency")
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount"  />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                              <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="deductionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Deduction <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currencies</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control"  >
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-10">Select item calculation.</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate By<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_by" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="currency">$</option>
                                    <option value="percentage">%</option>
                                </select>
                                @error('calculate_by') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate On<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_on" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="gross">Gross</option>
                                    <option value="net">Net</option>
                                </select>
                                @error('calculate_on') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @if ($calculate_by == "percentage")
                                <div class="form-group">
                                    <label for="name">Percentage</label>
                                    <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Enter Percentage"  />
                                    @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($calculate_by == "currency")
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount"  />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                              <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="country">Status<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="status" class="form-control" required >
                            <option value="">Select Option</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

