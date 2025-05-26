<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                           
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#leaveTypeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Leave Type</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="leave_typesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Entitlement
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($leave_types->count()>0)
                                <tbody>
                                    @foreach ($leave_types as $leave_type)
                                  <tr>
                                    <td>{{$leave_type->name}}</td>
                                    <td>{{$leave_type->entitlement ? $leave_type->entitlement." Days" : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" data-toggle="modal" data-target="#leaveTypeEditModal" wire:click="edit({{$leave_type->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#leaveTypeDeleteModal{{ $leave_type->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('leave_types.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveTypeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Leave Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter title"  required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Entitlement</label>
                                <input type="number" min="0" class="form-control" wire:model.debounce.300ms="entitlement" placeholder="Enter Leave Type Entitlement / Year" >
                                @error('entitlement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="store()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Create</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="leaveTypeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Leave Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <input type="hidden" wire:model="leave_type_id">
                <div class="modal-body">
                  <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter title"  required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Entitlement</label>
                                <input type="number" min="0" class="form-control" wire:model.debounce.300ms="entitlement" placeholder="Enter Leave Type Entitlement / Year" >
                                @error('entitlement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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



</div>

