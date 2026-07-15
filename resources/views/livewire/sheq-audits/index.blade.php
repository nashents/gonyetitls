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
                                <a href="" data-toggle="modal" data-target="#sheq_auditModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Schedule Audit</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="Search audit number..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="planned">Planned</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="department_filter">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Number</th>
                                    <th class="th-sm">Checklist</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Lead Auditor</th>
                                    <th class="th-sm">Type</th>
                                    <th class="th-sm">Scheduled</th>
                                    <th class="th-sm">Score</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_audits))
                                <tbody>
                                    @forelse ($sheq_audits as $sheq_audit)
                                  <tr>
                                    <td><a href="{{route('sheq_audits.show',$sheq_audit->id)}}">{{$sheq_audit->audit_number}}</a></td>
                                    <td>{{$sheq_audit->template->name ?? '-'}}</td>
                                    <td>{{$sheq_audit->department->name ?? '-'}}</td>
                                    <td>{{$sheq_audit->lead_auditor ? $sheq_audit->lead_auditor->name.' '.$sheq_audit->lead_auditor->surname : '-'}}</td>
                                    <td>{{ucwords($sheq_audit->audit_type)}}</td>
                                    <td>{{$sheq_audit->scheduled_date ? \Carbon\Carbon::parse($sheq_audit->scheduled_date)->format('d M Y') : '-'}}</td>
                                    <td>
                                        @if ($sheq_audit->responses->count())
                                            {{$sheq_audit->percentageScore()}}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sheq_audit->status == 'planned')
                                            <span class="label label-default">Planned</span>
                                        @elseif ($sheq_audit->status == 'in_progress')
                                            <span class="label label-info">In Progress</span>
                                        @elseif ($sheq_audit->status == 'completed')
                                            <span class="label label-primary">Completed</span>
                                        @else
                                            <span class="label label-success">Closed</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if (in_array($sheq_audit->status,['planned','in_progress']))
                                                    <li><a href="{{route('sheq_audits.conduct',$sheq_audit->id)}}"><i class="fa fa-play color-info"></i> Conduct Audit</a></li>
                                                    <li><a href="#" wire:click="edit({{$sheq_audit->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                                <li><a href="{{route('sheq_audits.show',$sheq_audit->id)}}"><i class="fa fa-file-text color-primary"></i> View Report</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$sheq_audit->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Audits Found ....
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
                                    @if (isset($sheq_audits))
                                        {{ $sheq_audits->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_auditDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Audit?</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_auditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Schedule Audit <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-audits.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_auditEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Audit <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_audit_id">
                <div class="modal-body">
                    @include('livewire.sheq-audits.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
