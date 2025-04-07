<div>
    <section class="section">
        <x-loading/>
        <div class="allocation-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <form wire:submit.prevent="search()" class="p-20" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="selectedType">Report Type</label>
                                            <select class="form-control" wire:model.debounce.300ms="selectedType">
                                                <option value="">Select Type</option>
                                                <option value="individual">Individual Employee Allocations</option>
                                                <option value="all">All Employees Allocations</option>

                                            </select>
                                             @error('selectedType') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        </div>
                                    </div>
                                    @if (!is_null($selectedType))
                                    <div class="row">
                                        @if (!is_null($selectedEmployee))
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="employee_id">Employee(s)</label>
                                               <select class="form-control" wire:model.debounce.300ms="employee_id">
                                                   <option value="">Select Employee</option>
                                                   @foreach ($employees as $employee)
                                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} ({{$employee->employee_number}})</option>
                                                   @endforeach
                                               </select>
                                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        @endif
                                        <!-- /.col-md-6 -->

                                        <div class="col-md-{{$selectedEmployee ? "4" : "6"}}">
                                            <div class="form-group">
                                                <label for="from">From</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="from" placeholder="From Date">
                                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-{{$selectedEmployee ? "4" : "6"}}">
                                            <div class="form-group">
                                                <label for="to">To</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="to" placeholder="To Date">
                                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group pull-right mt-10" >
                                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                                <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Generate Report</button>
                                            </div>
                                        </div>
                                        </div>
                                    @endif



                                </form>
                            </div>
                            <div class="panel-title" style="float: right">
                                <a href="{{route('allocations.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('allocations.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('allocations.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                <a href="" data-toggle="modal" data-target="#allocationsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">


                            <table id="allocationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Allocation #
                                    </th>
                                    <th class="th-sm">Allocation Type
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Container
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity(Litres)
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Amount($)
                                    </th>
                                    <th class="th-sm">Balance(Litres)
                                    </th>
                                    <th class="th-sm">Validity
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($search))
                                @if ($allocations->count()>0)
                                <tbody>
                                    @foreach ($allocations as $allocation)
                                  <tr>
                                    <td>{{$allocation->allocation_number}}</td>
                                    <td>{{$allocation->allocation_type}}</td>
                                    <td>{{$allocation->employee->name}} {{$allocation->employee->surname}} ({{$allocation->employee->employee_number}})</td>
                                    <td>{{$allocation->vehicle->make}} {{$allocation->vehicle->model}} ({{$allocation->vehicle->registration_number}})</td>
                                    <td>{{$allocation->container->name}}</td>
                                    <td>{{$allocation->fuel_type}}</td>
                                    <td>{{$allocation->quantity}}l</td>
                                    <td>${{$allocation->rate}}</td>
                                    <td>${{$allocation->amount}}</td>
                                    <td>{{$allocation->balance}}l</td>
                                    @php
                                    $now =  Carbon\Carbon::now();
                                 @endphp
                                    <td><span class="label label-{{$allocation->expiry_date >= $now  ? "success" : "danger"}} label-rounded">{{$allocation->expiry_date >= $now ? "Active" : "Expired"}}</span></td>
                                    <td><span class="label label-{{$allocation->status == 1 ? "success" : "danger"}} label-rounded">{{$allocation->status == 1 ? "Active" : "Expired"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$allocation->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#allocationDeleteModal{{ $allocation->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('allocations.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.allocation-fluid -->
    </section>


</div>

