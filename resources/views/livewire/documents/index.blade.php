<div>
    <x-loading/>

    <style>
        .documents-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .documents-title h4 {
            margin: 0;
            font-weight: 700;
        }

        .documents-title small {
            color: #6b7280;
        }

        .documents-actions a {
            margin-left: 6px;
        }

        .document-folder {
            margin-bottom: 12px;
            padding: 12px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .document-folder-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .document-folder-header a {
            font-weight: 700;
        }

        .documents-list {
            margin-top: 10px;
        }

        .doc-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 13px 15px;
            margin-bottom: 9px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-left: 5px solid #28a745;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.03);
        }

        .doc-card.doc-expired {
            border-left-color: #dc3545;
            background: #fff8f8;
        }

        .doc-card.doc-warning {
            border-left-color: #ffc107;
            background: #fffdf5;
        }

        .doc-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 10px;
            font-size: 19px;
        }

        .doc-main {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }

        .doc-name {
            min-width: 0;
        }

        .doc-name a {
            font-weight: 700;
        }

        .doc-file-name {
            display: block;
            max-width: 420px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .doc-meta {
            min-width: 250px;
            text-align: right;
        }

        .doc-actions {
            min-width: 60px;
            text-align: right;
        }

        .doc-actions a {
            margin-left: 8px;
        }

        .empty-docs {
            padding: 30px;
            text-align: center;
            color: #6b7280;
            background: #fff;
            border: 1px dashed #d1d5db;
            border-radius: 10px;
        }

        .documents-pagination {
            margin-top: 18px;
            padding: 12px 0;
            display: flex;
            justify-content: flex-end;
        }

        .documents-pagination nav {
            margin-bottom: 0;
        }

        @media(max-width: 768px) {
            .documents-toolbar,
            .doc-card,
            .doc-main {
                display: block;
            }

            .documents-actions,
            .doc-meta,
            .doc-actions {
                text-align: left;
                margin-top: 10px;
            }

            .doc-icon {
                margin-bottom: 8px;
            }

            .documents-pagination {
                justify-content: flex-start;
            }
        }
    </style>

    @php
       $canManageDocuments = true;

    if (isset($employee) && Auth::user()->employee && Auth::user()->employee->id == $employee->id) {
        $canManageDocuments = false;
    }

    /*
        Supports both:
        1. Collection: Document::get()
        2. Paginator: Document::paginate()
    */
    $documentsCollection = $documents instanceof \Illuminate\Pagination\AbstractPaginator
        ? collect($documents->items())
        : collect($documents ?? []);

    $uncategorized_documents = $documentsCollection->where('folder_id', null);
    @endphp

    <div class="documents-toolbar">
        <div class="documents-title">
            <h4>
                <i class="fa fa-file"></i>
                {{ $category === 'all' ? 'Expired Documents' : 'Documents' }}
            </h4>
            <small>
                {{ $category === 'all' ? 'Showing expired documents across all modules.' : 'Manage uploaded files, folders, and expiry tracking.' }}
            </small>
        </div>

        @if($canManageDocuments)
            <div class="documents-actions">
                <a href="#" data-toggle="modal" data-target="#documentModal" class="btn btn-default">
                    <i class="fa fa-plus-square-o"></i> Document
                </a>
                <a href="#" data-toggle="modal" data-target="#folderModal" class="btn btn-default">
                    <i class="fa fa-plus-square-o"></i> Folder
                </a>
            </div>
        @endif
    </div>

    @if($category === 'all')
        <div class="documents-list">
            @forelse ($documentsCollection as $document)
                @include('livewire.documents.partials.document-row', [
                    'document' => $document,
                    'canManageDocuments' => $canManageDocuments
                ])
            @empty
                <div class="empty-docs">
                    <i class="fa fa-folder-open"></i><br>
                    No expired documents found.
                </div>
            @endforelse
        </div>

        @if($documents instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="documents-pagination">
                {{ $documents->links() }}
            </div>
        @endif
    @else
        @if ($folders && $folders->count() > 0)
            @foreach ($folders as $folder)
                <div class="document-folder">
                    <div class="document-folder-header">
                        <div>
                            @if ($selectedFolder != $folder->id)
                                <a href="#" wire:click.prevent="setFolder({{ $folder->id }})">
                                    <i class="fa fa-folder"></i> {{ $folder->title }}
                                </a>
                            @else
                                <a href="#" wire:click.prevent="setFolder({{ $folder->id }})">
                                    <i class="fa fa-folder-open"></i> {{ $folder->title }}
                                </a>
                            @endif
                        </div>

                        @if($canManageDocuments)
                            <div>
                                <a href="#" wire:click.prevent="editFolder({{ $folder->id }})">
                                    <i class="fa fa-edit color-success"></i>
                                </a>
                                <a href="#" wire:click.prevent="showFolderDelete({{ $folder->id }})">
                                    <i class="fa fa-trash color-danger"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if ($selectedFolder == $folder->id)
                        @php
                            $folder_documents = $documentsCollection->where('folder_id', $selectedFolder);
                        @endphp

                        <div class="documents-list">
                            @forelse ($folder_documents as $document)
                                @include('livewire.documents.partials.document-row', [
                                    'document' => $document,
                                    'canManageDocuments' => $canManageDocuments
                                ])
                            @empty
                                <div class="empty-docs">No documents in this folder.</div>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        @if ($uncategorized_documents->count() > 0)
            <div class="documents-list">
                @foreach ($uncategorized_documents as $document)
                    @include('livewire.documents.partials.document-row', [
                        'document' => $document,
                        'canManageDocuments' => $canManageDocuments
                    ])
                @endforeach
            </div>
        @elseif(!$folders || $folders->count() == 0)
            <div class="empty-docs">
                <i class="fa fa-folder-open"></i><br>
                No documents found.
            </div>
        @endif

        @if($documents instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="documents-pagination">
                {{ $documents->links() }}
            </div>
        @endif
    @endif

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label">
                        <i class="fa fa-plus"></i> Add Document(s)
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="store()">
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
                                    <small>
                                        <a href="#" wire:click.prevent="showFolder()">
                                            <i class="fa fa-plus-square-o"></i> New Folder
                                        </a>
                                    </small>
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
                                        <input type="text" class="form-control" wire:model.debounce.300ms="title" placeholder="Enter Document Title" required>
                                        @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Upload File<span class="required" style="color: red">*</span></label>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file" id="{{ $rand }}" placeholder="Upload File" required>
                                    @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="expires_at" placeholder="Expiry Date">
                                    @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="folderModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label">
                        <i class="fa fa-plus"></i> Add Folder
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="storeFolder()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="folder_title" placeholder="Enter Folder Title" required>
                            @error('folder_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="folderEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label">
                        <i class="fa fa-edit"></i> Edit Folder
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="updateFolder()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control" wire:model.debounce.300ms="folder_title" placeholder="Enter Folder Title" required>
                            @error('folder_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-refresh"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="documentDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to delete this Document?</strong></center>
                </div>

                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal">
                            <i class="fa fa-times"></i> Close
                        </button>
                        <button wire:click.prevent="deleteDocument()" class="btn bg-black btn-wide btn-rounded">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="folderDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to delete this Folder?</strong></center>
                </div>

                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal">
                            <i class="fa fa-times"></i> Close
                        </button>
                        <button wire:click.prevent="deleteFolder()" class="btn bg-black btn-wide btn-rounded">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="documentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label">
                        <i class="fa fa-edit"></i> Edit Document
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="update()">
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
                                    <small>
                                        <a href="#" wire:click.prevent="showFolder()">
                                            <i class="fa fa-plus-square-o"></i> New Folder
                                        </a>
                                    </small>
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
                                        <input type="text" class="form-control" wire:model.debounce.300ms="title" placeholder="Enter Document Title" required>
                                        @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Upload File</label>

                                    @if($filename)
                                        <small style="color: red">
                                            Selected File:
                                            <a href="{{ asset('myfiles/documents/'.$filename) }}" target="_blank">
                                                <i class="fa fa-file"></i> {{ $filename }}
                                            </a>
                                        </small>
                                    @endif

                                    <input type="file" class="form-control" wire:model.debounce.300ms="file" id="{{ $rand }}" placeholder="Upload File">
                                    @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="expires_at" placeholder="Expiry Date">
                                    @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 