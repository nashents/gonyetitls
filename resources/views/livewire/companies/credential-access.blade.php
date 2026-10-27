<div>
    <style>
        .credential-access-section { border: 1px solid #e3e3e3; border-radius: 6px; margin-top: 20px; background: #fff; }
        .credential-access-section .ca-header { display: flex; align-items: flex-start; gap: 12px; padding: 15px 18px; border-bottom: 1px solid #eee; background: #fafafa; border-radius: 6px 6px 0 0; }
        .credential-access-section .ca-icon { flex: 0 0 38px; height: 38px; border-radius: 50%; background: #fdecea; color: #c0392b; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .credential-access-section .ca-header h4 { margin: 0 0 4px 0; font-weight: 600; }
        .credential-access-section .ca-header p { margin: 0; color: #777; font-size: 13px; line-height: 1.5; }
        .credential-access-section .ca-body { padding: 15px 18px; }
        .credential-access-section .ca-label { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #888; margin-bottom: 8px; display: block; }
        .credential-access-section .ca-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 15px; min-height: 30px; }
        .credential-access-section .ca-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 15px; background: #e8f4fd; color: #1f6fa8; font-size: 13px; }
        .credential-access-section .ca-chip a { color: inherit; opacity: .6; cursor: pointer; }
        .credential-access-section .ca-chip a:hover { opacity: 1; text-decoration: none; }
        .credential-access-section .ca-list { max-height: 260px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 6px 10px; }
        .credential-access-section .ca-list label { display: block; font-weight: normal; padding: 5px 0; margin: 0; border-bottom: 1px dashed #f0f0f0; cursor: pointer; }
        .credential-access-section .ca-list label:last-child { border-bottom: 0; }
        .credential-access-section .ca-footer { padding: 12px 18px; border-top: 1px solid #eee; text-align: right; }
    </style>

    <form wire:submit.prevent="update()">
        <div class="credential-access-section">
            <div class="ca-header">
                <div class="ca-icon"><i class="fa fa-key"></i></div>
                <div>
                    <h4>Credentials Access</h4>
                    <p>
                        Choose who can see employee PINs and use <strong>Send via Email</strong>, <strong>Send via WhatsApp</strong>
                        and <strong>Bulk Send Credentials</strong> on Employees and Drivers.
                        Every send is logged, and the email shows a "Shared by" line.
                    </p>
                </div>
            </div>

            <div class="ca-body">
                <span class="ca-label">Allowed to share ({{ $selected_users->count() }})</span>
                <div class="ca-chips">
                    @forelse ($selected_users as $selected)
                        <span class="ca-chip" wire:key="ca-chip-{{ $selected->id }}">
                            {{ ucfirst($selected->name) }} {{ ucfirst($selected->surname) }}
                            <a wire:click.prevent="removeSharer({{ $selected->id }})" title="Remove"><i class="fa fa-times"></i></a>
                        </span>
                    @empty
                        <small class="text-muted">Nobody selected yet.</small>
                    @endforelse
                </div>

                <span class="ca-label">Add people</span>
                <div class="form-group" style="margin-bottom: 8px;">
                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search by name or username">
                </div>
                <div class="ca-list">
                    @forelse ($users as $user)
                        {{-- wire:key and the checkbox id must differ: Livewire matches elements by either --}}
                        <label for="ca-checkbox-{{ $user->id }}" wire:key="ca-row-{{ $user->id }}">
                            <input type="checkbox" id="ca-checkbox-{{ $user->id }}" value="{{ $user->id }}" wire:model="sharer_ids" />
                            {{ ucfirst($user->name) }} {{ ucfirst($user->surname) }}
                            @if ($user->username)
                                <small class="text-muted">({{ $user->username }})</small>
                            @endif
                        </label>
                    @empty
                        <small class="text-muted">No users found.</small>
                    @endforelse
                </div>
            </div>

            <div class="ca-footer">
                <button type="submit" class="btn btn-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save Access</button>
            </div>
        </div>
    </form>
</div>
