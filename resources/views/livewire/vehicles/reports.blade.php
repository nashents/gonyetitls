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
                                    <form wire:submit.prevent="search()" class="p-20" enctype="multipart/form-data">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="from">From</label>
                                                    <input type="date" class="form-control" wire:model.debounce.300ms="from" placeholder="From Date">
                                                    @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
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
                                    </form>
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                                <table id="vehiclesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">CreatedBy
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">Model
                                            </th>
                                            <th class="th-sm">HRN
                                            </th>
                                            <th class="th-sm">Chasis#
                                            </th>
                                            <th class="th-sm">Engine#
                                            </th>
                                            <th class="th-sm">Year
                                            </th>
                                            <th class="th-sm">Availability
                                            </th>
                                            <th class="th-sm">Service
                                            </th>
                                            <th class="th-sm">Actions
                                            </th>
                                          </tr>
                                    </thead>
                                    @if (isset($search))
                                    @if ($vehicles->count()>0)
                                    <tbody>
                                        @foreach ($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ucfirst($vehicle->user ? $vehicle->user->name : "undefined")}} {{ucfirst($vehicle->user ? $vehicle->user->surname : "undefined")}}</td>
                                            <td>{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "undefined")}}</td>
                                            <td>{{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "undefined")}}</td>
                                            <td>{{ucfirst($vehicle->registration_number)}}</td>
                                            <td>{{$vehicle->chasis_number}}</td>
                                            <td>{{$vehicle->engine_number}}</td>
                                            <td>{{$vehicle->year}}</td>
                                            <td><span class="badge bg-{{$vehicle->status == 1 ? "success" : "danger"}}">{{$vehicle->status == 1 ? "Available" : "Unavailable"}}</span></td>
                                            <td><span class="badge bg-{{$vehicle->service == 0 ? "success" : "danger"}}">{{$vehicle->status == 0 ? "Fit for use" : "In Service"}}</span></td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="{{route('vehicles.show', $vehicle->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                        <li><a href="{{route('vehicles.edit', $vehicle->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#vehicleDeleteModal{{$vehicle->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                        @if ($vehicle->status == 1)
                                                        <li><a href="{{route('vehicles.deactivate', $vehicle->id)}}"  ><i class="fa fa-toggle-off color-danger"></i>Deactivate</a></li>
                                                        @else
                                                        <li><a href="{{route('vehicles.activate', $vehicle->id)}}"  ><i class="fa fa-toggle-on color-success"></i>Activate</a></li>
                                                        @endif

                                                    </ul>
                                                </div>
                                                @include('vehicles.delete')

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
            <!-- /.container-fluid -->
        </section>

          <!-- Modal -->


    </div>
