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
                                <h4>{{$sheq_audit->audit_number}} — {{$sheq_audit->template->name ?? ''}}
                                    <small>{{$sheq_audit->department->name ?? ''}}</small>
                                    <span class="label label-primary">Running Score: {{$sheq_audit->actualTotal()}} / {{$sheq_audit->possibleTotal()}} ({{$sheq_audit->percentageScore()}}%)</span>
                                    <span class="label label-danger">NC: {{$sheq_audit->nonConformityCount()}}</span>
                                    <span class="label label-warning">OFI: {{$sheq_audit->ofiCount()}}</span>
                                </h4>
                                <small>Key: Grading (NC - Non Conformity, OFI - Opportunity for Improvement, C - Conformity)</small>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            @foreach ($sheq_audit->template->sections as $section)
                                <div class="panel panel-default" style="margin-bottom:15px">
                                    <div class="panel-heading">
                                        <strong>{{$section->code}} {{$section->title}}</strong>
                                        <span class="label label-default">{{$sheq_audit->sectionActualTotal($section)}} / {{$section->possibleTotal()}}</span>
                                    </div>
                                    <div class="panel-body" style="padding:10px">
                                        <table class="table table-bordered table-sm" width="100%">
                                            <thead>
                                                <tr>
                                                    <th style="width:6%">Ref</th>
                                                    <th style="width:26%">Requirement</th>
                                                    <th style="width:8%">Grading</th>
                                                    <th style="width:8%">Actual Mark</th>
                                                    <th style="width:22%">Findings</th>
                                                    <th style="width:20%">Objective Evidence</th>
                                                    <th style="width:10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($section->items as $item)
                                                    <tr>
                                                        <td>{{$item->code}}</td>
                                                        <td>
                                                            {{$item->requirement}}
                                                            @if ($item->guidance)
                                                                <br><small class="text-muted"><i class="fa fa-info-circle"></i> {{$item->guidance}}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <select class="form-control input-sm" wire:model.defer="grading.{{$item->id}}">
                                                                <option value="">-</option>
                                                                <option value="C">C</option>
                                                                <option value="OFI">OFI</option>
                                                                <option value="NC">NC</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" max="{{$item->possible_mark}}" class="form-control input-sm" wire:model.defer="actual_mark.{{$item->id}}" placeholder="/{{$item->possible_mark}}">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control input-sm" rows="2" wire:model.defer="findings.{{$item->id}}"></textarea>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control input-sm" rows="2" wire:model.defer="evidence.{{$item->id}}"></textarea>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-xs btn-success" wire:click="saveItem({{$item->id}})"><i class="fa fa-save"></i> Save</button>
                                                            <button type="button" class="btn btn-xs btn-warning" wire:click="raiseAction({{$item->id}})" style="margin-top:3px"><i class="fa fa-tasks"></i> Action</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Close Out</strong></div>
                                <div class="panel-body" style="padding:10px">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Audit Summary</label>
                                                <textarea class="form-control" rows="3" wire:model.defer="summary"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Recommendations</label>
                                                <textarea class="form-control" rows="3" wire:model.defer="recommendations"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn bg-success btn-wide btn-rounded" wire:click="completeAudit()" onclick="return confirm('Complete this audit? Scores will be finalised.')"><i class="fa fa-check"></i> Complete Audit</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="auditActionModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-tasks"></i> Raise Action from Finding <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeAction()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="action_title" required>
                                @error('action_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Responsible Person<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="action_employee_id" required>
                                    <option value="">Select Option</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('action_employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select class="form-control" wire:model.debounce.300ms="action_priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="action_due_date" required>
                                @error('action_due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="action_description" cols="30" rows="3"></textarea>
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
