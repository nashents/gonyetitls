<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#documentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Document</a>
        <br>
        <br>
        <table id="documentsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Title
                </th>
                <th class="th-sm">File
                </th>
                <th class="th-sm">Expiry
                </th>
                <th class="th-sm">Status
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @foreach ($documents as $document)
              <tr>
                <td>{{$document->title}}</td>
                <td><a href="{{asset('myfiles/documents/'.$document->filename)}}"> <i class="fa fa-file"></i> {{$document->filename}}</a></td>
                <td>{{$document->expiry_date}}</td>
                <td><span class="badge bg-{{$document->status == 1 ? "success" : "danger"}}">{{$document->status == 1 ? "Valid" : "Expired"}}</span></td>

                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="edit({{$document->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#documentDeleteModal{{$document->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('documents.delete')

            </td>
            </tr>
              @endforeach
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Document(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="title.0" placeholder="Enter Document Title" >
                            @error('title.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="file">Upload File</label>
                            <input type="file" class="form-control"  wire:model.debounce.300ms="file.0" placeholder="Upload File" >
                            @error('file.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" class="form-control"  wire:model.debounce.300ms="expiry_date.0" placeholder="Expiry Date" >
                            @error('expiry_date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                        </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter Document Title" >
                                    @error('title.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Upload File</label>
                                    <input type="file" class="form-control"  wire:model.debounce.300ms="file.{{$value}}" placeholder="Issue Date" >
                                    @error('file.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Expiry Date</label>
                                    <input type="date" class="form-control"  wire:model.debounce.300ms="expiry_date.{{$value}}" placeholder="Expiry Date" >
                                    @error('expiry_date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> File</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Employee Document(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="title" placeholder="Enter Document Title" >
                            @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="file">Upload File</label>
                            <small style="color: red">Selected File: <a href="{{asset('myfiles/documents/'.$filename)}}"><i class="fa fa-file"></i> {{$filename}}</a></small>
                            <input type="file" class="form-control"  wire:model.debounce.300ms="file" placeholder="Upload File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" class="form-control"  wire:model.debounce.300ms="expiry_date" placeholder="Expiry Date" >
                            @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                        </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
