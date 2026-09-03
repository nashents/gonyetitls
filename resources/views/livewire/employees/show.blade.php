<div>
    @php
           $roles = Auth::user()->roles;
            foreach($roles as $role){
                $role_names[] = $role->name;
            }
    @endphp
    <div class="row mt-30">
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{$employee->name}} {{$employee->surname}}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <img src="{{asset('images/uploads/'.$employee->user->profile)}}" alt="{{$employee->name}} {{$employee->surname}}" class="img-responsive">
                            @if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names))
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-xs btn-labeled mt-10" data-toggle="modal" data-target="#profilePictureModal">Update Profile<span class="btn-label btn-label-right"><i class="fa fa-pencil"></i></span></button>
                                </div>
                            @endif 
                         
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Account Credentials</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Username</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>{{$employee->user ? $employee->user->username : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Pin</th>
                                    <td>
                                        <small class="color-success">
                                            <i class="fa fa-arrow-right"></i> 
                                            <span class="pin-blur">
                                                    {{$employee->pin}}
                                            </span>
                                        </small>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Use email <br> as username</th>
                                    <td>
                                       
                                        <div class="mb-10">
                                           <input type="checkbox" wire:model.debounce.300ms="use_email_as_username" wire:change="setUsername()"  class="line-style" />
                                           <label for="one" class="radio-label"></label>
                                           @error('use_email_as_username') <span class="text-danger error">{{ $message }}</span>@enderror
                                       </div>
                                    </td>
                                </tr>
                                @if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL))
                                    <tr>
                                        <th>Share Credentials</th>
                                        <td>
                                            <button type="button" wire:click.prevent="sendCredentials()" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-send-o"></i>{{$employee->user->sent_credentials == False ? "Send" : "Resend"}}</button>
                                        </td>
                                    </tr>
                                @endif
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.panel -->

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Employee Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">user</span>
                    <span class="label label-danger label-rounded label-bordered">tags</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Basic Info</a></li>
                <li role="presentation"><a href="#history" aria-controls="history" role="tab" data-toggle="tab">History</a></li>
                @if (isset($employee->driver))
                <li role="presentation"><a href="#driver" aria-controls="driver" role="tab" data-toggle="tab">Driver Info</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trip(s)</a></li>
                <li role="presentation"><a href="#allowances" aria-controls="allowances" role="tab" data-toggle="tab">Allowance(s)</a></li>
                <li role="presentation"><a href="#recoveries" aria-controls="recoveries" role="tab" data-toggle="tab">Recoveries</a></li>
                @endif
                <li role="presentation"><a href="#fitness" aria-controls="fitness" role="tab" data-toggle="tab">Reminders</a></li>
                <li role="presentation"><a href="#departments" aria-controls="departments" role="tab" data-toggle="tab">Departments</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#dependants" aria-controls="dependants" role="tab" data-toggle="tab">Dependants</a></li>
                <li role="presentation"><a href="#leaves" aria-controls="leaves" role="tab" data-toggle="tab">Leave Requests</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Emp#</th>
                                <td class="w-20 line-height-35">{{$employee->employee_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$employee->name}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Middle Name</th>
                                <td class="w-20 line-height-35">{{$employee->middle_name}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Surname</th>
                                    <td class="w-20 line-height-35">{{$employee->surname}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Gender</th>
                                    <td class="w-20 line-height-35">{{$employee->gender}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">DOB</th>
                                    <td class="w-20 line-height-35">{{$employee->dob}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$employee->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">ID #</th>
                                    <td class="w-20 line-height-35">{{$employee->idnumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$employee->phonenumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Country</th>
                                    <td class="w-20 line-height-35">{{$employee->country}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Province</th>
                                    <td class="w-20 line-height-35">{{$employee->province}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">City</th>
                                    <td class="w-20 line-height-35">{{$employee->city}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Suburb</th>
                                    <td class="w-20 line-height-35">{{$employee->suburb}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Street Address</th>
                                    <td class="w-20 line-height-35">{{$employee->street_address}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Post</th>
                                    <td class="w-20 line-height-35">{{$employee->post}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Grade</th>
                                    <td class="w-20 line-height-35">{{$employee->grade ? $employee->grade->grade_name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Department(s)</th>
                                    <td class="w-20 line-height-35">@foreach ($employee->departments as $department)
                                        {{$department->name ? $department->name." " : ""}} 
                                    @endforeach
                                </td>

                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Rank(s)</th>
                                    <td class="w-20 line-height-35">@foreach ($employee->ranks as $rank)
                                        {{$rank->name}}
                                    @endforeach
                                </td>

                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Branch</th>
                                    <td class="w-20 line-height-35">{{$employee->branch ? $employee->branch->name : ""}}</td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">Role(s)</th>
                                    <td class="w-20 line-height-35">@foreach ($employee->user?->roles ?? [] as $role)
                                        {{$role->name}}
                                    @endforeach
                                </td>

                                </tr>
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Contract Duration</th>
                                    <td class="w-20 line-height-35">{{$employee->duration ? $employee->duration."Month(s)" : ''}} </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Expiration Date</th>
                                    <td class="w-20 line-height-35">{{$employee->expiration}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Next Of Kin</th>
                                    <td class="w-20 line-height-35">{{$employee->next_of_kin}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Relationship</th>
                                    <td class="w-20 line-height-35">{{$employee->relationship}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Contact</th>
                                    <td class="w-20 line-height-35">{{$employee->contact}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Employement Start Date</th>
                                    <td class="w-20 line-height-35">{{$employee->start_date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Employement End Date</th>
                                    <td class="w-20 line-height-35">{{$employee->end_date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Employement Duration</th>
                                    @php
                                      if (preg_match($pattern, $employee->start_date) && preg_match($pattern, $employee->end_date) ){
                                        $start_date = Carbon\Carbon::parse($employee->start_date);
                                        $end_date = Carbon\Carbon::parse($employee->end_date);

                                        if (isset($start_date) && isset($end_date)) {
                                            $diffInYears = $start_date->diffInYears($end_date);
                                            $diffInMonths = $start_date->diffInMonths($end_date) % 12;
                                        }

                                      }else{
                                        $diffInYears = Null;
                                        $diffInMonths = Null;
                                      }
                                                               
                                    @endphp
                                    <td class="w-20 line-height-35">
                                        {{ $diffInYears ?  $diffInYears." Year(s)" : ""}}  {{ $diffInMonths ?  $diffInMonths." Months" : ""}}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Leave Accrual Rate / Month</th>
                                    <td class="w-20 line-height-35">{{$employee->accrual_rate ? $employee->accrual_rate." Day(s)" : ""}}
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Available Leave Days</th>
                                    <td class="w-20 line-height-35">{{$employee->leave_days ? $employee->leave_days." Day(s)" : ""}} 
                                </tr>
                                <tr>

                                </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="history">
                       <table   class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th class="th-sm" style="width: 20%;">Position
                            </th>
                            <th class="th-sm">Prev Pos EndDate
                            </th>
                            <th class="th-sm">New Pos StartDate
                            </th>
                            <th class="th-sm">Reason
                            </th>
                            <th class="th-sm">ChangedBy
                            </th>
                            <th class="th-sm">Remarks
                            </th>
                            <th class="th-sm">ChangedOn
                            </th>
                          </tr>
                        </thead>
                        @if ($employee->employee_positions)
                        <tbody>
                            @forelse ($employee->employee_positions as $employee_position)
                          <tr>
                            <td>
                                <strong>Job Title:</strong> {{$employee_position->job_title ? $employee_position->job_title->title : ""}} <br>
                                <strong>Grade:</strong> {{$employee_position->grade ? $employee_position->grade->grade_code : ""}} <br>
                                <strong>Dpt:</strong> {{$employee_position->department ? $employee_position->department->name : ""}} <br>
                                <strong>Rank</strong> {{$employee_position->rank ? $employee_position->rank->name : ""}} <br>
                                <strong>Branch</strong> {{$employee_position->branch ? $employee_position->branch->name : ""}} <br>
                            </td>
                            <td>{{$employee_position->end_date}}</td>
                            <td>{{$employee_position->start_date}}</td>
                            <td>{{$employee_position->change_reason}}</td>
                            <td>
                                @php
                                    $user = App\Models\User::find($employee_position->changed_by);
                                @endphp
                                {{$user ? $user->name : ""}} {{$user ? $user->surname : ""}} 
                            </td>
                            <td>{{$employee_position->remarks}}</td>
                            <td>{{$employee_position->created_at}}</td>
                          </tr>
                          @empty
                          <tr>
                            <td colspan="7">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Employeement History Found ....
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
                @if (isset($employee->driver))
                <div role="tabpanel" class="tab-pane" id="driver">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Transporter</th>
                                <td class="w-20 line-height-35">
                                    @if ($employee->driver)
                                    {{$employee->driver->transporter ? $employee->driver->transporter->name : ""}}
                                    @endif
                                   </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Experience</th>
                                <td class="w-20 line-height-35">{{$employee->driver->experience}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">License Class</th>
                                <td class="w-20 line-height-35">{{$employee->driver->class}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">License #</th>
                                <td class="w-20 line-height-35">{{$employee->driver->license_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Passport #</th>
                                <td class="w-20 line-height-35">{{$employee->driver->passport_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Reference Name</th>
                                <td class="w-20 line-height-35">{{$employee->driver->reference}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Reference Phonenumber</th>
                                <td class="w-20 line-height-35">{{$employee->driver->reference_phonenumber}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    @if ($employee->driver)
                        @livewire('drivers.show.trips', ['id' => $employee->driver->id])
                    @endif
                </div>
                <div  role="tabpanel" class="tab-pane" id="allowances">
                    <table   class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th class="th-sm">Trip
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Allowance
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Amount
                            </th>
                            {{-- <th class="th-sm">Status
                            </th> --}}
                          </tr>
                        </thead>
                        @if (isset($driver_allowances))
                        <tbody>
                            @forelse ($driver_allowances as $driver_allowance)
                          <tr>
                            <td>
                                @if ($driver_allowance->trip)
                                {{$driver_allowance->trip ? $driver_allowance->trip->trip_number : ""}} <strong>From:</strong> {{$driver_allowance->trip->loading_point ? $driver_allowance->trip->loading_point->name : ""}} <strong>To:</strong> {{$driver_allowance->trip->offloading_point ? $driver_allowance->trip->offloading_point->name : ""}}
                                @endif
                            </td>
                            <td>{{$driver_allowance->trip ? $driver_allowance->trip->start_date : ""}} </td>
                            <td>{{$driver_allowance->allowance ? $driver_allowance->allowance->name : ""}}</td>                            
                            <td>{{$driver_allowance->currency ? $driver_allowance->currency->name : ""}}</td>  
                            <td>
                                @if ($driver_allowance->amount)
                                    {{$driver_allowance->currency ? $driver_allowance->currency->symbol : ""}}{{number_format($driver_allowance->amount,2)}}
                                @endif
                            </td>
                            {{-- <td><span class="label label-{{($driver_allowance->status == 'Paid') ? 'success' : (($driver_allowance->status == 'Partial') ? 'warning' : 'danger') }}">{{ $driver_allowance->status }}</span></td>--}}
                          </tr>
                          @empty
                          <tr>
                            <td colspan="6">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No allowance for driver found ....
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
                                @if (isset($driver_allowances))
                                    {{ $driver_allowances->links() }} 
                                @endif 
                            </ul>
                        </nav>  
                      
                </div>
                 
                <div role="tabpanel" class="tab-pane" id="recoveries">
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th class="th-sm">Recovery#
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Trip
                            </th>
                            <th class="th-sm">Deduction
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Amount
                            </th>
                            <th class="th-sm">Balance
                            </th>
                            <th class="th-sm">Payment
                            </th>
                            <th class="th-sm">Action
                            </th>
                          </tr>
                        </thead>
                        @if (isset($recoveries))
                        <tbody>
                            @forelse ($recoveries as $recovery)
                          <tr>
                            <td>{{$recovery->recovery_number}}</td>
                            <td>{{$recovery->date}}</td>
                            <td>
                                @if ($recovery->trip)
                                {{$recovery->trip ? $recovery->trip->trip_number : ""}} <strong>From:</strong> {{$recovery->trip->loading_point ? $recovery->trip->loading_point->name : ""}} <strong>To:</strong> {{$recovery->trip->offloading_point ? $recovery->trip->offloading_point->name : ""}}        
                                @endif
                            </td>
                            <td>{{$recovery->deduction ? $recovery->deduction->name : ""}}</td>
                            <td>{{$recovery->currency ? $recovery->currency->name : ""}}</td>  
                            <td>
                                @if ($recovery->amount)
                                    {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->amount,2)}}
                                @endif
                            </td>
                            <td>
                                @if ($recovery->balance)
                                    {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->balance,2)}}
                                @endif
                            </td>
                            <td><span class="label label-{{($recovery->status == 'Paid') ? 'success' : (($recovery->status == 'Partial') ? 'warning' : 'danger') }}">{{ $recovery->status }}</span></td>
                            <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{route('recoveries.show',$recovery->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                    </ul>
                                </div>
                               
                        </td>
                          </tr>
                          @empty
                          <tr>
                            <td colspan="9">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No recoveries for driver found ....
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
                                @if (isset($recoveries))
                                    {{ $recoveries->links() }} 
                                @endif 
                            </ul>
                        </nav>    
                </div>
                @endif
                <div role="tabpanel" class="tab-pane" id="fitness">
                    @livewire('fitnesses.index', ['id' => $employee->id, 'type' => "Employee"])
               </div>
                <div role="tabpanel" class="tab-pane" id="departments">
                    <a href="" data-toggle="modal" data-target="#departmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Department</a>
                    <br>
                    <br>
                    <table id="departmentsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="th-sm">Name
                                </th> 
                                <th class="th-sm">Code
                                </th> 
                                <th class="th-sm">Actions
                                </th> 
                            </tr>
                        </thead>
                        @if ($employee_departments->count()>0)
                            <tbody>
                                @foreach ($employee_departments as $department)
                                    <tr>
                                        <td>{{$department->name}}</td>
                                        <td>{{$department->department_code}}</td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>

                                                <ul class="dropdown-menu">
                                                    <li><a href="#" wire:click="showRemove({{ $department->id }})"><i class="fa fa-remove color-danger"></i>Remove</a></li>
                                                </ul>
                                            </div>
                                            @include('employees.delete')

                                    </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                        @endif
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $employee->id,'category'=>'employee'])
                </div>
                <div role="tabpanel" class="tab-pane" id="dependants">
                    @livewire('employees.dependants', ['id' => $employee->id])
               </div>
               <div role="tabpanel" class="tab-pane" id="leaves">
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th class="th-sm">CreatedBy
                            </th>
                            <th class="th-sm">Type
                            </th>
                            <th class="th-sm">Date Applied
                            </th>
                            <th class="th-sm">Start Date
                            </th>
                            <th class="th-sm">End Date
                            </th>
                            <th class="th-sm">Duration
                            </th>
                            <th class="th-sm">Reason
                            </th>
                            <th class="th-sm">Status
                            </th>
                          </tr>
                        </thead>
                        @if (isset($leaves))
                        <tbody>
                            @forelse ($leaves as $leave)
                          <tr>
                            <td>
                                {{ucfirst($leave->user ? $leave->user->name : '')}} {{ucfirst($leave->user ? $leave->user->surname : '')}}
                            </td>
                            <td>{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                            <td>{{Carbon\Carbon::parse($leave->created_at)->format('d F Y')}}</td>
                            <td>{{Carbon\Carbon::parse($leave->from)->format('d F Y')}}</td>
                            <td>{{Carbon\Carbon::parse($leave->to)->format('d F Y')}}</td>
                            <td>{{$leave->days ? $leave->days." Days" : ""}}</td>
                            <td>{{$leave->reason}}</td>
                            <td>
                                    @php
                                    $today = Carbon\Carbon::today();
                                    $start = Carbon\Carbon::parse($leave->from);
                                    $end = Carbon\Carbon::parse($leave->to);

                                    if ($today->lt($start)) {
                                        $status = 'Not Yet Started';
                                        $badgeClass = 'badge bg-warning text-dark';
                                    } elseif ($today->between($start, $end)) {
                                        $status = 'In Progress';
                                        $badgeClass = 'badge bg-info text-white';
                                    } else {
                                        $status = 'Completed';
                                        $badgeClass = 'badge bg-success';
                                    }
                                @endphp
                                <span class="badge bg-{{($leave->status == 'approved') ? 'success' : (($leave->status == 'rejected') ? 'danger' : 'warning') }}">{{($leave->status == 'approved') ? 'approved' : (($leave->status == 'rejected') ? 'rejected' : 'pending' )}}</span>
                                <small><span class="{{ $badgeClass }}">{{ $status }}</span></small>
                            </td>
                          </tr>
                          @empty
                          <tr>
                            <td colspan="8">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Leave Applications Found ....
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
                            @if (isset($driver_allowances))
                                {{ $driver_allowances->links() }} 
                            @endif 
                        </ul>
                    </nav>    
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
        <!-- /.col-md-9 -->
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="departmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="deparment">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Department(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addDepartments()" >
                <div class="modal-body"> 
                    <div class="form-group">
                        <label for="title">Departments<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="department_id.0" class="form-control" required>
                            <option value="">Select Department</option>
                                @foreach ($all_departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                        </select>
                        @error('department_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                        @foreach ($inputs as $key => $value)
               
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="title">Departments<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="department_id.{{ $value }}" class="form-control" required>
                                        <option value="">Select Department</option>
                                            @foreach ($all_departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                    </select>
                                    @error('department_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Department</button>
                                </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeDepartmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this {{ $selected_department?->name }} Department?</strong> </center> 
                </div>
                <form wire:submit.prevent="removeDepartment()">
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="updateEmployeeLeaveDaysModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Update Available Leave Days Cargo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">

                    <div class="form-group">
                        <label for="name">Available Leave Days</label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Enter Leave Days" required >
                        @error('leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    @section('extra-js')

<script>
    $(document).ready( function () {
        $('#tripsTable').DataTable();
    } );
    </script>

@endsection


<div  class="modal" id="profilePictureModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal4Label"><i class="fa fa-picture-o"></i>Upload your picture <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form method="POST" action="{{route('postProfile', $employee->user->id)}}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="category" value="school">
            <div class="modal-body">
                <div class="form-group">
                    <label for="address">Profile Picture</label>
                   <input type="file" name="file" placeholder="Upload profile image" class="form-control">
                    @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
            
            </div>
            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>


</div>
