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
                                <a href="#" data-toggle="modal" data-target="#trailer_linkModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trailer Link</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="trailer_linksTable" class="table table-striped table-trailer_linked table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Trailer A
                                    </th>
                                    <th class="th-sm">Trailer B
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($trailer_links->count()>0)
                                <tbody>
                                    @foreach ($trailer_links as $trailer_link)
                                  <tr>
                                    <td>{{$trailer_link->transporter ? $trailer_link->transporter->name : ""}}</td>
                                    <td>{{ucfirst(App\Models\Trailer::find($trailer_link->trailer_a)->registration_number)}}</td>
                                    <td>{{ucfirst(App\Models\Trailer::find($trailer_link->trailer_b)->registration_number)}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$trailer_link->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#trailer_linkDeleteModal{{ $trailer_link->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('trailer_links.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailer_linkModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Trailer Link <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Transporters<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required>
                            <option value="">Select Transporter</option>
                            @foreach ($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                @php
                                $trailer_links = App\Models\TrailerLink::latest()->get();
                                foreach ($trailer_links as $trailer_link) {
                                    $trailer_a_ids[] = $trailer_link->trailer_a;
                                    $trailer_b_ids[] = $trailer_link->trailer_b;
                                }
                            @endphp
                                <label for="from">Trailer A<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="trailer_a" class="form-control" required >
                                   <option value="">Select Trailer A</option>
                                   @if (!is_null($selectedTransporter))
                                   @foreach ($trailers as $trailer)
                                        @if (isset($trailer_a_ids) && isset($trailer_b_ids))
                                            @if (in_array($trailer->id, $trailer_a_ids ) || in_array($trailer->id, $trailer_b_ids ))

                                            @else
                                            <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                            @endif
                                        @else
                                            <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                        @endif
                                   @endforeach
                                   @endif
                               </select>
                                @error('trailer_a') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">Trailer B<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="trailer_b" class="form-control" required >
                                   <option value="">Select Trailer</option>
                                   @if (!is_null($selectedTransporter))
                                   @foreach ($trailers as $trailer)
                                        @if (isset($trailer_a_ids) && isset($trailer_b_ids))
                                            @if (in_array($trailer->id, $trailer_a_ids ) || in_array($trailer->id, $trailer_b_ids ))

                                            @else
                                            <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                            @endif
                                        @else
                                            <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                        @endif
                                   @endforeach
                                   @endif
                               </select>
                                @error('trailer_b') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trailer_linkEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Trailer Link <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Transporters<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required>
                                <option value="">Select Transporter</option>
                                @foreach ($transporters as $transprter)
                                    <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    @php
                                    $trailer_links = App\Models\TrailerLink::latest()->get();
                                    foreach ($trailer_links as $trailer_link) {
                                        $trailer_a_ids[] = $trailer_link->trailer_a;
                                        $trailer_b_ids[] = $trailer_link->trailer_b;
                                    }
                                @endphp
                                    <label for="from">Trailer A<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="trailer_a" class="form-control" required >
                                       <option value="">Select Trailer A</option>
                                       @if (!is_null($selectedTransporter))
                                       @foreach ($trailers as $trailer)
                                            @if (isset($trailer_a_ids) && isset($trailer_b_ids))
                                                @if (in_array($trailer->id, $trailer_a_ids ) || in_array($trailer->id, $trailer_b_ids ))
    
                                                @else
                                                <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                            @endif
                                       @endforeach
                                       @endif
                                   </select>
                                    @error('trailer_a') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from">Trailer B<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="trailer_b" class="form-control" required >
                                       <option value="">Select Trailer</option>
                                       @if (!is_null($selectedTransporter))
                                       @foreach ($trailers as $trailer)
                                            @if (isset($trailer_a_ids) && isset($trailer_b_ids))
                                                @if (in_array($trailer->id, $trailer_a_ids ) || in_array($trailer->id, $trailer_b_ids ))
    
                                                @else
                                                <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $trailer->id }}">{{ $trailer->registration_number }}</option>
                                            @endif
                                       @endforeach
                                       @endif
                                   </select>
                                    @error('trailer_b') <span class="error" style="color:red">{{ $message }}</span> @enderror
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



</div>

