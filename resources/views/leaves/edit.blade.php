<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Leave Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form >
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="name" required disabled>
                            @error('name') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="surname" required disabled>
                            @error('surname') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- /.col-md-6 -->
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Email</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="email" required disabled>
                            @error('name') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="leave_days">Available Leave Days</label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="available_leave_days" disabled >
                            @error('available_leave_days') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- /.col-md-6 -->
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="leave_type">Leave Type</label>
                          <select wire:model.debounce.300ms="leave_type_id" class="form-control" required>
                              <option value="" selected>Select Leave Type</option>
                              @foreach ($leave_types as $leave_type)
                                  <option value="{{$leave_type->id}}">{{$leave_type->name}}</option>
                              @endforeach
                          </select>
                            @error('leave_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    
                    <!-- /.col-md-6 -->
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Start Leave Date</label>
                            <input type="date" class="form-control" wire:model.debounce.300ms="from" required>
                            @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="to">End Leave Date</label>
                            <input type="date" class="form-control" wire:model.debounce.300ms="to" required >
                            @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- /.col-md-6 -->
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Reason for leave</label>
                           <textarea class="form-control" wire:model.debounce.300ms="reason"  cols="30" rows="5" required></textarea>
                            @error('reason') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                  
                    <!-- /.col-md-6 -->
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button wire:click.prevent="update()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>