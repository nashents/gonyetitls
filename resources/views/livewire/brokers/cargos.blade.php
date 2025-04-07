<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#addCargoModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Cargo</a>
        <br>
        <br>
        <table id="broker_cargosTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Type
                </th>
                <th class="th-sm">Group
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Measurement
                </th>
                <th class="th-sm">Risk Level
                </th>
                <th class="th-sm">Action
                </th>
              </tr>
            </thead>
            @if ($broker_cargos->count()>0)
            <tbody>
                @foreach ($broker_cargos as $broker_cargo)
              <tr>
                <td>{{ucfirst($broker_cargo->type)}}</td>
                <td>{{ucfirst($broker_cargo->group)}}</td>
                <td>{{ucfirst($broker_cargo->name)}}</td>
                <td>{{ucfirst($broker_cargo->measurement)}}</td>
                <td>{{ucfirst($broker_cargo->risk)}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="removeShow({{$broker->id}},{{ $broker_cargo->id }})"><i class="fa fa-trash color-danger"></i>Remove</a></li>
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

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to remove this Cargo from Broker`s Cargo List? </strong> </center>
                    </div>
                    <form wire:submit.prevent="removeCargo()" >
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
        

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addCargoModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="cargo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Cargo(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div style="height: 400px; overflow: auto">
                            <div class="form-group">
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                    <thead>
                                      <tr>
                                        <th class="th-sm">Add Broker Cargos<span class="required" style="color: red">*</span></th>
                                      </tr>
                                    </thead>
                                    @if ($cargos->count()>0)
                                    <tbody>
                                        @foreach ($cargos as $cargo)
                                      <tr>
                                        <td>
                                            <div class="mb-10">
                                                <input type="checkbox" wire:model.debounce.300ms="cargo_id.{{$cargo->id}}" wire:key="{{ $cargo->id }}" value="{{ $cargo->id }}" class="line-style"  />
                                                <label for="one" class="radio-label">{{$cargo->name}} </label>
                                                @error('cargo_id.'.$cargo->id) <span class="text-danger error">{{ $message }}</span>@enderror
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
        @section('extra-js')
            <script>
                $(document).ready( function () {
                    $('#broker_cargosTable').DataTable();
                } );
            </script>
         @endsection
</div>
