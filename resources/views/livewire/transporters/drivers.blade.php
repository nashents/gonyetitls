<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title" style="float: right">
           
        </div>
        <div class="panel-title">
            <a href="{{route('drivers.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Driver</a>
         
        </div>

    </div>
    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

        <table id="driversTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
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
            @if ($drivers->count()>0)
            <tbody>
                @foreach ($drivers as $driver)
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

          </table>

        <!-- /.col-md-12 -->
    </div>
</div>
