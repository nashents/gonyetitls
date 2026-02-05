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
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search attendance register...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Attendance#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Date & Time
                                        </th>
                                        <th class="th-sm">Department
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($attendances))
                                    <tbody>
                                        @forelse  ($attendances as $attendance)
                                       
                                      <tr>
                                        <td>
                                            {{$attendance->attendance_number}}
                                        </td>
                                        <td>{{ucfirst($attendance->user ? $attendance->user->name : "")}} {{ucfirst($attendance->user ? $attendance->user->surname : "")}}</td>
                                        <td>{{$attendance->date}} {{$attendance->time ? "@ ".$attendance->time : ""}}</td>
                                        <td>{{$attendance->department ? $attendance->department->name : ""}}</td>
                                        <td><span class="badge bg-{{($attendance->authorization == 'approved') ? 'success' : (($attendance->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($attendance->authorization == 'approved') ? 'approved' : (($attendance->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('attendances.show', $attendance->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Attendance Register Found ....
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
                                        @if (isset($attendances))
                                            @if ($attendances->count()>0)
                                                {{ $attendances->links() }} 
                                            @endif
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



    </div>
