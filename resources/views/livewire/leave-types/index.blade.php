<div>
    <section class="section">
        <x-loading/>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#leaveTypeModal" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Leave Type
                                </a>
                            </div>
                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <table id="leave_typesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Entitlement</th>
                                        <th>Accrual</th>
                                        <th>Paid</th>
                                        <th>Carry Forward</th>
                                        <th>Max Consecutive</th>
                                        <th>Max Balance</th>
                                        <th>Medical Report</th>
                                        <th>Attachment</th>
                                        <th>Approvals</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                @if ($leave_types->count() > 0)
                                    <tbody>
                                        @foreach ($leave_types as $leave_type)
                                            <tr>
                                                <td>{{ $leave_type->name }}</td>
                                                <td>{{ $leave_type->code }}</td>
                                                <td>
                                                    {{ $leave_type->entitlement ? $leave_type->entitlement . ' Days' : '0 Days' }}
                                                </td>
                                                <td>
                                                    @if($leave_type->is_accruable)
                                                        <span class="badge bg-success">Yes</span><br>
                                                        <small>{{ $leave_type->monthly_accrual_rate }} / Month</small>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $leave_type->is_paid ? 'success' : 'danger' }}">
                                                        {{ $leave_type->is_paid ? 'Paid' : 'Unpaid' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($leave_type->carry_forward_allowed)
                                                        <span class="badge bg-success">Yes</span><br>
                                                        <small>Max: {{ $leave_type->max_carry_forward_days }} Days</small>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $leave_type->max_consecutive_days ? $leave_type->max_consecutive_days . ' Days' : '-' }}
                                                </td>
                                                <td>
                                                    {{ $leave_type->maximum_leave_days ? $leave_type->maximum_leave_days . ' Days' : '-' }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $leave_type->requires_medical_report ? 'warning' : 'default' }}">
                                                        {{ $leave_type->requires_medical_report ? 'Required' : 'No' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $leave_type->requires_attachment ? 'warning' : 'default' }}">
                                                        {{ $leave_type->requires_attachment ? 'Required' : 'No' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        HOD:
                                                        <strong>{{ $leave_type->requires_hod_approval ? 'Yes' : 'No' }}</strong>
                                                    </small>
                                                    <br>
                                                    <small>
                                                        Management:
                                                        <strong>{{ $leave_type->requires_management_approval ? 'Yes' : 'No' }}</strong>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $leave_type->active ? 'success' : 'danger' }}">
                                                        {{ $leave_type->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="#" wire:click.prevent="edit({{ $leave_type->id }})">
                                                                    <i class="fa fa-edit color-success"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" data-toggle="modal" data-target="#leaveTypeDeleteModal{{ $leave_type->id }}">
                                                                    <i class="fa fa-trash color-danger"></i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                    <div wire:ignore.self class="modal fade" id="leaveTypeDeleteModal{{ $leave_type->id }}" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">
                                                                        <i class="fa fa-trash"></i> Delete Leave Type
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                    </h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>
                                                                        Are you sure you want to delete
                                                                        <strong>{{ $leave_type->name }}</strong>?
                                                                    </p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                                                        <i class="fa fa-times"></i> Close
                                                                    </button>
                                                                    <button wire:click.prevent="delete({{ $leave_type->id }})" data-dismiss="modal" class="btn btn-danger btn-wide btn-rounded">
                                                                        <i class="fa fa-trash"></i> Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{ asset('images/nodata.png') }}" alt="">
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CREATE MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveTypeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fa fa-plus"></i> Add Leave Type
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form>
                    @include('livewire.leave-types.form-fields')

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button wire:click.prevent="store()" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveTypeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Leave Type
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form>
                    <input type="hidden" wire:model="leave_type_id">

                    @include('livewire.leave-types.form-fields')

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button wire:click.prevent="update()" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-refresh"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SHARED FORM FIELDS --}}
    <template id="leave-type-form-fields-template"></template>

    <script>
        window.addEventListener('hide-leaveTypeModal', event => {
            $('#leaveTypeModal').modal('hide');
        });

        window.addEventListener('show-leaveTypeEditModal', event => {
            $('#leaveTypeEditModal').modal('show');
        });

        window.addEventListener('hide-leaveTypeEditModal', event => {
            $('#leaveTypeEditModal').modal('hide');
        });
    </script>
</div>