<div>
    <form wire:submit.prevent="update()">
        <div class="mb-10" style="margin-top:30px;">
            <input type="checkbox" wire:model.debounce.300ms="enable_requisition_two_step_authorization"   class="line-style" />
            <label for="one" class="radio-label">Enable Requisition Two Step Authorization</label>
            @error('enable_requisition_two_step_authorization') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="mb-10">
            <input type="checkbox" wire:model.debounce.300ms="require_tyre_serial_on_dispatch" class="line-style" />
            <label for="one" class="radio-label">Require Tyre Serial Numbers on Dispatch</label>
            <br><small class="form-text text-muted">When on, a tyre with no serial number recorded cannot be dispatched until one is entered. When off, the serial can be left blank on dispatch and added later.</small>
        </div>
        <div class="mb-10 form-group">
            <label for="fleet_identifier_preference">Fleet Identifier</label>
            <select wire:model.debounce.300ms="fleet_identifier_preference" id="fleet_identifier_preference" class="form-control">
                <option value="registration_number">Vehicle Registration Number (VRN)</option>
                <option value="fleet_number">Fleet Number</option>
            </select>
            <small class="form-text text-muted">Controls how horses, trailers and vehicles are labelled and sorted throughout the system.</small>
            @error('fleet_identifier_preference') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="mb-10 form-group">
            <label for="default_booking_transaction_type">Default Booking Transaction Type</label>
            <select wire:model.debounce.300ms="default_booking_transaction_type" id="default_booking_transaction_type" class="form-control">
                <option value="expense">Expense/Internal Job</option>
                <option value="income">Income/External Job</option>
            </select>
            <small class="form-text text-muted">The transaction type pre-selected when a new booking is created.</small>
            @error('default_booking_transaction_type') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="mb-10 form-group">
            <label for="transporter_expense_recovery">Transporter Trip Expenses Recovery</label>
            <select wire:model.debounce.300ms="transporter_expense_recovery" id="transporter_expense_recovery" class="form-control">
                <option value="deduct">Deduct from the transporter payment bill</option>
                <option value="invoice">Invoice the transporter separately</option>
            </select>
            <small class="form-text text-muted">Trip expenses with category Transporter that the company paid are posted to Transporter Recoverable, not company expenses. Choose how they are recovered: automatically taken off the trip's transporter payment bill, or raised as an invoice to the transporter ("Invoice to: Transporter"). Expenses the transporter's own driver paid (Funded By: Transporter) are only recorded - the transporter is paid in full.</small>
            @error('transporter_expense_recovery') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-10 form-group">
                    <label for="transporter_fuel_recovery">Transporter Fuel Recovery</label>
                    <select wire:model="transporter_fuel_recovery" id="transporter_fuel_recovery" class="form-control">
                        <option value="cost">At cost</option>
                        <option value="markup">Marked up</option>
                    </select>
                    <small class="form-text text-muted">Fuel orders for Transporter-category (subcontracted) trips post to Transporter Recoverable and are recovered with the trip's other transporter expenses. Marked up: the transporter is charged cost plus the markup below, and the markup is booked to Fuel Margin (income). Applies to fuel orders posted or edited after the change.</small>
                </div>
            </div>
            <div class="col-md-4">
                @if ($transporter_fuel_recovery === 'markup')
                <div class="mb-10 form-group">
                    <label for="transporter_fuel_markup">Markup %</label>
                    <input type="number" step="any" min="0" wire:model.debounce.300ms="transporter_fuel_markup" id="transporter_fuel_markup" class="form-control" placeholder="e.g. 10">
                    @error('transporter_fuel_markup') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
                @endif
            </div>
        </div>
        <hr>
        <h5 style="display:flex; justify-content:space-between; align-items:center;">
            <span><i class="fas fa-percentage"></i> Loss Allowances &amp; Chargeable Rates</span>
            <a href="{{ route('loss_rates.index') }}" class="btn btn-default btn-sm btn-rounded"><i class="fa fa-table"></i> Loss Rates Table</a>
        </h5>
        {{-- Same look and wording as the legend on the Loss Rates page. --}}
        <style>
            .lr-legend { background: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 4px; padding: 8px 12px; margin-bottom: 15px; font-size: 13px; color: #333; }
            .lr-legend .lr-order { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 6px; }
            .lr-legend .lr-what { min-width: 90px; font-weight: 600; }
            .lr-legend .lr-step { background: #fff; border: 1px solid #ccc; border-radius: 3px; padding: 2px 8px; font-weight: 600; }
            .lr-legend .lr-step.lr-here { background: #e8f4fd; border-color: #5bc0de; }
            .lr-legend .lr-arrow { color: #888; }
        </style>
        <div class="lr-legend">
            <div>A trip uses the first value set. The defaults on this page are the last step.
                Set customer + cargo allowances and per-cargo rates in the <a href="{{ route('loss_rates.index') }}"><strong>Loss Rates Table</strong></a>.</div>
            <div class="lr-order">
                <span class="lr-what">Allowable %:</span>
                <span class="lr-step">Customer + Cargo cell</span><span class="lr-arrow">&rarr;</span>
                <span class="lr-step">Customer's All cargos</span><span class="lr-arrow">&rarr;</span>
                <span class="lr-step">Cargo default</span><span class="lr-arrow">&rarr;</span>
                <span class="lr-step lr-here">Business default (below)</span>
            </div>
            <div class="lr-order">
                <span class="lr-what">Rate:</span>
                <span class="lr-step">Cargo Own Fleet / Subcontractor rate</span><span class="lr-arrow">&rarr;</span>
                <span class="lr-step lr-here">Business default (below)</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-10 form-group">
                    <label for="allowable_loss_percentage">Default Allowable Loss %</label>
                    <input type="number" step="any" min="0" max="100" wire:model.debounce.300ms="allowable_loss_percentage" id="allowable_loss_percentage" class="form-control" placeholder="e.g. 0.3 (= 0.003)">
                    <small class="form-text text-muted">
                        Entered as a <strong>percent</strong>: 0.3 means 0.3% = 0.003 of the load.
                        @if (is_numeric($allowable_loss_percentage))
                            <strong>Currently: {{ \App\Services\LossRateService::formatPct($allowable_loss_percentage) }}</strong>
                        @endif
                    </small>
                    @error('allowable_loss_percentage') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label><br>
                <button type="button" class="btn btn-default btn-rounded" wire:click="applyToCustomers('allowable_loss_percentage')"
                    onclick="confirm('Set this allowable loss % on EVERY customer (all cargos), replacing their current values? Customer + cargo agreements are kept.') || event.stopImmediatePropagation()">
                    <i class="fa fa-users"></i> Apply to All Customers
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-10 form-group">
                    <label for="own_fleet_chargeable_loss_rate">Default Rate &ndash; Own Fleet ({{ $ownFleetName }})</label>
                    <input type="number" step="any" min="0" wire:model.debounce.300ms="own_fleet_chargeable_loss_rate" id="own_fleet_chargeable_loss_rate" class="form-control" placeholder="per ton / litre, e.g. 1.50">
                    @error('own_fleet_chargeable_loss_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-10 form-group">
                    <label for="subcontractor_chargeable_loss_rate">Default Rate &ndash; Subcontractors</label>
                    <input type="number" step="any" min="0" wire:model.debounce.300ms="subcontractor_chargeable_loss_rate" id="subcontractor_chargeable_loss_rate" class="form-control" placeholder="per ton / litre, e.g. 1.60">
                    @error('subcontractor_chargeable_loss_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-12">
                <small class="form-text text-muted">Chargeable Loss Amount = chargeable loss &times; rate, in the trip's currency. Used when the trip's cargo has no rate of its own. Own fleet = the Default Transporter; all other transporters share the subcontractor rate.</small>
            </div>
        </div>
        <div class="btn-group" role="group" style="float: right;">
            <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
        </div>
        <br>
        <hr> 
    </form>
</div>
