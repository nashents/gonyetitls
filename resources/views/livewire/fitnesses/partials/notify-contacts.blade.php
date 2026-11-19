{{-- Customer / vendor reminders: also email that party's contacts. Expects $recipients (email => name) and $partyLabel ("customer"/"vendor"). --}}
<div class="notify-contacts" style="margin-top:8px">
    <input type="checkbox" wire:model.defer="notify_contacts" class="line-style" />
    <label class="radio-label">Also email the {{ $partyLabel }}'s contacts</label>
    <small class="text-muted d-block" style="margin-left:22px">
        @if(count($recipients))
            They get a courtesy reminder ({{ count($recipients) }} address{{ count($recipients) == 1 ? '' : 'es' }}: {{ implode(', ', array_keys($recipients)) }}), without any internal link or copy list. Replies come to you.
        @else
            <span class="text-warning"><i class="fa fa-exclamation-triangle"></i> No contact email on file for this {{ $partyLabel }} — add one under its Contacts tab.</span>
        @endif
    </small>
</div>
