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
                                <form wire:submit.prevent="search()" class="p-20"  enctype="multipart/form-data">

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

                            <table id="assignmentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">#
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Starting Odometer
                                    </th>
                                    @if (isset($assignment->ending_odometer))
                                    <th class="th-sm">Ending Odometer
                                    </th>
                                    @endif
                                    <th class="th-sm">Start Date
                                    </th>
                                    @if (isset($assignment->end_date))
                                    <th class="th-sm">End Date
                                    </th>
                                    @endif
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($search))
                                @if ($assignments->count()>0)
                                <tbody>
                                    @foreach ($assignments as $assignment)
                                  <tr>
                                  <td>{{$assignment->id}}</td>
                                    <td>{{$assignment->vehicle->make}} {{$assignment->vehicle->model}} ({{$assignment->vehicle->registration_number}})</td>
                                    <td>{{ucfirst($assignment->driver->employee->name)}} {{ucfirst($assignment->driver->employee->surname)}}</td>
                                    <td>{{$assignment->starting_odometer}}</td>
                                    @if (isset($assignment->ending_odometer))
                                    <td>{{$assignment->ending_odometer}}</td>
                                    @endif

                                    <td>{{$assignment->start_date}}</td>
                                    @if (isset($assignment->end_date))
                                    <td>{{$assignment->end_date}}</td>
                                    @endif
                                    <td>{{$assignment->comments}}</td>
                                    <td><span class="label label-{{$assignment->status == 1 ? "success" : "danger"}} label-rounded">{{$assignment->status == 1 ? "Active" : "Expired"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" data-toggle="modal" data-target="#assignmentEditModal" wire:click="edit({{$assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#assignmentDeleteModal{{ $assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
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



</div>

