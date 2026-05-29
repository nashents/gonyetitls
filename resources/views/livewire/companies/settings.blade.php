<div>
    <form wire:submit.prevent="update()">
        <div class="mb-10" style="margin-top:30px;">
            <input type="checkbox" wire:model.debounce.300ms="enable_requisition_two_step_authorization"   class="line-style" />
            <label for="one" class="radio-label">Enable Requisition Two Step Authorization</label>
            @error('enable_requisition_two_step_authorization') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="btn-group" role="group" style="float: right;">
            <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
        </div>
        <br>
        <hr> 
    </form>
</div>
