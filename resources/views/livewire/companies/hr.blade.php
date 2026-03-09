<div>
    <form wire:submit.prevent="update()">
            <small style="color: red">Every update you make will be effected to all employees!!.</small>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vat">Leave Accrual Rate</label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="accrual_rate" placeholder="Accural Rate For All Employees" >
                        @error('accrual_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vat">Maximum Leave Days</label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="maximum_leave_days" placeholder="Maximum leave days for all employees" >
                        @error('maximum_leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vat">Days Calculation<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="days_calculation" required>
                            <option value="">Select Option</option>
                            <option value="include_weekends">Include Weekends</option>
                            <option value="exclude_weekends">Exclude Weekends</option>
                            <option value="one_weekend_day">1 Weekend Day</option>
                        </select>
                        @error('days_calculation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
         
            <div class="btn-group" role="group" style="float: right;">
                <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
            </div>
            <br>
            <hr>
            
           
    </form>
</div>
