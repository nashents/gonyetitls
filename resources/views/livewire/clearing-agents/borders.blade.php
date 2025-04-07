<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#addBorderModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Border</a>
        <br>
        <br>
        <table id="clearing_agents_bordersTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Country A
                </th>
                <th class="th-sm">Country B
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if ($clearing_agent_borders->count()>0)
                @foreach ($clearing_agent_borders as $clearing_agent_border)
              <tr>
                <td>{{ucfirst(App\Models\Country::find($clearing_agent_border->country_a)->name)}}</td>
                <td>{{ucfirst(App\Models\Country::find($clearing_agent_border->country_b)->name)}}</td>
                <td>{{ucfirst($clearing_agent_border->name)}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="removeShow({{$clearing_agent->id}},{{ $clearing_agent_border->id }})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                  

            </td>
            </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeBorderModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to remove this border from Clearing Agent`s border List?</strong> </center>
                    </div>
                    <form wire:submit.prevent="removeborder()" >
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addBorderModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-file"></i> Add borders(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">borders</label>
                        <select class="form-control" wire:model.debounce.300ms="border_id.0">
                            <option value="">Select border</option>
                            @foreach ($borders as $border)
                                <option value="{{ $border->id }}">{{ $border->name }}</option>
                            @endforeach
                        </select>
                        @error('border_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="title">borders</label>
                                    <select class="form-control" wire:model.debounce.300ms="border_id.{{ $value }}">
                                        <option value="">Select border</option>
                                        @foreach ($borders as $border)
                                            <option value="{{ $border->id }}">{{ $border->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('border_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> border</button>
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
   
</div>
