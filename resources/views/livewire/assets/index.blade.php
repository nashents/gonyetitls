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
                                <a href="{{route('assets.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Asset</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search assets...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                   <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">ID/Serial#
                                    </th>
                                    <th class="th-sm">Location
                                    </th>
                                    <th class="th-sm">Item Contents
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Amt
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Cost
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($assets))
                                <tbody>
                                    @forelse ($assets as $asset)
                                  <tr>
                                    <td>
                                    @if ($asset->product)
                                            {{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}     
                                    @endif
                                    </td>
                                    <td>{{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->product->identification_number ? "PN#: ".$asset->product->identification_number : ""}}</td>
                                    <td>
                                        @if ($asset->store)
                                             <strong>Store:</strong>  {{$asset->store ? $asset->store->name : ""}}
                                             <br>
                                        @endif
                                        @if ($asset->product->category)
                                           <strong>Category:</strong> {{$asset->product->category ? $asset->product->category->name : ""}}  {{$asset->product->category_value ? $asset->product->category_value->name : ""}} 
                                           <br>
                                        @endif
                                        @if ($asset->rack)
                                          
                                              <strong>Rack:</strong> {{$asset->rack ? $asset->rack->name : ""}} {{$asset->rack ? $asset->rack->rack_number : ""}}
                                                <br>
                                        @endif
                                        @if ($asset->bin)
                                              <strong>Bin:</strong> {{$asset->bin ? $asset->bin->name : ""}} {{$asset->bin ? $asset->bin->bin_number : ""}} 
                                               <br>
                                        @endif
                                       
                                    </td>
                                    <td> <strong>Content(s): </strong> {{$asset->weight}} <strong>Bal: </strong> {{$asset->balance ? $asset->balance : ""}} {{$asset->product ? $asset->product->unit_of_measure : ""}}</td>
                                    <td>
                                        @if ($asset->purchase_date)
                                            {{Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d')}}        
                                        @endif
                                    </td>
                                    <td>{{$asset->currency ? $asset->currency->name : ""}}</td>
                                    <td>
                                        @if ($asset->amount)
                                            {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->amount,2)}}  
                                        @endif
                                    </td>
                                    <td>
                                        {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->tax_amount ? $asset->tax_amount : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->cost ? $asset->cost : 0,2)}}  
                                    </td>
                                    <td>
                                        @if ($asset->subtotal_incl)
                                            {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->subtotal_incl,2)}}  
                                        @endif
                                        @if (Auth::user()->employee->company->currency_id != $asset->currency_id)
                                            <br>
                                            <small>
                                                <strong>Exc Rate:</strong> {{$asset->exchange_rate}} <br>
                                                <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($asset->exchange_amount,2)}}
                                            </small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$asset->status == 1 ? "success" : "danger"}}">{{$asset->status == 1 ? "Instore" : "Out Of stock"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('assets.show',$asset->id )}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="{{route('assets.edit',$asset->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click="showDispose({{$asset->id}})"  ><i class="fa fa-times color-warning"></i> Dispose</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#assetDeleteModal{{ $asset->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('assets.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Assets Found ....
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
                                    @if (isset($assets))
                                        {{ $assets->links() }} 
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Dispose Asset <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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


</div>

