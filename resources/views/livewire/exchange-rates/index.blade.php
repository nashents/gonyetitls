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
                                <a href="" data-toggle="modal" data-target="#exchange_rateModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Exchange Rate</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="exchange_ratesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead >
                                      <th class="th-sm">UpdatedBy
                                    </th>
                                    <th class="th-sm">Frequency
                                    </th>
                                    <th class="th-sm">Expires
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Exchange Rate
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach ($exchange_rates as $exchange_rate)
                                  <tr>
                                    <td>{{$exchange_rate->user ? $exchange_rate->user->name : ""}} {{$exchange_rate->user ? $exchange_rate->user->surname : ""}}</td>
                                    <td>{{ucfirst($exchange_rate->frequency)}}</td>
                                    @php
                                        $expiry_date = Carbon\Carbon::parse($exchange_rate->expiry);
                                        $now = Carbon\Carbon::now();
                                    @endphp
                                    <td>
                                         <span class="label label-{{ $now->lt($expiry_date) ? 'success' : 'danger' }}">
                                            {{ $expiry_date->format('Y-m-d') }}
                                        </span>
                                    </td>
                                    <td>{{$exchange_rate->currency ? $exchange_rate->currency->name : ""}} ({{$exchange_rate->currency ? $exchange_rate->currency->symbol : ""}})</td>
                                    <td>{{$exchange_rate->exchange_rate}}</td>
                                    <td><span class="badge bg-{{$exchange_rate->status == 1 ? "success" : "danger"}}">{{$exchange_rate->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$exchange_rate->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#exchange_rateDeleteModal{{$exchange_rate->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('exchange_rates.delete')
                    
                                </td>
                                </tr>
                                  @endforeach
                                </tbody>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="exchange_rateModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="exchange_rate">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Exchange Rate(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Trading Currencies<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                    <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                @endforeach
                                </select>
                            
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Exchange Rate<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="rate" placeholder="Enter Current Exchange Rate" required/>
                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="exchange_rateEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="exchange_rate">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Exchange Rate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Trading Currencies<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                        <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                    </select>
                                
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Exchange Rate<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="rate" placeholder="Enter Current Exchange Rate" required/>
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                    @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>   
                        <div class="form-group">
                            <label for="title">Status<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="status" required>
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

