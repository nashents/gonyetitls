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
                                <a href="" data-toggle="modal" data-target="#sheq_obligationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Compliance Obligation</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Search..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="type_filter">
                                        <option value="">All Types</option>
                                        <option value="permit">Permit</option>
                                        <option value="licence">Licence</option>
                                        <option value="legal">Legal / Statutory</option>
                                        <option value="agreement">Agreement / SLA</option>
                                        <option value="exemption">Exemption</option>
                                        <option value="guideline">Regulator Guideline</option>
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
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="valid">Valid</option>
                                        <option value="pending_renewal">Pending Renewal</option>
                                        <option value="expired">Expired</option>
                                        <option value="not_applicable">No Longer Applicable</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Number</th>
                                    <th class="th-sm">Title</th>
                                    <th class="th-sm">Type</th>
                                    <th class="th-sm">Authority</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Responsible</th>
                                    <th class="th-sm">Expiry</th>
                                    <th class="th-sm">Last Evaluation</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_obligations))
                                <tbody>
                                    @forelse ($sheq_obligations as $obligation)
                                  <tr>
                                    <td>{{$obligation->obligation_number}}</td>
                                    <td>{{$obligation->title}} @if($obligation->reference_number)<br><small>{{$obligation->reference_number}}</small>@endif</td>
                                    <td>{{ucwords(str_replace('_',' ',$obligation->type))}}</td>
                                    <td>{{$obligation->issuing_authority}}</td>
                                    <td>{{$obligation->department->name ?? '-'}}</td>
                                    <td>{{$obligation->employee ? $obligation->employee->name.' '.$obligation->employee->surname : '-'}}</td>
                                    <td>
                                        {{$obligation->expiry_date ? \Carbon\Carbon::parse($obligation->expiry_date)->format('d M Y') : '-'}}
                                        @if ($obligation->isExpired())
                                            <span class="label label-danger">Expired</span>
                                        @elseif ($obligation->expiry_date && \Carbon\Carbon::parse($obligation->expiry_date)->diffInDays(\Carbon\Carbon::today()) <= 30)
                                            <span class="label label-warning">Expiring Soon</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $latest = $obligation->latestEvaluation(); @endphp
                                        @if ($latest)
                                            @if ($latest->compliance_status == 'compliant')
                                                <span class="label label-success">Compliant</span>
                                            @elseif ($latest->compliance_status == 'partially_compliant')
                                                <span class="label label-warning">Partially Compliant</span>
                                            @else
                                                <span class="label label-danger">Non-Compliant</span>
                                            @endif
                                            <br><small>{{\Carbon\Carbon::parse($latest->evaluation_date)->format('d M Y')}}</small>
                                        @else
                                            <span class="label label-default">Not Evaluated</span>
                                        @endif
                                    </td>
                                    <td>{{ucwords(str_replace('_',' ',$obligation->status))}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="evaluate({{$obligation->id}})"><i class="fa fa-check-square color-primary"></i> Evaluate Compliance</a></li>
                                                <li><a href="#" wire:click="edit({{$obligation->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$obligation->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Compliance Obligations Found ....
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
                                    @if (isset($sheq_obligations))
                                        {{ $sheq_obligations->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_obligationDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Compliance Obligation?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_obligationModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Compliance Obligation <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-obligations.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_obligationEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Compliance Obligation <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_obligation_id">
                <div class="modal-body">
                    @include('livewire.sheq-obligations.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_obligationEvaluateModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-check-square"></i> Evaluate Compliance <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeEvaluation()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Evaluation Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="evaluation_date" required>
                                @error('evaluation_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Compliance Status<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="compliance_status" required>
                                    <option value="">Select Option</option>
                                    <option value="compliant">Compliant</option>
                                    <option value="partially_compliant">Partially Compliant</option>
                                    <option value="non_compliant">Non-Compliant</option>
                                </select>
                                @error('compliance_status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Findings / Evidence</label>
                                <textarea class="form-control" wire:model.debounce.300ms="findings" cols="30" rows="3"></textarea>
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
