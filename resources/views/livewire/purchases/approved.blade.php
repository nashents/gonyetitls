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
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">
                                            Date
                                           <hr style="margin-top:2px; margin-bottom:2px">
                                            Expiry
                                        </th>
                                        <th class="th-sm">Vendor
                                        </th>
                                        <th class="th-sm">Summary
                                        </th>
                                        <th class="th-sm">Ccy
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Auth
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
                                        <td>
                                            @if ($purchase->user)
                                                 {{$purchase->user ? $purchase->user->name : ""}} {{$purchase->user ? $purchase->user->surname : ""}}
                                                @if ($purchase->user->employee)
                                                    <br>
                                                    <small><strong>{{$purchase->user->employee->departments ? $purchase->user->employee->departments->first()->name : ""}}</strong></small>
                                                @endif
                                            @endif
                                        </td>
                                         <td>
                                            {{$purchase->date}}
                                           <hr style="margin-top:2px; margin-bottom:2px">
                                            <span class="badge bg-{{Carbon\Carbon::now() < $purchase->expiry ? 'success' : 'danger' }}">{{Carbon\Carbon::parse($purchase->expiry)->format('d-m-Y')}}</span>
                                        </td>
                                        <td>{{$purchase->vendor ? $purchase->vendor->name : ""}}</td>
                                        <td>
                                            @foreach ($purchase->purchase_products as $purchase_product )
                                                @if ($purchase_product->product)
                                                        {{$purchase_product->product ? $purchase_product->product->name : ""}} {{$purchase_product->product->brand ? $purchase_product->product->brand->name : ""}} ({{$purchase_product->qty}}) <br>
                                                @endif
                                            @endforeach
                                            
                                            @if ($purchase->description)
                                                <br>
                                                <i><strong>Notes: </strong> {{$purchase->description}}</i>
                                            @endif
                                        </td>
                                        <td>{{$purchase->currency ? $purchase->currency->name : ""}}</td>
                                        <td>{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->total ? $purchase->total : 0,2)}}

                                             @if (Auth::user()->employee->company->currency_id != $purchase->currency_id)
                                            <br>
                                            <small>
                                                <strong>Exc Rate:</strong> {{$purchase->exchange_rate}} <br>
                                                <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($purchase->exchange_amount,2)}}
                                            </small>
                                        @endif
                                        </td>
                                        <td><span class="badge bg-{{($purchase->authorization == 'approved') ? 'success' : (($purchase->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($purchase->authorization == 'approved') ? 'approved' : (($purchase->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @php
                                                $user = App\Models\User::find($purchase->authorized_by_id);
                                            @endphp
                                            @if ($user)
                                                <br>
                                               <small><strong style="background-color: orange">AuthBy: {{$user->name}} {{$user->surname}}</strong></small>  
                                            @endif
                                            @if ($purchase->authorization_comments)
                                            <br>
                                            <small><strong style="background-color: orange">Auth Comments: {{$purchase->authorization_comments}}</strong></small>  
                                            @endif 
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('purchases.show',$purchase->id)}}" ><i class="fa fa-eye color-default"></i> View</a></li>
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
