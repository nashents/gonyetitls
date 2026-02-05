<div>
    <div class="row mt-30">
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Attendance Register</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Attendance#</th>
                                <td class="w-20 line-height-35">{{$attendance->attendance_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$attendance->user ? $attendance->user->name : ""}} {{$attendance->user ? $attendance->user->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Department</th>
                                <td class="w-20 line-height-35">{{$attendance->department ? $attendance->department->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Date & Time</th>
                                <td class="w-20 line-height-35">{{$attendance->date}} {{ $attendance->time ? " @ ".$attendance->time : ""}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table  class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                        <thead>
                            <tr>
                                <th class="th-sm">Employee
                                </th>
                                <th class="th-sm">Shift
                                </th>
                                <th class="th-sm">Status
                                </th>
                                <th class="th-sm">Checkin
                                </th>
                                <th class="th-sm">Checkout
                                </th>
                                <th class="th-sm">Notes
                                </th>
                            </tr>
                        </thead>
                        @if (isset($attendance_registers))
                            <tbody>
                                @forelse ($attendance_registers as $attendance_register)
                                    <tr>
                                        <td>{{$attendance_register->employee ? $attendance_register->employee->name : ""}} {{$attendance_register->employee ? $attendance_register->employee->surname : ""}}</td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $attendance_register->shift ?? '')) }}</td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $attendance_register->status ?? '')) }}</td>
                                        <td>{{$attendance_register->checkin}}</td>
                                        <td>{{$attendance_register->checkout}}</td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $attendance_register->notes ?? '')) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Attendance Register Marked....
                                            </div>
                                        </td>
                                    </tr>  
                                @endforelse
                            </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                        @endif
                    </table>
                </div>
              
               
               <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
