<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                            <h5>Shift Dates & Range</h5>
                        
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Filter By
                                    </span>
                                    <select wire:model.debounce.300ms="shift_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Shift Created At</option>
                                    <option value="date">Shift Date</option>
                                </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-right: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    From
                                    </span>
                                    <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    To
                                    </span>
                                    <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                
                                <!-- /input-group -->
                                
                            </div>
                            <h5>Filter reports by</h5>
                            <div class="row">
                                    <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Shift
                                    </span>
                                    <select wire:model.debounce.300ms="type" class="form-control" aria-label="..." >
                                        <option value="">Select Shift</option>
                                        <option value="Morning">Morning</option>
                                        <option value="Night">Night</option>
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Transporters
                                    </span>
                                    <select wire:model.debounce.300ms="selectedTransporter" class="form-control" aria-label="..." >
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                                <option value="{{ $transporter->id }}"  >{{ ucfirst($transporter->name) }}</option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Horses
                                    </span>
                                    <select wire:model.debounce.300ms="selectedHorse" class="form-control" aria-label="..." >
                                        <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{ $horse->id }}"  > {{ $horse->registration_number }} {{ $horse->fleet_number ? "(".$horse->fleet_number.")" : "" }} </option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Drivers
                                    </span>
                                    <select wire:model.debounce.300ms="selectedDriver" class="form-control" aria-label="..." >
                                        <option value="">Select Driver</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}"  >{{ ucfirst($driver->employee ? $driver->employee->name : " employee") }} {{ ucfirst($driver->employee ? $driver->employee->surname : "") }}</option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                
                                
                            </div>
                            <div class="row">
                                    <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                    Customers
                                    </span>
                                    <select wire:model.debounce.300ms="selectedCustomer" class="form-control" aria-label="..." >
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group ">
                                        <span class="input-group-addon">
                                    Cargos
                                    </span>
                                    <select wire:model.debounce.300ms="selectedCargo" class="form-control  " aria-label="..." >
                                        <option value="">Select Cargo</option>
                                        @foreach ($cargos as $cargo)
                                            <option value="{{ $cargo->id }}"  >{{ ucfirst($cargo->name) }}</option>
                                            @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group ">
                                        <span class="input-group-addon">
                                    LPs
                                    </span>
                                    <select wire:model.debounce.300ms="selectedLoadingPoint" class="form-control  " aria-label="..." >
                                        <option value="">Select Loading Point</option>
                                        @foreach ($loading_points as $loading_point)
                                                <option value="{{ $loading_point->id }}"  > {{ $loading_point->name }}</option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group ">
                                        <span class="input-group-addon">
                                    OPs
                                    </span>
                                    <select wire:model.debounce.300ms="selectedOffloadingPoint" class="form-control  " aria-label="..." >
                                        <option value="">Select Offloading Point</option>
                                        @foreach ($offloading_points as $offloading_point)
                                                <option value="{{ $offloading_point->id }}"  > {{ $offloading_point->name }}</option>
                                        @endforeach
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                
                                
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="input-group ">
                                        <span class="input-group-addon">
                                    CreatedBy
                                    </span>
                                    <select wire:model.debounce.300ms="selectedUser" class="form-control  " aria-label="..." >
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                    
                                        <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option> 
                                        
                                            
                                        @endforeach
                                        
                                    </select>
                                        
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <a href="#" wire:click.prevent="exportShiftsReportExcel()"  class="btn btn-default border-primary btn-rounded btn-wide btn-xs"><i class="fa fa-download"></i>Excel</a>
                                        <a href="#" wire:click.prevent="exportShiftsReportCSV()" class="btn btn-default border-primary btn-rounded btn-wide btn-xs" ><i class="fa fa-download"></i>CSV</a>
                                        <a href="#" wire:click.prevent="exportShiftsReportPDF()" class="btn btn-default border-primary btn-rounded btn-wide btn-xs"><i class="fa fa-download"></i>PDF</a>
                                    </div>
                                </div>
                                
                                {{-- <div class="col-md-4">
                                        <a href="#" wire:click.prevent="previewReports" class="btn btn-default border-primary btn-wide btn-rounded btn-xs" type="button"  aria-haspopup="true" aria-expanded="true">
                                                <i class="fas fa-eye"></i> Preview
                                            </a>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                       
                    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            
                        <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th class="th-sm">Shift
                                </th>
                                <th class="th-sm" style="width: 13%;">
                                    Duty
                                </th>
                                <th class="th-sm" style="width: 20%;">Narration
                                </th>
                            
                                <th class="th-sm" style="width:120px;">
                                    Hours
                                    <hr style="margin-top:2px; margin-bottom:2px">
                                    Distance
                                </th>
                                <th class="th-sm" style="width: 5%;">
                                    Fuel
                                </th>
                                <th class="th-sm" style="width:120px;">
                                    F/C (H)
                                    <hr style="margin-top:2px; margin-bottom:2px">
                                    F/C (M)
                                </th>
                            
                                <th class="th-sm">Status
                                </th>
                                <th class="th-sm">Action
                                </th>
                            </tr>
                            </thead>
                            @if (isset($shifts))
                                <tbody>
                                    @forelse($shifts as $shift)
                                <tr>

                                    <td>
                                        <strong>{{ucfirst($shift->type)}}  {{ucfirst($shift->for)}}</strong>
                                        <br>
                                        <small><strong>CreatedBy:</strong> {{$shift->user ? $shift->user->name : ""}} {{$shift->user ? $shift->user->surname : ""}}</small>
                                    </td>
                                    <td>
                                        <strong>Date:</strong> {{$shift->date}} <br>
                                        <strong>Start:</strong> {{$shift->shift_start_time}} <br>
                                        <strong>Close:</strong> {{$shift->shift_end_time}} <br>
                                    </td>
                                    <td>
                                        <strong>Customer:</strong> {{$shift->customer ? $shift->customer->name : ""}} <br>
                                        <strong>Cargo:</strong> {{$shift->cargo ? $shift->cargo->name : ""}} <br>
                                        @if ($shift->driver)
                                            <strong>Driver:</strong>  {{$shift->driver->employee ? $shift->driver->employee->name : ""}} {{$shift->driver->employee ? $shift->driver->employee->surname : ""}} <br>        
                                        @endif
                                        @if ($shift->horse)
                                            <strong>Horse:</strong>  {{$shift->horse->registration_number}} {{$shift->horse->fleet_number ? "(".$shift->horse->fleet_number.")" : ""}} <br>
                                        @elseif($shift->vehicle)
                                            <strong>Vehicle:</strong> {{$shift->horse->registration_number}} <br>
                                        @endif
                                    @if ($shift->loading_points->isNotEmpty() && $shift->loading_points->count()>0)
                                            <strong>Loading Points: </strong>
                                            @foreach ($shift->loading_points as $loading_point)
                                                {{ $loading_point->name }} @if (!$loop->last), @endif
                                            @endforeach
                                        @endif
                                        <br>
                                        @if ($shift->offloading_points->isNotEmpty())
                                            <strong>Offloading Points: </strong>
                                            @foreach ($shift->offloading_points as $offloading_point)
                                                {{ $offloading_point->name }}@if (!$loop->last), @endif
                                            @endforeach
                                        @endif
                                    </td>
                                
                                    <td>
                                        {{$shift->hours ? $shift->hours." Hrs" : ""}}
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                        {{$shift->actual_mileage ? $shift->actual_mileage." Kms" : ""}}
                                    </td>
                                    <td>
                                        {{$shift->total_fuel ? $shift->total_fuel. " l" : ""}}
                                    </td>
                                <td>
                                        {{$shift->fuel_consumption_hours ? number_format($shift->fuel_consumption_hours,2)." H/l" : ""}}
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                        {{$shift->fuel_consumption_mileage ? number_format($shift->fuel_consumption_mileage,2)." Km/l" : ""}}
                                    </td>
                                    <td><span class="badge bg-{{$shift->status == 1 ? "warning" : "success"}}">{{$shift->status == 1 ? "Open" : "Closed"}}</span></td>
                                
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('shifts.show', $shift->id) }}"   ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$shift->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#shiftDeleteModal{{ $shift->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('shifts.delete')
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Shifts Found ....
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
                                @if (isset($shifts))
                                    {{ $shifts->links() }} 
                                @endif 
                            </ul>
                        </nav> 
                    </div>
                </div>
            </div>
        </div>
    </div>  <!-- /.container-fluid -->
</section>
</div>
