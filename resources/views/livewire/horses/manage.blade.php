<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                                {{-- <div class="panel-title">
                                    <a href="{{route('horses.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Horse</a>
                                </div> --}}
                                <div class="panel-title" style="float: right">
                                    <a href="{{route('horses.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                    <a href="{{route('horses.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                    <a href="{{route('horses.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                    <a href="" data-toggle="modal" data-target="#horsesImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a>
                                </div>

                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                                <table id="horsesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">CreatedBy
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">Model
                                            </th>
                                            <th class="th-sm">HRN
                                            </th>
                                            <th class="th-sm">Chasis#
                                            </th>
                                            <th class="th-sm">Engine#
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
                                    @if ($horses->count()>0)
                                    <tbody>
                                        @foreach ($horses as $horse)
                                        <tr>
                                            <td>{{ucfirst($horse->user ? $horse->user->name : "undefined")}} {{ucfirst($horse->user ? $horse->user->surname : "undefined")}}</td>
                                            <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "undefined")}}</td>
                                            <td>{{ucfirst($horse->horse_model ? $horse->horse_model->name : "undefined")}}</td>
                                            <td>{{ucfirst($horse->registration_number)}}</td>
                                            <td>{{$horse->chasis_number}}</td>
                                            <td>{{$horse->engine_number}}</td>
                                            <td>{{$horse->year}}</td>
                                            <td><span class="badge bg-{{$horse->status == 1 ? "success" : "danger"}}">{{$horse->status == 1 ? "Available" : "Unavailable"}}</span></td>
                                            <td><span class="badge bg-{{$horse->service == 0 ? "success" : "danger"}}">{{$horse->status == 0 ? "Fit for use" : "In Service"}}</span></td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="{{route('horses.show', $horse->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                        <li><a href="{{route('horses.edit', $horse->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#horseDeleteModal{{$horse->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                        @if ($horse->status == 1)
                                                        <li><a href="{{route('horses.deactivate', $horse->id)}}"  ><i class="fa fa-toggle-off color-danger"></i>Deactivate</a></li>
                                                        @else
                                                        <li><a href="{{route('horses.activate', $horse->id)}}"  ><i class="fa fa-toggle-on color-success"></i>Activate</a></li>
                                                        @endif

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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="horsesImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import horses <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('horses.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Horse(s) Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload horse File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div><!-- Modal -->


    </div>
