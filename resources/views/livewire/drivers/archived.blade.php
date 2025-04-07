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
                                        <th class="th-sm">Gender
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
                                        <td>{{$driver->employee ? $driver->employee->gender : ""}}</td>
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
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" wire:click="restore({{$driver->id}})"><i class="fas fa-undo color-default"></i> Restore</a></li>

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
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>

        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="driverRestoreModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to restore this Driver?</strong> </center>
                    </div>
                    <form wire:submit.prevent="update()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

          <!-- Modal -->


    </div>
