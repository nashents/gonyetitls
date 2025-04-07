<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#purchaseQuotationsModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Document</a>
        <br>
        <br>
        <table id="quotationsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Title
                </th>
                <th class="th-sm">Vendor
                </th>
                <th class="th-sm">Document
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @foreach ($purchase_documents as $purchase_document)
              <tr>
                <td>{{$purchase_document->title}}</td>
                <td>{{$purchase_document->vendor->name}}</td>
                <td><a href="{{asset('myfiles/documents/'.$purchase_document->filename)}}">{{$purchase_document->filename}}</a></td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="edit({{$purchase_document->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            {{-- <li><a href="#" data-toggle="modal" data-target="#purchaseProductDeleteModal{{$purchase_document->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                        </ul>
                    </div>
                    @include('purchase_documents.delete')

            </td>
            </tr>
              @endforeach
            </tbody>
          </table>
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseQuotationsModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-file"></i> Upload Quotation<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="save()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <select wire:model.debounce.300ms="title.0" class="form-control">
                                        <option value="">Select Title</option>
                                        <option value="Invoice">Invoice</option>
                                        <option value="Quotation 1">Quotation 1</option>
                                        <option value="Quotation 2">Quotation 2</option>
                                        <option value="Quotation 3">Quotation 3</option>
                                        <option value="Receipt">Receipt</option>
                                     
                                    </select>
                                    @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="Product">Vendor Types</label>
                                    <select wire:model.debounce.300ms="selectedVendorType.0" class="form-control">
                                        <option value="">Select Type</option>
                                        @foreach ($vendor_types as $vendor_type)
                                          <option value="{{$vendor_type->id}}">{{$vendor_type->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedVendorType.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="Product">Vendors</label>
                                    <select wire:model.debounce.300ms="vendor_id.0" class="form-control">
                                        <option value="">Select Vendor</option>
                                        @if (!is_null($selectedVendorType))
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                                @endforeach
                                            @endif
                                    </select>
                                    @error('vendor_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="Product">Attach Quotation</label>
                                    <input type="file"  class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload Quote">
                                    @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>

                        </div>

                            @foreach ($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <select wire:model.debounce.300ms="title.{{$value}}" class="form-control">
                                            <option value="">Select Title</option>
                                            <option value="Quotation 1">Quotation 1</option>
                                            <option value="Quotation 2">Quotation 2</option>
                                            <option value="Quotation 3">Quotation 3</option>
                                            <option value="Invoice">Invoice</option>
                                            <option value="Receipt">Receipt</option>
                                        </select>
                                        @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="Product">Vendor Types</label>
                                        <select wire:model.debounce.300ms="selectedVendorType.{{$value}}" class="form-control">
                                            <option value="">Select Type</option>
                                            @foreach ($vendor_types as $vendor_type)
                                              <option value="{{$vendor_type->id}}">{{$vendor_type->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedVendorType.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="Product">Vendors</label>
                                        <select wire:model.debounce.300ms="vendor_id.{{$value}}" class="form-control">
                                            <option value="">Select Vendor</option>
                                            @if (!is_null($selectedVendorType))
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                                @endforeach
                                            @endif

                                        </select>
                                        @error('vendor_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="Product">Attach Quotation</label>
                                        <input type="file"  class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload Quotation ">
                                        @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                                <!-- /.col-md-6 -->
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
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseQuotationsEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-file"></i> Edit Quotation<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <select wire:model.debounce.300ms="title" class="form-control">
                                <option value="">Select Title</option>
                                <option value="Quotation 1">Quotation 1</option>
                                <option value="Quotation 2">Quotation 2</option>
                                <option value="Quotation 3">Quotation 3</option>
                                  <option value="Invoice">Invoice</option>
                                  <option value="Receipt">Receipt</option>
                            </select>
                            @error('title') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                       <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Product">Vendor Types</label>
                                <select wire:model.debounce.300ms="selectedVendorType" class="form-control">
                                    <option value="">Select Type</option>
                                    @foreach ($vendor_types as $vendor_type)
                                      <option value="{{$vendor_type->id}}">{{$vendor_type->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedVendorType') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Product">Vendors</label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control">
                                    <option value="">Select Vendor</option>
                                    @if (!is_null($selectedVendorType))
                                        @foreach ($vendors as $vendor)
                                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                        @endforeach
                                    @endif

                                </select>
                                @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       </div>
                        <div class="form-group">
                            <label for="Product">Attach Quotation</label>
                            <small>Selected File: <a href="{{asset('myfiles/documents/'.$previous_file)}}">{{$previous_file}}</a></small>
                            <input type="file"  class="form-control" wire:model.debounce.300ms="file"  placeholder="Upload Quote">
                            @error('file') <span class="text-danger error">{{ $message }}</span>@enderror
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
        @section('extra-js')
        <script>
            $(document).ready( function () {
                $('#quotationsTable').DataTable();
            } );
            </script>
            
        @endsection
</div>
