{{-- Marking rows for $employees; $prefix keeps wire:keys unique per modal. --}}
@php $i = 1; @endphp
@forelse ($employees as $employee)
    <tr wire:key="{{ $prefix }}-row-{{ $employee->id }}">
        <td>
            {{$i++}}) {{$employee->name}} {{$employee->surname}}  <small><strong>{{$employee->post ? "(".$employee->post.")" : ""}}</strong></small>
        </td>
        <td>
            <select class="form-control" wire:model.debounce.300ms="status.{{$employee->id}}" wire:key="{{ $prefix }}-status-{{ $employee->id }}" >
                <option value="">Select Option</option>
                <option value="absent">Absent</option>
                <option value="annual_leave">Annual Leave</option>
                <option value="compassionate_leave">Compassionate Leave</option>
                <option value="off">Off</option>
                <option value="present">Present</option>
                <option value="public_holiday">Public Holiday</option>
                <option value="sick_leave">Sick Leave</option>
                <option value="suspension">Suspension</option>
                <option value="training">Training</option>
                <option value="unpaid_leave">Unpaid Leave</option>
            </select>
        </td>
        <td>
            <select class="form-control" wire:model.debounce.300ms="shift.{{$employee->id}}" wire:key="{{ $prefix }}-shift-{{ $employee->id }}">
                <option value="">Select Option</option>
                <option value="day">Day</option>
                <option value="night">Night</option>
            </select>
        </td>
        <td>
            <input type="time" class="form-control" wire:model.debounce.300ms="checkin.{{$employee->id}}" wire:key="{{ $prefix }}-checkin-{{ $employee->id }}">
        </td>
        <td>
            <input type="time" class="form-control" wire:model.debounce.300ms="checkout.{{$employee->id}}" wire:key="{{ $prefix }}-checkout-{{ $employee->id }}">
        </td>
        <td>
            <select class="form-control" wire:model.debounce.300ms="notes.{{$employee->id}}" wire:key="{{ $prefix }}-notes-{{ $employee->id }}">
                <option value="">Select Option</option>
                <option value="early_arrival">Early Arrival</option>
                <option value="on_time">On time</option>
                <option value="slightly_late">Slightly Late</option>
                <option value="late_arrival">Late Arrival</option>
            </select>
        </td>
    </tr>
@empty
    @if (!empty($emptyMessage))
        <tr wire:key="{{ $prefix }}-empty">
            <td colspan="6" class="text-center text-muted" style="padding:15px">{{ $emptyMessage }}</td>
        </tr>
    @endif
@endforelse
