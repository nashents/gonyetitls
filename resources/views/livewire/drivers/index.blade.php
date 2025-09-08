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
                                <div class="panel-title" style="float: right">
                                   
                                </div>
                                <div class="panel-title">
                                    <a href="{{route('drivers.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Driver</a>
                                    <a href="" data-toggle="modal" data-target="#driversImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportDriversExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportDriversCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportDriversPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>

                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                 <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search drivers...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Profile
                                        </th>
                                        <th class="th-sm">Driver#
                                        </th>
                                        <th class="th-sm">Transporter
                                        </th>
                                        <th class="th-sm">Fullname
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
                                    @if (isset($drivers))
                                    <tbody>
                                        @forelse ($drivers as $driver)
                                        @if (isset($driver->employee))
                                      <tr>
                                        @php
                                            $user = App\Models\User::find($driver->user_id);
                                        @endphp
                                        <td class="line-height-35">  
                                          <img src="{{asset('images/uploads/'.$user->profile)}}" alt="" class="border-radius-50 img-circle profile-img " style="width: 50px; height:50px">
                                        </td>
                                        <td>{{ucfirst($driver->driver_number)}}</td>
                                        <td>{{ucfirst($driver->transporter ? $driver->transporter->name : "")}}</td>
                                        <td>{{ucfirst($driver->employee ? $driver->employee->name : "")}} {{ucfirst($driver->employee ?$driver->employee->surname : "")}}</td>
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
                                            @php
                                                $user = App\Models\User::find($driver->user_id)
                                            @endphp
                                            @if ($user)
                                                @if (!is_null($user->username))
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
                                                    <li><a href="{{route('drivers.edit', $driver->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#driverDeleteModal{{$driver->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @if ($driver->status == 1)
                                                    <li><a href="{{route('drivers.deactivate',$driver->id)}}"  ><i class="fa fa-toggle-on color-danger"></i>Deactivate</a></li>
                                                    @else
                                                    <li><a href="{{route('drivers.activate',$driver->id)}}" ><i class="fa fa-toggle-off color-success"></i>Activate</a></li>
                                                    @endif
                                                    @if ($driver->user)
                                                    @if ($driver->user->active == 1)
                                                    <li><a href="{{route('employees.deactivate', $driver->employee_id)}}"  ><i class="fa fa-ban color-danger"></i>Suspend</a></li>
                                                    @else
                                                    <li><a href="{{route('employees.activate', $driver->employee_id)}}"  ><i class="fa fa-toggle-off color-success"></i>Unsuspend</a></li>
                                                    <li><a href="{{route('drivers.archive', $driver->id)}}"  ><i class="fa fa-archive color-primary"></i>Archive</a></li>
                                                    @endif
                                                    @endif

                                                </ul>
                                            </div>
                                            @include('drivers.delete')
                                        </td>
                                      </tr>
                                      @endif
                                      @empty
                                    <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Drivers Found ....
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
                                            @if (isset($drivers))
                                                {{ $drivers->links() }} 
                                            @endif 
                                        </ul>
                                    </nav>  

                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="driversImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Drivers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('drivers.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Driver(s) Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload Driver File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>
          <!-- Modal -->


    </div>
