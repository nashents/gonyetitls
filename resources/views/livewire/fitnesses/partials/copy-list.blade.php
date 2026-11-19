{{--
    Copy-list picker for reminder forms (ManagesReminderCopies trait).
    Needs: $copyList (the user's ReminderCopy rows), $copyEmployees.
--}}
@php
    $activeCopies = $copyList->where('status', true);
    $picked = array_map('strval', (array) $selected_copies);
@endphp
<div class="copy-list-box" style="border:1px solid #e3e7ec;border-radius:6px;padding:10px 12px;margin-top:10px;background:#fafbfc">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
        <strong><i class="fa fa-users"></i> Copy list <small class="text-muted">— who else gets these reminder emails</small></strong>
        <span>
            <a href="#" wire:click.prevent="toggleAddCopy"><i class="fa fa-{{ $show_add_copy ? 'minus' : 'plus' }}-square-o"></i> Add person</a>
            &nbsp;·&nbsp;
            <a href="{{ route('reminders.copy') }}" target="_blank"><i class="fa fa-external-link"></i> Manage full list</a>
        </span>
    </div>

    <div style="margin:8px 0 4px">
        <label class="radio-inline" style="margin-right:12px"><input type="radio" wire:model="copy_mode" value="none"> Nobody</label>
        <label class="radio-inline" style="margin-right:12px"><input type="radio" wire:model="copy_mode" value="all"> Everyone active ({{ $activeCopies->count() }})</label>
        <label class="radio-inline"><input type="radio" wire:model="copy_mode" value="selected"> Only people I tick</label>
    </div>

    @if($show_add_copy)
        <div class="row" style="margin:6px -6px 4px">
            <div class="col-md-3" style="padding:0 6px">
                <select class="form-control input-sm" wire:model="new_copy_employee_id">
                    <option value="">Pick an employee…</option>
                    @foreach($copyEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->surname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2" style="padding:0 6px">
                <input type="text" class="form-control input-sm" wire:model.defer="new_copy_name" placeholder="Name *">
                @error('new_copy_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2" style="padding:0 6px">
                <input type="text" class="form-control input-sm" wire:model.defer="new_copy_surname" placeholder="Surname">
            </div>
            <div class="col-md-3" style="padding:0 6px">
                <input type="email" class="form-control input-sm" wire:model.defer="new_copy_email" placeholder="Email *">
                @error('new_copy_email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2" style="padding:0 6px">
                <button type="button" class="btn btn-success btn-sm btn-block" wire:click="addCopy" wire:loading.attr="disabled"><i class="fa fa-plus"></i> Add</button>
            </div>
        </div>
    @endif

    @if($copyList->isEmpty())
        <p class="text-muted" style="margin:6px 0 0"><small>Your copy list is empty. Use <em>Add person</em> to add colleagues who should be copied on reminders.</small></p>
    @else
        @if($copy_mode === 'selected')
            <small>
                <a href="#" wire:click.prevent="selectAllCopies">Tick all active</a> ·
                <a href="#" wire:click.prevent="clearSelectedCopies">Clear</a>
                · {{ count(array_intersect($picked, $activeCopies->pluck('id')->map(fn ($i) => (string) $i)->all())) }} selected
            </small>
        @endif
        <div style="max-height:190px;overflow-y:auto;margin-top:4px">
            <table class="table table-condensed" style="margin:0;background:transparent">
                <tbody>
                    @foreach($copyList as $copy)
                        @php $isPicked = in_array((string) $copy->id, $picked, true); @endphp
                        <tr wire:key="copy-{{ $copy->id }}" style="{{ $copy->status ? '' : 'opacity:.55' }}">
                            <td style="width:28px;vertical-align:middle">
                                @if($copy_mode === 'selected')
                                    <input type="checkbox" wire:model="selected_copies" value="{{ $copy->id }}" @if(!$copy->status) disabled title="Inactive - activate to pick" @endif>
                                @elseif($copy_mode === 'all')
                                    <i class="fa {{ $copy->status ? 'fa-check text-success' : 'fa-minus text-muted' }}"></i>
                                @else
                                    <i class="fa fa-minus text-muted"></i>
                                @endif
                            </td>
                            <td style="vertical-align:middle">{{ trim($copy->name . ' ' . $copy->surname) }}</td>
                            <td style="vertical-align:middle"><small>{{ $copy->email }}</small></td>
                            <td style="vertical-align:middle;white-space:nowrap;text-align:right">
                                <a href="#" wire:click.prevent="toggleCopyActive({{ $copy->id }})" title="{{ $copy->status ? 'Deactivate (stop all copies to this person)' : 'Activate' }}">
                                    <span class="badge bg-{{ $copy->status ? 'success' : 'secondary' }}">{{ $copy->status ? 'Active' : 'Inactive' }}</span>
                                </a>
                                <a href="#" wire:click.prevent="removeCopy({{ $copy->id }})" onclick="return confirm('Remove {{ addslashes($copy->email) }} from your copy list? They stop getting copies of all your reminders.')" title="Remove from copy list"><i class="fa fa-trash color-danger"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <small class="text-muted">Active / Inactive and removing apply to your whole copy list, not just this reminder.</small>
    @endif
</div>
