<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title">
            <a href="#" data-toggle="modal" data-target="#addHorseModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Horse</a>
        </div>
    </div>
    <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

        <table id="broker_horsesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <tr>
                <th class="th-sm">Horse#
                </th>
                <th class="th-sm">Fleet#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Make
                </th>
                <th class="th-sm">HRN
                </th>
                <th class="th-sm">Mileage
                </th>
                <th class="th-sm"><i class="fa fa-arrow-right" aria-hidden="true"></i> Service
                </th>
                <th class="th-sm">Fitness
                </th>
                <th class="th-sm">Availability
                </th>
               
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            @if (isset($broker_horses))
            @if ($broker_horses->count()>0)
            <tbody>
                @foreach ($broker_horses as $horse)
              <tr>
               
                <td>{{$horse->horse_number}}</td>
                <td>{{$horse->fleet_number}}</td>
                <td>{{$horse->transporter ? $horse->transporter->name : ""}}</td>
                <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}}</td>
                <td>{{ucfirst($horse->registration_number)}}</td>
                <td>{{$horse->mileage ? $horse->mileage."Kms" : ""}}</td>
                <td>{{$horse->next_service ? $horse->next_service."Kms" : ""}}</td>
                  <td><span class="badge bg-{{$horse->service == 0 ? "success" : "danger"}}">{{$horse->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                <td><span class="badge bg-{{$horse->status == 1 ? "success" : "danger"}}">{{$horse->status == 1 ? "Available" : "Unavailable"}}</span></td>
              
              
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{route('horses.show', $horse->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                        </ul>
                    </div>
                    @include('horses.delete')

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
                   <center> <strong>Are you sure you want to remove this Horse from Broker`s Horse List? </strong> </center>
                </div>
                <form wire:submit.prevent="removeHorse()" >
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addHorseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="horse">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Horse(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div style="height: 400px; overflow: auto">
                        <div class="form-group">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%"  style="height: 400px">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Add Horse<span class="required" style="color: red">*</span></th>
                                  </tr>
                                </thead>
                                @if ($horses->count()>0)
                                <tbody>
                                    @foreach ($horses as $horse)
                                  <tr>
                                    <td>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="horse_id.{{$horse->id}}" wire:key="{{ $horse->id }}" value="{{ $horse->id }}" class="line-style"  />
                                            <label for="one" class="radio-label">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} </label>
                                            @error('horse.'.$horse->id) <span class="text-danger error">{{ $message }}</span>@enderror
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
