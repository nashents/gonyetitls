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
                               Rejected leave applications
                            </div>

                        </div>
                        <div class="panel-body p-20">

                            <table id="leavesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Date Applied
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Leave Type
                                    </th>
                                    <th class="th-sm">Application Date
                                    </th>
                                    <th class="th-sm">HOD Status
                                    </th>
                                    <th class="th-sm">HR Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @php
                                $ranks = Auth::user()->employee->ranks;
                                foreach($ranks as $rank){
                                $rank_names[] = $rank->name;
                                }
                                $roles = Auth::user()->roles;
                                foreach($roles as $role){
                                $role_names[] = $role->name;
                                }
                                $departments = Auth::user()->employee->departments;
                                foreach($departments as $department){
                                $department_names[] = $department->name;
                                }
                            @endphp
                            @if (in_array('HOD',$rank_names))
                            @if (isset($leaves))
                                @if ($leaves->count()>0)
                                <tbody>
                                    @foreach ($leaves as $leave)
                                  <tr>
                                    <td>{{$leave->created_at}}</td>
                                  <td>{{ucfirst($leave->user ? $leave->user->name : "")}} {{ucfirst($leave->user ? $leave->user->surname : "")}}</td>
                                  <td>{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                                    <td>{{$leave->created_at}}</td>
                                    <td><span class="badge bg-{{($leave->management_decision == '1') ? 'success' : (($leave->management_decision == '0') ? 'danger' : 'warning') }}">{{($leave->management_decision == '1') ? 'approved' : (($leave->management_decision == '0') ? 'rejected' : 'pending' )}}</span></td>
                                    <td><span class="badge bg-{{($leave->hod_decision == '1') ? 'success' : (($leave->hod_decision == '0') ? 'danger' : 'warning') }}">{{($leave->hod_decision == '1') ? 'approved' : (($leave->hod_decision == '0') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('leaves.show',$leave->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#decisionModal{{$leave->id}}" ><i class="fa fa-gavel color-default"></i> Decision</a></li>
                                            </ul>
                                            @include('leaves.decision')
                                        </div>
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                            @endif
                            @endif
                            @endif
                            @if (in_array('Management',$rank_names))
                            @if (isset($management_leaves))
                                @if ($management_leaves->count()>0)
                                <tbody>
                                    @foreach ($management_leaves as $leave)
                                  <tr>
                                    <td>{{$leave->created_at}}</td>
                                    <td>{{ucfirst($leave->user ? $leave->user->name : "")}} {{ucfirst($leave->user ? $leave->user->surname : "")}}</td>
                                    <td>{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                                    <td>{{$leave->created_at}}</td>
                                    <td><span class="badge bg-{{($leave->management_decision == 'approved') ? 'success' : (($leave->management_decision == 'rejected') ? 'danger' : 'warning') }}">{{($leave->management_decision == 'approved') ? 'approved' : (($leave->management_decision == 'rejected') ? 'rejected' : 'pending' )}}</span></td>
                                    <td><span class="badge bg-{{($leave->hod_decision == 'approved') ? 'success' : (($leave->hod_decision == 'rejected') ? 'danger' : 'warning') }}">{{($leave->hod_decision == 'approved') ? 'approved' : (($leave->hod_decision == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('leaves.show',$leave->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#decisionModal{{$leave->id}}" ><i class="fa fa-gavel color-default"></i> Decision</a></li>
                                            </ul>
                                            @include('leaves.decision')
                                        </div>
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                            @endif
                            @endif
                            @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
</div>
