<div>
    @php
        use Carbon\Carbon;
    @endphp
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
                                <table id="employeesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption>Reporting To</caption>
                                    <thead >
                                        <th class="th-sm">
                                            Profile
                                        </th>
                                        <th class="th-sm">Fullname
                                        </th>
                                        <th class="th-sm">Gender
                                        </th>
                                        <th class="th-sm">Email
                                        </th>
                                        <th class="th-sm">Department
                                        </th>
                                        <th class="th-sm">Post
                                        </th>
                                        <th class="th-sm">Leave Status
                                        </th>
                                      </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($department_heads as $employee)
                                            <tr>
                                                <td class="line-height-35"><img src="{{asset('images/uploads/'.$employee->user->profile)}}" alt="" class="border-radius-50 img-circle profile-img " style="width: 50px; height:50px"></td>
                                                <td>{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</td>
                                                <td>{{$employee->gender}}</td>
                                                <td>{{$employee->email}}</td>
                                                <td>
                                                    @foreach ($employee->departments as $department)
                                                        @if (in_array($department->id, $employee_department_ids))
                                                            <li>{{ $department->name }}</li>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>{{$employee->post}}</td> 
                                                <td>
                                                    @php
                                                        $today = Carbon::today();
                                                        $onLeave = $employee->leaves->contains(function ($leave) use ($today) {
                                                            return $today->between($leave->start_date, $leave->end_date);
                                                        });
                                                    @endphp

                                                    @if ($onLeave)
                                                        <span class="badge bg-primary">On Leave</span>
                                                    @else
                                                        <span class="badge bg-success">Available</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                  </table>

                                <table id="employeesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption>My Team</caption>
                                    <thead >
                                        <th class="th-sm">
                                            Profile
                                        </th>
                                        <th class="th-sm">Fullname
                                        </th>
                                        <th class="th-sm">Gender
                                        </th>
                                        <th class="th-sm">Email
                                        </th>
                                        <th class="th-sm">Department(s)
                                        </th>
                                        <th class="th-sm">Post
                                        </th>
                                        <th class="th-sm">Leave Status
                                        </th>
                                      </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($employees as $employee)
                                            <tr>
                                                <td class="line-height-35"><img src="{{asset('images/uploads/'.$employee->user->profile)}}" alt="" class="border-radius-50 img-circle profile-img " style="width: 50px; height:50px"></td>
                                                <td>{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</td>
                                                <td>{{$employee->gender}}</td>
                                                <td>{{$employee->email}}</td>
                                                <td>
                                                    @foreach ($employee->departments as $department)
                                                        @if (in_array($department->id, $employee_department_ids))
                                                            <li>{{ $department->name }}</li>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>{{$employee->post}}</td> 
                                                <td>
                                                    @php
                                                        $today = Carbon::today();
                                                        $onLeave = $employee->leaves->contains(function ($leave) use ($today) {
                                                            return $today->between($leave->start_date, $leave->end_date);
                                                        });
                                                    @endphp

                                                    @if ($onLeave)
                                                        <span class="badge bg-primary">On Leave</span>
                                                    @else
                                                        <span class="badge bg-success">Available</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
