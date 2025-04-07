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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Purchase#
                                        </th>
                                        <th class="th-sm">Order Date
                                        </th>
                                        <th class="th-sm">Expiry Date
                                        </th>
                                        <th class="th-sm">Vendor
                                        </th>
                                        <th class="th-sm">Notes
                                        </th>
                                        <th class="th-sm">Products
                                        </th>
                                        <th class="th-sm">Currency
                                        </th>
                                        <th class="th-sm">Subtotal
                                        </th>
                                        <th class="th-sm">Tax Amt
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if (isset($purchases))
                                <tbody>
                                    @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>{{$purchase->purchase_number}}</td>
                                        <td>{{$purchase->date}}</td>
                                        <td><span class="label label-{{Carbon\Carbon::now() < $purchase->expiry ? 'success' : 'danger' }}">{{Carbon\Carbon::parse($purchase->expiry)->format('d-m-Y')}}</span></td>
                                        <td>{{$purchase->vendor ? $purchase->vendor->name : ""}}</td>
                                        <td>{{$purchase->description}}</td>
                                        <td>
                                            @foreach ($purchase->purchase_products as $purchase_product )
                                                {{$purchase_product->product ? $purchase_product->product->name : ""}} <br>
                                            @endforeach
                                        </td>
                                        <td>{{$purchase->currency ? $purchase->currency->name : ""}}</td>
                                        <td>{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->subtotal ? $purchase->subtotal : 0,2)}}</td>
                                        <td>{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->tax_amount ? $purchase->tax_amount : 0,2)}}</td>
                                        <td>{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->total ? $purchase->total : 0,2)}}</td>
                                        <td><span class="badge bg-{{($purchase->authorization == 'approved') ? 'success' : (($purchase->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($purchase->authorization == 'approved') ? 'approved' : (($purchase->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('purchases.show',$purchase->id)}}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                    <li><a href="#" wire:click="authorize({{$purchase->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                </ul>
                                            </div>
                                            @include('purchases.delete')
                                    </td>
                                      </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Purchase Orders Found ....
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
                                    @if (isset($purchases))
                                        {{ $purchases->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Purchase Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                    <select class="form-control" wire:model.debounce.300ms="authorize">
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                  <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
