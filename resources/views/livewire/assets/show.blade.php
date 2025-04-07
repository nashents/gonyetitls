<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Asset Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Attachments</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Asset#</th>
                                <td class="w-20 line-height-35">{{$asset->asset_number}} </td>
                            </tr>
                            @if ($asset->purchase)
                            <tr>
                                <th class="w-10 text-center line-height-35">PurchaseOrder#</th>
                                <td class="w-20 line-height-35"><a href="{{ route('purchases.show',$asset->purchase->id) }}" style="color:blue">{{$asset->purchase ? $asset->purchase->purchase_number : "undefined"}}</a></td>
                            </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Image</th>
                                <td class="w-20 line-height-35"><img src="{{asset('images/uploads/'.$asset->product->filename)}}" style="width: 25%; height:25%;" alt=""> </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product</th>
                                <td class="w-20 line-height-35">{{$asset->product ? $asset->product->name : ""}} {{$asset->product ? $asset->product->model : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Serial Number</th>
                                <td class="w-20 line-height-35">{{$asset->serial_number}} </td>
                            </tr>
                            
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$asset->currency ? $asset->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Rate</th>
                                <td class="w-20 line-height-35">{{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->amount ? $asset->amount : 0,2)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">{{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->tax_amount ? $asset->tax_amount : 0,2)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total(Incl)</th>
                                <td class="w-20 line-height-35">{{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->subtotal_incl ? $asset->subtotal_incl : 0,2)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Condition</th>
                                <td class="w-20 line-height-35">{{$asset->condition}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Date</th>
                                <td class="w-20 line-height-35">{{$asset->purchase_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Type</th>
                                <td class="w-20 line-height-35">{{$asset->purchase_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Warranty Expiry Date</th>
                                <td class="w-20 line-height-35">{{$asset->warranty_exp_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Depriciation Type</th>
                                <td class="w-20 line-height-35">{{$asset->depreciation_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Useful Life</th>
                                <td class="w-20 line-height-35">
                                    @if ($asset->life)
                                        {{$asset->life}}Year(s)
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Residual Value</th>
                                <td class="w-20 line-height-35">
                                    @if ($asset->residual_value)
                                        {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->residual_value,2)}}        
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$asset->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35">
                                    @if ($asset->asset_assignments->count()>0)
                                    <a href="{{ route('asset_assignments.show',$asset->asset_assignments->first()->id) }}" target="_blank">  <span class="badge bg-{{$asset->status == 1 ? "success" : "danger"}}">{{$asset->status == 1 ? "Instore" : "Dispatched"}}</span></a>
                                   
                                    @else   
                                    <span class="badge bg-{{$asset->status == 1 ? "success" : "danger"}}">{{$asset->status == 1 ? "Instore" : "Dispatched"}}</span>
                                    @endif
                                </td>
                            </tr>

                        </tbody>
                    </table>

                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $asset->id,'category' =>'asset'])
                  </div>
               
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>


                <!-- /.section-title -->
            </div>
          
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
