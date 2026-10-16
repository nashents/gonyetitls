{{-- "Assign to horse" checkbox (HandlesTripTrailerLinks::getHorseAssignmentStateProperty). --}}
@php($horseAssignmentState = $this->horseAssignmentState)
@if ($horseAssignmentState === 'can_assign')
    <div class="form-group" title="Saves this driver and these trailers as the horse's active Horse - Driver and Horse - Trailer assignments when the trip is saved. Their other active assignments are ended at this trip's start date and starting mileage, and the next trip for this horse pre-fills them.">
        <input type="checkbox" id="assign_to_horse" wire:model="assign_to_horse" class="line-style" />
        <label for="assign_to_horse" class="radio-label">Assign driver &amp; trailers to this horse <i class="fa fa-info-circle text-muted"></i></label>
    </div>
@elseif ($horseAssignmentState === 'assigned')
    <div class="form-group"><small class="text-success"><i class="fa fa-check"></i> Driver and trailers are already assigned to this horse.</small></div>
@endif
