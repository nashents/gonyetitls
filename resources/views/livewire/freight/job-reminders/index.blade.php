<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Job Reminders</h5>
                                <small style="color: green">Follow-ups set on each freight job's Reminders tab, across all {{ $seesAll ? '' : 'your ' }}jobs.
                                    Add or edit a reminder on the job itself.</small>
                            </div>
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            @php
                                $tiles = [
                                    'overdue' => ['Overdue', '#c0392b', 'fa-exclamation-circle'],
                                    'today' => ['Due today', '#d35400', 'fa-clock-o'],
                                    'week' => ['Next 7 days', '#2471a3', 'fa-calendar'],
                                    'open' => ['All open', '#555', 'fa-bell'],
                                ];
                            @endphp
                            <div class="row mb-20">
                                @foreach ($tiles as $key => [$label, $color, $icon])
                                    <div class="col-md-3 col-sm-6" style="margin-bottom:8px">
                                        <a href="#" wire:click.prevent="setStatus('{{ $key }}')" style="display:block;text-decoration:none;border:1px solid {{ $status === $key ? $color : '#e3e7ec' }};border-left:4px solid {{ $color }};border-radius:4px;padding:10px 14px;background:{{ $status === $key ? '#f7f9fb' : '#fff' }}">
                                            <span style="font-size:22px;font-weight:bold;color:{{ $color }}">{{ $counts[$key] }}</span>
                                            <span style="color:#555;margin-left:6px"><i class="fa {{ $icon }}"></i> {{ $label }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row mb-10">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search reminder, job #, customer ref, customer">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" wire:model="status">
                                        <option value="open">Open</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="today">Due today</option>
                                        <option value="week">Next 7 days</option>
                                        <option value="done">Done</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" wire:model="from" title="Due from">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" wire:model="to" title="Due to">
                                </div>
                                <div class="col-md-2" style="padding-top:7px">
                                    <label style="font-weight:normal"><input type="checkbox" wire:model="mine"> Only mine</label>
                                </div>
                            </div>

                            <div class="row mb-10">
                                <div class="col-md-12">
                                    <a href="#" wire:click.prevent="exportExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click.prevent="exportCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click.prevent="exportPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Job #</th>
                                            <th>Customer</th>
                                            <th>Reminder</th>
                                            <th>Due</th>
                                            <th>Emails</th>
                                            <th>Recipients</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rows as $reminder)
                                            @php
                                                $job = $reminder->freight_job;
                                                $days = $reminder->daysLeft();
                                                $recipients = $service->recipients($reminder);
                                                $next = $reminder->nextSendDate();
                                            @endphp
                                            <tr wire:key="job-reminders-row-{{ $reminder->id }}" class="{{ $reminder->isDone() ? 'text-muted' : '' }}">
                                                <td><a href="{{ route('freight.jobs.show', $job->id) }}#reminders">{{ $job->job_number }}</a>
                                                    @if ($job->customer_reference)<br><small class="text-muted">{{ $job->customer_reference }}</small>@endif
                                                </td>
                                                <td>{{ $job->customer_label }}</td>
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
                                                    @if ($next)<br><small class="text-muted">Next: {{ $next->isToday() ? 'today' : $next->format('d M Y') }}</small>@endif
                                                    @if ($reminder->last_sent_at)<br><small class="text-muted">Last sent {{ $reminder->last_sent_at->format('d M Y H:i') }}</small>@endif
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
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            <li><a href="{{ route('freight.jobs.show', $job->id) }}#reminders"><i class="fa fa-eye color-default"></i> View / Edit on Job</a></li>
                                                            @if ($reminder->isDone())
                                                                <li><a href="#" wire:click.prevent="reopen({{ $reminder->id }})"><i class="fa fa-undo color-default"></i> Reopen</a></li>
                                                            @else
                                                                <li><a href="#" wire:click.prevent="markDone({{ $reminder->id }})"><i class="fa fa-check color-success"></i> Mark as Done</a></li>
                                                                <li><a href="#" wire:click.prevent="sendNow({{ $reminder->id }})" wire:confirm="Email this reminder to all its recipients now?"><i class="fa fa-paper-plane color-primary"></i> Send Now</a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div style="text-align:center; color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Job Reminders Found ....
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @include('livewire.freight.partials.pagination', ['paginator' => $rows])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
