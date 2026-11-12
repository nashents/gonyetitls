{{-- Change Position modal: multi-department picker with a default flag.
     Backed by App\Http\Livewire\Concerns\ManagesPositionDepartments. --}}
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Departments</label>
            <select class="form-control" wire:model="department_picker">
                <option value="">+ Add a department</option>
                @foreach ($departments as $department)
                    @if (! in_array($department->id, $department_ids ?? []))
                        <option value="{{ $department->id }}">{{ $department->name }}{{ $department->is_locked ? ' (Core)' : '' }}</option>
                    @endif
                @endforeach
            </select>
            @error('department_ids') <span class="error" style="color:red">{{ $message }}</span> @enderror

            @if (! empty($department_ids))
                <table class="table table-bordered table-condensed" style="margin-top:10px; margin-bottom:5px;">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th class="text-center" style="width:110px;">Default</th>
                            <th class="text-center" style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($department_ids as $selected_department_id)
                            @php $selected_department = $departments->firstWhere('id', $selected_department_id); @endphp
                            <tr wire:key="position-department-{{ $selected_department_id }}">
                                <td>
                                    {{ $selected_department ? $selected_department->name : 'Department #'.$selected_department_id }}
                                    @if ($selected_department && $selected_department->is_locked)
                                        <span class="label label-primary" title="Core system department"><i class="fa fa-lock"></i> Core</span>
                                    @else
                                        <span class="label label-default" title="Custom department - does not grant system access on its own">Custom</span>
                                    @endif
                                    @if ((int) $default_department_id === (int) $selected_department_id)
                                        <span class="label label-success">Default</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="default_department_id" wire:model="default_department_id" value="{{ $selected_department_id }}" title="Make this the default department">
                                </td>
                                <td class="text-center">
                                    <a href="#" wire:click.prevent="removeDepartment({{ $selected_department_id }})" title="Remove department"><i class="fa fa-trash color-danger"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <small class="text-muted">The default department is used for leave, payroll and attendance. Include at least one <strong>Core</strong> department so the employee can view their part of the system.</small>
            @else
                <br><small class="text-muted">No departments selected yet.</small>
            @endif
        </div>
    </div>
</div>
