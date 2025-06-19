<div>
    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th class="th-sm">Start Time
            </th>
            <th class="th-sm">Open Hours
            </th>
            <th class="th-sm">Open Mileage
            </th>
            <th class="th-sm">Work Description & Location
            </th>
            <th class="th-sm">Stop Time
            </th>
            <th class="th-sm">Close Hours
            </th>
            <th class="th-sm">Close Mileage
            </th>
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if (isset($rehandlings))
        <tbody>
            @forelse ($rehandlings as $rehandling)
          <tr>
            <td>{{$rehandling->start_time}}</td>
            <td>{{$rehandling->open_hours ? $rehandling->open_hours : " Hours"}}</td>
            <td>{{$rehandling->open_mileage ? $rehandling->open_mileage." Kms" : ""}}</td>
            <td>
                {{$rehandling->work ? $rehandling->work->description : ""}}
                @if ($rehandling->location)
                    @ {{$rehandling->location ? $rehandling->location->name : ""}}
                @endif
            </td>
              <td>{{$rehandling->stop_time}}</td>
              <td>{{$rehandling->close_hours ? $rehandling->close_hours." Hours" : ""}}</td>
              <td>{{$rehandling->close_mileage ? $rehandling->close_mileage." Kms" : ""}}</td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" wire:click="edit({{$rehandling->id}})"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                    </ul>
                </div>
                @include('rehandlings.delete')
        </td>
          </tr>
          @empty
          <tr>
            <td colspan="9">
                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                    No rehandling work found ....
                </div>
               
            </td>
          </tr>  
            @endforelse
        </tbody>
        @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
         @endif
      </table>
      <nav class="text-center" style="float: right">
        <ul class="pagination rounded-corners">
            @if (isset($rehandlings))
                {{ $rehandlings->links() }} 
            @endif 
        </ul>
    </nav>     
</div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rehandlingEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-60" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Rehandling Work<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="Update()" >
                <div class="modal-body">
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_time" placeholder="Enter Start Time"/>
                                @error('start_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="open_hours" placeholder="Enter Open Engine Hours"/>
                                @error('open_hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage" placeholder="Enter Open Mileage"/>
                                @error('open_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites</label>
                                <select class="form-control" wire:model.debounce.300ms="location_id">
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="close_time" placeholder="Enter Close Time"/>
                                @error('close_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="close_hours" placeholder="Enter Close Engine Hours"/>
                                @error('close_hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage" placeholder="Enter Close Mileage"/>
                                @error('close_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>