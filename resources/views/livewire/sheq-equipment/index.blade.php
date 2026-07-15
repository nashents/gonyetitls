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
                                <a href="" data-toggle="modal" data-target="#sheq_equipmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Equipment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Search equipment..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="class_filter">
                                        <option value="">All Classes</option>
                                        @foreach ($classes as $class)
                                            <option value="{{$class->id}}">{{$class->name}}</option>
                                        @endforeach
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
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="in_service">In Service</option>
                                        <option value="quarantined">Quarantined</option>
                                        <option value="decommissioned">Decommissioned</option>
                                        <option value="overdue">Inspection Overdue</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Number</th>
                                    <th class="th-sm">Class</th>
                                    <th class="th-sm">Description</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">SWL</th>
                                    <th class="th-sm">Cert Expiry</th>
                                    <th class="th-sm">Colour Code</th>
                                    <th class="th-sm">Next Inspection</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_equipment))
                                <tbody>
                                    @forelse ($sheq_equipment as $equipment)
                                  <tr>
                                    <td>{{$equipment->equipment_number}}</td>
                                    <td>{{$equipment->equipment_class->name ?? '-'}}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($equipment->description, 50) }}</td>
                                    <td>{{$equipment->department->name ?? '-'}}</td>
                                    <td>{{$equipment->swl ?? '-'}}</td>
                                    <td>
                                        {{$equipment->certificate_expiry ? \Carbon\Carbon::parse($equipment->certificate_expiry)->format('d M Y') : '-'}}
                                        @if ($equipment->isCertificateExpired())
                                            <span class="label label-danger">Expired</span>
                                        @endif
                                    </td>
                                    <td>{{ucwords($equipment->current_color_code) ?: '-'}}</td>
                                    <td>
                                        {{$equipment->next_inspection_date ? \Carbon\Carbon::parse($equipment->next_inspection_date)->format('d M Y') : '-'}}
                                        @if ($equipment->isInspectionOverdue())
                                            <span class="label label-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($equipment->status == 'in_service')
                                            <span class="label label-success">In Service</span>
                                        @elseif ($equipment->status == 'quarantined')
                                            <span class="label label-danger">Quarantined</span>
                                        @else
                                            <span class="label label-default">{{ucwords(str_replace('_',' ',$equipment->status))}}</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="inspect({{$equipment->id}})"><i class="fa fa-check-square color-primary"></i> Record Inspection</a></li>
                                                <li><a href="#" wire:click="edit({{$equipment->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$equipment->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Equipment Found ....
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
                                    @if (isset($sheq_equipment))
                                        {{ $sheq_equipment->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_equipmentDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Equipment?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_equipmentModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Equipment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-equipment.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_equipmentEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Equipment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_equipment_id">
                <div class="modal-body">
                    @include('livewire.sheq-equipment.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_equipmentInspectModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-check-square"></i> Record Equipment Inspection <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeInspection()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspection Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="inspection_date" required>
                                @error('inspection_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspector</label>
                                <select class="form-control" wire:model.debounce.300ms="inspector_id">
                                    <option value="">Select Option</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Result<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="result" required>
                                    <option value="">Select Option</option>
                                    <option value="pass">Pass</option>
                                    <option value="fail">Fail (Quarantine)</option>
                                </select>
                                @error('result') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Colour Code Applied</label>
                                <select class="form-control" wire:model.debounce.300ms="color_code_applied">
                                    <option value="">None</option>
                                    <option value="red">Red</option>
                                    <option value="green">Green</option>
                                    <option value="blue">Blue</option>
                                    <option value="yellow">Yellow</option>
                                    <option value="white">White</option>
                                    <option value="orange">Orange</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Defects Found</label>
                                <textarea class="form-control" wire:model.debounce.300ms="defects" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="comments" rows="2"></textarea>
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
