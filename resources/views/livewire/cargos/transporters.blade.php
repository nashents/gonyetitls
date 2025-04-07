<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#addTransporterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transporter</a>
        <br>
        <br>
        <table id="cargo_transportersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Transporter#
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Email
                </th>
                <th class="th-sm">Phonenumber
                </th>
                <th class="th-sm">Address
                </th>
                <th class="th-sm">Action
                </th>
              </tr>
            </thead>
            @if ($cargo_transporters->count()>0)
            <tbody>
                @foreach ($cargo_transporters as $cargo_transporter)
              <tr>
                <td>{{ucfirst($cargo_transporter->transporter_number)}}</td>
                <td>{{ucfirst($cargo_transporter->name)}}</td>
                <td>{{ucfirst($cargo_transporter->email)}}</td>
                <td>{{ucfirst($cargo_transporter->phonenumber)}}</td>
                <td>{{$cargo_transporter->street_address}} {{$cargo_transporter->suburb}} {{$cargo_transporter->city}} {{$cargo_transporter->country}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="removeShow({{$cargo->id}},{{ $cargo_transporter->id }})"><i class="fa fa-trash color-danger"></i>Remove</a></li>
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
                       <center> <strong>Are you sure you want to remove this Transporter from Cargo`s Transporter List? </strong> </center>
                    </div>
                    <form wire:submit.prevent="removeTransporter()" >
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
        

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addTransporterModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="transporter">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Transporter(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div style="height: 400px; overflow: auto">
                            <div class="form-group">
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                    <thead>
                                      <tr>
                                        <th class="th-sm">Add Transporters<span class="required" style="color: red">*</span></th>
                                      </tr>
                                    </thead>
                                    @if ($transporters->count()>0)
                                    <tbody>
                                        @foreach ($transporters as $transporter)
                                      <tr>
                                        <td>
                                            <div class="mb-10">
                                                <input type="checkbox" wire:model.debounce.300ms="transporter_id.{{$transporter->id}}" wire:key="{{ $transporter->id }}" value="{{ $transporter->id }}" class="line-style"  />
                                                <label for="one" class="radio-label">{{$transporter->name}} </label>
                                                @error('transporter_id.'.$transporter->id) <span class="text-danger error">{{ $message }}</span>@enderror
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
