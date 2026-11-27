<div>
    <div class="mb-10" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
        <a href="#" wire:click.prevent="create" class="btn btn-xs btn-info"><i class="fa fa-plus"></i> Add Reminder</a>
        @if ($doneCount)
            <label style="font-weight:normal;margin:0"><input type="checkbox" wire:model="show_done"> Show done ({{ $doneCount }})</label>
        @endif
    </div>
    <div style="font-size:12px; line-height:1.4; color:#31708f; margin-bottom:8px;">
        <i class="fa fa-info-circle"></i> Follow-ups for this job, e.g. <em>Permit ready before discharge</em>. They are emailed to the staff you pick (and the client if ticked) on the days you choose before the due date,
        and show in the freight reminders bell and daily digest until marked done.
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Reminder</th>
                <th>Due</th>
                <th>Emails</th>
                <th>Recipients</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $reminder)
                @php
                    $days = $reminder->daysLeft();
                    $recipients = $service->recipients($reminder);
                    $next = $reminder->nextSendDate();
                @endphp
                <tr wire:key="job-reminder-{{ $reminder->id }}" class="{{ $reminder->isDone() ? 'text-muted' : '' }}">
                    <td>
                        <strong>{{ $reminder->title }}</strong>
                        @if ($reminder->notes)<br><small class="text-muted">{{ $reminder->notes }}</small>@endif
                        <br><small class="text-muted">Set by {{ $reminder->user ? trim($reminder->user->name . ' ' . $reminder->user->surname) : '—' }}</small>
                    </td>
                    <td style="white-space:nowrap">
                        {{ $reminder->due_at->format('d M Y H:i') }}
                        @unless ($reminder->isDone())
                            <br>
                            @if ($days < 0)
                                <small class="text-danger"><strong>{{ abs($days) }} day{{ abs($days) == 1 ? '' : 's' }} overdue</strong></small>
                            @elseif ($days === 0)
                                <small style="color:#d35400"><strong>Due today</strong></small>
                            @else
                                <small class="text-muted">in {{ $days }} day{{ $days == 1 ? '' : 's' }}</small>
                            @endif
                        @endunless
                    </td>
                    <td>
                        <small>{{ $reminder->remindLabel() }}{{ $reminder->daily_when_overdue ? ', daily when overdue' : '' }}</small>
                        @if ($next)
                            <br><small class="text-muted">Next: {{ $next->isToday() ? 'today' : $next->format('d M Y') }}</small>
                        @endif
                        @if ($reminder->last_sent_at)
                            <br><small class="text-muted">Last sent {{ $reminder->last_sent_at->format('d M Y H:i') }}</small>
                        @endif
                    </td>
                    <td>
                        <small>
                            <i class="fa fa-users"></i> {{ implode(', ', $recipients['staff']) ?: '—' }}
                            @if ($recipients['external'])
                                <br><i class="fa fa-envelope-o"></i> {{ implode(', ', $recipients['external']) }}
                            @elseif ($reminder->notify_client)
                                <br><span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Client has no contact email</span>
                            @endif
                        </small>
                    </td>
                    <td>
                        @if ($reminder->isDone())
                            <span class="label label-success label-wide">Done</span>
                            <br><small class="text-muted">{{ $reminder->completed_at?->format('d M Y') }}{{ $reminder->completer ? ' by ' . $reminder->completer->name : '' }}</small>
                        @elseif ($days < 0)
                            <span class="label label-danger label-wide">Overdue</span>
                        @elseif ($days <= 1)
                            <span class="label label-warning label-wide">Due soon</span>
                        @else
                            <span class="label label-info label-wide">Open</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap">
                        @if ($reminder->isDone())
                            <a href="#" wire:click.prevent="reopen({{ $reminder->id }})" class="btn btn-xs btn-default" title="Reopen"><i class="fa fa-undo"></i></a>
                        @else
                            <a href="#" wire:click.prevent="markDone({{ $reminder->id }})" class="btn btn-xs btn-success" title="Mark as done"><i class="fa fa-check"></i></a>
                            <a href="#" wire:click.prevent="sendNow({{ $reminder->id }})" wire:confirm="Email this reminder to all its recipients now?" class="btn btn-xs btn-info" title="Send now"><i class="fa fa-paper-plane"></i></a>
                            <a href="#" wire:click.prevent="edit({{ $reminder->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        <a href="#" wire:click.prevent="delete({{ $reminder->id }})" wire:confirm="Delete reminder &quot;{{ $reminder->title }}&quot;?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No {{ $show_done ? '' : 'open ' }}reminders on this job.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @include('livewire.freight.partials.pagination', ['paginator' => $rows])

    <!-- Add / Edit Reminder Modal -->
    <div wire:ignore.self class="modal fade" id="jobReminderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $reminder_id ? 'Edit Reminder' : 'Add Reminder' }} <small>{{ $job->job_number }}</small></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>What needs to happen <span class="required text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="title" placeholder="e.g. Import permit ready before discharge">
                                    @error('title') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Due <span class="required text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.defer="due_at">
                                    @if ($etd || $eta)
                                        <div style="font-size:12px; line-height:1.4; color:#555; margin-top:3px;">
                                            Use:
                                            @if ($etd)<a href="#" wire:click.prevent="useDate('etd')">ETD {{ $etd->format('d M Y') }}</a>@endif
                                            @if ($etd && $eta) · @endif
                                            @if ($eta)<a href="#" wire:click.prevent="useDate('eta')">ETA {{ $eta->format('d M Y') }}</a>@endif
                                        </div>
                                    @endif
                                    @error('due_at') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email reminders</label>
                            <div>
                                @foreach ($remindOptions as $day => $label)
                                    <label class="checkbox-inline" style="margin-right:10px">
                                        <input type="checkbox" wire:model.defer="remind_days" value="{{ $day }}"> {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <label class="checkbox-inline" style="margin-top:4px">
                                <input type="checkbox" wire:model.defer="daily_when_overdue"> Keep reminding daily once overdue (until marked done)
                            </label>
                            <div style="font-size:12px; line-height:1.4; color:#555; margin-top:4px;">Emails go out in the morning. A day that has already passed is sent straight away. Tick none to only show it in the bell.</div>
                            @error('remind_days') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Staff to email <small class="text-muted">(you are always included; {{ count(array_filter((array) $staff_user_ids)) }} ticked)</small></label>
                            <input type="text" class="form-control input-sm" wire:model.debounce.300ms="staff_search" placeholder="Search employees by name or email..." style="margin-bottom:4px">
                            <div style="max-height:180px;overflow-y:auto;border:1px solid #e3e7ec;border-radius:4px;padding:6px 10px;background:#fafbfc">
                                <div class="row">
                                    @forelse ($staffOptions as $employee)
                                        @php $hasEmail = filter_var($employee->user?->email, FILTER_VALIDATE_EMAIL); @endphp
                                        <div class="col-md-4" wire:key="staff-option-{{ $employee->id }}">
                                            <label style="font-weight:normal;margin:2px 0" title="{{ $hasEmail ? $employee->user->email : 'No email address - gets this reminder in the notifications bell only' }}">
                                                <input type="checkbox" wire:model.defer="staff_user_ids" value="{{ $employee->user_id }}">
                                                {{ $employee->name }} {{ $employee->surname }}
                                                @unless ($hasEmail)<small style="color:#c77a00" title="No email address - gets it in the notifications bell only">(bell only)</small>@endunless
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-md-12 text-muted">No employees match "{{ $staff_search }}".</div>
                                    @endforelse
                                </div>
                            </div>
                            <div style="font-size:12px; line-height:1.4; color:#555; margin-top:3px;">Non-driver employees of this job's company with a system login. Everyone ticked sees it in their notifications bell; those with an email also get emails. The job's creator, officers and salesperson are ticked by default and listed first.</div>
                            @error('staff_user_ids.*') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group" style="border:1px solid #e3e7ec;border-radius:6px;padding:10px 12px;">
                            <label style="font-weight:normal;margin:0">
                                <input type="checkbox" wire:model="notify_client">
                                <strong>Also email the client</strong> ({{ $job->customer?->name ?? 'no customer on this job' }})
                            </label>
                            <div style="font-size:12px; line-height:1.4; color:#555; margin:3px 0 0 20px;">
                                @if (count($clientEmails))
                                    To: {{ implode(', ', array_keys($clientEmails)) }}. They get a courtesy reminder without internal notes or links; replies come to you.
                                @else
                                    <span style="color:#c77a00;"><i class="fa fa-exclamation-triangle"></i> No contact email on file for this customer - add one under the customer's Contacts tab, or type addresses below.</span>
                                @endif
                            </div>
                            @if ($notify_client)
                                <div style="margin:8px 0 0 20px">
                                    <label>Message to the client</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="client_message" placeholder="e.g. Please send us the approved import permit so we can clear the cargo on arrival."></textarea>
                                    @error('client_message') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            <div style="margin:8px 0 0 20px">
                                <label>Other emails <small class="text-muted">(comma separated, get the client version)</small></label>
                                <input type="text" class="form-control" wire:model.defer="extra_emails" placeholder="e.g. agent@example.com, permits@client.com">
                                @error('extra_emails') <span class="text-danger error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Internal notes</label>
                            <textarea class="form-control" rows="2" wire:model.defer="notes" placeholder="Only staff see this"></textarea>
                            @error('notes') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
