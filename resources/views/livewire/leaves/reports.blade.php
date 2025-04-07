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
                                <form wire:submit.prevent="search()" class="p-20" enctype="multipart/form-data">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from">From</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="from" placeholder="From Date">
                                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="to">To</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="to" placeholder="To Date">
                                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group pull-right mt-10" >
                                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                                <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Generate Report</button>
                                            </div>
                                        </div>
                                        </div>
                                </form>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

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
                                    <th class="th-sm">HR Status
                                    </th>
                                    <th class="th-sm">HOD Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if (isset($search))
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
                                                <li><a href="#" data-toggle="modal" data-target="#leaveEditModal" wire:click.prevent="edit({{$leave->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#leaveDeleteModal{{$leave->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('leaves.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
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


    <!-- Modal -->


</div>
