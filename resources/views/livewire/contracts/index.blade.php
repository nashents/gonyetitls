<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#contractModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Contract</a>
        <br>
        <br>
        <table id="contractsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Start Date
                </th>
                <th class="th-sm">End Date
                </th>
                <th class="th-sm">Duration
                </th>
                <th class="th-sm">Comments
                </th>
                <th class="th-sm">Status
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($contracts))
                @if ($contracts->count() > 0)
                @foreach ($contracts as $contract)
              <tr>
                <td>{{$contract->start_date}}</td>
                <td>{{$contract->end_date}}</td>
                <td>{{$contract->duration ? $contract->duration." Months" : ""}}</td>
                <td>{{$contract->comments}}</td>
                <td><span class="badge bg-{{$contract->status == 1 ? "success" : "danger"}}">{{$contract->status == 1 ? "Active" : "Expired"}}</span></td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="edit({{$contract->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#contractDeleteModal{{$contract->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('contracts.delete')

            </td>
            </tr>
              @endforeach
              @else
              <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
              @endif
              @else
              {{-- <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt=""> --}}
              @endif
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="contractModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="contract">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Contract <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">End Date</label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="end_date" placeholder="Enter End Date" >
                                @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Duration(Months)</label>
                                <input type="number" step="any" min="1" class="form-control"  wire:model.debounce.300ms="duration" placeholder="Enter Contract Duration" >
                                @error('duration') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title">Comments</label>
                        <textarea class="form-control"  wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="contractEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="contract">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Contract <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">End Date</label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="end_date" placeholder="Enter End Date" >
                                @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Duration(Months)</label>
                                <input type="number" step="any" min="1" class="form-control"  wire:model.debounce.300ms="duration" placeholder="Enter Contract Duration" >
                                @error('duration') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Status</label>
                               <select class="form-control"  wire:model.debounce.300ms="status" >
                                <option value="">Select Option</option>
                                <option value="1">Active</option>
                                <option value="0">Expired</option>
                               </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Comments</label>
                                <textarea class="form-control"  wire:model.debounce.300ms="comments" cols="30" rows="4"></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
   
    @section('extra-js')
    <script>
    $(document).ready( function () {
        $('#contractsTable').DataTable();
    } );
    </script>
 
@endsection

</div>
