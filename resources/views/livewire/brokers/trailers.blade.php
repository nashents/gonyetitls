<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title">
            <a href="#" data-toggle="modal" data-target="#addTrailerModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trailer</a>
        </div>
    </div>
    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

        <table id="broker_trailersTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <tr>
                <th class="th-sm">Trailer#
                </th>
                <th class="th-sm">Fleet#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Make
                </th>
                <th class="th-sm">Model
                </th>
                <th class="th-sm">TRN
                </th>
                <th class="th-sm">Year
                </th>
                <th class="th-sm">Availability
                </th>
                <th class="th-sm">Service
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            @if (isset($broker_trailers))
            @if ($broker_trailers->count()>0)
            <tbody>
                @foreach ($broker_trailers as $trailer)
              <tr>
                <td>{{$trailer->trailer_number}}</td>
                <td>{{$trailer->fleet_number}}</td>
                <td>{{$trailer->transporter ? $trailer->transporter->name : ""}}</td>
                <td>{{$trailer->make}}</td>
                <td>{{$trailer->model}}</td>
                <td>{{$trailer->registration_number}}</td>
                <td>{{$trailer->year}}</td>
                <td><span class="badge bg-{{$trailer->status == 1 ? "success" : "danger"}}">{{$trailer->status == 1 ? "Available" : "Unavailable"}}</span></td>
                <td><span class="badge bg-{{$trailer->service == 0 ? "success" : "danger"}}">{{$trailer->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{route('trailers.show', $trailer->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                        </ul>
                    </div>
                    @include('trailers.delete')

            </td>
              </tr>
              @endforeach
            </tbody>
            @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
            @endif
            @endif

          </table>

        <!-- /.col-md-12 -->
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to remove this Trailer from Broker`s Trailer List? </strong> </center>
                </div>
                <form wire:submit.prevent="removeTrailer()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addTrailerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="trailer">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Trailer(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div style="height: 400px; overflow: auto">
                        <div class="form-group">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Add Trailer<span class="required" style="color: red">*</span></th>
                                  </tr>
                                </thead>
                                @if ($trailers->count()>0)
                                <tbody>
                                    @foreach ($trailers as $trailer)
                                  <tr>
                                    <td>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="trailer_id.{{$trailer->id}}" wire:key="{{ $trailer->id }}" value="{{ $trailer->id }}" class="line-style"  />
                                            <label for="one" class="radio-label">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </label>
                                            @error('trailer_id.'.$trailer->id) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @endif
                              </table>  
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

</div>
