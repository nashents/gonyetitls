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
                        </div>
                        <div class="panel-body p-20">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                         <th class="th-sm">Employee
                                        </th>
                                        <th class="th-sm">Type
                                        </th>
                                        <th class="th-sm">CreatedOn
                                        </th>
                                        <th class="th-sm">Start Date
                                        </th>
                                        <th class="th-sm">End Date
                                        </th>
                                        <th class="th-sm">Duration
                                        </th>
                                        <th class="th-sm">Reason
                                        </th>
                                        <th class="th-sm">Flags
                                        </th>
                                        <th class="th-sm">HR Auth
                                        </th>
                                        <th class="th-sm">HOD Auth
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                </thead>
                               
                                @if (isset($leaves))
                                <tbody>
                                    @forelse ($leaves as $leave)
                                  <tr>
                                      <td>
                                        {{ucfirst($leave->employee ? $leave->employee->name : '')}} {{ucfirst($leave->employee ? $leave->employee->surname : '')}}
                                        @if ($leave->department)
                                            <br>
                                            <small><strong><i>{{$leave->department ? $leave->department->name : ""}}</i></strong></small>
                                        @endif
                                    </td>
                                    <td>{{$leave->leave_type ? $leave->leave_type->name : ""}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->created_at)->format('d F Y')}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->from)->format('d F Y')}}</td>
                                    <td>{{Carbon\Carbon::parse($leave->to)->format('d F Y')}}</td>
                                    <td>{{$leave->days ? $leave->days." Days" : ""}}</td>
                                    <td>{{$leave->reason}}</td>
                                    <td>
                                        @if ($leave->is_backdated)
                                            <span class="badge bg-danger">Backdated</span>
                                        @endif    
                                        @if ($leave->is_emergency)
                                            <span class="badge bg-warning">Emergency</span>
                                        @endif    
                                    </td>
                                  <td><span class="badge bg-{{($leave->management_decision == 'approved') ? 'success' : (($leave->management_decision == 'rejected') ? 'danger' : 'warning') }}">{{($leave->management_decision == 'approved') ? 'approved' : (($leave->management_decision == 'rejected') ? 'rejected' : 'pending' )}}</span></td>
                                    <td><span class="badge bg-{{($leave->hod_decision == 'approved') ? 'success' : (($leave->hod_decision == 'rejected') ? 'danger' : 'warning') }}">{{($leave->hod_decision == 'approved') ? 'approved' : (($leave->hod_decision == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td>
                                       <span class="badge bg-{{($leave->status == 'approved') ? 'success' : (($leave->status == 'rejected') ? 'danger' : 'warning') }}">{{($leave->status == 'approved') ? 'approved' : (($leave->status == 'rejected') ? 'rejected' : 'pending' )}}</span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
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
                                          
                                                <li><a href="{{route('leaves.show',$leave->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                @if (Auth::user()->employee->id !== $leave->employee_id)
                                                    @if (isset($hod))
                                                        <li><a href="#"  wire:click="authorize({{$leave->id}},'hod')" ><i class="fa fa-gavel color-default"></i> HOD Auth</a></li>
                                                    @endif
                                                    @if ((in_array('Management', $rank_names) && in_array('Human Resources', $department_names)) || (in_array('Admin', $role_names) && in_array('Human Resources', $department_names)) || in_array('Super Admin', $role_names))
                                                        <li><a href="#"  wire:click="authorize({{$leave->id}},'hr')" ><i class="fa fa-gavel color-default"></i> HR Auth</a></li>
                                                    @endif
                                                @endif
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
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
                                    @if (isset($leaves))
                                        {{ $leaves->links() }} 
                                    @endif 
                                </ul>
                            </nav>    
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="decisionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gavel"></i>Approve | Reject Application<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="decision">
                <div class="modal-body">
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
                @if (Auth::user()->employee->department_head)
                <div class="panel-title">
                    HOD Decision
                 </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Decision</label>
                              <select wire:model.debounce.300ms="decision" class="form-control" >
                                <option value=""> Select Decision</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                              </select>
                                @error('decision') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Reason</label>
                               <textarea wire:model.debounce.300ms="reason" class="form-control"  cols="30" rows="5"></textarea>
                                @error('reason') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                @endif
                @if ((in_array('Management',$rank_names)))
                <div class="panel-title">
                    Management Decision
                 </div>
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Decision</label>
                          <select wire:model.debounce.300ms="decision" class="form-control" >
                            <option value=""> Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                          </select>
                            @error('decision') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Reason</label>
                           <textarea wire:model.debounce.300ms="reason" class="form-control"  cols="30" rows="5"></textarea>
                            @error('reason') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                @endif
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
