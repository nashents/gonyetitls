<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>

                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#lossModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Loss Cause</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="lossesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Loss Cause Category
                                    </th>
                                    <th class="th-sm">Loss Cause Group
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($losses->count()>0)
                                <tbody>
                                    @foreach ($losses as $loss)
                                  <tr>
                                    <td>{{$loss->loss_category ? $loss->loss_category->name : ""}}</td>
                                    <td>{{$loss->loss_group ? $loss->loss_group->name : ""}}</td>
                                    <td>{{$loss->name}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$loss->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#lossDeleteModal{{ $loss->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('losses.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="lossModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Loss Cause <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Loss Cause Categories<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="selectedLossCategory" required>
                                <option value="">Select Loss Cause Category</option>
                                @foreach ($loss_categories as $loss_category)
                                   <option value="{{$loss_category->id}}">{{$loss_category->name}}</option> 
                                @endforeach
                               </select>
                               <small>  <a href="{{ route('loss_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loss Cause Category</a></small> 
                                @error('selectedLossCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Loss Cause Groups</label>
                               <select class="form-control" wire:model.debounce.300ms="loss_group_id">
                                <option value="">Select Loss Cause Group</option>
                                @foreach ($loss_groups as $loss_group)
                                   <option value="{{$loss_group->id}}">{{$loss_group->name}}</option> 
                                @endforeach
                               </select>
                               <small>  <a href="{{ route('loss_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loss Cause Group</a></small> 
                                @error('loss_group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
               
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="lossEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Loss Cause <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Loss Cause Categories<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="selectedLossCategory" required>
                                <option value="">Select Loss Cause Category</option>
                                @foreach ($loss_categories as $loss_category)
                                   <option value="{{$loss_category->id}}">{{$loss_category->name}}</option> 
                                @endforeach
                               </select>
                                @error('selectedLossCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Loss Cause Groups</label>
                               <select class="form-control" wire:model.debounce.300ms="loss_group_id">
                                <option value="">Select Loss Cause Group</option>
                                @foreach ($loss_groups as $loss_group)
                                   <option value="{{$loss_group->id}}">{{$loss_group->name}}</option> 
                                @endforeach
                               </select>
                                @error('loss_group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
               
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

