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
                                <a href="" data-toggle="modal" data-target="#service_typeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Job Type</a>
                            </div>
                            
                        </div>
                       
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <small style="color: green">To add items to any job type checklist click view under actions.</small>
                                 <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search job types...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Inspection Item(s)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($service_types))
                                <tbody>
                                    @forelse ($service_types as $service_type)
                                  <tr>
                                    <td>{{$service_type->name}}</td>
                                    <td>
                                         @php
                                            // Group items by sub-category name (null becomes 'no-category')
                                            $grouped = $service_type->inspection_services
                                                ->groupBy(function ($item) {
                                                    return $item->inspection_group->name ?? 'no-category';
                                                });

                                            // Separate the "no-category" group
                                            $noCategoryItems = $grouped->pull('no-category');
                                        @endphp
                                      <ul style="list-style-type: none; padding-left: 0; margin: 0;">
                                            {{-- Sub-category groups --}}
                                            @foreach ($grouped as $subCategory => $items)
                                                <li style="margin-bottom: 0.75rem;">
                                                    <strong style="display: block; font-weight: 600; margin-bottom: 0.25rem;">
                                                        {{ $subCategory }}
                                                    </strong>

                                                    <ol style="margin-left: 1.5rem; padding-left: 1rem; margin-top: 0.25rem;">
                                                        @foreach ($items as $item)
                                                            <li style="margin-bottom: 0.25rem;">
                                                                {{ $item->inspection_type->name ?? '' }}
                                                            </li>
                                                        @endforeach
                                                    </ol>
                                                </li>
                                            @endforeach

                                            {{-- Uncategorized items --}}
                                            @if ($noCategoryItems && $noCategoryItems->count())
                                                <li style="margin-top: 1rem;">
                                                    <strong style="display: block; font-weight: 600; margin-bottom: 0.25rem;">
                                                        Ungrouped
                                                    </strong>

                                                    <ol style="margin-left: 1.5rem; padding-left: 1rem; margin-top: 0.25rem;">
                                                        @foreach ($noCategoryItems as $item)
                                                            <li style="margin-bottom: 0.25rem;">
                                                                {{ $item->inspection_type->name ?? '' }}
                                                            </li>
                                                        @endforeach
                                                    </ol>
                                                </li>
                                            @endif
                                        </ul>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('service_types.show',$service_type->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($service_type->name != "Tyre Inspection")
                                                     <li><a href="#" data-toggle="modal" data-target="#service_typeEditModal" wire:click="edit({{$service_type->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#service_typeDeleteModal{{ $service_type->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                               
                                            </ul>
                                        </div>
                                        @include('service_types.delete')
                                </td>
                                  </tr>
                                    @empty
                                  <tr>
                                    <td colspan="3">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Job Types Found ....
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
                                    @if (isset($service_types))
                                        {{ $service_types->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="service_typeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Job Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Service Type"  required >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="service_typeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Job Type <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                <input type="hidden" wire:model="service_type_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
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

