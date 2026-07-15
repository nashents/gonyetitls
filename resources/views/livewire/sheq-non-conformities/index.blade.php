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
                                <a href="" data-toggle="modal" data-target="#sheq_non_conformityModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Non-Conformity</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Search..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="open">Open</option>
                                        <option value="investigation">Under Investigation</option>
                                        <option value="actions">Actions In Progress</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="source_filter">
                                        <option value="">All Sources</option>
                                        <option value="audit">Audit</option>
                                        <option value="inspection">Inspection</option>
                                        <option value="incident">Incident</option>
                                        <option value="customer_complaint">Customer Complaint</option>
                                        <option value="contractor">Contractor</option>
                                        <option value="process">Process / Product</option>
                                        <option value="stop_and_fix">Stop & Fix / Stop Note</option>
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
                                    <th class="th-sm">Source</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Raised</th>
                                    <th class="th-sm">Description</th>
                                    <th class="th-sm">Class</th>
                                    <th class="th-sm">Actions</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_non_conformities))
                                <tbody>
                                    @forelse ($sheq_non_conformities as $nc)
                                  <tr>
                                    <td>{{$nc->nc_number}}</td>
                                    <td>{{ucwords(str_replace('_',' ',$nc->source))}}</td>
                                    <td>{{$nc->department->name ?? '-'}}</td>
                                    <td>{{$nc->date_raised ? \Carbon\Carbon::parse($nc->date_raised)->format('d M Y') : '-'}}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($nc->description, 80) }}</td>
                                    <td>
                                        @if ($nc->classification == 'major')
                                            <span class="label label-danger">Major</span>
                                        @elseif ($nc->classification == 'minor')
                                            <span class="label label-warning">Minor</span>
                                        @else
                                            <span class="label label-default">Observation</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{$nc->actions->count()}}
                                        @if ($nc->actions->whereNotIn('status',['completed','verified'])->count())
                                            <span class="label label-warning">{{$nc->actions->whereNotIn('status',['completed','verified'])->count()}} Open</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($nc->status == 'closed')
                                            <span class="label label-success">Closed</span>
                                        @elseif ($nc->status == 'actions')
                                            <span class="label label-info">Actions In Progress</span>
                                        @elseif ($nc->status == 'investigation')
                                            <span class="label label-primary">Under Investigation</span>
                                        @else
                                            <span class="label label-default">Open</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="raiseAction({{$nc->id}})"><i class="fa fa-tasks color-primary"></i> Raise Corrective Action</a></li>
                                                <li><a href="#" wire:click="edit({{$nc->id}})"><i class="fa fa-edit color-success"></i> Edit / Investigate</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$nc->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Non-Conformities Found ....
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
                                    @if (isset($sheq_non_conformities))
                                        {{ $sheq_non_conformities->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_non_conformityDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Non-Conformity?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_non_conformityModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Raise Non-Conformity <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-non-conformities.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_non_conformityEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit / Investigate Non-Conformity <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_non_conformity_id">
                <div class="modal-body">
                    @include('livewire.sheq-non-conformities.form')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Effectiveness Review (on closure)</label>
                                <textarea class="form-control" wire:model.debounce.300ms="effectiveness_review" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ncActionModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-tasks"></i> Raise Corrective Action <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
