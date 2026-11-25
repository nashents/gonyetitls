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
                                <th class="w-10 text-center line-height-35">Register Status</th>
                                <td class="w-20 line-height-35">
                                    @if ($attendance->isDraft())
                                        <span class="label label-warning">Draft</span>
                                    @else
                                        <span class="label label-success">Final</span>
                                        @if ($attendance->finalized_at)
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->finalized_at)->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @endif
                                </td>
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
                            <tr>
                                <th class="w-10 text-center line-height-35">Marked / Not Marked</th>
                                <td class="w-20 line-height-35">{{ $marked_count }} / {{ $unmarked_count }}</td>
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
                                @php
                                    // Same name on more than one employee record: flag it so the
                                    // second record (often the one left blank) is easy to trace.
                                    $nameKey = fn ($e) => $e ? strtolower(preg_replace('/\s+/', ' ', trim($e->name . ' ' . $e->surname))) : '';
                                    $nameCounts = collect($attendance_registers)->map(fn ($r) => $nameKey($r->employee))->countBy();
                                @endphp
                                @forelse ($attendance_registers as $row)
                                    @php $register = $row->register; @endphp
                                    <tr wire:key="register-row-{{ $loop->index }}-{{ $row->employee->id ?? 'x' }}">
                                        <td>
                                            {{$row->employee ? $row->employee->name : ""}} {{$row->employee ? $row->employee->surname : ""}}
                                            @if ($row->employee)
                                                <small class="text-muted">({{ $row->employee->employee_number ?: 'id ' . $row->employee->id }})</small>
                                                @if (($nameCounts[$nameKey($row->employee)] ?? 0) > 1)
                                                    <span class="label label-danger" title="Another employee record on this register has the same name (employee id {{ $row->employee->id }})">Duplicate record</span>
                                                @endif
                                            @endif
                                            @if ($row->employee)
                                                <a href="{{ route('audit_logs.index', ['attendance' => $attendance->id, 'attendanceEmployee' => $row->employee->id]) }}" title="Audit history for this person on this register" style="margin-left:4px"><i class="fa fa-history"></i></a>
                                            @endif
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $register->shift ?? '')) }}</td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $register->status ?? '')) }}</td>
                                        <td>{{ $register->checkin ?? '' }}</td>
                                        <td>{{ $register->checkout ?? '' }}</td>
                                        <td>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $register->notes ?? '')) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Employees In This Department....
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
