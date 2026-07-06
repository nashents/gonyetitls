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
                            <tr>
                                <th class="w-10 text-center line-height-35">Product</th>
                                <td class="w-20 line-height-35">{{$asset->product ? $asset->product->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product Department</th>
                                <td class="w-20 line-height-35">{{$asset->product?->department}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">ID#s</th>
                                <td class="w-20 line-height-35">{{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->part_number ? "PN#: ".$asset->part_number : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35"> {{$asset->vendor ? $asset->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Location</th>
                                <td class="w-20 line-height-35">
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
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Item Contents</th>
                                <td class="w-20 line-height-35"><strong>Content(s): </strong> {{$asset->weight}} <strong>Bal: </strong> {{$asset->balance ? $asset->balance : ""}} {{$asset->product ? $asset->product->unit_of_measure : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$asset->currency ? $asset->currency->name : ""}}</td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Amount</th>
                                <td class="w-20 line-height-35">
                                    @if ($asset->amount)
                                        {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">
                                    {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format( $asset->tax_amount ? $asset->tax_amount : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Additional Cost</th>
                                <td class="w-20 line-height-35">
                                    {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format( $asset->cost ? $asset->cost : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($asset->total)
                                        {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->total,2)}}
                                    @endif
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$asset->purchase_date}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Condition </th>
                                <td class="w-20 line-height-35">{{$asset->condition}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Type</th>
                                <td class="w-20 line-height-35">{{$asset->purchase_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Warrant Expiry Date</th>
                                <td class="w-20 line-height-35">{{$asset->warranty_exp_date}}</td>
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
                                <td class="w-20 line-height-35"><span class="badge bg-{{$asset->status == 1 ? "success" : "danger"}}">{{$asset->status == 1 ? "Active" : "Inactive"}}</span></td>
                            </tr>

                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group pull-right mt-10" >
                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                            </div>
                        </div>
                        </div>
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
