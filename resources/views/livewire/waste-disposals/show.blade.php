<div>
    <div class="row mt-30">
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Waste Disposal Record</a></li>
                <li role="presentation" ><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">WasteDisposal#</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->waste_disposal_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->movement}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->user ? $waste_disposal->user->name : ""}} {{$waste_disposal->user ? $waste_disposal->user->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedOn</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->created_at}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">DisposedBy</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->employee ? $waste_disposal->employee->name : ""}} {{$waste_disposal->employee ? $waste_disposal->employee->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">DisposedOn</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->date}}</td>
                            </tr>
                            
                            @if ($waste_disposal->customer)
                                <tr>
                                    <th class="w-10 text-center line-height-35">TransferTo</th>
                                    <td class="w-20 line-height-35">{{$waste_disposal->customer ? $waste_disposal->customer->name : ""}}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$waste_disposal->currency ? $waste_disposal->currency->name : ""}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table  class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                        <caption>Waste Disposal Items</caption>
                        <thead>
                            <tr>
                                <th class="th-sm">Waste Type
                                </th>
                                <th class="th-sm">Additional Info
                                </th>
                                <th class="th-sm">Use
                                </th>
                                <th class="th-sm">Qty
                                </th>
                                <th class="th-sm">Ccy
                                </th>
                                <th class="th-sm">Amount
                                </th>
                            </tr>
                        </thead>
                        @if (isset($waste_disposal_items))
                            <tbody>
                                @forelse ($waste_disposal_items as $waste_disposal_item)
                                    <tr>
                                        <td>{{$waste_disposal_item->waste_type ? $waste_disposal_item->waste_type->name : ""}} {{$waste_disposal_item->waste_type ? $waste_disposal_item->waste_type->category : ""}} {{$waste_disposal_item->waste_type ? $waste_disposal_item->waste_type->composition : ""}}</td>
                                        <td>{{$waste_disposal_item->description}}</td>
                                        <td>{{$waste_disposal_item->use}}</td>
                                        <td>{{$waste_disposal_item->qty}} {{$waste_disposal_item->unit_of_measure}}</td>
                                        <td>{{$waste_disposal_item->currency ? $waste_disposal_item->currency->name : ""}}</td>
                                        <td>{{$waste_disposal_item->currency ? $waste_disposal_item->currency->symbol : ""}}{{number_format($waste_disposal_item->amount ? $waste_disposal_item->amount : 0, 2)}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Disposal Items Recorded....
                                            </div>
                                        </td>
                                    </tr>  
                                @endforelse
                            </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                        @endif
                    </table>
                </div>
               <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $waste_disposal->id,'category'=>'waste_disposal'])
                </div>
               
               <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
