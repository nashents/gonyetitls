<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        @if (Auth::user()->employee)
        @if ($trip->authorization == "approved")
        <a href="" data-toggle="modal" data-target="#documentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Document</a>
        @endif
        <br>
        <br>
        @endif
        <x-loading/>
        <table id="documentsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Document#
                </th>
                <th class="th-sm">Title
                </th>
                <th class="th-sm">File
                </th>
                <th class="th-sm">Submitted On
                </th>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($documents as $document)
              <tr>
                <td>{{$document->document_number}}</td>
                <td>{{$document->title}}</td>
                <td>
                    <a href="{{asset('myfiles/documents/'.$document->filename)}}"> <i class="fa fa-file"></i> {{$document->filename}}</a>
                </td>
                <td>{{$document->date}}</td>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
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
                    @include('trips.documents.delete')

            </td>
            @endif
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Trip Documents Uploaded....
                    </div>
                   
                </td>
              </tr>  
            @endforelse
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Trip Document(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expiry_date">Document #</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="document_number.0" placeholder="Document#" >
                                @error('document_number.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                            <select  class="form-control"  wire:model.debounce.300ms="title.0" required>
                                <option value="">Select Title</option>
                                <option value="POD">POD</option>
                                <option value="CD3">CD3</option>
                            </select>
                            @error('title.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Upload File<span class="required" style="color: red">*</span></label>
                                <input type="file" class="form-control"  wire:model.debounce.300ms="file.0" placeholder="Upload File" id="{{ $rand }}" required>
                                @error('file.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Submitted On</label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="date.0" placeholder="Document Submitted On">
                                @error('date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiry_date">Document#</label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="document_number.{{ $value }}" placeholder="Document#" >
                                    @error('document_number.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control"  wire:model.debounce.300ms="title.{{ $value }}" required>
                                        <option value="">Select Title</option>
                                        <option value="POD">POD</option>
                                        <option value="CD3">CD3</option>
                                    </select>
                                    @error('title.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="file">Upload File<span class="required" style="color: red">*</span></label>
                                    <input type="file" class="form-control"  wire:model.debounce.300ms="file.{{$value}}" id="{{ $rand }}" placeholder="File Upload" required>
                                    @error('file.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="file">Submitted On</label>
                                    <input type="date" class="form-control"  wire:model.debounce.300ms="date.{{$value}}" placeholder="Document Submitted On">
                                    @error('date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Document</button>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Trip Document<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expiry_date">Document#</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="document_number" placeholder="Document#" >
                                @error('document_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                            <select  class="form-control"  wire:model.debounce.300ms="title" required>
                            <option value="">Select Title</option>
                            <option value="POD">POD</option>
                            <option value="CD3">CD3</option>
                            </select>
                            @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Upload File</label>
                                <small style="color: red">Selected File: <a href="{{asset('myfiles/documents/'.$filename)}}"><i class="fa fa-file"></i> {{$filename}}</a></small>
                                <input type="file" class="form-control"  wire:model.debounce.300ms="file" placeholder="Upload File" />
                                @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Submitted On</label>
                                <input type="date" class="form-control"  wire:model.debounce.300ms="date" placeholder="Document Submitted On">
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

@section('extra-js')
<script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
@endsection