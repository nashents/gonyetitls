<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>Inventory Details</strong> </a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Attachments</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Inventory#</th>
                                <td class="w-20 line-height-35">{{$inventory->inventory_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product Department</th>
                                <td class="w-20 line-height-35">{{$inventory->product?->department}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product</th>
                                <td class="w-20 line-height-35">{{$inventory->product ? $inventory->product->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">ID#s</th>
                                <td class="w-20 line-height-35">{{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35"> {{$inventory->vendor ? $inventory->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Location</th>
                                <td class="w-20 line-height-35">
                                     @if ($inventory->store)
                                             <strong>Store:</strong>  {{$inventory->store ? $inventory->store->name : ""}}
                                             <br>
                                        @endif
                                        @if ($inventory->product->category)
                                           <strong>Category:</strong> {{$inventory->product->category ? $inventory->product->category->name : ""}}  {{$inventory->product->category_value ? $inventory->product->category_value->name : ""}} 
                                           <br>
                                        @endif
                                        @if ($inventory->rack)
                                          
                                              <strong>Rack:</strong> {{$inventory->rack ? $inventory->rack->name : ""}} {{$inventory->rack ? $inventory->rack->rack_number : ""}}
                                                <br>
                                        @endif
                                        @if ($inventory->bin)
                                              <strong>Bin:</strong> {{$inventory->bin ? $inventory->bin->name : ""}} {{$inventory->bin ? $inventory->bin->bin_number : ""}} 
                                               <br>
                                        @endif
                                </td>
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Item Contents</th>
                                <td class="w-20 line-height-35"><strong>Content(s): </strong> {{$inventory->weight}} <strong>Bal: </strong> {{$inventory->balance ? $inventory->balance : ""}} {{$inventory->product ? $inventory->product->unit_of_measure : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$inventory->currency ? $inventory->currency->name : ""}}</td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Amount</th>
                                <td class="w-20 line-height-35">
                                    @if ($inventory->amount)
                                        {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">
                                    {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format( $inventory->tax_amount ? $inventory->tax_amount : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Additional Cost</th>
                                <td class="w-20 line-height-35">
                                    {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format( $inventory->cost ? $inventory->cost : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($inventory->total)
                                        {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total,2)}}
                                    @endif
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$inventory->purchase_date}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Condition </th>
                                <td class="w-20 line-height-35">{{$inventory->condition}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Type</th>
                                <td class="w-20 line-height-35">{{$inventory->purchase_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Warrant Expiry Date</th>
                                <td class="w-20 line-height-35">{{$inventory->warranty_exp_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Useful Life</th>
                                <td class="w-20 line-height-35">
                                    @if ($inventory->life)
                                         {{$inventory->life}}Year(s)
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Residual Value</th>
                                <td class="w-20 line-height-35">
                                    @if ($inventory->residual_value)
                                    {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->residual_value,2)}}        
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$inventory->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$inventory->status == 1 ? "success" : "danger"}}">{{$inventory->status == 1 ? "Active" : "Inactive"}}</span></td>
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
                    @livewire('documents.index', ['id' => $inventory->id,'category' =>'inventory'])
                  </div>


                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
