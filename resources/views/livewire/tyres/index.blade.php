<div>
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
                            <div class="panel-title">
                                <a href="{{route('tyres.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tyre</a>
                                <a href="" data-toggle="modal" data-target="#tyresImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search tyres...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">Type/Serial#
                                    </th>
                                    <th class="th-sm">Specs
                                    </th>
                                    <th class="th-sm">Purchase Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Tax Amt
                                    </th>
                                    <th class="th-sm">Subtotal(Incl)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($tyres))
                                <tbody>
                                    @forelse ($tyres as $tyre)
                                  <tr>
                                    <td>{{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}</td>
                                    <td>{{$tyre->type}} ({{$tyre->serial_number}})</td>
                                    <td>{{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}</td>
                                    <td>{{$tyre->purchase_date}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->name : ""}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->amount,2)}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->tax_amount ? $tyre->tax_amount : 0,2)}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}</td>
                                    <td>
                                        @if ($tyre->status == 0 && isset($tyre->tyre_assignment))
                                        <a href="{{route('tyre_assignments.show',$tyre->tyre_assignment->id)}}">
                                            <span class="badge bg-{{$tyre->status == 1 ? "warning" : "success"}}">{{$tyre->status == 1 ? "Unassigned" : "Assigned"}}</span>        
                                        </a>
                                       @else
                                       <span class="badge bg-{{$tyre->status == 1 ? "warning" : "success"}}">{{$tyre->status == 1 ? "Unassigned" : "Assigned"}}</span>        
                                    @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('tyres.show',$tyre->id) }}" ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="{{ route('tyres.edit',$tyre->id) }}" ><i class="fa fa-edit color-success"></i>Edit</a></li>
                                                <li><a href="#" wire:click="showDispose({{$tyre->id}})"  ><i class="fa fa-times color-warning"></i> Dispose</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tyreDeleteModal{{ $tyre->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('tyres.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Tyres Found ....
                                        </div>
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($tyres))
                                        {{ $tyres->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="disposeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Dispose Tyre <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="dispose()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Dispose Item<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="dispose" class="form-control" required >
                                   <option value="">Select Option</option>
                                   <option value="1">Yes</option>
                                   <option value="0">No</option>
                               </select>
                                @error('dispose') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Reason<span class="required" style="color: red">*</span></label>
                      <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3" required></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyresImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Tyres <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('tyres.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Tyres Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Loading Points File" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyreEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Tyre <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="width">Tyre Number</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="tyre_number"  placeholder="Tyre Number " required />
                        @error('tyre_number') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Product(s)<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="product_id" class="form-control" required>
                           <option value="">Select Product</option>
                         @foreach ($products as $product)
                            <option value="{{$product->id}}"> {{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                         @endforeach
                       </select>
                        @error('product_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Serial Number</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="serial_number"  placeholder="Serial Number " required />
                        @error('serial_number') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Width<i>(mL)</i> /</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="width"  placeholder="Width " required>
                        @error('width') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="aspect_ratio">A/Ratio (R)</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="aspect_ratio" placeholder="Aspect Ratio" required />
                        @error('aspect_ratio') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="diameter">Diameter<i>(in)</i></label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="diameter"  placeholder="Diameter " required />
                        @error('diameter') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Rate</label>
                        <input type="number" class="form-control" step="any" wire:model.debounce.300ms="amount"   required />
                        @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
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

