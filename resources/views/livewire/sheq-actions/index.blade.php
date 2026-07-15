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
                                <a href="" data-toggle="modal" data-target="#sheq_actionModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Action</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Search actions..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="verified">Verified</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="source_filter">
                                        <option value="">All Sources</option>
                                        <option value="audit">Audit</option>
                                        <option value="incident">Incident</option>
                                        <option value="inspection">Inspection</option>
                                        <option value="drill">Emergency Drill</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="risk_assessment">Risk Assessment</option>
                                        <option value="non_conformity">Non-Conformity</option>
                                        <option value="hygiene_survey">Hygiene Survey</option>
                                        <option value="change">Change Management</option>
                                        <option value="engagement">Leadership Engagement</option>
                                        <option value="management_review">Management Review</option>
                                        <option value="evaluation">Compliance Evaluation</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
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
                                    <th class="th-sm">Title</th>
                                    <th class="th-sm">Source</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Responsible</th>
                                    <th class="th-sm">Priority</th>
                                    <th class="th-sm">Due Date</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_actions))
                                <tbody>
                                    @forelse ($sheq_actions as $sheq_action)
                                  <tr>
                                    <td>{{$sheq_action->action_number}}</td>
                                    <td>{{$sheq_action->title}}</td>
                                    <td>{{ucwords(str_replace('_',' ',$sheq_action->source))}}</td>
                                    <td>{{$sheq_action->department->name ?? '-'}}</td>
                                    <td>{{$sheq_action->employee ? $sheq_action->employee->name.' '.$sheq_action->employee->surname : '-'}}</td>
                                    <td>
                                        @if ($sheq_action->priority == 'high')
                                            <span class="label label-danger">High</span>
                                        @elseif ($sheq_action->priority == 'medium')
                                            <span class="label label-warning">Medium</span>
                                        @else
                                            <span class="label label-default">Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{$sheq_action->due_date ? \Carbon\Carbon::parse($sheq_action->due_date)->format('d M Y') : '-'}}
                                        @if ($sheq_action->isOverdue())
                                            <span class="label label-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sheq_action->status == 'open')
                                            <span class="label label-default">Open</span>
                                        @elseif ($sheq_action->status == 'in_progress')
                                            <span class="label label-info">In Progress</span>
                                        @elseif ($sheq_action->status == 'completed')
                                            <span class="label label-primary">Completed</span>
                                        @elseif ($sheq_action->status == 'verified')
                                            <span class="label label-success">Verified
                                                @if($sheq_action->effectiveness)
                                                 - {{ucwords(str_replace('_',' ',$sheq_action->effectiveness))}}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if (!in_array($sheq_action->status,['completed','verified']))
                                                    @if ($sheq_action->status == 'open')
                                                    <li><a href="#" wire:click.prevent="start({{$sheq_action->id}})"><i class="fa fa-play color-info"></i> Start</a></li>
                                                    @endif
                                                    <li><a href="#" wire:click.prevent="complete({{$sheq_action->id}})"><i class="fa fa-check color-success"></i> Complete</a></li>
                                                    <li><a href="#" wire:click.prevent="edit({{$sheq_action->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                                @if ($sheq_action->status == 'completed')
                                                    <li><a href="#" wire:click.prevent="verify({{$sheq_action->id}})"><i class="fa fa-check-square color-primary"></i> Verify Effectiveness</a></li>
                                                @endif
                                                <li><a href="#" wire:click.prevent="delete({{$sheq_action->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Actions Found ....
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
                                    @if (isset($sheq_actions))
                                        {{ $sheq_actions->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_actionDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Action?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_actionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Action <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-actions.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_actionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Action <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_action_id">
                <div class="modal-body">
                    @include('livewire.sheq-actions.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_actionCompleteModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-check"></i> Complete Action <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="saveComplete()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Completion Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="completed_date" required>
                                @error('completed_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Completion Notes / Evidence</label>
                                <textarea class="form-control" wire:model.debounce.300ms="completion_notes" cols="30" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-check"></i>Complete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_actionVerifyModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-check-square"></i> Verify Action Effectiveness <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="saveVerify()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Effectiveness<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="effectiveness" required>
                                    <option value="">Select Option</option>
                                    <option value="effective">Effective</option>
                                    <option value="partially_effective">Partially Effective</option>
                                    <option value="not_effective">Not Effective (Re-open)</option>
                                </select>
                                @error('effectiveness') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Review Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="effectiveness_notes" cols="30" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
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

</div>
