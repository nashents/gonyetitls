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
                                <a href="" data-toggle="modal" data-target="#saveModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Problem Category</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search problem categories...">
                                    </div>
                                </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($problem_categories))
                                <tbody>
                                    @forelse ($problem_categories as $problem_category)
                                  <tr>
                                    <td>{{$problem_category->name}}</td>
                                    <td>{{$problem_category->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if (isset($problem_category->user_id))
                                                    <li><a href="#"  wire:click.prevent="edit({{$problem_category->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" wire:click.prevent="delete({{$problem_category->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                      
                                </td>
                                  </tr>
                                   @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Problem Category Found ....
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
                                        @if (isset($problem_categories))
                                            @if ($problem_categories->count()>0)
                                                {{ $problem_categories->links() }} 
                                            @endif
                                        @endif 
                                    </ul>
                                </nav>   

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
 
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="saveModal" taproblem_categorydex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Problem Category <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter problem_category Name">
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="updateModal" taproblem_categorydex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Problem Category <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter problem_category Name">
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Description</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Product Category?</strong> </center> 
            </div>
            <form wire:submit.prevent="destroy" >
            <div class="modal-footer no-border">
                <div class="btn-group" role="group">
                    <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>

</div>

