<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                       
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <x-loading/>
                            @if (isset($employee))
                            @if (Auth::user()->employee)
                            @if (Auth::user()->employee->id != $employee->id)
                            <a href="#" data-toggle="modal" data-target="#documentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Document</a>
                            <a href="#" data-toggle="modal" data-target="#folderModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Folder</a>
                            @endif
                            @endif
                            @else   
                            <a href="#" data-toggle="modal" data-target="#documentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Document</a>
                            <a href="#" data-toggle="modal" data-target="#folderModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Folder</a>
                            @endif
                            <br>
                            <br>
                    
                            <table id="documentsTable" class="table  table-striped table-borderless table-sm table-responsive" cellspacing="0" width="100%">
                                
                                <tbody>
                                    @if ($folders->count()>0)
                                        @foreach ($folders as $folder)
                                            <tr>
                                                <td style="padding-top: 15px; width:100px" >   
                                                    @if ($selectedFolder != $folder->id)   
                                                        <a href="#" wire:click="setFolder({{$folder->id}})"><i class="fa fa-folder"></i> {{$folder->title}}</a> 
                                                        
                                                        @if (isset($employee))
                                                        @if (Auth::user()->employee)
                                                            @if (Auth::user()->employee->id != $employee->id)
                                                            <a href="#" wire:click="editFolder({{$folder->id}})" ><i class="fa fa-edit color-success"></i></a> <a href="#" wire:click="showFolderDelete({{$folder->id}})"><i class="fa fa-trash color-danger"></i></a> 
                                                            @endif
                                                            @endif
                                                        @else
                                                        <a href="#" wire:click="editFolder({{$folder->id}})" ><i class="fa fa-edit color-success"></i></a> <a href="#" wire:click="showFolderDelete({{$folder->id}})"><i class="fa fa-trash color-danger"></i></a> 
                                                        @endif
                    
                                                    @else 
                    
                                                        <a style="padding-left:5px; " href="#" wire:click="setFolder({{$folder->id}})"><i class="fa fa-folder-open"></i> {{$folder->title}}</a>
                                                        
                                                        @if (isset($selectedFolder))
                                                            @php
                                                                $folder_documents = $documents->where('folder_id',$selectedFolder)
                                                            @endphp
                                                            @if ($folder_documents->count()>0)
                                                                @foreach ($folder_documents as $document)
                                                                    <tr>
                                                                    <td style="padding-left: 29px;">
                                                                        <a href="{{asset('myfiles/documents/'.$document->filename)}}"><i class="fa fa-file"></i> {{$document->title}} -  {{$document->filename}}</a> | {{$document->expires_at}} <span class="badge bg-{{$document->status == 1 ? "success" : "danger"}}">{{$document->status == 1 ? "Valid" : "Expired"}}</span> <a href="#" wire:click="edit({{$document->id}})" ><i class="fa fa-edit color-success"></i></a> <a href="#" wire:click="showDocumentDelete({{$document->id}})"><i class="fa fa-trash color-danger"></i></a>
                                                                    </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                            <tr>
                                                                <td>
                                                                    <p style="text-alight:center; text-color:grey; margin-left:27px;">No documents in this folder.</p> 
                                                                </td>
                                                            </tr>
                                                            @endif
                                                        @endif
                                                    @endif 
                                                </td> 
                                            </tr>
                                        @endforeach
                                   
                                    @endif
                                    @php
                                        $uncategorized_documents = $documents->where('folder_id',NULL);
                                    @endphp
                                    @if ($uncategorized_documents->count()>0)
                               
                                        @foreach ($uncategorized_documents as $document)
                                        <tr>
                                            <td> 
                                                <a href="{{asset('myfiles/documents/'.$document->filename)}}"><i class="fa fa-file"></i> {{$document->title}} -  {{$document->filename}}</a> | {{$document->expires_at}} <span class="badge bg-{{$document->status == 1 ? "success" : "danger"}}">{{$document->status == 1 ? "Valid" : "Expired"}}</span> <a href="#" wire:click="edit({{$document->id}})" ><i class="fa fa-edit color-success"></i></a> <a href="#" wire:click="showDocumentDelete({{$document->id}})"><i class="fa fa-trash color-danger"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                    
                        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Document(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                    </div>
                                    <form wire:submit.prevent="store()" >
                                    <div class="modal-body">
                                        <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="title">Folders</label>
                                                <select wire:model.debounce.300ms="folder_id" class="form-control">
                                                    <option value="">Select Folder</option>
                                                    @foreach ($folders as $folder)
                                                    <option value="{{ $folder->id }}">{{ $folder->title }}</option>
                                                    @endforeach
                                                </select>
                                                <small>  <a href="#" wire:click="showFolder()" ><i class="fa fa-plus-square-o"></i> New Folder</a></small> 
                                                @error('folder_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            @if (isset($purchase))
                                            <div class="form-group">
                                                <label for="title">Titles<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="title" class="form-control" required>
                                                    <option value="">Select Title</option>
                                                    <option value="Invoice">Invoice</option>
                                                    <option value="Quotation 1">Quotation 1</option>
                                                    <option value="Quotation 2">Quotation 2</option>
                                                    <option value="Quotation 3">Quotation 3</option>
                                                    <option value="Receipt">Receipt</option>
                                                </select>
                                                @error('title') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @else
                                            <div class="form-group">
                                                <label for="title">Title<span class="required" style="color: red">*</span></label>
                                                <input type="text" class="form-control"  wire:model.debounce.300ms="title" placeholder="Enter Document Title" required>
                                                @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            @endif
                                        </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="file">Upload File<span class="required" style="color: red">*</span></label>
                                                        <input type="file"  class="form-control"  wire:model.debounce.300ms="file" id="{{ $rand }}" placeholder="Upload File" required>
                                                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="expiry_date">Expiry Date</label>
                                                        <input type="date" class="form-control"  wire:model.debounce.300ms="expires_at" placeholder="Expiry Date" >
                                                        @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    
                        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="folderModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Folder <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                    </div>
                                    <form wire:submit.prevent="storeFolder()" >
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control"  wire:model.debounce.300ms="folder_title" placeholder="Enter Folder Title" required>
                                            @error('folder_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="folderEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Folder <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                    </div>
                                    <form wire:submit.prevent="updateFolder()" >
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control"  wire:model.debounce.300ms="folder_title" placeholder="Enter Folder Title" required>
                                            @error('folder_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    
                    
                    
                        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Document <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                    </div>
                                    <form wire:submit.prevent="update()" >
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="title">Folders</label>
                                                    <select wire:model.debounce.300ms="folder_id" class="form-control"yy>
                                                        <option value="">Select Folder</option>
                                                        @foreach ($folders as $folder)
                                                        <option value="{{ $folder->id }}">{{ $folder->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small>  <a href="#" wire:click="showFolder()" ><i class="fa fa-plus-square-o"></i> New Folder</a></small> 
                                                    @error('folder_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                @if (isset($purchase))
                                                <div class="form-group">
                                                    <label for="title">Titles<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="title" class="form-control" required>
                                                        <option value="">Select Title</option>
                                                        <option value="Invoice">Invoice</option>
                                                        <option value="Quotation 1">Quotation 1</option>
                                                        <option value="Quotation 2">Quotation 2</option>
                                                        <option value="Quotation 3">Quotation 3</option>
                                                        <option value="Receipt">Receipt</option>
                                                    </select>
                                                    @error('title') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                @else
                                                <div class="form-group">
                                                    <label for="title">Title<span class="required" style="color: red">*</span></label>
                                                    <input type="text" class="form-control"  wire:model.debounce.300ms="title" placeholder="Enter Document Title" required>
                                                    @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="file">Upload File</label>
                                                    <small style="color: red">Selected File: <a href="{{asset('myfiles/documents/'.$filename)}}"><i class="fa fa-file"></i> {{$filename}}</a></small>
                                                    <input type="file"  class="form-control"  wire:model.debounce.300ms="file" id="{{ $rand }}" placeholder="Upload File" >
                                                    @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="expiry_date">Expiry Date</label>
                                                    <input type="date" class="form-control"  wire:model.debounce.300ms="expires_at" placeholder="Expiry Date" >
                                                    @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

                        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="documentDeleteModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content bg-danger">
                                    <div class="modal-body">
                                       <center> <strong>Are you sure you want to delete this Document?</strong> </center>
                                    </div>
                                    <form method="POST" >
                                    <div class="modal-footer no-border">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                            <button wire:click.prevent="deleteDocument()" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                                        </div>
                                        <!-- /.btn-group -->
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>

                        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="folderDeleteModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content bg-danger">
                                    <div class="modal-body">
                                       <center> <strong>Are you sure you want to delete this Folder?</strong> </center>
                                    </div>
                                    <form method="POST" >
                                    <div class="modal-footer no-border">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                            <button wire:click.prevent="deleteFolder()" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                                        </div>
                                        <!-- /.btn-group -->
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

  
   



</div>

