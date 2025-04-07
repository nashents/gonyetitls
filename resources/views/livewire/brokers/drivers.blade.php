<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title" style="float: right">
           
        </div>
        <div class="panel-title">
            <a href="#" data-toggle="modal" data-target="#addDriverModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Driver</a>
        </div>

    </div>
    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

        <table id="broker_driversTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Driver#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Surname
                </th>
                <th class="th-sm">License#
                </th>
                <th class="th-sm">Class
                </th>
                <th class="th-sm">Experience
                </th>
                <th class="th-sm">Availability
                </th>
                <th class="th-sm">Account
                </th>
                @if (Auth::user()->is_admin())
                <th class="th-sm">Username
                </th> 
                @endif
               
                <th class="th-sm">Actions
                </th>

              </tr>
            </thead>
            @if (isset($broker_drivers))
            @if ($broker_drivers->count()>0)
            <tbody>
                @foreach ($broker_drivers as $driver)
                @if (isset($driver->employee))
              <tr>
                <td>{{ucfirst($driver->driver_number)}}</td>
                <td>{{ucfirst($driver->transporter ? $driver->transporter->name : "")}}</td>
                <td>{{ucfirst($driver->employee ? $driver->employee->name : "")}}</td>
                <td>{{ucfirst($driver->employee ?$driver->employee->surname : "")}}</td>
                <td>{{$driver->license_number}}</td>
                <td>{{$driver->class}}</td>
                <td>{{$driver->experience}}</td>
                <td><span class="badge bg-{{$driver->status == 1 ? "success" : "danger"}}">{{$driver->status == 1 ? "Available" : "Unavailable"}}</span></td>
                <td>
                    @if ($driver->user)
                    <span class="badge bg-{{$driver->user->active == 1 ? "success" : "danger"}}">{{$driver->user->active == 1 ? "Active" : "Inactive"}}</span>
                    @else
                    <span class="badge bg-danger">Deleted</span>
                    @endif
                </td>
                @if (Auth::user()->is_admin())
                <td>
                    @if ($driver->user)
                        @if (!is_null($driver->user->username))
                        <span class="badge bg-success">Username set</span>
                        @else
                        <span class="badge bg-warning">Username not set</span>
                        @endif
                    @endif
                   
                </td>
                @endif
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{route('employees.show', $driver->employee->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                        </ul>
                    </div>
                    @include('drivers.delete')

            </td>
              </tr>
              @endif
              @endforeach
            </tbody>
            @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
            @endif
            @endif

          </table>

        <!-- /.col-md-12 -->
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this Driver from Broker`s Driver List? </strong> </center>
                </div>
                <form wire:submit.prevent="removeDriver()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addDriverModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="driver">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Driver(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div style="height: 400px; overflow: auto">
                        <div class="form-group">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Add Driver<span class="required" style="color: red">*</span></th>
                                  </tr>
                                </thead>
                                @if ($drivers->count()>0)
                                <tbody>
                                    @foreach ($drivers as $driver)
                                  <tr>
                                    <td>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="driver_id.{{$driver->id}}" wire:key="{{ $driver->id }}" value="{{ $driver->id }}" class="line-style"  />
                                            <label for="one" class="radio-label"> {{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}} </label>
                                            @error('driver_id.'.$driver->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @endif
                              </table>  
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
</div>
