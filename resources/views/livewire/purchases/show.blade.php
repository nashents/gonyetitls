<div>
    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Purchase Order</a></li>
                <li role="presentation"><a href="#products" aria-controls="products" role="tab" data-toggle="tab">Products</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Attachments</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Order#</th>
                                <td class="w-20 line-height-35">{{$purchase->purchase_number}} </td>
                            </tr>
                            @if ($purchase->booking)
                            <tr>
                                <th class="w-10 text-center line-height-35">Booking</th>
                                <td class="w-20 line-height-35">
                                    <a href="{{ route('bookings.show',$purchase->booking->id) }}" style="color: blue">
                                        {{$purchase->booking ? $purchase->booking->booking_number : ""}} | 
                                        @if ($purchase->booking->horse)
                                        {{$purchase->booking->horse->horse_make ? $purchase->booking->horse->horse_make->name : ""}} {{$purchase->booking->horse->horse_model ? $purchase->booking->horse->horse_model->name : ""}} {{$purchase->booking->horse->registration_number}}
                                        @elseif ($purchase->booking->vehicle)
                                        {{$purchase->booking->vehicle->vehicle_make ? $purchase->booking->vehicle->vehicle_make->name : ""}} {{$purchase->booking->vehicle->vehicle_model ? $purchase->booking->vehicle->vehicle_model->name : ""}} {{$purchase->booking->vehicle->registration_number}}
                                        @elseif ($purchase->booking->trailer)
                                        {{$purchase->booking->trailer ? $purchase->booking->trailer->make : ""}} {{$purchase->booking->trailer ? $purchase->booking->trailer->model : ""}} {{$purchase->booking->trailer ? $purchase->booking->trailer->registration_number : ""}}
                                        @endif
                                    </a> </td>
                            </tr>
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$purchase->date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Expense Category</th>
                                <td class="w-20 line-height-35">{{$purchase->account ? $purchase->account->name : ""}}</td>
                            </tr>
                        
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35">{{$purchase->vendor ? $purchase->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$purchase->currency ? $purchase->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Subtotal</th>
                                <td class="w-20 line-height-35">{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->subtotal ? $purchase->subtotal : 0,2)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->tax_amount ? $purchase->tax_amount : 0,2)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total</th>
                                <td class="w-20 line-height-35">{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->total ? $purchase->total : 0,2)}}</td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Additional Notes</th>
                                <td class="w-20 line-height-35">{{$purchase->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($purchase->authorization == 'approved') ? 'success' : (($purchase->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($purchase->authorization == 'approved') ? 'approved' : (($purchase->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            @if ($purchase->authorization_comments)
                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">{{$purchase->authorization_comments}}</td>
                            </tr>
                            @endif
                           


                        </tbody>
                    </table>
                </div>

                <div role="tabpanel" class="tab-pane" id="products">
                  @livewire('purchases.products', ['id' => $purchase->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $purchase->id,'category' =>'purchase'])
                  </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                        </div>
                    </div>
                    </div>
                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>

</div>
